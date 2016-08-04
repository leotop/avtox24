<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Emex Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * link to service description - http://wsdoc.emex.ru/
 * Интерфейс удалённого поставщика
 * Class EmexRemoteSupplier
 */
class EmexRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'http://ws.emex.ru/EmExService.asmx?WSDL';
    const WSDL_BRAND_DICTIONARY_ADDRESS = 'http://ws.emex.ru/EmExDictionaries.asmx?WSDL';
    const WSDL_TIMEOUT = 3;
    
    // Фильтр по заменам.
    const PARAM_SUBSTLEVEL_ORIGINAL = 'OriginalOnly';
    const PARAM_SUBSTLEVEL_ALL = 'All';
    
    // Фильтр по заменам.
    const PARAM_SUBSTFILTER_NONE = 'None';
    const PARAM_SUBSTFILTER_ORIGINAL_AND_REPLASES = 'FilterOriginalAndReplacements';
    const PARAM_SUBSTFILTER_ORIGINAL_AND_ANALOGS = 'FilterOriginalAndAnalogs';
    
    // Тип доставки.
    const PARAM_DELYVERY_PRI = 'PRI';
    const PARAM_DELYVERY_ALT = 'ALT';
    
    
    /**
     * @var string
     */
    public static $title = 'Emex';
    
    /**
     * public - для вывода в настройка
     * @var string
     */
    public $url = 'http://www.emex.ru'; // х
    
    /**
     * @var
     */
    protected $soap;
    
    /**
     * Создадим объект
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Инициализация.
     * 
     * @throws Exception
     */
    public function init()
    {   
		/*
		 * Get default and set new timeout for soap request.
		 */
		$defaultTimeout = ini_get('default_socket_timeout');
		ini_set("default_socket_timeout", self::WSDL_TIMEOUT);

		if (!class_exists('SoapClient')) {
			throw new Exception('SOAP module is not installed');
		}
		
		/*
		 * Create new soap client.
		 */
        try {
	        $this->soap = new SoapClient(self::WSDL_ADDRESS, array('trace' => false, 'soap_version' => SOAP_1_2));
        } catch(SoapFault $e) {
	        throw new Exception('WSDL error: ' . $e->GetMessage());
        }

		/*
		 * Set default timeout for soap request.
		 */
		ini_set("default_socket_timeout", $defaultTimeout);
    }


    /**
     * Авторизация.
     */
    public function login()
    {

    }
	
    
    /**
     * http://ws.emex.ru/EmExDictionaries.asmx?op=GetMakes
     * бренды кодируются по своей системе, а не используются по названиям.
     * 
     * @return array
     * @throws Exception
     */
    public function loadMakers($login, $password)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/cache.php');

        $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/remote_suppliers/'));
        $life_time = 60*60*24;
        $cache_id = 'emex-makers';
        if ($cachedResult = $lmCache->getData($cache_id, $life_time)) {
            return $cachedResult;
        } else {
            $soap = new SoapClient(self::WSDL_BRAND_DICTIONARY_ADDRESS, array('trace' => false, 'soap_version' => SOAP_1_2));
            try {
                $params = array(
                    'login' => $login,
                    'password' => $password
                );

                $response = $soap->__soapCall('GetMakesDict', array($params));
            } catch (Exception $e) {
                $error = trim($e->GetMessage());
                if (strpos($error, 'AccessProvider') !== false) {
                    throw new Exception($error, LM_AUTO_DEBUG_USER_ERROR);
                }
                throw $e;
            }

            $result = array();

            foreach ( (array)$response->GetMakesDictResult->ShortMakeInfo as $maker) {
				//$makeNameOrig = iconv('CP1251', 'UTF-8', $maker->MakeName);
				$makeNameClean = str_replace(" / ", "/", $maker->MakeName);
                $result[ mb_strtoupper(strval($makeNameClean)) ] = 
					array(
						'makeNameOrig'	=> $maker->MakeName,
						'makeNameLogo'	=> strval($maker->MakeLogo)					
					);
            }
            unset($soap);

            $lmCache->setData($cache_id, $result);
        }
        return $result;
    }
	
    
    /**
     * Поиск.
     * Сначала мы получаем страничку с результатами поиска.
     * Чтобы получить данные из "остальные предложения от", надо отправить на каждое по запросу.
     * с указание бренда и shkey.PGr (Original / ReplacementNonOriginal / ReplacementOriginal).
     * 
     * @throws Exception
     */
    public function search()
    {
        $login    = $this->profile_options['LOGIN'];
        $password = $this->profile_options['PASSWORD'];


        $brand_map = $this->loadMakers($login, $password);

        $logo = '';
        if (!empty($this->extra['e_ml'])) {
            $logo = $this->extra['e_ml'];
        } else if(!empty($this->brand_title)) {

            if(array_key_exists($this->brand_title, $brand_map)) {
                $logo = $brand_map[ $this->brand_title ]['makeNameLogo'];
            } else if(array_key_exists(mb_strtoupper($this->brand_title), $brand_map)) {
                $logo = $brand_map[ mb_strtoupper($this->brand_title) ]['makeNameLogo'];
            } else {
                $this->response_type = '404';
                return;
            }
        }

        /*
         * Параметры для поиска детали.
         */
        $param = array(
            'login' 			 => $login,
            'password' 			 => $password,
            'makeLogo' 			 => $logo,
            'detailNum'			 => $this->query,
            'substLevel' 		 => self::PARAM_SUBSTLEVEL_ALL,
            'substFilter'		 => $this->profile_options['FILTER_BY_TYPE_SPARES'] ? : self::PARAM_SUBSTFILTER_NONE,
            'deliveryRegionType' => self::PARAM_DELYVERY_PRI
        );
		
        /*
         * Некоторые вводят пароль русскими буквами.
         */
        if (!defined('BX_UTF') || BX_UTF !== true) {
            $param['password']   = iconv('CP1251', 'UTF-8', $param['password']);
        }
		
        try {
            $response = $this->soap->__soapCall('FindDetailAdv4', array($param));
        } catch (Exception $e) {
            $error = trim($e->GetMessage());
            if (strpos($error, 'AccessProvider') !== false) {
                throw new Exception($error, LM_AUTO_DEBUG_USER_ERROR);
            }
            throw $e;
        }

        if ($response->FindDetailAdv4Result->IsSuccess == false) {
            throw new Exception(strval($response->FindDetailAdv4Result->ErrorMessage), LM_AUTO_DEBUG_USER_ERROR);
        }
        /*
         * Два варианта того, как придут данные. В одном случае в $xml->FindDetailAdv2Result->DetailItem
         * окажется просто элемент с предложениями, а во втором - массив элементов с предложениями.
         * Пример первого случая - артикул 93196389.
         */
//         if (!is_array($response->FindDetailAdv3Result->Details)) {
//             $response = $response->FindDetailAdv3Result;
//         } else {
            $response = (array)$response->FindDetailAdv4Result->Details->SoapDetailItem;
//         }

        /*
            может вернуться один элемент, а может --массив. отличить удаётся именно проверкой, объект лежит в первом элементе или нет.
            если первый элемент -- не объект, то у нас идут сразу данные детали, поэтому надо привести к привычному массиву деталей.
        */
        if ( !empty($response) && !is_object($response[0])) {
            $response = array( 0 => $response );
        }
        $parts = array();

        /*
         * РЕЗЕРВНЫЕ КАТАЛОГИ
         * Скорее всего каталоги мы уже искали
         * Но если кто-то ещё вернул каталоги, надо показать, что для этой детали
         * есть именно у этого поставщика,
         * поэтому всё равно их пропишем
         */
        $reserve_catalogs = array();

        foreach ($response as $part) {

            if (is_object($part)) {//если пришло много деталей -- у нас part будет объектом, иначе --массивом.
                $part   = get_object_vars($part);
            }

            /*
             * Уникальная extra для детали
             */
            $extra = $this->extra;
            $extra['e_ml'] = strval($part['MakeLogo']);
//             $extra[''] = $part[''];
            $price          = floatval(str_replace(array(' ', ','), array('', '.') , $part['ResultPrice']));
            $brand_title    = strval($part['MakeName']);
            $article        = strval($part['DetailNum']);
            $title          = strval($part['DetailNameRus']);
            $quantity       = intval($part['Quantity']);
            

            $delivery_time  = $this->profile_options['DELIVERY_TIME'] == 'expected' ? intval($part['ADDays']) : intval($part['DeliverTimeGuaranteed']);
            $g_delivery_time  = intval($part['DeliverTimeGuaranteed']);
            $date_update    = '';
            $multiplication_factor = max(1, intval($part["LotQuantity"]));

            /*
                $MESS['LM_AUTO_SEARCH_GROUP_N'] = 'Искомый артикул';
                $MESS['LM_AUTO_SEARCH_GROUP_0'] = 'Неоригинальные аналоги';
                $MESS['LM_AUTO_SEARCH_GROUP_1'] = 'OEM аналоги';
                $MESS['LM_AUTO_SEARCH_GROUP_2'] = 'Продажные номера';
                $MESS['LM_AUTO_SEARCH_GROUP_3'] = 'Сравнительные номера';
                $MESS['LM_AUTO_SEARCH_GROUP_4'] = 'Замены';
                $MESS['LM_AUTO_SEARCH_GROUP_5'] = 'Замены устаревшего артикула';
                $MESS['LM_AUTO_SEARCH_GROUP_6'] = 'EAN';
                $MESS['LM_AUTO_SEARCH_GROUP_10'] = 'Другое';
            */

            switch ($part['PriceGroup']) {
                case 'Original':
                    $analog_type = 'N';
                    break;
                case 'NewNumber':
                    $analog_type = 5;
                    break;
                case 'ReplacementOriginal':
                    $analog_type = 1;
                    break;
                case 'ReplacementNonOriginal':
                    $analog_type = 0;
                    break;
                default:
                    $analog_type = 10;
            }

            /*
             * #6788, #9888
             * почему то в искомый артикул могут попадать лишние бренды, наверное за счет аналогов
			 * а возможно, проблема была в том что таблица brand_map содержит не все бренды - соотв. осуществлялся поиск без брендов
             */
            if ($analog_type == 'N' && strlen($this->extra[hash("crc32", $this->profile_options['LMRSID'], false).'bt']) > 0 && strtoupper(trim($brand_title)) != strtoupper(trim($this->extra[hash("crc32", $this->profile_options['LMRSID'],
                    false).'bt']))) {
                $analog_type = 0;
            }

            /*
             * Из-за этого при наличии словоформ при 2 запросах пропадали ориг. детали. Задач 12706
             */
            /*
            if ($analog_type == 'N' && strlen($this->extra['e_ml']) > 0 && strtoupper(trim($brand_map[ $this->brand_title ])) != strtoupper(trim($this->extra['e_ml']))) {
                $analog_type = 0;
            }
            */

            /*
             * Резервные каталоги
             */
            if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                $reserve_catalogs[$brand_title] = array(
                    'article' => $article,
                    'brand_title' => $brand_title,
                    'title' => $title,
                    'extra'=> array('e_ml'=>$part['MakeLogo'])
                );
            }

            $itempart = array(
                'id'                => 'emex',
                'article'           => LinemediaAutoPartsHelper::clearArticle($article),
                'brand_title'       => $brand_title,
                'title'             => $title,
                'price'             => $price,
                'quantity'          => $quantity,
                'delivery_time'     => $delivery_time * 24, // в часах
                'g_delivery_time'     => $g_delivery_time * 24, // в часах
                'date_update'       => $date_update,
                'data-source'       => self::$title,
                'multiplication_factor' =>$multiplication_factor
            );

            $extra['hash'] = md5($part['MakeLogo'].'|'.$part['DetailNum'].'|'.$part['PriceLogo'].'|'.$part['DestinationLogo']);

            $itempart['extra'] = $extra;

            $parts['analog_type_' . $analog_type] []= $itempart;
        }


        /*
         * Тип ответа
         */
        $this->response_type = count($reserve_catalogs) > 1 ? 'catalogs' : 'parts';
        if (count($reserve_catalogs) <= 1 && count($parts) == 0) {
            $this->response_type = '404';
        }

        $this->parts = $parts;
		
        /*
         * Резервные каталоги
         */
        $this->catalogs = array_values($reserve_catalogs);
    }

    
    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска.
     * 
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
    {
        $hash = $data['extra']['hash'];

        $this->query = $data['article'];
        $this->brand_title = $data['brand_title'];
        $this->extra = $data['extra'];

        $this->init();

        // Выполнить в любом случае для логина и получения id user
        $this->search();

        // Найдём именно эту деталь
        foreach ($this->parts as $group => $parts) {
            foreach ($parts as $part) {
                if ($part['extra']['hash'] == $hash) {
                    return $part;
                }
            }
        }

        throw new Exception(self::$title.': '.'Remote part not found');
    }

    
    /**
     * Получение конфига.
     * 
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            ),
            'DELIVERY_TIME' => array(
	            'title' => GetMessage('DELIVERY_TIME'),
	            'type' => 'list',
	            'values' => array('expected'=>GetMessage('DELIVERY_TIME_EXPECTED'),'guaranteed'=>GetMessage('DELIVERY_TIME_GUARANTEED'))
            ),
            
            'FILTER_BY_TYPE_SPARES' => array(
                'title' => GetMessage('FILTER'),
            	'type' => 'list',
                'values' => array(
                    'None' => GetMessage('NO_FILTER'),
                    'FilterOriginalAndReplacements' => GetMessage('ONLY_SOUGHT_ARTICLE_NEW_REPLACMENT'),
                    'FilterOriginalAndAnalogs' => GetMessage('ONLY_SOUGHT_ARTICLE_ANALOGS')
                )
            )

        );
    }

}

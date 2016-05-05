<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Allautoparts Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);


/**
 * Интерфейс удалённого поставщика
 * http://www.allautoparts.ru/closed/help/help.asp?id=44
 * Class AllautopartsRemoteSupplier
 */
class AllautopartsRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'https://allautoparts.ru/WEBService/SearchService.svc/wsdl?wsdl';
    const WSDL_TIMEOUT = 3;

    /**
     * @var string
     */
    public static $title = 'Allautoparts';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://www.allautoparts.ru/'; //
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
     * @throws Exception
     */
    public function init()
    {
		/*
		 * Get default and set new timeout for soap request
		 */
		$defaultTimeout = ini_get('default_socket_timeout');
		ini_set("default_socket_timeout", self::WSDL_TIMEOUT);
		
		if (!class_exists('SoapClient')) {
			throw new Exception('SOAP module is not installed');
		}

        $contextOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_host' => false,
                'peer_name' => 'allautoparts.ru'
            )
        );

        $sslContext = stream_context_create($contextOptions);

        /*
         * Create new soap client
         */
		try {
			$this->soap = new SoapClient(
                self::WSDL_ADDRESS,
                array(
                    'trace'          => false,
                    'soap_version'   => SOAP_1_1,
                    'compression'    => true,
                    'exceptions'     => 0,
                    'cache_wsdl'     => WSDL_CACHE_MEMORY,
                    'stream_context' => $sslContext
                )
            );
		} catch(SoapFault $e) {
			throw new Exception('WSDL error: ' . $e->GetMessage());
		}

		/*
		 * Set default timeout for soap request
		 */
		ini_set("default_socket_timeout", $defaultTimeout);
    }
    
    
    /**
     * Авторизация
     */
    public function login()
    {
        /*
         * Логин объединяется с поиском для ускорения загрузки страницы (один запрос вместо двух)
         */
    }

    /**
     * Создание входного xml для поставщика. нагло украдено с их официального примера
     * @param $data
     * @return string
     */
    protected function createSearchRequestXML($data)
    {
        $session_info = 'UserLogin="'.base64_encode($data['login']).'" UserPass="'.base64_encode($data['password']).'"';
        $xml = '<root>
                  <SessionInfo ParentID="'.$data['login_id'].'" '.$session_info.' />
                  <search>
                     <skeys>
                        <skey>'.$data['search_code'].'</skey>
                     </skeys>
                     <instock>'.$data['instock'].'</instock>
                     <showcross>'.$data['showcross'].'</showcross>
                     <periodmin>'.$data['periodmin'].'</periodmin>
                     <periodmax>'.$data['periodmax'].'</periodmax>
                  </search>
                </root>';
        return $xml;
    }

    /**
     * Поиск
     * @throws Exception
     */
    public function search()
    {
        $query 	  = rawurlencode($this->query);
        $param = array(
            'search_code'      	=> $query,
            'login_id'       	=> $this->profile_options['LOGIN_ID'], // COption::GetOptionString('linemedia.autoremotesuppliers', 'allautoparts_LOGIN_ID'),
            'login'    			=> $this->profile_options['LOGIN'], // COption::GetOptionString('linemedia.autoremotesuppliers', 'allautoparts_LOGIN'),
            'password' 			=> $this->profile_options['PASSWORD'], // COption::GetOptionString('linemedia.autoremotesuppliers', 'allautoparts_PASSWORD'),
            'instock'          	=> 1, //intval(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK') == 'Y'),
            'showcross'        	=> '1',
            'periodmin'        	=> '-1',
            'periodmax'        	=> '-1',
        );
        
        
        /*
         * Подготовим XML запрос
         */
        $xml = $this->createSearchRequestXML($param);
        
        try {
            $response = $this->soap->SearchOffer(array('SearchParametersXml' =>$xml));
        } catch (Exception $e) {
            throw $e;
        }

        $this->response_type = 'catalogs';

        $reserve_catalogs = array();
        $n = 0;
        $this->parts = array();
        
        try {
            $xml = simplexml_load_string($response->SearchOfferResult);
        } catch (Exception $e) {
            throw new Exception ('parse XML ' . $e->GetMessage());
        }
        
        /*
         * В этом элементе может содержаться строка с ошибкой
         */
        if ($xml->error) {
	        throw new Exception($xml->error->message);
        }
        

        $data = (array) $xml->rows;
        
        /*
         * Если железка одна, то она будет одна, иначе будет массив, поэтому такой вот трюк.
         */
        if (isset($data['row']->Quantity)) {
           	$data = array($data['row']);
        } elseif (isset($data['row'][0]->Quantity)) {
        	$data = $data['row'];
        } else {
        	$this->response_type = '404';
			return;
		}
		
		
        foreach ($data as $part) {
            if (is_object($part)) {
                $part = get_object_vars($part);
            }
            
            // Уточняющий запрос.
            if (!empty($this->brand_title) && strtolower(strval($part['AnalogueManufacturerName'])) !== strtolower($this->brand_title)) {
            	continue;
            }
            
            $price          = floatval(str_replace(array(' ', ','), array('', '.') , $part['Price']));
            $brand_title    = strval($part['AnalogueManufacturerName']);
            $article        = strval($part['AnalogueCode']);
            $title          = strval($part['ProductName']); // iconv('CP1251', 'UTF-8', strval($part['DetailNameRus']));
            $weight         = floatval($part['AnalogueWeight']);
            $quantity       = intval($part['Quantity']);
            $delivery_time  = intval($part['PeriodMin']) * 24;
            $date_update    = strval($part['UpdateAt']);

            $extra = array('aap_sid'=> (string)$part['SupplierID']);
            $extra['hash'] = md5($extra['aap_sid'].'|'.$price.'|'.$article);
			$extra['article_search'] = $this->query;
			$extra['brand_title_search'] = $this->brand_title;

            if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                $reserve_catalogs[ $brand_title ] = array(
                    'article' => $article,
                    'brand_title' => $brand_title,
                    'title' => $title,
                );
				$key = 'analog_type_N';
            } else {
                $key = 'analog_type_4';
            }
            
            // if (!empty($this->brand_title) && $this->brand_title !== $brand_title) {
            //  continue;
            // }
            
            $this->parts[$key] []= array(
                'id'                => 'allautoparts',
                'article'           => LinemediaAutoPartsHelper::clearArticle($article),
                'brand_title'       => $brand_title,
                'title'             => $title,
                'price'             => $price,
                'weight'            => $weight,
                'quantity'          => $quantity,
                'delivery_time'     => $delivery_time, // В часах
                'date_update'       => $date_update,
                'data-source'       => self::$title,
                'extra'             => $extra,
            );
            $n++;
        }

        /*
         * РЕЗЕРВНЫЕ КАТАЛОГИ
         */
        $this->catalogs = array_values($reserve_catalogs);
        if (empty($this->brand_title)) {
            $this->response_type = count($reserve_catalogs) > 1 ? 'catalogs' : 'parts';
        } else {
            $this->response_type = 'parts';
        }
        if (count($reserve_catalogs) <= 1 && count($this->parts) == 0) {
            $this->response_type = '404';
        }
        return;
    }

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
    {
        $hash = $data['extra']['hash'];

        $this->query = $data['extra']['article_search'];
        $this->brand_title = $data['extra']['brand_title_search'];
        $this->extra = $data['extra'];

        $this->init();

        // выполнить в любом случае для логина и получения id user
        $this->search();

        /*
         * Найдём именно эту деталь
         */
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
     * Получение конфигурационных данных.
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'LOGIN_ID' => array(
                'title' => GetMessage('LOGIN_ID'),
                'type'  => 'string',
            ),
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            ),
        );
    }

}


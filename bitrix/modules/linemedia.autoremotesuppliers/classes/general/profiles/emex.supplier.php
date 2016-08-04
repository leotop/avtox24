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
 * ��������� ��������� ����������
 * Class EmexRemoteSupplier
 */
class EmexRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'http://ws.emex.ru/EmExService.asmx?WSDL';
    const WSDL_BRAND_DICTIONARY_ADDRESS = 'http://ws.emex.ru/EmExDictionaries.asmx?WSDL';
    const WSDL_TIMEOUT = 3;
    
    // ������ �� �������.
    const PARAM_SUBSTLEVEL_ORIGINAL = 'OriginalOnly';
    const PARAM_SUBSTLEVEL_ALL = 'All';
    
    // ������ �� �������.
    const PARAM_SUBSTFILTER_NONE = 'None';
    const PARAM_SUBSTFILTER_ORIGINAL_AND_REPLASES = 'FilterOriginalAndReplacements';
    const PARAM_SUBSTFILTER_ORIGINAL_AND_ANALOGS = 'FilterOriginalAndAnalogs';
    
    // ��� ��������.
    const PARAM_DELYVERY_PRI = 'PRI';
    const PARAM_DELYVERY_ALT = 'ALT';
    
    
    /**
     * @var string
     */
    public static $title = 'Emex';
    
    /**
     * public - ��� ������ � ���������
     * @var string
     */
    public $url = 'http://www.emex.ru'; // �
    
    /**
     * @var
     */
    protected $soap;
    
    /**
     * �������� ������
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * �������������.
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
     * �����������.
     */
    public function login()
    {

    }
	
    
    /**
     * http://ws.emex.ru/EmExDictionaries.asmx?op=GetMakes
     * ������ ���������� �� ����� �������, � �� ������������ �� ���������.
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
     * �����.
     * ������� �� �������� ��������� � ������������ ������.
     * ����� �������� ������ �� "��������� ����������� ��", ���� ��������� �� ������ �� �������.
     * � �������� ������ � shkey.PGr (Original / ReplacementNonOriginal / ReplacementOriginal).
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
         * ��������� ��� ������ ������.
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
         * ��������� ������ ������ �������� �������.
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
         * ��� �������� ����, ��� ������ ������. � ����� ������ � $xml->FindDetailAdv2Result->DetailItem
         * �������� ������ ������� � �������������, � �� ������ - ������ ��������� � �������������.
         * ������ ������� ������ - ������� 93196389.
         */
//         if (!is_array($response->FindDetailAdv3Result->Details)) {
//             $response = $response->FindDetailAdv3Result;
//         } else {
            $response = (array)$response->FindDetailAdv4Result->Details->SoapDetailItem;
//         }

        /*
            ����� ��������� ���� �������, � ����� --������. �������� ������ ������ ���������, ������ ����� � ������ �������� ��� ���.
            ���� ������ ������� -- �� ������, �� � ��� ���� ����� ������ ������, ������� ���� �������� � ���������� ������� �������.
        */
        if ( !empty($response) && !is_object($response[0])) {
            $response = array( 0 => $response );
        }
        $parts = array();

        /*
         * ��������� ��������
         * ������ ����� �������� �� ��� ������
         * �� ���� ���-�� ��� ������ ��������, ���� ��������, ��� ��� ���� ������
         * ���� ������ � ����� ����������,
         * ������� �� ����� �� ��������
         */
        $reserve_catalogs = array();

        foreach ($response as $part) {

            if (is_object($part)) {//���� ������ ����� ������� -- � ��� part ����� ��������, ����� --��������.
                $part   = get_object_vars($part);
            }

            /*
             * ���������� extra ��� ������
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
                $MESS['LM_AUTO_SEARCH_GROUP_N'] = '������� �������';
                $MESS['LM_AUTO_SEARCH_GROUP_0'] = '�������������� �������';
                $MESS['LM_AUTO_SEARCH_GROUP_1'] = 'OEM �������';
                $MESS['LM_AUTO_SEARCH_GROUP_2'] = '��������� ������';
                $MESS['LM_AUTO_SEARCH_GROUP_3'] = '������������� ������';
                $MESS['LM_AUTO_SEARCH_GROUP_4'] = '������';
                $MESS['LM_AUTO_SEARCH_GROUP_5'] = '������ ����������� ��������';
                $MESS['LM_AUTO_SEARCH_GROUP_6'] = 'EAN';
                $MESS['LM_AUTO_SEARCH_GROUP_10'] = '������';
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
             * ������ �� � ������� ������� ����� �������� ������ ������, �������� �� ���� ��������
			 * � ��������, �������� ���� � ��� ��� ������� brand_map �������� �� ��� ������ - �����. ������������� ����� ��� �������
             */
            if ($analog_type == 'N' && strlen($this->extra[hash("crc32", $this->profile_options['LMRSID'], false).'bt']) > 0 && strtoupper(trim($brand_title)) != strtoupper(trim($this->extra[hash("crc32", $this->profile_options['LMRSID'],
                    false).'bt']))) {
                $analog_type = 0;
            }

            /*
             * ��-�� ����� ��� ������� ��������� ��� 2 �������� ��������� ����. ������. ����� 12706
             */
            /*
            if ($analog_type == 'N' && strlen($this->extra['e_ml']) > 0 && strtoupper(trim($brand_map[ $this->brand_title ])) != strtoupper(trim($this->extra['e_ml']))) {
                $analog_type = 0;
            }
            */

            /*
             * ��������� ��������
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
                'delivery_time'     => $delivery_time * 24, // � �����
                'g_delivery_time'     => $g_delivery_time * 24, // � �����
                'date_update'       => $date_update,
                'data-source'       => self::$title,
                'multiplication_factor' =>$multiplication_factor
            );

            $extra['hash'] = md5($part['MakeLogo'].'|'.$part['DetailNum'].'|'.$part['PriceLogo'].'|'.$part['DestinationLogo']);

            $itempart['extra'] = $extra;

            $parts['analog_type_' . $analog_type] []= $itempart;
        }


        /*
         * ��� ������
         */
        $this->response_type = count($reserve_catalogs) > 1 ? 'catalogs' : 'parts';
        if (count($reserve_catalogs) <= 1 && count($parts) == 0) {
            $this->response_type = '404';
        }

        $this->parts = $parts;
		
        /*
         * ��������� ��������
         */
        $this->catalogs = array_values($reserve_catalogs);
    }

    
    /**
     * �������� �������� ���������� � ������ (� �������� ����) ����������� �� ���, ��� ��� �������� ������� ���������� � ������ �� ������.
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

        // ��������� � ����� ������ ��� ������ � ��������� id user
        $this->search();

        // ����� ������ ��� ������
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
     * ��������� �������.
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

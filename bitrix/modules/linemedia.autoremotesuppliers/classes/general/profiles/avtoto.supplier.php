<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Autodoc Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/*
 * ??????????? ??????? ? ?????? ???? ?? ??????! ?? ?????????? ? ??? ????????!
 * ?????????? IncludeModuleLangFile ? GetMessage - ??? ?????????????? ? ?????????? ???????
 */
IncludeModuleLangFile(__FILE__);

/**
 * ????????? ????????? ??????????
 * http://www.part-kom.ru/webservices/
 * Class PartkomRemoteSupplier
 */
class AvtotoRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'http://www.avtoto.ru/services/search/soap.wsdl';
    const WSDL_TIMEOUT = 3;
    /**
     * @var string
     */
    public static $title = 'AvtoTo';
    /**
     * public - ??? ?????? ? ??????????
     * @var string
     */
    public $url = 'http://www.avtoto.ru'; //
    /**
     * @var
     */
    protected $soap;
    /**
     * @var
     */
    protected $search_time_limit = 3;

    /**
     * ???????? ??????
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * init
     * @throws Exception
     */
    public function init()
    {
		/*
		 * Get default and set new timeout for soap request
		 */
		$defaultTimeout = ini_get('default_socket_timeout');
		ini_set("default_socket_timeout", self::WSDL_TIMEOUT);

		/*
		 * Create new soap client
		 */
		try {
			$this->soap = new SoapClient(self::WSDL_ADDRESS, array('soap_version'   => SOAP_1_1, 'trace' => 1, 'encoding' => 'utf-8'));
		} catch(SoapFault $e) {
			throw new Exception('WSDL error: ' . $e->GetMessage());
		}

		/*
		 * Set default timeout for soap request
		 */
		ini_set("default_socket_timeout", $defaultTimeout);
    }
    
    /**
     * ???????????
     */
    public function login()
    {
        /*
         * ????? ???????????? ? ??????? ??? ????????? ???????? ???????? (???? ?????? ?????? ????)
         */
    }

    /**
     * ?????
     * @throws Exception
     */
    public function search()
    {
	    /*
        * ????? - ?????? - ID
        */
        $login     = $this->profile_options['LOGIN'];
        $password  = $this->profile_options['PASSWORD'];
        $client_id = $this->profile_options['CLIENT_ID'];

        $query 	  = $this->query;

        $param = array(
	        'user_id' => $client_id,
	        'user_login' => $login,
	        'user_password' => $password,
            'search_code' => $query,
            'search_cross' => $this->profile_options['USE_ANALOGS'] ? 'on' : 'off'
        );

        try {
        	$searchParam = $this->soap->__soapCall('SearchStart', array($param));
            //LinemediaAutoDebug::add('soap_call params ' . print_r($param, true));
        } catch (Exception $e) {
	        $error = trim($e->GetMessage());
	        if(strpos($error, 'password'))
	        	throw new Exception($error, LM_AUTO_DEBUG_USER_ERROR);

	        throw $e;
        }
        
        
        $start_time = microtime(true);
        
        while( microtime(true) - $start_time < $this->search_time_limit && empty($result)) {

            usleep(100000);

            try {
                $result = $this->soap->__soapCall('SearchGetParts', array($searchParam));
            } catch (Exception $e) {
                throw $e;
            }
        }

        if($result['Info']['Errors'][0]) {
            throw new Exception($result['Info']['Errors'][0], LM_AUTO_DEBUG_USER_ERROR);
        }

        if($result == array()) {
            throw new Exception('No parts', LM_AUTO_DEBUG_USER_ERROR);
        }

        foreach($result['Parts'] as $part) {
            /*
             * ?????????? extra ??? ??????
             */
            $extra = $this->extra;

            $extra['hash'] = md5(json_encode($part));

            $price 			= floatval(str_replace(array(' ', ','), array('','.') , $part['Price']));
            $brand_title 	= strval($part['Manuf']);
            $article 		= LinemediaAutoPartsHelper::clearArticle(strval($part['Code']));
            $title 			= strval($part['Name']);
            $quantity 		= intval($part['MaxCount']);
            $delivery_time  = explode('-', $part['Delivery']);
            $delivery_time  = intval(array_sum($delivery_time)/count($delivery_time));
            $date_update  	= strval($part['StorageDate']);

            $extra['Storage'] = $part['Storage'];
            $extra['article_search'] = $this->query;
            $extra['brand_title_search'] = $this->brand_title;
            $extra['DeliveryPercent'] = $part['DeliveryPercent'];
            $extra['MaxCount'] = $part['MaxCount'];
            $extra['BaseCount'] = $part['BaseCount'];
            $extra['PartId'] = $part['AvtotoData']['PartId'];
            $extra['hash'] = md5($article . $brand_title . $title . $delivery_time . $date_update . $part['AvtotoData']['PartId']);



            if($this->brand_title) {
                $brand_check = strtoupper($this->brand_title) == strtoupper($brand_title);
            } else {
                $brand_check = true;
            }

            if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query && $brand_check) {
                $this->catalogs[$brand_title] = array(
                    'article'           => $article,
                    'brand_title'       => $brand_title,
                    'title'             => $title,
                );

                $analog_type = 'N';
            } else {
                $analog_type = '0';
            }

            $this->parts['analog_type_' . $analog_type][] = array(
                'article'           => $article,
                'brand_title'       => $brand_title,
                'title'             => $title,
                'price'             => $price,
                'quantity'          => $quantity,
                'delivery_time'     => $delivery_time * 24, // ? ?????
                'date_update'       => $date_update,
                'data-source'       => self::$title,
                'extra'             => $extra,
            );
        }



        if (count($this->catalogs) == 1 || $this->brand_title != '') {
            $this->response_type = 'parts';
        } elseif (count($this->parts) == 0 && $this->brand_title != '' || count($this->catalogs) == 0) {
            $this->response_type = '404';
        } else {
            $this->response_type = 'catalogs';
        }
    }

    /**
     * ???????? ???????? ?????????? ? ?????? (? ???????? ????) ??????????? ?? ???, ??? ??? ???????? ??????? ?????????? ? ?????? ?? ??????
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
    {
		$this->init();
        $hash = $data['extra']['hash'];

        $this->query = $data['extra']['article_search'];
        $this->brand_title = $data['extra']['brand_title_search'] ?: '';
        // ????????? ? ????? ?????? ??? ?????? ? ????????? id user
        $this->search();

        /*
         * ????? ?????? ??? ??????
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
     * ???????? ????????????
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'CLIENT_ID' => array(
                'title' => GetMessage('CLIENT_ID'),
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
            'USE_ANALOGS' => array(
                'title' => GetMessage('USE_ANALOGS'),
                'type' => 'checkbox',
                'default' => true,
            ),
        );
    }
    
}


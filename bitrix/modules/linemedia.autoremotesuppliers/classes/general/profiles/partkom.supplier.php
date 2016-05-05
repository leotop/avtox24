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
 * ����������� ������� � ������ ���� �� ������! �� ���������� � ��� ��������!
 * ���������� IncludeModuleLangFile � GetMessage - ��� �������������� � ���������� �������
 */
IncludeModuleLangFile(__FILE__);

/**
 * ��������� ��������� ����������
 * http://www.part-kom.ru/webservices/
 * Class PartkomRemoteSupplier
 */
class PartkomRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'http://www.part-kom.ru/webservice/search.php?wsdl';
    const WSDL_TIMEOUT = 3;
    /**
     * @var string
     */
    public static $title = 'Part-Kom';
    /**
     * public - ��� ������ � ����������
     * @var string
     */
    public $url = 'http://www.part-kom.ru'; //
    /**
     * @var
     */
    protected $soap;
    /**
     * @var array
     */
    protected $makers = array();

    /**
     * �������� ������
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
	    $this->browser->setBaseUrl($this->url);
        $this->browser->setReferer($this->url);


		/*
		 * Get default and set new timeout for soap request
		 */
		$defaultTimeout = ini_get('default_socket_timeout');
		ini_set("default_socket_timeout", self::WSDL_TIMEOUT);

		/*
		 * Create new soap client
		 */
		try {
			$this->soap = new SoapClient(self::WSDL_ADDRESS, array('trace' => false));
		} catch(SoapFault $e) {
			throw new Exception('WSDL error: ' . $e->GetMessage());
		}

		/*
		 * Set default timeout for soap request
		 */
		ini_set("default_socket_timeout", $defaultTimeout);

        $this->loadMakers();
    }
    
    /**
     * �����������
     */
    public function login()
    {
        /*
         * ����� ������������ � ������� ��� ��������� �������� �������� (���� ������ ������ ����)
         */
    }

    /**
     * �����
     * @throws Exception
     */
    public function search()
    {
	    
	    /*
        * ����� - ������
        */
        $login    = $this->profile_options['LOGIN'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'partkom_LOGIN');
        $password = $this->profile_options['PASSWORD'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'partkom_PASSWORD');
        $query 	  = $this->query;
        
        
        /*$param = new stdClass();
        $param->login 			= $login;
        $param->password 		= $password;
        $param->detailNum 		= $query;
        $param->findSubstitutes = true;
        $param->store 			= true;
        $param->reCross 		= false;
        */
        
        $param = array(
	        'login' => $login,
	        'password' => $password,
	        'detailNum' => $query,
        );
        
        
        /*
         * � ��� ���� ���������, �.�. ��� ����� ������ �� �� ��������
         */
        
        if (intval($this->extra['ptk_mid']) > 0) {
	        $param['makerId'] = (int) $this->extra['ptk_mid'];
        } else {
	        if ($this->brand_title != '') {
	        	$maker = $this->makers[strtolower($this->brand_title)];
	        	$param['makerId'] = (int) $maker['id'];
	        } else {
		        $param['makerId'] = false;
	        }
        }

	    $param['findSubstitutes'] = true;
	    $param['store'] = false;
	    $param['reCross'] = false;

        try {
        	$response = $this->soap->__soapCall('FindDetail', $param);
            //LinemediaAutoDebug::add('soap_call params ' . print_r($param, true));
        } catch (Exception $e) {
	        $error = trim($e->GetMessage());
	        if(strpos($error, 'password'))
	        	throw new Exception($error, LM_AUTO_DEBUG_USER_ERROR);
	        
	        throw $e;
        }
        
        if($response['error']) {
            throw new Exception($response['error'], LM_AUTO_DEBUG_USER_ERROR);
        }

        $this->response_type = 'parts';
        
        if (count($response) == 0) {
        	$this->response_type = '404';
        }
        
        /*
        * ��������� ��������
        * ������ ����� �������� �� ��� ������
        * �� ���� ���-�� ��� ������ ��������, ���� ��������, ��� ��� ���� ������
        * ���� ������ � ����� ����������,
        * ������� �� ����� �� ��������
        */
        $reserve_catalogs = array();
        
        foreach ($response as $part) {                
            
            /*
             * ���������� extra ��� ������
             */
            $extra = $this->extra;
            
            $extra['hash'] = md5(json_encode($part));
            
            $price 			= floatval(str_replace(array(' ', ','), array('','.') , $part['price']));
            $brand_title 	= strval($part['maker']);
            $article 		= LinemediaAutoPartsHelper::clearArticle(strval($part['number']));
            $title 			= strval($part['description']);
            $quantity 		= intval($part['quantity']);
            $delivery_time  = intval($part['averageDeliveryDays']);
            $date_update  	= strval($part['lastUpdateDate']);
            //$extra['ptk_mid'] = intval($part['makerId']);
            // ivan 20.05.14 #8786
            // ���������� ��������� ����� ������� ������, � �� ������ ��� ������, ����� ����� ������ getPartData ����� � ������� ����������� � ������ ������� �� �����
            $extra['ptk_mid'] = $param['makerId'];
			$extra['article_search'] = $this->query;
			$extra['brand_title_search'] = $this->brand_title;

            
            
            if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query && !isset($catalogs[$brand_title])) {
            	$reserve_catalogs[$brand_title] = $part;
            }
            
            /*
            *	$MESS['LM_AUTO_SEARCH_FIND'] = '���� ����� ���������';
				$MESS['LM_AUTO_SEARCH_FINDING'] = '���� ����������� �������� � �����������...';
				
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
            $analog = trim($part['detailGroup']);
            
            switch ($analog) {
                case 'Original':
                	$analog_type = 'N';
                    break;
                case 'ReplacementNonOriginal':
                	$analog_type = '0';
                    break;
                case 'ReplacementOriginal':
                	$analog_type = '1';
                    break;
                default:
                   	$analog_type = '10';
            }
            
            
            $parts['analog_type_' . $analog_type][] = array(
                'id'                => 'partkom',
                'article'           => $article,
                'brand_title'       => $brand_title,
                'title'             => $title,
                'price'             => $price,
                'quantity'          => $quantity,
                'delivery_time'     => $delivery_time * 24, // � �����
                'date_update'       => $date_update,
                'data-source'       => self::$title,
                'extra'             => $extra,
            );
        }
        
        $this->parts = $parts;
        
        if (count($parts) == 0) {
        	$this->response_type = '404';
        }

        //LinemediaAutoDebug::add('parts ' . print_r($parts, true));
        /*
         * ��� ����� � ��������� �������� ����
         */
        if ($this->brand_title == '' && count($reserve_catalogs) > 1) {
	        $this->response_type = 'catalogs';
	    }

	    
        $good_catalogs = array();
        foreach ($reserve_catalogs as $catalog) {
        
        	$brand_title 	= strval($catalog['maker']);
            $article 		= strval($catalog['number']);
            $title 			= strval($catalog['description']);

        
	        $good_catalogs[] = array(
	        	'article'           => $article,
                'brand_title'       => $brand_title,
                'title'             => $title,
                'extra'             => array('ptk_mid' => $catalog['makerId']),
	        );
        }
        
        $this->catalogs = $good_catalogs;

    }

    /**
     * �������� �������� ���������� � ������ (� �������� ����) ����������� �� ���, ��� ��� �������� ������� ���������� � ������ �� ������
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
        // ivan 14.05.14 #8602
        // �������� exttra ��������� ��� ����������� ������ $this->search();
        $this->extra = $data['extra'];

        // ��������� � ����� ������ ��� ������ � ��������� id user
        $this->search();
        
        /*
         * ����� ������ ��� ������
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
     * ��������� ��������������
     * @throws Exception
     */
    protected function loadMakers()
    {
        $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/remote_suppliers/'));
		$life_time = 60*60*24;
		$cache_id = 'partkom-makers'; 
		if ($cachedResult = $lmCache->getData($cache_id, $life_time)) {

		    $this->makers = $cachedResult;

		} else {
			
            $login    = $this->profile_options['LOGIN'];
            $password = $this->profile_options['PASSWORD'];

	        $param = array(
		        'login' => $login,
		        'password' => $password,
	        );

	        try {
	        	$response = $this->soap->__soapCall('getMakersDict', $param);
	        } catch (Exception $e) {
		        $error = trim($e->GetMessage());
		        if(strpos($error, 'password'))
		        	throw new Exception(GetMessage('LM_AUTO_PK_ERR').$error, LM_AUTO_DEBUG_USER_ERROR);
		        
		        throw $e;
	        }
	        
	        $makers = array();
	        
	        foreach ($response as $maker) {
		        $makers[strtolower($maker['name'])] = $maker;
	        }

	        $this->makers = $makers;

            $lmCache->setData($cache_id, $makers);
		}
    }

    /**
     * �������� ������������
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
        );
    }
    
}


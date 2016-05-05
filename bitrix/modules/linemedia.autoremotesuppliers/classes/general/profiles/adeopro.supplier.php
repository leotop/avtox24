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

IncludeModuleLangFile(__FILE__); 


/**
 * ��������� ��������� ����������
 * https://www.dropbox.com/s/bk5x6e8osgw36eo/adeo.pro%20-%20XML_Service.doc
 * Class AdeoproRemoteSupplier
 */
class AdeoproRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'Adeo.Pro';
    /**
     * public - ��� ������ � ����������
     * @var string
     */
    public $url = 'http://adeo.pro'; //

    /**
     * ������ �� �������?
     * ������������� � ���������� ������
     * @var bool
     */
    protected $search_analogs = false;

    /**
     * �������� ������
     */
    public function __construct()
    {
        parent::__construct();
        
    }

    /**
     * �������������.
     */
    public function init()
    {
        $this->browser->setBaseUrl($this->url);
        $this->browser->setReferer($this->url);
        
        $this->search_analogs = '' != $this->profile_options['USE_ANALOGS'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_USE_ANALOGS');
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
        $login    = $this->profile_options['LOGIN'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'adeopro_LOGIN');
        $password = $this->profile_options['PASSWORD'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'adeopro_PASSWORD');
	    
	    $login 		= rawurlencode($login);
	    $password 	= rawurlencode($password);
        $query 		= rawurlencode(strtoupper($this->query));
        
        /*
         * � ��� ���� ���������, �.�. ��� ����� ������ �� �� ��������
         */
        if ($this->brand_title != '') {
            if (!empty($this->extra["adp_oa"])) {
                $query = rawurlencode($this->extra["adp_oa"]);
            }
        	$this->response_type = 'parts';
            $brand_title = rawurlencode($this->brand_title);
            $url = "/pricedetals.php?login=$login&password=$password&code=$query&brand=$brand_title&sm=1";
            
        } else {
        	$this->response_type = 'catalogs';
            // ��������� ���, ������ ����
            $url = "/pricedetals.php?login=$login&password=$password&code=$query&sm=1";
        }
        
        try {
        	$page = $this->browser->get($url);
        } catch (Exception $e) {
	        throw $e;
        }
        
        /*
        * ���� � ����� ������ �� XML - ���� �������� ������
        */
        $query_info = $this->browser->getLastQueryInfo();
        if(strpos($query_info['content_type'], 'text/xml') === false)
        	throw new Exception($page, LM_AUTO_DEBUG_USER_ERROR);
        
        
        /*
        * � ����� �������� XML
        */
        try {
	        $xml = simplexml_load_string($page);
	    } catch (Exception $e) {
	        throw new Exception ('Error parse XML. ' . $e->GetMessage() . ' - ' . $page);
        }
        
        /*
         * ��������
         */
        if ($this->response_type == 'catalogs') {
            $catalogs = array();
	        foreach ($xml->detail as $catalog) {
	        	$catalog = get_object_vars($catalog);
                /**
                    ���� ������� �� ������ -- ����� ����� �������. ������ ��� ��� ����� ������ �������,������� ��������� �� ���������(��������, 0022, � �� ������ 22)
                    "!==" � �� "!=" �����������!
                */
                if ( LinemediaAutoPartsHelper::clearArticle($catalog['article']) !== $this->query  ) {
                    continue;
                }
                
		        $catalogs[] = array(
	                'article' => $catalog['article'],
	                'brand_title' => $catalog['producer'],
	                'title' => strval($catalog['ident']),//  ������
	            );
	        }

            $this->catalogs = $catalogs;
            
            /*
             * ���� �������� �� ������� ��� ������� ����� ����
             */
            if (count($catalogs) <= 1) {
            	/*
            	 * ��������� �������� ������
            	 */
            	$this->response_type = 'parts';
            	
            	/*
            	 * ���� �������� ���� ������� - �������� ��� �����
            	 */
            	$brand_title = (count($catalogs) == 1) ? $catalogs[0]['brand_title'] : $this->brand_title;
            	$brand_title = rawurlencode($brand_title);
	            
	            $url = "/pricedetals.php?login=$login&password=$password&code=$query&brand=$brand_title&sm=1";
	            
	            $this->browser->setParam(CURLOPT_CONNECTTIMEOUT, 7);
	            $this->browser->setParam(CURLOPT_TIMEOUT, 15);
            	
            	try {
		        	$page = $this->browser->get($url);
		        } catch (Exception $e) {
			        throw $e;
		        }
            	
		        try {
			        $xml = simplexml_load_string($page);
			    } catch (Exception $e) {
			        throw new Exception ('parsing analogs XML. ' . $e->GetMessage() . ' - ' . $page);
		        }
            }
        }
        
        
        /*
         * ������, � �� ��������
         */
        if ($this->response_type == 'parts') {

            /*
             * ��������� ��������
             * ������ ����� �������� �� ��� ������
             * �� ���� ���-�� ��� ������ ��������, ���� ��������, ��� ��� ���� ������
             * ���� ������ � ����� ����������,
             * ������� �� ����� �� ��������
             */
            $reserve_catalogs = array();
            
            foreach ($xml->detail AS $part) {
                $part = get_object_vars($part);
                
                
                /*
                 * ���������� extra ��� ������
                 */
                $extra = $this->extra;
                $extra['hash'] = md5(json_encode(array(
                	$part['code'], $part['producer'], $part['dataprice'], $part['deliverydays'], $part['price'], $part['rest'], $part['bra_id']
                )));
                
                $price 			= floatval(str_replace(array(' ', ','), array('','.') , $part['price']));
                $brand_title 	= strval($part['producer']);
                $article 		= LinemediaAutoPartsHelper::clearArticle(strval($part['code']));
                $title 			= strval($part['caption']);
                $quantity 		= intval($part['rest']);
                $delivery_time  = intval($part['deliverydays']);
                $date_update  	= strval($part['dataprice']);
                
                
                /*
                 * ��������� ��������
                 */
                if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                	$reserve_catalogs[ $brand_title ] = array(
                		'article' => $article,
                		'brand_title' => $brand_title,
                		'title' => $title,
                        'extra'=>$extra
                	);
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
                $analog = md5(trim($part['analog']));
                
                switch ($analog) {
	                case '�����':
	                case '64384e355af2ecc3163615dc50441cb3':
	                	$analog_type = 'N';
                        break;
	                case '��������':
	                case '34c4df5a6421b59acba169866ebfba89':
	                	$analog_type = '1';
                        break;
	                case '������':
	                case '9403389d3f435f60ff0270a3d5dbc3f5':
                        $analog_type = '0';
                        break;
	                default:
	                   	$analog_type = '10';
                        break;
                }

                $parts['analog_type_' . $analog_type][] = array(
                    'id'                => 'adeopro',
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


            /*
             * ��������� ��������
             */
            $this->catalogs = array_values($reserve_catalogs);

            
            $this->parts = $parts;
            
            if (count($parts) == 0) {
            	$this->response_type = '404';
            }
        }
    }

    /**
     * �������� �������� ���������� � ������ (� �������� ����) ����������� �� ���, ��� ��� �������� ������� ���������� � ������ �� ������
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
        
        throw new Exception(self::$title.': '.'Remote part ['.$this->query.' '.$this->brand_title.'] not found');
    }

    /**
     * ��������� �������.
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


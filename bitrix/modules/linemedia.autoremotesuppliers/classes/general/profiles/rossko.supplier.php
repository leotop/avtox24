<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Rossko Supplier
 *
 * @author  Linemedia
 * @since   17/02/2014
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * interface of remote supplier
 */
class RosskoRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{

    const WSDL_ADDRESS = 'http://kemerovo.rossko.ru/service/v1/GetSearch?wsdl';
    const WSDL_TIMEOUT = 3;
    /**
     * @var string
     */
    public static $title = 'Rossko';
    /**
     * @var string
     */
    public $url = 'http://khv.rossko.ru/';
    /**
     * @var
     */
    protected $soap;

    /**
     * Создадим объект
     */
    public function __construct() {
        parent::__construct();
    }


    /**
     * Инициализация.
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
        
        /*
         * Create new soap client
         */
        try {
            $this->soap = new \SoapClient(self::WSDL_ADDRESS, array('trace' => true, 'soap_version' => SOAP_1_2));
        } catch(SoapFault $e) {
            throw new Exception('WSDL error: ' . $e->GetMessage());
        }

		/*
		 * Set default timeout for soap request
		 */
		ini_set("default_socket_timeout", $defaultTimeout);
    }


    /**
     * Login
     */
    public function login()
	{
    }

    /**
     * Search
     * @throws Exception
     */
    public function search() {
        
        $param = array(
            'KEY1' => (string) $this->profile_options['AUTHORIZED_KEY1'],
            'KEY2' => (string) $this->profile_options['AUTHORIZED_KEY2'],
            'TEXT' => (string) $this->query
        );
        
        
        try {
            $response = $this->soap->__soapCall('GetSearch', array($param));
        } catch (\Exception $ex) {
            throw new Exception('Error: ' . $ex->GetMessage());
        }    
         
       
		$partsInfo = $response->SearchResults->SearchResult;

		if ($partsInfo->Message) {
			throw new Exception($partsInfo->Message);
		}
		
		if($partsInfo->Success != true) {
			$this->response_type = '404';
			return;
		}

		if(empty($partsInfo->PartsList)) {
			$this->response_type = '404';
			return;
		}

		/*
		 * If only one part
		 */
		if (!is_array($partsInfo->PartsList->Part)) {
			$partsInfo->PartsList->Part = array(
				$partsInfo->PartsList->Part
			);
		}

		foreach( $partsInfo->PartsList->Part as $part) {

			if (!$part->StocksList) {
				continue;
			}

			
			if ($this->brand_title != '' && strtoupper($this->brand_title) != strtoupper($part->Brand)) {
				continue;
			}
			
			$analogType = strcasecmp(LinemediaAutoPartsHelper::clearArticle($this->query), LinemediaAutoPartsHelper::clearArticle($part->PartNumber)) == 0
				? 'N'
				: '0';

			$this->savePart($part, $analogType);

			if ($this->brand_title == '') {
				$this->saveCatalog($part);
			}

			if($this->profile_options['USE_ANALOGS']) {
				if($part->CrossesList->Part) {

					/*
					 * If only one part
					 */
					if (!is_array($part->CrossesList->Part)) {
						$part->CrossesList->Part = array(
							$part->CrossesList->Part
						);
					}

					foreach( $part->CrossesList->Part as $cross) {
						$this->savePart($cross, '0');
					}
				}
			}
		}
		
		/*
		 * Set response code
		 */
		if (count($this->catalogs) == 1 || $this->brand_title != '') {
			$this->response_type = 'parts';
		} elseif (count($this->parts) == 0 && $this->brand_title != '' || count($this->catalogs) == 0) {
			$this->response_type = '404';
		} else {
			$this->response_type = 'catalogs';
		}

    }

    /**
     *  Get a part info
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
	{
		$this->query = $data['extra']['article_original'];
		$this->brand_title = $data['brand_title'];

		$this->init();
		$this->search();

		/*
		 * Найдём именно эту деталь
		 */
		foreach ($this->parts as $group => $parts) {
			foreach ($parts as $part) {
				if ($part['extra']['GUID'] == $data['extra']['GUID']) {
					return $part;
				}
			}
		}

		throw new Exception(self::$title.': '.'Remote part not found');
    }

    /**
     *  Save part
     * @param $part
     * @param $analogType
     */
    public function savePart($part, $analogType)
	{
		$price = 0;
		$quantity = 0;
		$delivery_time = 0;

		if (is_array($part->StocksList->Stock)) {
			foreach($part->StocksList->Stock as $stock) {
				if($stock->Price > 0 && $stock->Count > 0) {

					if ($price > 0 && $stock->Price > $price) {
						continue;
					}

					$price = $stock->Price;
					$quantity = $stock->Count;
					$delivery_time = $stock->DeliveryTime;
				}
			}
		} else {
			$price = $part->StocksList->Stock->Price;
			$quantity = $part->StocksList->Stock->Count;
			$delivery_time = $part->StocksList->Stock->DeliveryTime;
		}

		if ($price && $quantity) {
			$this->parts["analog_type_$analogType"][] = array(
				'id'                => 'rossko',
				'article'           => LinemediaAutoPartsHelper::clearArticle($part->PartNumber),
				'brand_title'       => strtoupper($part->Brand),
				'title'             => $part->Name,
				'price'             => $price,
				'quantity'          => $quantity,
				'delivery_time'     => $delivery_time * 24, // в часах
				'date_update'       => '',
				'data-source'       => self::$title,
				'extra'				=> array(
					'GUID'			   => $part->GUID,
					'article_original' => $part->PartNumber
				)
			);
		}

	}

    /**
     *  Save catalog
     * @param $catalog
     */
    public function saveCatalog($catalog)
	{
		$this->catalogs[strtoupper($catalog->Brand)] = array(
			'article' => LinemediaAutoPartsHelper::clearArticle($catalog->PartNumber),
			'brand_title' => strtoupper($catalog->Brand),
			'title' => $catalog->Name,
		);
	}

    /**
     * Получить конфигурацию
     * @return array
     */
    public function getConfigVars() {

        return array(
            'AUTHORIZED_KEY1' => array(
                'title' => GetMessage('KEY1'),
                'type'  => 'string',
            ),
            'AUTHORIZED_KEY2' => array(
                'title' => GetMessage('KEY2'),
                'type'  => 'string',
            ),
			'USE_ANALOGS' => array(
				'title' => GetMessage('USE_ANALOGS'),
				'type' => 'checkbox',
				'default' => false,
				'description' => GetMessage('USE_ANALOGS_DESCR'),
			),
        );
    }

}


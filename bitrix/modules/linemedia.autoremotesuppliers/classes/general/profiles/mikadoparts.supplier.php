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
 * Интерфейс удалённого поставщика
 * http://service.mikadoparts.ru/price.asmx
 * Class MikadopartsRemoteSupplier
 */
class MikadopartsRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'http://www.mikado-parts.ru/ws/service.asmx?WSDL';
    const WSDL_TIMEOUT = 3;
    /**
     * @var string
     */
    public static $title = 'MikadoParts';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://www.mikado-parts.ru/'; //
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
		
		/*
		 * Create new soap client
		 */
		try {
			$this->soap = new SoapClient(self::WSDL_ADDRESS, array('trace' => true, 'soap_version' => SOAP_1_2));
		} catch(SoapFault $e) {
			throw new Exception('WSDL error: ' . $e->GetMessage());
		}

		/*
		 * Set default timeout for soap request
		 */
		ini_set("default_socket_timeout", $defaultTimeout);
	}

    /*
     * Авторизация
     */
    public function login()
    {

    }

    /**
     * Поиск
     * @throws Exception
     */
    public function search()
    {

        /*
         * Получим детали по артикулу
         */

        try {
            $param = array(
                'Search_Code' => $this->query,
                'ClientID' => $this->profile_options['LOGIN'],
                'Password' => $this->profile_options['PASSWORD']
            );
            $response_parts = $this->soapCall('Code_Search', $param);
        } catch (Exception $e) {
            throw $e;
        }


        $parts = array();

        $catalogs = array();
		$uniqCatalogs = array();

		if(is_array($response_parts->Code_SearchResult->List->Code_List_Row)) {
			foreach ($response_parts->Code_SearchResult->List->Code_List_Row as $part) {


				$part = get_object_vars($part);


				$article = LinemediaAutoPartsHelper::clearArticle(strval($part['ProducerCode']));

				// Тип запчастей: оригинальные или нет.
				if ($part['CodeType'] == 'Aftermarket') {
					$analog_type = 'N';//LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;
				} elseif ($part['CodeType'] == 'AnalogOEM') {
					$analog_type = '1';//LinemediaAutoPart::ANALOG_GROUP_OEM;
				} else {
					$analog_type = '0';//LinemediaAutoPart::ANALOG_GROUP_UNORIGINAL;
				}

				if ($this->brand_title != '' && $this->brand_title != $part['Brand']) {
					continue;
				}

				$parts['analog_type_' . $analog_type][] = array(
					'id'                => 'mikadoparts',
					'article'           => $article,
					'brand_title'       => strval($part['ProducerBrand']),
					'title'             => strval($part['Name']),
					'price'             => floatval(str_replace(array(' ', ','), array('', '.') , $part['PriceRUR'])),
					'quantity'          => intval($part['OnStock']) ?: 0,
					'delivery_time'     => (intval($part['Srock']) ?: 0) * 24, // в часах
					'date_update'       => '',
					'data-source'       => self::$title,
					'extra'             => array('ZakazCode' => strval($part['ZakazCode']), 'hash' => md5(strval($part['ZakazCode']))),
				);
				//а нет ли у нас уже данного бренда в каталогах?
				if (!isset($uniqCatalogs[strval($part['Brand'])])) {
					$uniqCatalogs[strval($part['Brand'])] = 1;
					$catalogs[] = array(
						'article'       => LinemediaAutoPartsHelper::clearArticle(strval($this->query)),
						'brand_title'   => strval($part['Brand']),
						'title'         => strval($part['Name']),
					);
				}
			}
		}


		if (!empty($catalogs) && $this->brand_title == '') {
			$this->response_type = 'catalogs';
		} elseif (count($catalogs) == 0) {
			$this->response_type = '404';
		} else {
			$this->response_type = 'parts';
		}

        $this->parts = $parts;
        $this->catalogs = $catalogs;
    }

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска
     * @param $data
     * @return array
     * @throws Exception
     */
    public function getPartData($data)
    {

        try {
            $param = array(
                'ZakazCode' => strval($data['extra']['ZakazCode']),
                'ClientID' => $this->profile_options['LOGIN'],
                'Password' => $this->profile_options['PASSWORD']
            );
            $response_part = $this->soapCall('Code_Info', $param);

            $response_part = $response_part->Code_InfoResult;

            $part = array(
                'id'                => 'mikadoparts',
                'article'           => LinemediaAutoPartsHelper::clearArticle(strval($response_part->Code)),
                'brand_title'       =>  strval($response_part->Brand),
                'title'             => strval($response_part->Name),
                'price'             => intval($response_part->Prices->Code_PriceInfo->PriceRUR),
                'quantity'          => intval($response_part->Prices->Code_PriceInfo->Onstock),
                'delivery_time'     => intval($response_part->Prices->Code_PriceInfo->Srock) * 24, // в часах
                'date_update'       => '',
                'data-source'       => self::$title,
                'extra'             => array('ZakazCode' => strval($data['extra']['ZakazCode'])),
            );

            return $part;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * soapCall
     * @param $func
     * @param $args
     * @return mixed
     * @throws Exception
     */
    protected function soapCall($func, $args)
    {
        if(!$this->soap) $this->init();
        try {
            $response = $this->soap->__soapCall($func, array($args));
        } catch (Exception $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * Получение конфигурационных данных.
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


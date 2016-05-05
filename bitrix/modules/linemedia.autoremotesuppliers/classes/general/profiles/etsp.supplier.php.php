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

/*
* Интерфейс удалённого поставщика
* http://service.mikadoparts.ru/price.asmx
*/
class EtspRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
	const WSDL_ADDRESS_SEARCH = 'http://ws.etsp.ru/Search.svc?wsdl';
	const WSDL_ADDRESS_LOGON = 'http://ws.etsp.ru/Security.svc?wsdl';
	const WSDL_ADDRESS_REMAINS = 'http://ws.etsp.ru/PartsRemains.svc?wsdl';

	const WSDL_TIMEOUT = 3;

	public static $title = 'Etsp';

	public $url = 'http://www.etsp.ru/'; // public - для вывода в настройках

	protected $soap;



	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Инициализация.
	 */
	public function init()
	{
		$context  = stream_context_create(array('http' => array('timeout' => self::WSDL_TIMEOUT)));
		$response = file_get_contents(self::WSDL_ADDRESS_SEARCH, false, $context);

		if ($response === false) {
			throw new Exception('WSDL error');
		}
		$this->soap = new SoapClient(self::WSDL_ADDRESS_SEARCH, array('trace' => true, 'soap_version' => SOAP_1_1)); //1.1 !!!
	}

	/*
	 * Авторизация
	 */
	public function login()
	{

		$soap = new SoapClient(self::WSDL_ADDRESS_LOGON, array('trace' => true, 'soap_version' => SOAP_1_1));
		$session = $this->loadSession();

		try {
			if($session) {
				$param = array (
					'HashSession' => $session
				);
				$response = $soap->__soapCall('IsAuthentificate', array($param));

				if ((string) $response->IsAuthentificateResult) {
					return true;
				}
			}

			$params = array(
				'Login' => (string) $this->profile_options['LOGIN'],
				'Password' => (string) $this->profile_options['PASSWORD']
			);
			$response = $soap->__soapCall('Logon', array($params));	

			if($response->LogonResult && strpos($response->LogonResult, 'error') == false) {
				$this->saveSession((string)$response->LogonResult);
				return true;
			}
		} catch (Exception $e) {
			$error = trim($e->GetMessage());
			if(strpos($error, 'AccessProvider') !== false) {
				throw new Exception($error, LM_AUTO_DEBUG_USER_ERROR);
			}
			throw $e;
		}

		throw new Exception('Incorrect login', LM_AUTO_DEBUG_USER_ERROR);
	}

	/**
	 * Поиск
	 */
	public function search()
	{

		$parts = array();
		$catalogs = array();

		$session = $this->loadSession();

		try {
			$param = array(
				'Number' => $this->query,
				'HashSession' => $session
			);
			$response = $this->soapCall('SearchAdvanced', $param);
		} catch (Exception $e) {
			throw $e;
		}
		$response = simplexml_load_string($response->SearchAdvancedResult);

		foreach ($response->part as $catalog) {
			$catalog = (array) $catalog;

			if(empty($catalog['is_sklad']) && empty($catalog['is_shipment'])) {
				continue;
			}

			if ($this->brand_title != '' && $this->brand_title != $catalog['group'] . ' (' . $catalog['unique_number'] . ')') {
				continue;
			}

			if ($this->brand_title != '') {
				$parts = $this->getPartsData(array(
					'code'=> $catalog['code'],
					'title'=> $catalog['name'],
				));
			}

			$catalogs[] = array(
				'article'       => $catalog['unique_number'],
				'brand_title'   => $catalog['group'] . ' (' . $catalog['unique_number'] . ')',
				'title'         => $catalog['name'],
				'extra' => array(
					'code' => $catalog['code'],
					'note' => $catalog['note'] ?: '',
					'omega_number' => $catalog['omega_number'] ?: '',
					'skuba_number' => $catalog['skuba_number'] ?: '',
					'code_image_part' => $catalog['code_image_part'] ?: '',
					'is_part_attendant' => $catalog['is_part_attendant'] ?: '',
					'is_sklad' => $catalog['is_sklad'] ?: '',
					'is_shipment' => $catalog['is_shipment'] ?: '',
				),
			);
		}

		$this->catalogs = $catalogs;
		$this->parts= $parts;

		if (count($response->part) == 1 || $this->brand_title == '') {
			$this->response_type = 'parts';
		} elseif (count($response->part) == 0) {
			$this->response_type = '404';
		} else {
			$this->response_type = 'catalogs';
		}

	}


	/**
	 * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска
	 */
	public function getPartsData($data)
	{
		$soap = new SoapClient(self::WSDL_ADDRESS_REMAINS, array('trace' => true, 'soap_version' => SOAP_1_1));
		$session = $this->loadSession();

		try {
			$params = array(
				'Code' => (string) $data['code'],
				'ShowRetailRemains' => 1,
				'ShowOutsideRemains' => 0,
				'HashSession' => (string) $session,

			);
			$response = $soap->__soapCall('GetPartsRemainsByCode', array($params));

			$response_parts =  (array) simplexml_load_string($response->GetPartsRemainsByCodeResult);


			$response_parts = $response_parts['sklad_remains'];

			$parts = array();

			foreach ($response_parts as $key => $part) {

				$part = (array) $part;

				$article = LinemediaAutoPartsHelper::clearArticle(strval($part['goods_code']));

				// Тип запчастей: оригинальные или нет.
				if ($article == $this->query) {
					$analog_type = LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;
				} else {
					$analog_type = LinemediaAutoPart::ANALOG_GROUP_UNORIGINAL;
				}

				$quantity = str_replace('>', '', $part['quantity']);
				$quantity = str_replace('<', '', $quantity);

				$parts['analog_type_' . $analog_type][] = array(
					'id'                => 'etsp',
					'article'           => LinemediaAutoPartsHelper::clearArticle(strval($part['goods_code'])),
					'brand_title'       => strval($part['manufacturer_name']),
					'title'             => strval($data['title']),
					'price'             => intval($part['price']),
					'quantity'          => (int)$quantity,
					'delivery_time'     => 0, // в часах
					'date_update'       => '',
					'weight' => (float)$part['weight'],
					'data-source'       => self::$title,
					'extra'             => array(
						'id_goods_unit' => strval($part['id_goods_unit']),
						'goods_comment' => strval($part['goods_comment']),
						'storage_id' => strval($part['storage_id']),
						'storage_name' => strval($part['storage_name']),
						'storage_position' => strval($part['storage_position']),
						'remains_status_id' => strval($part['remains_status_id']),
						'remains_status_name' => strval($part['remains_status_name']),
						'weight' => (float)$part['weight'],
						'ordered' => strval($part['ordered']),
						'quantity_cart' => strval($part['quantity_cart']),
						'code' => $data['code'],
						'title' => strval($data['title']),
					),
				);
			}
			return $parts;

		} catch (Exception $e) {
			throw $e;
		}
	}
	public function getPartData($data)
	{
		$soap = new SoapClient(self::WSDL_ADDRESS_REMAINS, array('trace' => true, 'soap_version' => SOAP_1_1));
		$session = $this->loadSession();

		try {
			$params = array(
				'Code' => (string) $data['extra']['code'],
				'ShowRetailRemains' => 1,
				'ShowOutsideRemains' => 0,
				'HashSession' => (string) $session,

			);
			$response = $soap->__soapCall('GetPartsRemainsByCode', array($params));

			$response_parts =  (array) simplexml_load_string($response->GetPartsRemainsByCodeResult);


			$response_parts = $response_parts['sklad_remains'];

			$returnPart = array();

			foreach ($response_parts as $key => $part) {

				$part = (array) $part;

				$quantity = str_replace('>', '', $part['quantity']);
				$quantity = str_replace('<', '', $quantity);

				if (strtoupper($part['manufacturer_name']) == strtoupper($data['brand_title']) && LinemediaAutoPartsHelper::clearArticle(strval($part['goods_code'])) == $data['article']) {
					$returnPart = array(
						'id'                => 'etsp',
						'article'           => LinemediaAutoPartsHelper::clearArticle(strval($part['goods_code'])),
						'brand_title'       => strval($part['manufacturer_name']),
						'title'             => strval($data['extra']['title']),
						'price'             => intval($part['price']),
						'quantity'          => (int)$quantity,
						'delivery_time'     => 0, // в часах
						'date_update'       => '',
						'weight'			=> 0,//(float)$data['extra']['weight'],
						'data-source'       => self::$title,
						'extra'             => array(
							'id_goods_unit' => strval($part['id_goods_unit']),
							'goods_comment' => strval($part['goods_comment']),
							'storage_id' => strval($part['storage_id']),
							'storage_name' => strval($part['storage_name']),
							'storage_position' => strval($part['storage_position']),
							'remains_status_id' => strval($part['remains_status_id']),
							'remains_status_name' => strval($part['remains_status_name']),
							'weight' => 0,//(float)$part['weight'],
							'ordered' => strval($part['ordered']),
							'quantity_cart' => strval($part['quantity_cart']),
						),
					);
				}
			}
			$data1 = print_r($response_parts, 1);
			$data1 .= print_r($data, 1);

			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tesadfaf.txt', $data1);

			return $returnPart;

		} catch (Exception $e) {
			throw $e;
		}
	}

	protected function soapCall($func, $args)
	{
		try {
			$response = $this->soap->__soapCall($func, array($args));
		} catch (Exception $e) {

			throw $e;
		}


		return $response;
	}


	/**
	 * Сохранение и подгрузка cookie.
	 */
	protected function saveSession($session)
	{
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_etsp_session.txt', (string) $session);
	}


	/**
	 * Загрузка полученных cookie.
	 */
	protected function loadSession()
	{
		return file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_etsp_session.txt');
	}


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


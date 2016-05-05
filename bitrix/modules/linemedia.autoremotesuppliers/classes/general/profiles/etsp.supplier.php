<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Etsp Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * ��������� ��������� ����������
 * 'http://ws.etsp.ru/
 * Class EtspRemoteSupplier
 */
class EtspRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
	const WSDL_ADDRESS_SEARCH = 'http://ws.etsp.ru/Search.svc?wsdl';
	const WSDL_ADDRESS_LOGON = 'http://ws.etsp.ru/Security.svc?wsdl';
	const WSDL_ADDRESS_REMAINS = 'http://ws.etsp.ru/PartsRemains.svc?wsdl';

	const WSDL_TIMEOUT = 5;

	public static $title = 'Etsp';

	public $url = 'http://www.etsp.ru/'; // public - ��� ������ � ����������

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
			$this->soap = new SoapClient(self::WSDL_ADDRESS_SEARCH, array('trace' => true, 'soap_version' => SOAP_1_1));
		} catch(SoapFault $e) {
			throw new Exception('WSDL error: ' . $e->GetMessage());
		}

		/*
		 * Set default timeout for soap request
		 */
		ini_set("default_socket_timeout", $defaultTimeout);
	}

    /**
     * �����������
     * @return bool
     * @throws Exception
     */
    public function login()	{

		try {
			$soap = new SoapClient(self::WSDL_ADDRESS_LOGON, array('trace' => true, 'soap_version' => SOAP_1_1));
		} catch(SoapFault $e) {
			throw new Exception('WSDL error: ' . $e->GetMessage());
		}
		/*
		 * ��������� ��� ����������� ������
		 */
		$session = $this->loadSession();

		try {
			/*
			 * ���� ������ ���������, �� ��������� �� �� ����������
			 */
			if($session) {
				$param = array (
					'HashSession' => $session
				);
				$response = $soap->__soapCall('IsAuthentificate', array($param));

				if ((string) $response->IsAuthentificateResult) {
					return true;
				}
			}


			/*
			 * ���� ������ ��� ��� ��� �����������, �� �������� ����������� � ��������� ������
			 */

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

		/*
		 * ���� ��� ��� � �� ������� �������������� - ������ ������
		 */
		throw new Exception('Auth error or incorrect login/password', LM_AUTO_DEBUG_USER_ERROR);
	}

    /**
     * �����
     */
    public function search()
	{
		/*
		 * ���� ������ ��� �������, �� �� ��������� ������� (����� �� ��������� ������ �� ������ ����������� � ��� ����)
		 * � ������������� ��� ���� ������ �� ������������� �������� ��������, ������� �� ��������� � extra
		 */
		if ($this->extra['search_article']) {
			$this->query = $this->extra['search_article'];
		}

		/*
		 * �������� ��������
		 */
		$this->getGroups();

		/*
		 * �������� ������ ��� ������� �������� ������ � ��� ������, ���� ��� ���� �� ����������,
		 * ��� ����� �������� 1 ������ � ���������� �� ���� ���������
		 */
		foreach ($this->catalogs as $catalog) {
			if (count($this->catalogs) == 1 || $this->brand_title != '')
				$this->getPartsData(array(
					'code'=> $catalog['extra']['code'],
					'title'=> $catalog['title'],
					'group'=> $catalog['brand_title']
				));
		}

		/*
		 * ������������� ��� ������
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
     * ���������� ��������
     * @throws Exception
     */
    protected function getGroups()
	{
		/*
		 * ������ ���������
		 */
		$param = array(
			'Text' => $this->query,
			'HashSession' => $this->loadSession()
		);

		/*
		 * ������ ������ �������� ������
		 */
		try {
			$response = $this->soapCall('SearchBasic', $param);
		} catch (Exception $e) {
			throw $e;
		}

		/*
		 * �� ��������������� XML-������ �������� ������
		 */
		$response = simplexml_load_string($response->SearchBasicResult);

		//����� ��� ����� ������� ������ - x ���������� � ������� ���!
		if($response->error_message)
			throw new Exception($response->error_message, LM_AUTO_DEBUG_USER_ERROR);

		/*
		 * �������� ������ ����������� �����, ����� � ���������� ��������� ������������� ������������� ������
		 */
		$catalogs = array();

		foreach ($response->part as $catalog) {
			/*
			 * ����������� � ������, �.�. � ��� �������� ������
			 */
			$catalog = (array) $catalog;

			/*
			 * ���������� ������ ��������
			 */
			if (empty($catalog['is_sklad']) && empty($catalog['is_shops']) && empty($catalog['is_shipment'])  && empty($catalog['is_outside'])) {
				continue;
			}

			$catalogs[] = $catalog['group'];

			unset($catalog);
		}

		/*
		 * �������� ������ � �-��� ���������� �������
		 */
		$catalogs = array_count_values($catalogs);

		/*
		 * ������, � ������� ����� ��������� ���������� ������ ��� ������ ��������� ������������� ������
		 */
		$catalogsNumbers = array();

		/*
		 * ��������� �� ����������� ������� � ...
		 */
		foreach ($response->part as $catalog) {
			/*
			 * ����������� � ������, �.�. � ��� �������� ������
			 */
			$catalog = (array) $catalog;

			/*
			 * ���������� ������ ��������
			 */
			if (empty($catalog['is_sklad']) && empty($catalog['is_shops']) && empty($catalog['is_shipment'])  && empty($catalog['is_outside'])) {
				continue;
			}

			/*
			 * ������ ��������� ��� ��������� � ������ �������
			 */
			static $catalogNumber = 1;

			if (!$catalog['group']) {
				$catalog['group'] = $catalog['group'] ?:'Catalog #'.$catalogNumber;
				$catalogNumber++;
			}

			/*
			 * ������ ��������� ������������ �� ������ �������
			 */
			if ($catalogs[$catalog['group']] > 1) {
				$catalogsNumbers[$catalog['group']]++;
				$catalog['group'] = $catalog['group'] . ' #' . $catalogsNumbers[$catalog['group']];
			}

			/*
			 * ��������� ��������
			 */
			$this->catalogs[$catalog['group']] = array(
				'article'       => $catalog['code'],
				'brand_title'   => $catalog['group'],
				'title'         => $catalog['name'],
				'extra' => array(
					'code' => $catalog['code'],
					'note' => $catalog['note'] ?: '',
					'omega_number' => $catalog['omega_number'] ?: '',
					'skuba_number' => $catalog['skuba_number'] ?: '',
					'code_image_part' => $catalog['code_image_part'] ?: '',
					'is_part_attendant' => $catalog['is_part_attendant'] ?: '',
					'is_sklad' => $catalog['is_sklad'] ?: '',
					'is_shops' => $catalog['is_shops'] ?: '',
					'is_shipment' => $catalog['is_shipment'] ?: '',
					'is_outside' => $catalog['is_outside'] ?: '',
					//��������� ��������� ������, �.�. ��� ������� ����������
					//��� �������� � ������� ����������� ������� �� ��� ������ "code"
					'search_article' => $this->query
				),
			);

			unset($catalog);
		}

		/*
		 * ���� ����� �����, �� ������� �� ������ ��������
		 */
		if ($this->brand_title) {
			foreach ($this->catalogs as $brand => $catalogData) {
				if (strtoupper($this->brand_title) != strtoupper($brand)) {
					unset($this->catalogs[$brand]);
				}
			}
		}
	}

    /**
     * ������� ������ �� ���������� � �������� $data
     * @param $data
     * @return bool
     * @throws Exception
     */
    protected function getPartsData($data)
	{
		$soap = new SoapClient(self::WSDL_ADDRESS_REMAINS, array('trace' => true, 'soap_version' => SOAP_1_1));
		/*
		 * ��������� ������
		 */
		$session = $this->loadSession();

		/*
		 * ������ ��������� �������
		 */
		$params = array(
			'Code' => (string) $data['code'],
			//������� ������ �������� ��������� ����
			'ShowRetailRemains' => 1,
			//������� ������ ������� ��� �����
			'ShowOutsideRemains' => 1,
			'HashSession' => (string) $session,
		);

		/*
		 * ������ ������ �� ��������� �������� ��� ������� ��������
		 */
		try {
			$response = $soap->__soapCall('GetPartsRemainsByCode', array($params));
		} catch (Exception $e) {
			throw $e;
		}

		/*
		 * �� ��������������� XML-������ �������� ������
		 */
		$response_parts =  (array) simplexml_load_string($response->GetPartsRemainsByCodeResult);

		//����� ��� ����� ������� ������ - x ���������� � ������� ���!
		if($response->error_message)
			throw new Exception($response->error_message, LM_AUTO_DEBUG_USER_ERROR);

		/*
		 * �������� ������� �� �������
		 */
		$response_parts_sklad = $response_parts['sklad_remains'];

		/*
		 * ���� ������ ������ 1 ������
		 */
		if (is_object($response_parts_sklad)) {
			$response_part = (array) $response_parts_sklad;
			$response_parts_sklad = array();
			$response_parts_sklad[0] = $response_part;
		}

		/*
		 * ��������� ������ � ��������
		 */
		$this->getPartsFromResponse($response_parts_sklad, $data);

		/*
		 * �������� ������� ������� ��� �����
		 */
		$response_parts_outside = $response_parts['outside_remains'];

		/*
		 * ���� ������ ������ 1 ������
		 */
		if (is_object($response_parts_outside)) {
			$response_part = (array) $response_parts_outside;
			$response_parts_outside = array();
			$response_parts_outside[10000] = $response_part;
		}

		/*
		 * ��������� ������ � ��������
		 */
		$this->getPartsFromResponse($response_parts_outside, $data);

		return true;

	}

    /**
     * ��������� �������� �� ���������� ������
     * @param $response_parts
     * @param array $data
     * @return bool
     */
    protected function getPartsFromResponse($response_parts, $data = array())
	{
		foreach ($response_parts as $part) {
			$part = (array) $part;

			$article = LinemediaAutoPartsHelper::clearArticle(strval($part['manufacturer_number']));

			// ��� ���������: ������������ ��� ���.
			if ($article == $this->query) {
				$analog_type = 'N';
			} else {
				$analog_type = '0';
			}

			/*
			 * �������� �-��
			 */
			$quantity = str_replace('>', '', $part['quantity']);
			$quantity = str_replace('<', '', $quantity);

			$this->parts['analog_type_' . $analog_type][] = array(
				'id'                => 'etsp',
				'article'           => LinemediaAutoPartsHelper::clearArticle(strval($part['manufacturer_number'])),
				'brand_title'       => strval($part['manufacturer_name']),
				'title'             => strval($data['title']),
				'price'             => intval($part['price']),
				'quantity'          => (int)$quantity,
				'delivery_time'     => $part['delivery_time'] * 24, // � �����
				'date_update'       => '',
				'weight' 			=> (float)$part['weight'],
				'data-source'       => self::$title,
				'extra'             => array(
					'id_goods_unit' => strval($part['id_goods_unit']),
					'goods_comment' => strval($part['goods_comment']),
					'storage_id'	 => strval($part['storage_id']),
					'storage_name' => strval($part['storage_name']),
					'storage_position' => strval($part['storage_position']),
					'remains_status_id' => strval($part['remains_status_id']),
					'remains_status_name' => strval($part['remains_status_name']),
					'weight' => (float)str_replace(',', '.', $part['weight']),
					'ordered' => strval($part['ordered']),
					'quantity_cart' => strval($part['quantity_cart']),
					'code' => $data['code'],
					'title' => strval($data['title']),
					'catalog_code' => $data['code'],
					'catalog_group' => $data['group'],
					'hash' => md5 ($part['quantity'].$part['delivery_time'].$part['manufacturer_name'].$part['manufacturer_number'].$part['weight'])
				),
			);
		}

		return true;
	}

    /**
     * �������� ���������� � ������ (�������� ��� ���������� � �������)
     * @param $data
     * @return array
     * @throws Exception
     */
    public function getPartData($data)
	{
		$data['group'] = $data['extra']['catalog_group'];
		$data['code'] = $data['extra']['code'];
		$data['title'] = $data['extra']['title'];

		/*
		 * ����������������
		 */
		$this->init();

		/*
		 * ������������
		 */
		$this->login();

		$soap = new SoapClient(self::WSDL_ADDRESS_REMAINS, array('trace' => true, 'soap_version' => SOAP_1_1));

		/*
		 * ��������� ������
		 */
		$session = $this->loadSession();

		/*
		 * ��������� ������ ��� ��������� �������� ������� ������
		 */
		$params = array(
			'Code' => (string) $data['extra']['code'],
			//������� ������ �������� ��������� ����
			'ShowRetailRemains' => 1,
			//������� ������ ������� ��� �����
			'ShowOutsideRemains' => 1,
			'HashSession' => (string) $session,
		);

		try {
			$response = $soap->__soapCall('GetPartsRemainsByCode', array($params));
		} catch (Exception $e) {
			throw $e;
		}

		/*
		 * �� ��������������� XML-������ �������� ������
		 */
		$response_parts =  (array) simplexml_load_string($response->GetPartsRemainsByCodeResult);

		/*
		 * �������� ������� �� �������
		 */
		$response_parts_sklad = $response_parts['sklad_remains'];

		/*
		 * ���� ������ ������ 1 ������
		 */
		if (is_object($response_parts_sklad)) {
			$response_part = (array) $response_parts_sklad;
			$response_parts_sklad = array();
			$response_parts_sklad[0] = $response_part;
		}

		/*
		 * ��������� ������ � ��������
		 */
		$this->getPartsFromResponse($response_parts_sklad, $data);

		/*
		 * �������� ������� ������� ��� �����
		 */
		$response_parts_outside = $response_parts['outside_remains'];

		/*
		 * ���� ������ ������ 1 ������
		 */
		if (is_object($response_parts_outside)) {
			$response_part = (array) $response_parts_outside;
			$response_parts_outside = array();
			$response_parts_outside[10000] = $response_part;
		}

		/*
		 * ��������� ������ � ��������
		 */
		$this->getPartsFromResponse($response_parts_outside, $data);

		foreach ($this->parts as $group => $parts) {
			foreach ($parts as $part) {
				if ($part['extra']['hash'] == $data['extra']['hash']) {
					return $part;
				}
			}
		}
		return array();
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
		try {
			$response = $this->soap->__soapCall($func, array($args));
		} catch (Exception $e) {

			throw $e;
		}


		return $response;
	}

    /**
     * ���������� � ��������� cookie.
     * @param $session
     */
    protected function saveSession($session)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/';

		if (!file_exists($path)) {
			mkdir($path);
		}

		file_put_contents($path . 'lm_auto_remote_suppl_etsp2_session.txt', (string) $session);
	}

    /**
     * �������� ���������� cookie.
     * @return string
     */
    protected function loadSession()
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_etsp_session.txt';
		$hash = '';
		
		if (file_exists($file)) {
			$hash =  file_get_contents($file);
		}

		return $hash;

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


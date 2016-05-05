<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote MXgroup Supplier
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
class MoskvorechieRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
	/**
	 * appellation and URL of supplier
	 *
	 * @var string
	 */
	public static $title = 'Moskvorechie';
	/**
	 * @var string
	 */
	public $url = 'http://portal.moskvorechie.ru/portal.api';
	/**
	 * @var string
	 */
	private static $encoding = 'utf8';


	/**
	 * initiate object
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 *  authorization
	 *  login are united with searching for accelerating of page downloading
	 */
	public function login()
	{
	}

	/**
	 * init
	 */
	public function init()
	{
		$this->browser->setBaseUrl($this->url);
	}

	/**
	 * search
	 *
	 * @throws Exception
	 */
	public function search()
	{

		$baseUrl = '?l=' . $this->profile_options['LOGIN'] . '&p=' . $this->profile_options['PASSWORD'] . '&cs=' . self::$encoding . '&act=price_by_nr_firm&nr=' . $this->query;

		if ($this->brand_title) {
			$baseUrl .= '&f=' . $this->brand_title;
		}

		if ($this->profile_options['SHOW_ANALOGS'] || !isset($this->profile_options['SHOW_ANALOGS'])) {
			$baseUrl .= '&alt';
		}

		if ($this->profile_options['SEARCH_BY_CROSSES']) {
			$baseUrl .= '&oe';
		}

		if ($this->profile_options['ONLY_AVAILABLE']) {
			$baseUrl .= '&avail';
		}

		try {
			$answer = $this->browser->get($baseUrl . '&name\n');
		} catch (\Exception $ex) {
			throw new \Exception($ex->getMessage());
		}


		$response = json_decode($answer, true);
		if ($error = json_last_error()) {

			switch ($error) {

				case JSON_ERROR_DEPTH:
				{
					$error_message = 'JSON parsing was ended up with error: Maximum stack depth exceeded';
					break;
				}

				case JSON_ERROR_STATE_MISMATCH:
				{
					$error_message = 'JSON parsing was ended up with error: Underflow or the modes mismatch';
					break;
				}

				case JSON_ERROR_CTRL_CHAR:
				{
					$error_message = 'JSON parsing was ended up with error: Unexpected control character found';
					break;
				}

				case JSON_ERROR_SYNTAX:
				{
					$error_message = 'JSON parsing was ended up with error: Syntax error, malformed JSON';
					break;
				}

				case JSON_ERROR_UTF8:
				{
					$error_message = 'JSON parsing was ended up with error: Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				}

				default:
					{
					$error_message = 'JSON parsing was ended up with error: Unknown error';
					break;
					}

			}

			throw new \Exception($error_message);

		}


		if ($response['result']['msg']) {
			throw new Exception($response['result']['msg']);
		}

		if (empty($response['result'])) {
			throw new Exception('Empty result');
		}

		$query = LinemediaAutoPartsHelper::clearArticle($this->query);

		if (is_array($response['result'])) {

			foreach ($response['result'] as $k => $part) {

				$article = LinemediaAutoPartsHelper::clearArticle($part['nr']);


				if ($this->brand_title == '' && $query == $article) {

					$this->catalogs[$part['brand']] = array(
						'article'     => $part['nr'],
						'brand_title' => $part['brand'],
						'title'       => $part['name'],
						'source'      => self::$title,
						'extra'       => array()
					);
				}

				$brandsCheck = $this->brand_title ? strtolower($this->brand_title) == strtolower($part['brand']) : true;

				if ($article == $query && $brandsCheck) {
					// из консоли у нас нет модул€ авто
					$part_group = 'N'; //LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;
				} else {
					// из консоли у нас нет модул€ авто
					$part_group = '0'; //LinemediaAutoPart::ANALOG_GROUP_UNORIGINAL;
				}


				$this->parts['analog_type_' . $part_group][] = array(
					'id'            => self::$title,
					'article'       => $article,
					'brand_title'   => $part['brand'],
					'title'         => $part['name'],
					'price'         => $part['price'],
					'quantity'      => (int)$part['stock'],
					'delivery_time' => $part['delivery'] == GetMessage('PART_ON_STOCK') ? 0 : (int)$part['delivery'],
					'modified'      => $part['upd'],
					'data-source'   => self::$title,
					'extra'         => array(
						'currency' => $part['currency'],
						'upd'      => $part['upd'],
						'id'       => $part['id'],
						'minq'     => $part['minq'],
						'hash'     => md5($article . $part['brand'] . $part['name'] . $part['price'] . $part['stock'] . $part['delivery'] . $part['currency'] . $part['upd'])
					)
				);


			}

		}


		/**
		 * if brand not found than perhaps will be returned either catalogs or spares
		 */
		if (count($this->catalogs) == 1 || $this->brand_title != '') {
			$this->response_type = 'parts';
		} elseif (count($this->parts) == 0) {
			$this->response_type = '404';
		} else {
			$this->response_type = 'catalogs';
		}
	}

	/**
	 *  add detail to cart
	 *
	 * @param $data
	 * @return array
	 */
	public function getPartData($data)
	{


		$this->query = $data['article'];
		$this->brand_title = $data['brand_title'];

		$this->init();
		$this->search();

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
	 * ѕолучение конфигурационных данных.
	 *
	 * @return array
	 */
	public function getConfigVars()
	{

		return array(
			'LOGIN'             => array(
				'title' => GetMessage('LOGIN'),
				'type'  => 'string',
			),
			'PASSWORD'          => array(
				'title' => GetMessage('PASSWORD'),
				'type'  => 'password',
			),
			'SEARCH_BY_CROSSES' => array(
				'title'   => GetMessage('SEARCH_BY_CROSSES'),
				'type'    => 'checkbox',
				'default' => false,
			),
			'SHOW_ANALOGS'      => array(
				'title'   => GetMessage('SHOW_ANALOGS'),
				'type'    => 'checkbox',
				'default' => true,
			),
			'ONLY_AVAILABLE'    => array(
				'title'   => GetMessage('ONLY_AVAILABLE'),
				'type'    => 'checkbox',
				'default' => false,
			),

		);
	}

}


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
 * doc: http://portal.linemedia.ru/company/personal/user/7/tasks/task/view/2556/
 * Интерфейс удалённого поставщика
 * Class FwheelRemoteSupplier
 */
class FwheelRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
	/**
	 * @var string
	 */
	public static $title = 'Fwheel';
	/**
	 * @var null
	 */
	private $brands = null;
	/**
	 * public - для вывода в настройках
	 * @var string
	 */
	public $url = 'http://trade.fwheel.com/portal/';

	/**
	 * Создадим объект
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Инициализация.
	 */
	public function init()
	{
		$this->browser->setBaseUrl($this->url);
	}


	/**
	 * Авторизация
	 */
	public function login($force_login = false)
	{
		/*
         * Загружаем уже сохраненную сессию
         */
		$session = $this->loadSession();

		if($session && !$force_login) {
			return true;
		}

		/*
		 * Если сессии нет, то проводим авторизацию и сохраняем сессию
		 */

		$urlLogin = "auth/login?kfreq_version=1&kfreq_resp_type=json&login=".$this->profile_options['LOGIN']."&pass=".$this->profile_options['PASSWORD'];

		$result = json_decode($this->browser->get($urlLogin), true);

		if($result['data']['sid'] && $result['data']['conditions']) {

			$this->saveSession($result['data']['sid']);

			if(is_array($result['data']['conditions'])) {
				foreach($result['data']['conditions'] as $cond) {
					if($cond['is_default'] == 1) {
						$this->saveDefaultCondition($cond['condition_id']);
					}
				}
			}

			return true;
		}

		/*
         * Если нам так и не удалось авторизоваться - кидаем ошибку
         */
		throw new Exception('Auth error or incorrect login/password', LM_AUTO_DEBUG_USER_ERROR);

	}

	/**
	 * Поиск
	 * @throws Exception
	 */
	public function search()
	{
		$this->login();

		$sid 		  = $this->loadSession();
		$condition_id = $this->loadDefaultCondition();
		$offer_type   = $this->profile_options['ONLY_IN_STOCK'] ? 0 : 1;
		$analogs 	  = $this->profile_options['USE_ANALOGS']   ? 1 : 0;

		if($this->brand_title) {
			$urlGetByCode = "goods/search_by_code_and_brand?kfreq_version=1&sid=$sid&condition_id=$condition_id&offer_type=$offer_type&analogs=$analogs&code={$this->query}&brand_name={$this->brand_title}";

		} else {
			$urlGetByCode = "goods/search_by_code?kfreq_version=1&sid=$sid&condition_id=$condition_id&offer_type=$offer_type&analogs=$analogs&code=".$this->query;

		}

		try{
			$response = $this->browser->get($urlGetByCode);
			$response = json_decode($response, true);

			/*
             * Если ответ не "ок" или есть код ошибки, то делаем авторизацию заново
             */
			if($response['result']['code'] != 0 || $response['result']['descr'] != 'OK') {
				$this->login(true);
				$response = $this->browser->get($urlGetByCode);
				$response = json_decode($response, true);
			}
		} catch (Exception $ex) {
			$query_info = $this->browser->getLastQueryInfo();

			throw new Exception('Last query info: <pre>' . print_r($query_info, true), LM_AUTO_DEBUG_USER_ERROR);
		}

		$response_parts = array();


		if (!empty($response['data']['goods'])) {
			$response_parts = $response['data']['goods'];
		} elseif($response['result']['code']) {
			throw new Exception("Error code: {$response['result']['code']}, error desc: {$response['result']['descr']}", LM_AUTO_DEBUG_USER_ERROR);
		}

		foreach ($response_parts as $group) {
			foreach($group as $key => $part) {

				$this->catalogs[$part['brand_name']] = array(
					'brand_title' => $part['brand_name'],
					'title'       => $part['prod_name'],
					'source'      => self::$title,
					'extra'       => array(
						'prod_id' => $part['prod_id'],
						'brand_id'=> $part['brand_id']
					)
				);

				if ($this->brand_title && strcasecmp($part['brand_name'], $this->brand_title) == 0 && strcasecmp($part['prod_code'], $this->query) == 0 ||
					!$this->brand_title && strcasecmp($part['prod_code'], $this->query) == 0) {
					$analog_type = 'N';
				} else {
					$analog_type = '0';
				}

				foreach ($part['offers'] as $offer) {
					$this->parts['analog_type_'.$analog_type][] = array(                                      // заполняем поля
						'id'                    => self::$title,
						'article'               => LinemediaAutoPartsHelper::clearArticle($part['prod_code']),
						'brand_title'           => $part['brand_name'],
						'title'                 => $part['prod_name'],
						'price'                 => $offer['price'],
						'weight'                => $part['weight'],
						'quantity'              => $offer['in_stock'],
						'delivery_time'         => intval($offer['deliv_time_descr']) * 24,
						'modified'           => $offer['up_date'],
						'data-source'           => self::$title,
						'extra'                 => array(
							'prod_id'   => $part['prod_id'],
							'brand_id'   => $part['brand_id'],
							'weight' => $part['weight'],
							'height'  => $part['height'],
							'width'  => $part['width'],
							'depth'  => $part['depth'],
							'imgs'  => $part['imgs'],
							'offer_id'  => $offer['offer_id'],
							'deliv_chance'  => $offer['deliv_chance'],
							'min_order'  => $offer['min_order'],
							'offer_name'  => $offer['offer_name'],
							'offer_type'  => $offer['offer_type'],
							'hash'   => md5($part['brand_name'].$part['prod_name'].$offer['price'].$offer['in_stock'].$part['deliv_time_max_descr'].$offer['up_date'].$part['prod_id'])
						)
					);
				}
			}
		}

        if (count($this->catalogs) == 1 || $this->brand_title != '') {
            $this->response_type = 'parts';
        } elseif (count($this->parts) == 0 && count($this->catalogs) == 0 ) {
            $this->response_type = '404';
        } else {
            $this->response_type = 'catalogs';
        }
	}

	/**
	 * Получить максимум информации о детали (а особенно цену) основываясь на том,
	 * что эта запчасть данного поставщика и пришла из поиска.
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function getPartData($data)
	{
		$md5_required_detail = $data['extra']['hash'];
		$this->init();
		$this->query = $data['article'];
		$this->brand_title = $data['brand_title'];
		$this->search();

		foreach ($this->parts as $group) {
			foreach($group as $key => $part) {
				$md5_current_detail = $part['extra']['hash'];
				if ($md5_current_detail == $md5_required_detail) {
					return $part;
				}
			}
		}

		return array();
	}

	/**
	 * Кроме ключа ничего не надо для доступа. Ну и плюс настраиваем, спрашивать ли аналоги у Берга
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
			'USE_ANALOGS' => array(
				'title' => GetMessage('USE_ANALOGS'),
				'type' => 'checkbox',
				'default' => false,
				'description' => GetMessage('USE_ANALOGS_DESCR'),
			),
			'ONLY_IN_STOCK' => array(
				'title' => GetMessage('ONLY_IN_STOCK'),
				'type' => 'checkbox',
				'default' => false,
				'description' => GetMessage('ONLY_IN_STOCK_DESCR'),
			),
		);
	}

	/**
	 * Сохранение и подгрузка cookie.
	 * @param $session
	 */
	protected function saveSession($session)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/';

		if (!file_exists($path)) {
			mkdir($path);
		}

		file_put_contents($path . 'lm_auto_remote_suppl_fwheel_session.txt', (string) $session);
	}

	protected function loadSession()
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_fwheel_session.txt';
		$hash = '';

		if (file_exists($file)) {
			$hash =  file_get_contents($file);
		}

		return $hash;

	}

	protected function saveDefaultCondition($session)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/';

		if (!file_exists($path)) {
			mkdir($path);
		}

		file_put_contents($path . 'lm_auto_remote_suppl_fwheel_default_cond.txt', (string) $session);
	}

	/**
	 * Загрузка полученных cookie.
	 * @return string
	 */
	protected function loadDefaultCondition()
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_fwheel_default_cond.txt';
		$hash = '';

		if (file_exists($file)) {
			$hash =  file_get_contents($file);
		}

		return $hash;

	}

}

<?php
/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Linemedia Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * Интерфейс удалённого поставщика
 * Class PartsibRemoteSupplier
 */
class PartsibRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'PartSib';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://partsib.ru'; //
    /**
     * @var string
     */
    protected $sessid = '';
    /**
     * @var int
     */
    protected $search_analogs = 0;
    /**
     * @var string
     */
    protected $sessFile = '';

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
		$this->search_analogs = '' != $this->profile_options['USE_ANALOGS'];
		$this->sessFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_partsib_session.txt';

		if (file_exists($this->sessFile)) {
			if (time() - filemtime($this->sessFile) > 8600) {
				unlink($this->sessFile);
			}
		}
	}


	/**
	 * Авторизация
	 */
	public function login()
	{
		if (file_exists($this->sessFile)) {
			$this->sessid = $this->loadSession();
		} else {
			$auth = $this->callPartsib("auth", array("username" => $this->profile_options['LOGIN'],
													 "password" => $this->profile_options['PASSWORD']));
			if (!$auth->success) {
				throw new Exception($auth->r);
			}

			$this->sessid = $auth->rows[0]->sessionId;

			$this->saveSession($this->sessid);
		}
	}


	/**
	 * Поиск
	 */
    public function search()
	{
		$catalogs = array();
		$parts =  array();

		$oCatalogs = $this->callPartsib(
			"searchByArticle",
			array(
				"PHPSESSID"  => $this->sessid,
				"article" 	 => $this->query,
				"alt"		 => $this->search_analogs
			)
		);


		if (!$oCatalogs) {
			$this->response_type = '404';
			return;
		}

		if(is_object($oCatalogs)) {
			if ($oCatalogs->success != 1) {
				$this->response_type = '404';
				return;
			}
		}

		if ($this->search_analogs) {
			foreach ($oCatalogs->rows as $key => $catalog) {
				/*
				 * Если включены аналоги и вернулся только 1 оригинальный каталог, то ищем его
				 */
				if ( strtolower(LinemediaAutoPartsHelper::clearArticle(strval($catalog->article))) == strtolower(LinemediaAutoPartsHelper::clearArticle(strval($this->query))) ) {
					$catalogs[$catalog->brand] = array(
						'article'       => LinemediaAutoPartsHelper::clearArticle(strval($this->query)),
						'brand_title'   => strval($catalog->brand),
						'title'         => strval($catalog->title),
					);
				}
				unset($catalog);
			}
		}


		/*
		 * Если были включены аналоги, но вернулось более 1 оригинального каталога, то у катлогов нет поля article и
		 * foreach что был выше ничего нам не даст.
		 */
		if (empty($catalogs)) {
			foreach ($oCatalogs->rows as $key => $catalog) {
					$catalogs[$catalog->brand] = array(
						'article'       => LinemediaAutoPartsHelper::clearArticle(strval($this->query)),
						'brand_title'   => strval($catalog->brand),
						'title'         => strval($catalog->title),
					);
				unset($catalog);
			}
		}


		
		$this->response_type = 'catalogs';
		$this->catalogs = $catalogs;

		if ($this->brand_title != '' && count($catalogs) > 1) {
			foreach ($oCatalogs->rows as $catalog) {
				if (strtoupper($catalog->brand) == strtoupper($this->brand_title)) {
					$items = $this->callPartsib(
						"searchByArticle",
						array(
							"PHPSESSID" => $this->sessid,
							"nomenclature_id" => $catalog->nomenclature_id,
							"alt"		 => $this->search_analogs
						)
					);					
					break;
				}
				unset($catalog);
			}
		}

		if ($this->brand_title != '' || count($catalogs) == 1) {

			if (!$items && $this->brand_title != '' && count($catalogs) > 1) {
				$this->response_type = '404';
				return;
			}

			if(is_object($items)) {
				if($items->success != 1) {
					$this->response_type = '404';
					return;
				}
			}

			if (count($catalogs) == 1) {
				$items = $oCatalogs;
			}

			foreach ($items->rows as $item) {
				if (strtolower(LinemediaAutoPartsHelper::clearArticle(strval($item->article))) == strtolower(LinemediaAutoPartsHelper::clearArticle(strval($this->query))) && $item->alt != 1) {
					$analog_type = 'N';//LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;
				} else {
					$analog_type = '0';//LinemediaAutoPart::ANALOG_GROUP_UNORIGINAL;
				}
				if (intval($item->quantity) > 0 && intval($item->price) > 0) {
					$parts['analog_type_' . $analog_type][] = array(
						'id'                => 'partsib',
						'article'           => LinemediaAutoPartsHelper::clearArticle(strval($item->article)),
						'brand_title'       => strval($item->brand),
						'title'             => strval($item->title),
						'price'             => floatval(str_replace(array(' ', ','), array('', '.') , (string)$item->price)),
						'quantity'          => intval($item->quantity) ?: 0,
						'delivery_time'     => (intval($item->delivery) ?: 0) * 24, // в часах
						'date_update'       => '',
						'data-source'       => self::$title,
						'extra'             => array('hash'=> md5(strval($item->brand) . strval($item->title) . $item->price . $item->article)),
						'currency'			=> $item->subst_supplier_ccy,
						'price-date'		=> $item->price_date,
						'wherestore' 		=> $item->wherestore
					);
				}
			}

			
			$this->response_type = 'parts';
			$this->parts = $parts;
		}
	}

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том,
     * что эта запчасть данного поставщика и пришла из поиска.
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
		$this->login();

		// выполнить в любом случае для логина и получения id user
		$this->search();

		/*
		* Найдём именно эту деталь
		*/
		foreach($this->parts as $group => $parts)
		{
			foreach($parts as $part)
			{
				if($part['extra']['hash'] == $hash)
					return $part;
			}
		}

		throw new Exception(self::$title.': Remote part not found');
	}

    /**
     * callPartsib
     * @param $funcName
     * @param $paramsHash
     * @return mixed
     */
    public function callPartsib($funcName, $paramsHash)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$paramsString = "";
		foreach ($paramsHash as $key => $val)
			$paramsString .= "&" . $key . "=" . $val;

		curl_setopt($ch, CURLOPT_URL,
			'https://partsib.ru/service.php?p=' . $funcName . $paramsString);

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11 Linemedia Autoexpert Parser');
		$data = curl_exec($ch);
		curl_close($ch);

		return json_decode($data);
	}

    /**
     * Сохранение и подгрузка cookie.
     * @param $session
     */
    protected function saveSession($session)
	{
		file_put_contents($this->sessFile, (string) $session);
	}

    /**
     * Загрузка полученных cookie.
     * @return string
     */
    protected function loadSession()
	{
		return file_get_contents($this->sessFile);
	}

    /**
     * Получить конфигурацию
     * @return array
     */
	public function getConfigVars()
	{
		return array(
			'LOGIN'    => array(
				'title' => GetMessage('LOGIN'),
				'type'  => 'string',
			),
			'PASSWORD' => array(
				'title' => GetMessage('PASSWORD'),
				'type'  => 'password',
			),
			'USE_ANALOGS'=>array(
				'title' => GetMessage('USE_ANALOGS'),
				'type'  => 'checkbox',
				'default' => false,
			)
		);
	}


}

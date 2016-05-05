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
 * doc: http://portal.linemedia.ru/company/personal/user/7/tasks/task/view/2556/
 * Class JapartsRemoteSupplier
 */
class JapartsRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'Japarts';
    /**
     * @var null
     */
    private $brands = null;
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://www.japarts.ru'; //

    /**
     * Искать ли аналоги?
     * Настраивается в настройках модуля
     * @var bool
     */
    protected $search_analogs = false;


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
        $this->browser->setReferer($this->url);
	}


    /**
     * Авторизация
     */
    public function login()
    {
        /*
         * Логин объединяется с поиском для ускорения загрузки страницы (один запрос вместо двух)
         */
    }

    /**
     * Поиск
     * @throws Exception
     */
    public function search()
    {
        $login = trim($this->profile_options['LOGIN']);
        $pass = $this->profile_options['PASSWORD'];
        $analog = $this->profile_options['USE_ANALOGS'] ? 1 : 0;

		$url = '?id=ws;action=search;login='.trim($login).';pass='.trim($pass).";detailnum=".trim($this->query).".;cross=".$analog.';';

		$response_parts = array();

		try {
			$page = $this->browser->get($url);
			$page = mb_convert_encoding($page, "UTF-8", 'CP1251');
		} catch (Exception $ex) {
			$query_info = $this->browser->getLastQueryInfo();

			throw new Exception($query_info, LM_AUTO_DEBUG_USER_ERROR);
		}
		
		
		// TODO: добавить json_last_error
		$response_parts = json_decode($page, true);
		if($response_parts[0]['error'])
			throw new Exception((string)$response_parts[0]['error']);
		

		if ($response_parts[0]['error']) {
			$errors = array(
				'NO ACTION SPECIFIED' => GetMessage('ERROR_NO_ACTION_SPECIFIED'),
                'LOGIN FAILED' => GetMessage('ERROR_LOGIN_FAILED'),
                'LOGIN NOT ACTIVATED' => GetMessage('ERROR_LOGIN_NOT_ACTIVATED'),
				'NO DETAILNUM SPECIFIED' => GetMessage('ERROR_NO_DETAILNUM_SPECIFIED'),
                'NO RESULTS FOUND' => GetMessage('NO RESULTS FOUND'),
                'MAKELOGO NOT EQUAL MAKENAME' => GetMessage('MAKELOGO NOT EQUAL MAKENAME'),
			);

			throw new Exception($errors[$response_parts[0]['error']], LM_AUTO_DEBUG_USER_ERROR);
		}

		foreach ( $response_parts as $i => $part) {
			$article = LinemediaAutoPartsHelper::clearArticle($part['detailnum']);

			$brandsCheck = $this->brand_title ? strtoupper($this->brand_title) == strtoupper($part['makename']) : 1;

			if ($brandsCheck) {
				$this->catalogs[$part['makename']] = array(
					'article'     => $part['detailnum'],
					'brand_title' => $part['makename'],
					'title'       => $part['detailname'],
					'source'      => self::$title,
					'extra'       => array('short_brand' => $part['makelogo'])
				);
			}

			if ($article == $this->query && $brandsCheck) {
				$key = 'N';//LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;
			} else {
				$key = '0';//LinemediaAutoPart::ANALOG_GROUP_UNORIGINAL;
			}

			if ($brandsCheck) {
				$this->parts['analog_type_'.$key][] = array(
					'id'                    => self::$title,
					'article'               => $article,
					'brand_title'           => $part['makename'],
					'title'                 => $part['detailname'],
					'price'                 => $part['pricerur'],
					'weight'                => '-',
					'quantity'              => (int) $part['quantity'],
					'delivery_time'         => $part['timegar'] * 24,
					'modified'           => '-',
					'data-source'           => self::$title,
					'extra'                 => array(
						'country'   => $part['country'],
						'supcode'   => $part['supcode'],
						'statistic' => $part['statistic'],
						'makelogo'  => $part['makelogo'],
						'search_article' => $this->query,
						'search_brand' => $this->brand_title
					),
				);
			}
		}
		/*
		 * Если нет бренда, то могут вернуться или каталоги или детали.
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
     * Получить максимум информации о детали (а особенно цену) основываясь на том,
     * что эта запчасть данного поставщика и пришла из поиска.
     * @param $data
     * @return array
     */
    public function getPartData($data)
    {
		$this->query = $data['extra']['search_article'];
		$this->brand_title = $data['extra']['search_brand'];

		$this->extra = $data['extra'];
		$this->init();
		$this->search();

        $md5hash = strtoupper($data['brand_title']).$data['article'].$data['extra']['country'].$data['extra']['supcode'];
		$md5_required_detail = md5($md5hash);

		foreach ($this->parts as $group => $parts) {
			foreach ($parts as $part) {
                $md5_current_detail = md5(strtoupper($part['brand_title']).$part['article'].$part['extra']['country'].$part['extra']['supcode'])    ;

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

            'USE_ANALOGS'=>array(
                'title' => GetMessage('JAPARTS_USE_ANALOGS'),
                'type'  => 'checkbox',
                'default' => false,
            )
        );
    }
}

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
 * Class LinemediaRemoteSupplier
 */
class LinemediaRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'Linemedia AutoExpert';
    /**
     * @var string
     */
    private static $path ='/bitrix/admin/linemedia.auto_search.php';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://auto.linemedia.ru'; //
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
        $url = $this->profile_options['URL'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'linemedia_URL');
        $this->browser->setBaseUrl($url);
//         $this->search_analogs = '' != COption::GetOptionString('linemedia.autoremotesuppliers', 'linemedia_USE_ANALOGS');
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
        $this->browser->resetSettings();
        $query  = urlencode($this->query);

        /*
         * У нас есть уточнение, т.е. это точно запрос не на каталоги
         */
        $params = array('q'=>$query);
        if ($this->brand_title != '') {
            $params['brand_title'] = urlencode($this->brand_title);
        }

		$url = self::$path.'?'.http_build_query($params, '', '&');

        $bu = 'http://'.$this->profile_options['URL'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'linemedia_URL');
		$this->browser->setBaseUrl($bu);
		
		
		
		/*
		* login pass
		*/
		$username = $this->profile_options['LOGIN'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'linemedia_LOGIN');
		$password = $this->profile_options['PASSWORD'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'linemedia_PASS');
		$this->browser->setParam(CURLOPT_USERPWD, $username . ":" . $password);
		
		
		try {
            $page = $this->browser->get($url);
        } catch (Exception $e) {
        	$query_info = $this->browser->getLastQueryInfo();
            if($query_info['http_code'] == 401)
                throw new Exception('Incorrect password for ' . $this->profile_options['URL'], LM_AUTO_DEBUG_USER_ERROR);
            throw new Exception ('Error loading page: ' . $e->GetMessage() . ' - ' . $page);
        }
		

        
        $data = json_decode($page, true);
        
        // Обработка ошибок парсинга ответа json.
        $error = json_last_error();
        $error_str = '';
        if (!empty($error)) {
            switch ($error) {
                case JSON_ERROR_DEPTH:
                    $error_str = 'maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error_str = 'underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error_str = 'unexpected control character found';
                    break;
                default:
                    $error_str = 'unknown response error';
                    break;
            }
            throw new Exception ('parse JSON. ' . $error_str);
        }
        
        
        
        
        
        /*
        * Если пришла ошибка
        */
        if($data['ERRORS'])
        {
        	if(is_array($data['ERRORS']))
        		$data['ERRORS'] = join("\n", $data['ERRORS']);
        	
	        throw new Exception ($data['ERRORS'], LM_AUTO_DEBUG_USER_ERROR);
        }
        

        /*
         * Если артикул в нескольких каталогах, то нам вернут предупреждение, 
         * по нему и будем ориентироваться, что нам вернули каталоги.
         */
		if (isset($data['catalogs'])) {
			$this->response_type = 'catalogs';
		} else if (isset($data['parts'])) {
			$this->response_type = 'parts';
		}
		$this->parts = array();
		$this->catalogs = array();
        
		if ($this->response_type == 'catalogs') {
            $this->catalogs = $data['catalogs'];
		} else {
            $n_parts = 0;
            foreach ($data['parts'] as $type=>$items) {
                foreach ($items as $part) {
                    ++$n_parts;
                    /*
                     * Стандартная процедура: если артикул равен искомому, то это оригинал, иначе это аналог
                     */
					if (LinemediaAutoPartsHelper::clearArticle($part['article']) == $this->query) {
						$this->catalogs[ $brand_title ] = array(
							'article'      => $part['article'],
							'brand_title'  => $part['brand_title'],
							'title'        => $part['title'],
                            'source'       => self::$title,
						);
					}
                    
                    $part['extra']['id'] 		= $part['id'];
                    $part['price'] 				= $part['price_src'];
                    $part['original_article'] 	= $part['article'];
                    $part['delivery_time'] 		= $part['delivery'];
                    
                    $part['id'] = 'linemedia';
                    
                    /*
                    * Хешом будет являться ID детали на удалённом сервере Атоэксперт
                    */
                    $part['hash'] = $part['extra']['id'];
                    
					$this->parts[ $type ][] = $part;
                }
            }
		}
		
        /*
         * Если нет бренда, то могут вернуться или каталоги или детали.
         */
        $this->catalogs = array_values($this->catalogs);
		if (empty($this->brand_title)) {
			$this->response_type = count($this->catalogs) > 1 ? 'catalogs' : 'parts';
		}
		if (count($this->catalogs) <= 1 && $n_parts == 0) {
			$this->response_type = '404';
		}
		return;

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

        // Выполнить в любом случае для логина и получения id user.
        $this->search();

        /*
         * Найдём именно эту деталь
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
     * Получение конфигурационных данных.
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'URL' => array(
                'title' => GetMessage('URL'),
                'type' => 'string',
            ),
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            )
        );
    }

}

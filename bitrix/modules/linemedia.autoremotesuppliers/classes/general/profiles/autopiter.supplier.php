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
 * http://service.autopiter.ru/price.asmx
 * Class AutopiterRemoteSupplier
 */
class AutopiterRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS = 'http://service.autopiter.ru/price.asmx?WSDL';
    const WSDL_TIMEOUT = 3;

    const SEARCH_CROSS_NO       = 0;
    const SEARCH_CROSS_YES      = 1;
    const SEARCH_CROSS_SUPER    = 2;
    /**
     * @var string
     */
    public static $title = 'Autopiter';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://www.autopiter.ru'; //
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
            // ВНИМАНИЕ!!! trace = true ОБЯЗАТЕЛЬНО! нужны заголовки ответа для парсинга cookie
            $this->soap = new SoapClient(self::WSDL_ADDRESS, array('trace' => true, 'soap_version' => SOAP_1_2));
        } catch(SoapFault $e) {
            throw new Exception('WSDL error: ' . $e->GetMessage());
        }

        /*
         * Set default timeout for soap request
         */
        ini_set("default_socket_timeout", $defaultTimeout);

        $this->loadCookies();

        // Авторизация.
        $this->login();
    }

    /**
     * Авторизация.
     * @param bool $skip
     * @throws Exception
     */
    public function login($skip = true)
    {
        $this->loadCookies();


        /*
         * Проверяем наличие куки авторизации, если ее нет, то авторизовываемся.
         */
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autopiter.txt')) {
            $cookies = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autopiter.txt'), true);

            if (empty($cookies['AuthCoocies'])) {
                $skip = false;
            }
        } else {
            $skip = false;
        }

        /*
         * Будем производить логин только если пришла ошибка авторизации, потому что мы храним куки для SOAP
         */
        if ($skip) {
            return;
        }

        /*
         * Логин - пароль
         */
        $login    = $this->profile_options['LOGIN'];
        $password = $this->profile_options['PASSWORD'];

        $param = array(
            'UserID' => $login,
            'Password' => $password,
            'Save' => true
        );
        $response = $this->soap->__soapCall('Authorization', array($param));

        if ($response->AuthorizationResult != true) {
            throw new Exception ('Incorrect password', LM_AUTO_DEBUG_USER_ERROR);
        }

        /*
         * Считаем и запишем куки
         */
        $headers_raw = $this->soap->__getLastResponseHeaders();

        $headers_raw = explode("\n", $headers_raw);
        $headers_raw = array_filter($headers_raw);

        $headers = array();
        foreach ($headers_raw as $header_str) {
            $header = explode(':', $header_str);
            $headers[$header[0]] = $header[1];
        }

        if (isset($headers['Set-Cookie'])) {
            // AuthCoocies=0102A6736B3D82ACCF08FEA623D4AA10C5CF08010631003000380032003000340000012F00FF; expires=Sun, 20-Jan-2013 10
            $cookies_str = $headers['Set-Cookie'];

            $cookies = array();
            $cookies_arr = explode("\n", $cookies_str);
            foreach ($cookies_arr as $cookie) {
                $cookie = explode(';', $cookie);
                $cookie_main = array_map('trim', explode('=', $cookie[0]));

                $cookies[$cookie_main[0]] = $cookie_main[1];
            }

            $this->saveCookies($cookies);
            $this->loadCookies();
        }
    }

    /**
     * Поиск
     * @throws Exception
     */
    public function search()
    {
        /*
         * У нас есть уточнение, т.е. это точно запрос не на каталоги
         */
        if ($this->brand_title != '') {
            $this->response_type = 'parts';

            if (empty($this->extra['cat_id'])) {

                /*
                 * Сначала запросим каталоги и попробуем найти нужный по brand_title
                 */
                try {
                    $param = array(
                        'ShortNumberDetail' => $this->query
                    );
                    $response = $this->soapCall('FindCatalog', $param);
                } catch (Exception $e) {
                    throw $e;
                }

                $catalogs = $response->FindCatalogResult->SearchedTheCatalog;
                if (!is_array($catalogs)) {
                    $catalogs = array($catalogs);
                }

                foreach ($catalogs as $catalog) {
                    if (strtoupper($catalog->Name) == strtoupper($this->brand_title)) {
                        $this->extra['cat_id'] = $catalog->id;
                    }
                }

                // ВНИМАНИЕ!!! на запрос деталей без указани ID бренда взвращается AuthorizationError.
                if (empty($this->extra['cat_id'])) {
                    $this->extra['cat_id'] = -1;
                    $this->query = null;
                    $this->brand_title = null;

                    $this->parts    = array();
                    $this->catalogs = array();

                    return;
                }
            }


            /*
             * "Получение прайс-листа по ID"
             */
            if ($this->extra['cat_id'] > 0 && $this->extra['det_id'] > 0) {
                try {
                    $param = array(
                        'ID'                => $this->extra['cat_id'],
                        'IdArticleDetail'   => $this->extra['det_id'],
                        'FormatCurrency'    => GetMessage('LM_AUTO_RSUPP_RUB'),
                        'SearchCross'       => self::SEARCH_CROSS_YES,
                    );

                    $response = $this->soapCall('GetPriceId', $param);

                    $response_parts = $response->GetPriceIdResult->BasePriceForClient;
                } catch (Exception $e) {
                    throw $e;
                }
            } else {
                /*
                 * По какой-то причине пришли не все параметры
                 * Например ссылка не содержит ничего кроме бренда
                 * тогда cat_id мы получили чуть выше изкаталогов
                 */

                try {
                    $param = array(
                        'ID'                => $this->extra['cat_id'],
                        'IdArticleDetail'   => null,
                        'FormatCurrency'    => GetMessage('LM_AUTO_RSUPP_RUB'),
                        'SearchCross'       => self::SEARCH_CROSS_YES,
                    );

                    $response = $this->soapCall('GetPriceId', $param);
                    $response_parts = $response->GetPriceIdResult->BasePriceForClient;
                } catch (Exception $e) {
                    throw $e;
                }

                // $response_parts = array();
                // $this->response_type = '404';
            }

        } else {
            $this->response_type = 'catalogs';

            /*
             * Сначала запросим каталоги
             */
            try {
                $param = array(
                    'ShortNumberDetail' => $this->query
                );

                $response = $this->soapCall('FindCatalog', $param);


            } catch (Exception $e) {
                throw $e;
            }


            /*
             * Каталогов нет, может детали есть?
             *
             * !is_array - если вернулся один каталог, надо показать детали из него, в таком случае ответ не массив6 а stdClass с каталогом
             */
            if (!is_array($response->FindCatalogResult->SearchedTheCatalog) || count($response->FindCatalogResult->SearchedTheCatalog) == 0) {

                $this->response_type = 'parts';
                try {
                    $param = array(
                        'ID'                => $response->FindCatalogResult->SearchedTheCatalog->id,
                        'IdArticleDetail'   => null,
                        'FormatCurrency'    => GetMessage('LM_AUTO_RSUPP_RUB'),
                        'SearchCross'       => self::SEARCH_CROSS_YES,
                    );

                    $response = $this->soapCall('GetPriceId', $param);
                } catch (Exception $e) {
                    throw $e;
                }

                $response_parts = $response->GetPriceIdResult->BasePriceForClient;//_d($response_parts);

                if (count($response_parts) == 0) {
                    $this->response_type = '404';
                }
            }
        }

        /*
         * Каталоги
         */
        if ($this->response_type == 'catalogs') {
            $catalogs = array();

            foreach ($response->FindCatalogResult->SearchedTheCatalog as $catalog) {
                $catalog = get_object_vars($catalog);
                if (!is_array($catalog) || count($catalog) == 0) {
                    continue;
                }
                $catalogs[] = array(
                    'article'       => $catalog['ShortNumber'],
                    'brand_title'   => $catalog['Name'],
                    'title'         => $catalog['NameDetail'],
                    'extra' => array(
                        'cat_id' => $catalog['id'],
                    ),
                );
            }

            if (count($catalogs) == 0) {
                $this->response_type = '404';
            }

            $this->catalogs = $catalogs;
        } elseif ($this->response_type == 'parts') {
            /*
             * Детали, а не каталоги
             */

            /*
             * Резервные каталоги.
             * Скорее всего каталоги мы уже искали.
             * Но если кто-то ещё вернул каталоги, надо показать, что для этой детали есть именно у этого поставщика.
             * Поэтому всё равно их пропишем.
             */
            $reserve_catalogs = array();


            /*
             * Если вернулась одна запчасть, она не в массиве.
             */
            if (!is_array($response_parts)) {
                $response_parts = array($response_parts);
            }

            foreach ($response_parts as $part) {
                $part = get_object_vars($part);

                /*
                 * Уникальная extra для детали.
                 */
                $extra = $this->extra;
                // $extra['hash'] = md5(json_encode($part)); --not working. reason unknown
                $extra['hash']  = strval($part['IdDetail']); // it works. IRL, this field appears to be unique for this supplier
                $extra['brand_title_original'] = strval($part['NameOfCatalog']);
                $price          = floatval(str_replace(array(' ', ','), array('', '.') , $part['SalePrice']));
                $brand_title    = strval($part['NameOfCatalog']);
                $article        = LinemediaAutoPartsHelper::clearArticle(strval($part['Number']));
                $title          = strval($part['NameRus']);
                $quantity       = intval($part['NumberOfAvailable']);
                $multiplication_factor = intval($part['MinNumberOfSales']);
                $delivery_time  = intval($part['NumberOfDaysSupply']);
                $date_update    = ''; //strval($part['dataprice']);
                // $extra['bra_id']= strval($part['bra_id']);
                $extra['det_id'] = strval($part['IdDetail']);
                //$extra['autopiterbt'] = strval($part['NameOfCatalog']);
                //if (!empty($response->FindCatalogResult->SearchedTheCatalog) && !is_array($response->FindCatalogResult->SearchedTheCatalog)) {
                $extra['cat_id'] = $part['ID'];//$response->FindCatalogResult->SearchedTheCatalog->id;
                //}


                // Тип запчастей: оригинальные или нет.
                if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                    $analog_type = 'N';
                } else {
                    $analog_type = 5;
                }


                /*
                 * Резервные каталоги
                 */
                if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                    $reserve_catalogs[$brand_title] = array(
                        'article' => $article,
                        'brand_title' => $brand_title,
                        'title' => $title,
                        'extra' => $extra
                    );
                    /*в extra есть det_id, который указывает конкретную деталь как ценовую позицию. соотв. если это уйдёт в экстре каталога,
                    то будет только одна железка от поставщика. что не очень хорошо.
                    */
                    unset($reserve_catalogs[ $brand_title ]['extra']['det_id']);
                }

                $parts['analog_type_' . $analog_type][] = array(
                    'id'                    => 'autopiter',
                    'article'               => $article,
                    'brand_title'           => $brand_title,
                    'title'                 => $title,
                    'price'                 => $price,
                    'quantity'              => $quantity,
                    'multiplication_factor' => $multiplication_factor,
                    'delivery_time'         => $delivery_time * 24, // в часах
                    'date_update'           => $date_update,
                    'data-source'           => self::$title,
                    'extra'                 => $extra,
                );
            }

            $this->parts = $parts;

            /*
             * Резервные каталоги
             */
            $this->catalogs = array_values($reserve_catalogs);
        }
    }

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска
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
        $this->login();

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
     * Сохранение и подгрузка cookie.
     * @param $cookies
     */
    protected function saveCookies($cookies)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autopiter.txt', json_encode($cookies));
    }

    /**
     * Загрузка полученных cookie.
     */
    protected function loadCookies()
    {
        try {
            $cookies = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autopiter.txt'), true);

            foreach ($cookies as $param => $val) {
                if (!is_object($this->soap)) {
                    $this->soap = new SoapClient(self::WSDL_ADDRESS, array('trace' => true, 'soap_version' => SOAP_1_2));
                }
                if (is_object($this->soap)) {
                    $this->soap->__setCookie($param, $val);
                }
            }
        } catch (Exception $e) {

        }
    }

    /**
     * SOAP-call
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

            /*
             * Необходимо авторизоваться
             */
            if (trim($e->GetMessage()) == 'AuthorizationError') {


	            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autopiter.txt')) {
		            unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autopiter.txt');
	            }

                try {
                    $this->login();//false);
                } catch (Exception $e) {
                    throw new Exception('Login ' . $e->GetMessage(), LM_AUTO_DEBUG_USER_ERROR);
                }

                /*
                 * Повторный запрос
                 */
                try {
                    $response = $this->soap->__soapCall($func, array($args));
                } catch (Exception $e) {
                    if (trim($e->GetMessage()) == 'AuthorizationError') {
                        throw new Exception('Second login ' . $e->GetMessage(), LM_AUTO_DEBUG_ERROR); // это значит логин работает, а поиск нет! что-то критичное
                    }
                    throw $e;
                }
            } else {
                throw $e;
            }
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

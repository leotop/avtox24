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
 * Интерфейс удалённого поставщика
 * 'http://ws.etsp.ru/
 * Class EtspRemoteSupplier
 */
class EtspTrueRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const WSDL_ADDRESS_SEARCH  = 'http://ws.etsp.ru/Search.svc?wsdl';
    const WSDL_ADDRESS_LOGON   = 'http://ws.etsp.ru/Security.svc?wsdl';
    const WSDL_ADDRESS_REMAINS = 'http://ws.etsp.ru/PartsRemains.svc?wsdl';

    const WSDL_TIMEOUT = 5;

    public static $title = 'Etsptrue';

    public $browser_login = false;

    public $url = 'http://www.etsp.ru/'; // public - для вывода в настройках

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
            $this->soap = new SoapClient(self::WSDL_ADDRESS_SEARCH, array('trace' => true, 'soap_version' => SOAP_1_1));
        } catch(SoapFault $e) {
            throw new Exception('WSDL error: ' . $e->GetMessage());
        }

        /*
         * Set default timeout for soap request
         */
        ini_set("default_socket_timeout", $defaultTimeout);
    }

    public function browserLogin() {

        $this->browser->setBaseUrl($this->url);
        $this->browser->setReferer($this->url);
        $login    =  $this->profile_options['LOGIN']; // COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_LOGIN');
        $password =  $this->profile_options['PASSWORD']; // COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_PASSWORD');

        $login_post = array(
            'login'  => $login,
            'password' => $password,
        );
        $page = $this->browser->post('/', $login_post);

        /*
         * Логин не удался!
         */
        if (strpos($page, 'href="/logout.aspx"') === false) {
            throw new Exception('Incorrect password?', LM_AUTO_DEBUG_USER_ERROR);
            return false;
        }

        $this->browser_login = true;
        return true;
    }

    public function isLoginInBrowser($page) {

        if (strpos($page, 'href="/logout.aspx"') === false) {
            return false;
        }

        return true;
    }

    /**
     * Авторизация
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
         * Загружаем уже сохраненную сессию
         */
        $session = $this->loadSession();

        try {
            /*
             * Если сессия загружена, то проверяем ее на валидность
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
             * Если сессии нет или она закончилась, то проводим авторизацию и сохраняем сессию
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
         * Если нам так и не удалось авторизоваться - кидаем ошибку
         */
        throw new Exception('Auth error or incorrect login/password', LM_AUTO_DEBUG_USER_ERROR);
    }

    /**
     * Поиск
     */
    public function search()
    {
        if(!defined('BX_ROOT')) define('BX_ROOT', '/bitrix/');

        /*
         * Подключаем все нужное, чтобы заработало кеширование.
         */
        global $APPLICATION;

        require_once($_SERVER["DOCUMENT_ROOT"].  '/bitrix/modules/main/lib/loader.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/application.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/httpapplication.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/diag/exceptionhandler.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/config/configuration.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/text/string.php');
        //require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/option.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/mysql/option.php');


        $application = \Bitrix\Main\HttpApplication::getInstance();
        $application->initializeBasicKernel();

        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/cache.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/cache_files.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/data/cache.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/data/cacheenginefiles.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/module.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/mysql/database.php');
        require_once($_SERVER["DOCUMENT_ROOT"].  '/bitrix/modules/linemedia.auto/classes/general/api_modifications.php');
        require_once($_SERVER["DOCUMENT_ROOT"].  '/bitrix/modules/linemedia.auto/classes/general/monitoring.php');

        require_once($_SERVER["DOCUMENT_ROOT"].  '/bitrix/modules/linemedia.auto/classes/general/api_driver.php');

        /*
         * Формируем каталоги на основании содержимого групп
         */
        $this->createCatalogs();

        $this->createParts();


        /*
         * Устанавливаем код ответа
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
     * Сформируем каталоги
     * @throws Exception
     */
    protected function getGroups()
    {
        /*
         * Задаем параметры
         */
        $param = array(
            'Text' => $this->query,
            'HashSession' => $this->loadSession()
        );

        /*
         * Делаем запрос простого поиска
         */
        try {
            $response = $this->soapCall('SearchBasic', $param);
        } catch (Exception $e) {
            throw $e;
        }

        /*
         * Из сформированного XML-ответа получаем объект
         */
        $response = simplexml_load_string($response->SearchBasicResult);

        //Лимит для вашей учётной записи - x просмотров в течении дня!
        if($response->error_message)
            throw new Exception($response->error_message, LM_AUTO_DEBUG_USER_ERROR);

        /*
         * Получаем массив вернувшихся групп, чтобы в дальнейшем правильно пронумеровать повторяющиеся группы
         */
        $groups = array();

        /*
         * Пробегаем по вернувшимся группам и ...
         */
        if(!empty($response->part->code)) { // одна деталь
            $response->part = array($response->part);
        }
        foreach ($response->part as $group) {


            /*
             * Преобразуем в массив, т.к. к нам приходит объект
             */
            $group = (array) $group;

            /*
             * Не пропускаем пустые каталоги, т.к. они нужны для отправки в api аналогов и групп
             */
            if (empty($group['is_sklad']) && empty($group['is_shops']) && empty($group['is_shipment'])  && empty($group['is_outside'])) {
                //continue;
            }

            /*
             * Формируем каталоги
             */
            $groups[] = array(
                'article'       => $group['code'],
                'brand_title'   => $group['group']?:'-',
                'title'         => $group['name'],
                'extra' => array(
                    'code' => $group['code'],
                    'note' => $group['note'] ?: '',
                    'unique_number'=> $group['unique_number'] ?: '',
                    'omega_number' => $group['omega_number'] ?: '',
                    'skuba_number' => $group['skuba_number'] ?: '',
                    'code_image_part' => $group['code_image_part'] ?: '',
                    'is_part_attendant' => $group['is_part_attendant'] ?: '',
                    'is_sklad' => $group['is_sklad'] ?: '',
                    'is_shops' => $group['is_shops'] ?: '',
                    'is_shipment' => $group['is_shipment'] ?: '',
                    'is_outside' => $group['is_outside'] ?: '',
                    //'code_image_part'=> $group['code_image_part']
                ),
            );

            unset($group);
        }

        return $groups;
    }

    /**
     * Получим детали по информации о каталоге $data
     * @param $data
     * @return bool
     * @throws Exception
     */
    protected function createCatalogs()
    {
        $searchArticle = LinemediaAutoPartsHelper::clearArticle($this->query);

        $cache = new CPHPCache();
        $cache_time = 60*60*24*365*100;
        $cache_id = $searchArticle . self::$title;

        if ($cache->InitCache($cache_time, $cache_id, '/lm_auto/remote_suppliers/'.self::$title.'/')) {
            $cached = $cache->GetVars();
            $this->catalogs = $cached['data'];

            return true;
        }

        /*
        * Получаем все группы
        */
        $groups = $this->getGroups();

        foreach($groups as $groupKey => $group) {

            $groupsParts = $this->getGroupPartsByBrowser($group);

            foreach($groupsParts as $partKey => $groupPart) {

                /*
                 *  Является ли искомый артикул частью найденного?
                 * + он должен быть в самом начале
                 */
                if(strpos($groupPart['article'], $searchArticle) === 0) {
                    $group['brand_title'] = $groupPart['brand_title'];
                    $group['article'] = $groupPart['article'];

                    $this->catalogs[$group['brand_title']] = $group;

                    unset($groupsParts[$partKey]);

                    $this->saveGroupsAndCrossesApi(
                        $groupPart['article'],
                        $groupPart['brand_title'],
                        array(
                            array(
                                'code'  => $group['extra']['code'],
                                'title' => $group['title'],
                                'note'  => $group['extra']['note'],
                                'brand_title' => $group['brand_title']
                            )
                        ),
                        $groupsParts
                    );

                    break;
                }
            }
        }

        $cache->StartDataCache();
        $cached_data['data'] = $this->catalogs;
        $cache->EndDataCache($cached_data);

    }

    /**
     * Получим детали по информации о каталоге $data
     * @param $data
     * @return bool
     * @throws Exception
     */
    protected function createParts()
    {
        $this->login();

        if(count($this->catalogs) == 1) {
            $catalog = array_shift(array_slice($this->catalogs, 0, 1));
            $this->parts = $this->getGroupParts($catalog);
            return;
        }

        $searchBrand = strtoupper($this->brand_title);

        if(!empty($searchBrand)) {
            foreach($this->catalogs as $catalog) {
                if($searchBrand && $searchBrand == $catalog['brand_title']) {
                    $this->parts = $this->getGroupParts($catalog);
                    return;
                }
            }
        }
    }

    protected function getGroupPartsByBrowser($group)
    {
        $parts = array();

        if($this->browser_login == false) {
            if(!$this->browserLogin()) {
                throw new Exception('Can\'t login', LM_AUTO_DEBUG_USER_ERROR);
            }
        }

        $page = $this->browser->get('/search.aspx?text='.$this->query.'&my_number='.$group['extra']['code']);

        $document = phpQuery::newDocument($page);

        $trs_obj = $document->find('.storage-group-1');

        /*
         *  Пробегаем по всем tr, tr == запчасть
         */
        foreach ($trs_obj as $i => $tr_obj) {

            $part_data = array();

            /*
             * Проегаем по всех td в tr запчасти
             */
            $tds_obj = pq($tr_obj)->find('td');

            foreach ($tds_obj as $y => $td_obj) {

                /*
                 *  Получение цены
                 */
                /*
                if($y == 7) {
                    $part_data[$y]= pq($td_obj)->find('span')->html();
                    continue;
                }
                */

                $part_data[$y]= pq($td_obj)->html();
            }

            $article = LinemediaAutoPartsHelper::clearArticle(strval($part_data[1]));
            $brand_title = strtoupper(strval($part_data[0]));
            /*
            $price = intval($part_data[7]);
            $quantity = filter_var($part_data[2], FILTER_SANITIZE_NUMBER_INT);
            $delivety_time = 0;
            $status = $part_data[8];
            $weight = (float)$part_data[9];

            if ($article == $this->query) {
                $analog_type = 'N';
            } else {
                $analog_type = '0';
            }
            */
            $parts[] = array(
                'article'           => $article,
                'brand_title'       => $brand_title
            );
        }

        return $parts;
    }

    protected function getGroupParts($group)
    {
        $soap = new SoapClient(self::WSDL_ADDRESS_REMAINS, array('trace' => true, 'soap_version' => SOAP_1_1));

        $this->login();
        /*
         * Загружаем сессию
         */
        $session = $this->loadSession();

        /*
         * Задаем параметры запроса
         */
        $params = array(
            'Code' => (string) $group['extra']['code'],
            //Признак показа остатков розничной сети
            'ShowRetailRemains' => 1,
            //Признак показа товаров под заказ
            'ShowOutsideRemains' => 1,
            'ShowPriceByQuantity' => 0,
            'HashSession' => (string) $session,
        );

        /*
         * Делаем запрос на получение остатков для данного каталога
         */
        try {
            $response = $soap->__soapCall('GetPartsRemainsByCode2', array($params));
        } catch (Exception $e) {
            throw $e;
        }

        /*
         * Из сформированного XML-ответа получаем объект
         */
        $response_parts =  (array) simplexml_load_string($response->GetPartsRemainsByCode2Result);

        //Лимит для вашей учётной записи - x просмотров в течении дня!
        if($response_parts['error_message'])
            throw new Exception($response_parts['error_message'], LM_AUTO_DEBUG_USER_ERROR);

        /*
         * Получаем остатки на складах
         */
        $response_parts_sklad = $response_parts['sklad_remains'];

        /*
         * Если только 1 деталь
         */
        if (is_object($response_parts_sklad)) {
            $response_part = (array) $response_parts_sklad;
            $response_parts_sklad = array();
            $response_parts_sklad[0] = $response_part;
        }

        /*
         * Формируем массив с деталями
         */
        $partsSklad = $this->getPartsFromResponse($response_parts_sklad, $group);

        /*
         * Получаем остатки товаров под заказ
         */
        $response_parts_outside = $response_parts['outside_remains'];

        /*
         * Если только 1 деталь
         */
        if (is_object($response_parts_outside)) {
            $response_part = (array) $response_parts_outside;
            $response_parts_outside = array();
            $response_parts_outside[10000] = $response_part;
        }

        /*
         * Формируем массив с деталями
         */
        $partsOutside = $this->getPartsFromResponse($response_parts_outside, $group);

        return array_merge_recursive($partsSklad, $partsOutside);
    }

    /**
     * Формируем запчасти из пришедшего ответа
     *
     *@param $response_parts
     * @param array $group
     * @return bool
     */
    protected function getPartsFromResponse($response_parts, $group = array())
    {
        $parts = array();

        $response_parts = (array) $response_parts;
        $response_parts = array_shift($response_parts);
        $response_parts = (array) $response_parts;
        $response_parts = (array) $response_parts['item'];


        /*
         * Если 1 деталь все таки пришли
         */
        if (!empty($response_parts['id_goods_unit']) && !empty($response_parts['goods_code'])) {
            $response_parts = array(0 => $response_parts);
        }
        foreach ($response_parts as $part) {

            $part = (array) $part;

            $article = LinemediaAutoPartsHelper::clearArticle(strval($part['manufacturer_number']));

            // Тип запчастей: оригинальные или нет.
            if ($article == $this->query) {
                $analog_type = 'N';
            } else {
                $analog_type = '0';
            }

            /*
             * Выделяем к-во
             */
            $quantity = str_replace('>', '', $part['quantity']);
            $quantity = str_replace('<', '', $quantity);

            $parts['analog_type_' . $analog_type][] = array(
                'id'                => 'etspnew',
                'article'           => LinemediaAutoPartsHelper::clearArticle(strval($part['manufacturer_number'])),
                'brand_title'       => strtoupper(strval($part['manufacturer_name'])),
                'title'             => !is_object($part['goods_comment']) && !empty($part['goods_comment']) ? strval($group['title']) . ', ' . strval($part['goods_comment']) : strval($group['title']) . ',
				' . strval($group['extra']['note']),
                'price'             => intval($part['price']),
                'quantity'          => (int)$quantity,
                'delivery_time'     => $part['delivery_time'] * 24, // в часах
                'date_update'       => '',
                'weight' 			=> (float)$part['weight'],
                'data-source'       => self::$title,
                'extra'             => array(
                    'id_goods_unit' => strval($part['id_goods_unit']),
                    //'goods_comment' => strval($part['goods_comment']),
                    'storage_id'	 => strval($part['storage_id']),
                    'storage_name' => strval($part['storage_name']),
                    'storage_position' => strval($part['storage_position']),
                    'remains_status_id' => strval($part['remains_status_id']),
                    'remains_status_name' => strval($part['remains_status_name']),
                    'weight' => (float)str_replace(',', '.', $part['weight']),
                    //'ordered' => strval($part['ordered']),
                    //'quantity_cart' => strval($part['quantity_cart']),
                    'code' => $group['extra']['code'],
                    'title' => strval($group['title']),
                    'hash' => md5 ($part['quantity'].$part['delivery_time'].$part['manufacturer_name'].$part['manufacturer_number'].$part['weight'])
                ),
            );
        }

        return $parts;
    }

    /**
     * Получаем информацию о детали (например при добавлении в корзину)
     * @param $data
     * @return array
     * @throws Exception
     */
    public function getPartData($data)
    {
        /*
         * Инициализируемся
         */
        $this->init();

        /*
         * Авторизуемся
         */
        $this->login();


        $parts = $this->getGroupParts($data);

        foreach ($parts as $group => $parts) {
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
     * Сохранение и подгрузка cookie.
     * @param $session
     */
    protected function saveSession($session)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/';

        if (!file_exists($path)) {
            mkdir($path);
        }

        file_put_contents($path . 'lm_auto_remote_suppl_etsptrue_session.txt', (string) $session);
    }

    /**
     * Загрузка полученных cookie.
     * @return string
     */
    protected function loadSession()
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_etsptrue_session.txt';
        $hash = '';

        if (file_exists($file)) {
            $hash =  file_get_contents($file);
        }

        return $hash;

    }

    public function saveGroupsAndCrossesApi($article, $brand_title, $group, $crosses) {

        $request = array(
            'action'      => 'saveGroupsAndCrosses',
            'article'     => $article,
            'brand_title' => $brand_title,
            'group'      => $group,
            'crosses'     => $crosses
        );

        try {
            $api = new LinemediaAutoApiDriver();
            $api->query('etspActions', $request);
        } catch (Exception $e) {

        }

        return true;
    }

    /**
     * Получение конфига.
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


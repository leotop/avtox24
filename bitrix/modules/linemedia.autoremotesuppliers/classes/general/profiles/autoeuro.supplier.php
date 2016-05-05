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
 * Class AutoeuroRemoteSupplier
 */
class AutoeuroRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'Autoeuro';
    /**
     * @var null
     */
    private $brands = null;
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://online.autoeuro.ru';
    protected $type = 'catalogs';

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
    public function login()
    {

    }

    public function sendRequest($command) {
        $auth = array(
            'client_name' => $this->profile_options['LOGIN'],
            'client_pwd' => $this->profile_options['PASSWORD']
        );

        $data = array(
            'command' => $command,
            'auth' => $auth
        );

        $data = array('postdata' => base64_encode(serialize($data)));

        try {
            $response = $this->browser->post('/ae_server/srv_main.php', $data);
            $response = unserialize(base64_decode($response));
        } catch (Exception $ex) {
            $query_info = $this->browser->getLastQueryInfo();

            throw new Exception('Last query info: <pre>' . print_r($query_info, true), LM_AUTO_DEBUG_USER_ERROR);
        }

        return $response;
    }
    /**
     * Поиск
     * @throws Exception
     */
    public function search()
    {


        if($this->brand_title) {
            $command = array(
                'proc_id' => 'Get_Element_Details',
                'parm' => array(
                    $this->brand_title,
                    $this->query,
                    1
                )
            );

            $this->type = 'parts';
        } else {
            $command = array(
                'proc_id' => 'Search_By_Code',
                'parm' => array(
                    $this->query,
                    1
                )
            );
        }

        $response = $this->sendRequest($command);


        if($this->type == 'catalogs') {
            foreach ($response as $catalog) {
                $part['name'] = iconv ("CP1251" , "UTF-8" , $catalog['name']);

                $this->catalogs[$catalog['maker']] = array(
                    'brand_title' => $catalog['maker'],
                    'title'       => $catalog['name'],
                    'source'      => self::$title,
                );

            }
        }

        if(count($this->catalogs) == 1) {
            $this->type = 'parts';

            $catalog = array_pop($this->catalogs);
            $brand_title = $catalog['brand_title'];
            $command = array(
                'proc_id' => 'Get_Element_Details',
                'parm' => array(
                    $brand_title,
                    $this->query,
                    1
                )
  
            );
			
            $response = $this->sendRequest($command);
        }


        if($this->type == 'parts') {
            foreach ($response as $part) {

                $article = LinemediaAutoPartsHelper::clearArticle($part['code']);

                if ($this->brand_title && strcasecmp($part['maker'], $this->brand_title) == 0 && strcasecmp($article, $this->query) == 0 ||
                    !$this->brand_title && strcasecmp($article, $this->query) == 0) {
                    $analog_type = 'N';
                } else {
                    $analog_type = '0';
                }
			  
                $part['name'] = iconv ("CP1251" , "UTF-8" , $part['name']);

                $this->parts['analog_type_'.$analog_type][] = array(                                      // заполняем поля
                    'id'                    => 'AutoEuro',
                    'article'               => $article,
                    'brand_title'           => $part['maker'],
                    'title'                 => $part['name'],
                    'price'                 => $part['price'],
                    'quantity'              => $part['amount'],
                    'delivery_time'         => intval($part['order_time']) * 24,
                    'data-source'           => self::$title,
                    'extra'                 => array(
                        'hash' => md5($article.$part['maker'].$part['name'].$part['price'].$part['amount'])
                    )
                );
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

        file_put_contents($path . 'lm_auto_remote_suppl_autoeuro_session.txt', (string) $session);
    }

    protected function loadSession()
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autoeuro_session.txt';
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

        file_put_contents($path . 'lm_auto_remote_suppl_autoeuro_default_cond.txt', (string) $session);
    }

    /**
     * Загрузка полученных cookie.
     * @return string
     */
    protected function loadDefaultCondition()
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_remote_suppl_autoeuro_default_cond.txt';
        $hash = '';

        if (file_exists($file)) {
            $hash =  file_get_contents($file);
        }

        return $hash;

    }

}

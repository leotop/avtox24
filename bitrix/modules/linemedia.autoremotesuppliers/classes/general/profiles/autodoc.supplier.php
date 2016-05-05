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
 * Class AutodocRemoteSupplier
 */
class AutodocRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var
     */
    public static $access;

    const CROSS_TYPE_OFFICIAL       = 3;
    const CROSS_TYPE_NO_OFFICIAL    = 4;

    /**
     * @var string
     */
    public static $title = 'Autodoc';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://www.autodoc.ru'; //

    /**
     * Искать ли аналоги?
     * Настраивается в настройках модуля
     * @var bool
     */
    protected $search_analogs = false;
    /**
     * Ограничение на кол-во аналогов(0 -- без ограничения)
     * @var int
     */
    protected $analogs_limit = 0;
    /**
     * Ключи для создания хеша.
     * Кеш для аналогов из всех данных строить нельзя,
     * т.к. повторный поиск аналогов дает другие поля.
     * @var array
     */
    protected static $hash_keys = array(
        'id_man',
        'man_name',
        'part_art',
        'price_cl_out',
        //'cross_type',
        'qty',
        'type_price',
        'id_price',
        'dir_name',
        'id_dir_parent',
        'date_corr',
        'price_adoc',
        'price_adoc_out',
    );


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

        $this->search_analogs = '' !=  $this->profile_options['USE_ANALOGS'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_USE_ANALOGS');
        self::$access = $this->profile_options['ACCESS'] + 1;
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
        $query = urlencode($this->query);

        $this->analogs_limit =  $this->profile_options['ANALOGS_LIMIT']; // COption::GetOptionInt('linemedia.autoremotesuppliers', 'autodoc_ANALOGS_LIMIT', 0);

        /*
         * У нас есть уточнение, т.е. это точно запрос не на каталоги
         */
         if ($this->brand_title != '' || $this->extra['atd_bid'] > 0) {

            if (!empty($this->extra['atd_bid'])) {
                $autodoc_brand_id = urlencode($this->extra['atd_bid']);
            } else {
                $autodoc_brand_id = urlencode($this->extra['autodoc_brand_id']);
            }

            // $this->brand_title = str_replace(' ', '~', $this->brand_title);

           // $access = (isset($this->profile_options['ACCESS'])) ? (self::$ACCESS[$this->profile_options['ACCESS']]) : (self::$ACCESS[GetMessage('ACCESS_LEVEL_PROF')]);

            if ($autodoc_brand_id) {
                $url = "/Web/price/art/$query/manufacturer$autodoc_brand_id/$this->brand_title?analog=on&access=".self::$access;

            } else {
                $url = "/Web/price/art/$query";
            }
        } else {
            // Уточнения нет, просто ищем
            $url = '/Price/Index?analog=False&Article=' . $query;
        }

        /*
         * 19.12.12 Автодок перестал редиректить при логине
         */
        try {
            $page = $this->browser->get($url);
        } catch (Exception $e) {
            throw $e;
        }


        if (strpos($page, '<a href="/Account/LogOut') === false) {
            /*
             * Логин УЖЕ НЕ объединяется с поиском для ускорения загрузки страницы (один запрос вместо двух)
             */
            $login    =  $this->profile_options['LOGIN']; // COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_LOGIN');
            $password =  $this->profile_options['PASSWORD']; // COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_PASSWORD');

            $login_post = array(
                'returnUrl' => $url, // url поиска передаётся в процедуре логина
                'UserName'  => $login,
                'RememberMe' => 'true',
                'Password' => $password,
            );
            $page = $this->browser->post('/Account/LogOn', $login_post);


            /*
             * Логин не удался!
             */
            if (strpos($page, 'href="/Account/LogOut') === false) {
                throw new Exception('Incorrect password?', LM_AUTO_DEBUG_USER_ERROR);
                return;
            }

            $page = $this->browser->get($url);
        }


        $document = phpQuery::newDocument($page);

        $this->response_type = strpos($page, 'gridMans') !== false ? 'catalogs' : 'parts';

        /*
         * Каталоги
         */
        if ($this->response_type == 'catalogs') {
            /*
             * Найдём все ссылки
             */
            $links_obj = $document->find('#gridMans a');
            $hrefs = array();
            foreach ($links_obj as $link_obj) {
                $href = pq($link_obj)->attr('href');
                $hrefs[$href] = pq($link_obj)->html();
            }

            /*
             * Распарсим ссылки и соберём из них массив каталогов
             */
            $catalogs = array();
            $pattern = '|/Web/price/art/(.+?)/manufacturer(.+?)/(.+?)\?analog=on|i';
            foreach ($hrefs as $href => $title) {
                preg_match($pattern, $href, $matches);

                $article            = $matches[1];
                $autodoc_brand_id   = $matches[2];
                $brand_title        = $matches[3];

                $catalogs []= array(
                    'article' => $article,
                    'brand_title' => $brand_title,
                    'extra' => array(
                        'atd_bid' => $autodoc_brand_id,
                    ),
                );
            }
            $this->catalogs = $catalogs;

            /*
             * Каталоги не нашлись, значит и запчастей нет, помтому что мы видим сразу всю html страницу
             */
        }


        if ($this->response_type == 'parts') {

            /*
             * Удаляем все таблицы, не нашего бренда, если бренд задан
             * (например, таблица "Аналоги на центральном складе")
             */
            while($this->brand_title && $document->find('a.m-lightbox:first')->html() && strcasecmp($this->brand_title, $document->find('a.m-lightbox:first')->html()) != 0) {
                $document->find('a.m-lightbox:first')->parent()->parent()->parent()->parent()->parent()->parent()->parent()->parent()->remove();
            }

            /*
             * Детали, а не каталоги
             * Найдём tr
             */

            /*
             * Название бренда искомой детали
             */
            $detail_obj = $document->find('a.m-lightbox:first');
            $brand_title = (!empty($this->brand_title)) ? ($this->brand_title) : (trim($detail_obj->html()));

            /*
             * ID бренда в автодоке
             */
            $autodoc_brand_id = parse_url($detail_obj->attr('href'), PHP_URL_QUERY);
            $autodoc_brand_id = explode('=', $autodoc_brand_id);
            $autodoc_brand_id = (int) $autodoc_brand_id[1];


            /*
             * ID пользователя
             */
            preg_match("|var uID = '(.+?)';|i", $page, $matches);
            $autodoc_user_id = (int) $matches[1];
            $this->autodoc_user_id = $autodoc_user_id;

            /*
             * Описание искомой детали
             */
            $descr_obj = $document->find('h1.ContentHeader');
            $ex_title = trim($descr_obj->html());
            $cur_title = explode(' – ',$ex_title);
            $title = $cur_title[1];

            /*
             * РЕЗЕРВНЫЕ КАТАЛОГИ
             * Скорее всего каталоги мы уже искали
             * Но если кто-то ещё вернул каталоги, надо показать, что для этой детали
             * есть именно у этого поставщика,
             * поэтому всё равно их пропишем
             */
            $reserve_catalogs = array();

            /*
             * Доступные искомые артикулы и проч.
             */
            $trs_obj = $document->find('#gridDetails tr');
            $parts = array();
            foreach ($trs_obj as $i => $tr_obj) {
                if ($i == 0) {
                    continue; // пропуск заголовоков
                }

                $part_data = array();

                $tds_obj = pq($tr_obj)->find('td');
                foreach ($tds_obj as $y => $td_obj) {
                    $part_data []= pq($td_obj)->html();
                }

                $price = floatval(str_replace(array(' ', ','), array('', '.') , $part_data[0]));

                /*
                 * Уникальная extra для детали
                 */
                $extra = $this->extra;
                $extra['bid'] = $autodoc_brand_id;

                $itempart = array(
                    'id'                => 'autodoc',
                    'article'           => LinemediaAutoPartsHelper::clearArticle($this->query),
                    'brand_title'       => strtoupper($brand_title),
                    'title'             => $title,
                    'price'             => $price,
                    'quantity'          => $part_data[2],
                    'delivery_time'     => intval(strip_tags($part_data[3])) * 24, // в часах
                    'date_update'       => strval($part_data[4]),
                    'data-source'       => self::$title,
                );

                $extra['hash'] = md5(json_encode($itempart));
				$extra['brand_title_original'] = strtoupper($brand_title);

                $itempart['extra'] = $extra;

                $parts['analog_type_N'] []= $itempart;


                $reserve_catalogs[$brand_title] = array(
                    'article' => $this->query,
                    'brand_title' => $brand_title,
                    'title' => $title,
                    'extra' => array(
                        'atd_bid' => $autodoc_brand_id,
                    ),
                );

            }


            /*
             * Доступные аналоги
             */
            if ($this->search_analogs) {
                try {
                    $analogs = $this->getAnalogs($this->query, $autodoc_brand_id, $autodoc_user_id);
                    $parts = array_merge_recursive($parts, $analogs);
                } catch (Exception $e) {

                }
            }

            $this->parts = $parts;

            /*
             * Резервные каталоги
             */
            $this->catalogs = $reserve_catalogs;

        }
    }

    /**
     * Получение аналогов.
     * PricesServices/ItemsByCrossLine(37544,442,218629,0,4,194_85_131_106)?_=1353038334256
     * @param $article
     * @param $autodoc_brand_id
     * @param $autodoc_user_id
     * @return array
     */
    public function getAnalogs($article, $autodoc_brand_id, $autodoc_user_id)
    {
        /*
         * Соберём варианты аналогов
         */
        //$access = (isset($this->profile_options['ACCESS'])) ? (self::$ACCESS[$this->profile_options['ACCESS']]) : (self::$ACCESS[GetMessage('ACCESS_LEVEL_PROF')]); // COption::GetOptionString('linemedia.autoremotesuppliers', 'autodoc_ACCESS', self::ACCESS_1);

        $url = "/PricesServices/CrossLines?manID=$autodoc_brand_id&art=$article&userID=$autodoc_user_id&analog=True&access=".self::$access;
        // $url = "/PricesServices/CrossLines?manID=$autodoc_brand_id&art=$article&userID=$autodoc_user_id&analog=True&access=".$access;

        $page = $this->browser->get($url);
        $data = json_decode($page, true);

        LinemediaAutoDebug::add('Autodoc main analogs request returned', print_r($data, true), LM_AUTO_DEBUG_WARNING);

        $analogs_variants = array();
        foreach ($data['Items'] as $item) {
            $analogs_variants []= array(
                'atd_bid' => $item['id_man'],
                'brand_title' => $item['man_name'],
                'article' => $item['part_art'],
                'title' => $item['part_name'],
                'cross_type' => $item['cross_type'],
                'autodoc_user_id' => $autodoc_user_id,
            );
        }


        /*
         * Выберем запчасти
         */
        $analogs = array();
        foreach ($analogs_variants as $variant) {
            $article = $variant['article'];
            $autodoc_brand_id = $variant['atd_bid'];
            $autodoc_user_id = $variant['autodoc_user_id'];
            $step = 0;
            $cross_type = $variant['cross_type'];

            $analogs = array_merge_recursive($analogs, $this->getAnalogVariant($article, $autodoc_brand_id, $autodoc_user_id, $step, $cross_type));
            if ($this->analogs_limit > 0 && count($analogs[ 'analog_type_0' ]) >= $this->analogs_limit) {
                break;
            }
        }

        return $analogs;
    }

    /**
     * Получение различных типов аналогов.
     * @param $article
     * @param $autodoc_brand_id
     * @param $autodoc_user_id
     * @param $step
     * @param $cross_type
     * @return array
     */
    private function getAnalogVariant($article, $autodoc_brand_id, $autodoc_user_id, $step, $cross_type)
    {
        $ip = '1'; // str_replace('.', '_', $_SERVER['REMOTE_ADDR']);

        $url = "/PricesServices/ItemsByCrossLine($article,$autodoc_brand_id,$autodoc_user_id,$step,$cross_type,$ip)";
        $page = $this->browser->get($url);
        $data = json_decode($page, true);

        LinemediaAutoDebug::add('Autodoc additional analogs request for ['.$article.'] returned', print_r($data, true), LM_AUTO_DEBUG_WARNING);

        $analogs = array();

        /*
         * Все аналоги
         */
        foreach ($data['Items'] as $part) {
            $extra = $this->extra;

            //$extra['hash'] = md5(json_encode($part));
            //$extra['hash'] = md5(json_encode(array_intersect_key($part, array_combine(self::$hash_keys, self::$hash_keys))));

            $extra['atd_bid'] = $autodoc_brand_id;
            $extra['bid'] = $autodoc_brand_id;
            $extra['analog'] = 1;
            $extra['autodocbt']= $part['man_name'];

            switch ($cross_type) {
                case (self::CROSS_TYPE_OFFICIAL): # JLM20802   Официальные замены выделены синим цветом.
                    $analog_type = 0; //  3\4
                    break;
                case (self::CROSS_TYPE_NO_OFFICIAL):
                    $analog_type = 0; //  3\4
                    break;
            }

            $itempart = array(
                'id'                => 'autodoc',
                'article'           => LinemediaAutoPartsHelper::clearArticle($part['part_art']),
                'brand_title'       => strtoupper($part['man_name']),
                'title'             => $part['part_name'],
                'price'             => $part['price_cl_out'],
                'quantity'          => $part['qty'],
                'delivery_time'     => $part['dlv_day_cl'] * 24, // в часах
                'date_update'       => $part['date_corr'],
                'data-source'       => self::$title,
            );

            $extra['hash'] = md5(json_encode($itempart));
			$extra['brand_title_original'] = strtoupper($part['man_name']);

            $itempart['extra'] = $extra;

            $analogs['analog_type_' . $analog_type] []= $itempart;
        }

        return $analogs;
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
        $this->extra['atd_bid'] = $data['extra']['atd_bid'];
        $this->search_analogs = false; // точно не ищем аналоги!

        $this->init();

        // Выполнить в любом случае для логина и получения id user
        $this->search();

        /*
         * Это сама запчасть или мы её как аналог нашли?
         */
        if (isset($data['extra']['analog'])) {
            $article = $data['article'];
            // ID автодочного бренда
            $autodoc_brand_id = (int) $data['extra']['bid'];
            $step = 0;
            $cross_type = self::CROSS_TYPE_OFFICIAL;
            $autodoc_user_id = $this->autodoc_user_id;

            $analogs = $this->getAnalogVariant($article, $autodoc_brand_id, $autodoc_user_id, $step, $cross_type);
            $this->parts = array_merge_recursive($this->parts, $analogs);
        }

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
     * Список типов доступов.
     * @return array
     */
    public static function getAccesses() {
        return array(GetMessage('ACCESS_LEVEL_PROF'), GetMessage('ACCESS_LEVEL_BASIC'), GetMessage('ACCESS_LEVEL_MIN'));
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
            'USE_ANALOGS' => array(
                'title' => GetMessage('USE_ANALOGS'),
                'type' => 'checkbox',
                'default' => false,
                'description' => GetMessage('USE_ANALOGS_DESCR'),
            ),
            'ANALOGS_LIMIT' => array(
                'title' => GetMessage('ANALOGS_LIMIT'),
                'type' => 'string',
                'default' => '0',
                'description' => GetMessage('ANALOGS_LIMIT_DESCR'),
            ),
            'ACCESS' => array(
                'title' => GetMessage('ACCESS'),
                'type' => 'list',
                'values' => AutodocRemoteSupplier::getAccesses(),
                'default' => GetMessage('ACCESS_LEVEL_PROF'),
                'description' => GetMessage('ACCESS_DESCR'),
            ),
        );
    }

}

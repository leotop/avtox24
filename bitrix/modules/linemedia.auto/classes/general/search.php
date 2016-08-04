<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/*
* Поиск запчастей в базе данных
*/
class LinemediaAutoSearch
{
    const SEARCH_SIMPLE     = 'LinemediaAutoSearchSimple'; // Простой поиск
    const SEARCH_PARTIAL    = 'LinemediaAutoSearchPartial'; // Поиск по части артикула
    const SEARCH_GROUP      = 'LinemediaAutoSearchGroup'; // Групповой поиск
    const SEARCH_BY_PARAMS  = 'LinemediaAutoSearchByParams'; // Параметрический поиск ORM

    const ARTICLE_LIMIT = 'article';
    const TITLE_LIMIT = 'title';

    const DEBUG_LIMIT_ARTICLE_MESS = 'Search is limited by Article (force skip sphinx)';
    const DEBUG_LIMIT_TITLE_MESS = 'Search is limited by Title (skip article search, use sphinx only)';
    const DEBUG_LIMIT_DEFAULT = 'Search is not limited (use sphinx if no articles found)';

    private static $cache;

    /*
     * Тип поиска
     */
    protected $type = null;


    /**
     * Условия поиска
     */
    protected $search_conditions = array(
        'id' => false,
        'query' => '',
        'brand_title' => null,
        'extra' => array(),
    );


    /*
     * Результат поиска запчастей
     */
    protected $search_article_results = array();

    /*
     * Каталоги, если они найдены
     */
    protected $search_catalog_results = array();

    /*
     * Тип результата поиска
     */
    protected $result_type = '404';

    /*
     * Non-fatal exceptions from different modules
     */
    protected $exceptions = array();

    /*
     * Дополнительная информация
     */
    protected $result_info = array();

    /*
     * оганичения поиска по артиклу или названию
     */
    protected $search_limit = NULL;


    /*
     * Модификатор поиска
     */
    protected $modificator_set = null;

    /**
     * whether price from occur in admin page or public
     * @var boolean $is_search_in_admin_page
     */
    protected $is_admin_search;

    protected $settings_similar_group = array();


    /*
    * Информация об искомой группе запчастей
    */
    protected $node_info = array();

    /**
     * Конструктор пустой и создан ради события
     */
    public function __construct()
    {
        /*
         * На создание объекта поиска создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchInstanceCreate");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(
                &$this->search_conditions,
                &$this->search_article_results,
                &$this->search_catalog_results,
            ));
        }

        /*
         * По умолчанию - простой поиск.
         */
        $this->type = self::SEARCH_SIMPLE;
    }

    public function setModificator($modificator) {
        $this->modificator_set = $modificator;
    }

    /**
     * set whether price occur in admin page or public
     * @param boolean $is_admin_search
     * @return void
     */
    public function setIsAdminSearch($is_admin_search) {
        $this->is_admin_search = $is_admin_search;
    }

    public function setSortOrderForSimilarGroup($arrayOrderSort) {
        $this->settings_similar_group = $arrayOrderSort;
    }

    /**
     * Получить информацию из TecDoc.
     */
    public function getResultInfo()
    {
        return $this->result_info;
    }

    /**
     * set up limit to restrict search by using either search by article or by title
     * by default nothing to be used
     * @param $string
     */
    public function setSearchLimit($string) {
        $this->search_limit = $string;
    }

    /**
     * Установим поисковую строку
     */
    public function setSearchQuery($string)
    {
        $this->setSearchCondition('query', $string);
    }

    /**
     * Установим групповой поиск
     */
    public function setType($type)
    {
        if (in_array(strval($type), array(self::SEARCH_SIMPLE, self::SEARCH_PARTIAL, self::SEARCH_GROUP, self::SEARCH_BY_PARAMS))) {
            $this->type = (string) $type;
        }
    }

    /**
     * Установим поисковое уточнение
     */
    public function setSearchCondition($param, $val)
    {
        /*
         * На создание объекта поиска создаём событие
         */
        if(self::$cache['events']['OnSearchConditionChange']) {
            $events = self::$cache['events']['OnSearchConditionChange'];
        } else {
            $events = GetModuleEvents("linemedia.auto", "OnSearchConditionChange");
            self::$cache['events']['OnSearchConditionChange'] = $events;
        }

        while ($arEvent = $events->Fetch()) {
            // start monitoring
            $timer = LinemediaAutoMonitoring::startTimer(array('scope' => 'search', 'module' => 'linemedia.auto', 'action' => $arEvent['TO_CLASS'] . '.' . $arEvent['TO_METHOD']));
            ExecuteModuleEventEx($arEvent, array(
                &$param,
                &$val
            ));
            // end monitoring
            LinemediaAutoMonitoring::stopTimer($timer);
        }

        $this->search_conditions[$param] = $val;
    }

    /**
     * Все настройки установлены, выполняем поиск
     */
    public function execute()
    {
        /*
         * Сохраним оригинальную строку поиска 17414
         */
        $this->search_conditions['query_original'] = $this->search_conditions['query'];

        /*
         * Сотрём запятую при негрупповом поиске
         */
        if ($this->type != self::SEARCH_GROUP) {
            $this->search_conditions['query'] = str_replace(',', '', $this->search_conditions['query']);
        }

        $this->articles_to_search = array();

        /*
         * Основная деталь для поиска
         * которая пришла в запросе от пользователя
         * её надо сразу добавить в список тех, что мы ищем в локальной БД
         */
        if ($this->search_conditions['id'] > 0) {
            /*
             * Поиск по ID запчасти
             */
            $sought_part = array(
                'id'        => $this->search_conditions['id'],
                'sought'    => true         // искомый арикул, то, что вбил в поиск пользователь
            );
        } else {

            /*
             * Почистим артикул для всех дальнейших его использований. Назарков И. задача #15029 23.01.14
             * Чистим артикулы и бренды до вызовов методов поиска. Внутри методов поиска повторную чистку не делать!
             */
            $this->search_conditions['query'] = LinemediaAutoPartsHelper::clearArticle($this->search_conditions['query']);


            /*
             * Поиск по артикулу.
             * Очистим артикул для последующего правильного объединения.
             * Внимание: Sphinx ориентиреутся запрос (query) из $search_conditions, а не из $articles_to_search.
             */
            $sought_part = array(
                //'article'   => (string) $this->search_conditions['query'], // LinemediaAutoPartsHelper::clearArticle($this->search_conditions['query']),

                // Ilya Pyatin 05.06.13 - иначе не работает определение искомой детали среди аналогов (это к тому, что тут точно нужно
                // чистить артикул, перенес это выше, Назарков И. (23.01.14))
                'article'   => $this->search_conditions['query'],
                'sought'    => true         // искомый арикул, то, что вбил в поиск пользователь
            );

            /*
             * Название бренда
             */
            if ($this->search_conditions['brand_title']) {
                $this->search_conditions['brand_title'] = LinemediaAutoPartsHelper::clearBrand($this->search_conditions['brand_title']);
                $sought_part['brand_title'] = $this->search_conditions['brand_title'];

                $this->articles_to_search[$sought_part['article'] . '|' . $sought_part['brand_title']] = $sought_part;
            } else {
                $this->articles_to_search[$sought_part['article']] = $sought_part;
            }

            /*
             * Внешние данные
             */
            if ($this->search_conditions['extra']) {
                $sought_part['extra'] = $this->search_conditions['extra'];
            }
        }

        /*
         * На начало поиска создаём событие
         * в событие передаём массив с результатами поиска и объект поисковика
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteBegin");
        while ($arEvent = $events->Fetch()) {
            /*
             * Аргументы:
             * Первый    - условия поиска
             * Второй    - список запчастей, которые надо искать в локально базе
             * Третий    - каталоги, если нужно
             * Четвёртый - запчасти, которые там "как бы уже найдены" (например от emex)
             * Пятый     - тип поиска
             * Шестой    - дополнительная информация о деталях
             */
            try {
                // start monitoring
                $timer = LinemediaAutoMonitoring::startTimer(array('scope' => 'search', 'module' => 'linemedia.auto', 'action' => $arEvent['TO_CLASS'] . '.' . $arEvent['TO_METHOD']));
                ExecuteModuleEventEx($arEvent, array(
                    &$this->search_conditions,
                    &$this->articles_to_search,
                    &$this->search_catalog_results,
                    &$this->search_article_results,
                    &$this->type,
                    &$this->result_info,
                    &$this->modificator_set,
                    &$this->search_limit,
                ));
                // end monitoring
                LinemediaAutoMonitoring::stopTimer($timer);
            } catch (Exception $e) {
                $this->exceptions []= $e;
            }
        }

        /*
         * Проверим массив с каталогами на пустые значения и удалим дубликаты
         */
        foreach ($this->search_catalog_results as $y => $catalog) {

            /*
             * Исправлена ошибка, связанная с тем, что "проверка на массив" стояла не в том месте, т.е. в
             * метод clearBrand передавался массив и он возвращал null. Теперь в качестве параметра
             * гарантированно передается строка. (Алексеенко К.И.)
             */

            /*
             * Иногда в 'title' и 'brand_title' возращается массив, наверное т.к. в 'source' тоже массив
             * например когда детать и в аналогах текдока, и в базе, в этом случае считаем, что бренд и там
             * и там одинаковый и берем первый элемент массива. (Назарков И.)
             */
            if (is_array($this->search_catalog_results[$y]['brand_title'])) {
                $this->search_catalog_results[$y]['brand_title'] = $catalog['brand_title'][0] ? LinemediaAutoPartsHelper::clearBrand($catalog['brand_title'][0]) : '-';
            }
            else {
                $this->search_catalog_results[$y]['brand_title'] = $catalog['brand_title'] ? LinemediaAutoPartsHelper::clearBrand($catalog['brand_title']) : '-';
            }

            if (is_array($this->search_catalog_results[$y]['title'])) {
                $this->search_catalog_results[$y]['title'] = $catalog['title'][0] ? $catalog['title'][0] : '-';
            }
            else {
                $this->search_catalog_results[$y]['title'] = $catalog['title'] ? $catalog['title'] : '-';
            }
        }

        /*
         * Уберём повторяющиеся значения
         */
        $this->search_catalog_results = self::getIntersectCatalogs($this->search_catalog_results, $this->type);

        /*
         * Что у нас в ответе? Уточнение или уже детали?
         * Нельзя искать каталоги, если пришёл бренд.
         */
        $has_brand = ($this->search_conditions['brand_title'] != '');

        if ($has_brand) {
            /*
             * Показываем ТОЛЬКО детали
             */
            $this->result_type = 'parts';
        } else {
            /*
             * Показываем детали, если нет каталогов
             */
            if (count($this->search_catalog_results) > 1) {
                $this->result_type = 'catalogs';
            } else {
                $this->result_type = 'parts';
            }
        }

        /*
         * Каталоги
         */
        if ($this->result_type == 'catalogs') {
            /*
             * Вывод отладочной информации.
             */
            LinemediaAutoDebug::add('Catalogs found', false, LM_AUTO_DEBUG_WARNING);
        }

        /*
         * Детали
         */
        if ($this->result_type == 'parts') {
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('No catalogs, parts found', print_r($this->articles_to_search, true), LM_AUTO_DEBUG_WARNING);

            /*
            * Поиск информации (напр картинки) о группе искомой запчасти
            * в оригинальных каталогах
            */
            if(LinemediaAutoModule::isFunctionEnabled('linemedia_crosses')) {

                $api = new LinemediaAutoApiDriver();

                // какие бренды есть смысл искать (только доступные в наших оригинальных каталогах)?
                $obCache = new CPHPCache();
                $cache_id = 'linemedia_auto/search_node_info';
                if ($obCache->InitCache(3600, $cache_id, "/lm_auto/search_node_info")) {
                    $cache = $obCache->GetVars();
                    $search_brands = $cache['search_brands'];
                } else {
                    // запросим список доступных брендов
                    $available_original_brands = $api->getOriginalBrands();
                    $search_brands = array();
                    foreach($available_original_brands['data']['brands'] AS $brand) {
                        $search_brands[] = $brand['brand_title'];
                    }
                    $search_brands = array_map('strtolower', $search_brands);

                    if ($obCache->StartDataCache()) {
                        $obCache->EndDataCache(array(
                            'search_brands' => $search_brands,
                        ));
                    }
                }

                // найдём запчасти с подходящими брендами
                $node_info_request = array();
                foreach($this->articles_to_search AS $part) {
                    if(in_array(strtolower($part['brand_title']), $search_brands)) {
                        $node_info_request[] = array(
                            'brand_code' => $part['brand_title'],
                            'article' => $part['article'],
                        );
                    }
                }

                // если нет указания бренда, может он есть в результатах поиска?
                if(!$this->search_conditions['brand_title']) {
                    foreach($this->search_article_results AS $group) {
                        foreach($group AS $part) {
                            if(in_array(strtolower($part['brand_title']), $search_brands)) {
                                $node_info_request[] = array(
                                    'brand_code' => $part['brand_title'],
                                    'article' => $part['article'],
                                );
                            }
                        }
                    }
                }

                // отправим на поиск
                if(count($node_info_request)) {
                    $response = $api->searchNodeInfoByOriginalArticleMultiple($node_info_request);
                    if(count($response['data'])) {
                        $this->node_info = $response['data'];
                    }
                }
            }

            /*
             * Проводим непосредственно сам поиск.
             * Предполагается, что в событиях отработало подключение всех возможных поставщиков данных,
             * а потому здесь остаётся только выбрать полученные номера деталей из БД запчастей.
             */
            $found_local_items = array();
            if(is_array($this->articles_to_search) && count($this->articles_to_search) > 0) {

                /**
                 * Очистка деталей от пустых и повторяющихся значений
                 * добавим туда же очистку
                 */
                $this->articles_to_search = self::getUniqueArticlesTosearch($this->articles_to_search);

                $wordforms = new LinemediaAutoWordForm();

                /*
                 * Использование класса нужного типа поиска.
                 */
                $search = new $this->type();

//                if($this->type == self::SEARCH_BY_PARAMS) {
//
//                    $query = array_keys($this->articles_to_search);
//                    $found_local_items = $search->searchLocalDatabaseForPart(array('article' => $query), true);
//
//                } else { // SEARCH_SIMPLE

                foreach ($this->articles_to_search as $part) {

                    /*
                     * Вывод отладочной информации
                     */
                    LinemediaAutoDebug::add('Search db for art=' . $part['article'], print_r($part, true));

                    if ($found_local_item_list = $search->searchLocalDatabaseForPart($part, true)) {

                        foreach ($found_local_item_list as $k => &$found_local_item) {

                            /*
                             * Из какого поставщика аналогов пришёл этот артикул?
                             */
                            $found_local_item['analog-source'] = $part['source'];
                            $found_local_item['origin'] = $part['analog-source'];

                            /*
                             * Вывод отладочной информации.
                             */
                            LinemediaAutoDebug::add('Part found art=' . $part['article'] . ' with analog type=' . $part['analog_type']);

                            $analog_type = (string) $part['analog_type'];

                            /*
                            * Если искомый артикул вдруг оказался в аналогах - проставим ему группу "Искомый артикул"
                            */
                            //также проверяем бренды, иначе кроссы показываются в таблице оригинальных запчастей (Назарков И,)
                            //TODO: а это вообще нужно?
                            $brandIsChecked = $sought_part['brand_title'] ? $part['brand_title'] == $sought_part['brand_title'] : 1;

                            if($part['article'] == $sought_part['article'] AND $analog_type != 'N' &&$brandIsChecked)
                                $part['analog_type'] = 'N';

                            /*
                             * Дата: 28.10.13 18:02
                             * Кто: Назарков Илья
                             * Задача: 5938
                             * Пояснения: А нет ли бренда данной детали в группе брендов, объединенных словоформой и в которой есть искомый бренд?
                             */
                            if (in_array($part['brand_title'], (array) $wordforms->getBrandWordforms($sought_part['brand_title'])) && $part['article'] == $sought_part['article']) {
                                $analog_type = 'N';
                            }

                            /*
                             * Дата: 28.10.13 18:02
                             * Кто: Назарков Илья
                             * Задача: 5938
                             * Пояснения: Не является ли искомый бренд названием словоформы? Если да, то нет ли в брендах данной словоформы бренда данной детали?
                             */
                            if (in_array($part['brand_title'], (array) $wordforms->getGroupWordforms($sought_part['brand_title'])) && $part['article'] == $sought_part['article']) {
                                $analog_type = 'N';
                            }

                            /*
                             * Искомый артикул.
                             */
                            if ($part['sought']) {
                                /*
                                 * Вывод отладочной информации.
                                 */
                                LinemediaAutoDebug::add('Part art=' . $part['article']. ' is marked as sought-for by user');
                                $analog_type = 'N';
                            }

                            /*
                             * Покажем оригинальное написание артикула.
                             */
                            $found_local_item['article'] = $found_local_item['original_article'];

                            /*
                             * Передадим дополнительные параметры.
                             */
                            $found_local_item['extra'] = $part['extra'];

                            $found_local_items['analog_type_' . $analog_type] []= $found_local_item;
                        }
                    } // if ($found_local_item_list = $search->searchLocalDatabaseForPart($part, true))
                } // foreach ($this->articles_to_search as $part)
                //} // if($this->type == self::SEARCH_BY_PARAMS)

            } // if(is_array($this->articles_to_search) && count($this->articles_to_search) > 0)

            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Informations result', print_r($this->result_info, true), LM_AUTO_DEBUG_WARNING);

            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Local result', print_r($found_local_items, true), LM_AUTO_DEBUG_WARNING);

            /*
             * Объединим результат локального поиска с тем, что уже было в переменной.
             * Она может быть непустой, если туда вписаны запчасти, которых на самом деле нет локально.
             * Например Emex.
             */
            $this->search_article_results = array_merge_recursive($this->search_article_results, $found_local_items);

            foreach ($this->search_article_results as $i => $group) {
                foreach ($group as $j => $part) {

                    $skip = false;

                    $this->search_article_results[$i][$j]['brand_title'] = LinemediaAutoPartsHelper::clearBrand($part['brand_title']);

                    $this->search_article_results[$i][$j]['article'] = LinemediaAutoPartsHelper::clearArticle($part['article']);

                    /*
                     * Не показываем детали с 0 количеством если включена настройка "Показывать только в наличии" для внеш. поставщиков
                     * Правка по задаче 9075
                     */
                    if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y' && (int)$part['quantity'] == 0) {

                        $events = GetModuleEvents("linemedia.auto", "OnBeforeItemQuantityZeroDelete");
                        while ($arEvent = $events->Fetch()) {
                            if(ExecuteModuleEventEx($arEvent, array($part)) == false) {
                                $skip = true;
                                break;
                            }
                        }

                        if($skip) {
                            continue;
                        }
                        unset($this->search_article_results[$i][$j]);
                    }
                }
            }


            /*
             * Уберем одинаковые запчасти.
             */
            $this->search_article_results = self::getIntersectParts($this->search_article_results);


            if (count($this->search_article_results) == 0) {
                /*
                 * 404
                 */
                $this->result_type = '404';

                /*
                 * Вывод отладочной информации
                 */
                LinemediaAutoDebug::add('No catalogs and no parts found', false, LM_AUTO_DEBUG_WARNING);
            }
        } // if ($this->result_type == 'parts')

        /*
         * На конец поиска создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteEnd");
        $result_type = $this->result_type;
        while ($arEvent = $events->Fetch()) {
            /*
             * Аргументы
             * Первый    - условия поиска
             * Второй    - список запчастей, которые надо искать в локально базе
             * Третий    - Каталоги, если нужно
             * Четвёртый - запчасти, которые там "как бы уже найдены" (например от emex)
             * Пятый     - тип поиска
             * Шестой    - дополнительная информация о деталях
             */

            // start monitoring
            $timer = LinemediaAutoMonitoring::startTimer(array('scope' => 'search', 'module' => 'linemedia.auto', 'action' => $arEvent['TO_CLASS'] . '.' . $arEvent['TO_METHOD']));
            ExecuteModuleEventEx($arEvent, array(
                &$this->search_conditions,
                &$this->articles_to_search,
                &$this->search_catalog_results,
                &$this->search_article_results,
                &$this->type,
                &$this->result_info,
                &$this->modificator_set,
                &$this->settings_similar_group,
                &$this->search_limit,
            ));
            // end monitoring
            LinemediaAutoMonitoring::stopTimer($timer);
        }

        /*
         * Если после интерсекта остались пусты типы аналогов
         */
        $this->search_article_results = array_filter($this->search_article_results);

        /*
         * Применим словоформы.
         */

        /*
        * Словоформы решено было перенести в компонент search.results.
        * Как выяснилось, словоформы ломают добавление товара в корзину от удаленных поставщиков
        * название бренда, по которому при добавлении в корзину происходит поиск, заменяется на
        * соответствующее название группы в разделе словоформы (задача №21625)
        */
        // $use_wordform = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SHOW_WORDFORM_PARTS' ,'N');
        // if ($use_wordform == 'Y') {
        //     $wordforms = new LinemediaAutoWordForm();
        //     foreach ($this->search_article_results as &$group) {
        //         foreach ($group as &$part) {
        //             $wordform = $wordforms->getBrandGroup($part['brand_title']);
        //             if (!empty($wordform)) {
        //                 $part['original_brand_title'] = $part['brand_title'];
        //                 $part['brand_title'] = $wordform;
        //             }
        //         }
        //     }
        // }


        /**
         *
         * задача №21870
         * Иногда от удаленного поставщика приходят детали, которые должны являться каталогами
         * (например, уд. поставщик autoeuro артикул 21410-1LL0A - приходит как деталь, но
         * в случае, когда в local-database по этому же артикулу есть каталоги, то результат от
         * удаленного поставщика переделываем в каталог и выводим вместе с остальными каталогами)
         *
         **/
        // если есть детали и тип поиска - каталоги
        if(is_array($this->search_article_results) && $this->result_type == "catalogs") {

            // №22469
            // добавим детали в каталоги и проверим по словоформам
            // предыдущий алгоритм не учитывал словоформы
            if(is_array($this->search_article_results['analog_type_N']) &&
                count($this->search_article_results['analog_type_N']) > 0) {
                $this->search_catalog_results = array_merge($this->search_catalog_results, $this->search_article_results['analog_type_N']);
                $this->search_catalog_results = self::getIntersectCatalogs($this->search_catalog_results, $this->type);
            }

//            // пройдемся по каждой детали из analog_type_N ...
//            foreach($this->search_article_results['analog_type_N'] as $key_group => $group_array) {
//                // ... затем по каждому бренду из каталогов
//                foreach($this->search_catalog_results as $brand_name_catalog => $array_catalog) {
//                    // если название бренда из списка деталей совпадает с названием бренда из каталога
//                    // (не зависимо от регистра) и если эта деталь пришла от удаленного поставщика
//                    // (is_remote_supplier = 1)
//                    if(strcasecmp($group_array['brand_title'], $brand_name_catalog) != 0
//                        && $group_array['is_remote_supplier'] == 1) {
//                        // создаем новый массив для объединения с массивом каталогов
//                        $new_search_catalog_results[$group_array['brand_title']] = $group_array;
//                    }
//                }
//            }
//            // если новый массив не пустой, объединяем его с исходным массивом search_catalog_results
//            if (is_array($new_search_catalog_results) && count($new_search_catalog_results) > 0 ) {
//                $this->search_catalog_results = array_merge($this->search_catalog_results, $new_search_catalog_results);
//            }
        }

        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Final result', print_r($this, true), LM_AUTO_DEBUG_WARNING);
    }

    /**
     * Search return type (catalogs || parts || 404)
     */
    public function getResultsType()
    {
        return $this->result_type;
    }


    public function getResultsParts()
    {
        return $this->search_article_results;
    }


    public function getResultsCatalogs()
    {
        return $this->search_catalog_results;
    }


    /**
     * Получить ошибки, полученные от разных поставщиков поиска
     */
    public function getThrownExceptions()
    {
        return $this->exceptions;
    }

    /*
    * Получить информацию о группе запчастей искомой детали
    */
    public function getArticleNodeInfo()
    {
        return (array) $this->node_info;
    }

    public function getModificator() {
        return $this->modificator_set;
    }


    /**
     * Получение уникальных запчастей из каталогов.
     *
     * @param array $results
     */
    protected static function getIntersectParts($groups)
    {
        $result   = array();
        $hasitems = array();

        ksort($groups);
        // move analog_type_N to beginning of array
        if(array_key_exists('analog_type_N', $groups)) {
            $groups = array_merge(array('analog_type_N' => $groups['analog_type_N']), $groups);
        }

        foreach ($groups as $i => $parts) {
            $result[$i] = array();
            foreach ($parts as $j => $part) {
                $hash = md5($part['supplier_id'].$part['article'].$part['price'].$part['brand_title'].$part['delivery_time']);
                if (array_key_exists($hash, $hasitems)) {
                    $group_id = $hasitems[$hash];

                    /*
                     * Объединим extra значения
                     */
                    $new_extra = $result[$group_id][$hash]['extra'];
                    foreach ($part['extra'] as $k => $v) {
                        if ($new_extra[$k] === $v) {
                            continue;
                        }
                        /*
                         * Если сливаемый ключ в первом массиве пуст, то функция
                         * array_merge_recursive просто добавит его без значения.
                         */
                        if (!array_key_exists($k, $new_extra) || empty($new_extra[$k])) {
                            $new_extra[$k] = $part['extra'][$k];
                        } elseif (!empty($part['extra'][$k])) {
                            // ivan 20.05.14 #8786
                            // для скаляров просто перезаписываем
                            if(is_array($new_extra[$k]) || is_array($part['extra'][$k])) {
                                $new_extra[$k] = array_merge_recursive($new_extra[$k], $part['extra'][$k]);
                            } else {
                                $new_extra[$k] = $part['extra'][$k];
                            }
                            $debug = true;
                        }
                    }
                    $new_extra['wf_b'] = array_unique((array) $new_extra['wf_b']);
                    $result[$group_id][$hash]['extra'] = $new_extra;

                    continue;
                } else {
                    // Сохраним группу, в которой нашлась первая деталь
                    $hasitems[$hash] = $i;
                }
                $result[$i][$hash] = $part;
            }
        }

        // Выраванивание индекса.
        foreach ($result as $i => $parts) {
            $result[$i] = array_values($parts);
        }

        return $result;
    }

    /**
     * Получение уникальных каталогов с учетом словоформ.
     * @param array $results
     */
    public static function getIntersectCatalogs($results, $type = self::SEARCH_SIMPLE)
    {
        // если не простой тип поиска - возвращаем без изменений
        if($type != self::SEARCH_SIMPLE && $type != self::SEARCH_PARTIAL) {
            return $results;
        }

        /*
         * Объединим каталоги по брендам
         */
        $wordforms = new LinemediaAutoWordForm();

        $catalog_brands = array();

        foreach($results as $catalog) {

            $brand_title = $catalog['brand_title'];
            $brand_normalized = $wordforms->getBrandGroup($brand_title);
            if ($brand_normalized) {
                $brand_title = $brand_normalized;
                $brand_key = $brand_normalized;
            } else {
                $brand_key = LinemediaAutoWordForm::normalize($brand_title);
            }

            $source = $catalog['source'];
            if(strlen($source) < 1) {
                $source = 'undefined';
            }

            if(array_key_exists($brand_key, $catalog_brands)) {

                $catalog_brands[$brand_key]['titles'][$source] = trim($catalog['title']);

                if($catalog_brands[$brand_key]['title'] == '' || $catalog_brands[$brand_title]['title'] == '-') {
                    $catalog_brands[$brand_key]['title'] = trim($catalog['title']);
                }

                $catalog_brands[$brand_key]['brand_title_original'][$source] = $catalog['brand_title'];
                $catalog_brands[$brand_key]['sources'][] = $source;

                if(is_array($catalog['extra'])) {
                    $catalog_brands[$brand_key]['extra'] =
                        array_merge_recursive($catalog_brands[$brand_key]['extra'], $catalog['extra']);
                    // чистим экстру чтобы не генерировать слишком длинных ссылок
                    $catalog_brands[$brand_key]['extra'] = LinemediaAutoPartsHelper::clearExtra($catalog_brands[$brand_key]['extra']);
                }


            } else {

                $catalog_brands[$brand_key] = array(
                    'title' => trim($catalog['title']),
                    'titles' => array($source => $catalog['title']),
                    'article' => $catalog['article'],
                    'brand_title' => $brand_title,
                    'brand_title_original' => array($source => $catalog['brand_title']),
                    'extra' => $catalog['extra'],
                    'sources' => array($catalog['source']),
                );

                if($brand_normalized) {
                    $catalog_brands[$brand_key]['extra']['wf_b'] = $wordforms->getBrandWordforms($brand_title);
                }
                // чистим экстру чтобы не генерировать слишком длинных ссылок
                $catalog_brands[$brand_key]['extra'] = LinemediaAutoPartsHelper::clearExtra($catalog_brands[$brand_key]['extra']);
            }
        }

        return $catalog_brands;
    }


    /**
     * Отбор уникальных списков бренд-артикул.
     */
    protected static function getUniqueArticlesTosearch($parts)
    {
        $parts = array_reverse($parts);

        $result = array();

        foreach ($parts as $part) {

            $part['article'] = LinemediaAutoPartsHelper::clearArticle($part['article']);
            if(empty($part['article'])) {
                continue;
            }
            $part['brand_title'] = LinemediaAutoPartsHelper::clearBrand($part['brand_title']);
            $key = $part['article'] . '|' . $part['brand_title']; // .$part['analog-source'] - какая разница откуда аналог для поиска по локальной БД ?

            /**
             * №19048
             * 12.10.15
             * ioannes
             * сохранение extra
             */
            if(array_key_exists($key, $result)) {
                $result[$key] = array_merge($part,  $result[$key]);
            } else {
                $result[$key] = $part;
            }
            //$result[$key] = $part;
        }
        // оставим ключи для варианта группового поиска
        //$result = array_values($result);

        return $result;
    }


    /**
     * Получение уникальных названий каталогов.
     *
     * @param array $results
     */
    protected static function getIntersectCatalogTitleResults($results)
    {
        $results = array_filter($results, array('LinemediaAutoSearch', 'intersectCatalogTitleResults'));
        return $results;
    }


    /**
     * Пресечение результатов поиска, чтобы удалить одинаковые каталоги (по параметрам).
     *
     * @param array $item
     */
    protected static function intersectCatalogTitleResults($item)
    {
        $item = array_map('strtolower', $item);

        static $has = array();

        foreach ($has as $hasitem) {
            $hasitem = array_map('strtolower', $hasitem);
            if ($item['brand_title'] == $hasitem['brand_title']) {
                return false;
            }
        }
        $has []= $item;

        return true;
    }
}

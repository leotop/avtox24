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
* ����� ��������� � ���� ������
*/
class LinemediaAutoSearch
{
    const SEARCH_SIMPLE     = 'LinemediaAutoSearchSimple'; // ������� �����
    const SEARCH_PARTIAL    = 'LinemediaAutoSearchPartial'; // ����� �� ����� ��������
    const SEARCH_GROUP      = 'LinemediaAutoSearchGroup'; // ��������� �����
    const SEARCH_BY_PARAMS  = 'LinemediaAutoSearchByParams'; // ��������������� ����� ORM

    const ARTICLE_LIMIT = 'article';
    const TITLE_LIMIT = 'title';

    const DEBUG_LIMIT_ARTICLE_MESS = 'Search is limited by Article (force skip sphinx)';
    const DEBUG_LIMIT_TITLE_MESS = 'Search is limited by Title (skip article search, use sphinx only)';
    const DEBUG_LIMIT_DEFAULT = 'Search is not limited (use sphinx if no articles found)';

    private static $cache;

    /*
     * ��� ������
     */
    protected $type = null;


    /**
     * ������� ������
     */
    protected $search_conditions = array(
        'id' => false,
        'query' => '',
        'brand_title' => null,
        'extra' => array(),
    );


    /*
     * ��������� ������ ���������
     */
    protected $search_article_results = array();

    /*
     * ��������, ���� ��� �������
     */
    protected $search_catalog_results = array();

    /*
     * ��� ���������� ������
     */
    protected $result_type = '404';

    /*
     * Non-fatal exceptions from different modules
     */
    protected $exceptions = array();

    /*
     * �������������� ����������
     */
    protected $result_info = array();

    /*
     * ���������� ������ �� ������� ��� ��������
     */
    protected $search_limit = NULL;


    /*
     * ����������� ������
     */
    protected $modificator_set = null;

    /**
     * whether price from occur in admin page or public
     * @var boolean $is_search_in_admin_page
     */
    protected $is_admin_search;

    protected $settings_similar_group = array();


    /*
    * ���������� �� ������� ������ ���������
    */
    protected $node_info = array();

    /**
     * ����������� ������ � ������ ���� �������
     */
    public function __construct()
    {
        /*
         * �� �������� ������� ������ ������ �������
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
         * �� ��������� - ������� �����.
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
     * �������� ���������� �� TecDoc.
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
     * ��������� ��������� ������
     */
    public function setSearchQuery($string)
    {
        $this->setSearchCondition('query', $string);
    }

    /**
     * ��������� ��������� �����
     */
    public function setType($type)
    {
        if (in_array(strval($type), array(self::SEARCH_SIMPLE, self::SEARCH_PARTIAL, self::SEARCH_GROUP, self::SEARCH_BY_PARAMS))) {
            $this->type = (string) $type;
        }
    }

    /**
     * ��������� ��������� ���������
     */
    public function setSearchCondition($param, $val)
    {
        /*
         * �� �������� ������� ������ ������ �������
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
     * ��� ��������� �����������, ��������� �����
     */
    public function execute()
    {
        /*
         * �������� ������������ ������ ������ 17414
         */
        $this->search_conditions['query_original'] = $this->search_conditions['query'];

        /*
         * ����� ������� ��� ����������� ������
         */
        if ($this->type != self::SEARCH_GROUP) {
            $this->search_conditions['query'] = str_replace(',', '', $this->search_conditions['query']);
        }

        $this->articles_to_search = array();

        /*
         * �������� ������ ��� ������
         * ������� ������ � ������� �� ������������
         * � ���� ����� �������� � ������ ���, ��� �� ���� � ��������� ��
         */
        if ($this->search_conditions['id'] > 0) {
            /*
             * ����� �� ID ��������
             */
            $sought_part = array(
                'id'        => $this->search_conditions['id'],
                'sought'    => true         // ������� ������, ��, ��� ���� � ����� ������������
            );
        } else {

            /*
             * �������� ������� ��� ���� ���������� ��� �������������. �������� �. ������ #15029 23.01.14
             * ������ �������� � ������ �� ������� ������� ������. ������ ������� ������ ��������� ������ �� ������!
             */
            $this->search_conditions['query'] = LinemediaAutoPartsHelper::clearArticle($this->search_conditions['query']);


            /*
             * ����� �� ��������.
             * ������� ������� ��� ������������ ����������� �����������.
             * ��������: Sphinx ������������� ������ (query) �� $search_conditions, � �� �� $articles_to_search.
             */
            $sought_part = array(
                //'article'   => (string) $this->search_conditions['query'], // LinemediaAutoPartsHelper::clearArticle($this->search_conditions['query']),

                // Ilya Pyatin 05.06.13 - ����� �� �������� ����������� ������� ������ ����� �������� (��� � ����, ��� ��� ����� �����
                // ������� �������, ������� ��� ����, �������� �. (23.01.14))
                'article'   => $this->search_conditions['query'],
                'sought'    => true         // ������� ������, ��, ��� ���� � ����� ������������
            );

            /*
             * �������� ������
             */
            if ($this->search_conditions['brand_title']) {
                $this->search_conditions['brand_title'] = LinemediaAutoPartsHelper::clearBrand($this->search_conditions['brand_title']);
                $sought_part['brand_title'] = $this->search_conditions['brand_title'];

                $this->articles_to_search[$sought_part['article'] . '|' . $sought_part['brand_title']] = $sought_part;
            } else {
                $this->articles_to_search[$sought_part['article']] = $sought_part;
            }

            /*
             * ������� ������
             */
            if ($this->search_conditions['extra']) {
                $sought_part['extra'] = $this->search_conditions['extra'];
            }
        }

        /*
         * �� ������ ������ ������ �������
         * � ������� ������� ������ � ������������ ������ � ������ ����������
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteBegin");
        while ($arEvent = $events->Fetch()) {
            /*
             * ���������:
             * ������    - ������� ������
             * ������    - ������ ���������, ������� ���� ������ � �������� ����
             * ������    - ��������, ���� �����
             * �������� - ��������, ������� ��� "��� �� ��� �������" (�������� �� emex)
             * �����     - ��� ������
             * ������    - �������������� ���������� � �������
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
         * �������� ������ � ���������� �� ������ �������� � ������ ���������
         */
        foreach ($this->search_catalog_results as $y => $catalog) {

            /*
             * ���������� ������, ��������� � ���, ��� "�������� �� ������" ������ �� � ��� �����, �.�. �
             * ����� clearBrand ����������� ������ � �� ��������� null. ������ � �������� ���������
             * �������������� ���������� ������. (���������� �.�.)
             */

            /*
             * ������ � 'title' � 'brand_title' ����������� ������, �������� �.�. � 'source' ���� ������
             * �������� ����� ������ � � �������� �������, � � ����, � ���� ������ �������, ��� ����� � ���
             * � ��� ���������� � ����� ������ ������� �������. (�������� �.)
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
         * ����� ������������� ��������
         */
        $this->search_catalog_results = self::getIntersectCatalogs($this->search_catalog_results, $this->type);

        /*
         * ��� � ��� � ������? ��������� ��� ��� ������?
         * ������ ������ ��������, ���� ������ �����.
         */
        $has_brand = ($this->search_conditions['brand_title'] != '');

        if ($has_brand) {
            /*
             * ���������� ������ ������
             */
            $this->result_type = 'parts';
        } else {
            /*
             * ���������� ������, ���� ��� ���������
             */
            if (count($this->search_catalog_results) > 1) {
                $this->result_type = 'catalogs';
            } else {
                $this->result_type = 'parts';
            }
        }

        /*
         * ��������
         */
        if ($this->result_type == 'catalogs') {
            /*
             * ����� ���������� ����������.
             */
            LinemediaAutoDebug::add('Catalogs found', false, LM_AUTO_DEBUG_WARNING);
        }

        /*
         * ������
         */
        if ($this->result_type == 'parts') {
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('No catalogs, parts found', print_r($this->articles_to_search, true), LM_AUTO_DEBUG_WARNING);

            /*
            * ����� ���������� (���� ��������) � ������ ������� ��������
            * � ������������ ���������
            */
            if(LinemediaAutoModule::isFunctionEnabled('linemedia_crosses')) {

                $api = new LinemediaAutoApiDriver();

                // ����� ������ ���� ����� ������ (������ ��������� � ����� ������������ ���������)?
                $obCache = new CPHPCache();
                $cache_id = 'linemedia_auto/search_node_info';
                if ($obCache->InitCache(3600, $cache_id, "/lm_auto/search_node_info")) {
                    $cache = $obCache->GetVars();
                    $search_brands = $cache['search_brands'];
                } else {
                    // �������� ������ ��������� �������
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

                // ����� �������� � ����������� ��������
                $node_info_request = array();
                foreach($this->articles_to_search AS $part) {
                    if(in_array(strtolower($part['brand_title']), $search_brands)) {
                        $node_info_request[] = array(
                            'brand_code' => $part['brand_title'],
                            'article' => $part['article'],
                        );
                    }
                }

                // ���� ��� �������� ������, ����� �� ���� � ����������� ������?
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

                // �������� �� �����
                if(count($node_info_request)) {
                    $response = $api->searchNodeInfoByOriginalArticleMultiple($node_info_request);
                    if(count($response['data'])) {
                        $this->node_info = $response['data'];
                    }
                }
            }

            /*
             * �������� ��������������� ��� �����.
             * ��������������, ��� � �������� ���������� ����������� ���� ��������� ����������� ������,
             * � ������ ����� ������� ������ ������� ���������� ������ ������� �� �� ���������.
             */
            $found_local_items = array();
            if(is_array($this->articles_to_search) && count($this->articles_to_search) > 0) {

                /**
                 * ������� ������� �� ������ � ������������� ��������
                 * ������� ���� �� �������
                 */
                $this->articles_to_search = self::getUniqueArticlesTosearch($this->articles_to_search);

                $wordforms = new LinemediaAutoWordForm();

                /*
                 * ������������� ������ ������� ���� ������.
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
                     * ����� ���������� ����������
                     */
                    LinemediaAutoDebug::add('Search db for art=' . $part['article'], print_r($part, true));

                    if ($found_local_item_list = $search->searchLocalDatabaseForPart($part, true)) {

                        foreach ($found_local_item_list as $k => &$found_local_item) {

                            /*
                             * �� ������ ���������� �������� ������ ���� �������?
                             */
                            $found_local_item['analog-source'] = $part['source'];
                            $found_local_item['origin'] = $part['analog-source'];

                            /*
                             * ����� ���������� ����������.
                             */
                            LinemediaAutoDebug::add('Part found art=' . $part['article'] . ' with analog type=' . $part['analog_type']);

                            $analog_type = (string) $part['analog_type'];

                            /*
                            * ���� ������� ������� ����� �������� � �������� - ��������� ��� ������ "������� �������"
                            */
                            //����� ��������� ������, ����� ������ ������������ � ������� ������������ ��������� (�������� �,)
                            //TODO: � ��� ������ �����?
                            $brandIsChecked = $sought_part['brand_title'] ? $part['brand_title'] == $sought_part['brand_title'] : 1;

                            if($part['article'] == $sought_part['article'] AND $analog_type != 'N' &&$brandIsChecked)
                                $part['analog_type'] = 'N';

                            /*
                             * ����: 28.10.13 18:02
                             * ���: �������� ����
                             * ������: 5938
                             * ���������: � ��� �� ������ ������ ������ � ������ �������, ������������ ����������� � � ������� ���� ������� �����?
                             */
                            if (in_array($part['brand_title'], (array) $wordforms->getBrandWordforms($sought_part['brand_title'])) && $part['article'] == $sought_part['article']) {
                                $analog_type = 'N';
                            }

                            /*
                             * ����: 28.10.13 18:02
                             * ���: �������� ����
                             * ������: 5938
                             * ���������: �� �������� �� ������� ����� ��������� ����������? ���� ��, �� ��� �� � ������� ������ ���������� ������ ������ ������?
                             */
                            if (in_array($part['brand_title'], (array) $wordforms->getGroupWordforms($sought_part['brand_title'])) && $part['article'] == $sought_part['article']) {
                                $analog_type = 'N';
                            }

                            /*
                             * ������� �������.
                             */
                            if ($part['sought']) {
                                /*
                                 * ����� ���������� ����������.
                                 */
                                LinemediaAutoDebug::add('Part art=' . $part['article']. ' is marked as sought-for by user');
                                $analog_type = 'N';
                            }

                            /*
                             * ������� ������������ ��������� ��������.
                             */
                            $found_local_item['article'] = $found_local_item['original_article'];

                            /*
                             * ��������� �������������� ���������.
                             */
                            $found_local_item['extra'] = $part['extra'];

                            $found_local_items['analog_type_' . $analog_type] []= $found_local_item;
                        }
                    } // if ($found_local_item_list = $search->searchLocalDatabaseForPart($part, true))
                } // foreach ($this->articles_to_search as $part)
                //} // if($this->type == self::SEARCH_BY_PARAMS)

            } // if(is_array($this->articles_to_search) && count($this->articles_to_search) > 0)

            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Informations result', print_r($this->result_info, true), LM_AUTO_DEBUG_WARNING);

            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Local result', print_r($found_local_items, true), LM_AUTO_DEBUG_WARNING);

            /*
             * ��������� ��������� ���������� ������ � ���, ��� ��� ���� � ����������.
             * ��� ����� ���� ��������, ���� ���� ������� ��������, ������� �� ����� ���� ��� ��������.
             * �������� Emex.
             */
            $this->search_article_results = array_merge_recursive($this->search_article_results, $found_local_items);

            foreach ($this->search_article_results as $i => $group) {
                foreach ($group as $j => $part) {

                    $skip = false;

                    $this->search_article_results[$i][$j]['brand_title'] = LinemediaAutoPartsHelper::clearBrand($part['brand_title']);

                    $this->search_article_results[$i][$j]['article'] = LinemediaAutoPartsHelper::clearArticle($part['article']);

                    /*
                     * �� ���������� ������ � 0 ����������� ���� �������� ��������� "���������� ������ � �������" ��� ����. �����������
                     * ������ �� ������ 9075
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
             * ������ ���������� ��������.
             */
            $this->search_article_results = self::getIntersectParts($this->search_article_results);


            if (count($this->search_article_results) == 0) {
                /*
                 * 404
                 */
                $this->result_type = '404';

                /*
                 * ����� ���������� ����������
                 */
                LinemediaAutoDebug::add('No catalogs and no parts found', false, LM_AUTO_DEBUG_WARNING);
            }
        } // if ($this->result_type == 'parts')

        /*
         * �� ����� ������ ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteEnd");
        $result_type = $this->result_type;
        while ($arEvent = $events->Fetch()) {
            /*
             * ���������
             * ������    - ������� ������
             * ������    - ������ ���������, ������� ���� ������ � �������� ����
             * ������    - ��������, ���� �����
             * �������� - ��������, ������� ��� "��� �� ��� �������" (�������� �� emex)
             * �����     - ��� ������
             * ������    - �������������� ���������� � �������
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
         * ���� ����� ���������� �������� ����� ���� ��������
         */
        $this->search_article_results = array_filter($this->search_article_results);

        /*
         * �������� ����������.
         */

        /*
        * ���������� ������ ���� ��������� � ��������� search.results.
        * ��� ����������, ���������� ������ ���������� ������ � ������� �� ��������� �����������
        * �������� ������, �� �������� ��� ���������� � ������� ���������� �����, ���������� ��
        * ��������������� �������� ������ � ������� ���������� (������ �21625)
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
         * ������ �21870
         * ������ �� ���������� ���������� �������� ������, ������� ������ �������� ����������
         * (��������, ��. ��������� autoeuro ������� 21410-1LL0A - �������� ��� ������, ��
         * � ������, ����� � local-database �� ����� �� �������� ���� ��������, �� ��������� ��
         * ���������� ���������� ������������ � ������� � ������� ������ � ���������� ����������)
         *
         **/
        // ���� ���� ������ � ��� ������ - ��������
        if(is_array($this->search_article_results) && $this->result_type == "catalogs") {

            // �22469
            // ������� ������ � �������� � �������� �� �����������
            // ���������� �������� �� �������� ����������
            if(is_array($this->search_article_results['analog_type_N']) &&
                count($this->search_article_results['analog_type_N']) > 0) {
                $this->search_catalog_results = array_merge($this->search_catalog_results, $this->search_article_results['analog_type_N']);
                $this->search_catalog_results = self::getIntersectCatalogs($this->search_catalog_results, $this->type);
            }

//            // ��������� �� ������ ������ �� analog_type_N ...
//            foreach($this->search_article_results['analog_type_N'] as $key_group => $group_array) {
//                // ... ����� �� ������� ������ �� ���������
//                foreach($this->search_catalog_results as $brand_name_catalog => $array_catalog) {
//                    // ���� �������� ������ �� ������ ������� ��������� � ��������� ������ �� ��������
//                    // (�� �������� �� ��������) � ���� ��� ������ ������ �� ���������� ����������
//                    // (is_remote_supplier = 1)
//                    if(strcasecmp($group_array['brand_title'], $brand_name_catalog) != 0
//                        && $group_array['is_remote_supplier'] == 1) {
//                        // ������� ����� ������ ��� ����������� � �������� ���������
//                        $new_search_catalog_results[$group_array['brand_title']] = $group_array;
//                    }
//                }
//            }
//            // ���� ����� ������ �� ������, ���������� ��� � �������� �������� search_catalog_results
//            if (is_array($new_search_catalog_results) && count($new_search_catalog_results) > 0 ) {
//                $this->search_catalog_results = array_merge($this->search_catalog_results, $new_search_catalog_results);
//            }
        }

        /*
         * ����� ���������� ����������
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
     * �������� ������, ���������� �� ������ ����������� ������
     */
    public function getThrownExceptions()
    {
        return $this->exceptions;
    }

    /*
    * �������� ���������� � ������ ��������� ������� ������
    */
    public function getArticleNodeInfo()
    {
        return (array) $this->node_info;
    }

    public function getModificator() {
        return $this->modificator_set;
    }


    /**
     * ��������� ���������� ��������� �� ���������.
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
                     * ��������� extra ��������
                     */
                    $new_extra = $result[$group_id][$hash]['extra'];
                    foreach ($part['extra'] as $k => $v) {
                        if ($new_extra[$k] === $v) {
                            continue;
                        }
                        /*
                         * ���� ��������� ���� � ������ ������� ����, �� �������
                         * array_merge_recursive ������ ������� ��� ��� ��������.
                         */
                        if (!array_key_exists($k, $new_extra) || empty($new_extra[$k])) {
                            $new_extra[$k] = $part['extra'][$k];
                        } elseif (!empty($part['extra'][$k])) {
                            // ivan 20.05.14 #8786
                            // ��� �������� ������ ��������������
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
                    // �������� ������, � ������� ������� ������ ������
                    $hasitems[$hash] = $i;
                }
                $result[$i][$hash] = $part;
            }
        }

        // ������������� �������.
        foreach ($result as $i => $parts) {
            $result[$i] = array_values($parts);
        }

        return $result;
    }

    /**
     * ��������� ���������� ��������� � ������ ���������.
     * @param array $results
     */
    public static function getIntersectCatalogs($results, $type = self::SEARCH_SIMPLE)
    {
        // ���� �� ������� ��� ������ - ���������� ��� ���������
        if($type != self::SEARCH_SIMPLE && $type != self::SEARCH_PARTIAL) {
            return $results;
        }

        /*
         * ��������� �������� �� �������
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
                    // ������ ������ ����� �� ������������ ������� ������� ������
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
                // ������ ������ ����� �� ������������ ������� ������� ������
                $catalog_brands[$brand_key]['extra'] = LinemediaAutoPartsHelper::clearExtra($catalog_brands[$brand_key]['extra']);
            }
        }

        return $catalog_brands;
    }


    /**
     * ����� ���������� ������� �����-�������.
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
            $key = $part['article'] . '|' . $part['brand_title']; // .$part['analog-source'] - ����� ������� ������ ������ ��� ������ �� ��������� �� ?

            /**
             * �19048
             * 12.10.15
             * ioannes
             * ���������� extra
             */
            if(array_key_exists($key, $result)) {
                $result[$key] = array_merge($part,  $result[$key]);
            } else {
                $result[$key] = $part;
            }
            //$result[$key] = $part;
        }
        // ������� ����� ��� �������� ���������� ������
        //$result = array_values($result);

        return $result;
    }


    /**
     * ��������� ���������� �������� ���������.
     *
     * @param array $results
     */
    protected static function getIntersectCatalogTitleResults($results)
    {
        $results = array_filter($results, array('LinemediaAutoSearch', 'intersectCatalogTitleResults'));
        return $results;
    }


    /**
     * ���������� ����������� ������, ����� ������� ���������� �������� (�� ����������).
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

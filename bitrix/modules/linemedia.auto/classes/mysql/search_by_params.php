<?php
require_once('LinemediaProductsTable.php');

IncludeModuleLangFile(__FILE__);

/**
 * class LinemediaAutoSearchByParams is used for searching spares by any parameters
 */
class LinemediaAutoSearchByParams implements LinemediaAutoISearch {

    private $table;
    private $order_split_mode = false;
    private $search_mode;
    private $suppliers_enabled;
    private $back_map;
    private $conditions = array();
    private $wordforms;

    private static $cache;

    private static $SEARCH_LIMIT = 500;

    public function __construct() {

        $this->table = new LinemediaProductsTable();


        $this->order_split_mode = (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y');

        if(is_array(self::$cache['suppliers'])) {

            $this->suppliers_enabled = self::$cache['suppliers']['enabled'];
            $this->back_map = self::$cache['suppliers']['back_map'];
        } else {

            $this->getSuppliersEnabled();
        }

        if(isset(self::$cache['wordforms'])) {
            $this->wordforms = self::$cache['wordforms'];
        } else {
            $this->wordforms = new LinemediaAutoWordForm();
            self::$cache['wordforms'] = $this->wordforms;
        }
    }

    public function setConditions($conditions) {
        $this->conditions = $conditions;
    }

    public function searchLocalDatabaseForPart($part, $multiple = false) {

        $filter = array();

        // нет активных поставщиков!
        if(!is_array($this->suppliers_enabled) || count($this->suppliers_enabled) < 1) {
            return array();
        }

        //$fields = $this->table->getMap();

        $base_fields = LinemediaProductsTable::$BASE_FIELDS;
        foreach($part as $key => $value) {
            // оставляем возможность логики в полях, например >quantity
            $field_name = preg_replace("/[^a-z_]/", "", $key);
            if(in_array($field_name, $base_fields)) {
                $filter[$key] = $value;
            }

        }

        $additional_fields = (array) $part['additional_fields'];
        if(count($additional_fields) > 0) {
            $filter = array_merge($filter, $additional_fields);
        }

        // Показывать ли товары только в наличии.
        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y') {
            $filter['>quantity'] = 1;
        }

        $supplier_exists = $this->prepareSupplierFilter($filter);
        if(!$supplier_exists) {
            return array();
        }

        $this->prepareArticleFilter($filter);
        $this->prepareBrandFilter($filter);

        $sort = (array) $this->conditions['sort'];
        $select = (array) $this->conditions['select'];
        $offset = (int) $this->conditions['offset'];
        $limit = (int) $this->conditions['limit'];

        if(count($select) < 1) {
            $select = array('*');
        } else {
            // обязательно должно быть ID
            if(!in_array('ID', $select)) $select[] = 'ID';
        }

        if($limit < 1) {
            $limit = self::$SEARCH_LIMIT; // не допустим беспредела!
        }

        $params = array(
            'select' => $select,
            'filter' => $filter,
            'order'  => $sort,
            'offset' => $offset,
            'limit'  => $limit,
        );

        $res = $this->table->getList($params);

        $parts = array();
        while($part = $res->fetch()) {

            $part['source'] = 'local-database';

            if($this->order_split_mode) {
                $handle_parts = $this->handlePart($part);
                $parts = array_merge($parts, $handle_parts);
            } else {
                $parts[] = $part;
            }

            if (!$multiple) {
                break;
            }
        }

        // запрос пришел из АПИ - сохраним в файловый кеш, так как сессия недоступна
        if($this->order_split_mode && defined('LM_API_QUERY')) {
            $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/buy_from_api/'));
            foreach ($parts as $part) {
                if(!empty($part['buy_hash'])) {
                    $lmCache->setData($part['buy_hash'], $part);
                }
            }
        }

        return $parts;
    }

    private function handlePart(&$part) {

        $parts = array();

        $supplier_id = $part['supplier_id'];

        // внутренние поставщики, замена товаров
        if(is_array($this->back_map) && array_key_exists($supplier_id, $this->back_map)) {

            // от всех поставщиков найдём цепочки замены
            foreach($this->back_map[$supplier_id] AS $iii => $chain) {

                $new_part = $part;

                //  запишем цепочку
                // первый элемент - запчасть от поставщика в прайсе
                $supplier_obj = new LinemediaAutoSupplier($supplier_id);
                $supplier_obj->ignorePermissions();
                $delivery_time = $supplier_obj->get('delivery_time');
                $new_part['retail_chain'][] = array(
                    'supplier_id' => $supplier_id,
                    'price' => $part['price'],
                    'delivery_time' => $delivery_time,
                    'branch_id' => false,
                    'base_price' => true,
                );


                // пройдём с конца по всем промежуточным филиалам
                $recalc_chain = array_reverse($chain);
                foreach($recalc_chain AS $k => $chain_supplier_id) {

                    // запишем нового поставщика
                    $new_part['supplier_id'] = $chain_supplier_id;

                    // получим объект поставщика
                    $supplier_obj = new LinemediaAutoSupplier($chain_supplier_id);
                    $supplier_obj->ignorePermissions();
                    $branch_id = $supplier_obj->get('branch_owner');


                    // пересчитаем цену
                    $part_obj = new LinemediaAutoPart($new_part['id'], $new_part);
                    $price = new LinemediaAutoPrice($part_obj);
                    $price->setChain(array(
                        'branch_id' => $branch_id
                    ));
                    $price->enableDebugCollection();
                    $new_price = $price->calculate();


                    // в компоненте уже есть пересчёт цены и доставки, поэтому не меняем его в детали, но запишем в цепочку
                    $new_part['price'] = $new_price;
                    $new_part['price_debug'][] = $price->getDebug();
                    // увеличим время доставки
                    $new_part['delivery_time'] += $supplier_obj->get('delivery_time');

                    //  запишем цепочку
                    $new_part['retail_chain'][] = array(
                        'supplier_id' => $chain_supplier_id,
                        'price' => $new_price,
                        'delivery_time' => $new_part['delivery_time'],
                        'branch_id' => $branch_id,
                    );
                }


                $chain_id = md5(json_encode($new_part));
                $new_part['chain_id'] = $chain_id;
                $_SESSION['search_chains'][$chain_id] = array('added' => time(), 'part' => $new_part);

                // запрос пришел из АПИ - сохраним в файловый кеш, так как сессия недоступна
                if(defined('LM_API_QUERY')) {
                    $new_part['chain'] = array('added' => time(), 'part' => $new_part);
                    $new_part['buy_hash'] = $chain_id;
                }

                $parts[] = $new_part;
            } // foreach($this->back_map[$supplier_id] AS $iii => $chain)

        } else {

            $supplier_obj = new LinemediaAutoSupplier($supplier_id);
            $supplier_obj->ignorePermissions();
            $delivery_time = $supplier_obj->get('delivery_time');
            $branch_id = $supplier_obj->get('branch_owner');

            $part['retail_chain'][] = array(
                'supplier_id' => $supplier_id,
                'price' => $part['price'],
                'delivery_time' => $delivery_time,
                'branch_id' => false,
                'base_price' => true,
            );

            // пересчитаем цену
            $part_obj = new LinemediaAutoPart($part['id'], $part);
            $price = new LinemediaAutoPrice($part_obj);
            $price->setChain(array(
                'branch_id' => $branch_id
            ));
            $price->enableDebugCollection();
            $new_price = $price->calculate();


            $part['retail_chain'][] = array(
                'supplier_id' => $supplier_id,
                'price' => $new_price,
                'delivery_time' => $delivery_time,
                'branch_id' => $branch_id,
            );
            $part['price'] = $new_price;
            $part['delivery_time'] = $delivery_time;

            $chain_id = md5(json_encode($part));
            $part['chain_id'] = $chain_id;//md5(json_encode($part));
            $_SESSION['search_chains'][$chain_id] = array('added' => time(), 'part' => $part);

            // запрос пришел из АПИ - сохраним в файловый кеш, так как сессия недоступна
            if(defined('LM_API_QUERY')) {
                $part['chain'] = array('added' => time(), 'part' => $part);
                $part['buy_hash'] = $chain_id;
            }

            $parts[] = $part;

        } // if(is_array($this->back_map) && array_key_exists($supplier_id, $this->back_map))

        return $parts;
    }

    private function prepareArticleFilter(&$filter) {

        $articles = array();
        if(array_key_exists('article', $filter)) {
            if(!is_array($filter['article'])) {
                if(!empty($filter['article'])) {
                    $articles = array($filter['article']);
                }
            } else {
                $articles = $filter['article'];
            }
        }
        unset($filter['article']);
        // group search
        $group_search = false;
        foreach($articles as $article) {
            if(strpos($article, '|') !== false) {
                $group_search = true;
                break;
            }
        }

        if($group_search) {
            $group_filter = array();
            foreach($articles as $article) {
                $query_parts = explode('|', $article);
                $article_word = trim($query_parts[0]);
                $brand_title_words = array_slice($query_parts, 1);
                $filter_item = array();
                $filter_item[] = array('=article' => $article_word);
                if(count($brand_title_words) > 0) {
                    $brand_filter = array('=brand_title' => $brand_title_words);
                    $this->prepareBrandFilter($brand_filter);
                    $filter_item[] = $brand_filter;
                }
                $group_filter[] = $filter_item;
            }
            $group_filter['LOGIC'] = 'OR';
            $filter = array_merge($filter, $group_filter);
        } else {
            $filter['=article'] = $articles;
        }

//        if(count($articles) > 0) {
//            $lead_zero = array();
//            foreach($articles as &$article) {
//                $lead_zero[] = '0' . $article;
//            }
//            $articles = array_merge($articles, $lead_zero);
//        }
//        $filter['article'] = $articles;
    }

    private function prepareBrandFilter(&$filter) {

        $brand_filter = array();
        if(array_key_exists('brand_title', $filter)) {
            if(!is_array($filter['brand_title'])) {
                if(!empty($filter['brand_title'])) {
                    $brand_filter = array($filter['brand_title']);
                }
            } else {
                $brand_filter = $filter['brand_title'];
            }
        }
        unset($filter['brand_title']);
        if(count($brand_filter) > 0) {

            $filter['=brand_title'] = $this->wordforms->makeFilter($brand_filter);
        }
    }

    /**
     * Обработаем фильтр по поставщикам.
     * Если задан внешний фильтр, то предполагаем, что это либо скаляр, либо список - массив
     * @param $filter
     * @return bool
     */
    private function prepareSupplierFilter(&$filter) {

        $supplier_filter = array();
        if(array_key_exists('supplier_id', $filter)) {
            if(!is_array($filter['supplier_id'])) {
                if(!empty($filter['supplier_id'])) {
                    $supplier_filter = array($filter['supplier_id']);
                }
            } else {
                $supplier_filter = $filter['supplier_id'];
            }
        }
        unset($filter['supplier_id']);
        if(count($supplier_filter) > 0) {
            $filter['=supplier_id'] = array_intersect($supplier_filter, $this->suppliers_enabled);
        } else {
            $filter['=supplier_id'] = $this->suppliers_enabled;
        }

        return (count($filter['=supplier_id']) > 0);
    }

    private function getSuppliersEnabled() {

        $suppliers = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', 'PROPERTY_api' => false), false, false, array('ID', 'PROPERTY_supplier_id', 'PROPERTY_internal_supplier'));

        // поставщики, доступные для поиска
        foreach($suppliers as $supplier) {

            if($this->order_split_mode) {

                if($supplier['PROPERTY_INTERNAL_SUPPLIER_VALUE'] > 0) {

                    $branch_owner = $supplier['PROPS']['branch_owner']['VALUE'];
                    $supplier_children = LinemediaAutoBranchesInternalSupplier::getSupplierChildren($supplier['PROPERTY_SUPPLIER_ID_VALUE'], $branch_owner);
                    $supplier_children_ids = LinemediaAutoBranchesInternalSupplier::getSupplierChildrenIds($supplier_children['chains']);

                    // добавим в запрос
                    foreach($supplier_children_ids AS $sup_id) {
                        $this->suppliers_enabled[] = $sup_id;
                    }
                    // чтобы нарисовать back-map
                    $suppliers_children['chains'][$supplier['PROPERTY_SUPPLIER_ID_VALUE']] = $supplier_children;

                } else {
                    // ID внутренних поставщиков мы НЕ добавляем
                    $this->suppliers_enabled[] = $supplier['PROPERTY_SUPPLIER_ID_VALUE'];
                }

            } else {
                $this->suppliers_enabled[] = $supplier['PROPERTY_SUPPLIER_ID_VALUE'];
            }
        }
        self::$cache['suppliers']['enabled'] = $this->suppliers_enabled;

        if($this->order_split_mode) {
            $this->back_map = LinemediaAutoBranchesInternalSupplier::getBackMap($suppliers_children);
            self::$cache['suppliers']['back_map'] = $this->back_map;
        }
    }
}
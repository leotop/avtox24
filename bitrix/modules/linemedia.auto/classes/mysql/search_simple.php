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
 * Search through database
 */
class LinemediaAutoSearchSimple implements LinemediaAutoISearch
{
	/**
	* Статический кеш
	*/
	static $cache;

    /**
     * Поиск запчасти по локальной базе данных
     */
    public function searchLocalDatabaseForPart($part, $multiple = false)
    {
        if(!isset(self::$cache['db'])) {
	        self::$cache['db'] = $database = new LinemediaAutoDatabase();
        } else {
	        $database = self::$cache['db'];
        }

        /*
         * Основные критерии поиска
         */
        $article         = LinemediaAutoPartsHelper::clearArticle($part['article']);
        $id              = (int) $part['id'];
        $brand_title     = (string) $part['brand_title'];
        $supplier_id     = (string) $part['supplier_id'];

        if(strlen($part['article']) > 0 && strlen($article) == 0) {
            return;
        }
        
        
        $brand_title = trim($brand_title);
        $supplier_id = trim($supplier_id);
        
        /*
         * Внешние данные.
         */
        $extra = (array) $part['extra'];
        
        /*
         * Дополнительные критерии поиска, требующие дополнительного поиска по бренду
         */
        $additional_fields = (array) $part['additional_fields'];
        
        /*
         * Составляем запрос
         */
        $where = array();
        
        // Показывать ли товары только в наличии.
        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y') {
            $where []= '`quantity` > 0';
        }
        
        if ($id > 0) {
            $where []= '`id` = ' . $database->ForSql($id);
        }
        
        if ($brand_title != '') {
        	/*
        	 * Добавим словоформы.
        	 */
        	if(!isset(self::$cache['wordforms_obj'])) {
		        self::$cache['wordforms_obj'] = $wordforms = new LinemediaAutoWordForm();
	        } else {
		        $wordforms = self::$cache['wordforms_obj'];
	        }
	        
	        
	        if(!isset(self::$cache['wordforms'][$brand_title])) {
	        	self::$cache['wordforms'][$brand_title] = $brand_titles = $wordforms->getBrandWordforms($brand_title);
	        	$brand_titles = array_merge_recursive($brand_titles, (array)$wordforms->getBrandGroup($brand_title));
	        	
	        } else {
		        $brand_titles = self::$cache['wordforms'][$brand_title];
	        }


        	if (count($brand_titles) > 0) {
        	    $brand_titles[]= $brand_title;
        	    $brand_titles = array_unique($brand_titles);
        	    
        	    $brand_titles = array_map('strval', $brand_titles);
        	    $brand_titles = array_map('strtoupper', $brand_titles);
        		$brand_titles = array_map(array($database, 'ForSql'), $brand_titles);
        		$brand_titles = "'" . join("', '", $brand_titles) . "'";
            	$where[] = "UPPER(`brand_title`) IN ($brand_titles)";
            } else {
                $brand_title = strtoupper((string) $brand_title);
	            $where[] = "UPPER(`brand_title`) = '" . $database->ForSql($brand_title) . "'";
            }
        }
        
        if ($supplier_id != '') {
            $where[] = "`supplier_id` = '" . $database->ForSql($supplier_id) . "'";
        }
        
        if ($article != '') {
            /*
             * Удаление / Добавление ведущего нуля.
             */
            if (substr($article, 0, 1) == '0') {
	            $where[] = "(`article` = '" . $database->ForSql($article) . "' OR `article` = '" . $database->ForSql(substr($article, 1)) . "')";
            } else {
	            $where[] = "(`article` = '" . $database->ForSql($article) . "' OR `article` = '0" . $database->ForSql($article) . "')";
            }
        }
        // OnLocalSearchConditionsArray event
        
                
        
        /*
         * Убираем неактивных поставщиков и поставщиков с API-подключением.
         */
        if(!isset(self::$cache['active_suppliers'])) {
        	/*$obCache = new CPHPCache();
	        $life_time = 10 * 60;
	        $cache_id = 'active_suppliers';
	        if ($obCache->InitCache($life_time, $cache_id, '/')) {
	            $arSupplierIDs = $obCache->GetVars();
	        } else {*/
	            $arSupplierIDs  = array();
	            
	            /**
		        * Если среди деталей встретятся товары от указанных поставщиков, надо оригинал удалить
		        * но создать копии от указанных поставщиков с пересчётом цены и доставки согласно цепочке преобразований
		        */
	            $suppliers_children = array();
	            $arSuppliers    = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', 'PROPERTY_api' => false), false, false, array('ID', 'PROPERTY_supplier_id', 'PROPERTY_internal_supplier'));
	            foreach ($arSuppliers as $arSupplier) {
	            	
	            	// внутренний поставщик 
	            	// TODO: в модуль филиалов
	            	if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
		            	CModule::IncludeModule('linemedia.autobranches');
		            	
		            	if($arSupplier['PROPERTY_INTERNAL_SUPPLIER_VALUE'] > 0) {
			            	$branch_owner = $arSupplier['PROPS']['branch_owner']['VALUE'];
			            	
			            	
			            	$internal_supplier = new LinemediaAutoBranchesInternalSupplier($arSupplier['PROPERTY_SUPPLIER_ID_VALUE']);
			            	$supplier_children = LinemediaAutoBranchesInternalSupplier::getSupplierChildren($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'], $branch_owner);
			            	$supplier_children_ids = LinemediaAutoBranchesInternalSupplier::getSupplierChildrenIds($supplier_children['chains']);
			            	
			            	// добавим в запрос
			            	foreach($supplier_children_ids AS $sup_id) {
			            		$arSupplierIDs []= "'".strval($sup_id)."'";
			            	}
			            	
			            	
			            	// чтобы нарисовать back-map
			            	$suppliers_children['chains'][$arSupplier['PROPERTY_SUPPLIER_ID_VALUE']] = $supplier_children;
		            	} else {
			            	// ID внутренних поставщиков мы НЕ добавляем
			            	$arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
		            	}
	            	} else {
	            		$arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
	                }
	                
	            }
	            
	            /**
	            * Обратная карта поставщиков для умножения запчастей
	            */
	            if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
	            	CModule::IncludeModule('linemedia.autobranches');
	            	$back_map = LinemediaAutoBranchesInternalSupplier::getBackMap($suppliers_children);
	            	self::$cache['back_map'] = $back_map;
	            }
	            
            	
	            /*if ($obCache->StartDataCache()) {
	                $obCache->EndDataCache($arSupplierIDs);
	            }
	        }*/
	        $arSupplierIDs = array_unique($arSupplierIDs);
	        
	        self::$cache['active_suppliers'] = $arSupplierIDs;
        } else {
	        $arSupplierIDs = self::$cache['active_suppliers'];
	        $back_map = self::$cache['back_map'];
        }
        
        if (!empty($arSupplierIDs)) {
            $where []= '`supplier_id` IN (' . implode(', ', $arSupplierIDs) . ')';
        } else {
	        // нет активных поставщиков!
	        return array();
        }
        
        
        /*
         * Дополнительные критерии поиска.
         */
        if (count($additional_fields) > 0) {
            foreach ($additional_fields as $col => $val) {
                $operator = '=';
                if (in_array($col[0], array('=', '>', '<'))) {
                    $operator = $col[0];
                }
                $col = '`' . $database->ForSql($col) . '`';
                $val = "'" . $database->ForSql($val) . "'";
                $where []= "$col $operator $val";
            }
        }
        
        
        /*
         * Должен быть задан хоть один фильтр, количества и активных поставщиков.
         */
        if (count($where) <= 1) {
        	return false;
        }
        
        
        /*
         * Запрос.
         */
        $sql = 'SELECT * FROM `b_lm_products` WHERE ' . join(' AND ', $where);
          
        
        
        try {
            $res = $database->Query($sql);
        } catch (Exception $e) {
            throw $e;
        }


        $parts = array();
        $back_map = (array) $back_map;

        while ($part = $res->Fetch()) {
            /*
             * Источник поступления ин-ции о запчасти - локальная БД
             */
            $part['source'] = 'local-database';

            // внутренние поставщики, замена товаров
            if(array_key_exists($part['supplier_id'], $back_map)) {
            	// от всех поставщиков найдём цепочки замены
                foreach($back_map[$part['supplier_id']] AS $iii => $chain) {

                	$new_part = $part;

                	//  запишем цепочку
                	// первый элемент - запчасть от поставщика в прайсе
                	$supplier_obj = new LinemediaAutoSupplier($part['supplier_id']);
		            $supplier_obj->ignorePermissions();
		            $delivery_time = $supplier_obj->get('delivery_time');
	                $new_part['retail_chain'][] = array(
                		'supplier_id' => $part['supplier_id'],
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
                }
                
                
                // мой внешний поставщик
            } else {
            	
            	if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
	            	$supplier_obj = new LinemediaAutoSupplier($part['supplier_id']);
		            $supplier_obj->ignorePermissions();
		            $delivery_time = $supplier_obj->get('delivery_time');
		            $branch_id = $supplier_obj->get('branch_owner');
		            
	            	$part['retail_chain'][] = array(
	            		'supplier_id' => $part['supplier_id'],
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
	            		'supplier_id' => $part['supplier_id'],
	            		'price' => $new_price,
	            		'delivery_time' => $delivery_time,
	            		'branch_id' => $branch_id,
	            	);
	            	$part['price'] = $new_price;
	            	$part['delivery_time'] = $delivery_time;
            	}
            	
            	$chain_id = md5(json_encode($part));
                $part['chain_id'] = $chain_id;//md5(json_encode($part));
                $_SESSION['search_chains'][$chain_id] = array('added' => time(), 'part' => $part);

                // запрос пришел из АПИ - сохраним в файловый кеш, так как сессия недоступна
                if(defined('LM_API_QUERY')) {
                    $part['chain'] = array('added' => time(), 'part' => $part);
                    $part['buy_hash'] = $chain_id;
                }

            	$parts []= $part;
            	
            	if (!$multiple) {
            		break;
            	}
            }
        }
        
        if (!$multiple) {

            if($parts[0]) {
                // запрос пришел из АПИ - сохраним в файловый кеш, так как сессия недоступна
                if(defined('LM_API_QUERY')) {
                    if(!empty($parts[0]['buy_hash'])) {
                        $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/buy_from_api/'));
                        $lmCache->setData($parts[0]['buy_hash'], $parts[0]);
                    }
                }

                return $parts[0];
            }
            return false;
        }

        // запрос пришел из АПИ - сохраним в файловый кеш, так как сессия недоступна
        if(defined('LM_API_QUERY')) {
            $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/buy_from_api/'));
            foreach ($parts as $part) {
                if(!empty($part['buy_hash'])) {
                    $lmCache->setData($part['buy_hash'], $part);
                }
            }
        }

        return $parts;
    }
}

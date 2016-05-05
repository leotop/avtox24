<?php

/**
 * Linemedia Autoportal
 * Main module
 * group search class
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__); 
 
/**
 * class LinemediaAutoSearchGroup is used for searching spares by group conditions
 * like united by articles, brands, suppliers (optional)
 */
class LinemediaAutoSearchGroup implements LinemediaAutoISearch{

	static $cache = array();
	
	/**
	 * array of suppliers ID
	 * @var array
	 */
	private $suppliersID = array();
	
	/**
	 * set suppliers ID
	 * @param array $suppliersID
	 */
	public function setSuppliers(array $suppliersID)
	{
		$this->suppliersID = $suppliersID;
	}
	
	
    /**
     * ����� �������� �� ��������� ���� ������.
     */
    public function searchLocalDatabaseForPart($part, $multiple = false)
    {
        try {
            $database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }

        /**
         * ����� �� ��������� ������� �� �� �������
         */
        $is_external_id = false;

        /*
         * �������� �������� ������.
         */
        $part_arts	= explode(',', $part['article']);
        $parts		= array();
        foreach ($part_arts as $part_art) {

            $items = explode('|', $part_art);

        	$parts []= array(
        		'article' 	=> LinemediaAutoPartsHelper::clearArticle($items[0]),
        		'brands' 	=> array_filter(array(trim($items[1])))
 			);

            /*
             * �������� ����������� �������� ������� �� ��������, �� ��������
             * �������������� �����
             */
            if(count($items) > 2) {
                $parts['external_id'] = $items[2];
            }
        }
        
        /*
         * ���������� ������.
         */
        $where = array();
        
        // ���������� �� ������ ������ � �������.
        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y') {
            $where []= '`quantity` > 0';
        }
        
        $parts_where = array();
        foreach ($parts as &$part) {

        	$article	 = $database->ForSql($part['article']);
        	$brand_title = reset($part['brands']);
        	
        	if ($brand_title != '') {
        		
        		/*
        		 * ������� ����������.
        		 */
        		$wordforms = new LinemediaAutoWordForm();
        		$brand_titles = $wordforms->getBrandWordforms($brand_title);
        		if (count($brand_titles) > 0) {
        			$brand_titles []= $brand_title;
        			$brand_titles = array_unique($brand_titles);
        			$brand_titles = array_map('strval', $brand_titles);
        			$brand_titles = array_map('strtoupper', $brand_titles);
        			$brand_titles = array_unique($brand_titles);
                    $part['brand_forms'] = $brand_titles;
        			$brand_titles = array_map(array($database, 'ForSql'), $brand_titles);
        			$brand_titles = "'" . join("', '", $brand_titles) . "'";
        			$parts_where []= "(`article` = '$article' AND UPPER(`brand_title`) IN ($brand_titles))";
        		} else {
        			$brand_title = strtoupper((string) $brand_title);
        			$parts_where []= "(`article` = '$article' AND UPPER(`brand_title`) = '" . $database->ForSql($brand_title) . "')";
        		}
        	} else {
        		$parts_where []= "(`article` = '$article')";
        	}
        } // foreach ($parts as $part)
        
       $parts_where = count($parts_where) ? "(".join(' OR ', $parts_where).")" : false;
        if($parts_where) {
        	$where[] = $parts_where;
        }
              
        /*
         * ������� ���������� ����������� � ����������� � API-������������.
         */
        //$obCache = new CPHPCache();
        //$life_time = 600;
        //$cache_id = 'active_suppliers';
        //if ($obCache->InitCache($life_time, $cache_id, '/')) {
        //    $arSupplierIDs = $obCache->GetVars();
        //} else {
            $arSupplierIDs  = array();
            $back_map = array();
            $arSuppliers    = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', 'PROPERTY_api' => false), false, false, array('ID', 'PROPERTY_supplier_id', 'PROPERTY_internal_supplier'));
            foreach ($arSuppliers as $arSupplier) {
            	
            	
            	if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
	            	if($arSupplier['PROPERTY_INTERNAL_SUPPLIER_VALUE'] > 0) {
		            	$branch_owner = $arSupplier['PROPS']['branch_owner']['VALUE'];
		            	
		            	
		            	$internal_supplier = new LinemediaAutoBranchesInternalSupplier($arSupplier['PROPERTY_SUPPLIER_ID_VALUE']);
		            	$supplier_children = LinemediaAutoBranchesInternalSupplier::getSupplierChildren($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'], $branch_owner);
		            	$supplier_children_ids = LinemediaAutoBranchesInternalSupplier::getSupplierChildrenIds($supplier_children['chains']);
		            	
		            	// ������� � ������
		            	foreach($supplier_children_ids AS $sup_id) {
		            		$arSupplierIDs []= "'".strval($sup_id)."'";
		            	}
		            	
		            	
		            	// ����� ���������� back-map
		            	$suppliers_children['chains'][$arSupplier['PROPERTY_SUPPLIER_ID_VALUE']] = $supplier_children;
	            	} else {
		            	// ID ���������� ����������� �� �� ���������
		            	$arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
	            	}
            	} else {
            		$arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
                }
            }
            
            /**
            * �������� ����� ����������� ��� ��������� ���������
            */
            if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
            	$back_map = LinemediaAutoBranchesInternalSupplier::getBackMap($suppliers_children);
            	self::$cache['back_map'] = $back_map;
            }
            //if ($obCache->StartDataCache()) {
            //    $obCache->EndDataCache($arSupplierIDs);
            //}
        //}
        
        
        foreach ($this->suppliersID as &$supplier) {
        	$supplier = "'" . (string) $supplier . "'";
        }
        
        if (!empty($this->suppliersID)) {
        	$arSupplierIDs = array_intersect($this->suppliersID, $arSupplierIDs);
        }
        
        if (!empty($arSupplierIDs)) {
            $where []= '`supplier_id` IN (' . implode(', ', $arSupplierIDs) . ')';
        } else {
	        // ��� �������� �����������!
	        return array();
        }
        
    
        /*
         * ������ ���� ����� ���� ���� ������, ����� �������� �����������.
         */
        if (count($where) < 1) {
            return false;
        }
                
        /*
         * ������.
         */
        $sql = 'SELECT * FROM `b_lm_products` WHERE ' . join(' AND ', $where);

        try {
            $res = $database->Query($sql);
        } catch (Exception $e) {
            throw $e;
        }

        /*
         * �������� ������, �� ������� ������������� �����.
         */
        $searched_parts = $parts;

        /*
         * �� ���� ���� �������� ��� �����?
         */
        $parts = array();
        while ($part = $res->Fetch()) {
            /*
             * �������� ����������� ��-��� � �������� - ��������� ��
             */
            $part['data-source'] = 'local-database';

            // ���������� ����������, ������ �������
            if(array_key_exists($part['supplier_id'], $back_map)) {

            	// �� ���� ����������� ����� ������� ������
                foreach($back_map[$part['supplier_id']] AS $iii => $chain) {

                	$new_part = $part;

                	//  ������� �������
                	// ������ ������� - �������� �� ���������� � ������
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
                	
                	
                	// ������ � ����� �� ���� ������������� ��������
	                $recalc_chain = array_reverse($chain);
	                foreach($recalc_chain AS $k => $chain_supplier_id) {
		                
		                // ������� ������ ����������
		                $new_part['supplier_id'] = $chain_supplier_id;
		                
		                // ������� ������ ����������
		                $supplier_obj = new LinemediaAutoSupplier($chain_supplier_id);
			            $supplier_obj->ignorePermissions();
			            $branch_id = $supplier_obj->get('branch_owner');

		                
		                // ����������� ����
		                $part_obj = new LinemediaAutoPart($new_part['id'], $new_part);
		                $price = new LinemediaAutoPrice($part_obj);
		                $price->setChain(array(
		                	'branch_id' => $branch_id
		                ));
		                $price->enableDebugCollection();
			            $new_price = $price->calculate();
			            
			            
			            // � ���������� ��� ���� �������� ���� � ��������, ������� �� ������ ��� � ������, �� ������� � �������
			            
		                $new_part['price'] = $new_price;
		                $new_part['price_debug'][] = $price->getDebug();
		                // �������� ����� ��������
		                $new_part['delivery_time'] += $supplier_obj->get('delivery_time');
	                
		                //  ������� �������
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

                    // ������ ������ �� ��� - ������� ������� ��� ���������� � �������� ���, ��� ��� ������ ����������
                    if(defined('LM_API_QUERY')) {
                        $new_part['chain'] = array('added' => time(), 'part' => $new_part);
                        $new_part['buy_hash'] = $chain_id;
                    }

	                $parts[] = $new_part;
                }
                
                
                // ��� ������� ���������
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
	            	
	            	
	            	// ����������� ����
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
                $part['chain_id'] = $chain_id;
                $_SESSION['search_chains'][$chain_id] = array('added' => time(), 'part' => $part);

                // ������ ������ �� ��� - ������� ������� ��� ���������� � �������� ���, ��� ��� ������ ����������
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

        $parts = self::applyWordFormToSearchOutcome($parts);

        // ������ ������ �� ��� - �������� � �������� ���, ��� ��� ������ ����������
        if(defined('LM_API_QUERY')) {

            $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/buy_from_api/'));

            foreach ($parts as $part) {
                $lmCache->setData($part['buy_hash'], $part);
            }
        }

        return $parts;
    }
	
    
    /**
     * apply wordform to search outcome
     * @param array $spares
     * @throws \Exception
     * @return array
     */
    public static function applyWordFormToSearchOutcome(array $spares) {

    	$wordforms = new \LinemediaAutoWordForm();
    	foreach ($spares as &$spare) {
    		$wordform = $wordforms->getBrandGroup($spare['brand_title']);
    		if (!empty($wordform)) {
    			$spare['original_brand_title'] = $spare['brand_title'];
    			$spare['brand_title'] = $wordform;
    		}
    	}
    	return $spares;
    }
    
}

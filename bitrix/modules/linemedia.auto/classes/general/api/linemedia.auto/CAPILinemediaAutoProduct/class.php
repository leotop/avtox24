<?php

/**
 * Linemedia API
 * API module
 * Product class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
 * �������� ����� ������.
 */
class CAPILinemediaAutoProduct extends CAPIFrame
{

	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * ���������� ���������.
	 * 
	 * @param array $products
	 * @param boolean $update_if_exists - ���� ��� ���� ����� �������� (�������, �����, ���������), �� ������ �������� ������
	 */
    public function LinemediaAutoProduct_Add($products = array(), $update_if_exists = false, $unique_fields = array())
    {
    	/*
    	 * �������� ���� ������� � �������.
    	 */
    	$this->checkPermission(__METHOD__);
		
		$i = 0;
    	foreach ($products as $part_data) {
    		
    		if (isset($part_data['article']) && empty($part_data['original_article'])) {
	    		$part_data['original_article'] = $part_data['article'];
    		}

    		$part_data['article'] = LinemediaAutoPartsHelper::clearArticle($part_data['original_article']);

    		// �������������� ���������������� ����.
    		if (!empty($part_data['CUSTOM_FIELDS'])) {
    			$this->decodeArray($part_data['CUSTOM_FIELDS']);
    			foreach ($part_data['CUSTOM_FIELDS'] as $code => $field) {
    				$part_data[$code] = $field;
    			}
    			unset($part_data['CUSTOM_FIELDS']);
    		}
			
    		/*
			 * ������ ������ ��������.
			 */
			try {
				// ���� ��� ���� ����� �������� (�������, �����, ���������), �� ������ �������� ������.
				$search_arr = array(
					'article' 		=> $part_data['article'],
					'brand_title' 	=> $part_data['brand_title'],
					'supplier_id' 	=> $part_data['supplier_id'],
				);
				
				// ����� ������� �������������� �������� ������������ ������.
				if (!empty($unique_fields)) {
					$unique_fields = (array) $unique_fields;
					foreach ($unique_fields as $unique_field) {
						$unique_field = (string) $unique_field;
						$search_arr[$unique_field] = $part_data[$unique_field];
					}
				}

				if ($update_if_exists && $product = CAPILinemediaAutoProduct::LinemediaAutoProduct_getProducts($search_arr)) {						
			    	$part = new LinemediaAutoPart($product["0"]['id']);
			    	$part->load();
			    } else {
			    	// ������� ����������.
				    $part = new LinemediaAutoPart(false, $part_data);
			    }
				
			    foreach ($part_data as $k => $v) {
				    $part->set($k, $v);
			    }
				
			    $id = $part->save();
			} catch (Exception $e) {
				// $this->error($e->GetMessage());
				/*if ($i == 0) {
					file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log_failed_add_product.txt', '');
				}
			   
				$f_prods  = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/log_failed_add_product.txt');
				$f_prods .= $part_data['title'].', ';
				file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log_failed_add_product.txt', $f_prods);*/
			}
			$i = 1;
		}		        
		
		return (int) $id;
    }
    
    
    /**
	 * ���������� ���������.
	 * 
	 * @param array $arFilter
	 * @param array $arUpdate
	 */
    public function LinemediaAutoProduct_Update($arFilter = array(), $arUpdate = array())
    {
    	/*
    	 * �������� ���� ������� � �������.
    	 */
    	$this->checkPermission(__METHOD__);
    	$this->decodeArray($arFilter);
    	$this->decodeArray($arUpdate);
		
		//���� $filter �� ������ - ����� ������ ���������� ������� ���� � �����
		
		foreach ($arFilter as $filter) {
			if(is_array($filter)) {
				$arFilterNew[$filter["CODE"]] = $filter["VALUE"]; 
			} else {
				$arFilterNew = $arFilter;
			}
		}
		
    	$search = new LinemediaAutoSearchSimple();
		$products = CAPILinemediaAutoProduct::LinemediaAutoProduct_getProducts($arFilterNew);
		
    	foreach ($products as $part) {
    		/*
			 * ������ ������ ��������.
			 */
			try {			
				$part = new LinemediaAutoPart($part['id']);						
				$part->load();
				
				foreach($arUpdate AS $k => $v) {
					$part->set($k, $v);
				}

				/*foreach ($arUpdate as $update) {
					$arUpdateNew[$update["CODE"]] = $update["VALUE"];
				}*/
				
				/*foreach ($arUpdateNew as $k => $v) {
					$part->set($k, $v);
				}*/

			    $id = $part->save();
			} catch (Exception $e) {
			    $this->error($e->GetMessage());
			}
		}
		
		return count($products);
    }
    
    
    /**
	 * �������� ���������.
	 * 
	 * @param array $filters
	 */
    public function LinemediaAutoProduct_Delete($filters = array())
    {
    	/*
    	* �������� ���� ������� � �������
    	*/
    	$this->checkPermission(__METHOD__);

    	$database = new LinemediaAutoDatabase;

    	/*
         * �������� ���������������� ����.
         */
        $lmfields = new LinemediaAutoCustomFields();
        $custom_fields = $lmfields->getFields();
    	
    	$fields = array();
    	foreach ($custom_fields as $f) {
	    	$fields []= $f['code'];
    	}
    	$fields = array_merge($fields, array('id', 'title', 'article', 'original_article', 'brand_title', 'price', 'quantity', 'group_id', 'weight', 'supplier_id', 'modified'));
    	
    	$where = array();

        $code_filter = array();

        foreach($filters as $filter) {
            if (in_array($filter['CODE'], $fields)) {
                $code_filter[$filter['CODE']][] = $filter['VALUE'];
            }
        }

        foreach($code_filter as $code => $value) {
            if(count($value) == 1) {
                $where [] = "{$code} = '" . $database->ForSQL($value[0]) . "'";
            } else if(count($value) > 1) {
                $sql_arr = array();
                foreach($value as $val) {
                    $sql_arr[] = "'" . $database->ForSQL($val) . "'";
                }
                $where [] = "{$code} IN (" . join(', ', $sql_arr) . ")";
            }
        }

//        foreach($filters as $filter) {
//            if (in_array($filter['CODE'], $fields)) {
//                $where []= "{$filter['CODE']} = '" . $database->ForSQL($filter['VALUE']) . "'";
//            }
//        }
    	
    	if (count($where) == 0) {
    		throw new Exception('no filters');
    	}
    	$where_str = join(' AND ', $where);
    	
        $sql = "DELETE FROM `b_lm_products` WHERE $where_str";
        
    	try {
            $database->Query($sql);
        } catch (Exception $e) {
            $this->error('Error delete parts ' . $e->GetMessage());
        }
		return true;
    }
	
    
	/**
	 * ������� ������� �������� �������.
	 * 
	 * @param array $filters
	 */
	public function LinemediaAutoProduct_getProducts($filters)
	{
		$database = new LinemediaAutoDatabase();

		/*
         * �������� ���������������� ����.
         */
        $lmfields = new LinemediaAutoCustomFields();
        $custom_fields = $lmfields->getFields();
    	
    	$fields = array();
    	foreach ($custom_fields as $f) {
	    	$fields []= $f['code'];
    	}
    	$fields = array_merge($fields, array('id', 'title', 'article', 'original_article', 'brand_title', 'price', 'quantity', 'group_id', 'weight', 'supplier_id', 'modified'));
    	
    	$where = array();

        if(is_array(array_shift(array_values($filters)))) {
            foreach($filters as $filter) {
                if (in_array($filter['CODE'], $fields)) {
                    $where []= "{$filter['CODE']} = '" . $database->ForSQL($filter['VALUE']) . "'";
                }
            }
        } else {
            foreach($filters as $key => $value) {
                if (in_array($key, $fields)) {
                    $where []= "{$key} = '" . $database->ForSQL($value) . "'";
                }
            }
        }

    	if (count($where) == 0) {
    		throw new Exception('no filters');
    	}
    	$where_str = join(' AND ', $where);
    	
        $sql = "SELECT `id` FROM `b_lm_products` WHERE $where_str";

        try {
            $res = $database->Query($sql);
        } catch (Exception $e) {
            throw $e;
        }
		
		$parts = array();
		while ($part = $res->Fetch()) {
			// �������� ����������� ���������� � �������� - ��������� ��.
			$part['source'] = 'local-database';
			$parts[] = $part;    
        }
        
		if (is_array($parts) && !empty($parts)) {
			return $parts;
		} else {
			return false;
		}
	}
    
} 

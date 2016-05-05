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
 * Основной класс модуля.
 */
class CAPILinemediaAutoProduct extends CAPIFrame
{

	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * Добавление запчастей.
	 * 
	 * @param array $products
	 * @param boolean $update_if_exists - если уже есть такая запчасть (артикул, бренд, поставщик), то просто обновить данные
	 */
    public function LinemediaAutoProduct_Add($products = array(), $update_if_exists = false, $unique_fields = array())
    {
    	/*
    	 * Проверка прав доступа к функции.
    	 */
    	$this->checkPermission(__METHOD__);
		
		$i = 0;
    	foreach ($products as $part_data) {
    		
    		if (isset($part_data['article']) && empty($part_data['original_article'])) {
	    		$part_data['original_article'] = $part_data['article'];
    		}

    		$part_data['article'] = LinemediaAutoPartsHelper::clearArticle($part_data['original_article']);

    		// Дополнительные пользовательские поля.
    		if (!empty($part_data['CUSTOM_FIELDS'])) {
    			$this->decodeArray($part_data['CUSTOM_FIELDS']);
    			foreach ($part_data['CUSTOM_FIELDS'] as $code => $field) {
    				$part_data[$code] = $field;
    			}
    			unset($part_data['CUSTOM_FIELDS']);
    		}
			
    		/*
			 * Создаём объект запчасти.
			 */
			try {
				// Если уже есть такая запчасть (артикул, бренд, поставщик), то просто обновить данные.
				$search_arr = array(
					'article' 		=> $part_data['article'],
					'brand_title' 	=> $part_data['brand_title'],
					'supplier_id' 	=> $part_data['supplier_id'],
				);
				
				// Можно указать дополнительные критерии уникальности товара.
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
			    	// Простое добавление.
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
	 * Обновление запчастей.
	 * 
	 * @param array $arFilter
	 * @param array $arUpdate
	 */
    public function LinemediaAutoProduct_Update($arFilter = array(), $arUpdate = array())
    {
    	/*
    	 * Проверка прав доступа к функции.
    	 */
    	$this->checkPermission(__METHOD__);
    	$this->decodeArray($arFilter);
    	$this->decodeArray($arUpdate);
		
		//Если $filter не массив - тогда просто приравняем массивы друг к другу
		
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
			 * Создаём объект запчасти.
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
	 * Удаление запчастей.
	 * 
	 * @param array $filters
	 */
    public function LinemediaAutoProduct_Delete($filters = array())
    {
    	/*
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);

    	$database = new LinemediaAutoDatabase;

    	/*
         * Получаем пользовательские поля.
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
	 * Выборка деталей согласно фильтру.
	 * 
	 * @param array $filters
	 */
	public function LinemediaAutoProduct_getProducts($filters)
	{
		$database = new LinemediaAutoDatabase();

		/*
         * Получаем пользовательские поля.
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
			// Источник поступления информации о запчасти - локальная БД.
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

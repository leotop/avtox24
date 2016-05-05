<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Request baskets class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
 * Requests
 */
class LinemediaAutoSuppliersRequestBasket
{
    const TABLE = 'b_lm_suppliers_requests_baskets';


    /**
     * Список заявок.
     * @param array $aSort - массив сортировки
     * @param array $aFilter - массив фильтра
     * @return mixed
     */
    public static function GetList($aSort = array(), $aFilter = array())
	{
		global $DB;
        
        $joins = array();
        
		$arFilter = array();
		foreach ($aFilter as $key => $val) {
			if (empty($val)) {
				continue;
            }
			switch ($key) {
			    case "title":
                    $arFilter[] = "R.title LIKE '%$val%'";
                    break;
				case "request_id":
				case "basket_id":
				    if (is_array($val)) {
				        $val = implode(',', array_filter(array_map('intval', $val)));
				        $arFilter[] = "R.".$key." IN (".$val.")";
				    } else {
					    $arFilter[] = "R.".$key." = '".$DB->ForSql($val)."'";
					}
					break;
				case "supplier_id":
				    $val = (int) $val;
    			    $arFilter[] = "R.request_id IN (SELECT `id` FROM `b_lm_suppliers_requests` WHERE `supplier_id` = $val)";
					break;
                case "closed":
                    $val = (string) $val;
                    $arFilter[] = "R.request_id IN (SELECT `id` FROM `b_lm_suppliers_requests` WHERE `closed` = '$val')";
                    break;
				case "status":
                    if (is_array($val)) {
                        $val = implode("', '", array_filter(array_map('strval', $val)));
                        $arFilter[] = "BP.VALUE IN ('".$val."')";
                    } else {
                        $val = "'".$DB->ForSql(strval($val)) ."'";
                        $arFilter[] = "BP.VALUE = $val";
                    }
    			    $joins[] = "LEFT JOIN `b_sale_basket_props` BP ON BP.BASKET_ID = R.basket_id AND BP.CODE = 'status'";
					break;
                case 'created_by':
                    if (is_array($val)) {
                        $val = implode(',', array_filter(array_map('intval', $val)));
                        $arFilter[] = "R.request_id  IN ( SELECT `id` FROM `b_lm_suppliers_requests` WHERE user_id IN (".$val."))";
                    } else {
                        $arFilter[] = "R.request_id IN (SELECT `id` FROM `b_lm_suppliers_requests` WHERE user_id=".$DB->ForSql($val).")";
                    }
                break;
			}
		}

		$arOrder = array();
		foreach ($aSort as $key => $val) {
			$ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
			
			switch ($key) {
				case "request_id":
				case "basket_id":
				//case "quantity":
				//case "article":
				//case "brand_title":
					$arOrder[] = "R.".$key." ".$ord;
					break;
			}
		}
		if (count($arOrder) == 0) {
			$arOrder[] = "R.request_id DESC";
        }
		$sOrder = "\nORDER BY ".implode(", ",$arOrder);
        
		if (count($arFilter) == 0) {
			$sFilter = "";
		} else {
			$sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
        }
        
        $joins = join(' ', $joins);
        
		$strSql = "
			SELECT
				R.*
			FROM
				`".self::TABLE."` R
			$joins
			".$sFilter.$sOrder;
//          echo($strSql);
		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}
	
	
	/**
     * Получение заявки по ID.
     */
	function GetByRequestId($id)
	{
		global $DB;
		$id = intval($id);

		$strSql = "
			SELECT
				R.*
			FROM `".self::TABLE."` R
			WHERE R.request_id = ".$id."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}
	
	
    /**
     * Получение
	 * При первом попадании сюда результат будет пустой, т.к. заявка еще не попала
	 * в нашу таблицу.
     */
	function GetRequestIdByBasketId($id)
	{
	    global $DB;
		$id = intval($id);

		$strSql = "
			SELECT
				R.*
			FROM `".self::TABLE."` R
			WHERE R.basket_id = ".$id."
		";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		//$res = $res->Fetch();
		return $res; //(int) $res['request_id'];
	}
	
	
	/**
     * Удаление
     */
	function DeleteByBasketId($basket_id)
	{
	    global $DB;
	    $basket_id = (int) $basket_id;
	    $res = $DB->Query("DELETE FROM `".self::TABLE."` WHERE `basket_id` = $basket_id", false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}
	
	
	/**
     * Обновление.
     */
	function Update($id, $arFields)
	{
		global $DB;
		$id = intval($id);

		if(!$this->CheckFields($arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_lm_suppliers_requests_baskets", $arFields);
		if ($strUpdate!="") {
			$strSql = "UPDATE `".self::TABLE."` SET ".$strUpdate." WHERE id=".$id;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}
	
	
	/**
     * Добавление.
     */
	function Add($arFields)
	{
		global $DB;

		if(!self::CheckFields($arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_lm_suppliers_requests_baskets", $arFields);
		$strSql = "INSERT INTO b_lm_suppliers_requests_baskets (".$arInsert[0].") VALUES (".$arInsert[1].")";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return intval($DB->LastID());
	}
	
	
	function CheckFields($arFields)
	{
		global $DB;
		$aMsg = array();
        
		if (!empty($aMsg)) {
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}
    
    
    /**
     * Разделение корзин
     */
    function splitBasket($basket, $old_quantity, $new_diff)
    {
        
        LinemediaAutoDebug::add("Split baskets necessary");
          
        /*
         * новая корзина
         */
        $NEW = $basket;
        
        /*
         * Уменьшим кол-во
         */
        $NEW['QUANTITY'] -= $old_quantity;
        
        
        /*
         * Наложим diff
         */
        $NEW = $NEW + $new_diff; // array_merge_recursive проставляет массив в VALUE свойств
        
        
        /*
         * Почистим мусор
         */
        unset($NEW['DATE_INSERT'], $NEW['DATE_UPDATE'], $NEW['ID']);// unset bulk
        
        /*
         * Добавим комментарий
         */
        $NEW['NOTES'] = $NEW['NOTES'] . ' | Split with basket ' . $basket['ID'];
        
        $NEW['PROPS'] = array_values($NEW['PROPS']);
        
        
        global $DB;
        $DB->StartTransaction();
        
        $NEW_ID = CSaleBasket::Add($NEW);
        
        LinemediaAutoDebug::add("New basket $NEW_ID", false, LM_AUTO_DEBUG_WARNING);
        
        if ($NEW_ID < 1) {
             throw new Exception('New basket not created'); 
             exit;
        }
        
        /*
         * Старая корзина
         */
        $basket['QUANTITY'] = $old_quantity;
        $basket['PROPS'] = array_values($basket['PROPS']);
        $ok = CSaleBasket::Update($basket['ID'], $basket);
        
        if ($NEW_ID && $ok) {
            $DB->Commit();
        } else {
            $DB->Rollback();
        }
        
        return $NEW_ID;
    }
    
    
    /**
     * Найти дублирующиеся корзины и объединить их
     */
    function checkDuplicateBaskets($order_id)
    {
        CModule::IncludeModule('sale');
        CModule::IncludeModule('linemedia.auto');
        
        $order_id = (int) $order_id;
        if ($order_id < 1) {
            return;
        }
        
        /*
         * Корзины
         */
        $baskets = array();
        $basket_quantities = array();
        $res = CSaleBasket::GetList(array(), array('ORDER_ID' => $order_id));
        while ($basket = $res->Fetch()) {
            // Сохраним нужные поля, которые будут удалены для сравнения.
            $basket_id = $basket['ID'];
            $basket_quantities[$basket_id] = $basket['QUANTITY'];
            
            unset($basket['ID'], $basket['DATE_INSERT'], $basket['DATE_UPDATE'], $basket['QUANTITY'], $basket['NOTES']);
            $baskets[$basket_id] = $basket;
        }
        
        /*
         * Свойства, кроме дат (даты точно у всех корзин разные и в сравнении участвовать не должны)
         */
        $props = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => array_keys($baskets), '!~CODE' => '*date*'), false, false, array('BASKET_ID', 'NAME', 'CODE', 'VALUE', 'SORT'));
        while ($prop = $props->Fetch()) {
            if (strpos($prop['CODE'], 'date') !== false) {
                continue; // фильтр чего-то не срабатывает...
            }
            $basket_id = $prop['BASKET_ID'];
            unset($prop['BASKET_ID']);
            $baskets[$basket_id]['PROPS'][$prop['CODE']] = $prop;
        }
        
        /*
         * Найдём дубликаты корзин
         */
        $keys = array();         // чистые корзины
        $remove = array();  // дубликаты на удаление
        $update_plus = array();      // добавление QUANTITY к оригинальным корзинам от дубликатов
        foreach ($baskets as $basket_id => $basket) {
            array_multisort($basket);
            $key = md5(json_encode($basket));
            if (isset($keys[$key])) {
                $original_basket_id = $keys[$key]['ID'];
                $remove[] = $basket_id;
                $update_plus[$original_basket_id] += $basket_quantities[$basket_id];
            } else {
                $basket['ID'] = $basket_id;
                $keys[$key] = $basket;
            }
        }
        
        $success = true;
        global $DB;
        $DB->StartTransaction();
        
        /*
         * Обновляем
         */
        foreach ($update_plus as $basket_id => $add) {
            
            $quantity = $basket_quantities[$basket_id] + $add;
            
            /*
             * Разрешить изменение корзины
             */
            define('LM_AUTO_SUPPLIERS_ALLOW_BASKET_CHANGE_' . $basket_id, true);
            
            $success = CSaleBasket::Update($basket_id, array('QUANTITY' => $quantity));
            if (!$success) {
                break;
            }
        }
        
        if ($success) {
            /*
             * Удаляем
             */
            foreach ($remove as $basket_id) {
                /*
                 * Разрешить удаление корзины
                 */
                define('LM_AUTO_SUPPLIERS_ALLOW_BASKET_DELETE_' . $basket_id, true);
                
                $success = CSaleBasket::Delete($basket_id);
                self::DeleteByBasketId($basket_id); // удалим из заявки
                if (!$success) {
                    break;
                }
            }
        }
        
        if ($success) {
            $DB->Commit();
        } else {
            $DB->Rollback();
        }
    }
    
    
    /**
     * Получим список свойств
     */
    function loadBasketProps($basket_id, $fields = array('CODE', 'NAME', 'VALUE'))
    {
        CModule::IncludeModule('sale');
        $props = array();
        $db_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket_id), false, false, $fields);
        while ($ar_res = $db_res->Fetch()) {
            $props[$ar_res['CODE']] = $ar_res;
        }
        return $props;
    }
    
}

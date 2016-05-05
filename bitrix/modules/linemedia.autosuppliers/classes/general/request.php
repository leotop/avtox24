<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Requests class
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

/**
 * Класс заявок поставщикам
 * Class LinemediaAutoSuppliersRequest
 */
class LinemediaAutoSuppliersRequest
{
    /**
     * ID заявки
     * @var int
     */
    protected $id;
    /**
     *
     * @var bool
     */
    protected $loaded = false;
    /**
     * Содержание заявки (список товаров)
     * @var
     */
    protected $data;

    /**
     * Конструктор, проверяет подключение модулей 'linemedia.auto' и 'sale'
     * @param bool $id
     */
    public function __construct($id = false)
    {
        if (!CModule::IncludeModule('linemedia.auto')) {
            trigger_error('No main auto module');
            return;
        }
        
        if (!CModule::IncludeModule('sale')) {
            trigger_error('No sale module');
            return;
        }
    
        $this->id = (int) $id;
    }


    /**
     * Добавление заявки
     * @param $supplier_id - поставщик
     * @param $baskets - корзина (содержит информацию об ID заявки и ID корзины)
     * @param $status - статус заявки
     * @param string $step - шаг заявки (по умолчанию = 0)
     * @param string $closed - заявка закрыта (по умолчанию 'N' - нет)
     * @param string $note - примечание
     * @return int - возвращает ID заявки
     */
    public function add($supplier_id, $baskets, $status, $step = '0', $closed = 'N', $note = '')
    {
        $supplier_id = (string) $supplier_id;
        $status      = (string) $status;
        $step        = (string) $step;
        $note        = (string) $note;
        
        global $DB;
        
        $arFields = array(
            'supplier_id'   => "'".$supplier_id."'",
            'user_id'       => CUser::GetID(),
            'status'        => "'".$status."'",
            'step'          => "'".$step."'",
            'closed'        => "'".$closed."'",
            'note'          => "'".$note."'"
        );
        
        $DB->StartTransaction();
        
        /*
         * Заявка
         */
        $this->id = (int) $DB->Insert("b_lm_suppliers_requests", $arFields, $err_mess.__LINE__);
        
        /*
         * Корзины
         */
        foreach ($baskets as $basket) {
        
            $arFields = array(
                'request_id' => $this->id,
                'basket_id'  => (int) $basket['ID'],
            );
            $DB->Insert("b_lm_suppliers_requests_baskets", $arFields, $err_mess.__LINE__);

            define('LM_AUTO_SUPPLIERS_ALLOW_BASKET_CHANGE_' . $basket['ID'], true);            
        }
        
        if (strlen($strError) <= 0) {
            $DB->Commit();
        } else {
            $DB->Rollback();
            die($strError);
        }
        
        return $this->id;
    }
    
    
    
    /**
     * Загрузка данных о поставщике.
     */
    protected function load()
    {
        if (empty($this->id)) {
            return;
        }
        
        if ($this->loaded) {
            return;
        }
        
        $this->loaded = true;
        
        global $DB;
        $res = $DB->Query('SELECT * FROM `b_lm_suppliers_requests` WHERE `id` = ' . $this->id);
        $this->data = $res->Fetch();
        
        $res = $DB->Query('SELECT * FROM `b_lm_suppliers_requests_baskets` WHERE `request_id` = ' . $this->id);
        while ($basket = $res->Fetch()) {
            $this->data['basket_ids'][] = $basket['basket_id'];
        }
    }
    
    
    /**
     * Получение ID.
     */
    public function getID()
    {
        return $this->id;
    }
    
    
    /**
     * Получение поля.
     */
    public function get($field)
    {
        $this->load();
        if (isset($this->data[$field])) {
            return $this->data[$field];
        }
    }
    
    
    /*
     * Получить все поля.
     */
    public function getArray()
    {
        $this->load();
        return $this->data;
    }
    
    
    /**
     * Существование поставщика.
     */
    public function exists()
    {
        $this->load();
        return count($this->data) > 0;
    }
    
    
    /**
     * Список заявок.
     */
	public static function GetList($aSort = array(), $aFilter = array())
	{
		global $DB;

		$arFilter = array();
		foreach ($aFilter as $key => $val) {
			if (empty($val)) {
				continue;
            }
			switch ($key) {
				case "supplier_id":
                case "closed":
				case "id":
                    if (is_array($val)) {
                        $vals = join("','", array_map(array($DB, 'ForSql'), $val));
                        $arFilter[] = "R.".$key." IN ('$vals')";
                    } else {
                        $arFilter[] = "R.".$key." = '".$DB->ForSql($val)."'";
                    }
					break;
				case "status":
				    if (is_array($val)) {
				        $vals = join("','", array_map(array($DB, 'ForSql'), $val));
					    $arFilter[] = "R.status IN ('$vals')";
					} else {
					    $arFilter[] = "R.status like '%".$DB->ForSql($val)."%'";
					}
					break;
                case 'user_id':
                    if (is_array($val)) {
                        $vals = join("','", array_map(array($DB, 'ForSql'), $val));
                        $arFilter[] = "R.user_id IN ('$vals')";
                    } else {
                        $arFilter[] = "R.user_id=".$DB->ForSql($val);
                    }
                    break;
			}
		}

		$arOrder = array();
		foreach ($aSort as $key => $val) {
			$ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
			
			switch ($key) {
				case "id":
				case "status":
				case "supplier_id":
					$arOrder[] = "R.".$key." ".$ord;
					break;
			}
		}
		if (count($arOrder) == 0) {
			$arOrder[] = "R.id DESC";
        }
		$sOrder = "\nORDER BY ".implode(", ",$arOrder);

		if (count($arFilter) == 0) {
			$sFilter = "";
		} else {
			$sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
        }
        
		$strSql = "
			SELECT
				R.*,
				(SELECT SUM(`QUANTITY`) FROM `b_sale_basket` WHERE `ID` IN (SELECT `basket_id` FROM `b_lm_suppliers_requests_baskets` WHERE `request_id` = R.id)) AS basket_count,
				(SELECT GROUP_CONCAT(`ID`) FROM `b_sale_basket` WHERE `ID` IN (SELECT `basket_id` FROM `b_lm_suppliers_requests_baskets` WHERE `request_id` = R.id)) AS basket_ids
			FROM
				b_lm_suppliers_requests R
			".$sFilter.$sOrder;
//           echo ($strSql);
		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}
	
	
    /**
     * Закрыта ли заявка.
     */
    public function isClosed()
    {
        return ($this->get('closed') == 'Y');
    }
    
    
    /**
     * Открыть заявку.
     */
    public function open()
    {
        return $this->switched('N');
    }
    
    
    /**
     * Закрыть заявку.
     */
    public function close()
    {
        return $this->switched('Y');
    }


    /**
     * Переключатель открыта / закрыта.
     * @param string $close - 'N' - открыта, 'Y' - закрыта
     * @return mixed - В случае успешного переключения возвращает результат
     */
    public function switched($close = 'N')
    {
        global $DB;
        
        $sql = "UPDATE `b_lm_suppliers_requests` SET `closed` = '".strval($close)."' WHERE `id` = '".intval($this->id)."';";
        
        return $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }


    /**
     * Получить по заказ ID
     * @param $id ID заказа
     * @return mixed
     */
    function GetById($id)
	{
		global $DB;
		$id = intval($id);

		$strSql = "
			SELECT
				R.*
			FROM b_lm_suppliers_requests R
			WHERE R.id = ".$id."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}


    /**
     * Удалить по ID
     * @param $id ID заказа, который нужно удалить
     * @return mixed
     */
    function Delete($id)
	{
	    global $DB;
		$id = intval($id);

        /*
        * Вернём корзинам статус
        */
		/*
		 * C Игорем решили, что статус не должен меняться
		 */
		/*
        CModule::IncludeModule('sale');
        $request = new LinemediaAutoSuppliersRequest($id);
        $request = $request->getArray();
        foreach($request['basket_ids'] AS $basket_id)
        {
            $props = array();
            $db_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket_id));
            while ($prop = $db_res->Fetch())
            {
                unset($prop['BASKET_ID']);
                if($prop['CODE'] == 'supplier_request_status')
                    $prop['VALUE'] = 'new';
                $props[] = $prop;
            }
            $arFields = array(
                'PROPS' => $props
            );
            CSaleBasket::Update($basket_id, $arFields);
        }
		*/


		$DB->StartTransaction();

		$res = $DB->Query("
                DELETE FROM b_lm_suppliers_requests_baskets WHERE request_id = $id
		    ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
        
        if($res)
			$res = $DB->Query("
                DELETE FROM b_lm_suppliers_requests WHERE id = $id
		    ", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($res)
			$DB->Commit();
		else
			$DB->Rollback();
        
        
        
		return $res;
	}
	
	//update
	function Update($id, $arFields)
	{
		global $DB;
		$id = intval($id);

		if(!$this->CheckFields($arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_lm_suppliers_requests", $arFields);
		if($strUpdate!="")
		{
			$strSql = "UPDATE b_lm_suppliers_requests SET ".$strUpdate." WHERE id=".$id;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}

    /**
     * Проверка полей
     * @param $arFields - Массив полей
     * @return bool - Возвращает false в случае ошибки
     */
    function CheckFields($arFields)
	{
		global $DB;
		$this->LAST_ERROR = "";
		$aMsg = array();

		if ($arFields["supplier_id"] < 1) {
			$aMsg[] = array("id"=>"supplier_id", "text"=>GetMessage("incorrect_supplier_id"));
		}
		if (!empty($aMsg)) {
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			$this->LAST_ERROR = $e->GetString();
			return false;
		}
		return true;
	}


    /**
     * Сохранение в XLS.
     * @return mixed|string Возвращает название файла
     */
    function saveXLS()
    {
        $this->getArray();
        
        $supplier = new LinemediaAutoSupplier($this->data['supplier_id']);
        
        $currencies = array();
        $lcur = CCurrency::GetList(($b = "name"), ($o = "asc"), LANGUAGE_ID);
        while ($lcur_res = $lcur->Fetch()) {
            $currencies[$lcur_res["CURRENCY"]] = $lcur_res;
        }
        
        $base_currency = $supplier->get('currency');
        
        $currency_title = $currencies[$base_currency]['CURRENCY'];
        
        
        /*
         * Получим нужные корзины и список заказов
         */
        $result = array();
        $dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $this->data['basket_ids']), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "WEIGHT", 'ORDER_ID', 'NAME'));
        while ($basket = $dbBasketItems->Fetch()) {
            $props_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID']));
            while ($prop = $props_res->Fetch()) {
                $basket['PROPS'][$prop['CODE']] = $prop;
            }
            
            $brand_title = $basket['PROPS']['brand_title']['VALUE'];
            $article     = $basket['PROPS']['article']['VALUE'];
            $quantity    = $basket['QUANTITY'];
            
            $price = $basket['PROPS']['base_price']['VALUE'];
            
            /*
             * Пересчёт валюты
             */
            if ($basket['CURRENCY'] != '' && $basket['CURRENCY'] != $base_currency) {
                $price = $price * $currencies[$basket['CURRENCY']]['AMOUNT'];
            }
            
            $result[$brand_title][$article][$price]['quantity'] += $quantity;
            $result[$brand_title][$article][$price]['title'] = $basket['NAME'];
        }
        
        
        
        /*
         * Создание excel-файла.
         */
        $excel = new LinemediaAutoExcel();
        
        $note = str_replace(
            array('#NUM#', '#DATE#', '#NAME#'),
            array($this->id, date('d.m.Y', strtotime($this->data['date'])), COption::GetOptionString('main', 'site_name')),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_NOTE')
        );
        
        $excel->addHeader(array(
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_BRAND'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_ARTICLE'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_TITLE'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_PRICE') . ' ('.$currency_title.')',
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_QUANTITY_ORDERED'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_QUANTITY_CONFIRMED'),
        ));
        
        $excel->addNote($note);
        
        $excel->addColumnTypes(array(
            LinemediaAutoExcel::TYPE_TEXT,
            LinemediaAutoExcel::TYPE_TEXT,
            LinemediaAutoExcel::TYPE_TEXT,
            LinemediaAutoExcel::TYPE_DECIMAL,
            LinemediaAutoExcel::TYPE_INT,
            LinemediaAutoExcel::TYPE_INT
        ));
        
        foreach ($result as $brand_title => $articles) {
            foreach ($articles as $article => $prices) {
                foreach ($prices as $price => $data) {
                    $excel->addRow(array(
                        $brand_title,
                        $article,
                        $data['title'],
                        $price,
                        $data['quantity'],
                        0
                    ));
                }
            }
        }
        
        $filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autosuppliers/requests/request_'.$this->id.'.html';
        
        file_put_contents($filename, $excel->getResult());
        
        $filename = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->convert2XLS($filename));
        
        return $filename;
    }


    /**
     * Конвертация файла.
     * @param $filename - Название файла
     * @param string $from - Что конвертируем (по умолчанию html)
     * @return string - Возвращает название файла с новым расширением
     */
    private function convert2XLS($filename, $from = 'html')
    {
        switch ($from) {
            case 'html':
                $cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename) . '.xls';
                $cmd_result = shell_exec($cmd);
                
                unlink($filename);
                return $filename . '.xls';
                break;
            default:
                return $filename;
        }
    }
    
    
    /**
     * Доступна ли конвертация из XLS XLSX в CSV?
     */
    public static function isConversionSupported()
    {
        $returnVal = shell_exec("which ssconvert");
        if (!empty($returnVal)) {
            $ret = shell_exec('LANG=C ssconvert -v');
            return strpos($ret, 'version')!== false;
        }
        return (!empty($returnVal));
    }
}

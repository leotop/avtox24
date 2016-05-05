<?php

class LinemediaAutoTask
{
    const MODE_TEST = 'T'; // Режим тестирования конвертации прайслиста.


    static $default_connection = array ( 'protocol' => 'file', 'file' => array ());
    static $default_conversion = array (
			'zip_content_filename' => '',
			'type' => '',
			'encoding' => '',
			'skip_lines' => 0,
			'separator' => ';',
			'column_replacements' => array (),
			'column_replacements_all' => array (),
			'columns' => array (
				'brand_title' => 1,
				'article' => 2,
				'title' => 3,
				'price' => 4,
				'quantity' => 5,
				'weight' => 6,
			),
		);

    var $LAST_ERROR = "";
    
    
    
	/**
     * Cписок моделей.
     */
	function GetList($aSort = array(), $aFilter = array())
	{
		global $DB;
        
		$arFilter = array();
		foreach ($aFilter as $key => $val) {
			if (!is_array($val) && strlen($val) <= 0) {
				continue;
            }
            
			switch ($key) {
				case "id":
				case "supplier_id":
				case "active":
				case "protocol":
                case "mode":
                case "email": {
                    if(is_array($val)) {
                        foreach($val as $i => $v) {
                            $val[$i] = $DB->ForSql($v);
                        }
                        $arFilter[] = "T.".$key." IN ('".join("','", $val)."')";
                    } else {
                        $arFilter[] = "T.".$key." = '".$DB->ForSql($val)."'";
                    }
                } break;
				case "title":
					$arFilter[] = "T.title like '%".$DB->ForSql($val)."%'";
					break;
			}
		}

		$arOrder = array();
		foreach ($aSort as $key => $val) {
			$ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
            
			switch($key) {
				case "id":
				case "supplier_id":
				case "title":
				case "active":
				case "protocol":
					$arOrder[] = "T.".$key." ".$ord;
					break;
                case "last_exec":
                    $arOrder[] = $key." ".$ord;
                    break;
			}
		}
		if (count($arOrder) == 0) {
			$arOrder[] = "T.id DESC";
        }
		$sOrder = "\nORDER BY ".implode(", ",$arOrder);

		if (count($arFilter) == 0) {
			$sFilter = "";
		} else {
			$sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
        }

        $strSql = "
        SELECT
            T.*, (SELECT GROUP_CONCAT(S.id SEPARATOR ',') FROM `b_lm_tasks_shedule` S WHERE S.task_id = T.id) AS shedule_ids,
            (SELECT MAX(last_exec) FROM `b_lm_tasks_shedule` S WHERE S.task_id = T.id) as last_exec
        FROM
            `b_lm_tasks` AS T
        ".$sFilter.$sOrder;

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	/**
     * Получение элемента по ID.
     */
	function GetById($id)
	{
		global $DB;
		$id = intval($id);

		$strSql = "
			SELECT
				T.*, (SELECT GROUP_CONCAT(S.id SEPARATOR ',') FROM b_lm_tasks_shedule S WHERE S.task_id =T.id) AS shedule_ids
			FROM b_lm_tasks T
			WHERE T.id = ".$id."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}



	/**
     * Удаление.
     */
	function Delete($id)
	{
		global $DB;
		$id = intval($id);

		$DB->StartTransaction();
		
		$res = $DB->Query("
            DELETE FROM b_lm_tasks_shedule WHERE task_id = $id
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
		if ($res)
			$res = $DB->Query("
	            DELETE FROM b_lm_tasks WHERE id = $id
			", false, "File: ".__FILE__."<br>Line: ".__LINE__);
        
        if ($res)
			$DB->Commit();
		else
			$DB->Rollback();
        
		return $res;
	}

    
	//check fields before writing
	function CheckFields($arFields)
	{
		global $DB;
		$this->LAST_ERROR = "";
		$aMsg = array();

		if (strlen($arFields["title"]) == 0) {
			$aMsg []= array("id" => "title", "text" => GetMessage("class_rub_err_title"));
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
     * Добавлнение.
     */
	function Add($arFields)
	{
		global $DB;

		if (!$this->CheckFields($arFields)) {
			return false;
		}
        
        if(is_array($arFields['connection'])) $arFields['connection'] = serialize($arFields['connection']);
        if(is_array($arFields['conversion'])) $arFields['conversion'] = serialize($arFields['conversion']);
        
		$id = $DB->Add("b_lm_tasks", $arFields);
		return $id;
	}
    
    
	/**
     * Обновление.
     */
	function Update($id, $arFields)
	{
		global $DB;
		$id = intval($id);
        
		if (!$this->CheckFields($arFields)) {
			return false;
        }

        if(is_array($arFields['connection'])) $arFields['connection'] = serialize($arFields['connection']);
        if(is_array($arFields['conversion'])) $arFields['conversion'] = serialize($arFields['conversion']);
        
		$strUpdate = $DB->PrepareUpdate("b_lm_tasks", $arFields);
		
		if ($strUpdate != "") {
			$strSql = "UPDATE `b_lm_tasks` SET ".$strUpdate." WHERE `id` = ".$id;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}
}

<?php

class LinemediaAutoTaskShedule
{
	var $LAST_ERROR = "";
    
    
    
	/**
     * Cписок моделей.
     */
	function GetList($aSort = array(), $aFilter = array())
	{
		global $DB;

		$arFilter = array();
		foreach ($aFilter as $key => $val) {
			if (strlen($val) <= 0) {
				continue;
            }
            
			switch ($key) {
				case "id":
				case "task_id":
				case "interval":
				case "days":
				case "start_day":
				case "start_time":
				case "last_exec":
					if(is_array($val))
					{
						$val = "'" . join("','", array_map(array($DB, 'ForSql'), $val)) . "'";
						$arFilter[] = "T.".$key." = " . $val;
					} else {
						$arFilter[] = "T.".$key." = '".$DB->ForSql($val)."'";
					}
					
					break;
			}
		}

		$arOrder = array();
		foreach ($aSort as $key => $val) {
			$ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
            
			switch($key) {
				case "id":
				case "task_id":
				case "start_day":
				case "interval":
				case "days":
				case "start_time":
				case "last_exec":
					$arOrder[] = "T.".$key." ".$ord;
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
				T.*
			FROM
				b_lm_tasks_shedule T
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
				T.*
			FROM b_lm_tasks_shedule T
			WHERE T.id = ".$id."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	/**
     * Получение элемента по ID.
     */
	function GetByTaskId($id)
	{
		global $DB;
		$id = intval($id);

		$strSql = "
			SELECT
				T.*
			FROM b_lm_tasks_shedule T
			WHERE T.task_id = ".$id."
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
            DELETE FROM b_lm_tasks_shedule WHERE id = $id
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
        
		$id = $DB->Add("b_lm_tasks_shedule", $arFields);
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
		$strUpdate = $DB->PrepareUpdate("b_lm_tasks_shedule", $arFields);
		if ($strUpdate != "") {
			$strSql = "UPDATE `b_lm_tasks_shedule` SET ".$strUpdate." WHERE `id` = ".$id;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}
}

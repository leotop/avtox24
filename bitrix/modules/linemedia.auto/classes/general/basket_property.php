<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   07/03/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

class LinemediaAutoBasketProperty
{
    var $LAST_ERROR = "";
    const TABLE = 'b_lm_sale_basket_props';


    /**
     * Список запчастей.
     */
    public static function getList($aSort = array(), $aFilter = array())
    {
        global $DB;

        $arFilter = array();
        foreach ($aFilter as $key => $val) {
            if (strlen($val) <= 0) {
                continue;
            }
            switch ($key) {
                case "ID":
                case "BASKET_ID":
                case "NAME":
                case "VALUE":
                case "CODE":
                case "SORT":
                    $arFilter []= "N.".$key." LIKE '%".$DB->ForSql($val)."%'";
                    break;
            }
        }

        $arOrder = array();
        foreach ($aSort as $key => $val) {
            $ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");

            switch ($key) {
                case "ID":
                case "BASKET_ID":
                case "NAME":
                case "VALUE":
                case "CODE":
                case "SORT":
                    $arOrder []= "N.".$key." ".$ord;
                    break;
            }
        }
        if (count($arOrder) == 0) {
            $arOrder[] = "N.SORT DESC";
        }
        $sOrder = "\nORDER BY ".implode(", ", $arOrder);

        if (count($arFilter) == 0) {
            $sFilter = "";
        } else {
            $sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
        }

        $strSql = "
            SELECT
                N.*
            FROM
                `".self::TABLE."` N
            ".$sFilter.$sOrder;

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /**
     * Получение элемента по ID.
     */
    public static function getById($id)
    {
        global $DB;
        $id = intval($id);

        $strSql = "
            SELECT
                N.*
            FROM `".self::TABLE."` N
            WHERE N.ID = ".$id."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
    
    /**
     * Получение записей пользователя с заданным ID.
     */
    public static function getByBasketId($basket_id)
    {
        global $DB;
        $basket_id = intval($basket_id);

        $strSql = "
            SELECT
                N.*
            FROM `".self::TABLE."` N
            WHERE N.BASKET_ID = ".$basket_id."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
    
    
    
    
    
    /**
     * Удаление записей пользователя с заданным ID и кодом
     */
    public static function deleteByBasketIdAndCode($basket_id, $code)
    {
        global $DB;
        $basket_id = intval($basket_id);
        $code = $DB->forSql($code);

        $strSql = "
            DELETE
            FROM `".self::TABLE."` 
            WHERE BASKET_ID = ".$basket_id." AND CODE = '".$code."'
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
    


    /**
     * Удаление по ID записи в таблице.
     */
    public static function deleteById($id)
    {
        global $DB;
        $id = intval($id);

        $DB->StartTransaction();

        $strSql = "DELETE FROM `".self::TABLE."` WHERE `ID` = $id";
        $res = $DB->Query(
            $strSql,
            false,
            "File: ".__FILE__."<br>Line: ".__LINE__
        );

        if ($res) {
            $DB->Commit();
        } else {
            $DB->Rollback();
        }

        return $res;
    }


    //check fields before writing
    public function checkFields($arFields)
    {
        $this->LAST_ERROR = "";
        $aMsg = array();

        if (!isset($arFields["BASKET_ID"]) || strlen($arFields["BASKET_ID"]) == 0) {
            $aMsg[] = array("id" => "BASKET_ID", "text" => "NO_BASKET_ID");
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
     * Добавление.
     */
    public function add($arFields)
    {
        global $DB;

        if (!$this->CheckFields($arFields)) {
            return false;
        }
        $id = $DB->Add(self::TABLE, $arFields);
        return $id;
    }


    /**
     * Обновление.
     */
    public function update($id, $arFields)
    {
        global $DB;
        $id = intval($id);

        $strUpdate = $DB->PrepareUpdate(self::TABLE, $arFields);
        if ($strUpdate != "") {
            $strSql = "UPDATE `".self::TABLE."` SET ".$strUpdate." WHERE `ID` = ".$id;
            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        }
        return true;
    }
}

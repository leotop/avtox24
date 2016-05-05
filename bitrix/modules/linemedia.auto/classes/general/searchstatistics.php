<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   11/06/2013
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoSearchStatistics
{
    var $LAST_ERROR = "";
    const TABLE = 'b_lm_search_statistics';


    /*
     * Список запросов, отформатированных нужным образом.
     */
    public static function getFormatList($aSort = array(), $aFilter = array())
    {
        global $DB;

        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
        $accessRight = self::getUserAccesRight();
        if($accessRight == 'D') {
            //throw  new Exception('Access to statistic denied!');
            return;
        }

        $arFilter = array();
        foreach ($aFilter as $key => $val) {
            if (strlen($val) <= 0) {
                continue;
            }
            switch ($key) {
                case "article":
                case "brand_title":
                case "supplier_id":
                case "branch_id":
                    $arFilter []= $key." = '".$DB->ForSql($val)."'";
                    break;
                case "added >":
                    if ($date = ParseDateTime($val, CSite::GetDateFormat('FULL'))) {
                        if (strlen($val) < 11) {
                            $date["HH"] = 0;
                            $date["MI"] = 0;
                            $date["SS"] = 0;
                        }
                        $date = date($DB->DateFormatToPHP('YYYY.MM.DD HH:MI:SS'), mktime($date["HH"], $date["MI"], $date["SS"], $date["MM"], $date["DD"], $date["YYYY"]));
                        $arFilter []= $key." '".$DB->ForSql($date)."'";
                    }

                    break;
                case "added <":
                    if ($date = ParseDateTime($val, CSite::GetDateFormat('FULL'))) {
                        if (strlen($val) < 11) {
                            $date["HH"] = 23;
                            $date["MI"] = 59;
                            $date["SS"] = 59;
                        }
                        $date = date($DB->DateFormatToPHP('YYYY.MM.DD HH:MI:SS'), mktime($date["HH"], $date["MI"], $date["SS"], $date["MM"], $date["DD"], $date["YYYY"]));
                        $arFilter []= $key." '".$DB->ForSql($date)."'";
                    }                    
                    break;
            }
        }

        $arOrder = array();
        foreach ($aSort as $key => $val) {
            $ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");

            switch ($key) {
                case "article":
                case "brand_title":
                case "supplier_id":
                case "good_requests":
                case "requests":
                case "analog_exist":
                case "avg_analogs":
                case "article_found":
                case "article_not_found":

                    $arOrder []= $key." ".$ord;
                    break;
            }
        }

        if (count($arOrder) == 0) {
            $arOrder[] = "requests DESC";
        }
        $sOrder = "\nORDER BY ".implode(", ", $arOrder);

        // условие ограничения доступа по поставщикам
        $accessFilter = self::createSuppliersSqlFilter();

        if (count($arFilter) == 0) {
            if(!empty($accessFilter)) $sFilter = "\nWHERE " . $accessFilter;
            else $sFilter = "\nWHERE 1=1";
        } else {
            $sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
            if(!empty($accessFilter)) $sFilter .= " AND " . $accessFilter;
        }

        /*
         * Cоздаём событие для управления фильтром
         */
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeStatisticQuery');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$sFilter, &$accessRight));
        }

        $strSql = "SELECT
                        article,
                        brand_title,
                        supplier_id,
                        count(*) as requests,
                        round(count(IF(variants>0,1,NULL))/count(*)*100,0) as good_requests,
                        round(count(IF(analogs>0,1,NULL))/count(*)*100,0) as analog_exist,
                        round(avg(NULLIF(analogs ,0)),0) as avg_analogs,
                        count(IF(variants>0,1,NULL)) as article_found,
                        count(*) - count(IF(variants>0,1,NULL)) as article_not_found

                    FROM  `" . self::TABLE . "`" .$sFilter ." GROUP BY  article, brand_title, supplier_id " . $sOrder;

        return $DB->Query($strSql, true, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /*
     * Список запросов.
     */
    public static function getList($aSort = array(), $aFilter = array())
    {
        global $DB;

        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
        $accessRight = self::getUserAccesRight();
        if($accessRight == 'D') {
            //throw  new Exception('Access to statistic denied!');
            return false;
        }

        $arFilter = array();
        foreach ($aFilter as $key => $val) {
            if (strlen($val) <= 0) {
                continue;
            }
            switch ($key) {
                case "id":
                case "added":
                case "article":
                case "brand_title":
                case "supplier_id":
                case "branch_id":
                case "variants":
                case "analogs":
                    $arFilter []= $key." = '".$DB->ForSql($val)."'";
                    break;
            }
        }

        $arOrder = array();
        foreach ($aSort as $key => $val) {
            $ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");

            switch ($key) {
                case "id":
                case "added":
                case "article":
                case "brand_title":
                case "supplier_id":
                case "variants":
                case "analogs":
                    $arOrder []= $key." ".$ord;
                    break;
            }
        }
        if (count($arOrder) == 0) {
            $arOrder[] = "id DESC";
        }
        $sOrder = "\nORDER BY ".implode(", ", $arOrder);

        // условие ограничения доступа по поставщикам
        $accessFilter = self::createSuppliersSqlFilter();

        if (count($arFilter) == 0) {
            if(!empty($accessFilter)) $sFilter = "\nWHERE " . $accessFilter;
            else $sFilter = "\nWHERE 1=1";
        } else {
            $sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
            if(!empty($accessFilter)) $sFilter .= " AND " . $accessFilter;
        }

        /*
         * Cоздаём событие для управления фильтром
         */
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeStatisticQuery');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$sFilter, &$accessRight));
        }

        $strSql = "
            SELECT
                *
            FROM
                `".self::TABLE."`
            ".$sFilter.$sOrder;

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /*
     * Получение запроса по ID.
     */
    public static function getById($id)
    {
        global $DB;

        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
        $accessRight = self::getUserAccesRight();
        if($accessRight == 'D') {
            //throw  new Exception('Access to statistic denied!');
            return false;
        }

        $id = intval($id);

        $sFilter = "WHERE id = ".$id;

        // условие ограничения доступа по поставщикам
        $accessFilter = self::createSuppliersSqlFilter();
        if(!empty($accessFilter)) $sFilter .= " AND " . $accessFilter;

        /*
         * Cоздаём событие для управления фильтром
         */
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeStatisticQuery');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$sFilter, &$accessRight));
        }

        $strSql = "
            SELECT
                *
            FROM `".self::TABLE."`
            ".$sFilter."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
    /*
     * Получение запросов по артикулу.
     */
    public static function getByArticle($article)
    {
        global $DB;

        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
        $accessRight = self::getUserAccesRight();
        if($accessRight == 'D') {
            //throw  new Exception('Access to statistic denied!');
            return false;
        }

        $sFilter = "WHERE article = '".$article."'";

        // условие ограничения доступа по поставщикам
        $accessFilter = self::createSuppliersSqlFilter();
        if(!empty($accessFilter)) $sFilter .= " AND " . $accessFilter;

        /*
         * Cоздаём событие для управления фильтром
         */
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeStatisticQuery');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$sFilter, &$accessRight));
        }

        $strSql = "
            SELECT
                *
            FROM `".self::TABLE."`
            ".$sFilter."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /*
     * Получение запросов по бренду.
     */
    public static function getByBrand($brand_title)
    {
        global $DB;

        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
        $accessRight = self::getUserAccesRight();
        if($accessRight == 'D') {
            //throw  new Exception('Access to statistic denied!');
            return false;
        }

        $sFilter = "WHERE brand_title = '".$brand_title."'";

        // условие ограничения доступа по поставщикам
        $accessFilter = self::createSuppliersSqlFilter();
        if(!empty($accessFilter)) $sFilter .= " AND " . $accessFilter;

        /*
         * Cоздаём событие для управления фильтром
         */
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeStatisticQuery');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$sFilter, &$accessRight));
        }

        $strSql = "
            SELECT
                *
            FROM `".self::TABLE."`
            ".$sFilter."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }


    /*
     * Удаление по ID записи в таблице.
     */
    public static function deleteById($id)
    {
        global $DB;

        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
        $accessRight = self::getUserAccesRight();
        if($accessRight == 'D') {
            throw  new Exception('Access to statistic denied!');
        }

        $id = intval($id);

        $DB->StartTransaction();

        $strSql = "DELETE FROM `".self::TABLE."` WHERE `id` = $id";
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

        if (!isset($arFields["article"]) || strlen($arFields["article"]) == 0) {
            $aMsg[] = array("id" => "article", "text" => GetMessage("class_rub_err_article"));
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

        $id = $DB->add(self::TABLE, $arFields, array(), '', true);
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
            $strSql = "UPDATE `".self::TABLE."` SET ".$strUpdate." WHERE `id` = ".$id;
            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        }
        return true;
    }

	/**
	 * Удаление старых данных.
	 */
	public static function removeAgent()
	{
		global $DB;
		$days = (int) COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS', '31');
		$seconds = $days * 24 * 60 * 60;
		$strSql = "DELETE FROM `".self::TABLE."` WHERE UNIX_TIMESTAMP(added) <= UNIX_TIMESTAMP(NOW())-" . "$seconds";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

    /*
     * Возвращает SQL фильтр для ограничения доступа только к возможным поставщикам
     */

    private static function createSuppliersSqlFilter() {

        // список доступных текущему пользователю поставщиков
        $arSuppliers = LinemediaAutoSupplier::getAllowedSuppliers('supplier_id');

        if(count($arSuppliers) > 0) {
            return "(supplier_id IS NULL OR supplier_id IN ('" . join("', '", $arSuppliers) . "'))";
        } else {
            return "supplier_id IS NULL"; // запрещены все
        }
    }

    /*
     * Доступ текущего пользователя к поставщикам согласно настройкам главного модуля
     */
    public static function getUserAccesRight() {

        global $USER;

        $curUserGroup = $USER->GetUserGroupArray();

        $sModuleId = "linemedia.auto";
        $arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_STATISTICS);

        $maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);

        return $maxRole;
    }

}

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
class LinemediaAutoSearchPartial implements LinemediaAutoISearch
{
    /**
     * ����� �������� �� ��������� ���� ������
     */
    public function searchLocalDatabaseForPart($part, $multiple = false)
    {
        try {
            $database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }
        
        /*
         * �������� �������� ������
         */
        $article         = LinemediaAutoPartsHelper::clearArticle($part['article']);
        $id              = (int) $part['id'];
        $brand_title     = (string) $part['brand_title'];
        $supplier_id     = (string) $part['supplier_id'];
        
        /*
         * �������������� �������� ������, ��������� ��������������� ������ �� ������
         */
        $extra = (array) $part['extra'];
        
        
        /*
         * ���������� ������
         */
        $where = array();
        
        // ���������� �� ������ ������ � �������.
        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y') {
            $where []= '`quantity` > 0';
        }
        
        if ($id > 0) {
            $where[] = '`id` = ' . $database->ForSql($id);
        }
        
        
        /*
         * ������� ���������� ����������� � ����������� � API-������������.
         */
        $obCache = new CPHPCache();
        $life_time = 10 * 60;
        $cache_id = 'active_suppliers';
        if ($obCache->InitCache($life_time, $cache_id, '/')) {
            $arSupplierIDs = $obCache->GetVars();
        } else {
            $arSupplierIDs  = array();
            $arSuppliers    = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', 'PROPERTY_api' => false), false, false, array('ID', 'PROPERTY_supplier_id'));
            foreach ($arSuppliers as $arSupplier) {
                $arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
            }
            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache($arSupplierIDs);
            }
        }
        
        if (!empty($arSupplierIDs)) {
            $where []= '`supplier_id` IN (' . implode(', ', $arSupplierIDs) . ')';
        } else {
	        // ��� �������� �����������!
	        return array();
        }
        
        if ($brand_title != '') {
            /*
             * ������� ����������.
             */
            $wordforms = new LinemediaAutoWordForm();
            $brand_titles = $wordforms->getBrandWordforms($brand_title);
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
        
        if ($supplier_id) {
            $where[] = '`supplier_id` = ' . $database->ForSql($supplier_id);
        }
        
        if ($article) {
            $where[] = "`article` LIKE '%" . $database->ForSql($article) . "%'";
        }
        
        
        /*
         * �������������� �������� ������.
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
         * ������ ���� ����� ���� ���� ������, ���������� � �������� �����������.
         */
        if (count($where) <= 1) {
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
         * �� ���� ���� �������� ��� �����?
         */
        if ($multiple) {
            $parts = array();
            while ($part = $res->Fetch()) {
                /*
                 * �������� ����������� ��-��� � �������� - ��������� ��
                 */
                $part['data-source'] = 'local-database';
                $parts []= $part;
            }
            return $parts;
        } else {
            if ($part = $res->Fetch()) {
                /*
                 * �������� ����������� ��-��� � �������� - ��������� ��
                 */
                $part['data-source'] = 'local-database';
                return $part;
            } else {
                return false;
            }
        }
    }
}

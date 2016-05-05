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
class LinemediaAutoSearch extends LinemediaAutoSearchAll
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
        $brand_id        = (int) $part['brand_id'];
        $brand_title     = (string) $part['brand_title'];
        $supplier_id     = (int) $part['supplier_id'];
        
        /*
         * �������������� �������� ������, ��������� ��������������� ������ �� ������
         */
        $extra = (array) $part['extra'];
        
        //$brand_tecdoc_id = (int) $part['brand_tecdoc_id'];
        //$brand_title      = (string) $part['brand_title'];
        
        /*
         * ���������� ������
         */
        $where = array(
            '`quantity` > 0',
        );
        
        if ($id > 0) {
            $where[] = '`id` = ' . $database->ForSql($id);
        }
        
        if ($brand_id) {
            $where[] = '`brand_id` = ' . $database->ForSql($brand_id);
        }
        
        /*
        * ����� �������������� �������� ������, �� ������������� ID
        * �������� ��� ������� ������� �� TRW
        * � ��������� ���� ��� TRW
        * ����� �������� �� ������ �������
        * � ���� TRW �� �� ����, �� ������
        */
        if ($brand_id < 1 && $brand_title != '') {
            $brand_title = $database->ForSql($brand_title);
            $where[] = "brand_id = (SELECT `id` FROM `b_lm_brands` WHERE `title` = '$brand_title')";
        }
        
        if ($supplier_id) {
            $where[] = '`supplier_id` = ' . $database->ForSql($supplier_id);
        }
        
        if ($article) {
            $where[] = '`article` = \'' . $database->ForSql($article) . "'";
        }
        
        /*
         * �������������� �������� ������
         */
        if (count($extra) > 0) {
            foreach ($extra as $col => $val) {
                $operator = '=';
                if (in_array($col[0], array('=', '>', '<'))) {
                    $operator = $col[0];
                }
                $col = '`' . $database->ForSql($col) . '`';
                $val = "'" . $database->ForSql($val) . "'";
                $where[] = "$col $operator $val";
            }
        }
        
        try {
            $res = $database->Query('SELECT * FROM `b_lm_products` WHERE ' . join(' AND ', $where));
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

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
     * Поиск запчасти по локальной базе данных
     */
    public function searchLocalDatabaseForPart($part, $multiple = false)
    {
        try {
            $database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }
        
        /*
         * Основные критерии поиска
         */
        $article         = LinemediaAutoPartsHelper::clearArticle($part['article']);
        $id              = (int) $part['id'];
        $brand_id        = (int) $part['brand_id'];
        $brand_title     = (string) $part['brand_title'];
        $supplier_id     = (int) $part['supplier_id'];
        
        /*
         * Дополнительные критерии поиска, требующие дополнительного поиска по бренду
         */
        $extra = (array) $part['extra'];
        
        //$brand_tecdoc_id = (int) $part['brand_tecdoc_id'];
        //$brand_title      = (string) $part['brand_title'];
        
        /*
         * составляем запрос
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
        * Может присутствовать название бренда, но отсутствовать ID
        * Например это каталог текдока на TRW
        * В локальной базе нет TRW
        * Тогда запчасть не должна найтись
        * А если TRW всё же есть, то должна
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
         * Дополнительные критерии поиска
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
         * Мы ищем одну запчасть или много?
         */
        if ($multiple) {
            $parts = array();
            while ($part = $res->Fetch()) {
                /*
                 * Источник поступления ин-ции о запчасти - локальная БД
                 */
                $part['data-source'] = 'local-database';
                $parts []= $part;
            }
            return $parts;
        } else {
            if ($part = $res->Fetch()) {
                /*
                 * Источник поступления ин-ции о запчасти - локальная БД
                 */
                $part['data-source'] = 'local-database';
                return $part;
            } else {
                return false;
            }
        }
    }
}

<?php
/**
 * Linemedia API
 * Драйвер для модуля (linemedia.api) API для сайта
 * Описание структур
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/**
* Запчасть
*/
$structures["Struct_LinemediaAutoAnalogsSimple_Analog"] = array(
    "id" 					=> array("varType" => "integer"),
    "import_id" 			=> array("varType" => "string"),
    "group_original" 		=> array("varType" => "string"),
    "article_original" 		=> array("varType" => "string"),
    "brand_title_original" 	=> array("varType" => "string"),
    "group_analog" 			=> array("varType" => "string"),
    "article_analog" 		=> array("varType" => "string"),
    "brand_title_analog" 	=> array("varType" => "string"),
    "added" 				=> array("varType" => "string"),
);
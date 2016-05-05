<?php
/**
 * Linemedia API
 * Описание структур
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */
 
/**
* Описание списка обновлений
*/
$structures["Struct_PriceImportTask"] = array(
    "ID"		=> array("varType" => "integer"),
    "TASK_ID" 		=> array("varType" => "integer"),
    "SUPPLIER_ID" 	=> array("varType" => "string"),
    "PARTS_COUNT"	=> array("varType" => "integer"),
    "SUM_PRICE"		=> array("varType" => "string"),
    "PARTS_DIFF"	=> array("varType" => "integer"),
    "SUM_DIFF"		=> array("varType" => "integer"),
    "DATE"		=> array("varType" => "string"),
    "CORRECT_IMPORT"	=> array("varType" => "integer"),
);

/**
* Описание товаров из прайса
*/
$structures["Struct_PriceProductsList"] = array(
    "id"			=> array("varType" => "integer"),
    "title"	 		=> array("varType" => "string"),
    "article"	 		=> array("varType" => "string"),
    "original_article" 		=> array("varType" => "string"),
    "brand_title"		=> array("varType" => "string"),
    "price"	 		=> array("varType" => "string"),
    "quantity"	 		=> array("varType" => "string"),
    "group_id"	 		=> array("varType" => "string"),
    "weight"	 		=> array("varType" => "string"),
    "supplier_id" 		=> array("varType" => "string"),
    "modified"	 		=> array("varType" => "string"),
    "ENG"	 		=> array("varType" => "string"),
    "MGROUP"	 		=> array("varType" => "string"),
    "MCODE"	 		=> array("varType" => "string"),
    "AGEGROUPLC" 		=> array("varType" => "string"),
    "GROUPPT"	 		=> array("varType" => "string"),
    "MINIMUMORDERQUANTITY"	=> array("varType" => "string"),
    "NORMPACKING"	 	=> array("varType" => "string"),
    "FACTORYNUMBER"	 	=> array("varType" => "string"),
);

<?php


/*
* ��������
*/
$structures["Struct_LinemediaAuto_SearchPart"] = array(
    "article" 		    => array("varType" => "string"),
    "brand_title" 	    => array("varType" => "string"),
    "price" 		    => array("varType" => "float"),
    "delivery_time"     => array("varType" => "integer"),
    "id" 			    => array("varType" => "integer"),
    "title" 		    => array("varType" => "string"),
    "quantity" 		    => array("varType" => "float"),
    "part_search_url"   => array("varType" => "string"),
    "supplier_id"       => array("varType" => "string"),
    "buy_hash"          => array("varType" => "string"),
    //"extra"             => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
);


/*
* ������ �������� � ��������� ������
*/
$structures["Struct_LinemediaAuto_SearchAnalogs"] = array(
    "analog_type" 	=> array("varType" => "string"),
    "parts" 		=> array("varType" => "ArrayOfStruct_LinemediaAuto_SearchPart", "arrType" => "Struct_LinemediaAuto_SearchPart"),
);


/*
* ������� � ��������� ������
*/
$structures["Struct_LinemediaAuto_SearchCatalog"] = array(
    "title" 		=> array("varType" => "string"),
    "brand_title" 	=> array("varType" => "string"),
    "data-source" 	=> array("varType" => "string"),
    "extra" 		=> array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
);

/*
* ��������� �����
*/
$structures["Struct_LinemediaAuto_SearchResults"] = array(
    "type"		=> array("varType" => "string"),
    "parts"		=> array("varType" => "ArrayOfStruct_LinemediaAuto_SearchAnalogs", "arrType" => "Struct_LinemediaAuto_SearchAnalogs"),
    "catalogs"	=> array("varType" => "ArrayOfStruct_LinemediaAuto_SearchCatalog", "arrType" => "Struct_LinemediaAuto_SearchCatalog"),
);

$structures["Struct_LinemediaAuto_groupSearchResult"] = array(
    "result" => array("varType" => "ArrayOfStruct_LinemediaAuto_groupSearchResultItem", "arrType" => "Struct_LinemediaAuto_groupSearchResultItem"),
);

$structures["Struct_LinemediaAuto_groupSearchResultItem"] = array(
    "article"   => array("varType" => "string"),
    "brand"     => array("varType" => "string"),
    "quantity"  => array("varType" => "integer"),
    "parts"     => array("varType" => "ArrayOfStruct_LinemediaAuto_SearchPart", "arrType" => "Struct_LinemediaAuto_SearchPart"),
);

/*
 * �������� �������� ������� �������� ������
 */
$structures["Struct_LinemediaAuto_RequestItem"] = array(
    "article" => array("varType" => "string"),
    "brand" => array("varType" => "string"),
    "quantity" => array("varType" => "integer"),
);
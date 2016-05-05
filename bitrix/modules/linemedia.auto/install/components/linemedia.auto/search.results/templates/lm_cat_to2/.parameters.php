<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
CModule::IncludeModule("iblock");
CModule::IncludeModule("linemedia.auto");

$values = LinemediaAutoSupplier::getList();

$suppliers = array();
foreach ($values as $item) {
    $suppliers[$item['ID']] = $item['NAME'];
}

$arTemplateParameters = array(
    "HIDE_SUPPS"=>array(
        "NAME" => 'Те поставщики, которые',
        "TYPE" => "LIST",
        "MULTIPLE" => "Y",
        "VALUES" => $suppliers,
        "DEFAULT" => "",    
    ),
);*/
$arTemplateParameters = array(
    "QUANTITY_ROUNDING"=>array(
        "NAME" => GetMessage('LM_AUTO_QUANTITY_ROUNDING'),
        "TYPE" => "STRING",
        "DEFAULT" => "2",
    ),
);
?>

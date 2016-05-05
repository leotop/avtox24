<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $USER;

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('Module linemedia.auto not installed');
    return;
}

CModule::IncludeModule("iblock");

CJSCore::Init(array("jquery"));


$arParams['CALCULATE_DELAY'] = ($arParams['CALCULATE_DELAY'] == 'Y');
$arParams['DELAY'] = ($arParams['DELAY'] == 'Y');
$arParams['LIMIT'] = ($arParams['LIMIT'] == 'Y');
$arParams['FUNDS_ON_ACCOUT'] = ($arParams['FUNDS_ON_ACCOUT'] == 'Y');
$arParams['ORDER'] = ($arParams['ORDER'] == 'Y');


$suppliersID = array();
$unwroughtSuppliers = CIBlockElement::GetList(
		array(),
		array('IBLOCK_ID' => COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), 'ID' => \LinemediaAutoSupplier::getAllowedSuppliers() ,'ACTIVE' => 'Y')
);
	
while ($ob = $unwroughtSuppliers->GetNextElement()) {
	
	$id = $ob->GetFields();
	$id = $id['ID'];
	$ob = $ob->GetProperties();
	
	if ($ob['visual_title']['VALUE'] == null) {
		continue;
	}
	
	$suppliersID[$ob['visual_title']['VALUE']]['title'] = $ob['visual_title']['VALUE'];
	$suppliersID[$ob['visual_title']['VALUE']]['delivery'] = $ob['delivery_time']['VALUE'] != null ? $ob['delivery_time']['VALUE'] : 0;
	$suppliersID[$ob['visual_title']['VALUE']]['id'] = $ob['supplier_id']['VALUE'];
}


$arResult['suppliers'] = $suppliersID;

$this->IncludeComponentTemplate();

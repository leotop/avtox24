<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


/*
* Проверка наличия необходимых модулей
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}


/*
* Подключаемся к API
*/
$api = new LinemediaAutoApiDriver();
try {
	$data = $api->query('getAccountInfo', array());
} catch (Exception $e) {
	echo $e->GetMessage();
	return;
}

$catalogs = (array) $data['data']['original_catalogs'];

$arComponentParameters = array(
    "PARAMETERS" => array(
        'ADD_SECTIONS_CHAIN' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_ADD_CHAIN'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y'
        ),
        'HIDE_UNAVAILABLE' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_HIDE_UNAVAILABLE'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N',
        ),
        "SEF_MODE" => array(),
        "SEF_FOLDER" => array(),

        'DISABLE_STATS' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_DISABLE_STATS'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N',
        ),
        'INCLUDE_JQUERY' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_INCLUDE_JQUERY'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y',
        ),
        'VIN_URL' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_VIN_URL'),
                "TYPE" => "STRING",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'/auto/original/vin/',
        ),
    ),
);


/*foreach ($catalogs AS $catalog) {
	$available = ($catalog['available']) ? '' : ' ' . GetMessage('LM_AUTO_MAIN_CATALOG_UNAVAILABLE');
	$arComponentParameters['PARAMETERS']['CATALOG_' . strtoupper($catalog['brand_code'])] = array(
		"PARENT" => "BASE",
        "NAME" => GetMessage('LM_AUTO_MAIN_CATALOG') . ' ' .$catalog['brand_title'] . $available,
        "TYPE" => "STRING",
        "ADDITIONAL_VALUES" => "N",
        "MULTIPLE" => "N",
        "DEFAULT"=>'/auto/original/'.$catalog['brand_code'].'/',
	);
}*/
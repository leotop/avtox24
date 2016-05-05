<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("linemedia.auto")) {
    return;
}

if (CModule::IncludeModule("linemedia.autotecdoc")) {
	$sets_ids = LinemediaAutoTecdocApiModifications::getSetsIds();
	$sets_ids = array_combine($sets_ids, $sets_ids);
}

$sets_ids['default'] = GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_TECDOC_DEFAULT');

$arComponentParameters = array(
    "PARAMETERS" => array(
        "TECDOC_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_TECDOC_URL'),
            "TYPE" => "STRING",
            "DEFAULT" => COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DEMO_FOLDER', '/auto/').'tecdoc/'
        ),
        "GARAGE_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_GARAGE_URL'),
            "TYPE" => "STRING",
            "DEFAULT" => COption::GetOptionString('linemedia.auto', 'LM_AUTO_TECODC_DEMO_FOLDER', '/garage/')
        ),
        "ACTION_VAR" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_VAR_ACTION'),
            "TYPE" => "STRING",
            "DEFAULT" => 'act'
        ),
        "SHOW_CAR_BRANDS_IN_LINK" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_SHOW_CAR_BRANDS_IN_LINK'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => "N"
        ),
        'MODIFICATIONS_SET' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_MODIFICATIONS_SET'),
                "TYPE" => "LIST",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'default',
                'VALUES' => $sets_ids,
        ),
        "SET_TITLE" => array()
	),
);

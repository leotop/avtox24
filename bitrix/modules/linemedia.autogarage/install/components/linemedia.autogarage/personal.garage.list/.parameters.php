<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("linemedia.auto")) {
    return;
}

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
            "DEFAULT" => COption::GetOptionString('linemedia.auto', 'LM_AUTO_GARAGE_DEMO_FOLDER', '/garage/')
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
        "SET_TITLE" => array()
	),
);

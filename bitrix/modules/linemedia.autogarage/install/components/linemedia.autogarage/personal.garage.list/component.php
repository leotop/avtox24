<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("main")) {
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("iblock")) {
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO_MAIN_MODULE_NOT_INSTALL');
    return;
}

if (!CModule::IncludeModule("linemedia.autogarage")) {
    ShowError('LM_AUTO_GARAGE_MODULE_NOT_INSTALL');
    return;
}

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));
}

$arParams['IBLOCK_ID'] = COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');

$arParams['TECDOC_URL'] = (string) $arParams['TECDOC_URL'];
if (empty($arParams['TECDOC_URL'])) {
    $arParams['TECDOC_URL'] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DEMO_FOLDER', '/auto/').'tecdoc/';
}

$arParams['GARAGE_URL'] = (string) $arParams['GARAGE_URL'];
if (empty($arParams['GARAGE_URL'])) {
    $arParams['GARAGE_URL'] = COption::GetOptionString('linemedia.autogarage', 'LM_AUTO_GARAGE_DEMO_FOLDER', '/garage/');
}

$arParams['ACTION_VAR'] = (string) $arParams['ACTION_VAR'];
if (empty($arParams['ACTION_VAR'])) {
    $arParams['ACTION_VAR'] = 'act';
}

$arParams['SET_TITLE'] = in_array($arParams['SET_TITLE'], array('Y', 'N')) ? $arParams['SET_TITLE'] : 'N';

$arResult = array('ERRORS' => array());

if (intval($arParams['IBLOCK_ID']) > 0) {
    
    $arSort = array('NAME' => 'ASC');
    $arFilter = array('CREATED_BY' => $USER->GetID(), 'IBLOCK_ID' => $arParams['IBLOCK_ID']);
    $arSelectFields = array(
        'ID',
        'CREATED_BY',
        'NAME',
        'PROPERTY_VIN',
        'PROPERTY_BRAND',
        'PROPERTY_BRAND_ID',
        'PROPERTY_MODEL',
        'PROPERTY_MODEL_ID',
        'PROPERTY_MODIFICATION',
        'PROPERTY_MODIFICATION_ID',
        'PROPERTY_EXTRA',
    );
    
    $rsGarage = CIBlockElement::GetList($arSort, $arFilter, null, null, $arSelectFields);
    while ($arGarage = $rsGarage->Fetch()) {
        $arResult['ITEMS'][] = $arGarage;
    }
    
    /*
     * Событие для других модулей
     */
    $events = GetModuleEvents("linemedia.autogarage", "OnBuildListItems");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$arResult['ITEMS']));
        } catch (Exception $e) {
            $arResult['ERRORS'] []= $e->GetMessage();
        }
    }

   $arResult['NAV_STRING'] = $arParams['GARAGE_URL'];
   $arResult['ACTION'] = $ACTION;
   $arResult['ID'] = $ID;
}


$this->IncludeComponentTemplate();

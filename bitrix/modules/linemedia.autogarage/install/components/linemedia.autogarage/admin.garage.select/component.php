<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("iblock")) {
    ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    return;
}

if (!CModule::IncludeModule("sale")) {
    ShowError(GetMessage('SALE_MODULE_NOT_INSTALLED'));
    return;
}

if (!CModule::IncludeModule("linemedia.autogarage")) {
    ShowError(GetMessage('LM_AUTO_GARAGE_MODULE_NOT_INSTALLED'));
    return;
}

// Пользователь.
$arParams['USER_ID']         = (int) $arParams['USER_ID']; 
$arParams['PERSON_TYPE_ID']  = (int) $arParams['PERSON_TYPE_ID'];

$arResult = array('ITEMS' => array(), 'PROPERTIES' => array());



$iblock_id = COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');

$db = CSaleOrderProps::GetList(array(), array('CODE' => array('MARK_ID', 'MODEL_ID', 'MODIFICATION_ID', 'AUTO_TEXT'), 'PERSON_TYPE_ID' => $arParams['PERSON_TYPE_ID']), false, false, array('ID', 'CODE'));
while ($property = $db->Fetch()) {
    $arResult['PROPERTIES'][$property['CODE']] = $property['ID'];
}

if ( intval($arParams['ORDER_ID']) >0) {
    $rs = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID'=>$arParams['ORDER_ID'], 'CODE'=>array('MARK_ID', 'MODEL_ID', 'MODIFICATION_ID', 'AUTO_TEXT')));
    $arResult['CURVAL'] = array();
    while ($property = $rs->Fetch()) {
        $arResult['CURVAL'][$property['CODE']] = $property['VALUE'];
    }
}

if (!empty($arResult['PROPERTIES'])) {
    if ($arParams['USER_ID'] > 0) {
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
        
        $rsGarage = CIBlockElement::GetList(array('NAME' => 'ASC'), array('CREATED_BY' => $arParams['USER_ID'], 'IBLOCK_ID' => $iblock_id), null, null, $arSelectFields);
        while ($arGarage = $rsGarage->Fetch()) {
            $arResult['ITEMS'][$arGarage['ID']] = $arGarage;
        }
    }
}



$this->IncludeComponentTemplate();


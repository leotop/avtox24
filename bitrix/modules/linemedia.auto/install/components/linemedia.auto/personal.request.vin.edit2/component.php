<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*
 * Компонент отправляет запрос по VIN, работает на инфоблоках
 */

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTOPORTAL_MODULE_NOT_INSTALL'));
    return;
}

if (!CModule::IncludeModule('iblock')) {
    ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALL'));
    return;
}

function array_to_scv($array, $col_sep = ",", $row_sep = "\n", $qut = '"'){
	if (!is_array($array) or !is_array($array[0])) return false;
	
        foreach ($array as $key => $val){
		$tmp = '';
		foreach ($val as $cell_key => $cell_val){
			$cell_val = str_replace($qut, "$qut$qut", $cell_val);
			$tmp .= "$col_sep$qut$cell_val$qut";
		}
		$output .= substr($tmp, 1).$row_sep;
	}
	return $output;
}

//COption::SetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_lm_auto_vin", '285');
$iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_lm_auto_vin');
$arResult['IBLOCK'] = CIBlock::GetByID(intval($iblock_id))->Fetch();
if($arResult['IBLOCK'] === false){
    ShowError(GetMessage('LM_AUTOPORTAL_IBLOCK_NOT_FOUND'));
    return;
}

$fields_main = Array('vin', 'year', 'month', 'brand', 'model', 'modification', 'extra', 'horsepower', 'displacement'); //основной раздел
$fields_hidden = Array('brand_id', 'model_id', 'modification_id'); //поля, которые показываем в hidden
$fields_disabled = Array('reply'); //поля, которые совсем не выводим

$arResult['ERRORS'] = array();
$arResult['HTML'] = array();

/*
 * Событие для других модулей: получение дополнительного HTML для вывода.
 */
$events = GetModuleEvents("linemedia.auto", "OnVinShowHTML");
while ($arEvent = $events->Fetch()) {
    $arResult['HTML'][] = ExecuteModuleEventEx($arEvent, array(CUser::getID(), true));
}

$properties = Array();
$property_res = CIBlockProperty::GetList(Array('sort' => 'ASC'), Array('ACTIVE' => 'Y', 'IBLOCK_ID' => $iblock_id));
while($property = $property_res->Fetch()){
    //игнорируем не нужные свойства
    if(in_array($property['CODE'], $fields_disabled)){
        continue;
    }
    
    //пропустим свойство запроса
    if($property['CODE'] == 'request'){
        $arResult['REQUEST'] = $property;
        continue;
    }
    
    //выберем значения для свойств типа "список"
    if($property['PROPERTY_TYPE'] === 'L'){
        $prop_enum = CIBlockProperty::GetPropertyEnum($property['ID'], Array('SORT' => 'ASC'));
        while($enum = $prop_enum->Fetch()){
            $property['ENUM'][] = $enum;
        }
        unset($prop_enum, $enum);
    }
    
    //отправляемые значения
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_REQUEST['VIN_FIELDS'][$property['CODE']]) && !empty($_REQUEST['VIN_FIELDS'][$property['CODE']])){
            $property['VALUE'] = $_REQUEST['VIN_FIELDS'][$property['CODE']];
        }elseif($property['IS_REQUIRED'] === 'Y'){
            $arResult['ERRORS'][] = GetMessage('SUP_ERROR_REQUIRED', Array('#FIELD#' => $property['NAME']));
        }
    }
    
    //основные поля
    if(in_array($property['CODE'], $fields_main)){
        $arResult['FIELDS']['MAIN'][$property['CODE']] = $property;
    }
    //скрытые поля
    if(in_array($property['CODE'], $fields_hidden)){
        $arResult['FIELDS']['HIDDEN'][$property['CODE']] = $property;
    }
    //дополнительные поля
    if(!in_array($property['CODE'], $fields_main) && !in_array($property['CODE'], $fields_hidden)){
        $arResult['FIELDS']['EXTRA'][$property['CODE']] = $property;
    }
    
    $properties[$property['CODE']] = $property;
}
unset($property_res);

if (!empty($_REQUEST['save']) && $_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid()) {
    //$arResult['FIELDS_REQUEST'] = $_REQUEST['VIN_FIELDS'];
    
    //содержание запроса
    $arResult['REQUEST']['VALUE'] = Array();
    $request_isset = false;
    if(is_array($_REQUEST['request']['title']) && count($_REQUEST['request']['title']) > 0){
        foreach($_REQUEST['request']['title'] AS $field_key => $field_value){
            if(
               empty($_REQUEST['request']['title'][$field_key]) &&
               empty($_REQUEST['request']['art'][$field_key]) &&
               empty($_REQUEST['request']['quantity'][$field_key]) &&
               empty($_REQUEST['request']['comment'][$field_key])
               ){
                continue;
            }
            $arResult['REQUEST']['VALUE'][] = Array(
                                           'title' => $_REQUEST['request']['title'][$field_key],
                                           'art' => $_REQUEST['request']['art'][$field_key],
                                           'quantity' => $_REQUEST['request']['quantity'][$field_key],
                                           'comment' => $_REQUEST['request']['comment'][$field_key]
                                           );
            if(!empty($_REQUEST['request']['title'][$field_key]) || !empty($_REQUEST['request']['art'][$field_key])){
                $request_isset = true;
            }
        }
        unset($field_key, $field_value);
    }
    if($request_isset === false){
        $arResult['ERRORS'][] = GetMessage('SUP_ERROR_PARTS');
    }
    
    //все хорошо, добавляем запрос
    if (empty($arResult['ERRORS'])) {
        $element_fields = Array(
                                'IBLOCK_ID' => $iblock_id,
                                'NAME' => GetMessage('SUP_REQUEST_NAME', Array('#VIN#' => $properties['vin']['VALUE'])),
                                'ACTIVE' => 'Y'
                                );
        
        foreach($properties AS $prop_code => $property){
            if(!empty($property['VALUE'])){
                if($property['USER_TYPE'] === 'HTML'){
                    $element_fields['PROPERTY_VALUES'][$prop_code] = Array('VALUE' => Array('TYPE' => 'TEXT', 'TEXT' => $property['VALUE']));
                }else{
                    $element_fields['PROPERTY_VALUES'][$prop_code] = $property['VALUE'];
                }
            }
        }
        unset($prop_code, $property);
        $element_fields['PROPERTY_VALUES']['request'] =  Array('VALUE' => Array('TYPE' => 'TEXT', 'TEXT' => array_to_scv($arResult['REQUEST']['VALUE'])));
        
        $oIblockElement = new CIBlockElement;
        if($element_id = $oIblockElement->Add($element_fields)){
            $arResult['MESSAGE'] = GetMessage('SUP_ADD_SUCCESS');
        }else{
            $arResult['ERRORS'][] = GetMessage('SUP_ERROR_SEND');
        }
    }
}

$this->IncludeComponentTemplate();
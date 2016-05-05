<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('linemedia.auto')) {
	ShowError('LM_AUTO_MAIN_MODULE_NOT_INSTALL');
	return;
}

if (!CModule::IncludeModule("linemedia.autotecdoc")) {
	ShowError('No install modules linemedia.autotecdoc');
	throw new Exception('No install modules linemedia.autotecdoc');
	return;
}

$arParams['ACTIONS'] = (!empty($arParams['ACTIONS'])) ? ((array) $arParams['ACTIONS']) : ((array) $_REQUEST['actions']);
                                    
$arParams['BRAND_ID'] = (intval($arParams['BRAND_ID']) > 0) ? intval($arParams['BRAND_ID']) : intval($_REQUEST['brand_id']);
                                    
$arParams['MODEL_ID'] = (intval($arParams['MODEL_ID']) > 0) ? intval($arParams['MODEL_ID']) : intval($_REQUEST['model_id']);
                                    
$arParams['MODIFICATION_ID'] = (intval($arParams['MODIFICATION_ID']) > 0) ? intval($arParams['MODIFICATION_ID']) : intval($_REQUEST['modification_id']);

if (empty($arParams['MODIFICATIONS_SET'])) {
    $arParams['MODIFICATIONS_SET'] = 'default';
}

if (empty($arParams['ACTIONS'])) {
    $arParams['ACTIONS'] = array('getBrands');
}


/*
 * Подключим на страницу jQuery
 */
CJSCore::Init(array('jquery'));


/*
 * Класс для доступа к API.
 */
$api = new LinemediaAutoTecDocApiDriver();
$api->changeModificationsSetId($arParams['MODIFICATIONS_SET']);

$arResult = array();

foreach ($arParams['ACTIONS'] as $action) {
    switch ($action) {
        // Бренды.
        case 'getBrands':
            try {
                $response = $api->query('getBrands', $data = array('types' => array()));
                if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                    $result = $response['data'];
                }
                if (!empty($result)) {
                    $arResult['brands'] = array();
                    $result = (array) $result;
                    if (count($result) > 0) {
                        foreach ($result as $value) {
                            if ($value['hidden'] == 'Y') {
                                continue;
                            }
                            $arResult['brands'][$value['manuId']] = $value;
                        }
                        unset($value);
                    }
                    unset($result);
                    unset($response);
                }
            } catch (Exception $e) {
                // nothing
            }
            break;
        
        // Модели.
        case 'getModels':
            try {
                $response = $api->query('getVehicleModels', $data = array('types' => array(), 'brand_id' => $arParams['BRAND_ID']));
                
	            if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                    $result = $response['data'];
                }
                if (!empty($result)) {
                    $arResult['models'] = array();
                    $result = (array) $result;
                    if (count($result) > 0) {
                        foreach ($result as $value) {
                            if ($value['hidden'] == 'Y') {
                                continue;
                            }
                            $arResult['models'][$value['modelId']] = $value;
                        }
                        unset($value);
                    }
                    unset($result);
                    unset($response);
                }
            } catch (Exception $e) {
                // nothing
            }
            break;
        
        // Модификации.
        case 'getModifications':
            try {
                $response = $api->query('getModelVariantsWithInfo', $data = array('brand_id' => $arParams['BRAND_ID'], 'model_id' => $arParams['MODEL_ID']));
                if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                    $result = $response['data'];
                }
                
                if (!empty($result)) {
                    $arResult['modifications'] = Array();
                    $result = (array) $result;
                    if (count($result) > 0) {
                        foreach ($result as $value) {
                            if ($value['hidden'] == 'Y') {
                                continue;
                            }
                            $arResult['modifications'][$value['carId']] = $value;
                        }
                        unset($value);
                    }
                    unset($result);
                    unset($response);
                }
            } catch (Exception $e) {
                // nothing
            }
            break;
    }
}

$this->IncludeComponentTemplate();
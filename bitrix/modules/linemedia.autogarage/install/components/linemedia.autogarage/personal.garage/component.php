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

    echo ShowError(GetMessage("LM_AUTO_GARAGE_ACCESS_DENIED"));
	return;
}


$api = new LinemediaAutoApiDriver();


try {

	$account_info = $api->getAccountInfo();
	$available_tecdoc = $account_info['data']['tecdoc']['available'];

} catch (Exception $e) {
	$errorAPI = $e->getMessage();
	$available_tecdoc = false;
}



$arParams['IBLOCK_ID'] = COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');

$arParams['GARAGE_URL'] = (string) $arParams['GARAGE_URL'];
if (empty($arParams['GARAGE_URL'])) {
    $arParams['GARAGE_URL'] = COption::GetOptionString('linemedia.autogarage', 'LM_AUTO_GARAGE_DEMO_FOLDER', '/garage/');
}

$arParams['TECDOC_URL'] = (string) $arParams['TECDOC_URL'];
if (empty($arParams['TECDOC_URL'])) {
    $arParams['TECDOC_URL'] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_TECODC_DEMO_FOLDER', '/auto/').'tecdoc/';
}

$arParams['ACTION_VAR'] = (string) $arParams['ACTION_VAR'];
if (empty($arParams['ACTION_VAR'])) {
    $arParams['ACTION_VAR'] = 'act';
}

$arParams['SET_TITLE'] = in_array($arParams['SET_TITLE'], array('Y', 'N')) ? $arParams['SET_TITLE'] : 'N';

$arParams['MODIFICATIONS_SET'] = $arParams['MODIFICATIONS_SET'] ? : 'default';
/*
 * Коды доступа
 */
$api_id  = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_ID');
$api_key = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_KEY');

$oAutoportal = new LinemediaAutoApiDriver($api_id, $api_key);


$arResult = array('ERRORS' => array());

if (intval($arParams['IBLOCK_ID']) > 0) {
    $allowedActions = array('list', 'edit', 'delete');

    $ACTION =   isset($_REQUEST[$arParams['ACTION_VAR']])
                && is_string($_REQUEST[$arParams['ACTION_VAR']])
                && in_array($_REQUEST[ $arParams['ACTION_VAR']], $allowedActions) ? $_REQUEST[$arParams['ACTION_VAR']] : $allowedActions[0];


    $res =  CIBlockElement::GetByID((int) $_REQUEST['id']);
    $arResult['ITEM'] = ($res) ? ($res->GetNext()) : (null);

    $ID = ('list' != $ACTION) && isset($_REQUEST['id']) && intval($_REQUEST['id']) && ($arResult['ITEM']) && ($arResult['ITEM']['CREATED_BY'] == $USER->GetID()) ? intval($_REQUEST['id']) : 0;

    if (0 == $ID) {
        $arResult['ITEM'] = null;
    }
    if ($arParams['SET_TITLE'] == 'Y') {
        global $APPLICATION;
        switch ($ACTION) {
            case 'list':
                $APPLICATION->SetTitle(GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_MY_GARAGE'));
                break;

            case 'edit':
                if (intval($ID) > 0) {
                    $APPLICATION->SetTitle(GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_EDIT_PARAMS_AUTO'));
                } else {
                    $APPLICATION->SetTitle(GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_ADD_AUTO'));
                }
                break;
        }
    }
    $arResult['BRANDS'] = Array();

    /*
     * Получение $aBrands списка всех брендов
     */

    if ($available_tecdoc == 1) {

    	try {
    		$aBrands = array('success' => false, 'msg' => 'server error', 'data' => array());

    		$brands = $oAutoportal->query('getBrands', array());

    		if (is_array($brands)) {
    			if ($brands['status'] == 'ok') {
    				if (isset($brands['data']) && is_array($brands['data']) && count($brands['data']) > 0) {
    					$aBrands['success'] = true;
    					$aBrands['msg'] = '';
    					$aBrands['data'] = $brands['data'];
    				}
    			} else {
    				if (isset($brands['error']['text'])) {
    					$aBrands['msg'] = $brands['error']['text'];
    				} else {
    					$aBrands['msg'] = 'Server error';
    				}
    			}
    		}
    	} catch (Exception $e) {
    		ShowError($e->GetMessage());
    		return;
    	}
    }

    if ($aBrands['success'] === true && count($aBrands['data']) > 0) {
        $arResult['BRANDS'] = $aBrands['data'];
    }
    unset($aBrands);

    $arResult['ITEMS'] = array();
    $arResult['PROPS'] = array();

    /*
     * Удаление
     */
    if ('delete' == $ACTION) {
        if ($ID) {
            $element = new LinemediaAutoGarage($ID);
            try {
                $r = $element->delete();
                LocalRedirect($arParams['GARAGE_URL']);
                exit();
            } catch (Exception $e) {
                $arResult['ERRORS'] []= $e->GetMessage();
            }
        }
    }

    /*
     * manual adding automobile in garage
     */

    if ('edit' == $ACTION && $available_tecdoc != 1) {
    	if ('POST' == $_SERVER['REQUEST_METHOD']) {
    		$arPropertyCodes = array(
    				'vin',
    				'brand',
    				'model',
    				'modification',
    				'extra',
    		);
    		$arPropertyData = array();
    		foreach ($_POST as $k => $v) {
    			if (in_array($k, $arPropertyCodes)) {
    				$arPropertyData[$k] = trim($v);
    				if ('extra' == $k) {
    					$arPropertyData[$k] = array('VALUE' => array('TEXT' => trim($v), 'TYPE' => 'text'));
    				}
    			}
    		}


    		$timestamp = new DateTime(date(DateTime::ATOM));

    		/*
    		 * Бренд, модель и модификация авто обязательны
    		*/

    		$arPropertyData["brand_id"] = (int) $timestamp->getTimestamp() + 1;
    		$arPropertyData["model_id"] = (int) $timestamp->getTimestamp() + 2;
    		$arPropertyData["modification_id"] = (int) $timestamp->getTimestamp() + 3;
    		if (!$arPropertyData["brand"] || !$arPropertyData["model"] || !$arPropertyData["modification"]) {
    			$arResult['ERROR'] = GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_SET_PARAMS_AUTO');
    		}

    		if (!strlen($arResult['ERROR'])) {
    			$arDataFields = array(
    					'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    					'CREATED_BY' => $USER->GetID(),
    					'NAME' => ($arPropertyData['brand'] && $arPropertyData['model']) ? $arPropertyData['brand'] . ' ' . $arPropertyData['model'] : '-',
    					'PROPERTY_VALUES' => $arPropertyData,
    			);

    			$el = new CIBlockElement();
    			$result = $ID ? $el->Update($ID, $arDataFields, false, false, false) : $el->Add($arDataFields, false, false, false);
    			if (false === $result) {
    				$arResult['ERROR'] = $el->LAST_ERROR;
    			} else {
    				LocalRedirect($arParams['GARAGE_URL']);
    				exit();
    			}
    		}

    	}

    	$rsProps = CIBlockProperty::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $arParams['IBLOCK_ID']));
    	while ($arProps = $rsProps->Fetch()) {
    		if ('L' == $arProps['PROPERTY_TYPE']) {
    			$props = array();
    			$rsPropsVals = CIBlockPropertyEnum::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'PROPERTY_ID' => $arProps['ID']));
    			while ($arPropsVals = $rsPropsVals->Fetch()) {
    				$props[] = $arPropsVals;
    			}
    			$arProps['VALUE_ENUM'] = $props;
    		}
    		$arResult['PROPS'][] = $arProps;
    	}
    	if ($arResult['ITEM']['ID']) {
    		$dbprops = CIBlockElement::GetProperty($arParams['IBLOCK_ID'], $arResult['ITEM']['ID']);

    		$props = array();
    		while ($prop = $dbprops->GetNext()) {
    			if (empty($props[$prop['CODE']])) {
    				$props[$prop['CODE']] = $prop;
    			} else {
    				if (!is_array($props[$prop['CODE']]['VALUE'])) {
    					$props[$prop['CODE']]['VALUE'] = array($props[$prop['CODE']]['VALUE']);
    				}
    				if (!is_array($props[$prop['CODE']]['~VALUE'])) {
    					$props[$prop['CODE']]['~VALUE'] = array($props[$prop['CODE']]['~VALUE']);
    				}
    				$props[$prop['CODE']]['VALUE'][] = $prop['VALUE'];
    				$props[$prop['CODE']]['~VALUE'][] = $prop['~VALUE'];
    			}
    		}
    		unset($dbprops, $prop);
    		$arResult['ITEM_PROPERTIES'] = $props;
    	}

    }

    /*
     * Редактирование
     */
    if ('edit' == $ACTION && $available_tecdoc == 1) {
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $arPropertyCodes = array(
                'vin',
                'brand',
                'brand_id',
                'model',
                'model_id',
                'modification',
                'modification_id',
                'extra',
            );
            $arPropertyData = array();
            foreach ($_POST as $k => $v) {
                if (in_array($k, $arPropertyCodes)) {
                    $arPropertyData[$k] = trim($v);
                    if ('extra' == $k) {
                        $arPropertyData[$k] = array('VALUE' => array('TEXT' => trim($v), 'TYPE' => 'text'));
                    }
                }
            }

           /*
            * Бренд, модель и модификация авто обязательны
            */
           $arPropertyData["brand_id"] = (int)$arPropertyData["brand_id"];
           $arPropertyData["model_id"] = (int)$arPropertyData["model_id"];
           $arPropertyData["modification_id"] = (int)$arPropertyData["modification_id"];
           if (!$arPropertyData["brand_id"] || !$arPropertyData["model_id"] || !$arPropertyData["modification_id"]) {
                $arResult['ERROR'] = GetMessage('LM_AUTO_GARAGE_PERSONAL_GARAGE_SET_PARAMS_AUTO');
           }

            if (!strlen($arResult['ERROR'])) {
                $arDataFields = array(
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'CREATED_BY' => $USER->GetID(),
                    'NAME' => ($arPropertyData['brand'] && $arPropertyData['model']) ? $arPropertyData['brand'] . ' ' . $arPropertyData['model'] : '-',
                    'PROPERTY_VALUES' => $arPropertyData,
                );

                $el = new CIBlockElement();
                $result = $ID ? $el->Update($ID, $arDataFields, false, false, false) : $el->Add($arDataFields, false, false, false);
                if (false === $result) {
                    $arResult['ERROR'] = $el->LAST_ERROR;
                } else {
                    LocalRedirect($arParams['GARAGE_URL']);
                    exit();
                }
            }
        }

        $rsProps = CIBlockProperty::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $arParams['IBLOCK_ID']));
        while ($arProps = $rsProps->Fetch()) {
            if ('L' == $arProps['PROPERTY_TYPE']) {
                $props = array();
                $rsPropsVals = CIBlockPropertyEnum::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'PROPERTY_ID' => $arProps['ID']));
                while ($arPropsVals = $rsPropsVals->Fetch()) {
                    $props[] = $arPropsVals;
                }
                $arProps['VALUE_ENUM'] = $props;
            }
            $arResult['PROPS'][] = $arProps;
        }
        if ($arResult['ITEM']['ID']) {
            $dbprops = CIBlockElement::GetProperty($arParams['IBLOCK_ID'], $arResult['ITEM']['ID']);

            $props = array();
            while ($prop = $dbprops->GetNext()) {
                if (empty($props[$prop['CODE']])) {
                    $props[$prop['CODE']] = $prop;
                } else {
                  if (!is_array($props[$prop['CODE']]['VALUE'])) {
                    $props[$prop['CODE']]['VALUE'] = array($props[$prop['CODE']]['VALUE']);
                  }
                  if (!is_array($props[$prop['CODE']]['~VALUE'])) {
                    $props[$prop['CODE']]['~VALUE'] = array($props[$prop['CODE']]['~VALUE']);
                  }
                  $props[$prop['CODE']]['VALUE'][] = $prop['VALUE'];
                  $props[$prop['CODE']]['~VALUE'][] = $prop['~VALUE'];
                }
            }
            unset($dbprops, $prop);
            $arResult['ITEM_PROPERTIES'] = $props;
        }

        if (!$arResult['ITEM_PROPERTIES']['model']['VALUE'] && $arResult['ITEM_PROPERTIES']['brand_id']['VALUE']) {
            $arResult['MODELS'] = array();

            $aModels = array('success' => false, 'msg' => 'server error', 'data' => array());
            try {
                /*
                 * Получение cпискa от текдока всех моделей данного производителея авто по id бренда
                 */
                $models = $oAutoportal->query('getVehicleModels', array('brand_id' => intval($arResult['ITEM_PROPERTIES']['brand_id']['VALUE'])));

                if (is_array($models)) {
                    if ($models['status'] == 'ok') {
                        if (isset($models['data']) && is_array($models['data']) && count($models['data']) > 0) {
                            $aModels['success'] = true;
                            $aModels['msg'] = '';
                            $aModels['data'] = $models['data'];
                        }
                    } else {
                        if (isset($models['error']['text'])) {
                            $aModels['msg'] = $models['error']['text'];
                        } else {
                            $aModels['msg'] = 'Server error';
                        }
                    }
                }
            } catch (Exception $e) {
                ShowError($e->GetMessage());
                return;
            }

            if ($aModels['success'] === true && count($aModels['data']) > 0) {
                $arResult['MODELS'] = $aModels['data'];
            }
            unset($aModels);
        }

        if (!$arResult['ITEM_PROPERTIES']['modification']['VALUE'] && $arResult['ITEM_PROPERTIES']['brand_id']['VALUE'] && $arResult['ITEM_PROPERTIES']['model_id']['VALUE']) {
            $arResult['MODIFICATIONS'] = array();

            $aModifications = array('success' => false, 'msg' => 'server error', 'data' => array());
            try {
                /*
                 * Получение от текдока вариантов(типы) модели по ID с дополнительной инфой
                 */
                $modification = $oAutoportal->query('getModelVariantsWithInfo', array('brand_id' => intval($arResult['ITEM_PROPERTIES']['brand_id']['VALUE']), 'model_id' => intval($arResult['ITEM_PROPERTIES']['model_id']['VALUE'])));

                if (is_array($modification)) {
                    if ($modification['status'] == 'ok') {
                        if (isset($modification['data']) && is_array($modification['data']) && count($modification['data']) > 0) {
                            $aModifications['success'] = true;
                            $aModifications['msg'] = '';
                            $aModifications['data'] = $modification['data'];
                        }
                    } else {
                        if (isset($modification['error']['text'])) {
                            $aModifications['msg'] = $modification['error']['text'];
                        } else {
                            $aModifications['msg'] = 'Server error';
                        }
                    }
                }
            } catch (Exception $e) {
                ShowError($e->GetMessage());
                return;
            }


            if ($aModifications['success'] === true && count($aModifications['data']) > 0) {
                $arResult['MODIFICATIONS'] = $aModifications['data'];
            }
            unset($aModifications);
        }
    }

    /*
     * Список.
     */
    if ('list' == $ACTION) {
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
    }

   $arResult['NAV_STRING'] = $arParams['GARAGE_URL'];
   $arResult['ACTION'] = $ACTION;
   $arResult['ID'] = $ID;

}

if ($available_tecdoc != 1) {

	$arResult['UNAVAILABLE_TECDOC'] = true;
	$arResult['ERRORS'][] = $errorAPI;
	$this->IncludeComponentTemplate('unavailable_api');
}
else {
	$arResult['UNAVAILABLE_TECDOC'] = false;
	$this->IncludeComponentTemplate();
}



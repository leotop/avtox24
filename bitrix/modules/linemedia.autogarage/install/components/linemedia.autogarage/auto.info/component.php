<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("sale")) {
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

$arParams['BRAND_ID'] = (intval($arParams['BRAND_ID']) > 0) ? intval($arParams['BRAND_ID']) : 0;
$arParams['MODEL_ID'] = (intval($arParams['MODEL_ID']) > 0) ? intval($arParams['MODEL_ID']) : 0;
$arParams['MODIFICATION_ID'] = (intval($arParams['MODIFICATION_ID']) > 0) ? intval($arParams['MODIFICATION_ID']) : 0;

if (empty($arParams['BRAND_ID']) && !empty($_REQUEST['brand_id'])) {
    $arParams['BRAND_ID'] = intval($_REQUEST['brand_id']);
}
if (empty($arParams['MODEL_ID']) && !empty($_REQUEST['model_id'])) {
    $arParams['MODEL_ID'] = intval($_REQUEST['model_id']);
}
if (empty($arParams['MODIFICATION_ID']) && !empty($_REQUEST['modification_id'])) {
    $arParams['MODIFICATION_ID'] = intval($_REQUEST['modification_id']);
}

/*
 * Функция для сортировки брендов внутри буквенной группы
*/
if (!function_exists('tecdocItemsSort')) {
    function tecdocItemsSort($a, $b)
    {

        /*
         *    если указано только у одного элемента, то второму ставим 500 (потому что стандартное битриксное значение для "сортировка по умолчанию")
        */
        if ( isset($a['sort']) ^ isset($b['sort'])) {
            $a['sort'] =  isset($a['sort']) ? $a['sort'] : 500;
            $b['sort'] =  isset($b['sort']) ? $b['sort'] : 500;
        }
        /*
         * Если отсутствуют поля сортировки или оба поля 500
        */
        if ((!isset($a['sort']) && !isset($b['sort'])) || ($a['sort'] == 500 && $b['sort'] == 500)) {
            /*
             * Бренды
            */
            if (isset($a['manuName'])) {
                if ($a['manuName'] == $b['manuName']) {
                    return 0;
                }
                return ($a['manuName'] < $b['manuName']) ? -1 : 1;
            }

            /*
             * Модели
            */
            if (isset($a['modelname'])) {
                if ($a['modelname'] == $b['modelname']) {
                    return 0;
                }
                return ($a['modelname'] < $b['modelname']) ? -1 : 1;
            }


            /*
             * Модификации
            */
            if (isset($a['carName'])) {
                if ($a['carName'] == $b['carName']) {
                    return 0;
                }
                return ($a['carName'] < $b['carName']) ? -1 : 1;
            }


            /*
             * Группы
            */
            if (isset($a['assemblyGroupName'])) {
                // TODO: не факт, что работает на 1251
                $a['assemblyGroupName'] = mb_convert_case($a['assemblyGroupName'], MB_CASE_TITLE);
                $b['assemblyGroupName'] = mb_convert_case($b['assemblyGroupName'], MB_CASE_TITLE);

                if ($a['assemblyGroupName'] == $b['assemblyGroupName']) {
                    return 0;
                }
                return ($a['assemblyGroupName'] < $b['assemblyGroupName']) ? -1 : 1;
            }


            /*
             * Детали сортируются по цене, затем по бренду
            */
            if (isset($a['articleNo'])) {

                $a_min = (int) $a['min_price'];
                $b_min = (int) $b['min_price'];

                $a_min = ($a_min) ? $a_min : 999999999;
                $b_min = ($b_min) ? $b_min : 999999999;

                if ($a_min == $b_min) {
                    $a['brandName'] = mb_convert_case($a['brandName'], MB_CASE_TITLE);
                    $b['brandName'] = mb_convert_case($b['brandName'], MB_CASE_TITLE);

                    if ($a['brandName'] == $b['brandName']) {
                        return 0;
                    }
                    return ($a['brandName'] < $b['brandName']) ? -1 : 1;
                }
                return ($a_min < $b_min) ? -1 : 1;
            }
        }

        if ($a['sort'] == $b['sort']) {
            return 0;
        }
        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }
}





/*
 * Коды доступа
 */
$api_id  = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_ID');
$api_key = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_KEY');

$oAutoportal = new LinemediaAutoApiDriver($api_id, $api_key);

$oAutoportal->changeModificationsSetId($arParams['MODIFICATIONS_SET']);

$tecdoc = LinemediaAutoTecDocRights::getInstance();

$tecdoc = new LinemediaAutoGarageApiTecDoc($arParams['MODIFICATIONS_SET']);

$arResult = array();

/*
 * Бренды.
 */
$arResult['BRANDS'] = array();
try {
    $aBrandRes = $tecdoc->getBrands();
} catch (Exception $e) {
    ShowError($e->GetMessage());
    return;
}


if (is_array($aBrandRes['ITEMS']) && count($aBrandRes['ITEMS']) > 0) {
    foreach ($aBrandRes['ITEMS'] as $aBrandItem) {
        $arResult['BRANDS'][$aBrandItem['manuId']] = $aBrandItem;
    }
    unset($aBrandItem);
}
unset($aBrandRes['ITEMS']);

uasort($arResult['BRANDS'], 'tecdocItemsSort');


/*
 * Модели.
 */
if ($arParams['BRAND_ID'] != 0) {
    $arResult['MODELS'] = array();

    try {
        $aModels = $tecdoc->getModels(array('brand_id' => $arParams['BRAND_ID']));
    } catch (Exception $e) {
        ShowError($e->GetMessage());
        return;
    }

    if (is_array($aModels['ITEMS']) && count($aModels['ITEMS']) > 0) {
        foreach ($aModels['ITEMS'] as $aModelItem) {
            $arResult['MODELS'][$aModelItem['modelId']] = $aModelItem;
        }
        unset($aModelItem);
    }
    unset($aModels);
}


/*
 * Модификации.
 */
if ($arParams['MODEL_ID'] != 0 && $arParams['BRAND_ID'] != 0) {
    $arResult['MODIFICATIONS'] = array();

    try {
        $aModifications = $tecdoc->getModifications(array('brand_id' => $arParams['BRAND_ID'], 'model_id' => $arParams['MODEL_ID']));
    } catch (Exception $e) {
        ShowError($e->GetMessage());
        return;
    }

    if (is_array($aModifications['ITEMS']) && count($aModifications['ITEMS']) > 0) {
        foreach($aModifications['ITEMS'] as $aModificationItem) {
            $arResult['MODIFICATIONS'][$aModificationItem['carId']] = $aModificationItem;
        }
        unset($aModificationItem);
    }
    unset($aModifications);
}

$this->IncludeComponentTemplate();






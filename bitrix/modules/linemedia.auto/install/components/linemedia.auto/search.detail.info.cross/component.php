<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}

/*
 * Компонент выводит детальную информацию о запчасти текдока.
 */

include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/linemedia.auto/classes/general/api_crosses_driver.php");

$arParams['AJAX'] = ($arParams['AJAX'] == 'Y') ? 'Y' : 'N';


//if (empty($arParams['SEARCH_URL'])) {
//    $arParams['SEARCH_URL'] = '/auto/search/';
//}

//if (empty($arParams['ADD_SECTIONS_CHAIN'])) {
//    $arParams['ADD_SECTIONS_CHAIN'] = 'Y';
//}

if (empty($arParams['SHOW_ORIGINAL_ITEMS'])) {
    $arParams['SHOW_ORIGINAL_ITEMS'] = 'Y';
}

if (empty($arParams['SHOW_APPLICABILITY'])) {
    $arParams['SHOW_APPLICABILITY'] = 'Y';
}

if (empty($arParams['SHOW_SEARCH_FORM'])) {
    $arParams['SHOW_SEARCH_FORM'] = 'Y';
}

if (empty($arParams['CACHED'])) {
    $arParams['CACHED'] = 'Y';
}

if (empty($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}
$arParams['CACHE_TIME'] = (int) $arParams['CACHE_TIME'];


$arParams['ARTICLE_ID'] = trim((string) $arParams['ARTICLE_ID']);


/* 
 * Для ajax-вызова не делаем преждевременный запрос в api.
 */
if ($arParams['AJAX_CALL'] == 'Y') {
    $this->IncludeComponentTemplate();
    return;
}


if (empty($arParams['ARTICLE_ID'])) {
    // CHTTP::SetStatus('404 Not Found');
    ShowError(GetMessage('LM_AUTO_MAIN_DETAIL_NOT_FOUND'));
    return;
}


$arResult = array();

/*
 * Подключение к API.
 */
$api_crosses_driver = new LinemediaAutoCrossesApiDriver();

/*
 * Информация о детали.
*/
try {

    // Запрос данных из API.
    $request_args = array($arParams['ARTICLE_ID']);

    $request_args['options'] = array(
        'include_properties' => true,
        'include_images' => true,
        'include_oem' => ($arParams['SHOW_ORIGINAL_ITEMS'] == 'Y'),
        'include_appliance' => false,
        'include_appliance_brands' => ($arParams['SHOW_APPLICABILITY'] == 'Y'),
        'include_data' => false,
    );

    $return_response = $api_crosses_driver->getPartInfoByArtIds($request_args);

    if($return_response['status'] == 'ok') {
        $arResult['DATA'] = current($return_response['data']);
    } else {
        $arResult['ERRORS'] = $return_response['error'];
    }

} catch (Exception $e) {
    $arResult['ERRORS'] []= $e->getMessage();
}



/*
 * Деталь не найдена или ошибка.
 */
if (!empty($arResult['ERRORS']) || empty($arResult['DATA'])) {
    // CHTTP::SetStatus("404 Not Found");

    // DEBUG: Не нашелся бренд.
    LinemediaAutoDebug::add('Tecdoc not found detail ['.$arParams['BRAND'].'] '.$arParams['ARTICLE'], false, LM_AUTO_DEBUG_ERROR);

    ShowError(GetMessage('LM_AUTO_MAIN_DETAIL_NOT_FOUND'));
    return;
}

/*
 * Изображение.
 */
$arResult['IMAGE'] = $arResult['DATA']['info']['images'][0]['URL'];

/*
 * Установить название в заголовок.
 */
if ($arParams['SET_TITLE'] == 'Y') {
    $APPLICATION->SetTitle($arResult['DATA']['BRAND'].' '.$arResult['DATA']['ARTICLE']);
}

/*
 * Добавлять в цепочку навигации.
 */
if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
    $APPLICATION->AddChainItem($arResult['DATA']['BRAND'].' '.$arResult['DATA']['ARTICLE'], null);
}


$this->IncludeComponentTemplate();

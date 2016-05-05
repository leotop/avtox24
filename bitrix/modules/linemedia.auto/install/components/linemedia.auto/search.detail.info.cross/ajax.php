<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/*
 * Если надо только уточнить применимость.
 */
if ($_REQUEST['applicability'] == 'Y') {
    
    if (!CModule::IncludeModule('linemedia.auto')) {
        ShowError(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTO_MODULE'));
        return;
    }

    /*
     * Подключение к API.
     */
    $api_crosses_driver = new LinemediaAutoCrossesApiDriver();

    try {

        // Запрос данных из API.
        $request_args = array(
            'art_id' => (int) $_REQUEST['article_id'],
            'mfa_id' => (int) $_REQUEST['mfa_id'],
        );

        $return_response = $api_crosses_driver->getApplianceByArtMfaId($request_args);

        if($return_response['status'] == 'ok') {
            $arResult['APPLICABILITY'] = $return_response['data']['result'];
        } else {
            $arResult['ERRORS'] = $return_response['error'];
        }

    } catch (Exception $e) {
        $arResult['ERROR'] = $e->GetMessage();
        include(dirname(__FILE__).'/templates/'.$template.'/error.php');
        die();
    }
    include(dirname(__FILE__).'/templates/'.$template.'/applicability.php');
    die();
}

//$APPLICATION->ShowHead();

$APPLICATION->IncludeComponent(
    "linemedia.auto:search.detail.info.cross",
    "popup",
    array(
        'AJAX'          => 'N',
        'ARTICLE_ID'    => $_REQUEST['article_id'],
    ),
    false
);



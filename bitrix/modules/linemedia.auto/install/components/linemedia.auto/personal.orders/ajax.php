<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', true);

global $USER;


/*
 * языковой файл
 */
include('lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!CModule::IncludeModule('linemedia.auto')) {
    die('no module');
}

/*
 *  акое действие надо выполнить?
 */
$action = (string) $_REQUEST['action'];

if($action == 'return' && check_bitrix_sessid()) {

    $basketId = intval($_REQUEST['basket']);
    $returnStatus = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_USER_RETURN');

    if($basketId > 0 && strlen($returnStatus) == 1) {
        $lmCart = new LinemediaAutoBasket();
        $lmCart->statusItem($basketId, $returnStatus);
        $arProps = $lmCart->getProps($basketId);
        if ($ex = $APPLICATION->GetException()) {
            die($ex->GetString());
        } else {
            /*
             * —обытие на отправку статусов.
             */
            $events = GetModuleEvents("linemedia.auto", "OnAfterBasketStatusesChange");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array(array($basketId), $returnStatus));
            }
            $_SESSION['MSG_RETURN_ACTION'] = str_replace('#NAME#', $basketId, GetMessage('RETURN_STATUS_IS_SET'));
            die('OK');
        }
    } else {
        die('basket error');
    }
} else if($action == 'cancel' && check_bitrix_sessid()) {

    $basketId = intval($_REQUEST['basket']);
    $cancelStatusReason = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_USER_CANCEL_REASON');

    if($basketId > 0) {
        $lmCart = new LinemediaAutoBasket();
        $lmCart->cancelItem($basketId, 'Y', $cancelStatusReason);

        if($statusAfterCancel) {
            $lmCart->statusItem($basketId, $statusAfterCancel);
        }

        if ($ex = $APPLICATION->GetException()) {
            die($ex->GetString());
        } else {
            $_SESSION['MSG_CANCEL_ACTION'] = str_replace('#NAME#', $basketId, GetMessage('BASKET_IS_CANCELED'));
            die('OK');
        }
    } else {
        die('basket error');
    }
} else if($action == 'remove_cancel' && check_bitrix_sessid()) {

    $basketId = intval($_REQUEST['basket']);

    if($basketId > 0) {

        $lmCart = new LinemediaAutoBasket();
        $lmCart->cancelItem($basketId, 'N');

        if ($ex = $APPLICATION->GetException()) {
            die($ex->GetString());
        } else {
            $_SESSION['MSG_CANCEL_ACTION'] = str_replace('#NAME#', $basketId, GetMessage('BASKET_CANCEL_REMOVE'));
            die('OK');
        }
    } else {
        die('basket error');
    }
}

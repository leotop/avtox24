<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule('sale')) {
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


/*
 * Обработка параметров.
 */
if(is_array($arParams['USER_ID']) && count($arParams['USER_ID']) > 0) {
    $arParams['USER_ID'] = $arParams['USER_ID'];
} else {
    $arParams['USER_ID'] = (int) (!empty($arParams['USER_ID'])) ? ($arParams['USER_ID']) : (CUser::GetID());
}

$arParams['USER_ID'] = (int) (!empty($arParams['USER_ID'])) ? ($arParams['USER_ID']) : (CUser::getID());

$arParams['COUNT_ON_PAGE'] = (int) ($arParams['COUNT_ON_PAGE'] > 0) ? ($arParams['COUNT_ON_PAGE']) : (20);

$arParams['USE_STATUS_COLOR'] = ($arParams['USE_STATUS_COLOR'] == 'Y');

$arParams['SET_TITLE'] = ($arParams['SET_TITLE'] == 'Y');

$arParams['PATH_TO_PAYMENT'] = (string) (!empty($arParams['PATH_TO_PAYMENT'])) ? ($arParams['PATH_TO_PAYMENT']) : ('/auto/order/make/');

$arParams['UNION_BY_ORDERS'] = ($arParams['UNION_BY_ORDERS'] == 'Y');


/*
 * Подключим jQuery
 */
CJSCore::Init(array('jquery'));

global $USER;

$arResult = array();

// Список статусов.
$arResult['STATUSES'] = LinemediaAutoOrder::getStatusesList();

if ($arParams['USE_STATUS_COLOR']) {
    $arResult['STATUS_COLORS'] = array();
    foreach ($arResult['STATUSES'] as $status) {
        $arResult['STATUS_COLORS'][$status['ID']] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_' . $status['ID'], '#ffffff');
    }
}


/*
 * Фильтрация заказов.
 */

$filter = new LinemediaAutoBasketFilter();

// Показываем заказы пользователя.
$filter->setUserId($arParams['USER_ID']);

if (!empty($_REQUEST['ORDER_ID'])) {
    $filter->setOrderId((int) $_REQUEST['ORDER_ID']);
}

if (!empty($_REQUEST['NAME'])) {
    $filter->setName((string) $_REQUEST['NAME']);
}

if (!empty($_REQUEST['ARTICLE'])) {
    $filter->setArticle((string) $_REQUEST['ARTICLE']);
}

if (!empty($_REQUEST['BRAND'])) {
    $filter->setBrandTitle((string) $_REQUEST['BRAND']);
}

if (!empty($_REQUEST['STATUS'])) {
    $filter->setStatus((array) $_REQUEST['STATUS']);
}

if (!empty($_REQUEST['PAYED'])) {
    $filter->setPayed((string) $_REQUEST['PAYED']);
}

if (!empty($_REQUEST['CANCELED'])) {
    $filter->setCanceled((string) $_REQUEST['CANCELED']);
}


$ids = $filter->filter();

if (empty($ids)) {
    $ids = false;
}

$arNavParams = array(
    'nPageSize' => $arParams['COUNT_ON_PAGE'],
    'bDescPageNumbering' => false,
    'bShowAll' => 'Y',
);

/*
 * Получаем список корзин пользователя.
 */
$dbbaskets = CSaleBasket::GetList(
    array('ORDER_ID' => 'DESC'),
    array('ID' => $ids, '!ORDER_ID' => false),
    false,
    $arNavParams,
    array()
);

$groups = array();

$baskets = array();

$arResult['TOTAL_PRICE'] = 0;

while ($basket = $dbbaskets->Fetch()) {

    $basket['PROPS'] = LinemediaAutoBasket::getProps($basket['ID']);
    
    $lmorder = new LinemediaAutoOrder($basket['ORDER_ID']);
    
    $order = CSaleOrder::getByID($basket['ORDER_ID']);
    $order['PROPS'] = $lmorder->getProps();
    $order_files = new LinemediaAutoOrderDocuments($basket['ORDER_ID']);
    //$order['FILES'] = $order_files->getFiles();

    $link_template = $this->getPath() . "/print.php?folder=#FILE_FOLDER#&file=#FILE_NAME#";

    $order['FILE_LINKS'] = $order_files->getFileLinks($link_template);
    
    $basket['ORDER'] = $order;
    
    $basket['STATUS_NAME']  = $arResult['STATUSES'][$basket['PROPS']['status']['VALUE']]['NAME'];
    $basket['STATUS_COLOR'] = $arResult['STATUS_COLORS'][$basket['PROPS']['status']['VALUE']];
    
    // Группировка корзин по заказам.
    $groups[$order['ID']] []= $basket['ID'];

    $baskets[$basket['ID']] = $basket;

	$arResult['TOTAL_PRICE']  += $basket['PRICE'] * $basket['QUANTITY'];
}

$arResult['BASKETS'] = $baskets;

$arResult['GROUPS'] = $groups;

$arResult['NAV_STRING'] = $dbbaskets->GetPageNavStringEx($navComponentObject, GetMessage('PO_ORDERS'));


if ($arParams['SET_TITLE']) {
    $APPLICATION->SetTitle(GetMessage('PO_ORDERS'));
}

foreach ($arResult['BASKETS'] as $id => &$basket) {
	$basket['STATUS_NAME'] = LinemediaAutoStatus::GetPublicTitleByStatusID($basket['PROPS']['status']['VALUE']);
}


foreach ($arResult['STATUSES'] as $key => &$status) {
    $arResult['STATUSES'][$key]['NAME'] = LinemediaAutoStatus::GetPublicTitleByStatusID($status['ID']);
}


if (CModule::IncludeModule('linemedia.autobranches')) {
    try {
        $director = new \LinemediaAutoBranchesDirector($USER->GetID());
        if(is_object($director)) {
            $arResult['obsoleteOrder'] = $director->getMostObsoleteOrder();
        }
    } catch(Exception $e) {

    }
}

$this->IncludeComponentTemplate();





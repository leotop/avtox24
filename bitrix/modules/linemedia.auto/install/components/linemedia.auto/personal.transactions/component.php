<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("sale")) {
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));

	return;
}

if (!CModule::IncludeModule("currency")) {
	ShowError(GetMessage("CURRENCY_MODULE_NOT_INSTALL"));

	return;
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

global $USER, $APPLICATION;

if (!$USER->GetID()) {
	ShowError(GetMessage('LM_AUTO_TRANSACTIONS_ERROR_AUTH'));

	return;
};

/**
 * Обработка входных параметров.
 */

$arParams['TITLE'] = $arParams['TITLE'] ? : GetMessage('LM_AUTO_TRANSACTIONS_SET_TITLE');
$arParams['ADD_SECTION_CHAIN'] = $arParams['ADD_SECTION_CHAIN'] == 'Y' ? 'Y' : "N";
$arParams['SET_TITLE_TRANSACTIONS'] = $arParams['SET_TITLE_TRANSACTIONS'] == 'Y' ? 'Y' : "N";
$arParams['INIT_JQUERY'] = $arParams['INIT_JQUERY'] == 'Y' ? 'Y' : "N";
$arParams['ORDERS_PATH'] = $arParams['ORDERS_PATH'] ? : '/auto/orders/';


/*
* Подключим на страницу jquery
*/
if ($arParams['INIT_JQUERY'] == 'Y') {
	CJSCore::Init(array('jquery'));
}


/**
 * Подключаем 'window' для выбора даты при сортировке
 */
CJSCore::Init(array('window'));

$arResult['transactions'] = array();

$user_id = $USER->GetID();
$current_date = $DB->FormatDate(date("d.m.Y"), 'DD.MM.YYYY', CSite::GetDateFormat("SHORT"));

/**
 *
 * Получаем транзакции текущего клиента
 */

$arFilter = array("USER_ID" => $user_id);

$arFilter['>=TRANSACT_DATE'] = !empty($_REQUEST['date_from']) > 0 ? trim(strip_tags($_REQUEST['date_from'] . ' 00:00:00')) : '01.01.1970';
$arFilter['<=TRANSACT_DATE'] = !empty($_REQUEST['date_to']) ? trim(strip_tags($_REQUEST['date_to']) . ' 23:59:59') : $current_date . ' 23:59:59';

if (isset($_REQUEST['trans_id']) && (int)$_REQUEST['trans_id'] > 0) {
	$arFilter['ID'] = (int)trim(strip_tags($_REQUEST['trans_id']));
}

if (isset($_REQUEST['order_id']) && (int)$_REQUEST['order_id'] > 0) {
	$arFilter['ORDER_ID'] = (int)trim(strip_tags($_REQUEST['order_id']));
}

$site_base_currency = CCurrency::GetBaseCurrency(); // COption::GetOptionString('sale', 'default_currency');//

$arResult['transactions'] = LinemediaAutoTransaction::getUserTransaction($user_id, $arFilter, 'DESC');

$oldest_reserve = LinemediaAutoTransaction::getOldestReserve($user_id);

$oldest_order_ts = false;
if(is_array($oldest_reserve)) {
    $oldest_order_ts = MakeTimeStamp($oldest_reserve['TRANSACT_DATE'], "YYYY-MM-DD HH:MI:SS");
} else {
    $oldest_order_ts = time();
}



foreach($arResult['transactions'] as $key => $trans) {

    $ts = MakeTimeStamp($trans['TRANSACT_DATE'], "YYYY-MM-DD HH:MI:SS");
    if($trans['TYPE'] == LinemediaAutoTransaction::TYPE_GOODS_IN_RESERVE && $oldest_order_ts && $ts < $oldest_order_ts) {
        $arResult['transactions'][$key]['CLOSED_BY_DEPOSIT'] = true;
    }

    if($trans['TYPE'] == LinemediaAutoTransaction::TYPE_GOODS_IN_RESERVE && intval($trans['REFUSED_BY']) > 0) {
        $refuse_id = intval($trans['REFUSED_BY']);
        $arResult['transactions'][$refuse_id]['REMARK'] = ' (' . GetMessage('RESERV') . ' #' . $trans['ID'] . ')';
    }
}


/**
 * Текущий счет клиента
 */
$res = CSaleUserAccount::GetList(array(), array('USER_ID' => $user_id));
while($fields = $res->Fetch()) {
    if(intval($fields['CURRENT_BUDGET']) != 0) {
        $arResult['cash'][$fields['CURRENCY']] = SaleFormatCurrency($fields["CURRENT_BUDGET"], $fields["CURRENCY"]);
    }

}

/*
$res_user_account = CSaleUserAccount::GetByUserID($user_id, $site_base_currency);
$arResult['cash'] = $res_user_account["CURRENT_BUDGET"] > 0
	? SaleFormatCurrency($res_user_account["CURRENT_BUDGET"], $res_user_account["CURRENCY"])
	: SaleFormatCurrency(0, $site_base_currency);
*/
/**
 * Получаем сумму "долга по заказам"
 */

/*
 * Фильтрация заказов.
 */

$filter = new LinemediaAutoBasketFilter();

$statusesAll = LinemediaAutoOrder::getStatusesList();

$excluded = array('F', 'P', 'C');
$status_to_action = (array) unserialize(COption::GetOptionString('linemedia.auto', 'TRANSACTION_STATUSES'));
foreach($status_to_action as $status => $action) {
    if($action == LinemediaAutoTransaction::ACTION_SHIPMENT ||
        $action == LinemediaAutoTransaction::ACTION_SUPPLIER ||
        $action == LinemediaAutoTransaction::ACTION_GOODS_RETURN) {
        $excluded[] = $status;
    }
}
$arResult['EXCLUDED_STATUSES'] = $excluded;

foreach ($statusesAll as $key => $status) {
    if(in_array($status['ID'], $excluded)) continue;
    $statuses[] = $status['ID'];
}

// Показываем заказы пользователя.
$filter->setUserId($user_id);
$filter->setStatus($statuses);
$filter->setPayed('N');
$filter->setCanceled('N');
$ids = $filter->filter();

if (empty($ids)) {
	$ids = false;
}

/*
 * Получаем список корзин пользователя.
 */

$dbbaskets = CSaleBasket::GetList(
	array('ORDER_ID' => 'DESC'),
	array('ID' => $ids, '!ORDER_ID' => false)
);

$groups = array();
$baskets = array();
$arResult['sum_to_pay'] = 0;

while ($basket = $dbbaskets->Fetch()) {
	$basket['PROPS'] = LinemediaAutoBasket::getProps($basket['ID']);

	$lmorder = new LinemediaAutoOrder($basket['ORDER_ID']);

	$order = CSaleOrder::getByID($basket['ORDER_ID']);
	$order['PROPS'] = $lmorder->getProps();

	$basket['ORDER'] = $order;

	$basket['STATUS_NAME'] = $arResult['STATUSES'][$basket['PROPS']['status']['VALUE']]['NAME'];
	$basket['STATUS_COLOR'] = $arResult['STATUS_COLORS'][$basket['PROPS']['status']['VALUE']];

	// Группировка корзин по заказам.
	$groups[$order['ID']] [] = $basket['ID'];
	$baskets[$basket['ID']] = $basket;
	$arResult['sum_to_pay'] += $basket['PRICE'] * $basket['QUANTITY'];

}
$arResult['BASKETS'] = $baskets;
$arResult['GROUPS'] = $groups;

foreach ($arResult['GROUPS'] as $group) {
	$basket = $arResult['BASKETS'][reset($group)];
	//$arResult['sum_to_pay'] += $basket['ORDER']['PRICE'];
}

$arResult['sum_to_pay_currency'] = $arResult['sum_to_pay'] > 0
	? SaleFormatCurrency(round($arResult['sum_to_pay']), $site_base_currency)
	: SaleFormatCurrency(0, $site_base_currency);


/*
 *  Хлебные крошки  + заголовок
 */
if ($arParams['SET_TITLE_TRANSACTIONS'] == 'Y') {
	$APPLICATION->SetTitle($arParams['TITLE']);
}

if ($arParams['ADD_SECTION_CHAIN'] == 'Y') {
	$APPLICATION->AddChainItem(GetMessage("LM_AUTO_TRANSACTIONS_SET_TITILE"), $APPLICATION->GetCurPage());
}


$arResult['date_from'] = isset($_REQUEST['date_from']) ? strip_tags(trim($_REQUEST['date_from'])) : '';
$arResult['date_to'] = isset($_REQUEST['date_to']) ? strip_tags(trim($_REQUEST['date_to'])) : $current_date;
$arResult['trans_id'] = isset($_REQUEST['trans_id']) && (int)$_REQUEST['trans_id'] > 0 ? strip_tags(trim($_REQUEST['trans_id'])) : '';
$arResult['order_id'] = isset($_REQUEST['order_id']) && (int)$_REQUEST['order_id'] > 0 ? strip_tags(trim($_REQUEST['order_id'])) : '';
$arResult['site_base_currency'] = $site_base_currency;
$arResult['STATUSES'] = LinemediaAutoOrder::getStatusesList();

$this->IncludeComponentTemplate();

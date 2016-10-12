<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$error = null;

if(!defined('LM_AUTO_ADMIN_PAGE_REQUIRE')) {
    $saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

    if ($saleModulePermissions == 'D') {
        $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
    }

    if (!CModule::IncludeModule("sale")) {
        ShowError('SALE MODULE NOT INSTALLED');
        return;
    }

    if (!CModule::IncludeModule("linemedia.auto")) {
        ShowError('LM_AUTO MODULE NOT INSTALLED');
        return;
    }

    if (!CModule::IncludeModule("iblock")) {
        ShowError('IBLOCK MODULE NOT INSTALLED');
        return;
    }

}

$autoBranches = CModule::IncludeModule("linemedia.autobranches") ? true : false;
$autoBranches = $autoBranches && COption::GetOptionString("linemedia.auto", 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST', 'N') == "Y" && COption::GetOptionString("linemedia.auto", 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == "Y";
IncludeModuleLangFile(__FILE__);



global $USER, $APPLICATION;
$sModuleId = "linemedia.auto";

/*********************************************************/
// �������� ���� �������� ������������
$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_ORDERS);
//$arTasksFilter = array("BINDING" => "linemedia_auto_price_import");        

$curUserGroup = $USER->GetUserGroupArray();  //������ ����� ������������

//println($curUserGroup) ;

$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter); //������������ ���� ������������
//echo $maxRole;

$resUserGroupsPerms = LinemediaAutoGroup::getUserPermissionsForModuleBinding($sModuleId, $curUserGroup, $arTasksFilter);
while($aUserGroupsPerms = $resUserGroupsPerms->Fetch())
{
    $arUserGroupsPerms[] = $aUserGroupsPerms;
}

foreach($arUserGroupsPerms as $perm)
{
    if($maxRole == $perm["LETTER"]) $groupId = $perm["GROUP_ID"];
}


/*********************************************************/

/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListCheckRights");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    $arFilterModule = array_merge($arFilterModule, ExecuteModuleEventEx($arEvent, array($USER->GetID())));
}


if (empty($USER)) {
    $USER = new CUser();
}


if(!defined('LM_AUTO_ADMIN_PAGE_REQUIRE'))
    $APPLICATION->SetTitle(GetMessage('LM_AUTO_ORDERS_LIST_TITLE'));

/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListSetTitle");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    $arFilterModule = array_merge($arFilterModule, ExecuteModuleEventEx($arEvent, array($USER->GetID())));
}

$arAccessibleSites = array();
$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
    array(),
    array('GROUP_ID' => $GLOBALS['USER']->GetUserGroupArray()),
    false,
    false,
    array('SITE_ID')
);

while ($arAccessibleSite = $dbAccessibleSites->Fetch()) {
    if (!in_array($arAccessibleSite['SITE_ID'], $arAccessibleSites)) {
        $arAccessibleSites []= $arAccessibleSite['SITE_ID'];
    }
}

$sTableID = "tbl_sale_orders_list";

$oSort = new CAdminSorting($sTableID, 'ID', 'DESC', 'sOrBy', 'sOrOrder');
$lAdmin = new CAdminList($sTableID, $oSort);

/*
 * ��������� ��������
 */
$arPageSettings = array(
    'LIST_PAGE' => 'linemedia.auto_sale_orders_list.php',
    'DETAIL_PAGE' => 'linemedia.auto_sale_order_detail.php',
    'EDIT_PAGE' => 'linemedia.auto_sale_order_edit.php',
    'PRINT_PAGE' => 'linemedia.auto_sale_order_print.php',
    'NEW_PAGE' => 'linemedia.auto_sale_order_new.php',
);

/*
 * C����� �������
 */
$events = GetModuleEvents('linemedia.auto', 'OnBeforeOrdersListPageBuild');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arPageSettings));
}

/*
 * Edit actions
 */
if ($lAdmin->EditAction()) {
    /*
    * �������� ������� ��� ������
    */
    $events = GetModuleEvents("linemedia.auto", "OnAdminOrderListEditActions");
    $arFilterModule = array();
    while ($arEvent = $events->Fetch()) {
        $arFilterModule = array_merge($arFilterModule, ExecuteModuleEventEx($arEvent, array($USER->GetID())));
    }
}

$arFilterFields = array(
    "filter_ids",
    "filter_id_from",
    "filter_id_to",
    "filter_date_from",
    "filter_date_to",
    "filter_date_update_from",
    "filter_date_update_to",
    "filter_currency",
    "filter_status",
    "filter_payed",
    "filter_pay_system",
    "filter_delivery",
    "filter_canceled",
    "filter_supplier",
    "filter_article",
    "filter_brand",
    "filter_person_type",
    "filter_user_id",
    "filter_user_login",
    "filter_user_email",
    "filter_universal",
    "filter_manager_id",
    "filter_order_id"
);

/*
    * �������� ������� ��� ������
    */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListFilterFields");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    $arFilterModule = array_merge($arFilterModule, ExecuteModuleEventEx($arEvent, array(&$arFilterFields)));
}


/*
 * �������� �������� ������.
 */
$arOrderProps = array();
$arOrderPropsCode = array();
$dbProps = CSaleOrderProps::GetList(
    array('PERSON_TYPE_ID' => 'ASC', 'SORT' => 'ASC'),
    array(),
    false,
    false,
    array('ID', 'NAME', 'PERSON_TYPE_NAME', 'PERSON_TYPE_ID', 'SORT', 'IS_FILTERED', 'TYPE', 'CODE')
);

while ($arProps = $dbProps->GetNext()) {
    if (strlen($arProps['CODE']) > 0) {
        if (empty($arOrderPropsCode[$arProps["CODE"]])) {
            $arOrderPropsCode[$arProps["CODE"]] = $arProps;
        }
    } else {
        $arOrderProps[intval($arProps["ID"])] = $arProps;
    }
}

foreach ($arOrderProps as $key => $value){
    if ($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTISELECT") {
        $arFilterFields[] = "filter_prop_".$key;
    }
}

foreach ($arOrderPropsCode as $key => $value){
    if ($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTISELECT") {
        $arFilterFields[] = "filter_prop_".$key;
    }
}

$lAdmin->InitFilter($arFilterFields);



/**
 * ����������.
 */


$arListSuppliers = LinemediaAutoSupplier::GetList();
$arSuppliers = array();
foreach ($arListSuppliers as $arSupplier) {
    $arSuppliers[$arSupplier['PROPS']['supplier_id']['VALUE']] = $arSupplier;
}
LinemediaAutoDebug::add('Suppliers', print_r($arSuppliers, true), LM_AUTO_DEBUG_WARNING);

/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminShowOrdersListFilterReady");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$arFilterFields, &$arFilterModule, &$arSuppliers));
    } catch (Exception $e) {
        throw $e;
    }
}


/*
 * ���� ���������� AJAX-�����, �� ��� ���������� �������
 * � ���� ���� $filter_... �������� ��������, ������ ����� ���������� �������.
 * ������� ������ ��� ����, ������.
 */
if (!empty($_REQUEST) && isset($_REQUEST['AJAX']) && check_bitrix_sessid()) {
    $arFiltersGET = array();

    if ($_REQUEST['del_filter'] != 'Y' && $_REQUEST['set_filter'] == 'Y') {

        foreach ($_REQUEST as $itemcode => $item) {
            if (strpos($itemcode, 'filter_') !== false) {
                $arFiltersGET[$itemcode] = $item;
            }
        }

        /*
         * �������� ������� ��� ������
         */
        $events = GetModuleEvents("linemedia.auto", "OnAdminShowOrdersListFilterVarsReady");
        while ($arEvent = $events->Fetch()) {
            try {
                ExecuteModuleEventEx($arEvent, array(&$arFiltersGET));
            } catch (Exception $e) {
                throw $e;
            }
        }

        extract($arFiltersGET);
    }
}

/*
 * ����������.
 */
$lmfilter = new LinemediaAutoBasketFilter();
$arPermFilter = LinemediaAutoGroup::makeOrderFilter($maxRole, $arFilterTmp); // ������ ��� ������ ��� �������, ������� ����� ������ ������� ������������
// ������� ����������� ����
//if($maxRole == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH || $maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH)
//{
//    $arFilialIds = LinemediaAutoGroup::getUserDealerId();
//
//}
//println($arFilialIds);     
// ����� �� ID ������� (������).
if (intval($filter_ids) > 0) {
    $lmfilter->setIds(array_filter(array_map('intval', explode(',', strval($filter_ids)))));
}

// ����� �� ID ������� (��).
if (intval($filter_id_from) > 0) {
    $lmfilter->setIdFrom($filter_id_from);
}

// ����� �� ID ������� (��).
if (intval($filter_id_to) > 0) {
    $lmfilter->setIdTo($filter_id_to);
}

// ����� �� ���� ���������� (��).
if (strlen($filter_date_from) > 0) {
    $lmfilter->setDateFrom($filter_date_from);
}

// ����� �� ���� ���������� (��).
if (strlen($filter_date_to) > 0) {
    $lmfilter->setDateTo($filter_date_to);
}

// ����� �� ���� ���������� (��).
if (strlen($filter_date_update_from) > 0) {
    $lmfilter->setDateUpdateFrom($filter_date_update_from);
}

// ����� �� ���� ���������� (��).
if (strlen($filter_date_update_to) > 0) {
    $lmfilter->setDateUpdateTo($filter_date_update_to);
}

// ������ �� ������.
if (!empty($filter_payed)) {
    $lmfilter->setPayed($filter_payed);
}

// ������ �� ������.
if (!empty($filter_canceled)) {
    $lmfilter->setCanceled($filter_canceled);
}

// ������ �� ��������.
if (isset($filter_status) && is_array($filter_status) && !empty($filter_status)) {
    $lmfilter->setStatus($filter_status);
}

// ������ �� ���� �����������.
if (!empty($filter_person_type)) {
    $lmfilter->setPersonType($filter_person_type);
}

// ������ �� ��������� ��������.
if (isset($filter_pay_system) && is_array($filter_pay_system) && !empty($filter_pay_system)) {
    $lmfilter->setPaySystem($filter_pay_system);
}

// ������ �� ���������.
if (isset($filter_delivery) && is_array($filter_delivery) && !empty($filter_delivery)) {
    $lmfilter->setDelivery($filter_delivery);
}

// ������ �� �����������. 


//����� ������ �������, ��� ���� ���� � ��������� ��� ��������, 
// ����������� ������ � ������ ������� � �����������, ��� ����� ���� ������� � ������� 

if(($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS && !is_array($arPermFilter["USER_ID"])))  // $maxRole ==  Q
{
    $lmfilter->setSupplier(array(-1));
}
else{
    if (isset($filter_supplier) && !empty($filter_supplier)) {
        //���� �������������� ������ �� �����������
        $suppliers_ids =array_filter(array_map('intval', (array)$filter_supplier));
        $set_suppliers = array();
        foreach ($suppliers_ids as $key => $id) {
            if(array_key_exists($id, $arListSuppliers)) // ���� ��������� ��������
                $set_suppliers[] = $arListSuppliers[$id]['PROPS']['supplier_id']['VALUE'];
        }
        $lmfilter->setSupplier($set_suppliers);

    } elseif(!$USER->isAdmin())
    {
        $set_suppliers = array();
        foreach($arListSuppliers as $id => $arValue) { // ������ ��������� ����������
            $set_suppliers[] = $arListSuppliers[$id]['PROPS']['supplier_id']['VALUE'];
        }
        if(count($set_suppliers) < 1) {
            $lmfilter->setSupplier(array('null'));
            $error = GetMessage("LM_AUTO_NO_ALLOWED_SUPPLIERS");
        } else {
            $lmfilter->setSupplier($set_suppliers);
        }
    }
}

//println($lmfilter);

// ������ �� ��������.
if (strlen($filter_article) > 0) {
    $lmfilter->setArticle($filter_article);
}

// ������ �� ������.
if (strlen($filter_brand) > 0) {
    $lmfilter->setBrandTitle($filter_brand);
}

// ������ �� ID ������������.
if (intval($filter_user_id) > 0) {
    $lmfilter->setUserId($filter_user_id);
}

// ������ �� ID ���������
if(intval($filter_manager_id) > 0 && $autoBranches) {
    $manager= new LinemediaAutoBranchesManager((int) $filter_manager_id);
    $managerUsers = $manager->getBranchesUserIDsList() ?: array(-1);
    $lmfilter->setUserId($managerUsers);
}

// ������ �� ������ ������������.
if (strlen($filter_user_login) > 0) {
    $lmfilter->setUserLogin($filter_user_login);
}

// ������ �� e-mail ������������.
if (strlen($filter_user_email) > 0) {
    $lmfilter->setUserEmail($filter_user_email);
}

// ������ �� e-mail ������������.
if (strlen($filter_universal) > 0) {
    $lmfilter->setUniversal($filter_universal);
}

// �������������� ������� �������.
if (!empty($arFilterModule)) {
    $lmfilter->setAdditionalFilter($arFilterModule);
}

//println($arFilterTmp);


/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListFilter");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array($USER->GetID(), &$lmfilter, &$maxRole));
}


// �������������� ������ �� ��������, ���� ��� ���������� ��������
// ����� ��������� ������ �� ��������?
//if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') != 'Y') {
    if($maxRole == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH || $maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH)
    {
        $arFilialIds = LinemediaAutoGroup::getUserDealerId();

        if(!empty($arFilialIds) && is_array($arFilialIds))
        {
            $res = CSaleOrderProps::GetList(array(), array('CODE' => 'BRANCH_ID'), false, false, array());
            // ���� �������� BRANCH_ID ����
            if($res->Fetch()) {
                $lmfilter->setOrderProperty("BRANCH_ID", $arFilialIds["UF_DEALER_ID"]["0"]);
            }
        }
    }
//}

//println($arPermFilter);

if(is_array($arPermFilter) && !empty($arPermFilter))
{
    if(strlen($filter_universal) > 0 && $maxRole != "Q")
    {
        $lmfilter->setUserId($arPermFilter["USER_ID"]);
        //$filter_universal = array_push($arPermFilter["USER_ID"], $filter_universal);

        // ������ �� user_id ������������
        //$lmfilter->setUniversalArr($filter_universal);
    }
    else
    {
        if((is_array($arPermFilter["USER_ID"]) && !empty($arPermFilter["USER_ID"])) || strlen($arPermFilter["USER_ID"]) > 0)
        {
            $lmfilter->setUserId($arPermFilter["USER_ID"]);

            //$filter_universal = $arPermFilter["USER_ID"];
            // ������ �� user_id ������������
            //$lmfilter->setUniversalArr($filter_universal);
        }
    }
}
else
{

    if (strlen($filter_universal) > 0) {
        // ������ �� user_id ������������
        $lmfilter->setUniversalArr($filter_universal);
    }
}

$available_statuses = array();
$dbAvStatuses = LinemediaAutoProductStatus::getAvailableStatuses("PERM_VIEW");
while($arAvStatuses = $dbAvStatuses -> Fetch())
{
    $available_statuses[$arAvStatuses['ID']] = $arAvStatuses;
    // println($arAvStatuses);
}

$arAllStatuses = LinemediaAutoProductStatus::getAllStatusesPermissions(1);
//println($arAllStatuses);
foreach($arAllStatuses as $key => $status)
{
    if($status["PERM_VIEW"] == "N") $arStatusNA[] = $key;
}

//println($arAllStatuses);


/*$dbStatusNA = LinemediaAutoProductStatus::getNotAvailableStatuses("PERM_VIEW");
 
 
 if($dbStatusNA)
 {
 while($statusNA = $dbStatusNA -> Fetch())
 {
     $arStatusNA[] = $statusNA["ID"];
     
 }
 }*/

$arStatusNA = array_unique($arStatusNA);
//println($arStatusNA);
//var_dump($dbStatusNA);
//foreach()

$lmfilter->setNStatus($arStatusNA);



//println($lmfilter); 

$aBasketItemsSFilter = $lmfilter->filter();

$arBasketFilter = array();



if ($lmfilter->isFiltered()) {
    if (!empty($aBasketItemsSFilter)) {
        if (isset($arBasketFilter['ID'])) {
            if (is_array($arBasketFilter['ID'])) {
                $arBasketFilter['ID'] = array_values(array_intersect($arBasketFilter['ID'], $aBasketItemsSFilter));
                if (count($arBasketFilter['ID']) == 0) {
                    $arBasketFilter['ID'] = false;
                }
            }
        } else {
            $arBasketFilter['ID'] = $aBasketItemsSFilter;
        }
    } else {
        $arBasketFilter['ID'] = false;
    }
}



$arFilterTmp = $arBasketFilter;
// var_dump($arFilterTmp);

if (!isset($arFilterTmp['ORDER_ID']) || $arFilterTmp['ORDER_ID'] === false) {
    $arFilterTmp['!ORDER_ID'] = 'NULL';
}



/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListCheckFilter");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arFilterTmp));
}

// TODO: ��������� ����� �� ��� ������.

/*
if ($saleModulePermissions == "W") {

} else {
    $arFilterTmp = array_merge(
        $arFilter,
        array(
            "STATUS_PERMS_GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray(),
            ">=STATUS_PERMS_PERM_VIEW" => "Y"
        )
    );
}
*/

/*
 * AJAX-�����
 */
if (!empty($_REQUEST) && isset($_REQUEST['AJAX']) && check_bitrix_sessid()) {
    $action = (string) $_REQUEST['act'];

    switch ($action) {
        case 'totals':
            $purchase   = 0;
            $sales      = 0;
            $profits    = 0;

            $lmbasket = new LinemediaAutoBasket();

            $db = CSaleBasket::GetList(array(), $arFilterTmp, false, false, array('ID', 'PRICE', 'QUANTITY', 'CURRENCY'));
            while ($basket = $db->Fetch()) {
                $props = $lmbasket->getProps($basket['ID']);

                if($props['canceled']['VALUE'] == 'Y') {
                    continue;
                }

                /*
                 * �������� ������� ��� ������ (������� ������������ � order/detail.php, sale_order_list.php)
                 */
                $events = GetModuleEvents("linemedia.auto", "OnBeforeAdminShowBasketDetail");
                while ($arEvent = $events->Fetch()) {
                    try {
                        ExecuteModuleEventEx($arEvent, array(&$basket, &$props));
                    } catch (Exception $e) {
                        throw $e;
                    }
                }

                /*
                 *  �������� ������ ����������
                 */
                if (CModule::IncludeModule('iblock')) {
                    $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS', 0);
                    if ($iblock_id > 0) {
                        $rs = CIBlockElement::GetList(array(), array('PROPERTY_supplier_id' => $props['supplier_id']['VALUE'], 'IBLOCK_ID' => $iblock_id), 0, 0, array('ID','IBLOCK_ID','PROPERTY_currency'));
                        $supplier_data = $rs->Fetch();
                    }

                }

                $purchase_price = $props['base_price']['VALUE'];
                if (CModule::IncludeModule('currency') && !empty($supplier_data)) {
                    // ���� �� ��������� ����, ��� ���� �� �������� ������� � �� ��������� � �����������, �� ������������
                    if(!$props['base_price']['from_retail_chain']) {
                        $purchase_price = CCurrencyRates::ConvertCurrency($props['base_price']['VALUE'], $supplier_data['PROPERTY_CURRENCY_VALUE'], $basket['CURRENCY']);
                    }
                }
                $purchase   += $purchase_price * $basket['QUANTITY'];
                $sales      += $basket['PRICE'] * $basket['QUANTITY'];
            }
            $profits = $sales - $purchase;

            $response = array(
                'purchase'  => CurrencyFormat($purchase, CCurrency::GetBaseCurrency()),
                'sales'     => CurrencyFormat($sales, CCurrency::GetBaseCurrency()),
                'profits'   => CurrencyFormat($profits, CCurrency::GetBaseCurrency())
            );

            header('Content-type: application/json');
            die(json_encode($response));
            break;

        default:
            die();
            break;
    }

    exit();
}





// ������ ��������� ������.
$paysystems = LinemediaAutoOrder::getPaysystemsList();

// ������ ��������.
$deliveries = LinemediaAutoOrder::getDeliveryList();

// ������ ��������.
$statuses = LinemediaAutoOrder::getStatusesList();

// ������ ����� ������������.
$persons = LinemediaAutoOrder::getPersonTypesList();

/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListStatusesPaySystemsDeliveriesPersons");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    $arFilterModule = array_merge($arFilterModule, ExecuteModuleEventEx($arEvent, array(&$paysystems, &$deliveries, &$statuses, &$persons)));
}



// ������� �������������� ����������
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListReady");
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$lmfilter, &$paysystems, &$deliveries, &$statuses, &$persons));
}


/*
 * ��������� ��������.
 */
$arID = array();

// ��������� ��� ���� �������.
if ($_REQUEST['action_target'] == 'selected') {
    $dbbaskets = CSaleBasket::GetList(array(), array('!ORDER_ID' => false), false, false, array('ID'));
    while ($basket = $dbbaskets->Fetch()) {
        $arID []= $basket['ID'];
    }
} else {
    $arID = $lAdmin->GroupAction();
}

$status = substr((string) $_REQUEST['action'], strlen('status_'), 1);

// Transaction.
/*if (in_array($status, array_keys((array) unserialize(COption::GetOptionString('linemedia.auto', 'TRANSACTION_STATUSES'))))) {
	$arID = (array) $arID;
    	
	$events = GetModuleEvents('linemedia.auto', 'OnApplyingStatus');
	while ($arEvent = $events->Fetch()) {
		$eventsOutcome = ExecuteModuleEventEx($arEvent, array(&$arID, $status));
        
        if (in_array(\LinemediaAutoTransactionFactory::TITLE, $eventsOutcome)) {     		
        	list($class, $isSuccess, $code) = $eventsOutcome; 		
        	if (!$isSuccess && !$USER->IsAdmin()) {
        		$error = GetMessage('LM_AUTO_ERROR_TRANSACTION');
        	}
        }
	}
}*/





// ���� ��������.
switch ($_REQUEST['action']) {
    case 'inner_pay':
        $obasket = new LinemediaAutoBasket();
        foreach ($arID as $ID) {
            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_PAYMENT");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;
            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else
            {
                $data = $obasket->getData($ID);
                $order_data = CSaleOrder::GetByID($data['ORDER_ID']);

                $acc = CSaleUserAccount::GetByUserID($order_data['USER_ID'], $data['CURRENCY']);

                if (doubleval($acc['CURRENT_BUDGET']) < doubleval($data['PRICE'])*intval($data['QUANTITY'])) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY_NOT_ENOUGH_MONEY'), $ID);
                    continue;
                }
                $basket_props = $obasket->getProps($ID);
                $notes = GetMessage('GROUP_INNER_PAY_ITEM_NOTES', array('#ORDER_ID#'=>$order_data['ID'],
                        '#ARTICLE#'=>$basket_props['article']['VALUE'],
                        '#BRAND#'=>$basket_props['brand_title']['VALUE']
                    )
                );
                /*
                  ��������� � ����������� ����� �����. ���, � �� ����� withdraw(), ������ ��� ���� ��������� �������������� ����������� � ����������
                */
                if (CSaleUserAccount::UpdateAccount($order_data['USER_ID'], -$data['PRICE']*intval($data['QUANTITY']), $data['CURRENCY'],
                    $notes, $order_data['ID'], $notes)) {
                    $obasket->payItem($ID, 'Y');
                    if ($ex = $APPLICATION->GetException()) {
                        $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY').': '.$ex->GetString(), $ID);
                    }
                } else {
                    if ($ex = $APPLICATION->GetException()) {
                        $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY').': '.$ex->GetString(), $ID);
                    } else {
                        $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY_UNKNOWN'), $ID);
                    }
                }
            }
        }//foreach
        break;

    case 'pay':
        $obasket = new LinemediaAutoBasket();
        //println($arID);
        foreach ($arID as $ID) {

            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_PAYMENT");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;
            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else $obasket->payItem($ID, 'Y');



            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY').': '.$ex->GetString(), $ID);
            }
        }
        break;

    case 'pay_no':
        $obasket = new LinemediaAutoBasket();
        foreach ($arID as $ID) {
            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_PAYMENT");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;
            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else $obasket->payItem($ID, 'N');

            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY').': '.$ex->GetString(), $ID);
            }
        }
        break;

    case 'cancel':
        $obasket = new LinemediaAutoBasket();
        foreach ($arID as $ID) {
            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_CANCEL");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;

            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else $obasket->cancelItem($ID, 'Y', $_REQUEST['comment']);

            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_CANCEL').': '.$ex->GetString(), $ID);
            }
        }
        break;

    case 'cancel_no':
        $obasket = new LinemediaAutoBasket();
        foreach ($arID as $ID) {
            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_CANCEL");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;
            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else $obasket->cancelItem($ID, 'N');
            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_CANCEL_NO').': '.$ex->GetString(), $ID);
            }
        }
        break;

    case 'delivery':
        $obasket = new LinemediaAutoBasket();
        foreach ($arID as $ID) {
            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_DELIVERY");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;
            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else $obasket->deliveryItem($ID, 'Y');

            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_DELIVERY').': '.$ex->GetString(), $ID);
            }
        }
        break;

    case 'delivery_no':
        $obasket = new LinemediaAutoBasket();
        foreach ($arID as $ID) {
            $m = LinemediaAutoProductStatus::permAction($ID, "PERM_DELIVERY");
            // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
            $lmCanEditOrder = true;
            if(is_array($arPermFilter) && !empty($arPermFilter)) {
                $basket_data = $obasket->getData($ID);
                $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
                $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
            }
            if(!$m || !$lmCanEditOrder) $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID);
            else $obasket->deliveryItem($ID, 'N');

            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_DELIVERY_NO').': '.$ex->GetString(), $ID);
            }
        }
        break;

    case 'delete':
        @set_time_limit(0);
        // �������� ������� ��� ������� ������ - ����������, ���� ������� ������� "��� ����"
        $lmCanEditOrder = true;
        if(is_array($arPermFilter) && !empty($arPermFilter)) {
            $basket_data = $obasket->getData($ID);
            $order = new LinemediaAutoOrder($basket_data['ORDER_ID']);
            $lmCanEditOrder = $order->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);
        }
        if (CSaleOrder::CanUserDeleteOrder($ID, $GLOBALS['USER']->GetUserGroupArray(), $GLOBALS['USER']->GetID()) && $lmCanEditOrder) {
            $DB->StartTransaction();
            if (!CSaleOrder::Delete($ID)) {
                $DB->Rollback();
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError($ex->GetString(), $ID);
                } else {
                    $lAdmin->AddGroupError(GetMessage('SALE_DELETE_ERROR'), $ID);
                }
            } else {
                $DB->Commit();
            }
        } else {
            $lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SO_NO_PERMS2DEL")), $ID);
        }
        break;
    case 'suppliers_request':
        foreach ($arID as $ID) {
            $basket = CSaleBasket::getByID(intval($ID));
            $ids[] = $basket['ORDER_ID'];
        }

        $ids = implode(',', $ids);

        echo '
				<script>
				window.top.location.href = "'."/bitrix/admin/linemedia.autosuppliers_out.php?order_ids=".$ids."&lang=".LANGUAGE_ID.'";
				</script>
				';

        break;
}


/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnApplyGroupAction");
while ($arEvent = $events->Fetch()) {
    try {
        $resultEvents = ExecuteModuleEventEx($arEvent, array(&$arID, substr((string) $_REQUEST['action'], strlen('status_'), 1), $_REQUEST['action']));
    } catch (Exception $e) {
        throw $e;
    }

    if ($resultEvents === false) {
        if ($ex = $APPLICATION->GetException()) {
            $lAdmin->AddGroupError($ex->GetString());
        }
        break;
    }
}

if (empty($error)) {

    // ����� ��������.
    if ($resultEvents !== false && strpos(strval($_REQUEST['action']), 'status') !== false) {
        $status_error = false;
        $status = substr((string) $_REQUEST['action'], strlen('status_'), 1);

        // println($_REQUEST);
        //if($_REQUEST["action"] == "pay") die('123');

        foreach ($arID as $id) {
            $basket_ids[$id] = $status;
        }

        //������� ��������� ��������
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketUpdateStatuses");
        while ($arEvent = $events->Fetch())
        {
            try
            {
                ExecuteModuleEventEx($arEvent, array($basket_ids));
            }
            catch (Exception $e)
            {
                throw $e;
            }
        }



        $obasket = new LinemediaAutoBasket();
        foreach($arID as $id) {
            $chStatusResultEvent = true;
            $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketStatusesChange");
            while ($arEvent = $events->Fetch()) {
                try {
                    $chStatusResultEvent = ExecuteModuleEventEx($arEvent, array($groupId, $_REQUEST["action"], $id));
                }
                catch (Exception $e)
                {
                    throw $e;
                }
            }

            // ���������, ����� �������� ������������ �������, ��� �� ���� ������� ������ �� ������ ���������.
            $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET'] = true;

            if ($chStatusResultEvent == true) {
                // foreach ($arID as $ID) {
                $obasket->statusItem($id, $status, $_REQUEST['comment']);
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ACTION_SET_STATUS').': '.$ex->GetString(), $id);
                    $status_error = true;
                }
                //}
            } else {
                $lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$id);
                $status_error = true;
            }

            if (!$status_error) {
                /*
                 * ������� �� �������� ��������.
                 */
                $events = GetModuleEvents("linemedia.auto", "OnAfterBasketStatusesChange");
                while ($arEvent = $events->Fetch()) {
                    ExecuteModuleEventEx($arEvent, array(&$arID, &$status));
                }
            }
        }

        unset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET']);
    }
} else {
    $lAdmin->AddGroupError($error);
}


/*
 * ��������� ���� ������.
 */
$arHeaders = array(
    array('id' => 'ORDER_ID',       'content' => GetMessage('ID'),          'sort' => 'ORDER_ID',       'default' => true),
    array('id' => 'PAYED',          'content' => GetMessage('PAYED'),       'sort' => '',               'default' => true),
    array('id' => 'CANCELED',       'content' => GetMessage('CANCELED'),    'sort' => '',               'default' => true),
    array('id' => 'PERSON_TYPE',    'content' => GetMessage('PERSON_TYPE'), 'sort' => '',               'default' => true),
    array('id' => 'QUANTITY',       'content' => GetMessage('QUANTITY'),    'sort' => 'QUANTITY',       'default' => true),
    array('id' => 'STATUS_ID',       'content' => GetMessage('STATUS_ID'),    'sort' => 'STATUS_ID',       'default' => false),
    array('id' => 'PRICE',          'content' => GetMessage('PRICE'),       'sort' => 'PRICE',          'default' => true),
    array('id' => 'AMOUNT',         'content' => GetMessage('AMOUNT'),       'sort' => '',              'default' => true),
    array('id' => 'USER',           'content' => GetMessage('USER'),        'sort' => 'USER_ID',        'default' => true),
    array('id' => 'STATUS',         'content' => GetMessage('STATUS'),      'sort' => '',               'default' => true),
    array('id' => 'ARTICLE',        'content' => GetMessage('ARTICLE'),     'sort' => '',               'default' => true),
    array('id' => 'ORIGINAL_ARTICLE','content' => GetMessage('ORIGINAL_ARTICLE'), 'sort' => '',               'default' => false),
    array('id' => 'BRAND',          'content' => GetMessage('BRAND'),       'sort' => '',               'default' => true),
    array('id' => 'NAME',           'content' => GetMessage('NAME'),        'sort' => 'NAME',           'default' => true),
    array('id' => 'SUPPLIER',       'content' => GetMessage('SUPPLIER'),    'sort' => '',               'default' => true),
    array('id' => 'DELIVERY',       'content' => GetMessage('DELIVERY'),    'sort' => '',               'default' => false),
    array('id' => 'PAYSYSTEM',      'content' => GetMessage('PAYSYSTEM'),   'sort' => '',               'default' => false),
    array('id' => 'BASEPRICE',      'content' => GetMessage('BASEPRICE'),   'sort' => '',               'default' => false),
    array('id' => 'BASEPRICE_AMOUNT','content' => GetMessage('BASEPRICE_AMOUNT'),   'sort' => '',       'default' => false),
    array('id' => 'DELIVERY_TIME',  'content' => GetMessage('DELIVERY_TIME'),'sort' => '',              'default' => false),
    array('id' => 'COMMENTS',       'content' => GetMessage('COMMENTS'),     'sort' => '',              'default' => false),
    array('id' => 'USER_DESCRIPTION',  'content' => GetMessage('USER_DESCRIPTION'),'sort' => '',        'default' => false),
    array('id' => 'USER_ACCOUNT',  'content' => GetMessage('USER_ACCOUNT'),'sort' => '', 'default' => false),
    array('id' => 'BRANCH',  'content' => GetMessage('BRANCH'),'sort' => '', 'default' => true),
);

if ($autoBranches) {
    $arHeaders[] =     array('id' => 'ORDER_MANAGER',  'content' => GetMessage('ORDER_MANAGER'), 'sort' => '', 'default' => false);
    $arHeaders[] = array('id' => 'RETAIL_CHAIN',  'content' => GetMessage('RETAIL_CHAIN'), 'sort' => '', 'default' => false);
}

if($USER->isAdmin()) {
    $arHeaders[] = array('id' => 'DEBUG',  'content' => 'DEBUG' ,'sort' => '', 'default' => false);
}


/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnBeforeAdminShowOrdersList");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$arHeaders));
    } catch (Exception $e) {
        throw $e;
    }
}

$lAdmin->AddHeaders($arHeaders);


//$t["BRANCH_ID"] = $arPermFilter["UF_DEALER_ID"];
//$arFilterTmp = array_merge($t, $arFilterTmp);

/*
echo "<pre>filter tmp";
print_r($arFilterTmp);
echo "</pre>";*/



$arGroupByTmp = false;
$arSelectFields = array();

//if($maxRole == "Q" && !is_array($arPermFilter["USER_ID"])) CAdminMessage::ShowMessage("ERROR");

// ������� ������ �������.
$dbBasketList = CSaleBasket::GetList(
    array($sOrBy => $sOrOrder),
    $arFilterTmp,
    $arGroupByTmp,
    array('nPageSize' => CAdminResult::GetNavSize($sTableID)),
    $arSelectFields
);


// ������������� ������ - ������� ������.
$dbBasketList = new CAdminResult($dbBasketList, $sTableID);
$dbBasketList->NavStart();


// ��������� ������ ���������.
$lAdmin->NavText($dbBasketList->GetNavPrint(GetMessage('ORDERS_LIST')));

// ���������� ������������ ����.
$show_panels_settings = true;
if(CModule::IncludeModule('linemedia.autobranches')) {
    $director_group = COption::GetOptionInt('linemedia.autobranches', "LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR");

    if(!$USER->IsAdmin() && in_array($director_group, $USER->GetUserGroupArray())) {
        $show_panels_settings = false;
    }
}

$lAdmin->AddAdminContextMenu(array(), false, $show_panels_settings);



CUtil::InitJSCore(array('jquery', 'window'));

$isManager = false;

if(CModule::IncludeModule('linemedia.autobranches')) {

    $managersID = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');

    if(isset($managersID) && in_array($managersID, $USER->GetUserGroupArray()) && !$USER->IsAdmin()) {
        $isManager = true;
    }
}
/*
 * ����� ������ ������� ����������� (����������� ������).
 */
while ($arBasketItem = $dbBasketList->GetNext()) {

    // ������������ ������ ��� ������.
    $row =& $lAdmin->AddRow($arBasketItem['ID'], $arBasketItem);

    // ����� (�����������).
    $arOrder = CSaleOrder::GetByID($arBasketItem['ORDER_ID']);

    $arBranchOrderProperty = CSaleOrderPropsValue::GetList(array(),array('ORDER_ID'=>$arBasketItem['ORDER_ID'], 'CODE'=>'BRANCH_ID'))->Fetch();
    $arBranchElement = CIBlockElement::GetByID($arBranchOrderProperty['VALUE'])->Fetch();
    $arBranchName = $arBranchElement['NAME'];

    // �������� �������.
    $arBasketProps = LinemediaAutoBasket::getProps($arBasketItem['ID']);

    /*
     * �������� ������� ��� ������
     */
    $events = GetModuleEvents("linemedia.auto", "OnBeforeAdminShowBasketRow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$row, &$arBasketItem, &$arBasketProps, &$arOrder));
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ID ������.
    $row->AddViewField('ORDER_ID', "<b><a href='/bitrix/admin/".$arPageSettings['DETAIL_PAGE']."?ID=".$arBasketItem['ORDER_ID'].GetFilterParams("filter_")."&lang=".LANGUAGE_ID."' title='".GetMessage("SALE_DETAIL_DESCR")."'>".GetMessage("SO_ORDER_ID_PREF").$arBasketItem['ORDER_ID']."</a></b><br />".GetMessage('SO_FROM').' '.$arOrder['DATE_INSERT']);

    // ����������.
    $row->AddViewField('PAYED', ($arBasketProps['payed']['VALUE'] == 'Y') ? (GetMessage('SALE_YES')) : (GetMessage('SALE_NO')));

    // ����������.
    $row->AddViewField('CANCELED', ($arBasketProps['canceled']['VALUE'] == 'Y') ? (GetMessage('SALE_YES')) : (GetMessage('SALE_NO')));

    // ����������.
    $row->AddField('PERSON_TYPE', $persons[$arOrder['PERSON_TYPE_ID']]['NAME']);

    // ����������.
    $row->AddField('QUANTITY', $arBasketItem['QUANTITY']);

    // ������ ������
    $color = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_COLOR_' . $arOrder['STATUS_ID'], '#ffffff');
    $row->AddViewField('STATUS_ID', '<span id="status-order-'.$arOrder['ID'].'-'.$arBasketItem['ID'].'" data-color="'.$color.'"></span>'.$statuses[$arOrder['STATUS_ID']]['NAME']. '<script>$("#status-order-'.$arOrder['ID'].'-'.$arBasketItem['ID'].'").parent("td").css("background-color", "'.$color.'")</script>');

    // ����.
    $row->AddField('PRICE', CurrencyFormat($arBasketItem['PRICE'], $arBasketItem['CURRENCY']));

    // �����.
    $row->AddField('AMOUNT', CurrencyFormat($arBasketItem['PRICE'] * $arBasketItem['QUANTITY'], $arBasketItem['CURRENCY']));

    // ������������.
    $arUser = CUser::getById($arOrder['USER_ID'])->Fetch();

    if($isManager)
        $user_link = '[<a href="/bitrix/admin/linemedia.auto_buyers_list_edit.php?lang=ru&ID='.$arUser['ID'].'">'.$arUser['ID'].'</a>] ';
    elseif($APPLICATION->GetGroupRight("main")>'Q')
        $user_link = '[<a href="/bitrix/admin/user_edit.php?lang=ru&ID='.$arUser['ID'].'">'.$arUser['ID'].'</a>] ';
    else
        $user_link = '';
    $row->AddField('USER', $user_link.$arUser['NAME'].' '.$arUser['LAST_NAME'].' ('.$arUser['EMAIL'].')');

    // ������ ������.
    $color = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_COLOR_' . $arBasketProps['status']['VALUE'], '#ffffff');
    $sid = $arBasketItem['ID'];

    if ($_REQUEST['mode'] == 'frame') {
        $jquery = '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>';
    }
    $row->AddViewField('STATUS', '<span id="status-item-'.$sid.'" data-color="'.$color.'"></span>'.$statuses[$arBasketProps['status']['VALUE']]['NAME']. $jquery .'<script>$("#status-item-'.$sid.'").parent("td")
    .css("background-color", "'.$color.'")</script>');

    // �������.
    $row->AddField('ARTICLE', $arBasketProps['article']['VALUE']);

    // ������������ �������.
    $row->AddField('ORIGINAL_ARTICLE', $arBasketProps['original_article']['VALUE']);

    // �����.
    $row->AddField('BRAND', $arBasketProps['brand_title']['VALUE']);


    // ����� ������� ������ �� ������ ����������
    if (CModule::IncludeModule('iblock')) {
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS', 0);
        if ($iblock_id > 0) {
            $rs = CIBlockElement::GetList(array(), array('PROPERTY_supplier_id' => $arBasketProps['supplier_id']['VALUE'], 'IBLOCK_ID' => $iblock_id), 0, 0, array('ID','IBLOCK_ID','PROPERTY_currency'));
            $supplier_data = $rs->Fetch();
        }

    }

    $purchase_price = $arBasketProps['base_price']['VALUE'];
    if (CModule::IncludeModule('currency')) {
        // ���� �� ��������� ����, ��� ���� �� �������� ������� � �� ��������� � �����������, �� ������������
        if(!$arBasketProps['base_price']['from_retail_chain']) {
            $purchase_price = CCurrencyRates::ConvertCurrency($arBasketProps['base_price']['VALUE'], $supplier_data['PROPERTY_CURRENCY_VALUE'], $arBasketItem['CURRENCY']);
        }
        // ���������� ����
        $row->AddField(
            'BASEPRICE',
            CurrencyFormat($purchase_price, $arBasketItem['CURRENCY'])
        );
        // ����� �������
        $row->AddField(
            'BASEPRICE',
            CurrencyFormat($purchase_price * $arBasketItem['QUANTITY'], $arBasketItem['CURRENCY'])
        );
    } else {
        // ���������� ����
        $row->AddField(
            'BASEPRICE',
            CurrencyFormat($purchase_price, $supplier_data['PROPERTY_CURRENCY_VALUE'])
        );
        // ����� �������
        $row->AddField(
            'BASEPRICE',
            CurrencyFormat($purchase_price * $arBasketItem['QUANTITY'], $supplier_data['PROPERTY_CURRENCY_VALUE'])
        );
    }

    // �������� ������.
    $row->AddField('NAME',  $arBasketItem['NAME']);

    // ���������.
    $arSupplier = array();
    foreach($arSuppliers as $key => $value) {
        if(ToLower($key) == ToLower($arBasketProps['supplier_id']['VALUE'])) {
            $arSupplier = $value;
            break;
        }
    }

    if(!$iblock_supplier_id){
        $iblock_supplier_id_arr = array_values($arSupplier['PROPS']);
        $iblock_supplier_id = $iblock_supplier_id_arr[0]['IBLOCK_ID'];
    }

//  ��� ������� � ����������� ������ � ������� ������� - ��������� ������ ������ � ���������� ������������!
//	$ib_supplier_rights = 'D';
//	if(CModule::IncludeModule('iblock'))
//	{
//		$ib_supplier_rights = CIBlock::GetPermission($iblock_supplier_id, $USER::GetId());
//	}
//
//	if($ib_supplier_rights > 'D')

    $row->AddViewField('SUPPLIER', '[<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?type=linemedia_auto&IBLOCK_ID='.$iblock_supplier_id.'&ID='.$arSupplier['ID'].'">'.$arSupplier['ID'].'</a>] '.$arSuppliers[$arSupplier['PROPS']['supplier_id']['VALUE']]['NAME']); // $arBasketProps['supplier_title']['VALUE']

    // ��������.
    $row->AddField('DELIVERY', $deliveries[$arOrder['DELIVERY_ID']]['NAME']);

    if ($autoBranches) {
        // �������� �����.
        $oUser = new LinemediaAutoBranchesUser($arUser['ID']);
        $arUser = CUser::getById($oUser->getManagerID(false))->Fetch();

        $view = $arUser['ID']
            ? '[<a href="/bitrix/admin/user_edit.php?lang=ru&ID='.$arUser['ID'].'">'.$arUser['ID'].'</a>] '.$arUser['NAME'].' '.$arUser['LAST_NAME'].' ('.$arUser['EMAIL'].')'
            : '';

        $row->AddField('ORDER_MANAGER', $view);
    }

    // ���� ������������.
    $arSaleAccount = CSaleUserAccount::GetByUserID($arOrder['USER_ID'], CCurrency::getBaseCurrency());
    $row->AddViewField('USER_ACCOUNT', '<span style="color: '.(($arSaleAccount['CURRENT_BUDGET'] > 0) ? ('#009900;') : ('#cc0000')).'">'.CurrencyFormat($arSaleAccount['CURRENT_BUDGET'], $arSaleAccount['CURRENCY']).'</span>');

    // ��������� �������.
    $row->AddField('PAYSYSTEM', $paysystems[$arOrder['PAY_SYSTEM_ID']]['NAME']);

    // ���� ��������.
    $delivery_time = (int) $arBasketProps['delivery_time']['VALUE'];
    if ($delivery_time > 0) {
        if ($delivery_time >= 24) {
            $days = round($delivery_time / 24);
            $delivery_time = '&asymp; ' . $days . ' ' . GetMessage('LM_AUTO_MAIN_DAYS');
        } else {
            $delivery_time .= ' ' . GetMessage('LM_AUTO_MAIN_HOURS');
        }
    } else {
        $delivery_time = '';
    }
    $row->AddField('DELIVERY_TIME', $delivery_time);


    // �����������.
    $row->AddField('COMMENTS',  $arOrder['COMMENTS']);

    // ����������� ���������� � ������.
    $row->AddField('USER_DESCRIPTION',  $arOrder['USER_DESCRIPTION']);

    // ������
    $row->AddField('BRANCH', $arBranchName);

    /*
     * ���������� ��������.
     */
    $arActions = array();

    $ob = new LinemediaAutoOrder($arOrder['ID']);
    $lmCanViewOrder = $ob->getUserPermissionsForOrder($maxRole, 'read', $arPermFilter);
    $lmCanEditOrder = $ob->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);

    // var_dump($lmCanEditOrder);

    // �������������� ��������.
    $arActions []= array(
        'ICON' => 'view',
        'DEFAULT' => true,
        'TEXT' => GetMessage('ACTION_DETAIL'),
        'ACTION' => $lAdmin->ActionRedirect($arPageSettings['DETAIL_PAGE'] . "?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
    );

    // ������ ������.
    $arActions []= array(
        'ICON' => 'print',
        'DEFAULT' => false,
        'TEXT' => GetMessage('ACTION_PRINT'),
        'ACTION' => $lAdmin->ActionRedirect($arPageSettings['PRINT_PAGE'] . "?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
    );

    // ��������� ������.
    //echo $maxRole;
    // var_dump($lmCanEditOrder);
    if($lmCanEditOrder)
    {
        //if (CSaleOrder::CanUserUpdateOrder($arOrder['ID'], $GLOBALS['USER']->GetUserGroupArray())) {
        $arActions []= array(
            'ICON' => 'edit',
            'DEFAULT' => false,
            'TEXT' => GetMessage('ACTION_EDIT'),
            'ACTION' => $lAdmin->ActionRedirect($arPageSettings['EDIT_PAGE'] . "?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
        );
        //}
    }

    /*
     * �������� ������� ��� ������
     */
    $events = GetModuleEvents("linemedia.auto", "OnAfterAdminShowBasketRow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$row, &$arBasketItem, &$arBasketProps, &$arOrder, &$arActions, &$lAdmin, &$arSuppliers));
        } catch (Exception $e) {
            throw $e;
        }
    }


    $row->AddActions($arActions);



    // ������
    if($USER->isAdmin()) {

        $DEBUG = 'basket_id: ' . $arBasketItem['ID'] . '<br>';
        if($arBasketProps['to_branch_id']) {
            $DEBUG .= '<nobr>to_branch_id: ' . $arBasketProps['to_branch_id']['VALUE'] . '</nobr><br>';
        }
        if($arBasketProps['from_branch_id']) {
            $DEBUG .= '<nobr>from_branch_id: ' . $arBasketProps['from_branch_id']['VALUE'] . '</nobr><br>';
        }
        if($arBasketProps['parent_basket_id']) {
            $DEBUG .= '<nobr>parent_basket_id: ' . $arBasketProps['parent_basket_id']['VALUE'] . '</nobr><br>';
        }
        if($arBasketProps['child_basket_id']) {
            $DEBUG .= '<nobr>child_basket_id: ' . $arBasketProps['child_basket_id']['VALUE'] . '</nobr><br>';
        }
        if($arBasketProps['retail_chain']) {
            $DEBUG .= '<nobr><span style="color:brown" title="' . print_r(json_decode($arBasketProps['retail_chain']['VALUE'], 1), 1) . '">retail_chain</span></nobr><br>';
        }


        $row->AddField('DEBUG', $DEBUG);
    }
}



// ������ ������
$lAdmin->AddFooter(
    array(
        array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $dbBasketList->SelectedRowsCount()),
        array("title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0", "counter" => true),
    )
);

// ��������� ��������.
/*  $arGroupActions = array(
      'pay' => GetMessage("GROUP_ACTION_PAY"),
      'inner_pay' => GetMessage("GROUP_ACTION_INNER_PAY"),
      'pay_no' => GetMessage("GROUP_ACTION_PAY_NO"),
      'cancel' => GetMessage("GROUP_ACTION_CANCEL"),
      'cancel_no' => GetMessage("GROUP_ACTION_CANCEL_NO"),
      'delivery' => GetMessage("GROUP_ACTION_DELIVERY"),
      'delivery_no' => GetMessage("GROUP_ACTION_DELIVERY_NO"),
  );*/

if(!$USER->IsAdmin())
{
    foreach($arAllStatuses as $st)
    {
        if($st["PERM_PAYMENT"] == "Y")
        {

            echo $sk;
            //println($st);
            $pay = array('pay' => GetMessage("GROUP_ACTION_PAY"));
            $payNo = array('pay_no' => GetMessage("GROUP_ACTION_PAY_NO"));
            $innerPay = array('inner_pay' => GetMessage("GROUP_ACTION_INNER_PAY"));
        }
        if($st["PERM_CANCEL"] == "Y")
        {
            $canc = array('cancel' => GetMessage("GROUP_ACTION_CANCEL"));
            $cancNo = array('cancel_no' => GetMessage("GROUP_ACTION_CANCEL_NO"));
        }
        if($st["PERM_DELIVERY"] == "Y")
        {
            $deliv = array('delivery' => GetMessage("GROUP_ACTION_DELIVERY"));
            $delivNo = array('delivery_no' => GetMessage("GROUP_ACTION_DELIVERY_NO"));
        }
    }



    $arCommonActions = array($pay, $payNo, $innerPay, $canc, $cancNo, $deliv, $delivNo);

    foreach($arCommonActions  as $t)
    {
        foreach($t as $k => $v)
        {
            $arGroupActions[$k] = $v;
        }
    }
}
else
{
    $arGroupActions = array(
        'pay' => GetMessage("GROUP_ACTION_PAY"),
        'inner_pay' => GetMessage("GROUP_ACTION_INNER_PAY"),
        'pay_no' => GetMessage("GROUP_ACTION_PAY_NO"),
        'cancel' => GetMessage("GROUP_ACTION_CANCEL"),
        'cancel_no' => GetMessage("GROUP_ACTION_CANCEL_NO"),
        'delivery' => GetMessage("GROUP_ACTION_DELIVERY"),
        'delivery_no' => GetMessage("GROUP_ACTION_DELIVERY_NO"),
    );
}

/*
 * �������� ������� ��� ������
 */
$events = GetModuleEvents("linemedia.auto", "OnCreateGroupActionsList");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$arGroupActions));
    } catch (Exception $e) {
        throw $e;
    }
}

// ��������� �������� �� ���������.
$dbStatusList = LinemediaAutoProductStatus::getAvailableStatuses("PERM_STATUS","PERM_STATUS");
while($arStatus = $dbStatusList->Fetch()){
    $arGroupActions['status_'.$arStatus['ID']] = GetMessage("GROUP_ACTION_SET_STATUS").' "'.$arStatus['NAME'].'"';
}

// ������ �����������
$arGroupActions['suppliers_request'] = GetMessage("LM_AUTO_SUPPLIERS_REQUEST");




$events = GetModuleEvents("linemedia.auto", "AfterCreateGroupActionsList");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$arGroupActions));
    } catch (Exception $e) {
        throw $e;
    }
}


$lAdmin->AddGroupActionTable($arGroupActions, array("disable_action_target" => !$USER->IsAdmin()));


$lAdmin->CheckListMode();



require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if (!empty($error)) {
    CAdminMessage::ShowMessage($error);
}
?>

<?
if($maxRole == "D") {
    CAdminMessage::ShowMessage(GetMessage("SOD_NO_PERMS2VIEW"));
} else {
    ?>

    <form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
    <?
    $arFilterFieldsTmp = array(
        GetMessage('SALE_F_BUYER'),
        GetMessage('LM_AUTO_ORDERS_LIST_BASKET_IDS'),
        GetMessage("SALE_F_DATE"),
        GetMessage("SALE_F_DATE_UPDATE"),
        GetMessage("SALE_F_ID"),
        GetMessage("SALE_F_STATUS"),
        GetMessage("SALE_F_PAYED"),
        GetMessage("SALE_F_CANCELED"),
        GetMessage("SALE_F_PERSON_TYPE"),
        GetMessage("SALE_F_PAY_SYSTEM"),
        GetMessage("SALE_F_DELIVERY"),
        GetMessage("SALE_F_SUPPLIER"),
        GetMessage("SALE_F_ARTICLE"),
        GetMessage("SALE_F_BRAND"),
        GetMessage("SALE_F_USER_ID"),
        GetMessage("SALE_F_USER_LOGIN"),
        GetMessage("SALE_F_USER_EMAIL"),
    );

    if ($autoBranches) {
        $arFilterFieldsTmp[] = GetMessage("SALE_F_MANAGER_ID");
    }


    /*
     * �������� ������� ��� ������: ���������� �������������� ��������.
     */
    $events = GetModuleEvents("linemedia.auto", "OnAdminOrderListBuildFilters");
    $arFiltersHTML = array();
    while ($arEvent = $events->Fetch()) {
        ExecuteModuleEventEx($arEvent, array($USER->GetID(), &$arFilterFieldsTmp, &$arFiltersHTML));
    }


    $oFilter = new CAdminFilter(
        $sTableID."_filter",
        $arFilterFieldsTmp
    );


    $oFilter->Begin();
    ?>
    <?if(in_array('filter_universal', $arFilterFields)) {?>
        <tr>
            <td><?=GetMessage('SALE_F_BUYER')?>:</td>
            <td>
                <input type="text" name="filter_universal" value="<?echo htmlspecialcharsbx($filter_universal)?>" size="40">
            </td>
        </tr>

    <?} if(in_array('filter_ids', $arFilterFields)) {?>
        <tr>
            <td><?=GetMessage('LM_AUTO_ORDERS_LIST_BASKET_IDS')?>:</td>
            <td>
                <input type="text" name="filter_ids" value="<?echo htmlspecialcharsbx($filter_ids)?>" size="40">
            </td>
        </tr>
    <?} if(in_array('filter_date_from', $arFilterFields)) {?>
        <tr>
            <td><b><?= GetMessage("SALE_F_DATE") ?>:</b></td>
            <td>
                <?= CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y") ?>
            </td>
        </tr>
    <?} if(in_array('filter_date_update_from', $arFilterFields)){?>
        <tr>
            <td><?= GetMessage("SALE_F_DATE_UPDATE") ?>:</td>
            <td>
                <?= CalendarPeriod("filter_date_update_from", $filter_date_update_from, "filter_date_update_to", $filter_date_update_to, "find_form", "Y") ?>
            </td>
        </tr>
    <?}if(in_array('filter_id_from', $arFilterFields)){?>
        <tr>
            <td><?= GetMessage("SALE_F_ID") ?>:</td>
            <td>
                <script language="JavaScript">
                    function filter_id_from_change()
                    {
                        if (document.find_form.filter_id_to.value.length <= 0) {
                            document.find_form.filter_id_to.value = document.find_form.filter_id_from.value;
                        }
                    }
                </script>
                <?= GetMessage("SALE_F_FROM") ?>
                <input type="text" name="filter_id_from" OnChange="filter_id_from_change()" value="<?= (intval($filter_id_from) > 0) ? intval($filter_id_from) : ""?>" size="10" />

                <?= GetMessage("SALE_F_TO") ?>
                <input type="text" name="filter_id_to" value="<?= (intval($filter_id_to) > 0) ? intval($filter_id_to) : ""?>" size="10" />
            </td>
        </tr>
    <?} if(in_array('filter_status', $arFilterFields)){?>
        <tr>
            <td valign="top"><?= GetMessage("SALE_F_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
            <td valign="top">
                <select name="filter_status[]" multiple size="3">
                    <?
                    $dbStatusList = LinemediaAutoProductStatus::getAvailableStatuses("PERM_VIEW","PERM_VIEW");
                    while ($arStatusList = $dbStatusList->Fetch()) {
                        ?><option value="<?= htmlspecialchars($arStatusList["ID"]) ?>"<?if (is_array($filter_status) && in_array($arStatusList["ID"], $filter_status)) echo " selected"?>>[<?= htmlspecialchars($arStatusList["ID"]) ?>] <?= htmlspecialcharsEx($arStatusList["NAME"]) ?></option><?
                    }
                    ?>
                </select>
            </td>
        </tr>
    <?} if(in_array('filter_payed', $arFilterFields)){?>
        <tr>
            <td><?= GetMessage("SALE_F_PAYED") ?>:</td>
            <td>
                <select name="filter_payed">
                    <option value=""><?= GetMessage("SALE_F_ALL")?></option>
                    <option value="Y"<? if ($filter_payed == "Y") echo " selected" ?>><?= GetMessage("SALE_YES")?></option>
                    <option value="N"<? if ($filter_payed == "N") echo " selected" ?>><?= GetMessage("SALE_NO")?></option>
                </select>
            </td>
        </tr>
    <?} if(in_array('filter_canceled', $arFilterFields)){?>
        <tr>
            <td><?= GetMessage("SALE_F_CANCELED") ?>:</td>
            <td>
                <select name="filter_canceled">
                    <option value=""><?= GetMessage("SALE_F_ALL")?></option>
                    <option value="Y"<? if ($filter_canceled == "Y") echo " selected" ?>><?= GetMessage("SALE_YES")?></option>
                    <option value="N"<? if ($filter_canceled == "N") echo " selected" ?>><?= GetMessage("SALE_NO")?></option>
                </select>
            </td>
        </tr>
    <?} if(in_array('filter_person_type', $arFilterFields)){?>
        <tr>
            <td>
                <?= GetMessage("SALE_F_PERSON_TYPE") ?>:<br />
                <img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
            </td>
            <td>
                <select name="filter_person_type[]" multiple size="3">
                    <option value=""><?= GetMessage("SALE_F_ALL") ?></option>
                    <? $l = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array()); ?>
                    <? while ($personType = $l->Fetch()) { ?>
                        <option value="<?= htmlspecialchars($personType["ID"])?>"<? if (is_array($filter_person_type) && in_array($personType["ID"], $filter_person_type)) echo " selected"?>>
                            [<?= htmlspecialchars($personType["ID"]) ?>] <?= htmlspecialchars($personType["NAME"])?> <?= "(".htmlspecialchars($personType["LID"]).")";?>
                        </option>
                    <? } ?>
                </select>
            </td>
        </tr>
    <?} if(in_array('filter_pay_system', $arFilterFields)){?>
        <tr>
            <td>
                <?= GetMessage("SALE_F_PAY_SYSTEM") ?>:<br />
                <img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
            </td>
            <td>
                <select name="filter_pay_system[]" multiple size="3">
                    <option value=""><?= GetMessage("SALE_F_ALL") ?></option>
                    <? $l = CSalePaySystem::GetList(Array("SORT"=>"ASC", "NAME"=>"ASC"), Array()); ?>
                    <? while ($paySystem = $l->Fetch()) { ?>
                        <option value="<?= htmlspecialchars($paySystem["ID"])?>"<? if (is_array($filter_pay_system) && in_array($paySystem["ID"], $filter_pay_system)) echo " selected" ?>>
                            [<?= htmlspecialchars($paySystem["ID"]) ?>] <?= htmlspecialchars($paySystem["NAME"])?> <?= "(".htmlspecialchars($paySystem["LID"]).")";?>
                        </option>
                    <? } ?>
                </select>
            </td>
        </tr>
    <?} if(in_array('filter_delivery', $arFilterFields)){?>
        <tr>
            <td>
                <?= GetMessage("SALE_F_DELIVERY") ?>:<br />
                <img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
            </td>
            <td>
                <select name="filter_delivery[]" multiple size="3">
                    <option value=""><?= GetMessage("SALE_F_ALL") ?></option>
                    <?
                    $rsDeliveryServicesList = CSaleDeliveryHandler::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
                    $arDeliveryServicesList = array();
                    while ($arDeliveryService = $rsDeliveryServicesList->Fetch()) {
                        if (!is_array($arDeliveryService) || !is_array($arDeliveryService["PROFILES"])) {
                            continue;
                        }
                        foreach ($arDeliveryService["PROFILES"] as $profile_id => $arDeliveryProfile) {
                            $delivery_id = $arDeliveryService["SID"].":".$profile_id;
                            ?><option value="<?echo htmlspecialchars($delivery_id)?>"<?if (is_array($filter_delivery) && in_array($delivery_id, $filter_delivery)) echo " selected"?>>[<?echo htmlspecialchars($delivery_id)?>] <?echo htmlspecialchars($arDeliveryService["NAME"].": ".$arDeliveryProfile["TITLE"])?></option><?
                        }
                    }

                    $dbDelivery = CSaleDelivery::GetList(
                        array("SORT" => "ASC", "NAME" => "ASC"),
                        array("ACTIVE" => "Y",)
                    );

                    while ($arDelivery = $dbDelivery->GetNext()) { ?>
                        <option value="<?= $arDelivery["ID"]?>"<? if (is_array($filter_delivery) && in_array($delivery_id, $filter_delivery)) echo " selected"?>>
                            [<?= $arDelivery["ID"]?>] <?= $arDelivery["NAME"]?>
                        </option>
                    <? } ?>
                </select>
            </td>
        </tr>
    <?} if(in_array('filter_supplier', $arFilterFields)){
        ?>
        <tr>
            <td><?= GetMessage("SALE_F_SUPPLIER") ?>:</td>
            <td>
                <select name="filter_supplier[]" size="5" multiple="multiple">
                    <?
                    $list = LinemediaAutoSupplier::getList();
                    foreach ($list as $key=>$item) {?>
                        <option value="<?=$item['ID']?>" <?if (in_array($item['ID'], $_REQUEST['filter_supplier'])) {?>selected="selected"<?}?>>[<?=$item['PROPS']['supplier_id']['VALUE']?>] <?=$item['NAME']?></option>
                    <?}
                    ?>
                </select>
            </td>
        </tr>
    <?}?>
    <tr>
        <td><?= GetMessage("SALE_F_ARTICLE") ?>:</td>
        <td>
            <input type="text" name="filter_article" value="<?= htmlspecialcharsEx($filter_article)?>" size="40" />
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("SALE_F_BRAND") ?>:</td>
        <td>
            <input type="text" name="filter_brand" value="<?= htmlspecialcharsEx($filter_brand)?>" size="40" />
        </td>
    </tr>

    <?if(in_array('filter_user_id', $arFilterFields)){?>
        <tr>
            <td><?= GetMessage("SALE_F_USER_ID") ?>:</td>
            <td>
                <?= FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
            </td>
        </tr>
    <?}?>
    <?if(in_array('filter_user_login', $arFilterFields)){?>
        <tr>
            <td><?= GetMessage("SALE_F_USER_LOGIN") ?>:</td>
            <td>
                <input type="text" name="filter_user_login" value="<?= htmlspecialcharsEx($filter_user_login)?>" size="40" />
            </td>
        </tr>
    <?}?>
    <?if(in_array('filter_user_email', $arFilterFields)) {?>
        <tr>
            <td><?= GetMessage("SALE_F_USER_EMAIL") ?>:</td>
            <td>
                <input type="text" name="filter_user_email" value="<?= htmlspecialcharsEx($filter_user_email)?>" size="40" />
            </td>
        </tr>
    <?}?>
    <?if ($autoBranches AND in_array('filter_manager_id', $arFilterFields)) {?>
        <tr>
            <td><?= GetMessage("SALE_F_MANAGER_ID") ?>:</td>
            <td>
                <?= FindUserID("filter_manager_id", $filter_manager_id, "", "find_form");?>
            </td>
        </tr>
    <?}?>
    <?
    if($maxRole !== LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH && $maxRole !== LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH)
    {}
    else
    {
        $arFiltersHTML = array();
        $filialId = LinemediaAutoGroup::getUserDealerId();
        $res = CIBlockElement::GetByID((int)$filialId["UF_DEALER_ID"]["0"]);

        if($ar_res = $res->GetNext())
            $filialName = $ar_res['NAME'];

        $html .= '<option value="'.$filialId.'">'.$filialName.'</option>';
        $arFiltersHTML []= '
        <tr>
            <td>'.GetMessage('LM_AUTO_BRANCHES_BRANCH').':</td>
            <td>
                <select name="filter_branch[]" multiple size="3">
                    '.$html.'
                </select>
            </td>
        </tr>
    ';
    }

    foreach ($arFiltersHTML as $arFilterHTML) { ?>
        <?= $arFilterHTML ?>
    <? }
    ?>

    <?
    $oFilter->Buttons(
        array(
            "table_id" => $sTableID,
            "url" => $APPLICATION->GetCurPage(),
            "form" => "find_form"
        )
    );
    $oFilter->End();
    ?>

    </form>
    <!-- ������ -->
    <? $lAdmin->DisplayList();

    $show_summary = true;
    $show_income = true;

    if(CModule::IncludeModule('linemedia.autobranches')) {
        $director_group = COption::GetOptionInt('linemedia.autobranches', "LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR");

        if(!$USER->IsAdmin() && in_array($director_group, $USER->GetUserGroupArray())) {
            $show_income = false;
        }
    }
    /*
     * �������� ������� ��� ������: ���������� �������������� ��������.
     */
    $events = GetModuleEvents("linemedia.auto", "OnAdminOrderListAfterDisplayList");
    $arFiltersHTML = array();
    while ($arEvent = $events->Fetch()) {
        ExecuteModuleEventEx($arEvent, array(&$show_summary));
    }


    if($show_summary) {?>
        <div class="order-itog order-itog-list">
            <table width="100%">
                <tbody>
                <?if($show_income) {?>
                    <tr>
                        <td class="title">
                            <?= GetMessage('LM_AUTO_ORDER_LIST_TOTAL_PURCHASE') ?>:
                        </td>
                        <td class="title">
                            <div id="lm-order-list-total-purchase" style="white-space: nowrap;">
                                <?= CurrencyFormat(0, CCurrency::GetBaseCurrency()) ?>
                            </div>
                        </td>
                    </tr>
                <?}?>
                <tr>
                    <td class="title">
                        <?= GetMessage('LM_AUTO_ORDER_LIST_TOTAL_SALES') ?>:
                    </td>
                    <td class="title">
                        <div id="lm-order-list-total-sales" style="white-space: nowrap;">
                            <?= CurrencyFormat(0, CCurrency::GetBaseCurrency()) ?>
                        </div>
                    </td>
                </tr>
                <?if($show_income) {?>
                    <tr class="itog">
                        <td class="ileft">
                            <div><?= GetMessage('LM_AUTO_ORDER_LIST_TOTAL_PROFITS') ?>:</div>
                        </td>
                        <td class="iright">
                            <div id="lm-order-list-total-profits" style="white-space: nowrap;">
                                <?= CurrencyFormat(0, CCurrency::GetBaseCurrency()) ?>
                            </div>
                        </td>
                    </tr>
                <?}?>
                </tbody>
            </table>
        </div>
    <?}?>

<?}?>

<script type="text/javascript">
    $(document).ready(function() {

        function getTotalSumms(fields)
        {
            var data = {'act': 'totals'};
            var filter = $('#<?= $sTableID ?>_filterset_filter').closest('form').serialize();

            for (var i in fields) {
                data[i] = fields[i];
            }

            $.ajax({
                type: "GET",
                url: '<?= $APPLICATION->GetCurPage() ?>?AJAX=Y&<?= bitrix_sessid_get() ?>&' + filter,
                cache: false,
                data: data,
                dataType: "json",
                success: function(response) {
                    $('#lm-order-list-total-purchase').html(response.purchase);
                    $('#lm-order-list-total-sales').html(response.sales);
                    $('#lm-order-list-total-profits').html(response.profits);
                }
            });
        }

        getTotalSumms();

        $('#<?= $sTableID ?>_filterset_filter').click(function() {
            getTotalSumms({'set_filter': 'Y'});
        });

        $('#<?= $sTableID ?>_filterdel_filter').click(function() {
            getTotalSumms({'del_filter': 'Y'});
        });


        // �������� ���� �� ��������� �������.
        /*
         $('label.adm-designed-checkbox-label').live('mouseup', function() {
         var element  = $(this).siblings('input[type="checkbox"]');
         var elements = [];
         var all      = 'N'; // ������� ��� ��������, �� ������ � ���� ��������.
         var use      = true; // ������������ ������� ������� ��� ���������.

         // �������: ������� �� ���� �������� ������� �� ��������� ��������.
         // ���� ��������� ������� ��� �� ������, �� �� ������ ��������� - ������� ��� ��������.

         if ($(element).attr('id') == 'action_target' || $(element).attr('id') == 'tbl_sale_orders_list_check_all') {
         use = false;
         }

         if ($(element).attr('id') == 'action_target' && !$(element).is(':checked')) {
         // ����� ���� ��������� ���������.
         all = 'Y';
         } else {
         if ($(element).attr('id') == 'tbl_sale_orders_list_check_all') {
         if (!$(element).is(':checked')) {
         // ����� ���� ��������� �� ��������.
         $('input[type="checkbox"][name="ID[]"]').each(function() {
         elements.push($(this).val());
         });
         }
         } else {
         // ����� ���������� ���������.
         $('input[type="checkbox"][name="ID[]"]:checked').each(function() {
         elements.push($(this).val());
         });

         if (use) {
         if ($(element).is(':checked')) {
         delete elements[elements.indexOf($(element).val())];
         } else {
         elements.push($(element).val());
         }
         }
         }
         }
         });
         */

        $(document).on('mouseover', "#tbl_sale_orders_list_footer", function() {

            var btn = $("#tbl_sale_orders_list_footer input.adm-btn");
            var select = $("#tbl_sale_orders_list_footer select.adm-select");

            this.onclick = null;
            btn.unbind('click');
            btn.removeAttr('onclick');

            // new click handler
            btn.click(function(e) {
                var action = select.val();
                //���� ����� ����������� - �������� ��� �������
                <? if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CANCEL_REQUIRE_COMMENT', 'N') == 'Y') { ?>
                if(action == 'cancel') { // ������ ������
                    bxCommentDialog('<?= GetMessage("LM_AUTO_ORDER_LIST_CANCEL_REASON_TITLE") ?>');
                    e.stopImmediatePropagation();
                    return false;
                }
                <? } ?>
                <?
                $LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT'));
                if(is_array($LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT) && count($LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT) > 0) {
                    foreach($LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT as $strStatus) { ?>
                if(action == 'status_<?=$strStatus?>') {
                    bxCommentDialog("<?= GetMessage("LM_AUTO_ORDER_LIST_STATUS_REASON_TITLE"); ?>");
                    e.stopImmediatePropagation();
                    return false;
                }
                <? }
            }
            ?>
            });
        });

    });

    function clickOkButton() {
        if($("#dlg_msg_box").val().length > 0) {
            $("#form_tbl_sale_orders_list").append('<input type="hidden" name="comment" value="' + $("#dlg_msg_box").val().replace('"', '') + '" />');
            $("#form_tbl_sale_orders_list").submit();
            BX.WindowManager.Get().Close();
        }
    }

    var bxCommentDialogObj = null;
    function bxCommentDialog(title) {

        if(bxCommentDialogObj == null) {
            bxCommentDialogObj = new BX.CDialog({
                title: title,
                content: '<textarea id="dlg_msg_box" name="comment" style="width:365px;height:75px;"></textarea>',
                icon: 'head-block',
                resizable: true,
                draggable: true,
                height: '130',
                width: '400',
                buttons: [
                    '<input type="button" onclick="return clickOkButton();" value="OK" />',
                    BX.CDialog.btnCancel
                ]
            });
        } else {
            bxCommentDialogObj.SetTitle(title);
        }

        bxCommentDialogObj.Show();
    }

</script>

<? //}
require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>

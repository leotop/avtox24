<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
IncludeModuleLangFile(__FILE__);

global $USER, $APPLICATION;

$userPermission = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_FINANCE));

if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

if (!CModule::IncludeModule("iblock")) {
    ShowError('IBLOCK MODULE NOT INSTALLED');
    return;
}

// TODO: в модуль филиалов
if (CModule::IncludeModule("linemedia.autobranches")) {
	$usersID = array();
	if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_FINANCE_BRANCH) == 0) {
	     
	    $branches = array();
	    foreach(\LinemediaAutoBranchesBranch::getList() as $branch) {
	        $branches[$branch->getDirectorID()] = $branch->getBranchID();
	    }
	
	    if (array_key_exists($USER->GetID(), $branches)) {
	        $branch = new LinemediaAutoBranchesBranch($branches[$USER->GetID()]);
	        $usersID = $branch->getBranchesUserIDsList();
	    }
	} elseif (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_FINANCE_CLIENTS) == 0) {
		global $USER;
		$manager = new LinemediaAutoBranchesManager($USER->GetID());
		$usersID = $manager->getBranchesUserIDsList();
	}
} else {
	$usersID = array();
}

$arTransactTypes = array(
	"ORDER_PAY" => GetMessage("STA_TPAY"), // Оплата заказа
	"CC_CHARGE_OFF" => GetMessage("STA_TFROM_CARD"), // Внесение денег с пластиковой карты
	"OUT_CHARGE_OFF" => GetMessage("STA_TMONEY"), // Внесение денег
	"ORDER_UNPAY" => GetMessage("STA_TCANCEL_ORDER"), // Отмена оплаченности заказа
	"ORDER_CANCEL_PART" => GetMessage("STA_TCANCEL_SEMIORDER"), // Отмена частично оплаченного заказа
	"MANUAL" => GetMessage("STA_THAND"), // Ручное изменение счета
	"DEL_ACCOUNT" => GetMessage("STA_TDEL"), // Удаление счета
	"AFFILIATE" => GetMessage("STA_AF_VIP"), // Афилиатские выплаты

    'CREDIT_LIMIT' => GetMessage('STA_CREDIT_LIMIT'), // Пополнение кредитного лимита
    'GOODS_IN_RESERVE' => GetMessage('STA_GOODS_IN_RESERVE'), // Товар в резерве
    'DEPOSIT_REFUSED_IN_SHIPMENT' => GetMessage('STA_DEPOSIT_REFUSED_IN_SHIPMENT'), // Отказано в отгрузке
    'DEPOSIT_REFUSED_BY_SUPPLIER' => GetMessage('STA_DEPOSIT_REFUSED_BY_SUPPLIER'), // Отказано поставщиком
    'DEPOSIT_RETURN_GOODS' => GetMessage('STA_DEPOSIT_RETURN_GOODS'), // Возврат товара
    'DEPOSIT_FUNDS' => GetMessage('STA_DEPOSIT_FUNDS'), // Внесение средств
);

$sTableID = "tbl_sale_transact";

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_user_id",
	"filter_login",
	"filter_user",
	"filter_transact_date_from",
	"filter_transact_date_to",
	"filter_order_id",
	"filter_currency",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_user_id) > 0) $arFilter["USER_ID"] = IntVal($filter_user_id);
if (strlen($filter_login) > 0) $arFilter["USER_LOGIN"] = $filter_login;
if (strlen($filter_user) > 0) $arFilter["%USER_USER"] = $filter_user;
if (strlen($filter_currency) > 0) $arFilter["CURRENCY"] = $filter_currency;
if (strlen($filter_transact_date_from)>0) $arFilter[">=TRANSACT_DATE"] = Trim($filter_transact_date_from);
if (IntVal($filter_order_id) > 0) $arFilter["ORDER_ID"] = IntVal($filter_order_id);
if (strlen($filter_transact_date_to)>0)
{
	if ($arDate = ParseDateTime($filter_transact_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (StrLen($filter_transact_date_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_transact_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=TRANSACT_DATE"] = $filter_transact_date_to;
	}
	else
	{
		$filter_transact_date_to = "";
	}
}

if (isset($_REQUEST['filter_user_id']) && in_array($_REQUEST['filter_user_id'], $usersID)) {
    $arFilter['USER_ID'] = $_REQUEST['filter_user_id'];
} elseif (isset($_REQUEST['filter_user_id']) && !in_array($_REQUEST['filter_user_id'], $usersID)) {
    $arFilter['USER_ID'] = -1;
} else {
    $arFilter['USER_ID'] = $usersID;
}


$nPageSize = CAdminResult::GetNavSize($sTableID);
$dbTransactList = CSaleUserTransact::GetList(
		array($by => $order),
		$arFilter,
		false,
		array("nPageSize"=>$nPageSize),
		array("*")
	);


$dbTransactList = new CAdminResult($dbTransactList, $sTableID);
$dbTransactList->NavStart();
$lAdmin->NavText($dbTransactList->GetNavPrint(GetMessage("STA_NAV")));

$headers = array(
    array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
    array("id"=>"TRANSACT_DATE","content"=>GetMessage("STA_TRANS_DATE1"), "sort"=>"transact_date", "default"=>true),
    array("id"=>"USER_ID", "content"=>GetMessage('STA_USER1'),"sort"=>"user_id", "default"=>true),
    array("id"=>"AMOUNT", "content"=>GetMessage("STA_SUM"), "sort"=>"amount", "default"=>true),
    array("id"=>"ORDER_ID", "content"=>GetMessage("STA_ORDER"), "sort"=>"order_id", "default"=>true),
    array("id"=>"TYPE", "content"=>GetMessage("STA_TYPE"), "sort"=>"description", "default"=>true),

);

if($USER->IsAdmin()) {
    $headers[] = array("id"=>"DESCR", "content"=>GetMessage("STA_DESCR"), "sort"=>"", "default"=>true);
    $headers[] = array("id"=>"USER_IP", "content"=>GetMessage("STA_USER_IP"), "sort"=>"", "default"=>true);
}

$lAdmin->AddHeaders($headers);



$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$LOCAL_TRANS_USER_CACHE = array();


if (in_array("DESCR", $arVisibleColumns))
{
	$dbTransactList1 = CSaleUserTransact::GetList(
			array($by => $order),
			$arFilter,
			false,
			array("nPageSize"=>$nPageSize),
			array("ID", "EMPLOYEE_ID")
		);

	$arTrUsers = array();
	while ($arTransact = $dbTransactList1->Fetch())
	{
		$tmpTrans[] = $arTransact;
		if(IntVal($arTransact["EMPLOYEE_ID"]) > 0 && !in_array($arTransact["EMPLOYEE_ID"], $arTrUsers))
			$arTrUsers[] = $arTransact["EMPLOYEE_ID"];
	}

	if(!empty($arTrUsers))
	{
		$dbUser = CUser::GetList($by = "ID", $or = "ASC", array("ID" => implode(' || ', array_keys($arTrUsers))), array("FIELDS" => array("ID", "LOGIN", "NAME", "LAST_NAME")));
		while($arUser = $dbUser->Fetch())
		{
			$LOCAL_TRANS_USER_CACHE[$arUser["ID"]] = htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
		}
	}
}

while ($arTransact = $dbTransactList->NavNext(true, "f_"))
{
    
	$row =& $lAdmin->AddRow($f_ID, $arTransact);

	$row->AddField("ID", $f_ID);
	$row->AddField("TRANSACT_DATE", $f_TRANSACT_DATE);

    $lm_transaction = LinemediaAutoTransaction::getByBxTransaction($f_ID);

	$fieldValue  = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=".LANG."\" title=\"".GetMessage("STA_USER_INFO")."\">".$f_USER_ID."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_NAME"].((strlen($arTransact["USER_NAME"])<=0 || strlen($arTransact["USER_LAST_NAME"])<=0) ? "" : " ").$arTransact["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsEx($arTransact["USER_EMAIL"])."\" title=\"".GetMessage("STA_MAILTO")."\">".htmlspecialcharsEx($arTransact["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("AMOUNT", (($arTransact["DEBIT"] == "Y") ? "+" : "-").SaleFormatCurrency($arTransact["AMOUNT"], $arTransact["CURRENCY"])."<br><small>".(($arTransact["DEBIT"] == "Y") ? GetMessage("STA_TO_ACCOUNT") : GetMessage("STA_FROM_ACCOUNT"))."</small>");

	if (IntVal($arTransact["ORDER_ID"]) > 0)
		$fieldValue = "<a href=\"/bitrix/admin/sale_order_detail.php?ID=".$arTransact["ORDER_ID"]."&lang=".LANG."\" title=\"".GetMessage("STA_ORDER_VIEW")."\">".$arTransact["ORDER_ID"]."</a>";
	else
		$fieldValue = "&nbsp;";
	$row->AddField("ORDER_ID", $fieldValue);

	if (array_key_exists($lm_transaction["TYPE"], $arTransactTypes))
		$fieldValue = htmlspecialcharsEx($arTransactTypes[$lm_transaction["TYPE"]]);
	else
		$fieldValue = htmlspecialcharsEx($lm_transaction["TYPE"]);
	$row->AddField("TYPE", $fieldValue);

    if($USER->IsAdmin()) {
        $fieldValue = "&nbsp;";
        if (in_array("DESCR", $arVisibleColumns))
        {
            $fieldValue .= "<small>";
            if (IntVal($arTransact["EMPLOYEE_ID"]) > 0)
            {
                if (isset($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]])
                    && !empty($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]]))
                {
                    $fieldValue .= "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arTransact["EMPLOYEE_ID"]."&lang=".LANG."\" title=\"".GetMessage("STA_USER_INFO")."\">".$arTransact["EMPLOYEE_ID"]."</a>] ";
                    $fieldValue .= $LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]];
                    $fieldValue .= "<br />";
                }
            }
            $notes = htmlspecialcharsEx($arTransact["NOTES"]);
            if(preg_match('/USER_ID=(\d+)\s/', $notes, $matches)) {
                $user_id = $matches[1];
                $notes = str_replace('USER_ID=' . $user_id, "<br />USER_ID=[<a href=\"/bitrix/admin/user_edit.php?ID=".$user_id."&lang=".LANG."\">".$user_id."</a>]<br />", $notes);
            }
            $fieldValue .= $notes;
            $fieldValue .= "</small>";
        }
        $row->AddField("DESCR", $fieldValue);

        $row->AddField("USER_IP", $lm_transaction["USER_IP"]);
    }


	/*
	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SAA_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_account_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""));
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SAA_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('SAA_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
	*/
}





$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbTransactList->SelectedRowsCount()
		)
	)
);


if ($saleModulePermissions >= "U")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("STAN_ADD_NEW"),
			"LINK" => "/bitrix/admin/linemedia.auto_transaction_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE" => GetMessage("STAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();



require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("STA_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STA_USER_ID"),
		GetMessage("STA_USER_LOGIN"),
		GetMessage("STA_CURRENCY"),
		GetMessage("STA_TRANS_DATE"),
		GetMessage("STA_ORDER_ID"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("STA_USER")?>:</td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsEx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_USER_ID")?>:</td>
		<td>
			<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_USER_LOGIN")?>:</td>
		<td>
			<input type="text" name="filter_login" size="50" value="<?= htmlspecialcharsEx($filter_login) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_CURRENCY")?>:</td>
		<td>
			<?= CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("STA_ALL"), True, "", ""); ?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STA_TRANS_DATE")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_transact_date_from", $filter_transact_date_from, "filter_transact_date_to", $filter_transact_date_to, "bfilter", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_ORDER_ID")?>:</td>
		<td>
			<input type="text" name="filter_order_id" size="5" value="<?= htmlspecialcharsEx($filter_order_id) ?>">
		</td>
	</tr>
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
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION, $USER;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$userPermission = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_FINANCE));

if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}



require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
IncludeModuleLangFile(__FILE__);

$errorMessage = "";
$bVarsFromForm = false;

ClearVars();

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
    
    $usersID = array();
    
    // TODO: в модуль филиалов
    if (CModule::IncludeModule("linemedia.autobranches")) {
	    if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_FINANCE_BRANCH) == 0) { // && $ID != 0
	         
	        $branches = array();
	        foreach(\LinemediaAutoBranchesBranch::getList() as $branch) {
	            $branches[$branch->getDirectorID()] = $branch->getBranchId();
	        }
	    
	        if (array_key_exists($USER->GetID(), $branches)) {
	            $branch = new LinemediaAutoBranchesBranch($branches[$USER->GetID()]);
	            $usersID = $branch->getBranchesUserIDsList();
	        }
	    }
	    
	    if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_FINANCE_CLIENTS) == 0) { //  && $ID != 0
	        $usersID = \LinemediaAutoBranchesManager::getUsersOfManager($USER->GetID());
	    }
    }
    
	$USER_ID = IntVal($USER_ID);
	
	if (!in_array($USER_ID, $usersID)) {
	    if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_FINANCE_BRANCH) == 0) {
	        $errorMessage .= GetMessage('BRANCH_ERROR');
	    } elseif (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_FINANCE_CLIENTS) == 0) {
	        $errorMessage .= GetMessage('MANAGER_ERROR');
	    }
	}
	
	
	if ($USER_ID <= 0)
		$errorMessage .= GetMessage("STE_EMPTY_USER").".<br>";

	$TRANSACT_DATE = Trim($TRANSACT_DATE);
	if (strlen($TRANSACT_DATE) <= 0)
		$errorMessage .= GetMessage("STE_EMPTY_DATE").".<br>";

	$AMOUNT = str_replace(",", ".", $AMOUNT);
	$AMOUNT = DoubleVal($AMOUNT);
	if ($AMOUNT <= 0)
		$errorMessage .= GetMessage("STE_EMPTY_SUM").".<br>";

	$CURRENCY = Trim($CURRENCY);
	if (strlen($CURRENCY) <= 0)
		$errorMessage .= GetMessage("STE_EMPTY_CURRENCY").".<br>";

    switch($DEBIT) {
        case 'Y': {
            $DEBIT = 'Y';
        } break;
        case 'LIMIT_Y': {
            $DEBIT = 'Y';
        } break;
        case 'LIMIT_N': {
            $DEBIT = 'N';
        } break;
        default : {
            $DEBIT = 'N';
        } break;
    }

	if (strlen($errorMessage) <= 0)
	{
		if (!CSaleUserAccount::UpdateAccount($USER_ID, (($DEBIT == "Y") ? $AMOUNT : -$AMOUNT), $CURRENCY, "MANUAL", IntVal($ORDER_ID), $NOTES))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().".<br>";
			else
				$errorMessage .= GetMessage("STE_ERROR_SAVE_ACCOUNT").".<br>";
		}
	}

	if (strlen($errorMessage) <= 0)
		LocalRedirect("/bitrix/admin/linemedia.auto_transaction.php?lang=".LANG.GetFilterParams("filter_", false));
	else
		$bVarsFromForm = true;
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_user_transact", "", "str_");


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("STE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("STEN_2FLIST"),
				"TITLE" => GetMessage("STEN_2FLIST_TITLE"),
				"LINK" => "/bitrix/admin/linemedia.auto_transaction.php?lang=".LANG.GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if(strlen($errorMessage)>0)
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("STE_ERROR"), "HTML"=>true));?>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("STEN_TAB_TRANSACT"), "ICON" => "sale", "TITLE" => GetMessage("STEN_TAB_TRANSACT_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("STE_USER")?></td>
		<td width="60%"><?
			$user_name = "";
			if ($ID > 0)
				$user_name = "[<a title=\"".GetMessage("STE_USER_PROFILE")."\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$str_USER_ID."\">".$str_USER_ID."</a>] (".$str_USER_LOGIN.") ".$str_USER_NAME." ".$str_USER_LAST_NAME;

			echo \FindUserID(
        			    "USER_ID",
        			    $str_USER_ID,
			            $user_name="", 
			            $form_name = "form1", 
			            $tag_size = "3", 
			            $tag_maxlength="", 
			            $button_value = "...", 
			            $tag_class="typeinput", 
			            $button_class="tablebodybutton",
			            $search_page="/bitrix/admin/linemedia.auto_user_search.php"
                   );
			?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("STE_DATE")?>:</td>
		<td><?
			if (strlen($str_TRANSACT_DATE) <= 0)
				$str_TRANSACT_DATE = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)));
			echo CalendarDate("TRANSACT_DATE", $str_TRANSACT_DATE, "form1", "20", "");
			?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("STE_SUM")?></td>
		<td>
			<input type="text" name="AMOUNT" size="10" maxlength="20" value="<?= roundEx($str_AMOUNT, SALE_VALUE_PRECISION) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STE_CURRENCY")?></td>
		<td>
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, '', false, "", "")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STE_TYPE")?></td>
		<td>
			<select name="DEBIT">
				<option value="Y"<?if ($str_DEBIT == "Y") echo " selected";?>><?echo GetMessage("STE_DEBET")?></option>
				<option value="N"<?if ($str_DEBIT == "N") echo " selected";?>><?echo GetMessage("STE_KREDIT")?></option>
                <option value="LIMIT_Y"<?if ($str_DEBIT == "LIMIT_Y") echo " selected";?>><?echo GetMessage("STE_LIMIT_PLUS")?></option>
                <option value="LIMIT_N"<?if ($str_DEBIT == "LIMIT_N") echo " selected";?>><?echo GetMessage("STE_LIMIT_MINUS")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("STE_ORDER_ID")?></td>
		<td valign="top">
			<input type="text" name="ORDER_ID" size="5" maxlength="20" value="<?= $str_ORDER_ID ?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("STE_NOTES")?></td>
		<td valign="top">
			<textarea name="NOTES" rows="3" cols="40"><?= $str_NOTES ?></textarea>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons();
?>

<input type="submit" name="save" value="<?= GetMessage("STE_SAVE") ?>" <?if ($saleModulePermissions < "U") echo "disabled" ?>>&nbsp;<input type="button" value="<?echo GetMessage("STE_CANCEL1")?>" OnClick="window.location='/bitrix/admin/sale_transact_admin.php?lang=<?= LANG ?><?= GetFilterParams("filter_") ?>'">

<?
$tabControl->End();
?>

</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>


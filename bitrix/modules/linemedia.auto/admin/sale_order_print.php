<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$crmMode = (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE && isset($_REQUEST["CRM_MANAGER_USER_ID"]));

CModule::IncludeModule('linemedia.auto');

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($saleModulePermissions == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

ClearVars();

global $USER;

if (empty($USER)) {
    $USER = new CUser();
}

/*
 * Настройки страницы
 */
$arPageSettings = array(
    'LIST_PAGE' => 'linemedia.auto_sale_orders_list.php',
    'DETAIL_PAGE' => 'linemedia.auto_sale_order_detail.php',
    'EDIT_PAGE' => 'linemedia.auto_sale_order_edit.php',
    'PRINT_PAGE' => 'linemedia.auto_sale_order_print.php',
    'NEW_PAGE' => 'linemedia.auto_sale_order_new.php',
);

/*
 * Определяемся с валютой
 */
if (!CModule::IncludeModule("currency")) {
    ShowError(GetMessage("CURRENCY_MODULE_NOT_BE_LOADED"));
    return;
}
$base_currency = CCurrency::GetBaseCurrency();
$user_currency = $USER->GetParam('CURRENCY');
if(strlen($user_currency) != 3) {
    $user_currency = $base_currency;
}

/*
 * Cоздаём событие
 */
$events = GetModuleEvents('linemedia.auto', 'OnBeforeOrdersPrintPageBuild');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arPageSettings));
}

$ID = intval($ID);

if ($ID <= 0) {
    LocalRedirect("sale_order.php?lang=".LANG.GetFilterParams("filter_", false));
}

/*
 * Создаём событие "Отображение формы создания заказа"
 */
$access = true;
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderAccess");
while ($arEvent = $events->Fetch()) {
    $access = (bool) ExecuteModuleEventEx($arEvent, array(CUser::GetID(), $ID));
    if (!$access) {
        break;
    }
}

$documents = new LinemediaAutoOrderDocuments($ID);
$order_files = $documents->getFiles();
$is_basket_files = false;
foreach($order_files['basket'] as $id => $files) {
    if(is_array($files) && count($files) > 0) {
        $is_basket_files = true;
        break;
    }
}

/*******************************************************/
$sModuleId = "linemedia.auto";
$arTasksFilter = array("BINDING" => "linemedia_auto_order");
$curUserGroup = $USER->GetUserGroupArray();   //массив групп пользователя

$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
//echo "maxrole=".$maxRole;         

$resUserGroupsPerms = LinemediaAutoGroup::getUserPermissionsForModuleBinding($sModuleId, $curUserGroup, $arTasksFilter);

        while($aUserGroupsPerms = $resUserGroupsPerms->Fetch())
        {
            $arUserGroupsPerms[] = $aUserGroupsPerms;
        }
      
   foreach($arUserGroupsPerms as $perm)
    {
        if($maxRole == $perm["LETTER"]) $groupId = $perm["GROUP_ID"];
    }   

$arPermFilter = LinemediaAutoGroup::makeOrderFilter($maxRole, array()); 

$ob = new LinemediaAutoOrder($_REQUEST["ID"]);
//доступы на заказ
$lmCanViewOrder = $ob->getUserPermissionsForOrder($maxRole, 'read', $arPermFilter);
$lmCanEditOrder = $ob->getUserPermissionsForOrder($maxRole, 'write', $arPermFilter);

$arClientsIds = LinemediaAutoGroup::getUserClients();
 
if ($maxRole == 'D') {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
} 
/********************************************************/

/*if (!$USER->IsAdmin() && !$access) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}*/

$db_order = CSaleOrder::GetList(Array("ID"=>"DESC"), Array("ID"=>$ID));
if (!$db_order->ExtractFields("str_"))
    LocalRedirect("sale_order.php?lang=".LANG.GetFilterParams("filter_", false));

/*
 * Запросим заказ еще раз для события - в удобоваримом виде
 */
$dbOrder = CSaleOrder::GetList(
    array("ID" => "DESC"),
    array("ID" => $ID));
$arOrder = $dbOrder->Fetch();
/*
 * Создание событий для модуля (событие используется в order/detail.php, sale_order_edit.php, sale_order_print.php)
 */
$events = GetModuleEvents("linemedia.auto", "OnBeforeOrderShowDetailOrder");
while ($arEvent = $events->Fetch()) {
    try {
        $res = ExecuteModuleEventEx($arEvent, array(&$arOrder));
        if(is_array($res) && array_key_exists('PRICE', $res) && array_key_exists('TAX_VALUE', $res)) {
            $str_PRICE = $res['PRICE'];
            $str_TAX_VALUE = $res['TAX_VALUE'];
        }
    } catch (Exception $e) {
        throw $e;
    }
}

$APPLICATION->SetTitle(GetMessage("SALE_PRINT_RECORD", array("#ID#"=>$ID)));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
//$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $GLOBALS["USER"]->GetUserGroupArray());

$errorMessage = "";

if ($REQUEST_METHOD == "POST" && strlen($Print) > 0 && check_bitrix_sessid() && $lmCanViewOrder) {
    if (count($REPORT_ID) > 0) {
        $sBasket = "";
        $sQuantity = "";
        $bFirst = True;
        for ($i = 0; $i < count($BASKET_IDS); $i++) {
            if (IntVal($BASKET_IDS[$i]) <= 0) {
                continue;
            }
            $sBasket .= ($bFirst? "": ",").IntVal($BASKET_IDS[$i]);
            $sQuantity .= ($bFirst? "": ",").${"QUANTITY_".IntVal($BASKET_IDS[$i])};
            $bFirst = false;
        }
        ?>
        <script language="JavaScript">
        <?
        for ($i = 0; $i < count($REPORT_ID); $i++) {
            ?>
            window.open('/bitrix/admin/linemedia.auto_sale_print.php?doc=<?echo CUtil::JSEscape($REPORT_ID[$i]) ?>&ORDER_ID=<?echo $ID ?>&BASKET_IDS=<?echo urlencode($sBasket) ?>&QUANTITIES=<?echo urlencode($sQuantity) ?>', '_blank');
            <?
        }
        ?>
        </script>
        <?
    } else {
        $errorMessage = GetMessage("SOP_ERROR_REPORT");
    }
}

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
        array(
                "TEXT" => GetMessage("SOP_TO_LIST"),
                "LINK" => "/bitrix/admin/".$arPageSettings['LIST_PAGE']."?lang=".LANGUAGE_ID.GetFilterParams("filter_")
            )
    );

$aMenu[] = array("SEPARATOR" => "Y");

if ($lmCanEditOrder)
{
    $aMenu[] = array(
            "TEXT" => GetMessage("SOP_TO_EDIT"),
            "LINK" => "/bitrix/admin/".$arPageSettings['EDIT_PAGE']."?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
        );
}

if ($lmCanViewOrder)
{
    $aMenu[] = array(
            "TEXT" => GetMessage("SOP_TO_DETAIL"),
            "LINK" => "/bitrix/admin/".$arPageSettings['DETAIL_PAGE']."?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
        );
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

$curStatusPerms = LinemediaAutoProductStatus::getStatusesPermissions($str_STATUS_ID);
if($curStatusPerms['PERM_VIEW']=='Y' || $USER->IsAdmin()) $lmCanViewStatus = true;
else  $lmCanViewStatus = false;

if (!$lmCanViewOrder)
{
    CAdminMessage::ShowMessage(str_replace("#ID#", $ID, GetMessage("SOD_NO_PERMS2VIEW")).". ");
}
elseif(!$lmCanViewStatus)
{
    CAdminMessage::ShowMessage(str_replace("#ID#", $ID, GetMessage("SOE_NO_STATUS_VIEW_PERMS")).". ");
}
else
{
    CAdminMessage::ShowMessage($errorMessage);
    ?>
    <form method="POST" action="<?= $APPLICATION->GetCurPage()?>?" name="order_print">
    <?= GetFilterHiddens("filter_");?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="ID" value="<?= $ID ?>">
    <?= bitrix_sessid_post() ?>

    <?
    $aTabs = array(
            array("DIV" => "edit1", "TAB" => GetMessage("SOPN_TAB_PRINT"), "ICON" => "sale", "TITLE" => GetMessage("SOPN_TAB_PRINT_DESCR"))
        );

    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
    ?>

    <?
    $tabControl->BeginNextTab();
    ?>

        <tr>
            <td><?echo GetMessage("SALE_PR_ORDER_N")?>:</td>
            <td><?echo $ID ?></td>
        </tr>
        <tr>
            <td><?echo GetMessage("P_ORDER_DATE")?>:</td>
            <td><?echo $str_DATE_INSERT_FORMAT ?></td>
        </tr>
        <tr>
            <td><?echo GetMessage("P_ORDER_LANG")?>:</td>
            <td>
                <?
                echo "[".$str_LID."] ";
                $db_lang = CLang::GetByID($str_LID);
                if ($arLang = $db_lang->GetNext())
                {
                    echo $arLang["NAME"];
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><?echo GetMessage("P_ORDER_STATUS")?>:</td>
            <td>
                <?$ar_status = CSaleStatus::GetByID($str_STATUS_ID);?>
                [<?echo $ar_status["ID"] ?>] <?echo htmlspecialchars($ar_status["NAME"]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?echo GetMessage("P_ORDER_CANCELED")?> / <?echo GetMessage("P_ORDER_PAYED") ?> / <?echo GetMessage("P_ORDER_ALLOW_DELIVERY") ?>:
            </td>
            <td>
                <?
                echo (($str_CANCELED=="Y")?"<font color=\"#FF0000\"><b>":"");
                echo (($str_CANCELED=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
                echo (($str_CANCELED=="Y")?"</b>":"");
                ?>
                /
                <?
                echo (($str_PAYED=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
                ?>
                /
                <?
                echo (($str_ALLOW_DELIVERY=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
                ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
                    <tr class="heading">
                        <td><?echo GetMessage("SALE_PR_INCLUDE")?></td>
                        <td><?echo GetMessage("SALE_PR_NAME")?></td>
                        <td><?echo GetMessage("SALE_PR_QUANTITY")?></td>
                        <td><?echo GetMessage("SALE_PR_PRICE")?></td>
                        <td><?echo GetMessage("SALE_PR_SUM")?></td>
                        <? if($is_basket_files) { ?>
                            <td><?echo GetMessage("SALE_PR_PRINT_FILE")?></td>
                        <? } ?>
                    </tr>
                    <?
                    $db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ID));
                    while ($arBasket = $db_basket->GetNext())
                    {
                        /*
                         * Создание событий для модуля
                         */
                        $events = GetModuleEvents("linemedia.auto", "OnBeforeAdminShowBasketPrint");
                        while ($arEvent = $events->Fetch()) {
                            try {
                                ExecuteModuleEventEx($arEvent, array(&$arBasket));
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                        ?>
                        <tr>
                            <td valign="top">
                                <input type="checkbox" checked name="BASKET_IDS[]" value="<?echo $arBasket["ID"] ?>">
                            </td>
                            <td valign="top">
                                <?echo $arBasket["NAME"];?>
                            </td>
                            <td valign="top">
                                <input type="text" size="3" name="QUANTITY_<?echo $arBasket["ID"] ?>" value="<?echo $arBasket["QUANTITY"];?>">
                            </td>
                            <td valign="top" align="right">
                                <?=LinemediaAutoPrice::userAdminFormatCurrency($arBasket["PRICE"], $str_CURRENCY, $arOrder["DATE_INSERT"])?>
                            </td>
                            <td valign="top" align="right">
                                <?=LinemediaAutoPrice::userAdminFormatCurrency(IntVal($arBasket["QUANTITY"])*DoubleVal($arBasket["PRICE"]), $str_CURRENCY, $arOrder["DATE_INSERT"])?>
                            </td>
                            <? if($is_basket_files) { ?>
                            <td valign="top" align="right">
                                <? foreach($order_files['basket'][$arBasket["ID"]] as $file) { ?>
                                    <a target="_blank" title="<?=$file['file_name']?>" href="/bitrix/admin/linemedia.auto_print_file.php?folder=<?=$file['folder']?>&file=<?=$file['file_name']?>"><?=$file['type_name']?></a><br />
                                <? } ?>
                            </td>
                            <? } ?>
                        </tr>
                        <?
                    }
                    ?>
                </table>
            </td>
        </tr>
        <?
        $db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ID));
        while ($ar_tax_list = $db_tax_list->Fetch())
        {
            ?>
            <tr>
                <td align="right" width="50%">
                    <?
                    echo htmlspecialchars($ar_tax_list["TAX_NAME"]);
                    if ($ar_tax_list["IS_IN_PRICE"]=="Y")
                        echo " (".(($ar_tax_list["IS_PERCENT"]=="Y")?"".DoubleVal($ar_tax_list["VALUE"])."%, ":"").GetMessage("SALE_TAX_INPRICE").")";
                    elseif ($ar_tax_list["IS_PERCENT"]=="Y")
                        echo " (".DoubleVal($ar_tax_list["VALUE"])."%)";
                    ?>:
                </td>
                <td align="left" width="50%">
                    <?=LinemediaAutoPrice::userAdminFormatCurrency($ar_tax_list["VALUE_MONEY"], $str_CURRENCY, $arOrder["DATE_INSERT"])?>
                </td>
            </tr>
            <?
        }
        ?>
        <tr>
            <td align="right" width="50%">
                <?echo GetMessage("SALE_F_DELIVERY")?>:
            </td>
            <td align="left" width="50%">
                <?=LinemediaAutoPrice::userAdminFormatCurrency($str_PRICE_DELIVERY, $str_CURRENCY, $arOrder["DATE_INSERT"])?>
            </td>
        </tr>
        <tr>
            <td align="right" width="50%"><?echo GetMessage("SALE_F_ITOG")?>:</td>
            <td align="left" width="50%">
                <?=LinemediaAutoPrice::userAdminFormatCurrency($str_PRICE, $str_CURRENCY, $arOrder["DATE_INSERT"])?>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="2">&nbsp; </td>
        </tr>

        <tr>
            <td align="right" valign="top"><?echo GetMessage("SALE_PR_SHABLON")?>:<br><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
            <td>
                <select size="5" multiple name="REPORT_ID[]">
                    <?
                    $arSysLangs = array();
                    $db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
                    while ($arLang = $db_lang->Fetch())
                        $arSysLangs[] = $arLang["LID"];

                    $arReports = array();
                    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/"))
                    {
                        if ($handle = opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/"))
                        {
                            while (($file = readdir($handle)) !== false)
                            {
                                if ($file == "." || $file == "..")
                                    continue;

                                if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file) && strtoupper(substr($file, strlen($file)-4))==".PHP")
                                {
                                    $rep_title = $file;
                                    $file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file, "rb");
                                    $file_contents = fread($file_handle, 2500);
                                    fclose($file_handle);

                                    $rep_langs = "";
                                    $arMatches = array();
                                    if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
                                    {
                                        $arMatches[3] = Trim($arMatches[3]);
                                        if (strlen($arMatches[3])>0) $rep_title = $arMatches[3];
                                        $arMatches[2] = Trim($arMatches[2]);
                                        if (strlen($arMatches[2])>0) $rep_langs = $arMatches[2];
                                    }

                                    if (strlen($rep_langs)>0)
                                    {
                                        $bContinue = True;
                                        for ($ic = 0; $ic < count($arSysLangs); $ic++)
                                        {
                                            if (strpos($rep_langs, $arSysLangs[$ic])!==false)
                                            {
                                                $bContinue = False;
                                                break;
                                            }
                                        }
                                        if ($bContinue)
                                            continue;
                                    }

                                    $arReports[] = array(
                                            "PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file,
                                            "FILE" => $file,
                                            "TITLE" => $rep_title
                                        );
                                }
                            }
                        }
                        closedir($handle);
                    }

                    if ($handle = opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/reports/" . LANG . "/")) {
                        while (($file = readdir($handle)) !== false) {
                            if ($file == "." || $file == "..")
                                continue;

                            if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/reports/" . LANG . "/" . $file)
                                && !in_array($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/reports/" . LANG . "/" . $file, $arReports)
                                && strtoupper(substr($file, strlen($file)-4))==".PHP")
                            {
                                $rep_title = $file;
                                if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/" . LANG . "/reports/".$file)
                                    && strtoupper(substr($file, strlen($file)-4))==".PHP")
                                {
                                    $file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/" . LANG . "/reports/".$file, "rb");
                                    $file_contents = fread($file_handle, 2500);
                                    fclose($file_handle);
                                }
                                else
                                {
                                    $file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/reports/" . LANG . "/" . $file, "rb");
                                    $file_contents = fread($file_handle, 2500);
                                    fclose($file_handle);
                                }

                                $rep_langs = "";
                                $arMatches = array();
                                if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
                                {
                                    $arMatches[3] = Trim($arMatches[3]);
                                    if (strlen($arMatches[3])>0) $rep_title = $arMatches[3];
                                    $arMatches[2] = Trim($arMatches[2]);
                                    if (strlen($arMatches[2])>0) $rep_langs = $arMatches[2];
                                }

                                if (strlen($rep_langs)>0)
                                {
                                    $bContinue = True;
                                    for ($ic = 0; $ic < count($arSysLangs); $ic++)
                                    {
                                        if (strpos($rep_langs, $arSysLangs[$ic])!==false)
                                        {
                                            $bContinue = False;
                                            break;
                                        }
                                    }
                                    if ($bContinue)
                                        continue;
                                }

                                $arReports[] = array(
                                        "PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/reports/" . LANG . "/" . $file,
                                        "FILE" => $file,
                                        "TITLE" => $rep_title
                                    );
                            }
                        }
                    }
                    closedir($handle);

                    for ($ir = 0; $ir<count($arReports); $ir++):
                        ?>
                        <option value="<?echo substr($arReports[$ir]["FILE"], 0, strlen($arReports[$ir]["FILE"])-4); ?>"><?echo $arReports[$ir]["TITLE"];?></option>
                        <?
                    endfor;
                    ?>
                </select>
            </td>
        </tr>
        <? if(count($order_files['order']) > 0) { ?>
        <tr>
            <td align="right" width="50%"><?echo GetMessage("SALE_F_PRINT_FILE")?>:</td>
            <td align="left" width="50%">
                <? foreach($order_files['order'] as $file) { ?>
                    <a target="_blank" title="<?=$file['file_name']?>" href="/bitrix/admin/linemedia.auto_print_file.php?folder=<?=$file['folder']?>&file=<?=$file['file_name']?>"><?=$file['type_name']?></a><br />
                <? } ?>
            </td>
        </tr>
        <? } ?>
    <?
    $tabControl->EndTab();
    ?>

    <?
    $tabControl->Buttons();
    ?>
    <input type="hidden" name="Print" value="<?= GetMessage("SALE_PRINT")?>">
    <? if (!$crmMode) { ?>
        <input type="submit" class="button" value="<?= GetMessage("SALE_PRINT")?>" />
    <? } ?>

    <? $tabControl->End(); ?>

    </form>
    <?
}
?>
<br>
<?echo BeginNote();?>
    <?echo GetMessage("SALE_PR_NOTE1")?><br><br>
    <?echo GetMessage("SALE_PR_NOTE2")?><br><br>
    <?echo GetMessage("SALE_PR_NOTE3")?><br><br>
    <?echo GetMessage("SALE_PR_NOTE4")?><br><br>
    <?echo GetMessage("SALE_PR_NOTE5")?>
<?echo EndNote();?>


<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>
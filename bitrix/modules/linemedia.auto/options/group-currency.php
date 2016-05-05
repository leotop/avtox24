<? IncludeModuleLangFile(__FILE__);

$allGroups = array();
$allCurrency = array();

$rsAllGroups = CGroup::GetList($sort = "NAME", $asc = "asc", array("ACTIVE" => "Y"));
while ($arAllGroup = $rsAllGroups->Fetch()) {
    $arGroup = array();
    $arGroup["ID"] = intval($arAllGroup["ID"]);
    $arGroup["NAME"] = htmlspecialcharsbx($arAllGroup["NAME"]);
    $allGroups[$arGroup["ID"]] = $arGroup;
}

$LM_AUTO_MAIN_GROUP_CURRENCY = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GROUP_CURRENCY'));

$lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
while ($lcur_res = $lcur->Fetch()) {
    $allCurrency[ $lcur_res["CURRENCY"] ] = $lcur_res['CURRENCY'];
}

foreach($LM_AUTO_MAIN_GROUP_CURRENCY as $groupId => $currency) { ?>
    <tr class="rights_row">
        <td width="50%">
            <label><?= GetMessage('LM_AUTO_MAIN_GROUP') ?>:</label>
            <select class="s_branch" name="group_id[]">
                <?
                foreach($allGroups as $id => $group) {
                    if($groupId == $id) {
                        ?><option selected="selected" value="<?=$id?>"><?=$group['NAME']?></option><?
                    } else {
                        ?><option value="<?=$id?>"><?=$group['NAME']?></option><?
                    }
                }
                ?>
            </select>
        </td>
        <td valign="top">
            <label><?= GetMessage('LM_AUTO_MAIN_GROUP_CURRENCY') ?>:</label>
            <select class="s_supplier" name="currency[]">
                <option value="0"><?= GetMessage('LM_AUTO_MAIN_GROUP_CURRENCY_DEFAULT') ?></option>
                <?
                foreach($allCurrency as $id => $curr) {
                    if($curr == $currency) {
                        ?><option selected="selected" value="<?=$id?>"><?=$curr?></option><?
                    } else {
                        ?><option value="<?=$id?>"><?=$curr?></option><?
                    }
                }
                ?>
            </select>
        </td>
        <td>
            <a class="del_row" href="javascript:void(0)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a>
        </td>
    </tr>
<? } ?>
<tr class="rights_row">
    <td width="50%">
        <label><?= GetMessage('LM_AUTO_MAIN_GROUP') ?>:</label>
        <select class="s_branch" name="group_id[]">
            <?
            foreach($allGroups as $id => $group) {
                ?><option value="<?=$id?>"><?=$group['NAME']?></option><?
            }
            ?>
        </select>
    </td>
    <td valign="top">
        <label><?= GetMessage('LM_AUTO_MAIN_GROUP_CURRENCY') ?>:</label>
        <select class="s_supplier" name="currency[]">
            <option value="0"><?= GetMessage('LM_AUTO_MAIN_GROUP_CURRENCY_DEFAULT') ?></option>
            <?
            foreach($allCurrency as $id => $curr) {
                ?><option value="<?=$id?>"><?=$curr?></option><?
            }
            ?>
        </select>
    </td>
    <td>
        <a class="del_row" href="javascript:void(0)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a>
    </td>
</tr>
<tr class="btn_row">
    <td colspan="2" align="right">
        <a href="javascript:void(0)" onclick="addGroupCurrency();" hidefocus="true" class="adm-btn"><?= GetMessage('LM_AUTO_MAIN_GROUP_CURRENCY_ADD') ?></a>
    </td>
</tr>
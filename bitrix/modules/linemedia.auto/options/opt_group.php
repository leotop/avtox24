<?php 


$arGROUPS = array();
$arFilter = Array("ACTIVE"=>"Y");
if($md->SHOW_SUPER_ADMIN_GROUP_RIGHTS != "Y")
	$arFilter["ADMIN"] = "N";

$z = CGroup::GetList($v1="sort", $v2="asc", $arFilter);
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;
}
?>

<tr>
<td width="50%" valign="top">
<label for="LM_AUTO_MAIN_OPT_GROUP">
            <?= GetMessage('LM_AUTO_MAIN_OPT_GROUP') ?>
			<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>	
	<td>
	    <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_OPT_GROUP = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_OPT_GROUP')); ?>
        <select style="width: 100%;" id="LM_AUTO_MAIN_OPT_GROUP" name="LM_AUTO_MAIN_OPT_GROUP">
            <? foreach ($arGROUPS as $group) { ?>
			<option value="<?=$group["ID"]?>" <?if ($group['ID'] == $LM_AUTO_MAIN_OPT_GROUP) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>
	
	</td>     
</tr>


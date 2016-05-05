<?
if (!function_exists('GetMenuTreeHtml')) {
    // Функция построения меню с рекурсией
    function GetMenuTreeHtml($a_items = array(), $i = 0, $a_parent_id = array(), $i_level = 0)
    {
        global $aOptionValue;
        
        if (is_array($a_items) && count($a_items) > 0) {
            foreach ($a_items as $i_key => $a_item) {
                $i++;
                $s_input_key = ((count($a_parent_id) > 0) ? implode('_', $a_parent_id) . '_' : '') . $i_key;
                $s_input_id = 'LM_AUTO_MAIN_MENU_HIDE_STORE_' . $s_input_key;
                $s_input_name = 'LM_AUTO_MAIN_MENU_HIDE[STORE][' . $s_input_key . ']';
            ?>
            <div><input type="checkbox" name="<?= $s_input_name; ?>" id="<?= $s_input_id; ?>" value="Y" <?= (isset($aOptionValue['LM_AUTO_MAIN_MENU_HIDE']['STORE'][$s_input_key])) ? ' checked="checked"' : '' ?>/>
            <?
            if ($i_level > 0) {
                echo str_repeat('..', $i_level);
            }
            ?>
            <label for="<?= $s_input_id ?>"><?= $a_item['text'] ?></label></div>
            <?
                if (isset($a_item['items']) && is_array($a_item['items']) && count($a_item['items']) > 0) {
                    $a_parent_id_sum = $a_parent_id;
                    $a_parent_id_sum[] = $i_key;
                    GetMenuTreeHtml($a_item['items'], $i, $a_parent_id_sum, $i_level+1);
                    unset($a_parent_id_sum);
                }
            }
            unset($a_items, $a_item);
        }
    }
}
?>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE">
            <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE') ?>
			<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>
    <td valign="top">
		<?
		$md = CModule::CreateModuleObject($module_id);

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
     
        if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE" name="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE[]">
            <? foreach ($arGROUPS as $group) { ?>
			<option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>
    </td>
</tr>

<?//Скрывать пункт Настройки основного меню?>
<tr>
 <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN">
            <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN') ?>
			<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>	
	<td>
	    <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN" name="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN[]">
            <? foreach ($arGROUPS as $group) { ?>
			<option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>
	
	</td>
</tr>

<?//Скрыть меню Сервисы в основном меню?>
<tr>
 <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES">
            <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES') ?>
			<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>	
	<td>
	    <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES" name="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES[]">
            <? foreach ($arGROUPS as $group) { ?>
			<option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>
	
	</td>     
</tr>

<?//Скрыть меню Аналитика в основном меню?>
	<tr>
		<td width="50%" valign="top">
			<label for="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS">
				<?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS') ?>
				<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
			</label>
		</td>
		<td>
			<?if (!empty($arGROUPS)) { ?>
				<? $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS')); ?>
				<select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALITICS" name="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS[]">
					<? foreach ($arGROUPS as $group) { ?>
						<option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
					<? } ?>
				</select>
			<? } else { ?>
				<?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
			<? } ?>

		</td>
	</tr>
<?//Скрыть меню Управление инфоблоками (импорт, экспорт, настройки) в основном меню?>
    <tr>
        <td width="50%" valign="top">
            <label for="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION">
                <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION') ?>
                <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
            </label>
        </td>
        <td>
            <?if (!empty($arGROUPS)) { ?>
                <? $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION')); ?>
                <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION" name="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION[]">
                    <? foreach ($arGROUPS as $group) { ?>
                        <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
                    <? } ?>
                </select>
            <? } else { ?>
                <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
            <? } ?>

        </td>
    </tr>

<tr><td style="color: #000; font-size: 20px; font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;"><?echo GetMessage('LM_AUTO_MAIN_MENU_MAIN_MODULE_MENU_SETTINGS')?></td><td><hr></td></tr>

<?//Показать пункт меню Служебное -> Проверка цен в основном меню?>
<tr>
 <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK">
            <?= GetMessage('LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK') ?>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>    
    <td>
        <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK" name="LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK[]">
            <? foreach ($arGROUPS as $group) { ?>
            <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?> 
    
    </td>
</tr>

<?//Показать пункт меню Заявки поставщикам?>
<tr>
 <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS">
            <?= GetMessage('LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS') ?>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>    
    <td>
        <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS" name="LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS[]">
            <? foreach ($arGROUPS as $group) { ?>
            <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?> 
    
    </td>
</tr>


<?//Показать пункт меню Менеджеры?>
<tr>
 <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS">
            <?= GetMessage('LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS') ?>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>    
    <td>
        <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS" name="LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS[]">
            <? foreach ($arGROUPS as $group) { ?>
            <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>
    
    </td>
</tr>

<?//Показать пункт меню Покупатели?>
<tr>
 <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS">
            <?= GetMessage('LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS') ?>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>    
    <td>
        <?if (!empty($arGROUPS)) { ?>
        <? $LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS" name="LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS[]">
            <? foreach ($arGROUPS as $group) { ?>
            <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>
    
    </td>
</tr>

<?//Скрыть блок "Как зарабатывать больше"?>
    <tr>
        <td width="50%" valign="top">
            <label for="LM_AUTO_MAIN_MENU_HIDE_EARN_MORE">
                <?= GetMessage('LM_AUTO_MAIN_MENU_HIDE_EARN_MORE') ?>
                <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
            </label>
        </td>
        <td>
            <?if (!empty($arGROUPS)) { ?>
                <? $LM_AUTO_MAIN_MENU_HIDE_EARN_MORE = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_HIDE_EARN_MORE')); ?>
                <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_MENU_SHOW_EARN_MORE" name="LM_AUTO_MAIN_MENU_HIDE_EARN_MORE[]">
                    <? foreach ($arGROUPS as $group) { ?>
                        <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_MENU_HIDE_EARN_MORE)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
                    <? } ?>
                </select>
            <? } else { ?>
                <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
            <? } ?>

        </td>
    </tr>

<? /* if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/sale/admin/menu.php') && is_readable($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/sale/admin/menu.php')) { ?>
    <? require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/sale/admin/menu.php'); ?>
    <? $aOptionValue['LM_AUTO_MAIN_MENU_HIDE'] = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_HIDE', array())) ?>
    
    <tr id="LM_AUTO_MAIN_MENU_HIDE_STORE_TR" <? if (isset($aOptionValue['LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE']) && $aOptionValue['LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE'] == 'Y') { ?> style="display: none;"<? } ?>>
        <td valign="top" width="50%">
            <?= GetMessage('LM_AUTO_MAIN_MENU_HIDE') ?>:
        </td>
        <td valign="top" width="50%">
            <? GetMenuTreeHtml($aMenu) ?>
        </td>
    </tr>
<? } */ ?>

<tr><td style="color: #000; font-size: 20px; font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;"><?echo GetMessage('LM_AUTO_MAIN_GADGET_SETTINGS')?></td><td><hr></td></tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_GADGET_ORDERS_HIDE">
            <?= GetMessage('LM_AUTO_MAIN_GADGET_ORDERS_HIDE') ?>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </label>
    </td>
    <td>
        <?if (!empty($arGROUPS)) { ?>
            <? $LM_AUTO_MAIN_GADGET_ORDERS_HIDE = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GADGET_ORDERS_HIDE')); ?>
            <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_GADGET_ORDERS_HIDE" name="LM_AUTO_MAIN_GADGET_ORDERS_HIDE[]">
                <? foreach ($arGROUPS as $group) { ?>
                    <option value="<?=$group["ID"]?>" <?if(in_array($group['ID'], $LM_AUTO_MAIN_GADGET_ORDERS_HIDE)) { ?> selected="selected"<? } ?>><?=$group["NAME"]." [".$group["ID"]."]"?></option>
                <? } ?>
            </select>
        <? } else { ?>
            <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE_NO_GROUPS') ?>
        <? } ?>

    </td>
</tr>
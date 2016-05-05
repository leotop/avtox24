<?php
$statuses = LinemediaAutoOrder::getStatusesList();

?>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_RETURN_GOODS_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST" name="LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_USER_RETURN">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_USER_RETURN') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_USER_RETURN = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_USER_RETURN'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_USER_RETURN" name="LM_AUTO_MAIN_STATUS_USER_RETURN">
                <option value=""><?= GetMessage('LM_AUTO_MAIN_STATUS_NOT_IN_USE')?></option>
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_STATUS_USER_RETURN) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_MONEY_BACK">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_MONEY_BACK') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_MONEY_BACK = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_MONEY_BACK'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_MONEY_BACK" name="LM_AUTO_MAIN_STATUS_MONEY_BACK">
                <option value=""><?= GetMessage('LM_AUTO_MAIN_STATUS_NOT_IN_USE')?></option>
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_STATUS_MONEY_BACK) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK" name="LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK">
                <option value=""><?= GetMessage('LM_AUTO_MAIN_STATUS_NOT_IN_USE')?></option>
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_STATUS_SHOP_MONEY_BACK) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
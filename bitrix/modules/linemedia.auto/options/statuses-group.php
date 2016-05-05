<?php
$statuses = LinemediaAutoOrder::getStatusesList();
?>
<tr>
    <td colspan="2">
        <?= BeginNote();?>
        <?=GetMessage('LM_AUTO_MAIN_STATUSES_GROUP_NOTE')?>
        <?= EndNote(); ?>
    </td>
</tr>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_STATUSES_GROUP_1_TITLE') ?></td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_1_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_GROUP_1_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_GROUP_1_LIST" name="LM_AUTO_MAIN_STATUS_GROUP_1_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_GROUP_1_LIST)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO_STATUS') ?>
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_1_PAYED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_PAYED') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_1_PAYED" value="" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_PAYED', '') == '') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_ALL')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_1_PAYED" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_PAYED', '') == 'Y') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_YES')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_1_PAYED" value="N" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_PAYED', '') == 'N') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO')?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_CANCELED') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED" value="" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED', '') == '') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_ALL')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED', '') == 'Y') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_YES')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED" value="N" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_1_CANCELED', '') == 'N') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO')?>
    </td>
</tr>


<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_STATUSES_GROUP_2_TITLE') ?></td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_2_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_GROUP_2_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_GROUP_2_LIST" name="LM_AUTO_MAIN_STATUS_GROUP_2_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_GROUP_2_LIST)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO_STATUS') ?>
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_2_PAYED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_PAYED') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_2_PAYED" value="" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_PAYED', '') == '') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_ALL')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_2_PAYED" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_PAYED', '') == 'Y') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_YES')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_2_PAYED" value="N" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_PAYED', '') == 'N') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO')?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_CANCELED') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED" value="" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED', '') == '') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_ALL')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED', '') == 'Y') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_YES')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED" value="N" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_2_CANCELED', '') == 'N') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO')?>
    </td>
</tr>


<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_STATUSES_GROUP_3_TITLE') ?></td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_3_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_GROUP_3_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_GROUP_3_LIST" name="LM_AUTO_MAIN_STATUS_GROUP_3_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_GROUP_3_LIST)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO_STATUS') ?>
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_3_PAYED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_PAYED') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_3_PAYED" value="" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_PAYED', '') == '') ? ('checked="checked"') : ('') ?>" />-<?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_ALL')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_3_PAYED" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_PAYED', '') == 'Y') ? ('checked="checked"') : ('') ?>" />-<?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_YES')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_3_PAYED" value="N" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_PAYED', '') == 'N') ? ('checked="checked"') : ('') ?>" />-<?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO')?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_GROUP_CANCELED') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED" value="" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED', '') == '') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_ALL')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED', '') == 'Y') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_YES')?>
        <input type="radio" name="LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED" value="N" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_GROUP_3_CANCELED', '') == 'N') ? ('checked="checked"') : ('') ?>" /> - <?=GetMessage('LM_AUTO_MAIN_STATUS_GROUP_NO')?>
    </td>
</tr>
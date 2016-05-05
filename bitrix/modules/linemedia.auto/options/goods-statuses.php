<? $statuses = LinemediaAutoOrder::getStatusesList(); ?>

<? if (!empty($statuses)) { ?>
    <?
        $inputs = array();
        foreach ($statuses as $status) {
            $inputs []= '#LM_AUTO_MAIN_STATUS_COLOR_'.$status['ID'];
            $inputs []= '#LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_'.$status['ID'];
        }
    ?>
    
<? } ?>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_GOODS_STATUSES_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY">
            <?= GetMessage('LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY" id="LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_ID_AFTER_PAY">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_ID_AFTER_PAY') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_ID_AFTER_PAY = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_ID_AFTER_PAY'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_ID_AFTER_PAY" name="LM_AUTO_MAIN_STATUS_ID_AFTER_PAY">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_STATUS_ID_AFTER_PAY) { ?> selected="selected"<? } ?>>
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
        <label for="LM_AUTO_MAIN_CANCEL_STATUS_ID">
            <?= GetMessage('LM_AUTO_MAIN_CANCEL_STATUS_ID') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_CANCEL_STATUS_ID = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_CANCEL_STATUS_ID'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_CANCEL_STATUS_ID" name="LM_AUTO_MAIN_CANCEL_STATUS_ID">
                <option value=""></option>
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_CANCEL_STATUS_ID) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
            <label>
                <input type="checkbox" name="LM_AUTO_MAIN_CANCEL_ON_STATUS" id="LM_AUTO_MAIN_CANCEL_ON_STATUS" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_CANCEL_ON_STATUS', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
                - <?=GetMessage('LM_AUTO_MAIN_CANCEL_ON_STATUS');?>
            </label>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST" name="LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST)) { ?> selected="selected"<? } ?>>
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
        <label for="LM_AUTO_MAIN_USER_CANCEL_REASON">
            <?= GetMessage('LM_AUTO_MAIN_USER_CANCEL_REASON') ?>:
        </label>
    </td>
    <td valign="top">
         <? $LM_AUTO_MAIN_USER_CANCEL_REASON = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_USER_CANCEL_REASON'); ?>
         <textarea id="LM_AUTO_MAIN_USER_CANCEL_REASON" name="LM_AUTO_MAIN_USER_CANCEL_REASON" rows="2" cols="38"><?=$LM_AUTO_MAIN_USER_CANCEL_REASON?></textarea>
    </td>
</tr>

<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST" name="LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST)) { ?> selected="selected"<? } ?>>
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
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT" name="LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<? if (IsModuleInstalled('support')) { ?>
    <tr>
        <td width="50%">
            <label for="LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID">
                <?= GetMessage('LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID') ?>:
            </label>
        </td>
        <td valign="top">
            <? if (!empty($statuses)) { ?>
                <? $LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID'); ?>
                <select style="width: 100%;" id="LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID" name="LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID">
                    <? foreach ($statuses as $status) { ?>
                        <option value="<?= $status['ID'] ?>" <? if ($status['ID'] == $LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID) { ?> selected="selected"<? } ?>>
                            [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                        </option>
                    <? } ?>
                </select>
            <? } else { ?>
                Нет статусов
            <? } ?>
        </td>
    </tr>
<? } ?>

<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT" name="LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT[]">
                <option value=""></option>
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_REQUIRE_COMMENT)) { ?> selected="selected"<? } ?>>
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
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_CANCEL_REQUIRE_COMMENT">
            <?= GetMessage('LM_AUTO_MAIN_CANCEL_REQUIRE_COMMENT') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_CANCEL_REQUIRE_COMMENT" id="LM_AUTO_MAIN_CANCEL_REQUIRE_COMMENT" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_CANCEL_REQUIRE_COMMENT', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>

<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_TO_UNLOAD">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_TO_UNLOAD') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_TO_UNLOAD = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_TO_UNLOAD');
			?>
            <select id="LM_AUTO_MAIN_STATUS_TO_UNLOAD" name="LM_AUTO_MAIN_STATUS_TO_UNLOAD[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if ($status['ID'] == $LM_AUTO_MAIN_STATUS_TO_UNLOAD["0"]) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>

<tr class="heading">
    <td colspan="2"><?php echo GetMessage('LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN_TITLE') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN')); ?>
            <select multiple="multiple" style="width: 100%;" id="LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN" name="LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if(in_array($status['ID'], $LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
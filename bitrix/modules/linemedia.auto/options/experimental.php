<?php
IncludeModuleLangFile(__FILE__);
$LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT = ('Y' == COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N'));
$LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST = ('Y' == COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST', 'N'));
$LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH = ('Y' == COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH', 'N'));
?>
<tr>
    <td colspan="2">
    	<?= BeginNote();?>
	    <?=GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_NOTE')?>
	    <?= EndNote(); ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT">
            <?= GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT') ?>
        </label>
    </td>
    <td>
        <input type="checkbox" name="LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT" id="LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT" value="Y" <?=($LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT ? 'checked':'')?> />
        <small><?= GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT_DESCR') ?></small>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST">
            <?= GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST') ?>
        </label>
    </td>
    <td>
        <input type="checkbox" name="LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST" id="LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST" value="Y" <?=($LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST ? 'checked':'')?> />
        <small><?= GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST_DESCR') ?></small>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH">
            <?= GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH') ?>
        </label>
    </td>
    <td>
        <input type="checkbox" name="LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH" id="LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH" value="Y" <?=($LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH ? 'checked':'')?> />
        <small><?= GetMessage('LM_AUTO_MAIN_EXPERIMENTAL_ORM_SEARCH_DESCR') ?></small>
    </td>
</tr>
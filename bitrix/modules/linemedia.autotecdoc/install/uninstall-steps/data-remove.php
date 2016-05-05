<?
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * data-remove
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__) ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" method="post" id="LM_AUTO_TECDOC_frm">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autotecdoc" />
	<input type="hidden" name="uninstall" value="Y" />
	<input type="hidden" name="uninstall_step_id" value="finish" />
    
    <p><?= GetMessage("MOD_UNINST_SAVE") ?></p>
    <p>
        <input type="checkbox" name="REMOVE_IBLOCKS" id="REMOVE_IBLOCKS" value="Y">
        <label for="REMOVE_IBLOCKS"><?= GetMessage("LM_AUTO_TECDOC_REMOVE_IBLOCKS_DESC") ?></label>
    </p>
    
	<input type="button" onclick="if (confirm('<?= GetMessage('LM_AUTO_TECDOC_CONFIRM_REMOVE') ?>')) $('#LM_AUTO_TECDOC_frm').submit()" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>

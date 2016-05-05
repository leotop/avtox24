<? IncludeModuleLangFile(__FILE__) ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" method="post" id="lm_auto_main_frm">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autogarage" />
	<input type="hidden" name="uninstall" value="Y" />
	<input type="hidden" name="uninstall_step_id" value="finish" />
	

    <?= CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN")) ?>
	<p><?= GetMessage("MOD_UNINST_SAVE") ?></p>
	<p>
	    <input type="checkbox" name="REMOVE_GARAGE" id="REMOVE_GARAGE" value="Y" />
	    <label for="REMOVE_GARAGE"><?= GetMessage("LM_AUTO_GARAGE_REMOVE_GARAGE") ?></label>
	</p>
	<input type="submit" value="<?= GetMessage("MOD_UNINST_DEL") ?>" />
</form>

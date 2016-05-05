<?IncludeModuleLangFile(__FILE__);?>

<form action="<?=$APPLICATION->GetCurPage()?>" method="post" id="lm_auto_main_frm">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="linemedia.autosuppliers">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="uninstall_step_id" value="finish">
	

    <?=CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?=GetMessage("MOD_UNINST_SAVE")?></p>
	<p>
	    <input type="checkbox" name="REMOVE_REQUESTS" id="REMOVE_REQUESTS" value="Y">
	    <label for="REMOVE_REQUESTS"><?echo GetMessage("LM_AUTO_SUPPLIERS_REMOVE_REQUESTS_DESC")?></label>
	</p>
	<input type="button" onclick="if(confirm('<?=GetMessage('LM_AUTO_SUPPLIERS_CONFIRM_REMOVE')?>')) document.getElementById('lm_auto_main_frm').submit()" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>

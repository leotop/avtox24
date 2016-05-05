<?
/**
 * Linemedia Autoportal
 * Downloader module
 * Uninstall step
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
IncludeModuleLangFile(__FILE__);?>

<form action="<?=$APPLICATION->GetCurPage()?>" method="post" id="lm_auto_main_frm">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="linemedia.autodownloader">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="uninstall_step_id" value="finish">
	

    <?=CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	
	<p>
		<label>
			<?=GetMessage('LM_AUTO_DOWNLOADER_REMOVE_DB')?>
			<input type="checkbox" name="REMOVE_DB" value="Y">
		</label>
	</p>
	
	<p>
		<label>
			<?=GetMessage('LM_AUTO_DOWNLOADER_REMOVE_FILES')?>
			<input type="checkbox" name="REMOVE_FILES" value="Y">
		</label>
	</p>
	
	<input type="button" onclick="if(confirm('<?=GetMessage('LM_AUTO_DOWNLOADER_CONFIRM_REMOVE')?>')) document.getElementById('lm_auto_main_frm').submit()" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>

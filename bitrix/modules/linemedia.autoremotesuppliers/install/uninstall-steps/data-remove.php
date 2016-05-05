<?
/**
 * Linemedia Autoportal
 * Remote suppliers module
 * uninstall step
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
IncludeModuleLangFile(__FILE__);?>

<form action="<?=$APPLICATION->GetCurPage()?>" method="post" id="lm_auto_main_frm">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="linemedia.autoremotesuppliers">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="uninstall_step_id" value="finish">
	

    <?=CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	
	<input type="button" onclick="if(confirm('<?=GetMessage('LM_AUTO_REMOTE_SUPPLIERS_CONFIRM_REMOVE')?>')) document.getElementById('lm_auto_main_frm').submit()" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>

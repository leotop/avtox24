<?php
/**
 * Системный файл для удаления модуля
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
	<input type="hidden" name="id" value="linemedia.autoanalogssimple">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="uninstall_step_id" value="finish">
	

    <?=CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?=GetMessage("MOD_UNINST_SAVE")?></p>
	<p>
	    <input type="checkbox" name="REMOVE_ANALOGS" id="REMOVE_ANALOGS" value="Y">
	    <label for="REMOVE_ANALOGS"><?echo GetMessage("LM_AUTO_AS_REMOVE_ANALOGS")?></label>
	</p>
	<input type="submit" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>

<?php 
/**
 * Linemedia Autoportal
 * Analogs simple module
 * db create
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 IncludeModuleLangFile(__FILE__);
?>



<?
echo BeginNote();
echo GetMessage('LM_AUTO_AS_DATABASE_DESC');
echo EndNote();
?>


<?foreach((array) $GLOBALS['LM_AUTO_SA_SIMPLE'] AS $error)
    ShowError($error);
?>

<form action="<?=$APPLICATION->GetCurPage()?>" id="analogs-db-create-frm" method="post">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="linemedia.autoanalogssimple">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="install_step_id" value="finish">
	
    
    <p>
        <input type="submit" value="<?=GetMessage('LM_AUTO_AS_INSTALL')?>" />
    </p>
</form>

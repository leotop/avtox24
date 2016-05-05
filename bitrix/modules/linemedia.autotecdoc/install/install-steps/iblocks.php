<? IncludeModuleLangFile(__FILE__) ?>

<? if ($_REQUEST['DEMO_FOLDER_INSTALL'] == 'Y') { ?>
    <?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_TECDOC_DEMO_FOLDER_STEP_SUCCESS"), 'TYPE' => 'OK')) ?>
<? } ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="LM_AUTO_TECDOC" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autotecdoc" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="finish" />
	
	<?= BeginNote() ?>
	<?= GetMessage('LM_AUTO_TECDOC_IBLOCKS_DESC') ?>
	<?= EndNote() ?>
	
    <p>
        <input type="submit" value="<?= GetMessage('LM_AUTO_TECDOC_INSTALL_FOLDER') ?>" />
    </p>
</form>

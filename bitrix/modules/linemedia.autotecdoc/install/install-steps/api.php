<? IncludeModuleLangFile(__FILE__) ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="LM_AUTO_TECDOC" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autotecdoc" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="demo-folder" />
	
	<?= BeginNote() ?>
	<?= GetMessage('LM_AUTO_TECDOC_REGISTER_API_DESC') ?>
	<br/><br/>
	<?= GetMessage('LM_AUTO_TECDOC_REGISTER_API_WARN') ?>
	<?= EndNote() ?>
	
	<table class="list-table">
		<tr class="head">
			<td colspan="2"><?= GetMessage("LM_AUTO_TECDOC_REGISTER_API_HEADER") ?></td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_TECDOC_REGISTERING_API_SEND_SITE_NAME') ?>:</td>
			<td>
				<input type="text" id="SEND_SITENAME" name="send_sitename" value="<?= $_SERVER['SERVER_NAME'] ?>" />
			</td>
		</tr>
	</table>
	
	<p>
        <input type="submit" name="register_api" id="register_api" value="<?= GetMessage('LM_AUTO_TECDOC_REGISTER_START') ?>" />
    </p>
</form>
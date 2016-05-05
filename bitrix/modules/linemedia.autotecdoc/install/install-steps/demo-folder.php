<? IncludeModuleLangFile(__FILE__); ?>

<? if (!isset($_REQUEST['parts_db_cancel'])) { ?>
    <?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_TECDOC_PARTS_DB_STEP_SUCCESS"), 'TYPE' => 'OK')) ?>
<? } ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="LM_AUTO_TECDOC" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autotecdoc" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="iblocks" />
	
	<?= BeginNote() ?>
	<?= GetMessage('LM_AUTO_TECDOC_DEMO_FOLDER_DESC') ?>
	<?= EndNote() ?>
	
	
	<table class="list-table">
		<tr class="head">
			<td colspan="2"><?=GetMessage('LM_AUTO_TECDOC_DEMO_FOLDER_INSTALL_HEADER')?></td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage('LM_AUTO_TECDOC_DEMO_FOLDER_INSTALL')?>:</td>
			<td>
			    <input type="checkbox" id="DEMO_FOLDER_INSTALL" name="DEMO_FOLDER_INSTALL" value="Y" checked="checked" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage('LM_AUTO_TECDOC_DEMO_FOLDER_PATH')?>:</td>
			<td>
			    <input class="input-large" type="text" id="DEMO_FOLDER_PATH" name="DEMO_FOLDER_PATH" value="/tecdoc/" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage('LM_AUTO_TECDOC_DEMO_FOLDER_REWRITE')?>:</td>
			<td>
			    <input type="checkbox" id="DEMO_FOLDER_REWRITE" name="DEMO_FOLDER_REWRITE" value="Y" />
			</td>
		</tr>
	</table>
	
    <p>
        <input type="submit" value="<?= GetMessage('LM_AUTO_TECDOC_INSTALL_FOLDER') ?>" />
    </p>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $('#DEMO_FOLDER_INSTALL').click(function() {
            $('#DEMO_FOLDER_PATH').attr('disabled',    $('#DEMO_FOLDER_INSTALL').attr('checked') != 'checked');
            $('#DEMO_FOLDER_REWRITE').attr('disabled', $('#DEMO_FOLDER_INSTALL').attr('checked') != 'checked');
        });
    });
</script>

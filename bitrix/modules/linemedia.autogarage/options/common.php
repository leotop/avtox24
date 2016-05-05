<tr>
    <td width="50%">
        <label for="LM_AUTO_GARAGE_COMMON_DEMO_FOLDER_TITLE">
            <?= GetMessage('LM_AUTO_GARAGE_COMMON_DEMO_FOLDER_TITLE') ?>:
        </label>
    </td>
    <td valign="top">
        <input size="50" type="text" name="LM_AUTO_GARAGE_DEMO_FOLDER" id="LM_AUTO_GARAGE_DEMO_FOLDER" value="<?= COption::GetOptionString($sModuleId, 'LM_AUTO_GARAGE_DEMO_FOLDER', '/garage/') ?>" />
    </td>
</tr>
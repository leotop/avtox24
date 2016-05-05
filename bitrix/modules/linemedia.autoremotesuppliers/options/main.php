<?
IncludeModuleLangFile(__FILE__);
//$cache_time = COption::GetOptionString("linemedia.autoremotesuppliers", "cache_time", "0");
?>

<?/*?>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_REMOTE_SUPPLIERS_CACHE">
            <?= GetMessage('LM_AUTO_REMOTE_SUPPLIERS_CACHE') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="text" name="cache_time" value="<?= htmlspecialchars($cache_time) ?>" />
    </td>
</tr>
<?*/?>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES">
            <?= GetMessage('LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES" value="Y" <?= (COption::GetOptionString('linemedia.autoremotesuppliers', 'LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES', 'N') == 'Y') ? ('checked="checked"') : ('') ?> />
    </td>
</tr>
<?IncludeModuleLangFile(__FILE__);?>
<tr>
    <td colspan="2">
    	<?= BeginNote();?>
	    <?=GetMessage('LM_AUTO_AS_CSV_IMPORT_NOTE')?>
	    <?= EndNote(); ?>
    </td>
</tr>

<tr>
    <td width="50%" valign="top"><label for="CSV_IMPORT_STRING"><?=GetMessage( 'LM_AUTO_AS_CSV_IMPORT_STRING' );?>:</td>
    <td valign="top">
        <input size="50" type="text" name="CSV_IMPORT_STRING" id="CSV_IMPORT_STRING" value="<?=htmlspecialchars(COption::GetOptionString( 'linemedia.autoanalogssimple', 'CSV_IMPORT_STRING', 'art_orig;brand_orig;art_analog;brand_analog' ))?>">
    </td>
</tr>

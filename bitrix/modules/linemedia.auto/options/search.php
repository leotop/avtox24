<?php
IncludeModuleLangFile(__FILE__);
$LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS	= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS', '31');
$LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO          	= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO', 'Y');
$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES', 'Y');
$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL 	= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL', 'Y');
$LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES', 'Y');
$LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES', 'N');
$LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY', 'N');
?>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO" id="LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO" value="Y" <?=$LM_AUTO_MAIN_SEARCH_ANALOGS_PHOTO == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

<tr>
	<td width="50%">
		<label for="LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS">
			<?= GetMessage('LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS') ?>
		</label>
	</td>
	<td>
		<input type="text" name="LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS" id="LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS" size="3" value="<?= COption::GetOptionInt('linemedia.auto',
			'LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS', 31) ?>" />
		<?= GetMessage('LM_AUTO_MAIN_SEARCH_STATISTICS_LIFETIME_DAYS_DAYS') ?>
	</td>
</tr>
<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_SEARCH_CROSSES_TITLE') ?></td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES" id="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES" value="Y" <?=$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL" id="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL" value="Y" <?= $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES" id="LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES" value="Y" <?= $LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY" id="LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY" value="Y" <?= $LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES" id="LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES" value="Y" <?= $LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>




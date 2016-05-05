<?php
/**
 * Административный файл с настройками модуля
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
  IncludeModuleLangFile( __FILE__ );
$LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT = COption::GetOptionString($sModuleId, 'LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT', 'Y');
$LM_AUTO_ANALOGSSIMPLE_ADMIN_LIST_LIMIT = COption::GetOptionString($sModuleId, 'LM_AUTO_ANALOGSSIMPLE_ADMIN_LIST_LIMIT', 'N');
?>

<tr>
    <td colspan="2">
    	<?= BeginNote();?>
	    <?=GetMessage('LM_AUTO_ANALOGSSIMPLE_LIMITS_NOTE')?>
	    <?= EndNote(); ?>
    </td>
</tr>

<tr>
    <td width="50%">
        <label for="LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC">
            <?= GetMessage('LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC" id="LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC', 'N') == 'Y') ? 'checked="checked"':'' ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH">
            <?= GetMessage('LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH" id="LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>

<tr>
	<td width="50%" valign="top">
		<label for="LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS">
			<?= GetMessage('LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS') ?>:
		</label>
	</td>
	<td valign="top">
		<input size="3" type="text" name="LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS" id="LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS" value="<?=COption::GetOptionString($sModuleId,
			'LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS', '3' )?>">	</td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT">
            <?= GetMessage('LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT" id="LM_AUTO_MAIN_SEARCH_OEM_CROSSES_SOUGHT" value="Y" <?= $LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

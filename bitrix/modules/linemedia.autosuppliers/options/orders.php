<?
IncludeModuleLangFile( __FILE__ );

$statuses = array();
$res = CSaleStatus::GetList();
while ($status = $res->Fetch()) {
    $statuses[$status['ID']] = $status;
}

$first_statuses = unserialize(COption::GetOptionString($sModuleId, 'FIRST_STATUSES'));

$current_requested_id = COption::GetOptionString($sModuleId, 'REQUESTED_GOODS_STATUS', '');

$closed_statuses = unserialize(COption::GetOptionString($sModuleId, 'CLOSED_STATUSES'));

?>

<tr>
	<td width="50%" valign="top">
		<label for="LM_AUTO_PRICE_FORMAT">
			<?= GetMessage('LM_AUTO_SUPPLIERS_XLS_TYPE') ?>:
		</label>
	</td>
	<td valign="top">
		<? $formats = array(
			'html' => GetMessage('LM_AUTO_SUPPLIERS_XLS_TYPE_HTML'),
			'ssconvert' => GetMessage('LM_AUTO_SUPPLIERS_XLS_TYPE_SSCONVERT')
		) ?>
		<? $default = COption::GetOptionString($sModuleId, 'LM_AUTO_SUPPLIERS_XLS_TYPE', 'ssconvert') ?>
		<select name="LM_AUTO_SUPPLIERS_XLS_TYPE">
			<? foreach ($formats as $format => $title) {  ?>
				<option value="<?= $format ?>" <?= ($default == $format) ? ('selected') : ('') ?>><?= $title ?></option>
			<? } ?>
		</select>
	</td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_SUPPLIERS_EXPORT_ORIGINAL_ARTICLES">
            <?= GetMessage('LM_AUTO_SUPPLIERS_EXPORT_ORIGINAL_ARTICLES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_SUPPLIERS_EXPORT_ORIGINAL_ARTICLES" id="LM_AUTO_SUPPLIERS_EXPORT_ORIGINAL_ARTICLES" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_SUPPLIERS_EXPORT_ORIGINAL_ARTICLES', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>

<tr>
	<td width="50%" valign="top">
		<span style="color: red">*</span><?= GetMessage('LM_AUTO_SUPPLIERS_XLS_TYPE_HELP_SSCONVERT') ?>
	</td>
	<td width="50%" valign="top" >
		<span style="color: red">*</span><?= GetMessage('LM_AUTO_SUPPLIERS_XLS_TYPE_HELP_HTML') ?>
	</td>
</tr>

<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_SUPPLIERS_REQUESTED_FIRST_STATUSES">
            <?= GetMessage('LM_AUTO_SUPPLIERS_REQUESTED_FIRST_STATUSES') ?>:
        </label>
    </td>
    <td valign="top" width="50%">
        <select name="LM_AUTO_SUPPLIERS_REQUESTED_FIRST_STATUSES[]" id="LM_AUTO_SUPPLIERS_REQUESTED_FIRST_STATUSES" size="10" multiple="multiple">
            <? foreach ($statuses as $id => $status) { ?>
                <option value="<?= $id ?>"<?= (in_array($id, $first_statuses)) ? ' selected' : ''?>>
                    <?= $status['NAME'] ?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_SUPPLIERS_REQUESTED_GOODS_STATUS">
            <?= GetMessage('LM_AUTO_SUPPLIERS_REQUESTED_GOODS_STATUS') ?>:
        </label>
    </td>
    <td valign="top" width="50%">
        <select name="LM_AUTO_SUPPLIERS_REQUESTED_GOODS_STATUS" id="LM_AUTO_SUPPLIERS_REQUESTED_GOODS_STATUS">
            <option value=""><?=GetMessage('NOT_SELECTED')?></option>
            <? foreach ($statuses as $id => $status) { ?>
                <option value="<?=$id?>"<?=($id == $current_requested_id) ? ' selected' : ''?>><?=$status['NAME']?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?></option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_SUPPLIERS_CLOSED_STATUSES">
            <?= GetMessage('LM_AUTO_SUPPLIERS_CLOSED_STATUSES') ?>:
        </label>
    </td>
    <td valign="top" width="50%">
        <select name="LM_AUTO_SUPPLIERS_CLOSED_STATUSES[]" id="LM_AUTO_SUPPLIERS_CLOSED_STATUSES" size="10" multiple="multiple">
            <? foreach ($statuses as $id => $status) { ?>
                <option value="<?= $id ?>"<?= (in_array($id, $closed_statuses)) ? ' selected' : ''?>>
                    <?= $status['NAME'] ?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>

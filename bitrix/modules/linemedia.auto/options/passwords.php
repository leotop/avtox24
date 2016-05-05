<? IncludeModuleLangFile(__FILE__) ?>

<tr>
    <td colspan="2">
        <?= BeginNote();?>
            <?= GetMessage('LM_AUTO_MAIN_PASSWORDS_DESC') ?>
        <?= EndNote(); ?>
    </td>
</tr>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td colspan="2">
        <?= BeginNote() ?>
            <?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_DESC') ?>
        <?= EndNote() ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN">
            <?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN') ?>:
        </label>
    </td>
    <td>
        <input type="text" autocomplete="off" name="LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN" id="LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN', '') ?>" />
    </td>
</tr>

<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD">
            <?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD') ?>:
        </label>
    </td>
    <td>
        <input type="password" autocomplete="off" name="LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD" id="LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD', '') ?>" />
    </td>
</tr>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_ACCESS_REMOTE_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH">
            <?= GetMessage('LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH') ?>:
        </label>
    </td>
    <td>
        <input type="checkbox" name="LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH" id="LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH" value="Y" <?= $LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH != 'N' ? 'checked="checked"' : '' ?> />
    </td>
</tr>


<tr>
	<td width="50%" valign="top">
		<label for="LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH">
			<?= GetMessage('LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH') ?>
			<br/><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt=""/>
		</label>
	</td>
	<td valign="top">
		<?

		$allGroups = array();

		$rsAllGroups = CGroup::GetList($sort = "NAME", $asc = "asc", array("ACTIVE" => "Y"));
		while ($arAllGroup = $rsAllGroups->Fetch()) {
			$arGroup = array();
			$arGroup["ID"] = intval($arAllGroup["ID"]);
			$arGroup["NAME"] = htmlspecialcharsbx($arAllGroup["NAME"]);
			$allGroups[] = $arGroup;
		}

		if (!empty($allGroups)) {
			 $LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH')); ?>
			<select style="width: 100%;" multiple="multiple" size="10" id="$LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH" name="LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH[]">
				<? foreach ($allGroups as $group) { ?>
					<option
						value="<?= $group["ID"] ?>" <? if (in_array($group['ID'], $LM_AUTO_MAIN_ACCESS_GROUPS_REMOTE_SEARCH)) { ?> selected="selected"<? } ?>><?= $group["NAME"] . " [" . $group["ID"] . "]" ?></option>
				<? } ?>
			</select>
		<? } ?>
	</td>
</tr>
<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_CROSSES_ACCESS_TITLE') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_ACCESS_CROSSES_ENABLED">
            <?= GetMessage('LM_AUTO_MAIN_ACCESS_CROSSES_ENABLED') ?>:
        </label>
    </td>
    <td>
        <input type="checkbox" name="LM_AUTO_MAIN_ACCESS_CROSSES_ENABLED" id="LM_AUTO_MAIN_ACCESS_CROSSES_ENABLED" value="Y" <?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ACCESS_CROSSES_ENABLED', 'N') != 'Y' ? '' : 'checked="checked"' ?> />
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_CROSSES_LOGIN">
            <?= GetMessage('LM_AUTO_MAIN_CROSSES_LOGIN') ?>:
        </label>
    </td>
    <td>
        <input type="text" autocomplete="off" name="LM_AUTO_MAIN_CROSSES_LOGIN" id="LM_AUTO_MAIN_CROSSES_LOGIN" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CROSSES_LOGIN', '') ?>" />
    </td>
</tr>

<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_CROSSES_PASSWORD">
            <?= GetMessage('LM_AUTO_MAIN_CROSSES_PASSWORD') ?>:
        </label>
    </td>
    <td>
        <input type="password" autocomplete="off" name="LM_AUTO_MAIN_CROSSES_PASSWORD" id="LM_AUTO_MAIN_CROSSES_PASSWORD" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CROSSES_PASSWORD', '') ?>" />
    </td>
</tr>
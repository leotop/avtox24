<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));


if (!check_bitrix_sessid('sessid')) {
	die('Error session');
}

if ($_REQUEST['action'] == 'getProfiles') {

	if (!CModule::IncludeModule("sale")) {
		ShowError('Sale module include error');
		return;
	}

	/* User Profiles Begin */
	$userProfiles = array();
	$personTypeId = (int) $_REQUEST['PERSON_TYPE'];
	$userId = (int) $_REQUEST['user_id'];
	$checked = false;

	$dbUserProfiles = CSaleOrderUserProps::GetList(
		array("DATE_UPDATE" => "DESC"),
		array(
			"PERSON_TYPE_ID" => $personTypeId,
			"USER_ID" => $userId
		)
	);
	while($arUserProfiles = $dbUserProfiles->GetNext()) {
		$userProfiles[$arUserProfiles["ID"]] = $arUserProfiles;
	}

	?>
	<tr>
		<td class="name"><?=GetMessage('LM_AUTO_USER_PROFILE')?></td>
		<td>
			<select name="PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
				<option value="0"><?=GetMessage('LM_AUTO_NEW_PROFILE')?></option>
				<?
				foreach($userProfiles as $arUserProfiles)
				{?>
					<option value="<?= $arUserProfiles["ID"] ?>"<?if (!$checked) echo " selected";?>><?=$arUserProfiles["NAME"]?></option>
				<?
					$checked = true;
				}
				?>
			</select>
		</td>
	</tr>

<?
}
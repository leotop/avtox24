<?
// person types
$persons = false;
if(CModule::IncludeModule('sale')) {

    $persons = array();
    $selected_persons = (array) unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_NO_TRANSACTION_PERSON', ''));

    $res = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("ACTIVE" => "Y"));
    while($row = $res->Fetch()) {
        $persons[] = array(
            'ID' => $row['ID'],
            'NAME' => $row['NAME'],
            'SELECTED' => (in_array($row['ID'], $selected_persons)),
        );
    }
}

?>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_STATUS_CHANGE_OPERATION_TITLE') ?></td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH" id="LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_PAY_FROM_ACCOUNT_DURING_RESERVE">
            <?= GetMessage('LM_AUTO_MAIN_PAY_FROM_ACCOUNT_DURING_RESERVE') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_PAY_FROM_ACCOUNT_DURING_RESERVE" id="LM_AUTO_MAIN_PAY_FROM_ACCOUNT_DURING_RESERVE" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_PAY_FROM_ACCOUNT_DURING_RESERVE', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>

<tr class="heading">
	<td colspan="2"><?= GetMessage('ADMIN_PERMITTED_FINAL_STATUS') ?></td>
</tr>

<tr>
	<td valign="top" width="50%">
        <span id="STATUS_APPROVED_DM_HINT"></span>
        <script>BX.hint_replace(BX('STATUS_APPROVED_DM_HINT'), '<?= GetMessage('STATUS_APPROVED_DM_HINT') ?>');</script>
		<label for="STATUS_APPROVED_DM">
			<?= GetMessage('STATUS_APPROVED_DM') ?> :
		</label>
		<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
	</td>
	<td width="50%">

		<select id="STATUS_APPROVED_DM" name="STATUS_APPROVED_DM">

			<?
			$STATUS_APPROVED_DM = (string) COption::GetOptionString($sModuleId, 'STATUS_APPROVED_DM');
			foreach (array_values(LinemediaAutoOrder::getStatusesList()) as $iter) {
			?>
					<option value="<?= $iter['ID'] ?>" <? if (strcmp($iter['ID'], $STATUS_APPROVED_DM) == 0) { ?> style="background-color:#062467; color:#FFFFFF;" selected="selected"<? } ?>>
						[<?= $iter['ID'] ?>] <?= $iter['NAME'] ?>
					</option>
				<?php 
            }?>

		</select>
	</td>
</tr>

<tr>
	<td valign="top" width="50%">
        <span id="STATUS_SUPPLIER_REJECTION_HINT"></span>
        <script>BX.hint_replace(BX('STATUS_SUPPLIER_REJECTION_HINT'), '<?= GetMessage('STATUS_SUPPLIER_REJECTION_HINT') ?>');</script>
		<label for="STATUS_SUPPLIER_REJECTION">
			<?= GetMessage('STATUS_SUPPLIER_REJECTION') ?> :
		</label>
		<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
	</td>
	<td width="50%">

		<select id="STATUS_SUPPLIER_REJECTION" name="STATUS_SUPPLIER_REJECTION">

			<?
            $STATUS_SUPPLIER_REJECTION = (string) COption::GetOptionString($sModuleId, 'STATUS_SUPPLIER_REJECTION');
		    foreach (array_values(LinemediaAutoOrder::getStatusesList()) as $iter) {
			?>
					<option value="<?= $iter['ID'] ?>" <? if (strcmp($iter['ID'], $STATUS_SUPPLIER_REJECTION) == 0) { ?> style="background-color:#062467; color:#FFFFFF;" selected="selected"<? } ?>>
						[<?= $iter['ID'] ?>] <?= $iter['NAME'] ?>
					</option>
				<?php
			}?>

		</select>
	</td>
</tr>

<tr>
	<td valign="top" width="50%">
        <span id="STATUS_SHIPMENT_REJECTION_HINT"></span>
        <script>BX.hint_replace(BX('STATUS_SHIPMENT_REJECTION_HINT'), '<?= GetMessage('STATUS_SHIPMENT_REJECTION_HINT') ?>');</script>
		<label for="STATUS_SHIPMENT_REJECTION">
			<?= GetMessage('STATUS_SHIPMENT_REJECTION') ?> :
		</label>
		<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
	</td>
	<td width="50%">

		<select id="STATUS_SHIPMENT_REJECTION" name="STATUS_SHIPMENT_REJECTION">

			<?
            $STATUS_SHIPMENT_REJECTION = (string) COption::GetOptionString($sModuleId, 'STATUS_SHIPMENT_REJECTION');
			foreach (array_values(LinemediaAutoOrder::getStatusesList()) as $iter) {
			?>
					<option value="<?= $iter['ID'] ?>" <? if (strcmp($iter['ID'], $STATUS_SHIPMENT_REJECTION) == 0) { ?> style="background-color:#062467; color:#FFFFFF;" selected="selected"<? } ?>>
						[<?= $iter['ID'] ?>] <?= $iter['NAME'] ?>
					</option>
				<?php
			}?>

		</select>
	</td>
</tr>

<tr>
	<td valign="top" width="50%">
        <span id="STATUS_GOODS_RETURN_APPROVED_HINT"></span>
        <script>BX.hint_replace(BX('STATUS_GOODS_RETURN_APPROVED_HINT'), '<?= GetMessage('STATUS_GOODS_RETURN_APPROVED_HINT') ?>');</script>
		<label for="STATUS_GOODS_RETURN_APPROVED">
			<?= GetMessage('STATUS_GOODS_RETURN_APPROVED') ?> :
		</label>
		<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
	</td>
	<td width="50%">

		<select id="STATUS_COMMODITY_RETURN" name="STATUS_COMMODITY_RETURN">

			<?
			$STATUS_COMMODITY_RETURN = (string) COption::GetOptionString($sModuleId, 'STATUS_COMMODITY_RETURN');
			foreach (array_values(LinemediaAutoOrder::getStatusesList()) as $iter) {
			?>
					<option value="<?= $iter['ID'] ?>" <? if (strcmp($iter['ID'], $STATUS_COMMODITY_RETURN) == 0) { ?> style="background-color:#062467; color:#FFFFFF;" selected="selected"<? } ?>>
						[<?= $iter['ID'] ?>] <?= $iter['NAME'] ?>
					</option>
				<?php
			}?>

		</select>
	</td>
</tr>

<?/*
<tr>
	<td valign="top" width="50%">
		<label for="STATUS_COMPLETE">
			<?= GetMessage('STATUS_COMPLETE') ?> :
		</label>
		<br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
	</td>
	<td width="50%">

		<select id="STATUS_DEAL_CLOSED_BY_COMMODITY" name="STATUS_DEAL_CLOSED_BY_COMMODITY">

			<?
            $STATUS_DEAL_CLOSED_BY_COMMODITY = (string) COption::GetOptionString($sModuleId, 'STATUS_DEAL_CLOSED_BY_COMMODITY');
		    foreach (array_values(LinemediaAutoOrder::getStatusesList()) as $iter) {
			?>
					<option value="<?= $iter['ID'] ?>" <? if (strcmp($iter['ID'], $STATUS_DEAL_CLOSED_BY_COMMODITY) == 0) { ?> style="background-color:#062467; color:#FFFFFF;" selected="selected"<? } ?>>
						[<?= $iter['ID'] ?>] <?= $iter['NAME'] ?>
					</option>
				<?php
			}?>

		</select>
	</td>
</tr>
*/?>

<? if($persons) { ?>
    <tr>
        <td width="50%" valign="top">
            <label for="LM_AUTO_NO_TRANSACTION_PERSON">
                <?= GetMessage('LM_AUTO_NO_TRANSACTION_PERSON') ?>:
            </label>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
        </td>
        <td valign="top">
            <select name="LM_AUTO_NO_TRANSACTION_PERSON[]" multiple="multiple">
                <? foreach ($persons as $person) {  ?>
                    <option value="<?= $person['ID'] ?>" <?= ($person['SELECTED'] ? 'selected' : '') ?>><?= $person['NAME'] ?></option>
                <? } ?>
            </select>
        </td>
    </tr>
<? } ?>

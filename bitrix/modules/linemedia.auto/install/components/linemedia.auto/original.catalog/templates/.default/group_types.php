<div class="lm-auto-catalog-original vin">
	<b><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?></b>
	<form action="<?=$arParams['SEF_FOLDER']?>vin/" method="get" id="lm-auto-vin-frm">
		<input type="text" name="VIN" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<table class="lm-auto-catalog-original group_types">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_GROUP_TYPE')?></th>
		</tr>
	</thead>
	<tbody>
	    <?foreach($arResult['GROUP_TYPES'] AS $group_type){?>
	    <tr  onclick="javascript:document.location.href='<?=$arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa']) ?>/<?=intval($arResult['MODEL']['ID_mod'])?>/<?=$group_type['ID_typ']?>/'">
	        <td>
	        	<a href="<?=$arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa']) ?>/<?=intval($arResult['MODEL']['ID_mod'])?>/<?=$group_type['ID_typ']?>/"><?=htmlspecialchars($group_type['NameTyp'])?></a>	        
	        </td>
	    </tr>
	    <?}?>
	</tbody>
</table>
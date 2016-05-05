<div class="lm-auto-catalog-original vin">
	<b><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?></b>
	<form action="<?=$arParams['SEF_FOLDER']?>vin/" method="get" id="lm-auto-vin-frm">
		<input type="text" name="VIN" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<table class="lm-auto-catalog-original brands">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_BRAND')?></th>
		</tr>
	</thead>
	<tbody>
	    <?foreach($arResult['BRANDS'] AS $brand){?>
	    <tr>
	        <td>
	        	<a href="<?=$arParams['SEF_FOLDER'] . intval($brand['ID_mfa']) ?>/"><?=htmlspecialchars($brand['Name'])?></a>	        
	        </td>
	    </tr>
	    <?}?>
	</tbody>
</table>
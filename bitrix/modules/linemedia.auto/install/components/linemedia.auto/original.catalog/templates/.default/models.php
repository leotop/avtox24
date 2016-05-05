<div class="lm-auto-catalog-original vin">
	<b><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?></b>
	<form action="<?=$arParams['SEF_FOLDER']?>vin/" method="get" id="lm-auto-vin-frm">
		<input type="text" name="VIN" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<table class="lm-auto-catalog-original models">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_IMAGE')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_MODEL')?></th>
			<th><?=GetMessage('LM_AUTO_ORIG_YEARS')?></th>
			<th><?=GetMessage('LM_AUTO_ORIG_TYPE')?></th>
			
		</tr>
	</thead>
	<tbody>
	    <?foreach($arResult['MODELS'] AS $model){?>
	    <tr onclick="javascript:document.location.href='<?=$arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa'])?>/<?=intval($model['ID_mod'])?>/'">
	        <td class="car_img">
	        	<img src="<?=htmlspecialchars($model['Image'])?>" alt="<?=htmlspecialchars($model['Name'])?>" />
	        </td>
            
            <td>
	        	<a href="<?=$arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa'])?>/<?=intval($model['ID_mod'])?>/"><?=htmlspecialchars($model['Name'])?></a>	        
	        </td>
	        <td>
	        	<?=$model['DateStart']?> - <?=$model['DateEnd']?>
	        </td>
	        <td>
	        	<?=$model['Type']?>
	        </td>
	        
	        
	    </tr>
	    <?}?>
	</tbody>
</table>
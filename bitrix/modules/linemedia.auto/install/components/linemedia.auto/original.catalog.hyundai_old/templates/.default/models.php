<?
include(dirname(__FILE__) . '/vin_frm.php');
include(dirname(__FILE__) . '/quicksearch.php');
?>
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
	    <tr onclick="javascript:document.location.href='<?=$arParams['SEF_FOLDER'] . intval($model['ID_mod'])?>/'">
	        <td class="car_img">
	        	<img src="<?=htmlspecialchars($model['Image'])?>" alt="<?=htmlspecialchars($model['Name'])?>" />
	        </td>
            
            <td>
	        	<a href="<?=$arParams['SEF_FOLDER'] . intval($model['ID_mod'])?>/"><?=htmlspecialchars($model['Name'])?></a>	        
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
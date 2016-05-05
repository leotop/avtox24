<div class="lm-auto-catalog-original vin">
	<b><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?></b>
	<form action="<?=$arParams['SEF_FOLDER']?>vin/" method="get" id="lm-auto-vin-frm">
		<input type="text" name="VIN" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<table class="lm-auto-catalog-original groups">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_GROUP')?></th>
		</tr>
	</thead>
	<tbody>
    
        <?
        /*
        * ?? ??? ??? ?? - ????? ??? ?? ? ?? ???
        */
        $url = $arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa']) . '/' . intval($arResult['MODEL']['ID_mod']) . '/' . intval($arResult['GROUP_TYPE']['ID_typ']) . '/' . $arResult['GROUPS'][0]['ID_grp'] . '/';
        $groups_count = count($arResult['GROUPS']);        
        if($groups_count == 1){                
                LocalRedirect($url);
            }
        ?>
        
	    <?foreach($arResult['GROUPS'] AS $group){
            $url = $arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa']) . '/' . intval($arResult['MODEL']['ID_mod']) . '/' . intval($arResult['GROUP_TYPE']['ID_typ']) . '/' . $group['ID_grp'] . '/';	       
        ?>
	    <tr  onclick="javascript:document.location.href='<?=$url?>'">
	        <td>
	        	<a href="<?=$url?>"><?=htmlspecialchars($group['NameGrp'])?></a>	        
	        </td>
	    </tr>
	    <?}?>
	</tbody>
</table> 
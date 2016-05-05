<div class="lm-auto-catalog-original vin">
	<b><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?></b>
	<form action="<?=$arParams['SEF_FOLDER']?>vin/" method="get" id="lm-auto-vin-frm">
		<input type="text" name="VIN" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<div class="lm-auto-catalog-original group_sections">
    <?foreach($arResult['GROUP_SECTIONS'] AS $group_section){?>
    <div class="group_section">
        <div class="group_sections_img">
            <a href="<?=$arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa']) ?>/<?=intval($arResult['MODEL']['ID_mod'])?>/<?=intval($arResult['GROUP_TYPE']['ID_typ'])?>/<?=intval($arResult['GROUP']['ID_grp'])?>/<?=$group_section['ID_sec']?>/">
                <img src="<?=htmlspecialchars($group_section['Image'])?>" alt="<?=htmlspecialchars($group_section['Name'])?> <?=htmlspecialchars($arResult['BRAND']['Name'])?> <?=htmlspecialchars($arResult['MODEL']['Name'])?>" />
            </a>
        </div>
                
        <div class="group_sections_title"><a href="<?=$arParams['SEF_FOLDER'] . intval($arResult['BRAND']['ID_mfa']) ?>/<?=intval($arResult['MODEL']['ID_mod'])?>/<?=intval($arResult['GROUP_TYPE']['ID_typ'])?>/<?=intval($arResult['GROUP']['ID_grp'])?>/<?=$group_section['ID_sec']?>/"><?=htmlspecialchars($group_section['Name'])?></a></div>
        
        <div class="group_sections_desc"><?=nl2br(htmlspecialchars(str_replace('</br>',"\n",$group_section['Description'])))?></div>
    </div>
    <?}?>

</div>

<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="section">
<div class="title"><?=GetMessage("SOA_TEMPL_SUM_TITLE")?></div>

<?php 

//$basketItem = current($arResult["BASKET_ITEMS"]);
//$paramString = fillProperties($basketItem['PROPS'], $arParams);
//$decreaseCol = !(bool) $paramString ? 1 : 0;

?>

<table class="sale_data-table summary silver-table ">
<thead>
	<tr>
        <?= !in_array('IMAGE', $arParams['COLUMNS_LIST']) ? '<th>'.GetMessage("SOA_TEMPL_SUM_PICTURE").'</th>' : '';?>
        <?= !in_array('NAME', $arParams['COLUMNS_LIST']) ? '<th>'.GetMessage("SOA_TEMPL_SUM_NAME").'</th>' : '';?>
        <?= !in_array('PROPS', $arParams['COLUMNS_LIST']) && (bool) $paramString ? '<th>'.GetMessage("SOA_TEMPL_SUM_PROPS").'</th>' : '';?>
        <?= !in_array('DISCOUNT', $arParams['COLUMNS_LIST']) ? '<th>'.GetMessage("SOA_TEMPL_SUM_DISCOUNT").'</th>' : '';?>
        <?= !in_array('WEIGHT', $arParams['COLUMNS_LIST']) ? '<th>'.GetMessage("SOA_TEMPL_SUM_WEIGHT").'</th>' : '';?>
        <?= !in_array('QUANTITY', $arParams['COLUMNS_LIST']) ? '<th>'.GetMessage("SOA_TEMPL_SUM_QUANTITY").'</th>' : '';?>
        <?= !in_array('PRICE', $arParams['COLUMNS_LIST']) ? '<th>'.GetMessage("SOA_TEMPL_SUM_PRICE").'</th>' : '';?>
	</tr>
</thead>
<tbody>
	<?
	foreach ($arResult["BASKET_ITEMS"] as $arBasketItems) {

		$paramString = fillProperties($arBasketItems['PROPS'], $arParams);
		$decreaseCol = !(bool) $paramString ? 1 : 0;
		?>
		<tr>

			<?= !in_array('IMAGE', $arParams['COLUMNS_LIST']) ? '<td>'.fillPicture($arBasketItems, $arParams).'</td>' : ''?>
			<?= !in_array('NAME', $arParams['COLUMNS_LIST']) ? '<td><div class="col-xs-6">'.$arBasketItems["NAME"].'</div><div class="col-xs-6">'.$paramString.'</div></td>' : ''?>
		<!--
			<?= !in_array('PROPS', $arParams['COLUMNS_LIST']) && (bool) $paramString ? '<td>'.$paramString.'</td>' : ''?>
		-->
			<?= !in_array('DISCOUNT', $arParams['COLUMNS_LIST']) ? '<td>'.$arBasketItems["DISCOUNT_PRICE_PERCENT_FORMATED"].'</td>' : ''?>
			<?= !in_array('WEIGHT', $arParams['COLUMNS_LIST']) ? '<td>'.$arBasketItems["WEIGHT_FORMATED"].'</td>' : ''?>
			<?= !in_array('QUANTITY', $arParams['COLUMNS_LIST']) ? '<td>'.$arBasketItems["QUANTITY"].'</td>' : ''?>
            <?= !in_array('PRICE', $arParams['COLUMNS_LIST']) ? '<td class="price">'.LinemediaAutoPrice::userPrice($arBasketItems["PRICE_FORMATED"]).'</td>' : ''?>
		</tr>
    <? } ?>
	</tbody>

	<!-- frontier between top and bottom -->


	<tfoot style="width: auto">
	<? if (!in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST'])) {?>
	<tr class="">
		<td colspan="<?= 6 - count($arParams['COLUMNS_LIST']) - $decreaseCol ?>" class="itog"><?=GetMessage("SOA_TEMPL_SUM_WEIGHT_SUM")?></td>
		<td class="price"><?=$arResult["ORDER_WEIGHT_FORMATED"]?></td>
	</tr>
	<?}?>
	<tr>
		<td colspan="<?= 6 - $decreaseCol - (in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST']) ? count($arParams['COLUMNS_LIST']) - 1 : count($arParams['COLUMNS_LIST']))?>" class="itog"><?=GetMessage("SOA_TEMPL_SUM_SUMMARY")?></td>
		<td class="price"><?=LinemediaAutoPrice::userPrice($arResult["ORDER_PRICE_FORMATED"])?></td>
	</tr>
	<? if (doubleval($arResult["DISCOUNT_PRICE"]) > 0) { ?>
		<tr>
			<td colspan="<?= 6 - $decreaseCol - (in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST']) ? count($arParams['COLUMNS_LIST']) - 1 : count($arParams['COLUMNS_LIST'])) ?>" class="itog"><?=GetMessage("SOA_TEMPL_SUM_DISCOUNT")?><?if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0):?> (<?echo $arResult["DISCOUNT_PERCENT_FORMATED"];?>)<?endif;?>:</td>
			<td class="price"><?echo LinemediaAutoPrice::userPrice($arResult["DISCOUNT_PRICE_FORMATED"])?></td>
		</tr>
		<?
	}
	if (!empty($arResult["arTaxList"])) {
		foreach ($arResult["arTaxList"] as $val) {
			?>
			<tr>
				<td colspan="<?= 6 - $decreaseCol - (in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST']) ? count($arParams['COLUMNS_LIST']) - 1 : count($arParams['COLUMNS_LIST']))?>" class="itog"><?=$val["NAME"]?> <?=$val["VALUE_FORMATED"]?>:</td>
				<td class="price"><?=LinemediaAutoPrice::userPrice($val["VALUE_MONEY_FORMATED"])?></td>
			</tr>
			<?
		}
	}
	if (doubleval($arResult["DELIVERY_PRICE"]) > 0)
	{
		?>
		<tr>
			<td colspan="<?= 6 - $decreaseCol - (in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST']) ? count($arParams['COLUMNS_LIST']) - 1 : count($arParams['COLUMNS_LIST']))?>" class="itog"><?=GetMessage("SOA_TEMPL_SUM_DELIVERY")?></td>
			<td class="price"><?=LinemediaAutoPrice::userPrice($arResult["DELIVERY_PRICE_FORMATED"])?></td>
		</tr>
		<?
	}
	if (strlen($arResult["PAYED_FROM_ACCOUNT_FORMATED"]) > 0)
	{
		?>
		<tr>
			<td colspan="<?= 6 - $decreaseCol - (in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST']) ? count($arParams['COLUMNS_LIST']) - 1 : count($arParams['COLUMNS_LIST']))?>" class="itog"><?=GetMessage("SOA_TEMPL_SUM_PAYED")?></td>
			<td class="price"><?=LinemediaAutoPrice::userPrice($arResult["PAYED_FROM_ACCOUNT_FORMATED"])?></td>
		</tr>
		<?
	}
	?>
	<tr class="last">
		<td colspan="<?= 6 - $decreaseCol - (in_array('OVERALL_WEIGHT', $arParams['COLUMNS_LIST']) ? count($arParams['COLUMNS_LIST']) - 1 : count($arParams['COLUMNS_LIST']))?>" class="itog"><?=GetMessage("SOA_TEMPL_SUM_IT")?></td>
		<td class="price"><?=LinemediaAutoPrice::userPrice($arResult["ORDER_TOTAL_PRICE_FORMATED"])?></td>
	</tr>
</tfoot>
</table>

<br /><br />
<div class="title"><?=GetMessage("SOA_TEMPL_SUM_ADIT_INFO")?></div>

<table class="sale_order_table">
	<tr>
		<td class="order_comment">
			<div><?=GetMessage("SOA_TEMPL_SUM_COMMENTS")?></div>
			<textarea name="ORDER_DESCRIPTION" id="ORDER_DESCRIPTION"><?=$arResult["USER_VALS"]["ORDER_DESCRIPTION"]?></textarea>
		</td>
	</tr>
</table>
</div>

<?php

function fillProperties($props, $param) {

   $resultString = ''; 
   
   foreach($props as $val) {

       if (in_array($val['CODE'], (array) $param['HIDE_PROPERTIES']) || $val['CODE'] == 'retail_chain') {
           continue;
       }

       if (strtoupper($val["CODE"]) == 'DELIVERY_TIME') {
            $time_deliv = round(($val["VALUE"]/24), 0, PHP_ROUND_HALF_UP);
            $resultString .= $val["NAME"]." ~ ".$time_deliv." ".GetMessage('LM_AUTO_DAYS').".<br />";
            continue;
       }

	   if(in_array($val['CODE'], $param['EDITABLE_PROPERTIES'])) {
		   ob_start();
		   ?>
		   <div class="form-group">
			   <label for="<?=$val['BASKET_ID']?>_<?=$val['CODE']?>"><?=$val["NAME"] ?>:</label>
			   <input type="text" id="<?=$val['BASKET_ID']?>_<?=$val['CODE']?>" name="<?=$val['BASKET_ID']?>_<?=$val['CODE']?>" value="<?=$val["VALUE"] ?>">
		   </div>
		   <?
		   $resultString .= ob_get_clean();
		   continue;
	   }

	   $resultString .= $val["NAME"].": ".$val["VALUE"]."<br />";
   }

   return $resultString;
}

function fillPicture($basketItem, $param) {

   if (count($basketItem["DETAIL_PICTURE"]) > 0) {
         return CFile::ShowImage($basketItem["DETAIL_PICTURE"], $param["DISPLAY_IMG_WIDTH"], $param["DISPLAY_IMG_HEIGHT"], "border=0", "", false);
   }
   elseif (count($basketItem["PREVIEW_PICTURE"]) > 0) {
         return CFile::ShowImage($basketItem["PREVIEW_PICTURE"], $param["DISPLAY_IMG_WIDTH"], $param["DISPLAY_IMG_HEIGHT"], "border=0", "", false);
   }
}

?>


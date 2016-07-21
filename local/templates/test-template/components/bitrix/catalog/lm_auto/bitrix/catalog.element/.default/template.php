<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="catalog-element">
	<div class="photo-element">
	<? $image_preview_resize = CFile::ResizeImageGet($arResult["PREVIEW_PICTURE"], array("width" => 300, "height" => 300));?>
		<? $image_detail_resize = CFile::ResizeImageGet($arResult["DETAIL_PICTURE"], array("width" => 300, "height" => 300));?>
				<div class="main_photo">
					<?if(is_array($arResult["PREVIEW_PICTURE"]) && is_array($arResult["DETAIL_PICTURE"])):?>
						<a rel="catalog-detail-images" class="catalog-detail-images" href="<?=$arResult['DETAIL_PICTURE']['SRC']?>"><img border="0" src="<?=$image_detail_resize['src']?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" /></a>
						
                        <?/*?>
                        <img border="0" src="<?=$image_preview_resize['src']?>" width="<?=$arResult["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arResult["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" id="image_<?=$arResult["PREVIEW_PICTURE"]["ID"]?>" style="display:block;cursor:pointer;cursor: hand;" OnClick="document.getElementById('image_<?=$arResult["PREVIEW_PICTURE"]["ID"]?>').style.display='none';document.getElementById('image_<?=$arResult["DETAIL_PICTURE"]["ID"]?>').style.display='block'" />
						<img border="0" src="<?=$image_detail_resize['src']?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" id="image_<?=$arResult["DETAIL_PICTURE"]["ID"]?>" style="display:none;cursor:pointer; cursor: hand;" OnClick="document.getElementById('image_<?=$arResult["DETAIL_PICTURE"]["ID"]?>').style.display='none';document.getElementById('image_<?=$arResult["PREVIEW_PICTURE"]["ID"]?>').style.display='block'" />
                        <?*/?>
					
					<?elseif(is_array($arResult["DETAIL_PICTURE"])):?>
						<a rel="catalog-detail-images" class="catalog-detail-images" href="<?=$arResult['DETAIL_PICTURE']['SRC']?>"><img border="0" src="<?=$image_detail_resize['src']?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" /></a>
					<?elseif(is_array($arResult["PREVIEW_PICTURE"])):?>
						<a rel="catalog-detail-images" class="catalog-detail-images" href="<?=$arResult['PREVIEW_PICTURE']['SRC']?>"><img border="0" src="<?=$image_preview_resize['src']?>" width="<?=$arResult["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arResult["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" /></a>
					
					<?endif?>
				</div>
				<div class="gallery">
					<?if(count($arResult["MORE_PHOTO"])>0):?>
						<? // additional photos
						$LINE_ELEMENT_COUNT = 2; // number of elements in a row
						if(count($arResult["MORE_PHOTO"])>0):?>
							<?foreach($arResult["MORE_PHOTO"] as $PHOTO):?>
								<? $gallery_img = CFile::ResizeImageGet($PHOTO, array("width" => 70, "height" => 70));?>
								<a rel="catalog-detail-images" class="catalog-detail-images" href="<?=$PHOTO['SRC']?>"><img border="0" src="<?=$gallery_img['src']?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" /></a>
							<?endforeach?>
						<?endif?>
					<?endif;?>
				</div>
	</div>

	<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
				
		<?if(is_array($arResult["PREVIEW_PICTURE"]) || is_array($arResult["DETAIL_PICTURE"])):?>
			<td width="0%" valign="top">

			</td>
		<?endif;?>
			<td width="100%" valign="top" class="description">
				
				
				<? if($arParams['USE_PRICE_COUNT'] && $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] == ''){
				/*
				* Если не стоит настройка "Выводить диапозон цен" выводим цены из инфоблока
				*/?>
    	
			    	<?if(is_array($arResult["OFFERS"]) && !empty($arResult["OFFERS"])):?>
			    		<?foreach($arResult["OFFERS"] as $arOffer):?>
			    			<?foreach($arParams["OFFERS_FIELD_CODE"] as $field_code):?>
			    				<small><?echo GetMessage("IBLOCK_FIELD_".$field_code)?>:&nbsp;<?
			    						echo $arOffer[$field_code];?></small><br />
			    			<?endforeach;?>
			    			<?foreach($arOffer["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
			    				<small><?=$arProperty["NAME"]?>:&nbsp;<?
			    					if(is_array($arProperty["DISPLAY_VALUE"]))
			    						echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
			    					else
			    						echo $arProperty["DISPLAY_VALUE"];?></small><br />
			    			<?endforeach?>
			    			<?foreach($arOffer["PRICES"] as $code=>$arPrice):?>
			    				<?if($arPrice["CAN_ACCESS"]):?>
			    					<p><?=$arResult["CAT_PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
			    					<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
			    						<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
			    					<?else:?>
			    						<span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
			    					<?endif?>
			    					</p>
			    				<?endif;?>
			    			<?endforeach;?>
			    			<p>
			    			<?if($arParams["DISPLAY_COMPARE"]):?>
			    				<noindex>
			    				<a href="<?echo $arOffer["COMPARE_URL"]?>" rel="nofollow"><?echo GetMessage("CT_BCE_CATALOG_COMPARE")?></a>&nbsp;
			    				</noindex>
			    			<?endif?>
			    			<?if($arOffer["CAN_BUY"]):?>
			    				<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
			    					<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
			    					<table border="0" cellspacing="0" cellpadding="2">
			    						<tr valign="top">
			    							<td><?echo GetMessage("CT_BCE_QUANTITY")?>:</td>
			    							<td>
			    								<input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
			    							</td>
			    						</tr>
			    					</table>
			    					<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
			    					<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arOffer["ID"]?>">
			    					<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
			    					<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CT_BCE_CATALOG_ADD")?>">
			    					</form>
			    				<?else:?>
			    					<noindex>
			    					<a class="btn btn-warning" href="<?echo $arOffer["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
			    					&nbsp;<a class="btn btn-mini" href="<?echo $arOffer["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CT_BCE_CATALOG_ADD")?></a>
			    					</noindex>
			    				<?endif;?>
			    			<?elseif(count($arResult["CAT_PRICES"]) > 0):?>
			    				<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
			    				<?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
			    					"NOTIFY_ID" => $arOffer['ID'],
			    					"NOTIFY_URL" => htmlspecialcharsback($arOffer["SUBSCRIBE_URL"]),
			    					"NOTIFY_USE_CAPTHA" => "N"
			    					),
			    					$component
			    				);?>
			    			<?endif?>
			    			</p>
			    		<?endforeach;?>
			    	<?else:?>
			    		<?foreach($arResult["PRICES"] as $code=>$arPrice):?>
			    			<?if($arPrice["CAN_ACCESS"]):?>
			    				<p><?=$arResult["CAT_PRICES"][$code]["TITLE"];?>&nbsp;
			    				<?if($arParams["PRICE_VAT_SHOW_VALUE"] && ($arPrice["VATRATE_VALUE"] > 0)):?>
			    					<?if($arParams["PRICE_VAT_INCLUDE"]):?>
			    						(<?echo GetMessage("CATALOG_PRICE_VAT")?>)
			    					<?else:?>
			    						(<?echo GetMessage("CATALOG_PRICE_NOVAT")?>)
			    					<?endif?>
			    				<?endif;?>:&nbsp;
			    				<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
			    					<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
			    					<?if($arParams["PRICE_VAT_SHOW_VALUE"]):?><br />
			    						<?=GetMessage("CATALOG_VAT")?>:&nbsp;&nbsp;<span class="catalog-vat catalog-price"><?=$arPrice["DISCOUNT_VATRATE_VALUE"] > 0 ? $arPrice["PRINT_DISCOUNT_VATRATE_VALUE"] : GetMessage("CATALOG_NO_VAT")?></span>
			    					<?endif;?>
			    				<?else:?>
			    					<span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
			    					<?if($arParams["PRICE_VAT_SHOW_VALUE"]):?><br />
			    						<?=GetMessage("CATALOG_VAT")?>:&nbsp;&nbsp;<span class="catalog-vat catalog-price"><?=$arPrice["VATRATE_VALUE"] > 0 ? $arPrice["PRINT_VATRATE_VALUE"] : GetMessage("CATALOG_NO_VAT")?></span>
			    					<?endif;?>
			    				<?endif?>
			    				</p>
			    			<?endif;?>
			    		<?endforeach;?>
			    		<?if(is_array($arResult["PRICE_MATRIX"])):?>
			    			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="data-table">
			    			<!--thead>
			    			<tr>
			    				<?if(count($arResult["PRICE_MATRIX"]["ROWS"]) >= 1 && ($arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
			    					<td><?= GetMessage("CATALOG_QUANTITY") ?></td>
			    				<?endif;?>
			    				<?foreach($arResult["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
			    					<td><?= $arType["NAME_LANG"] ?></td>
			    				<?endforeach?>
			    			</tr>
			    			</thead-->
			    			<?foreach ($arResult["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):?>
			    			<tr>
			    				<?if(count($arResult["PRICE_MATRIX"]["ROWS"]) > 1 || count($arResult["PRICE_MATRIX"]["ROWS"]) == 1 && ($arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
			    					<th nowrap>
			    						<?if(IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
			    							echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
			    						elseif(IntVal($arQuantity["QUANTITY_FROM"]) > 0)
			    							echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
			    						elseif(IntVal($arQuantity["QUANTITY_TO"]) > 0)
			    							echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
			    						?>
			    					</th>
			    				<?endif;?>
			    				<?foreach($arResult["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
			    					<td>
			    						<?if($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"])
			    							echo '<s>'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</s> <span class="catalog-price">'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
			    						else
			    							echo '<span class="catalog-price">'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
			    						?>
			    					</td>
			    				<?endforeach?>
			    			</tr>
			    			<?endforeach?>
			    			</table>
			    			<?if($arParams["PRICE_VAT_SHOW_VALUE"]):?>
			    				<?if($arParams["PRICE_VAT_INCLUDE"]):?>
			    					<small><?=GetMessage('CATALOG_VAT_INCLUDED')?></small>
			    				<?else:?>
			    					<small><?=GetMessage('CATALOG_VAT_NOT_INCLUDED')?></small>
			    				<?endif?>
			    			<?endif;?><br />
			    		<?endif?>
			    		<?if($arResult["CAN_BUY"]):?>
			    			<?if($arParams["USE_PRODUCT_QUANTITY"] || count($arResult["PRODUCT_PROPERTIES"])):?>
			    				<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
			    				<table border="0" cellspacing="0" cellpadding="2">
			    				<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
			    					<tr valign="top">
			    						<td><?echo GetMessage("CT_BCE_QUANTITY")?>:</td>
			    						<td>
			    							<input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
			    						</td>
			    					</tr>
			    				<?endif;?>
			    				<?foreach($arResult["PRODUCT_PROPERTIES"] as $pid => $product_property):?>
			    					<tr valign="top">
			    						<td><?echo $arResult["PROPERTIES"][$pid]["NAME"]?>:</td>
			    						<td>
			    						<?if(
			    							$arResult["PROPERTIES"][$pid]["PROPERTY_TYPE"] == "L"
			    							&& $arResult["PROPERTIES"][$pid]["LIST_TYPE"] == "C"
			    						):?>
			    							<?foreach($product_property["VALUES"] as $k => $v):?>
			    								<label><input type="radio" name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]" value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"checked"'?>><?echo $v?></label><br>
			    							<?endforeach;?>
			    						<?else:?>
			    							<select name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]">
			    								<?foreach($product_property["VALUES"] as $k => $v):?>
			    									<option value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"selected"'?>><?echo $v?></option>
			    								<?endforeach;?>
			    							</select>
			    						<?endif;?>
			    						</td>
			    					</tr>
			    				<?endforeach;?>
			    				</table>
			    				<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
			    				<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arResult["ID"]?>">
			    				<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
			    				<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CATALOG_ADD_TO_BASKET")?>">
			    				</form>
			    			<?else:?>
			    				<br />
			    				<noindex>
			    				<a class="btn btn-warning" href="<?echo $arResult["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
			    				</noindex>
			    			<?endif;?>
			    		<?elseif((count($arResult["PRICES"]) > 0) || is_array($arResult["PRICE_MATRIX"])):?>
			    			<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
			    			<?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
			    				"NOTIFY_ID" => $arResult['ID'],
			    				"NOTIFY_PRODUCT_ID" => $arParams['PRODUCT_ID_VARIABLE'],
			    				"NOTIFY_ACTION" => $arParams['ACTION_VARIABLE'],
			    				"NOTIFY_URL" => htmlspecialcharsback($arResult["SUBSCRIBE_URL"]),
			    				"NOTIFY_USE_CAPTHA" => "N"
			    				),
			    				$component
			    			);?>
			    		<?endif?>
			    	<?endif?>
					<hr />
				<? } ?>
				
				<!-- Описание -->
				<p><b><?=GetMessage("DESCRIPTION")?></b></p>
				<?if($arResult["DETAIL_TEXT"]):?>
					<?=$arResult["DETAIL_TEXT"]?><br />
				<?elseif($arResult["PREVIEW_TEXT"]):?>
					<?=$arResult["PREVIEW_TEXT"]?><br />
				<?endif;?>
				
				<hr />
				
				<!-- Свойства -->
				<?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
					<?=$arProperty["NAME"]?>:<b>&nbsp;<?
					if(is_array($arProperty["DISPLAY_VALUE"])):
						echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
					elseif($pid=="MANUAL"):
						?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
					else:
						echo $arProperty["DISPLAY_VALUE"];?>
					<?endif?></b><br />
				<?endforeach?>
			</td>
		</tr>
	</table>
    
    
    
    <?
    if($arParams['USE_PRICE_COUNT'] && $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] != ''){
    /*
    * Если стоит настройка "Выводить диапозон цен" выводим результаты поиска по артикулу ajax
    */
    ?>
    
    	<hr />
    	
		<div class="mob-scroll"><img src="/bitrix/templates/fast-start_blue_copy/images/scroll.png"></div>
        <div class="offers_prices" id="offers_prices">

            <div class="alert alert-info">
                <img src="<?=$this->GetFolder();?>/images/loading.gif" /> <strong><?=GetMessage("LOOK_FOR_PRICES")?></strong>
            </div>
        </div>
    
        <script type="text/javascript">
            
            $.get("/auto/search/ajax.php", { 
                q: "<?=$arResult['PROPERTIES']['ARTNUMBER']['VALUE']?>", 
                brand_title: "<?=$arResult['PROPERTIES']['MANUFACTURER']['VALUE']?>",                 
            }).done(function(data) {
              $('#offers_prices').html(data);
              //alert("Data Loaded: " + data);
            });
        
        </script>
    
    <?}?>
    
    
    
	<?if(count($arResult["LINKED_ELEMENTS"])>0):?>
		<b><?=$arResult["LINKED_ELEMENTS"][0]["IBLOCK_NAME"]?>:</b>
		<ul>
	<?foreach($arResult["LINKED_ELEMENTS"] as $arElement):?>
		<li><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></li>
	<?endforeach;?>
		</ul>
	<?endif?>
	
	<?/*if(is_array($arResult["SECTION"])):?>
		<a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
	<?endif*/?>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('.catalog-detail-images').fancybox({
		'transitionIn': 'elastic',
		'transitionOut': 'elastic',
		'speedIn': 600,
		'speedOut': 200,
		'overlayShow': true,
		'cyclic' : true,
		'padding': 20,
		'titlePosition': 'over',
		'onComplete': function() {
		$("#fancybox-title").css({ 'top': '100%', 'bottom': 'auto' });
		}
	});
});
</script>
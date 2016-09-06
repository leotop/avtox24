<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(count($arResult["ITEMS"]) > 0): ?>
	<?
	$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
	$arNotify = unserialize($notifyOption);
	?>
<h4 class="newsale"><img src="<?=SITE_TEMPLATE_PATH?>/images/news_parts_icon.png" alt="<?=GetMessage("H1")?>" /><span></span> <?=GetMessage("H1")?></h4>


<div class="listitem-carousel">
	<ul class="lsnn" id="foo_<?=ToLower($arParams["FLAG_PROPERTY_CODE"])?>">

<?foreach($arResult["ITEMS"] as $key => $arItem):
	if(is_array($arItem))
	{
		$bPicture = is_array($arItem["PREVIEW_IMG"]);
		?><li class="itembg R2D2" itemscope itemtype = "http://schema.org/Product">
		
			<h4><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="item_title" title="<?=$arItem["NAME"]?>">
				<span itemprop = "name"><?=$arItem["NAME"]?> <span class="white_shadow"></span></span>
			</a></h4>
			
			<?if($arParams["DISPLAY_COMPARE"]):?>
			<noindex>
				<?if(is_array($arItem["OFFERS"]) && !empty($arItem["OFFERS"])):?>
					<span class="checkbox">
						<a href="javascript:void(0)" onclick="return showOfferPopup(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', <?=CUtil::PhpToJsObject($arItem["SKU_ELEMENTS"])?>, <?=CUtil::PhpToJsObject($arItem["SKU_PROPERTIES"])?>, <?=CUtil::PhpToJsObject($arResult["POPUP_MESS"])?>, 'compare');">
							<input type="checkbox" class="addtoCompareCheckbox"/><span class="checkbox_text"><?=GetMessage("CATALOG_COMPARE")?></span>
						</a>
					</span>
				<?else:?>
					<span class="checkbox">
						<a href="<?echo $arItem["COMPARE_URL"]?>" rel="nofollow" onclick="return addToCompare(this, 'list', '<?=GetMessage("CATALOG_IN_COMPARE")?>', '<?=$arItem["DELETE_COMPARE_URL"]?>');" id="catalog_add2compare_link_<?=$arItem['ID']?>">
							<input type="checkbox" class="addtoCompareCheckbox"/><span class="checkbox_text"><?=GetMessage("CATALOG_COMPARE")?></span>
						</a>
					</span>
				<?endif?>
			</noindex>
			<?endif?>
			<?if ($bPicture):?>
				<table style="width:200px !important;height:150px !important;"><tr><td class="tac vam" style="width: 200px !important;height:150px !important;"><a class="link" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img class="item_img" itemprop="image" src="<?=$arItem["PREVIEW_IMG"]["SRC"]?>" width="<?=$arItem["PREVIEW_IMG"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_IMG"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" /></a></td></tr></table>
			<?else:?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><div class="no-photo-div-big" style="height:130px; width:130px;"></div></a>
			<?endif?>
			
			
			<div class="buy">
				<div class="price" itemprop = "offers" itemscope itemtype = "http://schema.org/Offer"><?
				if(is_array($arItem["OFFERS"]) && !empty($arItem["OFFERS"]))   //if product has offers
				{
					if (count($arItem["OFFERS"]) > 1)
					{
					?>
						<span itemprop = "price" class="item_price" style="color:#000">
					<?
						echo GetMessage("CR_PRICE_OT");
						echo $arItem["PRINT_MIN_OFFER_PRICE"];
					?>
						</span>
					<?
					}
					else
					{
						foreach($arItem["OFFERS"] as $arOffer):?>
							<?foreach($arOffer["PRICES"] as $code=>$arPrice):?>
								<?if($arPrice["CAN_ACCESS"]):?>
										<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
											<span class="old-price"><?=$arPrice["PRINT_VALUE"]?></span>
											<span itemprop = "discount-price" class="item_price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br>
											<?else:?>
											<span itemprop = "price" class="item_price price"><?=$arPrice["PRINT_VALUE"]?></span>
										<?endif?>
								<?endif;?>
							<?endforeach;?>
						<?endforeach;
					}
				}
				else // if product doesn't have offers
				{
					$numPrices = count($arParams["PRICE_CODE"]);
					foreach($arItem["PRICES"] as $code=>$arPrice):
						if($arPrice["CAN_ACCESS"]):?>
							<?if ($numPrices>1):?><p style="padding: 0; margin-bottom: 5px;"><?=$arResult["PRICES"][$code]["TITLE"];?>:</p><?endif?>
							<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
								<span itemprop = "price" class="old-price"><?=$arPrice["PRINT_VALUE"]?></span>
								<span itemprop = "price" class="item_price discount-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br>
							<?else:?>
								<span itemprop = "price" class="item_price price"><?=$arPrice["PRINT_VALUE"]?></span>
							<?endif;
						endif;
					endforeach;
				}
				?>
				</div><br>
				
				<!-- Кнопка купить (подробно) -->
				<noindex><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="btn btn-warning addtoCart" id="catalog_add2cart_offer_link_<?=$arItem['ID']?>"><?echo GetMessage("CATALOG_ADD")?></a></noindex>
			</div>
			<div class="tlistitem_shadow"></div>
		</li>
<?
	}
endforeach;
?>
	</ul>
	<div class="clearfix"></div>
	<a id="prev<?=ToLower($arParams["FLAG_PROPERTY_CODE"])?>" class="prev" href="#">&lt;</a>
	<a id="next<?=ToLower($arParams["FLAG_PROPERTY_CODE"])?>" class="next" href="#">&gt;</a>
	<div id="pager<?=ToLower($arParams["FLAG_PROPERTY_CODE"])?>" class="pager"></div>
</div>
<?elseif($USER->IsAdmin()):?>
<?endif;?>


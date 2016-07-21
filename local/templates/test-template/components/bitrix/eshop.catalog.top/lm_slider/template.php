<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="row">
	<div class="span12">
		<?if(count($arResult["ITEMS"]) > 0): ?>
			<?$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
			$arNotify = unserialize($notifyOption);?>

			<!-- Заголовок списка -->
			<h5 class="title"><?=GetMessage("CR_TITLE_".$arParams["FLAG_PROPERTY_CODE"])?></h5>

			<!-- Слайдер -->
			<div id="myCarousel" class="carousel slide">
				<!-- Carousel items -->
				<div class="carousel-inner">
				
					<?foreach($arResult["ITEMS"] as $key => $arItem):
						if(is_array($arItem)){
							$bPicture = is_array($arItem["PREVIEW_IMG"]);?>
							
							<div class="item <?if($key==0){?>active<?}?>">
							
								<table class="item_t" cellspacing="20"> 
									<tr>
										<td>
											<!-- Изображение -->
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
										</td>
										<td>
											<!-- Название -->
											<h4><a class="title_item" href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="<?=$arItem["NAME"]?>"><?=$arItem["NAME"]?></a></h4>	
											<!-- Описание -->
											<p class="description"><?=substr(strip_tags($arItem["PREVIEW_TEXT"]), 0, 200); ?></p>
												
											<!-- Покупка -->
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
																			<span class="old-price"><?=$arPrice["PRINT_VALUE"]?></span><br>
																			<span itemprop = "discount-price" class="item_price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br>
																			<?else:?>
																			<span class="old-price"></span><br>
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
																<span itemprop = "price" class="old-price"><?=$arPrice["PRINT_VALUE"]?></span><br>
																<span itemprop = "price" class="item_price discount-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br>
															<?else:?>
																<span itemprop = "price" class="old-price"></span><br>
																<span itemprop = "price" class="item_price price"><?=$arPrice["PRINT_VALUE"]?></span>
															<?endif;
														endif;
													endforeach;
												}
												?>
												</div>
												
												<!-- Кнопка купить (подробно) -->
												<noindex><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="btn btn-warning addtoCart" id="catalog_add2cart_offer_link_<?=$arItem['ID']?>"><?echo GetMessage("CATALOG_ADD")?></a></noindex>
											</div>
											<div class="tlistitem_shadow"></div>

											<?
		                                    /*
		                                    * Окно Экспресс просмотра
		                                    */
		                                     
		                                    $url = $arItem["DETAIL_PAGE_URL"]. '?ajax=1';
		                                    $href = $APPLICATION->GetPopupLink( array(
		                                       'URL' => $url, 
		                                       'PARAMS' => array( 
		                                          'width' => 780, 
		                                          'height' => 570, 
		                                          'resizable' => true, 
		                                          'min_width' => 780, 
		                                          'min_height' => 570
		                                       )) 
		                                    );
		                                    ?> 
											
											<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" onclick="<?=$href?>;return false;" class="btn btn-mini btn-info express_show">Экспресс просмотр</a>
											
										</td>
									</tr>
								</table>
								
							</div>
					
					<?}endforeach;?>
			
				</div>
				
				<!-- Carousel nav -->
				<ol class="carousel-indicators">
					<?foreach($arResult["ITEMS"] as $key => $arItem):?>
						<li data-target="#myCarousel" data-slide-to="<?=$key?>" <?if($key==0){?>class="active"<?}?>></li>
					<?endforeach;?>
				</ol>
				
				<!-- Carousel nav -->
				<a class="carousel-control left" href="#myCarousel" data-slide="prev"><img src="<?=SITE_TEMPLATE_PATH?>/images/carousel-control-left.png" /></a>
				<a class="carousel-control right" href="#myCarousel" data-slide="next"><img src="<?=SITE_TEMPLATE_PATH?>/images/carousel-control-right.png" /></a>
			</div>

		<?endif;?>
	</div>
</div>

<script type="text/javascript">
	$('.carousel').carousel({
	  interval: 2000
	})
</script>
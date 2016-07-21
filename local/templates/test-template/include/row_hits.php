<!-- Хиты продаж -->
<div class="row-fluid hits_top"></div>
<div class="row-fluid hits">
	<div class="row-fluid hits_bg_left">
		<div class="row-fluid hits_bg_right">
			<div class="container">
				<div class="row">
					<div class="span12">
						 <?$APPLICATION->IncludeComponent("bitrix:eshop.catalog.top", "lm", array(
							"IBLOCK_TYPE_ID" => "catalog",
							"IBLOCK_ID" => "3",
							"ELEMENT_SORT_FIELD" => "RAND",
							"ELEMENT_SORT_ORDER" => "asc",
							"ELEMENT_COUNT" => "4",
							"FLAG_PROPERTY_CODE" => "SALELEADER",
							"OFFERS_LIMIT" => "4",
							"OFFERS_FIELD_CODE" => array(
								0 => "NAME",
								1 => "",
							),
							"OFFERS_PROPERTY_CODE" => array(
								0 => "COLOR",
								1 => "WIDTH",
								2 => "",
							),
							"OFFERS_SORT_FIELD" => "sort",
							"OFFERS_SORT_ORDER" => "asc",
							"ACTION_VARIABLE" => "action",
							"PRODUCT_ID_VARIABLE" => "id",
							"PRODUCT_QUANTITY_VARIABLE" => "quantity",
							"PRODUCT_PROPS_VARIABLE" => "prop",
							"SECTION_ID_VARIABLE" => "SECTION_ID",
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "180",
							"CACHE_GROUPS" => "Y",
							"DISPLAY_COMPARE" => "N",
							"PRICE_CODE" => array(
								0 => "BASE",
							),
							"USE_PRICE_COUNT" => "N",
							"SHOW_PRICE_COUNT" => "1",
							"PRICE_VAT_INCLUDE" => "Y",
							"CONVERT_CURRENCY" => "N",
							"OFFERS_CART_PROPERTIES" => array(
							),
							"DISPLAY_IMG_WIDTH" => "130",
							"DISPLAY_IMG_HEIGHT" => "130",
							"SHARPEN" => "30"
							),
							false
						);?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid hits_bottom"></div>
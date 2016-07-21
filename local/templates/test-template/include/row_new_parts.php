
<!-- Новинки -->
 
<div class="row-fluid new_parts"> 	
  <div class="container"> 		
    <div class="row"> 			
      <div class="span12"> 				<?$APPLICATION->IncludeComponent(
	"bitrix:eshop.catalog.top",
	"lm",
	Array(
		"DISPLAY_IMG_WIDTH" => "130",
		"DISPLAY_IMG_HEIGHT" => "130",
		"SHARPEN" => "30",
		"IBLOCK_TYPE_ID" => "catalog",
		"IBLOCK_ID" => "3",
		"ELEMENT_SORT_FIELD" => "shows",
		"ELEMENT_SORT_ORDER" => "asc",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"DISPLAY_COMPARE" => "N",
		"ELEMENT_COUNT" => "4",
		"FLAG_PROPERTY_CODE" => "NEWPRODUCT",
		"OFFERS_LIMIT" => "4",
		"OFFERS_FIELD_CODE" => array("NAME"),
		"OFFERS_PROPERTY_CODE" => array("COLOR", "WIDTH"),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"PRICE_CODE" => array("BASE"),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "180",
		"CACHE_GROUPS" => "Y",
		"CONVERT_CURRENCY" => "N",
		"OFFERS_CART_PROPERTIES" => array()
	)
);?> 			</div>
     		</div>
   	</div>
 </div>

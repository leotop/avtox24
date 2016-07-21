<!-- Slider -->
<div class="row-fluid slider_top"></div>
<div class="row-fluid slider">
	<div class="row-fluid slider_bg_left">
		<div class="row-fluid slider_bg_right">
			<div class="container">
				<div class="row">
					<div class="span5 description_main">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array(
							"AREA_FILE_SHOW" => "file",
							"PATH" => SITE_TEMPLATE_PATH."/include/slider_content.php"
							),
							false,
							array(
							"ACTIVE_COMPONENT" => "Y"
							)
						);?>
						<?if (!($USER->IsAuthorized())):?>
							<a href="/login/register/" class="btn btn-warning">Зарегистрируйтесь</a> <em>и получите <br>персональные <br>скидки!</em>
						<?else:?>
							<a href="/personal" class="btn btn-warning">Личный кабинет</a>
						<?endif;?>
					</div>
					<div class="span6">
					<?$APPLICATION->IncludeComponent("bitrix:eshop.catalog.top", "lm_slider", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => "3",
	"ELEMENT_SORT_FIELD" => "RAND",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "10",
	"FLAG_PROPERTY_CODE" => "SPECIALOFFER",
	"OFFERS_LIMIT" => "0",
	"OFFERS_FIELD_CODE" => array(
		0 => "NAME",
		1 => "PREVIEW_TEXT",
		2 => "",
	),
	"OFFERS_PROPERTY_CODE" => array(
		0 => "",
		1 => "COLOR",
		2 => "WIDTH",
		3 => "",
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
	"PRICE_VAT_INCLUDE" => "N",
	"CONVERT_CURRENCY" => "N",
	"OFFERS_CART_PROPERTIES" => array(
	),
	"DISPLAY_IMG_WIDTH" => "190",
	"DISPLAY_IMG_HEIGHT" => "250",
	"SHARPEN" => "30"
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "Y"
	)
);?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid slider_bottom"></div>
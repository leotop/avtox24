<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->IncludeComponent(
	"simai:catalog.yandex", 
	".default", 
	array(
		"USE_MULTIPLE_DATA_SOURCES" => "N",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"SECTION_SHOW_DEPTH" => "0",
		"SECTION_IDS" => array(
			0 => ",,",
		),
		"INCLUDE_SUBSECTIONS" => "Y",
		"USE_EXTRA_OFFER_SETTINGS" => "N",
		"OFFER_TYPE" => "",
		"OFFER_PRICE" => "Розничная",
		"OFFER_CURRENCY" => "RUB",
		"OFFER_NAME" => "NAME",
		"OFFER_MARKET_CATEGORY" => "",
		"OFFER_PICTURE" => "PREVIEW_PICTURE,DETAIL_PICTURE,MORE_PHOTO",
		"OFFER_DESCRIPTION" => "PREVIEW_TEXT,DETAIL_TEXT",
		"OFFER_AVAILABLE" => "",
		"USE_EXTRA_SHOP_SETTINGS" => "N",
		"SHOP_NAME" => "M@G",
		"SHOP_COMPANY" => "Мосавтомаг",
		"CURRENCY_BANK" => "MODULE",
		"CURRENCY_BANK_RATE_PLUS" => "",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"USE_VAT_PRICES" => "N",
		"COMPRESS" => "N",
		"USE_OUTPUT_FILE" => "Y",
		"USE_PRODUCT_IDS" => "N",
		"USE_URL_YMARKER" => "r1=yandext",
		"OFFER_SELLER_WARRANTY" => "",
		"OUTPUT_PATH" => "yandex_market.xml",
		"COMPRESS_FILE" => "N"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
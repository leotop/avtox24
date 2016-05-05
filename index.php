<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
$APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
$APPLICATION->SetPageProperty("title", "Автозапчасти для иномарок");
$APPLICATION->SetTitle("AvtoX24.ru");
?>

<?$APPLICATION->IncludeComponent(
	"linemedia.autotecdoc:tecdoc.catalog2", 
	".default", 
	array(
		"DETAIL_URL" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
		"SEARCH_ARTICLE_URL" => "",
		"COLUMNS_COUNT" => "4",
		"ADD_SECTIONS_CHAIN" => "Y",
		"SHOW_ORIGINAL_ITEMS" => "Y",
		"GROUP_MODELS" => "Y",
		"TECDOC_BRAND_TYPES" => array(
			0 => "1",
			1 => "2",
			2 => "3",
		),
		"MODIFICATIONS_SET" => "default",
		"HIDE_UNAVAILABLE" => "Y",
		"INCLUDE_PARTS_IMAGES" => "Y",
		"ANTI_BOTS" => "Y",
		"MANUAL_TECDOC_GROUPS" => "",
		"SWIFT_FILTER" => "N",
		"CONTEMPORARY_YEAR" => "1999",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/auto/tecdoc/",
		"COMPONENT_TEMPLATE" => ".default",
		"PATH_TO_TECDOC" => "/auto/tecdoc/"
	),
	false
);?>

 <?/*$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_slider.php"
	),
false,
Array(
	'ACTIVE_COMPONENT' => 'Y'
)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_brands.php"
	),
false,
Array(
	'ACTIVE_COMPONENT' => 'N'
)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_hits.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_catalogs.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_content_main.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_new_parts.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_news.php"
	)
);*/?> 	 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
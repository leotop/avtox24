<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
$APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
$APPLICATION->SetPageProperty("title", "Автозапчасти для иномарок");
$APPLICATION->SetTitle("AvtoX24.ru");
?><?$APPLICATION->IncludeComponent(
	"linemedia.autotecdoc:tecdoc.catalog2", 
	"visual", 
	array(
		"ADD_SECTIONS_CHAIN" => "Y",
		"ANTI_BOTS" => "Y",
		"COLUMNS_COUNT" => "4",
		"CONTEMPORARY_YEAR" => "1999",
		"DETAIL_URL" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
		"GROUP_MODELS" => "Y",
		"HIDE_UNAVAILABLE" => "Y",
		"INCLUDE_PARTS_IMAGES" => "Y",
		"MANUAL_TECDOC_GROUPS" => "",
		"MODIFICATIONS_SET" => "default",
		"PATH_TO_TECDOC" => "/auto/tecdoc/",
		"SEARCH_ARTICLE_URL" => "",
		"SEF_FOLDER" => "/auto/tecdoc/",
		"SEF_MODE" => "Y",
		"SHOW_ORIGINAL_ITEMS" => "Y",
		"SWIFT_FILTER" => "N",
		"TECDOC_BRAND_TYPES" => array(
			0 => "1",
			1 => "2",
			2 => "3",
		),
		"COMPONENT_TEMPLATE" => "visual"
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
);*/?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
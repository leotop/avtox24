<?
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * detail
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetPageProperty("keywords", "Каталог неоригинальных запчастей, TecDoc. неоригинал, запчасти для иномарок, запчасти для отечественных");
$APPLICATION->SetPageProperty("title", "Каталог неоригинальных запчастей");
$APPLICATION->SetTitle("Каталог неоригинальных запчастей");
?><?$APPLICATION->IncludeComponent(
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
		"HIDE_UNAVAILABLE" => "N",
		"INCLUDE_PARTS_IMAGES" => "Y",
		"ANTI_BOTS" => "Y",
		"MANUAL_TECDOC_GROUPS" => "",
		"SWIFT_FILTER" => "N",
		"CONTEMPORARY_YEAR" => "",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/auto/tecdoc/",
		"COMPONENT_TEMPLATE" => ".default",
		"PATH_TO_TECDOC" => "/auto/tecdoc/"
	),
	false
);?><? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?> 
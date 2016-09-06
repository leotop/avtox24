<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Автозапчасти");?>
<?
$APPLICATION->IncludeComponent(
	"linemedia.autotecdoc:tecdoc.catalog2", 
	"ajax", 
	array(
		"DETAIL_URL" => "",
		"SEARCH_ARTICLE_URL" => "",
		"COLUMNS_COUNT" => "1",
		"ADD_SECTIONS_CHAIN" => "N",
		"SHOW_ORIGINAL_ITEMS" => "Y",
		"GROUP_MODELS" => "N",
		"TECDOC_BRAND_TYPES" => array(
			0 => "1",
			1 => "2",
			2 => "3",
		),
		"MODIFICATIONS_SET" => "default",
		"HIDE_UNAVAILABLE" => "N",
		"DISABLE_STATS" => "Y",
		"INCLUDE_PARTS_IMAGES" => "N",
		"ANTI_BOTS" => "N",
		"MANUAL_TECDOC_GROUPS" => "",
		"SWIFT_FILTER" => "N",
		"CONTEMPORARY_YEAR" => "2006",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/templates/ajax/",
		"PATH_TO_TECDOC" => "/auto/tecdoc/"
	),
	false
);
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
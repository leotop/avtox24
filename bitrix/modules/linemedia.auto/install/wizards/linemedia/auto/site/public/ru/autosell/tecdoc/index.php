<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Поиск");
?>
<?
$APPLICATION->IncludeComponent("linemedia.auto:tecdoc.catalog", ".default", array(
	"SEARCH_URL" => "/auto/search/#ARTICLE_ID#/?brand=#BRAND_ID#",
	"COLUMNS_COUNT" => "4",
	"ADD_SECTIONS_CHAIN" => "Y",
	"SHOW_ORIGINAL_ITEMS" => "Y",
	"ADD_SEO_DATA" => "Y",
	"TECDOC_NEW_URL" => "N",
	"SHOW_CAR_BRANDS_IN_URI" => "N",
	"SEF_FOLDER" => "/auto/tecdoc/",
	"SEF_MODE" => "N"
	),
	false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>

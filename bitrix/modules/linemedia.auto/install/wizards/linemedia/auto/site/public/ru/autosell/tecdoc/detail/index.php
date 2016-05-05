<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Детальная информация");
?>
<?
$APPLICATION->IncludeComponent("linemedia.auto:tecdoc.catalog.detail", ".default", array(
	"SEARCH_URL" => "/auto/search/#ARTICLE_ID#/?brand=#BRAND_ID#",
	"ADD_SECTIONS_CHAIN" => "N",
	"SHOW_ORIGINAL_ITEMS" => "Y",
	"SHOW_SEARCH_FORM" => "Y",
	"SHOW_APPLICABILITY" => "Y",
	"SHOW_SEO" => "Y",
	"SEF_FOLDER" => "/auto/tecdoc/",
	"SEF_MODE" => "N",
	"SET_TITLE" => "Y"
	),
	false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>

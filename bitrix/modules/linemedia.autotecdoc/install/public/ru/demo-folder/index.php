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
$APPLICATION->SetTitle("Поиск");
?>
<?
$APPLICATION->IncludeComponent("linemedia.autotecdoc:tecdoc.catalog2", ".default", array(
	"DETAIL_URL" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
	"COLUMNS_COUNT" => "4",
	"ADD_SECTIONS_CHAIN" => "Y",
	"SHOW_ORIGINAL_ITEMS" => "Y",
	"GROUP_MODELS" => "N",
	"TECDOC_BRAND_TYPES" => array(
		0 => "1",
		1 => "2",
		2 => "3",
	),
	"MODIFICATIONS_SET" => "default",
	"DISABLE_STATS" => "N",
	"INCLUDE_PARTS_IMAGES" => "Y",
	"SEF_FOLDER" => "#DEMO_FOLDER#",
	"SEF_MODE" => "Y",
	"SEF_URL_TEMPLATES" => array(
		"DEFAULT" => "Y",
	)
	),
	false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
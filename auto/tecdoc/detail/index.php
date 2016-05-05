<?
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * detail-index
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Детальная информация");
?>
<?
$APPLICATION->IncludeComponent("linemedia.autotecdoc:tecdoc.catalog.detail", ".default", array(
	"SEARCH_URL" => "/auto/search/#ARTICLE#/",
	"ADD_SECTIONS_CHAIN" => "N",
	"SHOW_ORIGINAL_ITEMS" => "Y",
	"SHOW_SEARCH_FORM" => "Y",
	"SHOW_APPLICABILITY" => "Y",
	"SHOW_SEO" => "Y",
	"SEF_FOLDER" => "/auto/tecdoc/",
	"SEF_MODE" => "Y",
	"SET_TITLE" => "Y"
	),
	false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>

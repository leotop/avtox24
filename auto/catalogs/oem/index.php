<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оригинальные каталоги");
?><?$APPLICATION->IncludeComponent("linemedia.auto:original.catalog.index", ".default", array(
	"ADD_SECTIONS_CHAIN" => "Y",
	"HIDE_UNAVAILABLE" => "N",
	"DISABLE_STATS" => "N",
	"INCLUDE_JQUERY" => "N",
	"VIN_URL" => "/auto/catalogs/oem/vin/",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/auto/catalogs/oem/"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
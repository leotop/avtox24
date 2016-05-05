<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Расшифровка по VIN коду");
?><?$APPLICATION->IncludeComponent(
	"linemedia.auto:original.vin",
	"",
	Array(
		"SET_TITLE" => "Y",
		"CATALOGS_PATH" => "/auto/catalogs/oem/#BRAND#/#MODEL#/#TYPE#/",
		"DISABLE_STATS" => "N",
		"INCLUDE_JQUERY" => "N"
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
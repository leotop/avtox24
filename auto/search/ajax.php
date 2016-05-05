<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("");
$APPLICATION->IncludeComponent(
	"linemedia.auto:search.results",
	"",
	Array(
		"QUANTITY_ROUNDING" => "2",
		"ACTION_VAR" => "act",
		"QUERY" => $_REQUEST["q"],
		"PART_ID" => $_REQUEST["part_id"],
		"BRAND_TITLE" => $_REQUEST["brand_title"],
		"EXTRA" => $_REQUEST["extra"],
		"AUTH_URL" => "/auth/",
		"BASKET_URL" => "/auto/cart/",
		"VIN_URL" => "/auto/vin/",
		"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
		"PATH_NOTEPAD" => "/personal/notepad/",
		"SET_TITLE" => "N",
		"TITLE" => "Поиск запчасти #QUERY#",
		"HIDE_FIELDS" => array("weight", "supplier", "modified", "count"),
		"USE_GROUP_SEARCH" => "N",
		"SHOW_SUPPLIER" => array(),
		"REMAPPING" => "N",
		"SHOW_BLOCKS" => "results",
		"MERGE_GROUPS" => "N",
		"ANTI_BOTS" => "N",
		"SORT" => "price_src",
		"ORDER" => "asc",
		"LIMIT" => "0",
		"DISABLE_STATS" => "N",
		"SHOW_ANALOGS" => "N",
		"USE_REQUEST_FORM" => "Y"
	),
false
);
/*

$APPLICATION->IncludeComponent("linemedia.auto:search.results", ".default", array(
	"ACTION_VAR" => "act",
	"QUERY" => $_REQUEST["q"],
	"PART_ID" => $_REQUEST["part_id"],
	"BRAND_TITLE" => $_REQUEST["brand_title"],
	"EXTRA" => $_REQUEST["extra"],
	"AUTH_URL" => "/auth/",
	"BASKET_URL" => "/auto/cart/",
	"VIN_URL" => "/auto/vin/",
	"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
	"PATH_NOTEPAD" => "/auto/notepad/",
	"TITLE" => "Поиск запчасти #QUERY#",
	"HIDE_FIELDS" => array(
	),
	"USE_GROUP_SEARCH" => "Y",
	"SHOW_SUPPLIER" => array(
	),
	"REMAPPING" => "Y",
	"SHOW_BLOCKS" => "both",
	"MERGE_GROUPS" => "N",
	"ANTI_BOTS" => "N",
	"SORT" => "price_src",
	"ORDER" => "asc",
	"LIMIT" => "0",
	"DISABLE_STATS" => "N",
	"USE_REQUEST_FORM" => "Y",
	"SET_TITLE" => "N",
	"QUANTITY_ROUNDING" => "2"
	),
	false
);*/?>  <?/*
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ajax");
?><?$APPLICATION->IncludeComponent(
	"linemedia.auto:search.results",
	"",
	Array(
		"QUANTITY_ROUNDING" => "2",
		"ACTION_VAR" => "act",
		"QUERY" => $_REQUEST["q"],
		"PART_ID" => $_REQUEST["part_id"],
		"BRAND_TITLE" => $_REQUEST["brand_title"],
		"EXTRA" => $_REQUEST["extra"],
		"AUTH_URL" => "/auth/",
		"BASKET_URL" => "/auto/cart/",
		"VIN_URL" => "/auto/vin/",
		"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
		"PATH_NOTEPAD" => "/auto/notepad/",
		"SET_TITLE" => "N",
		"TITLE" => "Поиск запчасти #QUERY#",
		"HIDE_FIELDS" => array("weight", "supplier", "modified", "count"),
		"USE_GROUP_SEARCH" => "N",
		"SHOW_SUPPLIER" => array(),
		"REMAPPING" => "N",
		"SHOW_BLOCKS" => "results",
		"MERGE_GROUPS" => "Y",
		"ANTI_BOTS" => "N",
		"SORT" => "price_src",
		"ORDER" => "asc",
		"LIMIT" => "0",
		"DISABLE_STATS" => "N",
		"SHOW_ANALOGS" => "Y",
		"USE_REQUEST_FORM" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");*/?>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule('linemedia.auto');?>


<?$APPLICATION->IncludeComponent(
	"linemedia.auto:search.results",
	".default",
	Array(
		"QUANTITY_ROUNDING" => "2",
		"ACTION_VAR" => "s_action",
		"QUERY" => $_REQUEST["q"],
		"PART_ID" => $_REQUEST["part_id"],
		"BRAND_TITLE" => $_REQUEST["brand_title"],
		"EXTRA" => $_REQUEST["extra"],
		"AUTH_URL" => "/auth/",
		"BASKET_URL" => "/auto/cart/",
		"VIN_URL" => "/auto/vin/",
		"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
		"PATH_NOTEPAD" => "/auto/notepad/",
		"SET_TITLE" => "Y",
		"TITLE" => "????? ???????? #QUERY#",
		"HIDE_FIELDS" => array("article", "info", "weight", "supplier", "modified", "count", "stats"),
		"SHOW_CUSTOM_FIELDS" => array(),
		"USE_GROUP_SEARCH" => "N",
		"SHOW_SUPPLIER" => array("0", "1", "2", "3", "4", "5", "6", "33", "42", "43"),
		"REMAPPING" => "N",
		"SHOW_BLOCKS" => "results",
		"MERGE_GROUPS" => "N",
		"ANTI_BOTS" => "N",
		"SORT" => "price",
		"ORDER" => "asc",
		"LIMIT" => "0",
		"SHOW_ANALOGS" => "Y",
		"NO_SHOW_WORDFORMS" => "Y",
		"SHOW_ANALOGS_STATISTICS" => "Y",
		"USE_REQUEST_FORM" => "Y"
	)
);?>


<?/*$APPLICATION->IncludeComponent("linemedia.auto:search.results", ".default", array(
	"ACTION_VAR" => "s_action",
	"QUERY" => $_REQUEST["q"],
	"PART_ID" => $_REQUEST["part_id"],
	"BRAND_TITLE" => $_REQUEST["brand_title"],
	"EXTRA" => $_REQUEST["extra"],
	"AUTH_URL" => "/auth/",
	"BASKET_URL" => "/auto/cart/",
	"VIN_URL" => "/auto/vin/",
	"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
	"PATH_NOTEPAD" => "/auto/notepad/",
	"TITLE" => "????? ???????? #QUERY#",
	"HIDE_FIELDS" => array(
		0 => "weight",
		1 => "supplier",
        2 => "statistic",
	),
	"SHOW_CUSTOM_FIELDS" => array(
	),
	"USE_GROUP_SEARCH" => "N",
	"SHOW_SUPPLIER" => array(
		0 => "0",
		1 => "1",
		2 => "2",
		3 => "3",
		4 => "4",
		5 => "33",
		6 => "42",
		7 => "43",
		8 => "5",
		9 => "6",
	),
	"REMAPPING" => "N",
	"SHOW_BLOCKS" => "results",
	"MERGE_GROUPS" => "N",
	"ANTI_BOTS" => "Y",
	"SORT" => "supplier_title",
	"ORDER" => "asc",
	"LIMIT" => "0",
	"SHOW_ANALOGS" => "Y",
	"NO_SHOW_WORDFORMS" => "Y",
	"SHOW_ANALOGS_STATISTICS" => "Y",
	"USE_REQUEST_FORM" => "Y",
	"SET_TITLE" => "Y",
	"QUANTITY_ROUNDING" => "2"
	),
	false
);*/?>

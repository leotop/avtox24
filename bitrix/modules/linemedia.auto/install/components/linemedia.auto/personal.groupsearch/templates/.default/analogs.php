<?php

include ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// ≈сли нет главного модул€ - негде делать просчет.
if (!CModule::IncludeModule('linemedia.auto')) {
	return;
}

$article  = (string) $_REQUEST['article'];
$brand    = (string) $_REQUEST['brand'];

$APPLICATION->IncludeComponent(
	"linemedia.auto:search.results",
	"group_search",
	array(
		"SELECTED" => $selected,
		"ACTION_VAR" => "s_action",
		"QUERY" => $article,
		"BRAND_TITLE" => $brand,
		"EXTRA" => $_REQUEST["extra"],
		"SEARCH_ARTICLE_URL" => "/auto/search/#ARTICLE#/",
		"AUTH_URL" => "/auth/",
		"BASKET_URL" => "/auto/cart/",
		"VIN_URL" => "/auto/vin/",
		"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
		"PATH_NOTEPAD" => "/auto/notepad/",
		"TITLE" => "Result #QUERY#",
		"HIDE_FIELDS" => array(
			0 => "weight",
			//1 => "supplier",
		),
		"SHOW_CUSTOM_FIELDS" => array(),
		"USE_GROUP_SEARCH" => "N",
		"SHOW_SUPPLIER" => array(),
		"REMAPPING" => "N",
		"SHOW_BLOCKS" => "results",
		"MERGE_GROUPS" => "N",
		"ANTI_BOTS" => "N",
		"SORT" => "price_src",
		"ORDER" => "asc",
		"LIMIT" => "0",
		"SHOW_ANALOGS" => "Y",
		"NO_SHOW_WORDFORMS" => "Y",
		"SHOW_ANALOGS_STATISTICS" => "Y",
		"USE_REQUEST_FORM" => "Y",
		"SET_TITLE" => "N",
		"QUANTITY_ROUNDING" => "0",

        "SUPPLIERS_FILTER" => $_REQUEST['suppliers'],
	),
	false
);



<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_MAIN_SEARCH_DETAIL_INFO_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_MAIN_SEARCH_DETAIL_INFO_DESCRIPTION"),
	"ICON" => "/images/component_icon.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 10,
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
		"CHILD" => array(
			"ID" => "LM_AUTO_MAIN_TECDOC",
			"NAME" => GetMessage("LM_AUTO_MAIN_SEARCH_SUB_SECTION"),
			"SORT" => 10,
		),
	),
);
 

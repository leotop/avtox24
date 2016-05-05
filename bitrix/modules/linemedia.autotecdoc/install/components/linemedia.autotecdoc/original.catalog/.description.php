<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_MAIN_TECDOC_OROGINAL_CATALOG_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_MAIN_TECDOC_OROGINAL_CATALOG_DESC"),
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_TECDOC_OROGINAL_CATALOG_SECTION"),
		"CHILD" => array(
			"ID" => GetMessage("LM_AUTO_MAIN_TECDOC_OROGINAL_CATALOG_NAME"),
			"NAME" => GetMessage("LM_AUTO_MAIN_TECDOC_OROGINAL_CATALOG_NAME")
		)
	),
);
?>
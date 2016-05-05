<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_SUPPLIER_RELIABILITY_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_SUPPLIER_RELIABILITY_DESC"),
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_SUPPLIER_RELIABILITY_SECTION"),
		"CHILD" => array(
			"ID" => GetMessage("LM_AUTO_SUPPLIER_RELIABILITY_NAME"),
			"NAME" => GetMessage("LM_AUTO_SUPPLIER_RELIABILITY_NAME")
		)
	),
);
?>
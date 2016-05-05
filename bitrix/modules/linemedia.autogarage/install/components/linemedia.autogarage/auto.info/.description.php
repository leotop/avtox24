<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTOPORTAL_DEFAULT_AUTO_INFO_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTOPORTAL_DEFAULT_AUTO_INFO_DESCRIPTION"),
	"ICON" => "/images/iblock_compare_list.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 10,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "LM_AUTOPORTAL",
			"NAME" => GetMessage("LM_AUTOPORTAL_SECTION_COMP"),
			"SORT" => 370,
			"CHILD" => array(
			    "ID" => "LM_AUTOPORTAL_auto_info",
			),
		),
	),
);
?>
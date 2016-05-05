<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_BRANCHES_ADMIN_BRANCH_SELECT_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_BRANCHES_ADMIN_BRANCH_SELECT_DESCRIPTION"),
	"ICON" => "/images/eaddform.gif",
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => "LM_AUTO_BRANCHES",
            "NAME" => GetMessage("LM_AUTO_BRANCHES_SUB_SECTION"),
            "SORT" => 10,
        ),
	),
);
?>
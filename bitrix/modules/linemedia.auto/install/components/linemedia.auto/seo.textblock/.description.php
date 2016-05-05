<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LMSEO_DEFAULT_TEMPLATE_NAME"),
    "DESCRIPTION" => GetMessage("LMSEO_DEFAULT_TEMPLATE_DESCRIPTION"),
    "ICON" => "/images/sale_order_full.gif",
    "PATH" => array(
        "ID" => GetMessage("LM_SEO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => "LM_SEO_BLOCK",
            "NAME" => GetMessage("LM_SEO_MAIN_ORDERS_SUB_SECTION"),
            "SORT" => 10,
        ),
    ),
);
?>
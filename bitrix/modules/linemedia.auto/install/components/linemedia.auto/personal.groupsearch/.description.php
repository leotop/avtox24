<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LM_AUTO_MAIN_GROUP_SEARCH_NAME"),
    "DESCRIPTION" => GetMessage("LM_AUTO_MAIN_GROUP_SEARCH_DESCRIPTION"),
    "ICON" => "/images/component_icon.gif",
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => GetMessage("LM_AUTO_MAIN_GROUP_SEARCH_NAME"),
            "NAME" => GetMessage("LM_AUTO_MAIN_GROUP_SEARCH_NAME"),
            "SORT" => 10,
        ),
    ),
);

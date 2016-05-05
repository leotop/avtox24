<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arTemplateParameters = array(
    "QUERY_TITLE"=>array(
        "NAME" => GetMessage('LM_AUTO_QUERY_TITLE'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "QUERY_COMMENT"=>array(
        "NAME" => GetMessage('LM_AUTO_QUERY_COMMENT'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "QUERY_QUANTITY"=>array(
        "NAME" => GetMessage('LM_AUTO_QUERY_QUANTITY'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "QUERY_URL"=>array(
        "NAME" => GetMessage('LM_AUTO_QUERY_URL'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "QUERY_KEY"=>array(
        "NAME" => GetMessage('LM_AUTO_QUERY_KEY'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),

);
?>

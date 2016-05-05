<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        'ADD_SECTION_CHAIN' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_ADD_CHAIN'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'SET_TITLE_NOTEPAD' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_SET_TITLE'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        "TITLE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_TITLE'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_TITLE_DEFAULT')
        ),
        "STRING_SYMBOLS" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_STRING_SYMBOLS'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => 30
        ),
        'INIT_JQUERY' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_INIT_JQUERY'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
		'SEARCH_ARTICLE_URL' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_NOTEPAD_SEARCH_ARTICLE_URL'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>''
        ),
    ),
);

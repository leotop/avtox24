<?php
/**
 * Linemedia API
 * Описание методов
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */
 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);


/*
* Методы работы с поставщиками
*/
$methods["CAPILinemediaAutoPrice"] = array(
	'LinemediaAutoPrice_PriceImportTask' => array(
		"type"      => "public",
		"name"      => "PriceImportTask",
		"input"     => array(
			"filter" => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
		),
		"output"    => array(
			"result" => array("varType" => "Struct_PriceImportTask")
		),
		//'link' => 'http://dev.1c-bitrix.ru/api_help/catalog/classes/cprice/cprice__getbyid.872661b0.php',
	),
	'LinemediaAutoPrice_PriceProductsList' => array(
		"type"      => "public",
		"name"      => "PriceProductsList",
		"input"     => array(
			"filter" => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
		),
		"output"    => array(
			"result" => array("varType" => "Struct_PriceProductsList")
		),
		//'link' => 'http://dev.1c-bitrix.ru/api_help/catalog/classes/cprice/cprice__getbyid.872661b0.php',
	),
	
);

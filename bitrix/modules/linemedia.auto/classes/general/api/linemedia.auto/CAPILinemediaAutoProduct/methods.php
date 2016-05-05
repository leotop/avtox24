<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia API
 * Main auto module
 * Module events
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);


/*
* Методы работы с поиском
*/
$methods["CAPILinemediaAutoProduct"] = array(
	'LinemediaAutoProduct_Add' => array(
		"type"      => "public",
		"name"      => "Add",
		"input"     => array(
			'products' 		 	=> array("varType" => "ArrayOfStruct_LinemediaAuto_Product", "arrType" => "Struct_LinemediaAuto_Product"),
			'update_if_exists' 	=> array("varType" => "integer", "strict" => "no"),
			'unique_fields' 	=> array("varType" => "ArrayOfString", "arrType" => "string", "strict" => "no"),
		),
		"output"    => array(
			"ok" => array("varType" => "int")
		),
		'desc' => GetMessage('LM_API_FNC_ADD_DESCR'),
	),
	'LinemediaAutoProduct_Update' => array(
		"type"      => "public",
		"name"      => "Update",
		"input"     => array(
			"arFilter" 			=> array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray", "strict" => "no"),
			"arUpdate" 			=> array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray", "strict" => "no"),
		),
		"output"    => array(
			"updated_count" => array("varType" => "int")
		),
		'desc' => GetMessage('LM_API_FNC_ADD_DESCR'),
	),
	'LinemediaAutoProduct_Delete' => array(
		"type"      => "public",
		"name"      => "Delete",
		"input"     => array(
			'filter'		 => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
		),
		"output"    => array(
			"ok" => array("varType" => "bool")
		),
		'desc' => GetMessage('LM_API_FNC_DEL_DESCR'),
	),
	'LinemediaAutoProduct_getProducts' => array(
		"type"      => "public",
		"name"      => "getProducts",
		"input"     => array(
			"filter" => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
		),
		"output"    => array(
			"result" => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
		),
		'desc' => GetMessage('LM_API_FNC_GET_DESCR'),
	),
);

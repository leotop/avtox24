<?php
/**
 * Linemedia API
 * Драйвер для модуля (linemedia.api) API для сайта
 * Описание методов
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeModuleLangFile(__FILE__);


/**
* Методы работы с классом
*/
$methods["CAPILinemediaAutoAnalogsSimpleAnalog"] = array(
	'LinemediaAutoAnalogsSimpleAnalog_Add' => array(
		"type"      => "public",
		"name"      => "Add",
		"input"     => array(
			'elements' 		 => array("varType" => "ArrayOfStruct_LinemediaAutoAnalogsSimple_Analog", "arrType" => "Struct_LinemediaAutoAnalogsSimple_Analog"),
		),
		"output"    => array(
			"ok" => array("varType" => "bool")
		),
		'desc' => GetMessage('LM_API_FNC_ADD_DESCR'),
	),
	'LinemediaAutoAnalogsSimpleAnalog_Delete' => array(
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
	
);

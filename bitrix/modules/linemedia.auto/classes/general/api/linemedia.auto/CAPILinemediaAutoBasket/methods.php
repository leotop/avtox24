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
$methods["CAPILinemediaAutoBasket"] = array(

	'LinemediaAutoBasket_statusItem' => array(
		"type"      => "public",
		"name"      => "statusItem",
		"input"     => array(
			'basket_id' 		 => array("varType" => "int"),
            'newStatus'         => array("varType" => "string"),
		),
		"output"    => array(
			"ok" => array("varType" => "bool")
		),
		'desc' => GetMessage('LM_API_FNC_STATUS_ITEM_DESCR'),
	),

    'LinemediaAutoBasket_addItem' => array(
        "type"      => "public",
        "name"      => "addItem",
        "input"     => array(
            'buy_hash'    => array("varType" => "string", "strict" => "strict"),
            'quantity'      => array("varType" => "int", "strict" => "no"),
            'price'         => array("varType" => "float", "strict" => "no"),
            'user_id'       => array("varType" => "int", "strict" => "no"),
        ),
        "output"    => array(
            "id" => array("varType" => "int")
        ),
        'desc' => GetMessage('LM_API_FNC_ADD_ITEM_DESCR'),
    ),
);

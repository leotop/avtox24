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
* Методы работы с заказами
*/
$methods["CAPILinemediaAutoOrder"] = array(

    'LinemediaAutoOrder_makeOrder' => array(
        "type"      => "public",
        "name"      => "makeGroupOrder",
        "input"     => array(
            'order' 		=> array("varType" => "Struct_Order", "strict" => "strict"),
        ),
        "output" => array(
            'debug' => array("varType" => "string", "strict" => "no"),
            //'id' => array("varType" => "integer", "strict" => "strict"),
        ),
        'desc' => GetMessage('LM_API_FNC_MAKE_ORDER_DESCR'),
    ),
);

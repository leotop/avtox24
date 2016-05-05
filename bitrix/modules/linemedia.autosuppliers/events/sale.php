<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/**
 * Linemedia Autoportal
 * Suppliers module
 * Module events for sale
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule('linemedia.auto')) {
    trigger_error('Linemedia auto module not installed!');
}


class LinemediaAutoSuppliersEventSale
{
    
}

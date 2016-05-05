<?php 

/**
 * Linemedia Autoportal
 * Suppliers module
 * Main include file
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


include_once('install/version.php');
include_once('constants.php');


global $DBType;

CModule::AddAutoloadClasses(
	'linemedia.autosuppliers',
	array(
	        'LinemediaAutoSuppliersRequest'            => "classes/general/request.php",
	        'LinemediaAutoSuppliersRequestBasket'      => "classes/general/request_basket.php",
	        'LinemediaAutoSuppliersStep'               => "classes/general/step.php",
	        'LinemediaAutoSuppliersRequestExporter'    => "classes/general/request_exporter.php",
            'LinemediaAutoSuppliersRequestImporter'    => "classes/general/request_importer.php",
            
            /*
             * Обработчики событий
             */
            'LinemediaAutoSuppliersEventMain'          => "events/main.php",
	        'LinemediaAutoSuppliersEventSale'          => "events/sale.php",
	        'LinemediaAutoSuppliersEventLinemediaAuto' => "events/linemedia.auto.php",
	)
);

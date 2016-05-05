<?php 

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Main include file
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


include_once('install/version.php');

global $DBType;

CModule::AddAutoloadClasses(
	"linemedia.autoremotesuppliers",
	array(
        'LinemediaAutoRemoteSuppliersSupplier'                  => "classes/general/remote_supplier.php",
        'LinemediaAutoRemoteSuppliersPart'                      => "classes/general/part.php",
        'LinemediaAutoRemoteSuppliersBrowser'                   => "classes/general/browser.php",
        'phpQuery'                                              => "classes/general/phpQuery.php",
        
        'LinemediaAutoRemoteSuppliersIblockPropertyApi'         => "classes/general/iblock_prop_supplier_api.php",
        
        'LinemediaAutoRemoteSuppliersEventLinemediaAuto'        => "events/linemedia.auto.php",
        
        
	    'LinemediaAutoSuppliersThread'							=> 'classes/general/thread.php',
        //'LinemediaAutoSimpleCache'							    => 'classes/general/cache.php',
        'LinemediaAutoRemoteSuppliersCacheClearAgent'			=> 'classes/general/cache_clear_agent.php',
	)
);

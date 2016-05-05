<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Protocol
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
* Интерфейс удалённого поставщика
*/
abstract class LinemediaAutoDownloaderProtocol
{

	abstract public function download($test = false);
    abstract public static function getConfigVars();
    abstract public static function available();  
    abstract public static function getRequirements();
    
}


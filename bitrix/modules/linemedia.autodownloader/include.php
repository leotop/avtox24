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
    "linemedia.autodownloader",
    array(
        'LinemediaAutoDownloaderIProtocol'              => "classes/general/interfaces/protocol.php",
    
        'LinemediaAutoDownloaderMain'                   => "classes/general/main.php",
        'LinemediaAutoDownloaderDownloadAgent'          => "classes/general/download_agent.php",
        'LinemediaAutoDownloaderEventLinemediaAuto'     => "events/linemedia.auto.php",
    )
);

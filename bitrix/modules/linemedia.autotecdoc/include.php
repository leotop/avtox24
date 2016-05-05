<?php
/**
 * Linemedia Autoportal
 * Tecdoc module
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
	"linemedia.autotecdoc",
	array(
	        'LinemediaAutoTecDocApiDriver'          => "classes/general/api_driver.php",
	        'LinemediaAutoTecDocApiModifications'   => "classes/general/api_modifications.php",
            'LinemediaAutoTecDocTecDocRights'       => "classes/general/rights.php",
            'LinemediaAutoTecDocFileHelper'         => "classes/general/file_helper.php",
            'LinemediaAutoTecDocDebug'              => "classes/general/debug.php",
            'LinemediaAutoTecDocUrlHelper'          => "classes/general/url_helper.php",
            'LinemediaAutoTecDocXML2Arr'            => "classes/general/xml.php",
            'LinemediaAutoTecDocArr2XML'            => "classes/general/xml.php",
            'LinemediaAutoTecdocUserHelper'         => 'classes/general/user_helper.php', // вспомогательный класс для работы с пользователями
	)
);

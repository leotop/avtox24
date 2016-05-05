<?php

/**
 * Linemedia Autoportal
 * garage module
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
    "linemedia.autogarage",
    array(
            'LinemediaAutoGarageEventLinemediaAuto'     => "events/linemedia.auto.php", // Класс событий основного модуля.
            'LinemediaAutoGarage'                       => "classes/general/garage.php",
            'LinemediaAutoGarageApiTecDoc'              => "classes/general/api_tecdoc.php",
            'LinemediaAutoGarageEventLinemediaCatalogs' => "events/linemedia.catalogs.php",
    )
);

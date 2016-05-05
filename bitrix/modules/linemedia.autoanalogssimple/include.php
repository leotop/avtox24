<?php
/**
* Файл автозагрузки
*/

/**
 * Linemedia Autoportal
 * Модуль простых аналогов
 * Файл автозагрузки
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */




include_once('install/version.php');


global $DBType;

CModule::AddAutoloadClasses(
	"linemedia.autoanalogssimple",
	array(
            'LinemediaAutoAnalogsSimpleEventLinemediaAuto'        => "events/linemedia.auto.php", // Класс событий основного модуля.
            'LinemediaAutoAnalogsSimpleEventApi'        		=> "events/linemedia.api.php", // Класс событий основного модуля.
            'LinemediaAutoAnalogsSimpleAnalogAll'                 => "classes/general/analog.php", // Класс импортёр
            'LinemediaAutoAnalogsSimpleAnalog'                    => "classes/$DBType/analog.php", // Класс импортёр
	)
);

<?php
/**
* ���� ������������
*/

/**
 * Linemedia Autoportal
 * ������ ������� ��������
 * ���� ������������
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
            'LinemediaAutoAnalogsSimpleEventLinemediaAuto'        => "events/linemedia.auto.php", // ����� ������� ��������� ������.
            'LinemediaAutoAnalogsSimpleEventApi'        		=> "events/linemedia.api.php", // ����� ������� ��������� ������.
            'LinemediaAutoAnalogsSimpleAnalogAll'                 => "classes/general/analog.php", // ����� �������
            'LinemediaAutoAnalogsSimpleAnalog'                    => "classes/$DBType/analog.php", // ����� �������
	)
);

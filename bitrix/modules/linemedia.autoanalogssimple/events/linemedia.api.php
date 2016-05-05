<?php
/**
 * Linemedia Autoportal
 * Main module
 * Module events for API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */



if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);


/**
* ������� ��� ������ API
*/
class LinemediaAutoAnalogsSimpleEventApi
{

    /**
     * ���������� ������� � ������ ���
     * @param array $folders ������ ����� ��� ������������ ������� ��� ��� �����
     */
    public function OnModulesScan_AddAPI(&$folders)
    {
        $folders []= $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoanalogssimple/classes/general/api/';
    }
    
}

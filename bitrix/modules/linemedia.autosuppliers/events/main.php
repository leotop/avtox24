<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Suplpiers module
 * Module events for module Sale
 * 
 * @author  Linemedia
 * @since   22/01/2012
 * 
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

class LinemediaAutoSuppliersEventMain
{
    /**
     * Отправка письма с прикрепленным файлом.
     */
    public function OnBeforeEventAdd_AttachePrice(&$event, &$sid, &$arFields)
    {
        // Отправка прайслиста во вложении.
        if (!empty($arFields['ATTACH']) && CModule::IncludeModule('linemedia.auto')) {
            if (in_array($event, array('LM_AUTO_SUPPLIERS_REQUEST'))) {
                LinemediaAutoAttach::SendAttach($event, $sid, $arFields, $arFields['ATTACH'], $arFields['ATTACH_NAME']);
                return false;
            }
        }
    }
}


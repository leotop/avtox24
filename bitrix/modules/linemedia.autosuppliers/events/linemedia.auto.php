<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Branches module
 * Module events for module Main
 * 
 * @author  Linemedia
 * @since   22/01/2012
 * 
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);


if (!CModule::IncludeModule('linemedia.auto')) {
    trigger_error('Linemedia auto module not installed!');
}

class LinemediaAutoSuppliersEventLinemediaAuto
{
    
    /**
     * Добавим проверки.
     */
    public function OnRequirementsListGet_addConverterChecks(&$check)
    {
        $add = array();
        
        /*
         * Конвертер
         */

		$option = COption::GetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_XLS_TYPE', 'ssconvert');

		if ($option == 'html') {
			$status = 1;
		} elseif ($option == 'ssconvert') {
			$status = (bool) LinemediaAutoSuppliersRequest::isConversionSupported();
		}
        $add []= array(
            'title' => GetMessage('LM_AUTO_SUPPLIERS_CONVERTER_AVAILABLE'),
            'requirements' => GetMessage('LM_AUTO_SUPPLIERS_NO_CONVERTER'),
            'status' => $status,
        );
        $check['linemedia.autosuppliers'] = $add;
    }
    
    
    /**
     * Проверка на закрытие заявок.
     */
    public function OnAfterBasketItemStatus_checkRequestClose($basket_id, $status)
    {
        $closed_statuses = unserialize(COption::GetOptionString('linemedia.autosuppliers', 'CLOSED_STATUSES'));

        if (in_array($status, $closed_statuses)) {
            // Проверим, по заявкам.
            $res = LinemediaAutoSuppliersRequestBasket::GetRequestIdByBasketId($basket_id);
            $request_ids = array();
            while ($item = $res->Fetch()) {
                $request_ids []= (int) $item['request_id'];
            }

            $basket = new LinemediaAutoBasket();
            
            // Пройдем по корзинам.
            foreach ($request_ids as $request_id) {
                $res = LinemediaAutoSuppliersRequestBasket::GetByRequestId($request_id);
                
                $close = true;
                while ($item = $res->Fetch()) {
                    $basket_props = $basket->getProps($item['basket_id']);
                    if (!in_array($basket_props['status']['VALUE'], $closed_statuses)) {
                        $close = false;
                        break;
                    }
                }
                
                // Закроем заявку.
                if ($close) {
                    $request = new LinemediaAutoSuppliersRequest($request_id);
                    $request->close();
                }
            }
        }
    }
}

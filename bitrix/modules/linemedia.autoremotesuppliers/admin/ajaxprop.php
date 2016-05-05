<?php
/**
 * Поставщики для ajax-запроса
 */
/**
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';


if (CModule::IncludeModule('linemedia.autoremotesuppliers') && !empty($_REQUEST['sid'])) {
    $supplier_id = strval($_REQUEST['sid']);
    echo LinemediaAutoRemoteSuppliersIblockPropertyApi::showSupplierOptionsForm($supplier_id);
}
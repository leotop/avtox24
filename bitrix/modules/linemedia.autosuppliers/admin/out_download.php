<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.autosuppliers");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$POST_RIGHT = 'W';
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO_MODULE_NOT_INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autosuppliers")) {
    ShowError('LM_AUTOSUPPLIERS_MODULE_NOT_INSTALLED');
    return;
}

if (!CModule::IncludeModule("currency")) {
    ShowError('currency MODULE_NOT_INSTALLED');
    return;
}
if (!CModule::IncludeModule("sale")) {
    ShowError('sale MODULE_NOT_INSTALLED');
    return;
}

$ID = (int) $_GET['ID'];
$request = new LinemediaAutoSuppliersRequest($ID);
$request_data = $request->getArray();
$basket_ids = $request_data['basket_ids'];


/*
* Объект поставщика
*/
$supplier = new LinemediaAutoSupplier($request_data['supplier_id']);


/*
 * Какие есть валюты?
 */
$currencies = array();
$lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
while ($lcur_res = $lcur->Fetch()) {
    $currencies[$lcur_res["CURRENCY"]] = $lcur_res;
}

/*
 * Базовая валюта - валюта поставщика
 */
$base_currency = $supplier->get('currency');//CCurrency::GetBaseCurrency();


/*
 * Получим нужные корзины
 * и список заказов
 */
$result = array();
$dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $basket_ids), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "WEIGHT", 'ORDER_ID', 'NAME'));
while ($basket = $dbBasketItems->Fetch()) {

    $props_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID']));
    while ($prop = $props_res->Fetch()) {
        $basket['PROPS'][$prop['CODE']] = $prop;
    }
    
    $brand_title = $basket['PROPS']['brand_title']['VALUE'];
    $article     = $basket['PROPS']['article']['VALUE'];
    $quantity    = $basket['QUANTITY'];
    
    $price = $basket['PROPS']['base_price']['VALUE'];
    
    
    /*
     * Пересчёт валюты
     */
    if ($basket['CURRENCY'] != '' && $basket['CURRENCY'] != $base_currency) {
	    $price = $price * $currencies[$basket['CURRENCY']]['AMOUNT'];
    }
    
    $result[$brand_title][$article][$price]['quantity'] += $quantity;
    
    $result[$brand_title][$article][$price]['title'] = $basket['NAME'];
}


if (isset($_GET['download'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Transfer-Encoding: binary");
    
    $filename = str_replace(
        array('#NUM#', '#DATE#', '#FROM#', '#SUPPLIER#'),
        array(
            $ID,
            date('Y-m-d G:i', strtotime($request_data['date'])),
            COption::GetOptionString('main', 'site_name'),
            $supplier->get('NAME')
        ),
        GetMessage('LM_AUTO_SUPPLIERS_FILENAME')
    );
    
    $currency_title = $currencies[$base_currency]['CURRENCY'];
    
    
    /*
     * Составление CSV
     */
    if ($_GET['download'] == 'csv') {
        $exporter = new LinemediaAutoSuppliersRequestExporter();
        $exporter->setRequest($request);
        
        header("Content-Type: text/csv; charset=Windows-1251");
        header("Content-Disposition: attachment; filename=\"$filename.csv\";" );
        
        echo $exporter->getCSV();
    }
    
    
    /*
     * Составление XLS
     */
    if ($_GET['download'] == 'xls') {
        $exporter = new LinemediaAutoSuppliersRequestExporter();
        $exporter->setRequest($request);
        
        header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: filename=$filename.xls");
        
        echo $exporter->getHTML();
    }
}

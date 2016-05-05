<?php


/*
* Удаление инфоблоков
*/
if ($_POST['REMOVE_REQUESTS'] == 'Y') {
    global $DB;
    
    $DB->Query('DROP TABLE IF EXISTS b_lm_suppliers_requests_baskets');
    $DB->Query('DROP TABLE IF EXISTS b_lm_suppliers_requests');
    
    //$DB->Query("DELETE FROM b_sale_basket_props WHERE CODE = 'supplier_request_status' ");// нельзя сделать через АПИ
}



if (!$this->UnInstallDB() || !$this->UnInstallEvents() || !$this->UnInstallFiles()) {
    return;
}
UnRegisterModule( $this->MODULE_ID );

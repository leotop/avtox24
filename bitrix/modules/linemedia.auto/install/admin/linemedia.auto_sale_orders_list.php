<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_LIST', 'N') == 'Y') {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/admin/sale_orders_list2.php");
} else {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/admin/sale_orders_list.php");
}

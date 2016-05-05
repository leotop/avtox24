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

CModule::IncludeModule('sale');


$ID = (int) $_GET['ID'];
$request = new LinemediaAutoSuppliersRequest($ID);
$request = $request->getArray();
$basket_ids = $request['basket_ids'];


/*
 * Статусы.
 */
$statuses = array();
$res = CSaleStatus::GetList();
while ($status = $res->Fetch()) {
    $statuses[$status['ID']] = $status;
}



// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIERS_OUT_EDIT_TITLE'));

// jquery
$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.2/jquery.min.js');
$APPLICATION->SetAdditionalCSS('/bitrix/modules/linemedia.autosuppliers/interface/admin.css');

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



/*
 * Получим нужные корзины
 * и список заказов
 */
$users = array();
$orders = array();
$result = array();
$dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $basket_ids), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "WEIGHT", 'ORDER_ID'));
while ($basket = $dbBasketItems->Fetch()) {

    $props_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID']));
    while ($prop = $props_res->Fetch()) {
        $basket['PROPS'][$prop['CODE']] = $prop;
    }
    
    
    /*
     * Получим заказ этой корзины
     */
    $order_id = $basket['ORDER_ID'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = CSaleOrder::GetByID($order_id);
        
        $user_id = $orders[$order_id]['USER_ID'];
        if (!isset($users[$user_id])) {
            $user = CUser::GetByID($user_id);
            $users[$user_id] = $user->Fetch();
            
            $result[$user_id] = $users[$user_id];
        }
    } else {
        $user_id = $orders[$order_id]['USER_ID'];
    }
    $result[$user_id]['ORDERS'][$order_id]['ORDER'] = $orders[$order_id];
    $result[$user_id]['ORDERS'][$order_id]['BASKETS'][] = $basket;
}




/*
 * Распечатка таблицы
 */
echo '<div class="adm-detail-content-item-block lm-auto-suppliers-content-block">';
echo '<div id="lm-auto-suppliers-orders"><table class="lm-auto-suppliers-orders">';
foreach ($result as $user_id => $user) {
    echo '<tr class="user"><th>' . GetMessage('LM_AUTO_SUPPLIERS_USER') . ' ' . $user['EMAIL'] . '</th></tr>';
    foreach ($user['ORDERS'] as $order) {
        echo '<tr>';
            echo '<td>
                <a target="_blank" href="/bitrix/admin/linemedia.auto_sale_orders_list.php?lang='.LANG.'&set_filter=Y&filter_id_from=' . $order['ORDER']['ID'] . '&filter_id_to=' . $order['ORDER']['ID'] . '">' . GetMessage('LM_AUTO_SUPPLIERS_ORDER') . ' N ' . $order['ORDER']['ID'] . '</a></td>';
        echo '</tr>';
       
        echo '<tr>';
            echo '<td>';
                echo '<table class="lm-auto-suppliers-order">';
                echo '<tr  class="lm-auto-suppliers-order-header">';
                echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_BRAND') . '</td>';
                echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_ARTICLE') . '</td>';
                echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_QUANTITY') . '</td>';
                echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_PRICE') . '</td>';
                echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_STATUS') . '</td>';
                echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_SUPPLIER') . '</td>';
                echo '</tr>';
                foreach ($order['BASKETS'] as $basket_id => $basket) {
                    echo '<tr>';
                        echo '<td>' . $basket['PROPS']['brand_title']['VALUE'] . '</td>';
                        echo '<td>' . $basket['PROPS']['article']['VALUE'] . '</td>';
                        echo '<td>' . $basket['QUANTITY'] . '</td>';
                        echo '<td>' . $basket['PRICE'] . '</td>';
                        echo '<td>' . $statuses[$basket['PROPS']['status']['VALUE']]['NAME'] . '</td>';
                        echo '<td>' . $basket['PROPS']['supplier_title']['VALUE'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            echo '</td>';
        echo '</tr>';
    }
}
echo '</table></div>';

    
/*
 * Получим нужные корзины
 * и список поставщиков
 */
$suppliers = array();
$dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $basket_ids, '!ORDER_ID' => false), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "WEIGHT", 'ORDER_ID'));
while ($basket = $dbBasketItems->Fetch()) {

    $props_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID'], 'CODE' => array('article', 'brand_title', 'supplier_id')));
    while ($prop = $props_res->Fetch()) {
        $basket['PROPS'][$prop['CODE']] = $prop;
    }
    
    
    $supplier_id = $basket['PROPS']['supplier_id']['VALUE'];
    if (!isset($suppliers[$supplier_id])) {
        $supplier = new LinemediaAutoSupplier($supplier_id);
        
        $suppliers[$supplier_id] = array(
            'title' => $supplier->get('NAME'),
            'baskets' => array()
        );
    }
    
    
    $part_key = $basket['PROPS']['article']['VALUE'] . '=|=' . $basket['PROPS']['brand_title']['VALUE'];
    
    $suppliers[$supplier_id]['baskets'][$part_key]['quantity'] += $basket['QUANTITY'];
    $suppliers[$supplier_id]['baskets'][$part_key]['ids'][]     = $basket['ID'];
    $suppliers[$supplier_id]['baskets'][$part_key]['article']   = $basket['PROPS']['article']['VALUE'];
    $suppliers[$supplier_id]['baskets'][$part_key]['brand_title']   = $basket['PROPS']['brand_title']['VALUE'];
    
}

/*
 * Распечатаем HTML
 */
?>
<div id="lm-auto-suppliers-request">
<? foreach ($suppliers as $supplier_id => $supplier) { ?>
<h1><?= GetMessage('LM_AUTO_SUPPLIERS_SUPPLIER_REQUEST_TITLE') ?> "<?= $supplier['title'] ?>"</h1>
    <table class="lm-auto-supplier-request">
        
        <tr>
            <th><?=GetMessage('LM_AUTO_SUPPLIERS_BRAND')?></th>
            <th><?=GetMessage('LM_AUTO_SUPPLIERS_ARTICLE')?></th>
            <th colspan="2"><?=GetMessage('LM_AUTO_SUPPLIERS_QANTITY')?></th>
        </tr>
    <? foreach ($supplier['baskets'] as $basket) { ?>
        <? $id = $supplier_id . '-' . $basket['brand_title'] . '-' . $basket['article'];?>
        <? $rand = 'q'.rand(1, 99999999); ?>
        <tr id="<?= $id ?>">
            <td><?= $basket['brand_title'] ?></td>
            <td><?= $basket['article'] ?></td>
            <td><span id="<?=$rand?>" class="quantity" data-max="<?=$basket['quantity']?>"><?=$basket['quantity']?></span>/<?=$basket['quantity']?></td>
        </tr>
    <? } ?>
    </table>
<? } ?>

</div>

</div>

<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

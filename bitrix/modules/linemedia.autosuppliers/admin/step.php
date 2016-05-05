<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autosuppliers/include.php"); // инициализация модуля

IncludeModuleLangFile(__FILE__);
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.autosuppliers");

if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autosuppliers")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}

$ERROR = false;

/*
 * Шаг для заказа.
 */
$step = new LinemediaAutoSuppliersStep($key);

if (empty($step) || strlen($step->get('title')) == 0) {
    ShowError('LM_AUTO_SUPPLIERS_WRONG_STEP');
    return;
}

$POST_RIGHT = 'W';

/*
 * Настройки страницы
 */
$arPageSettings = array(
    'ORDERS_LIST_PAGE' => 'linemedia.auto_sale_orders_list.php',
    'STEP_PAGE' => 'linemedia.autosuppliers_step.php',
    'OUT_HISTORY_PAGE' => 'linemedia.autosuppliers_out_history.php',
);
/*
 * Cоздаём событие
 */
$events = GetModuleEvents('linemedia.autosuppliers', 'OnBeforeStepPageBuild');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arPageSettings));
}

/*
 * Статусы
 */
$statuses = array();
$res = CSaleStatus::GetList();
while ($status = $res->Fetch()) {
    $statuses[$status['ID']] = $status;
}

/*
 * Доступные статусы для просмотра и перевода
 */
if($USER->IsAdmin()) {
    /*
    * Открываем для админа доступ ко всем статусам
    */
    foreach($statuses as &$status) {
        foreach($status as $sKey => $value) {
            if(strpos($sKey, 'PERM_') !== false && $value == 'N') {
                $status[$sKey] = 'Y';
            }
        }
    }
    $arUserStatuses = $statuses;
} else {
    $arUserStatuses = array();
    $dbStatusListAvail = LinemediaAutoProductStatus::getAvailableStatuses("PERM_VIEW", array("PERM_STATUS", "PERM_STATUS_FROM"));
    while($arStatusListAvail = $dbStatusListAvail->GetNext()) {
        $arUserStatuses[$arStatusListAvail['ID']] = $arStatusListAvail;
    }
}
/*
 * Статусы в которые можно переводить
 */
$arUserTransferStatuses = array();
foreach ($arUserStatuses as $id => $status) {
    if($status['PERM_STATUS'] == 'Y' || $USER->IsAdmin()) $arUserTransferStatuses[$id] = $status;
}
/*
 * Статусы из которых можно переводить
 */
$arUserTransferFromStatuses = array();
foreach ($arUserStatuses as $id => $status) {
    if($status['PERM_STATUS_FROM'] == 'Y' || $USER->IsAdmin()) $arUserTransferFromStatuses[$id] = $status;
}

/*
 * Доступы
 */

$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_ORDERS);
$curUserGroup = $USER->GetUserGroupArray();

$maxRole = LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $curUserGroup, $arTasksFilter);

if ($maxRole == 'D') {
    $APPLICATION->AuthForm("ORDERS_ACCESS_DENIED");
}

/*
 * Поставщики.
 */
$suppliers = LinemediaAutoSupplier::GetList();

$arFilter = array('supplier_id' => $find_supplier_id, 'closed' => 'N', 'status'=> $step->get('filter-statuses'));

$filterStatus = $step->get('filter-statuses');
if(count(array_intersect($filterStatus, array_keys($arUserStatuses))) < 1) {
    $APPLICATION->AuthForm("STATUS_" . join('_', $filterStatus) . "_ACCESS_DENIED");
}

//if (IsModuleInstalled('linemedia.autobranches') && !$GLOBALS['USER']->IsAdmin()) {
//    $bUseBranchesModule = true;
//        $cur_val = COption::GetOptionString('linemedia.autobranches', 'LM_AUTO_BRANCHES_MANAGER_ORDER_ACCESS', 'all');
//        if ($cur_val!='all') {
//            $branches_iblock_id = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_IBLOCK_BRANCHES');
//            $arrBranchFilter =  array('IBLOCK_ID'=>$branches_iblock_id,
//                                        array(
//                                            'LOGIC'=>'OR',
//                                            array('PROPERTY_director'=>$GLOBALS['USER']->GetID()),
//                                            array('PROPERTY_managers'=>$GLOBALS['USER']->GetID())
//                                        )
//                                    );
//            $rs = CIBlockElement::GetList(array(), $arrBranchFilter, 0, 0, array('ID'));
//            if ($rs && $rs->SelectedRowsCount() > 0) {
//
//                $branch = $rs->Fetch();
//                $br = new LinemediaAutoBranchesBranch($branch['ID']);
//                /*
//                    убираем скрытых для филиала поставщиков
//                */
//                $hidden = $br->getProperty('hide_suppliers');
//                if (is_array($hidden)) {
//                    foreach ($suppliers as  $k=>$supp) {
//                        if (in_array($supp['ID'], $hidden['VALUE'])) {
//                            unset($suppliers[ $k ]);
//                        }
//                    }
//                }
//                $arBranchFilter = array();
//                if (in_array($cur_val, array('own', 'ownbranch')) && $br->getDirectorID()!==$GLOBALS['USER']->GetID()) {
//
//                    $arBranchFilter['created_by'] = $arFilter['created_by'] = $GLOBALS['USER']->GetID();
//
//                    $rs = CUser::GetList(($by="id"), ($order="asc"), array('UF_MANAGER_ID'=>$GLOBALS['USER']->GetID()));
//                    $uids = array();
//                    while ($u = $rs->Fetch()) {
//                        $uids[] = $u['ID'];
//                    }//while
//                } else if (in_array($cur_val, array('own', 'ownbranch', 'branch')) && $br->getDirectorID()==$GLOBALS['USER']->GetID()) {
//                    $vals = $br->getProperty('managers');
//                    $vals = $vals['VALUE'];
//                    $vals[] = $br->getDirectorID();
//                    $arBranchFilter['created_by'] = $arFilter['created_by'] = $vals; // созданные менеджерами или директором филиала
//
//                    $uids = $br->getBranchesUserIDsList(); // список пользователей привязанных к филиалу
//                }
//            }//if branch fetched
//        }//if not all
//} else {
//    $bUseBranchesModule = false;
//}//no branch module



/*
 * Заявки.
 */
$res = LinemediaAutoSuppliersRequestBasket::GetList(array(), $arFilter);
$items = array();
while ($item = $res->Fetch()) {
    $items []= (int) $item['request_id'];
}
$items = array_unique($items);

$requests = array();
foreach ($items as $request_id) {
    $requests[ $request_id ] = new LinemediaAutoSuppliersRequest($request_id);
}

/*
 * Подтверждение прихода
 */

if (isset($_GET['action'])) {

    if ($_GET['action'] == 'confirm') {

        $isError = false;

        $ids = (array) $_POST['ID'];

        $status = (string) $_POST['status'];

        /*
         * Проверка доступа
         */
        // нельзя переводить в указанный статус
        if(!array_key_exists($status, $arUserStatuses) || $arUserStatuses[$status]['PERM_STATUS'] != 'Y') {
            die(str_replace('#STATUS#', $statuses[$status]['NAME'], GetMessage('LM_AUTO_SUPPLIERS_ERROR_TO_STATUS')));
        }

            $basket_ids = array();
            foreach ($ids as $id => $entry) {
                $b_ids = array_filter(explode(',', strval($entry['basket_ids'])));
                $basket_ids = array_merge($basket_ids, $b_ids);
            }

            // проверка перевода из статуса
            foreach($basket_ids as $basket_id) {
                $basketProps = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket_id, 'CODE' => 'status'));
                if($prop = $basketProps->Fetch()) {
                    $oldStatus = $prop['VALUE'];
                    if($arUserStatuses[$oldStatus]['PERM_STATUS_FROM'] != 'Y') {
                        die(str_replace('#STATUS#', $statuses[$oldStatus]['NAME'], GetMessage('LM_AUTO_SUPPLIERS_ERROR_FROM_STATUS')));
                    }
                }
            }


            $baskets = array();

            $supplier_id = null;

            foreach ($ids as $id => $entry) {

                $quantity = (float) $entry['quantity'];
                $basket_ids = array_filter(explode(',', strval($entry['basket_ids'])));

                if ($quantity <= 0) {
                    continue;
                }
                $id          = explode('|', $id);
                $supplier_id = (int)    $id[0];
                $brand_title = (string) $id[1];
                $article     = (string) $id[2];

                // Количество списывается после каждой корзины.
                $left = $quantity;

                LinemediaAutoDebug::add("supplier_id $supplier_id | brand_title $brand_title | article $article | quantity $quantity", false, LM_AUTO_DEBUG_WARNING);

                $lmCart = new LinemediaAutoBasket();

                /*
                 * Пробежимся по корзинам.
                 */
                $baskets_res = CSaleBasket::GetList(array(), array('ID' => $basket_ids));

                while ($basket = $baskets_res->Fetch()) {
                    /*
                     * Получим список свойств.
                     */
                    $basket['PROPS'] = LinemediaAutoSuppliersRequestBasket::loadBasketProps($basket['ID']);

                    $last_status = $basket['PROPS']['status']['VALUE'];

                    /*
                     * Разрешить изменение корзины
                     */
                    define('LM_AUTO_SUPPLIERS_ALLOW_BASKET_CHANGE_' . $basket['ID'], true);

                    /*
                     * Товар пришёл не весь! Надо разделить корзину.
                     */
                    if ($left < $basket['QUANTITY']) {
                        $diff = array(
                            'PROPS' => array(
                                'status' => array(
                                    'VALUE' => $last_status
                                ),
                                'date_status' => array(
                                    'VALUE' => date('d.m.Y G:i:s')
                                )
                            )
                        );
                        $new_basket_id = LinemediaAutoSuppliersRequestBasket::splitBasket($basket, $left, $diff);

                        /*
                         * Объект корзины изменился, его надо перезагрузить!
                         */
                        $props = LinemediaAutoSuppliersRequestBasket::loadBasketProps($basket['ID']);

                        $lmCart->statusItem($basket['ID'], $status);

                        unset($props['status']);
                        $props['date_status']['VALUE'] = date('d.m.Y G:i:s');
                        LinemediaAutoBasket::setProperty($basket['ID'], array_values($props));

                    } else {
                        /*
                         * $props не инициализировано. возможно правильная работа функционала обусловлена именно передачей одного свойства даты
                         */

                        /*
                         * Товар пришёл весь
                         * Просто обновим значения в БД
                         */
                        $lmCart->statusItem($basket['ID'], $status);

                        //statusItem устанавливает date_status
                        unset($props['status']);
                        $props['date_status']['VALUE'] = date('d.m.Y G:i:s');
                        LinemediaAutoBasket::setProperty($basket['ID'], array_values($props));
                    }

                    $baskets []= $basket;

                    /*
                     * Проверим наличие дупликатов корзин в заказе
                     */
                    LinemediaAutoSuppliersRequestBasket::checkDuplicateBaskets($basket['ORDER_ID']);


                    /*
                     * Спишем остаток и если деталей больше нет - перейдём к обработке следующей детали
                     */
                    $left -= $basket['QUANTITY'];
                    if ($left <= 0) {
                        break;
                    }
                }


                /*
                 * Остались лишние товары!
                 */
                if ($left > 0) {
                    throw new Exception('Supplier [' . $supplier_id . '], brand [' . $brand_title . '], article [' . $article . '] left ' . $left . ' details');
                }
            }


        /*
         * Создание заявки.
         */
        if (
               ($step->get('request') == 'Y' && $_REQUEST['set-request'] == 'Y')
            || ($step->get('mail') == 'Y' && $_REQUEST['set-mail'] == 'Y')
        ) {
            $lmrequest = new LinemediaAutoSuppliersRequest();
            $lmrequest->add($supplier_id, $baskets, $status, $step->get('key'));
        }


        /*
         * Создание письма.
         */
        if ($step->get('mail') == 'Y' && $_REQUEST['set-mail'] == 'Y') {
            // Поставщик
            $supplier = new LinemediaAutoSupplier($supplier_id);

            // Кому отправляем заявку.
            $email = trim($supplier->get('email'));
            if (!empty($email)) {
                // Определим SITE_ID.
                $user = CUser::GetByID($USER->GetID())->Fetch();

                // Создадим файл.
                $file = $lmrequest->saveXLS();

                // Отправка заявки.
                CEvent::Send('LM_AUTO_SUPPLIERS_REQUEST', $user['LID'], array(
                    'EMAIL'     => $email,
                    'ID'        => $lmrequest->getID(),
                    'TIME'      => date('H:i d.m.Y'),
                    'ATTACH'    => $file
                ));
            }
        }


        die('OK');

    }
    exit();
}



/***********************************************************/

$sTableID = "b_lm_suppliers_requests_baskets"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "brand_title", "asc", "by", "order"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// Проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) {
        global $$f;
    }
    return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// Опишем элементы фильтра.
$FilterArr = array(
    "find_supplier_id",
    "find_request_id",
    "find_order_ids",
);

// Инициализируем фильтр.
$lAdmin->InitFilter($FilterArr);

// Если все значения фильтра корректны, обработаем его.
if (CheckFilter()) {
    // Создадим массив фильтрации для выборки LinemediaAutoSuppliersRequestBasket::GetList() на основе значений фильтра.
    $arFilter = array(
        'supplier_id'   => $find_supplier_id,
        'status'        => array_intersect($step->get('filter-statuses'), array_keys($arUserStatuses)),//$step->get('filter-statuses'),
        'request_id'    => $find_request_id,
        'order_ids'     => $find_order_ids,
    );
}



$lmfilter = new LinemediaAutoBasketFilter();

$arRequestFilter = array();
//if ($bUseBranchesModule && !$GLOBALS['USER']->IsAdmin()) {
//    $arRequestFilter = $arBranchFilter;
//    $lmfilter->setUserId($uids);
//}
$lmfilter->setSupplier($arFilter['supplier_id']);

$lmfilter->setStatus($arFilter['status']);

/*
 * Фильтрация для применения прав доступа
 */
$events = GetModuleEvents('linemedia.autosuppliers', 'OnBeforeStepSetFilter');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$lmfilter, &$suppliers, &$maxRole));
}

// Дополнительный фильтр по заявке.
if (!empty($arFilter['request_id'])) {
    if ($find_request_id == 'N') {
        $res = LinemediaAutoSuppliersRequestBasket::GetList(array(), $arRequestFilter);
        $request_basket_ids = array();
        while ($item = $res->Fetch()) {
            $request_basket_ids []= (int) $item['basket_id'];
        }
        $request_basket_ids = array_unique($request_basket_ids);

        $lmfilter->setNotIDs($request_basket_ids);
    } else {
        $arRequestFilter['request_id'] = $find_request_id;
        $res = LinemediaAutoSuppliersRequestBasket::GetList(array(), $arRequestFilter);
        $request_basket_ids = array();
        while ($item = $res->Fetch()) {
            $request_basket_ids []= (int) $item['basket_id'];
        }
        $lmfilter->setIds($request_basket_ids);
    }
}

// Дополнительный фильтр по id.
if (!empty($arFilter['order_ids'])) {
    $ids = explode(',', (string) $arFilter['order_ids']);
    if (!empty($ids)) {
        $lmfilter->setOrderIDs($ids);
    }
}

// ID корзин.
$basket_ids = $lmfilter->filter();

if (count($basket_ids) > 0) {

    // Список корзин.
    $baskets = array();
    $res = CSaleBasket::GetList(array(), array('ID' => $basket_ids));
    while ($basket = $res->Fetch()) {
        $baskets[$basket['ID']] = $basket;
    }

    // Список свойств.
    $res = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket_ids, 'CODE' => array('brand_title', 'article', 'status')), false, false, array('CODE', 'NAME', 'VALUE', 'SORT', 'BASKET_ID'));
    while ($prop = $res->Fetch()) {
        $basket_id = $prop['BASKET_ID'];
        unset($prop['BASKET_ID']);
        $baskets[$basket_id]['PROPS'][$prop['CODE']] = $prop;
    }


    /*
     * Подготовим сгруппированный список
     */
    $result = array();
    foreach ($baskets as $basket) {
        $brand_title = $basket['PROPS']['brand_title']['VALUE'];
        $article     = $basket['PROPS']['article']['VALUE'];
        $quantity    = $basket['QUANTITY'];

        $result[$brand_title][$article]['quantity'] += $quantity;
        $result[$brand_title][$article]['basket_ids'][] = $basket['ID'];
    }

    if (empty($find_supplier_id)) {
        $result = array();
    }
} else {
    $result = array();
}




// Преобразуем список в экземпляр класса CAdminResult.
$rsData = new CAdminResult($result, $sTableID);

// Аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// Отправим вывод переключателя страниц в основной объект $lAdmin.
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_SUPPLIERS_BRANDS_NAV")));


$lAdmin->AddHeaders(array(
    array(
        "id"        => "quantity",
        "content"   => GetMessage("LM_AUTO_SUPPLIERS_QUANTITY"),
        "sort"      => false,
        "default"   => true,
    ),
    array(
        "id"        => "brand_title",
        "content"   => GetMessage("LM_AUTO_SUPPLIERS_BRAND_TITLE"),
        "sort"      => false,
        "default"   => true,
    ),
    array(
        "id"        => "article",
        "content"   => GetMessage("LM_AUTO_SUPPLIERS_ARTICLE"),
        "sort"      => false,
        "default"   => true,
    ),
    array(
        "id"        => "orders",
        "content"   => GetMessage("LM_AUTO_SUPPLIERS_ORDERS"),
        "sort"      => false,
        "default"   => true,
    ),
    array(
        "id"        => "in_requests",
        "content"   => GetMessage("LM_AUTO_SUPPLIERS_IN_REQUESTS"),
        "sort"      => false,
        "default"   => true,
    ),
));

foreach ($result as $brand_title => $articles) {
    foreach ($articles as $article => $article_data) {
        $quantity = $article_data['quantity'];
        $basket_ids = (array) $article_data['basket_ids'];

        $arRes = array(
            'brand_title' => $brand_title,
            'article'     => $article,
            'quantity'    => $quantity,
        );

        // Создаем строку. Результат - экземпляр класса CAdminListRow.
        $ID = $find_supplier_id . '|' . $brand_title . '|' . $article;
        $row =& $lAdmin->AddRow($ID, $arRes);
        $row->AddViewField("quantity", '<input data-basket-ids="' . join(',',$basket_ids) . '" data-max="' . $quantity . '" class="quantity" type="text" size="4" name="quantity[]" value="0" id="' . $ID . '" /> <b>/ ' . $quantity . '</b>');


        /*
         * Список заказов
         */
        $row->AddViewField("orders", '<a target="_blank" href="/bitrix/admin/' . $arPageSettings['ORDERS_LIST_PAGE'] . '?set_filter=Y&filter_ids=' . join(',', $basket_ids) . '&lang=' . LANG . '">' . GetMessage('LM_AUTO_SUPPLIERS_ORDERS_VIEW') . '</a>');


        $res = LinemediaAutoSuppliersRequestBasket::getList(array(), array_merge((array) $arBranchFilter, array('basket_id' => $basket_ids, 'closed' => 'N')));
        $request_ids = array();
        $request_urls = array();
        $request_basket_ids = array();
        while ($request_res = $res->Fetch()) {
            $request_ids []= $request_res['request_id'];
            $request_basket_ids []= $request_res['basket_id'];
        }
        $request_ids = array_unique($request_ids);
        $request_basket_ids = array_unique($request_basket_ids);
        foreach ($request_ids as $request_id) {
            $request_urls []= '<a target="_blank" href="/bitrix/admin/' . $arPageSettings['OUT_HISTORY_PAGE'] . '?set_filter=Y&find_id='.$request_id.'">'.$request_id.'</a>';
        }

        if (count(array_diff($basket_ids, $request_basket_ids)) > 0) {
            $request_urls []= GetMessage('LM_AUTO_SUPPLIERS_NO_IN_REQUESTS');
        }

        /*
         * Участие в заявках
         */
        $row->AddViewField("in_requests", implode(' / ', $request_urls));
    }
}




CUtil::InitJSCore(array('window'));


// Альтернативный вывод
$lAdmin->CheckListMode();


$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.2/jquery.min.js');


foreach ($suppliers as $supplier) {
    if (ToLower($find_supplier_id) == ToLower($supplier['PROPS']['supplier_id']['VALUE']) && !empty($find_supplier_id)) {
        $current_supplier = $supplier;
        break;
    }
}

// Основной поставщик
$mailsupplier = new LinemediaAutoSupplier($find_supplier_id);


// Установим заголовок страницы
if (!empty($current_supplier)) {
    $APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIERS_STOCK_TITLE') . ' "' . $current_supplier['NAME'] . '"');
} else {
    $APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIER_NOT_SELECTED_WARNING'));
}

// Не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


// создадим объект фильтра
$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        GetMessage('LM_AUTO_SUPPLIER'),
        GetMessage('LM_AUTO_REQUEST'),
    )
);


?>

<?= BeginNote() ?>
    <?= GetMessage('LM_AUTO_SUPPLIER_STOCK_NOTE') ?>
    <hr/>
    <?= GetMessage('LM_AUTO_SUPPLIER_STEP_STATUSES') ?>: <br/>
    <ul>
        <? foreach ($step->get('filter-statuses') as $status) { ?>
            <li><b><?= $statuses[$status]['NAME'] ?></b> <? if (!empty($statuses[$status]['DESCRIPTION'])) { ?> (<?= $statuses[$status]['DESCRIPTION'] ?>)<? } ?>
                <? if(!array_key_exists($status, $arUserStatuses)) { ?><span style="color:red"><?=GetMessage('LM_AUTO_SUPPLIERS_NOT_VIEW_STATUS')?></span><? } ?>
        <? } ?>
    </ul>
    <div id="ajax_notice_msg" class="adm-info-message-wrap adm-info-message-green" style="display:none;">
        <div class="adm-info-message">
            <div id="ajax_notice_msg_text" class="adm-info-message-title">
            </div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <? if($ERROR) ShowError($ERROR); ?><span id="ajax_error_msg" style="color:red"></span>
<?= EndNote() ?>

<form name="find_form" id="lm_find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
<input type="hidden" name="set_filter" value="Y" />
<input type="hidden" name="lang" value="<?= LANG ?>" />
<input type="hidden" name="key" id="lm-auto-suppliers-key" value="<?= $key ?>" />
<input type="hidden" name="find_order_ids" value="<?= $find_order_ids ?>" />
<? $oFilter->Begin(); ?>
<tr>
    <td><b><?= GetMessage('LM_AUTO_SUPPLIER') ?></b>:</td>
    <td>
        <select name="find_supplier_id" id="lm_find_supplier_id_filter">
            <option value=""><?= GetMessage('LM_AUTO_SUPPLIER_NOT_SELECTED') ?></option>
            <? foreach ($suppliers as $supplier) { ?>
                <? $id = $supplier['PROPS']['supplier_id']['VALUE']?>
                <option value="<?= $id ?>" <?= ((string) $find_supplier_id == (string) $id) ? 'selected':''?>><?= $supplier['NAME'] ?></option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td><?= GetMessage('LM_AUTO_REQUEST') ?>:</td>
    <td>
        <select name="find_request_id" id="lm_find_request_id_filter">
            <option value=""><?= GetMessage('LM_AUTO_REQUEST_ALL') ?></option>
            <option value="N"><?= GetMessage('LM_AUTO_REQUEST_NOT_REQUEST') ?></option>
            <? foreach ($requests as $request) { ?>
                <option value="<?= $request->get('id') ?>" <?= ($find_request_id == $request->get('id')) ? 'selected':''?>>
                    <?= $request->get('id') ?> <?= GetMessage('LM_AUTO_SUPPLIERS_FOR') ?> <?= $request->get('date') ?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>
<?
$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<? if (!is_array($current_supplier)) { ?>
    <?= CAdminMessage::ShowMessage(GetMessage('LM_AUTO_SUPPLIER_NOT_SELECTED_WARNING')) ?>
<? } ?>

<div id="lm-auto-suppliers-list">
    <? $lAdmin->DisplayList(); ?>
</div>

<?
if(count($arUserTransferStatuses) > 0 && count($arUserTransferFromStatuses) > 0) { ?>
<div class="lm-auto-suppliers-nextstep-block">
    <input type="button" id="lm-auto-suppliers-submit" value="<?= GetMessage('LM_AUTO_SUPPLIERS_CONFIRM') ?>" />
    <select id="lm-auto-suppliers-status" name="lm-auto-suppliers-status">
        <? foreach ($arUserTransferStatuses as $id => $status) { ?>
            <option value="<?= $id ?>" <?= ($id == $step->get('default-status')) ? ('selected') : ('') ?>>
                <?= $status['NAME'] ?> <? if (!empty($statuses[$id]['DESCRIPTION'])) { ?>(<?= $statuses[$id]['DESCRIPTION'] ?>)<? } ?>
            </option>
        <? } ?>
    </select>
    <br/>

    <? if ($step->get('request') == 'Y') { ?>
        <br/>
        <label for="lm-auto-suppliers-set-request">
            <input type="checkbox" id="lm-auto-suppliers-set-request" value="Y" name="set-request" />
            <?= GetMessage('LM_AUTO_SUPPLIERS_REQUEST') ?>
        </label>
    <? } ?>

    <? if ($step->get('mail') == 'Y') { ?>
        <br/>
        <label for="lm-auto-suppliers-set-mail">
            <? $email = trim($mailsupplier->get('email')); ?>
            <input type="checkbox" id="lm-auto-suppliers-set-mail" value="Y" name="set-mail" <?= (empty($email)) ? ('disabled') : ('') ?> />
            <?= GetMessage('LM_AUTO_SUPPLIERS_MAIL') ?>:
        </label>
        &nbsp;
        <? $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'); ?>
        <a target="_blank" href="/bitrix/admin/iblock_element_edit.php?ID=<?= $mailsupplier->get('ID') ?>&type=linemedia_auto&lang=ru&IBLOCK_ID=<?= $supplier_iblock_id ?>" class="<?= (empty($email)) ? ('lm-auto-supplier-not-mail') : ('') ?>">
            <b><?= $mailsupplier->get('NAME') ?></b> (<?= (!empty($email)) ? ($email) : (GetMessage('LM_AUTO_SUPPLIERS_SUPPLIER_NO_EMAIL')) ?>)
        </a>
    <? } ?>
</div>
<? } else { ?>
    <? if(count($arUserTransferStatuses) == 0) { ?><br /><span style="color:red;"><?= GetMessage('LM_AUTO_SUPPLIERS_TRANSFER_STATUS_DENIED') ?></span><? } ?>
    <? if(count($arUserTransferFromStatuses) == 0) { ?><br /><span style="color:red;"><?= GetMessage('LM_AUTO_SUPPLIERS_TRANSFER_STATUS_FROM_DENIED') ?></span><? } ?>
<? } ?>

<div id="lm-auto-suppliers-response"></div>

<script>
    $(document).ready(function() {

        $('select[name="find_supplier_id"]').live('change', function() {
            /*
            Закомментировано, иначе не сохраняются дополнительные фильтры
            без нажатия на кнопку "Найти"
             */
            //$('#lm_find_form').trigger('submit');
        });


        $('#lm-auto-suppliers-submit').click(function() {
            var data = '';
            $('input.quantity').each(function(index, obj) {
                var id = $(obj).attr('id');
                var quantity = parseFloat($(obj).val());
                var basket_ids = $(obj).data('basket-ids');
                var status = $('#lm-auto-suppliers-status').val();
                var key = $('#lm-auto-suppliers-key').val();

                var request = $('#lm-auto-suppliers-set-request').val();
                var mail = $('#lm-auto-suppliers-set-mail').val();

                data += '&ID[' + id + '][quantity]=' + quantity;
                data += '&ID[' + id + '][basket_ids]=' + basket_ids;
                data += '&status=' + status;
                data += '&key=' + key;
                data += '&set-request=' + request;
                data += '&set-mail=' + mail;
            });

            $.ajax({
                type: 'POST',
                url: "/bitrix/admin/<?=$arPageSettings['STEP_PAGE']?>?lang=<?= LANG ?>&action=confirm",
                data: data
            }).done(function(html) {
                if (html == 'OK') {
                    document.location = document.location;
                } else {
                    $('#ajax_error_msg').html(html);
                }
            }).error(function(html) {
                $('#lm-auto-suppliers-response').html(html);
            });
        });


        $('input').live('change', function() {
            var max = parseInt($(this).data('max'));
            var cur = parseInt($(this).val());

            if (cur > max) {
                $(this).val(max);
            }
        });

        $('label.adm-designed-checkbox-label').live('click', function() {
            var id = $(this).attr('for');
            $('#' + id).trigger('click');
        });

        $("#b_lm_suppliers_requests_baskets_filterset_filter").live('click', function() {
            if($("#lm_find_supplier_id_filter").val().length > 0) {
                $(".adm-info-message-red").hide();
            } else {
                $(".adm-info-message-red").show();
            }
        });
    });
</script>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>
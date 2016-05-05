<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $USER;

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

if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}

/*
 * Настройки страницы
 */
$arPageSettings = array(
    'ORDERS_LIST_PAGE' => 'linemedia.auto_sale_orders_list.php',
    'STEP_PAGE' => 'linemedia.autosuppliers_step.php',
    'OUT_DOWNLOAD_PAGE' => 'linemedia.autosuppliers_out_download.php',
);
/*
 * Cоздаём событие
 */
$events = GetModuleEvents('linemedia.autosuppliers', 'OnBeforeOutHistoryPageBuild');
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
 * Доступные статусы для просмотра
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
    $dbStatusListAvail = LinemediaAutoProductStatus::getAvailableStatuses("PERM_VIEW", "PERM_STATUS");
    while($arStatusListAvail = $dbStatusListAvail->GetNext()) {
        $arUserStatuses[$arStatusListAvail['ID']] = $arStatusListAvail;
    }
}

$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_ORDERS);
$curUserGroup = $USER->GetUserGroupArray();

$maxRole = LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $curUserGroup, $arTasksFilter);

if ($maxRole == 'D') {
    $APPLICATION->AuthForm("ORDERS_ACCESS_DENIED");
}

// Список всех поставщиков.
$suppliers = LinemediaAutoSupplier::GetList();

// --------------- Вынесено в обработчик события
///*
//*   если есть модуль филиалов и просматривающий пользователь не админ, а менеджер или директор филиала, то у нас:
//    1) могут быть не все поставщики видны, часть может быть скрыта для филиала
//    2) не все заявки видны, а или только свои(если менеджер и опция видимости "только свои заказы") или всех заказов филиала(если директор или менеджер, но опция видимости "филиал")
//*/
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
//            $rs = CIBlockElement::GetList(array(), $arrBranchFilter,0,0, array('ID'));
//            if ($rs && $rs->SelectedRowsCount() > 0) {
//
//                $branch = $rs->Fetch();
//                $br = new LinemediaAutoBranchesBranch($branch['ID']);
//                /*
//                    убираем скрытых для филиала поставщиков
//                */
//                $hidden = $br->getProperty('hide_suppliers');
//                if (is_array($hidden)) {
//                    foreach ($hidden['VALUE'] as $val) {
//                        unset($suppliers[ $val ]);
//                    }
//                }
//
//                $arBranchFilter = array();
//                /*
//                    если пользователь менеджер и настроено,что он видит только свои заказы, то  показываем ему только его заявки.
//                    если же видимость заказов настроена в пределах филиала, то показываем заявки,созданные любым менеджером филиала.
//                    это проще,чем дофильтровывать по заказам от пользователей филиала, а результат не должен отличаться.
//                */
//                if (in_array($cur_val, array('own', 'ownbranch')) && $br->getDirectorID()!==$GLOBALS['USER']->GetID()) {
//                    $arBranchFilter['user_id'] = $GLOBALS['USER']->GetID();
//                } else if (in_array($cur_val, array('own', 'ownbranch', 'branch')) && $br->getDirectorID()==$GLOBALS['USER']->GetID()) {
//                    $vals = $br->getProperty('managers');
//                    $vals = $vals['VALUE'];
//                    $vals[] = $br->getDirectorID();
//                    $arBranchFilter['user_id'] = $vals;
//                }
//
//            }//if selected
//
//        }//if !all
//} else {
//    $bUseBranchesModule = false;
//}

/***********************************************************/
$sTableID = "b_lm_suppliers_requests"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "supplier_id", "asc", "by", "order"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// опишем элементы фильтра
$FilterArr = Array(
	"find_status",
	"find_id",
	"find_supplier_id",
	"filter_closed",
	"filter_ids",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if (CheckFilter()) {
	// создадим массив фильтрации для выборки LinemediaAutoSuppliersRequest::GetList() на основе значений фильтра
	$arFilter = array(
		"status"      => $find_status,
		"id"          => $find_id,
		"supplier_id" => $find_supplier_id,
		"closed"      => $filter_closed,
	);
}

if (!empty($filter_ids)) {
    $arFilter['id'] = explode(',', $filter_ids);
}

/*
 * Cоздаём событие
 */
$events = GetModuleEvents('linemedia.autosuppliers', 'OnBeforeHistorySetFilter');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arFilter, &$suppliers, &$maxRole));
}

// --------------- Вынесено в обработчик события
//if ($bUseBranchesModule) { //если есть модуль филиалов, то дополняем фильтр условиями по видимости поставщиков и привязке к менегерам
//    $ALLOWED_SUPPLIER_IDS = array();
//    foreach ($suppliers as $supp) {
//        $ALLOWED_SUPPLIER_IDS[] = $supp['PROPS']['supplier_id']['VALUE'];
//    }
//    if (!empty($arFilter['supplier_id'])) {
//        $arFilter['supplier_id'] = array_intersect((array)$arFilter['supplier_id'], $ALLOWED_SUPPLIER_IDS);
//    } else {
//        $arFilter['supplier_id'] = $ALLOWED_SUPPLIER_IDS;
//    }
//    // проверка на пустой массив
//    if(count($arFilter['supplier_id']) < 1) $arFilter['supplier_id'] = '-1'; // фактически запрещаем выборку
//
//    $arFilter = array_merge((array)$arFilter, (array)$arBranchFilter);
//}


// обработка одиночных и групповых действий
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    // если выбрано "Для всех элементов"
    if ($_REQUEST['action_target'] == 'selected') {
        $cData = new LinemediaAutoSuppliersRequest();
        $rsData = $cData->GetList(array($by => $order), $arFilter);
        while ($arRes = $rsData->Fetch()) {
            $arID[] = $arRes['ID'];
        }
    }

    // пройдем по списку элементов
    foreach ($arID as $ID) {
        if (strlen($ID)<=0) {
            continue;
        }
        $ID = IntVal($ID);

        // для каждого элемента совершим требуемое действие
        switch ($_REQUEST['action']) {
            // удаление
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                if (!LinemediaAutoSuppliersRequest::Delete($ID)) {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(GetMessage("rub_del_err"), $ID);
                }
                $DB->Commit();
                break;
        }
    }
}

/*
 * Проверим доступ к статусам
 */
if(count($arUserStatuses) < 1) {
    $arFilter['status'] = array(-1);
} else {
    if(array_key_exists('status', $arFilter) && $arFilter['status']) {
        $arFilter['status'] = array_intersect($arFilter['status'], array_keys($arUserStatuses));
    } else {
        $arFilter['status'] = array_keys($arUserStatuses);
    }
}

// Выберем список.
$cData = new LinemediaAutoSuppliersRequest();
$rsData = $cData->GetList(array($by => $order), $arFilter);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_SUPPLIERS_BRANDS_NAV")));




$lAdmin->AddHeaders(array(
  array(  "id"    =>"id",
    "content"  => "ID",
    "sort"     => "id",
    "default"  => true,
  ),
  array(  "id"    =>"supplier_id",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_SUPPLIER"),
    "sort"     => "supplier_id",
    "default"  => true,
  ),
  array(  "id"    =>"basket_count",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_BASKET_COUNT"),
    "sort"     => "basket_count",
    "default"  => true,
  ),
  array(  "id"    =>"date",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_DATE"),
    "sort"     => "date",
    "default"  => true,
  ),
  array(  "id"    =>"user_id",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_USER"),
    "sort"     => "user_id",
    "default"  => true,
  ),
  array(  "id"    =>"closed",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_CLOSED"),
    "sort"     => "closed",
    "default"  => false,
  ),
  array(  "id"    =>"status",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_STATUS"),
    "sort"     => "status",
    "default"  => false,
  ),
  array(  "id"    =>"note",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_NOTE"),
    "sort"     => false,
    "default"  => false,
  ),
));

if(!function_exists('format_by_count')) {
    function format_by_count($count, $form1, $form2, $form3)
    {
        $count = abs($count) % 100;
        $lcount = $count % 10;
        if ($count >= 11 && $count <= 19) return($form3);
        if ($lcount >= 2 && $lcount <= 4) return($form2);
        if ($lcount == 1) return($form1);
        return $form3;
    }
}


$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

$suppliers_cache = array();

while ($arRes = $rsData->NavNext(true, "f_")) {

    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_id, $arRes);

    $row->AddViewField("status", $statuses[$f_status]['NAME']);

    $supplier = isset($suppliers_cache[$f_supplier_id]) ? $suppliers_cache[$f_supplier_id] : new LinemediaAutoSupplier($f_supplier_id);
    $suppliers_cache[$f_supplier_id] = $supplier;

    $row->AddViewField("supplier_id", "<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier->get('ID') . "&type=linemedia_auto&lang=" . LANG . "&IBLOCK_ID=$suppliers_iblock_id'>[$f_supplier_id]</a> " . $supplier->get('NAME'));

    $U = CUser::GetByID($arRes['user_id']);
    $U = $U->Fetch();
    //проверим права доступа к пользователям
    if($USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('edit_all_users')) {
        $row->AddViewField("user_id", "<a href='/bitrix/admin/user_edit.php?lang=".LANG."&ID=$f_user_id'>[$f_user_id]</a> " . $U['LOGIN']);
    } else {
        $row->AddViewField("user_id", $U['LOGIN']);
    }


    //$row->AddViewField("orders", '<a target="_blank" href="/bitrix/admin/linemedia.auto_sale_orders_list.php?set_filter=Y&filter_ids=' . join(',', $basket_ids) . '&lang=' . LANG . '">' . GetMessage('LM_AUTO_SUPPLIERS_ORDERS_VIEW') . '</a>');
    //$row->AddViewField("basket_count", intval($f_basket_count) . ' ' . GetMessage('LM_AUTO_SUPPLIERS_GOODS') . " <a href='/bitrix/admin/linemedia.autosuppliers_out_edit.php?ID=$f_id&lang=".LANG."'>" . GetMessage('LM_AUTO_SUPPLIERS_EDIT_REQUEST') . "</a>");

    $row->AddViewField("basket_count", intval($f_basket_count).' '.format_by_count(intval($f_basket_count), GetMessage('LM_AUTO_SUPPLIERS_GOODS_1'), GetMessage('LM_AUTO_SUPPLIERS_GOODS_2'), GetMessage('LM_AUTO_SUPPLIERS_GOODS')).' <a target="_blank" href="/bitrix/admin/' . $arPageSettings['ORDERS_LIST_PAGE'] . '?set_filter=Y&filter_ids=' .$f_basket_ids.'&lang='.LANG.'">'.GetMessage('LM_AUTO_SUPPLIERS_EDIT_REQUEST')."</a>");

    $row->AddViewField("closed", ($arRes['closed'] == 'Y') ? (GetMessage('LM_AUTO_SUPPLIERS_YES')) : (GetMessage('LM_AUTO_SUPPLIERS_NO')));

    $row->AddViewField("note", htmlspecialchars($arRes['note']));


    $nextstep = LinemediaAutoSuppliersStep::getNextStepByKey($arRes['step']);
    // Сформируем контекстное меню.
    $arActions = Array();

    if ($POST_RIGHT >= "W") {
        if ($nextstep) {
            $arActions[] = array(
                "ICON"   => "move",
                "TEXT"   => GetMessage("LM_AUTO_SUPPLIERS_STEP").' "'.$nextstep->get('title').'"',
                "ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/".$arPageSettings['STEP_PAGE']."?set_filter=Y&key=".$nextstep->get('key')."&find_supplier_id=".$f_supplier_id."&find_request_id=".$f_id."&lang=" . LANG),
                "DEFAULT" => true
            );
        }
        $arActions []= array(
            "ICON"   => "btn_download",
            "TEXT"   => GetMessage("LM_AUTO_SUPPLIERS_DOWNLOAD_CSV"),
            "ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/".$arPageSettings['OUT_DOWNLOAD_PAGE']."?ID=$f_id&download=csv&lang=" . LANG),
        );
        $arActions []= array(
            "ICON"    => "btn_download",
            "TEXT"    => GetMessage("LM_AUTO_SUPPLIERS_DOWNLOAD_XLS"),
            "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/".$arPageSettings['OUT_DOWNLOAD_PAGE']."?ID=$f_id&download=xls&lang=" . LANG),
        );
        $arActions []= array(
            "ICON"   => "delete",
            "TEXT"   => GetMessage("LM_AUTO_SUPPLIERS_DELETE"),
            "ACTION" => "if(confirm('".GetMessage("CONFIRM_DELETE")."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
        );
    }
    $row->AddActions($arActions);

}


// резюме таблицы
$lAdmin->AddFooter(
    array(
        array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
        array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
    )
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
    "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
));

// сформируем меню из одного пункта - добавление рассылки
/*
$aContext = array(
    array(
        "TEXT"          => GetMessage("LM_AUTO_SUPPLIERS_ADD_REQUEST"),
        "LINK"          => "/bitrix/admin/linemedia.autosuppliers_out.php?lang=" . LANG,
        "supplier_id"   => GetMessage("LM_AUTO_SUPPLIERS_ADD_REQUEST"),
        "ICON"          => "btn_new",
    ),
);
*/
// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext, false, true);


CUtil::InitJSCore(array('window'));


// альтернативный вывод
$lAdmin->CheckListMode();


// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIERS_TITLE'));


// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



//$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.7.1/jquery.min.js');
//$APPLICATION->AddHeadScript('/bitrix/modules/linemedia.autoto/interface/script.js');


// создадим объект фильтра
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
    GetMessage("LM_AUTO_SUPPLIERS_STATUS"),
    GetMessage("LM_AUTO_SUPPLIERS_SUPPLIER"),
    GetMessage("LM_AUTO_SUPPLIERS_CLOSED"),
  )
);
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<input type="hidden" name="filter_ids" size="4" value="<?= htmlspecialchars($filter_ids)?>" />
<? $oFilter->Begin(); ?>
<tr>
  <td><?= "ID" ?>:</td>
  <td>
    <input type="text" name="find_id" size="4" value="<?= htmlspecialchars($find_id)?>" />
  </td>
</tr>
<tr>
  <td><?= GetMessage('LM_AUTO_SUPPLIERS_STATUS') ?>:</td>
  <td>
	  <select name="find_status[]" multiple size="3">
		  <?
		  foreach ($arUserStatuses as $arStatusList) {
			  ?><option value="<?= htmlspecialchars($arStatusList["ID"]) ?>"<?if (is_array($find_status) && in_array($arStatusList["ID"], $find_status)) echo " selected"?>>[<?= htmlspecialchars($arStatusList["ID"]) ?>] <?= htmlspecialcharsEx($arStatusList["NAME"]) ?></option><?
		  }
		  ?>
	  </select>
  </td>
</tr>
<tr>
    <td><?= GetMessage("LM_AUTO_SUPPLIERS_SUPPLIER").":" ?></td>
    <td>
        <select name="find_supplier_id" id="lm_find_supplier_id_filter">
            <option value=""><?= GetMessage('LM_AUTO_SUPPLIER_NOT_SELECTED') ?></option>
            <? foreach ($suppliers as $supplier) { ?>
                <? $id = $supplier['PROPS']['supplier_id']['VALUE'] ?>
                <option value="<?= $id ?>" <?= ($find_supplier_id == $id) ? 'selected' : '' ?>>
                    <?= $supplier['NAME'] ?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td><?= GetMessage("LM_AUTO_SUPPLIERS_CLOSED") ?>:</td>
    <td>
        <select name="filter_closed">
            <option value="" <?= ($filter_closed == '') ? ('selected') : ('') ?>>
                (<?= GetMessage('LM_AUTO_SUPPLIERS_ALL') ?>)
            </option>
            <option value="Y" <?= ($filter_closed == 'Y') ? ('selected') : ('') ?>>
                <?= GetMessage('LM_AUTO_SUPPLIERS_YES') ?>
            </option>
            <option value="N" <?= ($filter_closed == 'N') ? ('selected') : ('') ?>>
                <?= GetMessage('LM_AUTO_SUPPLIERS_NO') ?>
            </option>
        </select>
    </td>
</tr>
<?
$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>


<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();

require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');


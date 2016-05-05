<?php 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $USER, $APPLICATION;

IncludeModuleLangFile(__FILE__);
CJSCore::Init(array('jquery', 'window', 'ajax'));


$autoModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");


if ($autoModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_STATISTICS);
$curUserGroup = $USER->GetUserGroupArray();

$maxRole = LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $curUserGroup, $arTasksFilter);

if ($maxRole == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO_MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule('iblock')) {
    ShowError('LM_AUTO_IBLOCK_MODULE_NOT_INSTALLED');
    return;
}


if (empty($USER)) {
    $USER = new CUser();
}


$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIER_REFUSAL_STAT_LIST_TITLE'));
$APPLICATION->AddHeadScript('https://www.google.com/jsapi');
$APPLICATION->AddHeadScript('/bitrix/themes/.default/interface/supplier_ref_stat.js');

$events = GetModuleEvents('linemedia.auto', 'OnBeforeSetPageTitle');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array());
}



$sTableID = 'b_lm_supplier';

$oSort = new CAdminSorting($sTableID, 'name', 'DESC', 'sortBy', 'sortOrder');
$lAdmin = new CAdminList($sTableID, $oSort);



$setOfSuppliersStatistic = array();
$setOfSupplierId = array();

foreach (array_values(LinemediaAutoSupplier::GetList()) as $supplier) {

    $setOfSuppliersStatistic[] = array_merge(
        array('name' => $supplier['NAME']), LinemediaAutoSupplier::getStat($supplier['PROPS']['supplier_id']['VALUE'])
    );

    $setOfSupplierId[] = $supplier['PROPS']['supplier_id']['VALUE'];
}



foreach ($setOfSuppliersStatistic as &$supplier) {

    unset($supplier['delivery_time']);

    if (!key_exists('rejected', $supplier)) {
    	$supplier['rejected'] = 0;
    }

    if (!key_exists('completed', $supplier)) {
        $supplier['completed'] = 0;
    }

}



if (is_set($_REQUEST['sortBy'])) {

    usort($setOfSuppliersStatistic, function ($item1, $item2) use ($sortBy, $sortOrder) {

        if ($sortOrder == 'asc') {

            if ($item1[$sortBy] == $item2[$sortBy]) {
                return 0;
            }
            return $item1[$sortBy] > $item2[$sortBy] ? 1 : -1;
        }
        elseif ($sortOrder == 'desc') {

            if ($item1[$sortBy] == $item2[$sortBy]) {
                return 0;
            }
            return $item1[$sortBy] < $item2[$sortBy] ? 1 : -1;
        }

    });
}


$arFilterFields = array(
    'filter_supplier',
    'filter_refused',
    'filter_accomplished',
);

$lAdmin->InitFilter($arFilterFields);

$filterSupplier = array(
    'name' => (string) trim($_GET['filter_supplier']),
    'rejected' => (int) trim($_GET['filter_refused']),
    'completed' => (int) trim($_GET['filter_accomplished'])
);

foreach ($filterSupplier as $key => $filter) {
    if ($filter == null) {
    	unset($filterSupplier[$key]);
    }
}


if ($_REQUEST['set_filter'] == 'Y' && count($filterSupplier) > 0)  {
    // создадим массив фильтрации для выборки на основе значений фильтра

    $filteredSuppliersStatistic = array();

    foreach ($setOfSuppliersStatistic as $supplier) {

        if (count(array_intersect_assoc($supplier, $filterSupplier)) != count($filterSupplier)) {
            continue;
        }

        $filteredSuppliersStatistic[] = $supplier;
    }

    $setOfSuppliersStatistic = $filteredSuppliersStatistic;
    unset($filteredSuppliersStatistic);

}


/*
 * Выводимые поля заказа.
*/
$arHeaders = array(
    array('id' => 'name', 'content' => GetMessage('LM_AUTO_TABLE_SUPPLIER_HEADER'), 'sort' => 'name', 'default' => true),
    array('id' => 'rejected',  'content' => GetMessage('LM_AUTO_TABLE_REFUSED_HEADER'),  'sort' => 'rejected', 'default' => true),
    array('id' => 'completed', 'content' => GetMessage('LM_AUTO_TABLE_ACCOMPLISHED_HEADER'), 'sort' => 'completed', 'default' => true),
    array('id' => 'detailed', 'content' => GetMessage('LM_AUTO_TABLE_DETAILED_HEADER'), 'sort' => 'detailed', 'default' => true),
);


$lAdmin->AddHeaders($arHeaders);

$randomId = 1215;

for (reset($setOfSuppliersStatistic), reset($setOfSupplierId); current($setOfSuppliersStatistic) != null; next($setOfSupplierId), next($setOfSuppliersStatistic)) {

    $item = current($setOfSuppliersStatistic);
    $item['detailed'] = '';

    $row = &$lAdmin->AddRow(current($setOfSupplierId), $item);

    $url = '/bitrix/components/linemedia.auto/supplier.reliability.statistic/templates/.default/ajax.php?supplier_id='.current($setOfSupplierId);

    $row->AddViewField('detailed', "<a href='javascript:void(0);' onclick='showRSRD(this, ".$randomId.");' data-url='$url' title='Статистика отказов поставщика'>".GetMessage('LM_AUTO_TABLE_DETAILED_TITLE')."</a>");


    // сформируем контекстное меню
    $arActions = array();

    // применим контекстное меню к строке
    $row->AddActions($arActions);
}



$lAdmin->AddAdminContextMenu(array(), false, true);

$events = GetModuleEvents('linemedia.auto', 'OnBeforeProductsPageAdd');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$lAdmin, $sTableID));
}



$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");?>


<form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>">
<?
$arFilterFieldsTmp = array(
    GetMessage("SEARCH_ARTICLE"),
    GetMessage("SEARCH_BRAND_TITLE"),
);

$oFilter = new CAdminFilter(
    $sTableID."_filter",
    $arFilterFieldsTmp
);


$oFilter->Begin();
?>

<tr>
    <td><?=GetMessage("SEARCH_SUPPLIER")?>:</td>
    <td>
        <input type="text" name="filter_supplier" size="47" value="<?echo htmlspecialchars($filterSupplier['name'])?>">
    </td>
</tr>

<tr>
    <td><?=GetMessage("SEARCH_REFUSED")?>:</td>
    <td>
        <input type="text" name="filter_refused" size="47" value="<?echo htmlspecialchars($filterSupplier['rejected'])?>">
    </td>
</tr>

<tr>
    <td><?=GetMessage("SEARCH_ACCOMPLISHED")?>:</td>
    <td>
        <input type="text" name="filter_accomplished" size="47" value="<?echo htmlspecialchars($filterSupplier['completed'])?>">
    </td>
</tr>


<?
$oFilter->Buttons(
    array(
        "table_id" => $sTableID,
        "url" => $APPLICATION->GetCurPage(),
        "form" => "find_form"
    )
);
$oFilter->End();
?>

</form>

<? $lAdmin->DisplayList(); ?>

<?php require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>





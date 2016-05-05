<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$autoModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

if ($autoModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

IncludeModuleLangFile(__FILE__);
CJSCore::Init(array('jquery', 'window', 'ajax'));

$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_STATISTICS);
$curUserGroup = $USER->GetUserGroupArray();

$maxRole = LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $curUserGroup, $arTasksFilter);

if ($maxRole == 'D') {
    $APPLICATION->AuthForm("STATISTIC_ACCESS_DENIED");
}

global $USER;

if (empty($USER)) {
    $USER = new CUser();
}


$APPLICATION->SetTitle(GetMessage('LM_AUTO_SEARCH_STATISTICS_LIST_TITLE'));

$sTableID = "b_lm_search_statistics";

$oSort = new CAdminSorting($sTableID, 'requests', 'DESC', 'sOrBy', 'sOrOrder');
$lAdmin = new CAdminList($sTableID, $oSort);

// �������� �������� ������� ��� �������� ������� � ��������� �������
function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) {
        global $$f;
    }

    /*
       ����� ��������� �������� ���������� $find_��� �, � ������ ������������� ������,
       �������� $lAdmin->AddFilterError("�����_������").
    */

    return count($lAdmin->arFilterErrors) == 0; // ���� ������ ����, ������ false;
}

$arFilterFields = array(
    "filter_article",
    "filter_brand_title",
    "filter_supplier_id",
    "filter_date_from",
    "filter_date_to",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

$filter_article = (string) $_GET['filter_article'] ?  (string) trim($_GET['filter_article']) : '';
$filter_brand_title = (string) $_GET['filter_brand_title'] ? (string) trim($_GET['filter_brand_title']) : '';
$filter_supplier = (string) $_GET['filter_supplier_id'] ? (string) trim($_GET['filter_supplier_id']) : '';
$filter_date_from = strlen($_GET['filter_date_from']) > 0 ? (string) $_GET['filter_date_from'] : '';
$filter_date_to = strlen($_GET['filter_date_to']) > 0 ? (string) $_GET['filter_date_to'] : '';

// ���� ��� �������� ������� ���������, ���������� ���
if ( CheckFilter() ) {
    // �������� ������ ���������� ��� ������� �� ������ �������� �������
    $arFilter = Array(
        "article"	    => $filter_article,
        "brand_title" => $filter_brand_title,
        "supplier_id" => $filter_supplier,
        "added >"  => $filter_date_from,
        "added <"     => $filter_date_to
    );
}

/*
 * ����������
 */
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList();
foreach ($suppliers_res as $supplier) {
    $suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}

// ������� ������ �������.
$rsData = LinemediaAutoSearchStatistics::getFormatList(
    array($sOrBy => $sOrOrder),
    $arFilter
);


// ����������� ������ � ��������� ������ CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// ���������� CDBResult �������������� ������������ ���������.
$rsData->NavStart();

// �������� ����� ������������� ������� � �������� ������ $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('LM_AUTO_SEARCH_STATISTICS_LIST')));


/*
 * ��������� ���� ������.
 */
$arHeaders = array(
    array('id' => 'article',               'content' => GetMessage('ARTICLE'),                  'sort' => 'article',               'default' => true),
    array('id' => 'brand_title',          'content' => GetMessage('BRAND_TITLE'),            'sort' => 'brand_title',          'default' => true),
    array('id' => 'supplier_id',          'content' => GetMessage('SUPPLIER'),            'sort' => 'supplier_id',          'default' => true),
    array('id' => 'requests',             'content' => GetMessage('REQUESTS'),                'sort' => 'requests',             'default' => true),
    array('id' => 'good_requests',       'content' => GetMessage('GOOD_REQUESTS'),        'sort' => 'good_requests',       'default' => true),
    array('id' => 'analog_exist',         'content' => GetMessage('ANALOGS_EXIST'),        'sort' => 'analog_exist',         'default' => true),
    array('id' => 'avg_analogs',          'content' => GetMessage('AVG_ANALOGS'),          'sort' => 'avg_analogs',          'default' => true),
    array('id' => 'article_found',        'content' => GetMessage('ARTICLE_FOUND'),        'sort' => 'article_found',        'default' => true),
    array('id' => 'article_not_found',   'content' => GetMessage('ARTICLE_NOT_FOUND'),  'sort' => 'article_not_found',    'default' => false),
);


$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(false)) {

    // ������� ������. ��������� - ��������� ������ CAdminListRow
    $row =& $lAdmin->AddRow($arRes['ID'], $arRes);

    if(!empty($arRes['supplier_id'])) {
        $supplier = $suppliers[$arRes['supplier_id']];
        $row->AddViewField("supplier_id", "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>" . $arRes['supplier_id'] . "</a>] " . $supplier['NAME']);
    }

    // ���������� ����������� ����
    $arActions = Array();


    // �������� ����������� ���� � ������
    $row->AddActions($arActions);
}

// ���������� ������������ ����.
$lAdmin->AddAdminContextMenu(array(), false, true);

/*
// ������ �������
$lAdmin->AddFooter(
    array(
        array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // ���-�� ���������
        array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // ������� ��������� ���������
    )
);
*/

// �������������� �����
$lAdmin->CheckListMode();


require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");


?>


<form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
<?
$arFilterFieldsTmp = array(
    GetMessage("SEARCH_DATE"),
    GetMessage("SEARCH_ARTICLE"),
    GetMessage("SEARCH_BRAND_TITLE"),
    GetMessage("SEARCH_SUPPLIER_TITLE"),

);

$oFilter = new CAdminFilter(
    $sTableID."_filter",
    $arFilterFieldsTmp
);


$oFilter->Begin();
?>

<tr>
    <td><b><?=GetMessage("SEARCH_DATE")?>:</b></td>
    <td>
        <?= CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y") ?>
    </td>
</tr>

<tr>
    <td><?=GetMessage("SEARCH_ARTICLE")?>:</td>
    <td>
        <input type="text" name="filter_article" size="47" value="<?echo htmlspecialchars($filter_article)?>">
    </td>
</tr>

<tr>
    <td><?=GetMessage("SEARCH_BRAND_TITLE")?>:</td>
    <td>
        <input type="text" name="filter_brand_title" size="47" value="<?echo htmlspecialchars($filter_brand_title)?>">
    </td>
</tr>

<tr>
    <td><?=GetMessage("SEARCH_SUPPLIER_TITLE")?>:</td>
    <td>
        <input type="text" name="filter_supplier_id" size="47" value="<?echo htmlspecialchars($filter_supplier)?>">
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

<!-- ������ -->
<? $lAdmin->DisplayList(); ?>


<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>

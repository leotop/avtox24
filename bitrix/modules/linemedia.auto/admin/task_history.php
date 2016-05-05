<?php
/**
 * Linemedia Autoportal
 * Main module
 * История импорта
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 *
 * http://auto.x.linemedia.ru/bitrix/admin/
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

$last_ids = LinemediaAutoImportHistory::getLastImportIds();

if(isset($_REQUEST['action']) && in_array($_REQUEST['ID'], $last_ids)) {	
	switch($_REQUEST['action']) {
				
		case "make_incorrect":			
			$supplier_code = LinemediaAutoImportHistory::getSupplierId($_REQUEST['ID']);
			LinemediaAutoImportHistory::setCorrectnessPriceList($supplier_code, 1, true, 'N', true);			
			break;
		
		case "make_correct":
			$supplier_code = LinemediaAutoImportHistory::getSupplierId($_REQUEST['ID']);
			LinemediaAutoImportHistory::setCorrectnessPriceList($supplier_code, 0, true, 'Y', true);		
			break;		
	}
}


$table_id = "b_lm_import_history";

$oSort = new CAdminSorting($table_id, "ID", "desc");
$lAdmin = new CAdminList($table_id, $oSort);

$arFilterFields = array(
    "filter_task_id",
    "filter_supplier_id",
    "filter_date_from",
    "filter_date_to",
);

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if (IntVal($filter_task_id) > 0) $filter["TASK_ID"] = IntVal($filter_task_id);
if (strlen($filter_supplier_id) > 0) $filter["SUPPLIER_ID"] = $filter_supplier_id;
if (strlen($filter_date_from)>0) $filter[">=DATE"] = Trim($filter_date_from);
if (strlen($filter_date_to)>0) $filter["<=DATE"] = Trim($filter_date_to);

/*
 * Поставщики
 */
$suppliers = LinemediaAutoSupplier::GetList(array(), array(), false, false, array(), 'supplier_id');
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

// Выберем список.
$res = LinemediaAutoImportHistory::getList($order, $filter);
// Преобразуем список в экземпляр класса CAdminResult.
$res = new CAdminResult($res, $table_id);
// Аналогично CDBResult инициализируем постраничную навигацию.
$res->NavStart();
// Отправим вывод переключателя страниц в основной объект $lAdmin.
$lAdmin->NavText($res->GetNavPrint(GetMessage("LM_AUTO_MODELS_NAV")));


$lAdmin->AddHeaders(array(
    array(
        "id"       => "ID",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_ID"),
        "sort"     => "id",
        "default"  => true,
    ),
    array(
        "id"       => "SUPPLIER_ID",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_SUPPLIER_ID"),
        "sort"     => "SUPPLIER_ID",
        "default"  => true,
    ),
    array(
        "id"       => "TASK_ID",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_TASK_ID"),
        "sort"     => "TASK_ID",
        "default"  => true,
    ),
    array(
        "id"       => "PARTS_COUNT",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_PARTS_COUNT"),
        "sort"     => "PARTS_COUNT",
        "default"  => true,
    ),
    array(
        "id"       => "PARTS_DIFF",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_PARTS_DIFF"),
        "sort"     => "PARTS_DIFF",
        "default"  => true,
    ),
    array(
        "id"       => "SUM_PRICE",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_SUM_PRICE"),
        "sort"     => "SUM_PRICE",
        "default"  => true,
    ),
    array(
        "id"       => "SUM_DIFF",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_SUM_DIFF"),
        "sort"     => "SUM_DIFF",
        "default"  => true,
    ),
    array(
        "id"       => "DATE",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_DATE"),
        "sort"     => "DATE",
        "default"  => true,
    ),
	array(
        "id"       => "CORRECT_IMPORT",
        "content"  => GetMessage("LM_AUTO_IMPORT_HISTORY_CORRECT_IMPORT_HEADER"),
        "sort"     => "CORRECT_IMPORT",
        "default"  => true,
    ),
));

while ($fields = $res->NavNext(true, "f_"))
{
    $row = &$lAdmin->AddRow($f_ID, $fields);
			
    $row->AddViewField("ID", $f_ID);
    $row->AddViewField("TASK_ID", $f_TASK_ID);
    $row->AddViewField("SUPPLIER_ID", $f_SUPPLIER_ID);
    $row->AddViewField("PARTS_COUNT", $f_PARTS_COUNT);
    $row->AddViewField("SUM_PRICE", number_format($f_SUM_PRICE, 0, '.', ' '));
    $row->AddViewField("PARTS_DIFF", intval($f_PARTS_DIFF) > 0 ? abs(100 - $f_PARTS_DIFF) . '%' : '-');
    $row->AddViewField("SUM_DIFF", intval($f_SUM_DIFF) > 0 ? abs(100 - $f_SUM_DIFF) . '%' : '-');
    $row->AddViewField("DATE", $f_DATE);
    $row->AddViewField("CORRECT_IMPORT", intval($f_CORRECT_IMPORT) == 0 ? '<span style="color:green;">'.GetMessage("LM_AUTO_IMPORT_HISTORY_CORRECT_IMPORT").'</span>'
																		: '<span style="color:red;">'.GetMessage("LM_AUTO_IMPORT_HISTORY_INCORRECT_IMPORT").'</span>');
	$arActions = array();

	if(in_array($f_ID, $last_ids) && LinemediaAutoImportHistory::isSupplierExist($f_SUPPLIER_ID) != null) {
		if($f_CORRECT_IMPORT == 0) {
			// Редактирование элемента.
			$arActions[] = array(
				"ICON"    => "edit",
				"TEXT"    => GetMessage("LM_AUTO_STATUS_IMPORT_CHANGE_INCORRECT"),
				"ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/linemedia.auto_task_history.php?ID=".$f_ID."&action=make_incorrect&lang=".LANGUAGE_ID),
				"DEFAULT" => true
			);
		}
		else {
			$arActions[] = array(
				"ICON"    => "edit",
				"TEXT"    => GetMessage("LM_AUTO_STATUS_IMPORT_CHANGE_CORRECT"),
				"ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/linemedia.auto_task_history.php?ID=".$f_ID."&action=make_correct&lang=".LANGUAGE_ID),
				"DEFAULT" => true
			);
		}
	}
	$row->AddActions($arActions);
}

// Альтернативный вывод.
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage('LM_AUTO_IMPORT_HISTORY_PAGE_TITLE'));

// Не забудем разделить подготовку данных и вывод.
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');



$lAdmin->DisplayList();


require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');






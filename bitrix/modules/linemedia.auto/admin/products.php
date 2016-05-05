<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

global $USER, $APPLICATION;

$userPermission = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS));


if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}


if(!defined('LM_AUTO_ADMIN_PAGE_REQUIRE')) {
	$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

	if ($saleModulePermissions == 'D') {
	    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}

	$POST_RIGHT = 'W';

	if (!CModule::IncludeModule("linemedia.auto")) {
	    ShowError('LM_AUTO MODULE NOT INSTALLED');
	    return;
	}
}

/*
 * ������������ ���������� ����� ��� ����������� � ������.
 */
define('LM_AUTO_SHOW_PRODUCTS_LIMIT', 500);


$database = new LinemediaAutoDatabase();


/***********************************************************/
$sTableID = "b_lm_products"; // ID �������
$oSort = new CAdminSorting($sTableID, "title", "asc"); // ������ ����������
$lAdmin = new CAdminList($sTableID, $oSort); // �������� ������ ������

// �������� �������� ������� ��� �������� ������� � ��������� �������
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors) == 0; // ���� ������ ����, ������ false;
}

// ������ �������� �������
$FilterArr = Array(
	"find_article",
	"find_id",
	"find_original_article",
	"find_title",
	"find_brand_title",
	"find_price",
	"find_weight",
	"find_supplier_id",
);

// �������������� ������
$lAdmin->InitFilter($FilterArr);





















// ���� ��� �������� ������� ���������, ���������� ���
if (CheckFilter()) {
	// �������� ������ ���������� ��� ������� LinemediaautoBrand::GetList() �� ������ �������� �������
	$arFilter = array(
		"article"  		     => $find_article,
		"id"      		     => $find_id,
		"original_article"   => $find_original_article,
		"title"	  		     => $find_title,
		"brand_title"	     => $find_brand_title,
		"price"	  		     => $find_price,
		"weight"	  	     => $find_weight,
		"supplier_id"	     => $find_supplier_id,
	);
}

if ($lAdmin->EditAction()) {
	$events = GetModuleEvents("linemedia.auto", "OnProductsEditAction");
	while ($arEvent = $events->Fetch()) {
		ExecuteModuleEventEx($arEvent, array(&$lAdmin));
	}
}
// ��������� ��������� � ��������� ��������
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
	// ���� ������� "��� ���� ���������"

	if ($_REQUEST['action_target'] == 'selected') {
		switch ($_REQUEST['action']) {
    		// ��������
    		case "delete":
                $wheres = array('1');

                foreach ($arFilter as $code => $val) {
                    $val = trim($val);
                    if ($val != '') {
                        $val = $database->ForSQL($val);
                        $wheres []= "$code = '$val'";
                    }
                }

                // ������� "���������� �� ������� ��������".
				$events = GetModuleEvents("linemedia.auto", "OnProductsPageFilter");
				while ($arEvent = $events->Fetch()) {
					ExecuteModuleEventEx($arEvent, array($wheres));
				}

    			@set_time_limit(0);
    			$database->StartTransaction();
    			$database->Query("DELETE FROM `b_lm_products` WHERE ".implode(' AND ', $wheres).";");
    			$database->Commit();
                break;
    	}
	}

	// ������� �� ������ ���������
	foreach ($arID as $ID) {
		if (strlen($ID) <= 0) {
			continue;
        }
	   	$ID = intval($ID);

		// ��� ������� �������� �������� ��������� ��������
		switch ($_REQUEST['action']) {
    		// ��������
    		case "delete":
    			@set_time_limit(0);
    			$database->StartTransaction();

    			$database->Query('DELETE FROM `b_lm_products` WHERE `id` = ' . $ID . ' LIMIT 1;');

    			$database->Commit();
    		  break;
    	}
	}
}


$where = array('1');
foreach ($arFilter as $code => $val) {
	$val = trim($val);
	if ($val != '') {
		$val = $database->ForSQL($val);
		$where[$code] = "$code = '$val'";
	}
}



// ������� "���������� �� ������� ��������".
$events = GetModuleEvents("linemedia.auto", "OnProductsPageFilter");
while ($arEvent = $events->Fetch()) {
	ExecuteModuleEventEx($arEvent, array(&$where));
}


$supplierWhere = '';
$accessibleSuppliers = array();


// calculating all accessible suppliers for current user and 
// adding its to filter for further sifting unneeded items 
if (in_array($userPermission, array(LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS, LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS)) AND !$USER->IsAdmin()) {
    
    \CModule::IncludeModule('iblock');
    
    foreach (\LinemediaAutoSupplier::getAllowedSuppliers() as $supplier) {
         
        $dbRes = \CIBlockElement::GetProperty(
        		\COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), 
        		$supplier, 
        		array(), 
        		array(
        			'CODE' => 'supplier_id'
        		)
        )->Fetch();
        
        if ($dbRes != null) {
            $accessibleSuppliers[] = $dbRes['VALUE'];
            $supplierWhere .= ' supplier_id LIKE "' . (string) $dbRes['VALUE'] . '" OR';
        }
    }
    
    $supplierWhere = substr($supplierWhere, 0, strrpos($supplierWhere, 'OR'));
}



$where_str = join(' AND ', $where);

if ($supplierWhere != '') {
	if ($where['supplier_id'] == null) {
		$where_str .= ' AND ('.$supplierWhere.')';
	}
}


$sort_by     = (!empty($_REQUEST['by'])) ? ((string) $_REQUEST['by']) : ('id');
$sort_order  = ($_REQUEST['order'] == 'desc') ? ('DESC') : ('ASC');


// ������� ���������� �������
$rowsCount = 0;
//$dbData = $database->Query("SELECT TABLE_ROWS from information_schema.Tables where TABLE_SCHEMA= 'bitrix' && TABLE_NAME = 'b_lm_products'");

$dbData = $database->Query("SELECT COUNT( * ) FROM `b_lm_products` WHERE $where_str");

if($arFields = $dbData->Fetch()) {
	$rowsCount = array_pop($arFields); 
}

// �������� ����� ���������� ���������
$_SESSION['NAV_OBJECT'] = array(
	'RECORDS_COUNT' => $rowsCount,
	'PAGEN' => 1,
	'SIZEN' => 20,
	'PAGES_COUNT' => ceil($rowsCount / 20),
);

if(isset($_REQUEST['PAGEN_1'])) $_SESSION['NAV_OBJECT']['PAGEN'] = intval($_REQUEST['PAGEN_1']);
if(intval($_SESSION['NAV_OBJECT']['PAGEN']) < 1) $_SESSION['NAV_OBJECT']['PAGEN'] = 1;
if(isset($_REQUEST['SIZEN_1'])) $_SESSION['NAV_OBJECT']['SIZEN'] = intval($_REQUEST['SIZEN_1']);
if(intval($_SESSION['NAV_OBJECT']['SIZEN']) < 10) $_SESSION['NAV_OBJECT']['SIZEN'] = 10;

$_SESSION['NAV_OBJECT']['PAGES_COUNT'] = ceil($rowsCount / $_SESSION['NAV_OBJECT']['SIZEN']);


// ��� �������� ������ � �������� � ���
if($where_str == 1) {
	$page = $_SESSION['NAV_OBJECT']['PAGEN'];
	$limit = $_SESSION['NAV_OBJECT']['SIZEN'];

	$start = $limit * ($page - 1);
	
	$dbData = $database->Query("SELECT * FROM `b_lm_products` WHERE $where_str ORDER BY `$sort_by` $sort_order LIMIT $start, $limit;");

	// ����������� ������ � ��������� ������ CAdminResult
	$rsData = new CAdminResult($dbData, $sTableID);
	// ���������� CDBResult �������������� ������������ ���������.
	$rsData->NavStart();

	// ����������� �������� �� ������
	$rsData->NavRecordCount = $_SESSION['NAV_OBJECT']['RECORDS_COUNT'];
	$rsData->NavPageCount = $_SESSION['NAV_OBJECT']['PAGES_COUNT'];
	$rsData->PAGEN = $_SESSION['NAV_OBJECT']['PAGEN'];
	$rsData->NavPageNomer = $_SESSION['NAV_OBJECT']['PAGEN'];
	$rsData->NavPageSize = $_SESSION['NAV_OBJECT']['SIZEN'];
	$rsData->SIZEN = $_SESSION['NAV_OBJECT']['SIZEN'];

	//������������ ������
	$pagenKey = $rsData->SESS_PAGEN;
	$_SESSION[$pagenKey] = $_SESSION['NAV_OBJECT']['PAGEN'];
	$sizenKey = $rsData->SESS_SIZEN;
	$_SESSION[$sizenKey] = $_SESSION['NAV_OBJECT']['SIZEN'];

	// �������� ����� ������������� ������� � �������� ������ $lAdmin
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage('LM_AUTO_MAIN_BRANDS_NAV')));

} else {

  //  $where_str .= ' AND '.$supplierWhere; 
    
	$dbData = $database->Query("SELECT * FROM `b_lm_products` WHERE $where_str ORDER BY `$sort_by` $sort_order");
	// ����������� ������ � ��������� ������ CAdminResult
	$rsData = new CAdminResult($dbData, $sTableID);
	// ���������� CDBResult �������������� ������������ ���������.
	$rsData->NavStart();
	// �������� ����� ������������� ������� � �������� ������ $lAdmin
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage('LM_AUTO_MAIN_BRANDS_NAV')));
}

$arHeaders = array(
  array(  "id"    =>"id",
    "content"  =>"ID",
    "sort"     =>"id",
    "default"  =>true,
  ),
  array(  "id"    =>"title",
    "content"  => GetMessage("LM_AUTO_MAIN_TITLE"),
    "sort"     =>"title",
    "default"  =>true,
  ),
  array(  "id"    =>"article",
    "content"  =>GetMessage("LM_AUTO_MAIN_ARTICLE"),
    "sort"     =>"article",
    "default"  =>true,
  ),
  array(  "id"    =>"original_article",
    "content"  =>GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE"),
    "sort"     =>"original_article",
    "default"  =>true,
  ),

  array(  "id"    =>"brand_title",
    "content"  => GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
    "sort"     =>"brand_title",
    "default"  =>true,
  ),
  array(  "id"    =>"price",
    "content"  => GetMessage("LM_AUTO_MAIN_PRICE"),
    "sort"     =>"price",
    "default"  =>true,
  ),
  array(  "id"    =>"quantity",
    "content"  => GetMessage("LM_AUTO_MAIN_QUANTITY"),
    "sort"     =>"quantity",
    "default"  =>true,
  ),
  array(  "id"    =>"group_id",
    "content"  => GetMessage("LM_AUTO_MAIN_GROUP"),
    "sort"     =>"group_id",
    "default"  =>false,
  ),
  array(  "id"    =>"weight",
    "content"  => GetMessage("LM_AUTO_MAIN_WEIGHT"),
    "sort"     =>"weight",
    "default"  =>true,
  ),
  array(  "id"    =>"supplier_id",
    "content"  => GetMessage("LM_AUTO_MAIN_SUPPLIER_ID"),
    "sort"     =>"supplier_id",
    "default"  =>true,
  ),
  array(  "id"    =>"modified",
    "content"  => GetMessage("LM_AUTO_MAIN_MODIFIED"),
    "sort"     =>"modified",
    "default"  =>true,
  ),
);


/*
 * ���������������� ����.
 */
$lmfields = new LinemediaAutoCustomFields();

$custom_fields = $lmfields->getFields();

foreach ($custom_fields as $custom_field) {
    $arHeaders []= array(
        "id"        => $custom_field['code'],
        "content"   => $custom_field['name'],
        "sort"      => $custom_field['code'],
        "default"   => false,
    );
}



// ??????? ?????????????? ??????????
$events = GetModuleEvents("linemedia.auto", "OnProductsPageHeaders");
while ($arEvent = $events->Fetch()) {
	ExecuteModuleEventEx($arEvent, array(&$arHeaders));
}



$lAdmin->AddHeaders($arHeaders);


/*
 * ����������
 */
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList();
foreach ($suppliers_res as $supplier) {
	$suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}



// ??????? ?????????????? ??????????
$events = GetModuleEvents("linemedia.auto", "OnProductsPageStartRows");
while ($arEvent = $events->Fetch()) {
	ExecuteModuleEventEx($arEvent, array(&$rsData, &$suppliers));
}


$lines = 0;
while ($arRes = $rsData->NavNext(true, "f_")) {
    if (++$lines > LM_AUTO_SHOW_PRODUCTS_LIMIT) {
        break;
    }

    // ������� ������. ��������� - ��������� ������ CAdminListRow.
    $row =& $lAdmin->AddRow($f_id, $arRes);



    // ??????? ?????????????? ??????????
	$events = GetModuleEvents("linemedia.auto", "OnBeforeProductsPageRowAdd");
	while ($arEvent = $events->Fetch()) {
		ExecuteModuleEventEx($arEvent, array(&$row, &$arRes));
	}


    $supplier = $suppliers[$f_supplier_id];
    $row->AddViewField("supplier_id", "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>$f_supplier_id</a>] " . $supplier['NAME']);

    // ���������� ����������� ����.
    $arActions = array();

    // �������������� ��������.
    $arActions []= array(
        'ICON' => 'edit',
        'DEFAULT' => true,
        'TEXT' => GetMessage('LM_AUTO_MAIN_EDIT'),
        'ACTION' => $lAdmin->ActionRedirect("linemedia.auto_part_edit.php?ID=".$f_id."&lang=".LANGUAGE_ID)
    );

    // �������� ��������.
    $arActions []= array(
        "ICON"   => "delete",
        "TEXT"   => GetMessage("LM_AUTO_MAIN_DEL"),
        "ACTION" => "if(confirm('".GetMessage('LM_AUTO_MAIN_CONFIRM_DEL')."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
    );




    // ??????? ?????????????? ??????????
	$events = GetModuleEvents("linemedia.auto", "OnAfterProductsPageRowAdd");
	while ($arEvent = $events->Fetch()) {
		ExecuteModuleEventEx($arEvent, array(&$row, &$arActions));
	}


    // ???????? ??????????? ???? ? ??????.
    $row->AddActions($arActions);
}


// ���������� ���� �� ������ ������ - ���������� ��������
$aContext = array(
    array(
        "TEXT" => GetMessage("LM_AUTO_MAIN_ADD"),
        "LINK" => "/bitrix/admin/linemedia.auto_part_edit.php",
        "TITLE" => GetMessage("LM_AUTO_MAIN_ADD"),
        "ICON" => "btn_new",
    ),
);

$events = GetModuleEvents("linemedia.auto", "OnBeforeProductsPageContextAdd");
while ($arEvent = $events->Fetch()) {
	ExecuteModuleEventEx($arEvent, array(&$aContext));
}

// ��������� ��� � ������
$lAdmin->AddAdminContextMenu($aContext, false, true);


// ��������� ��������.
$lAdmin->AddGroupActionTable(Array(
    "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"), // ������� ��������� ��������
));


/*
//rendering list comprised in admin sheet depending on users privileges
$events = GetModuleEvents('linemedia.auto', 'OnBeforeProductsPageAdd');

while ($arEvent = $events->Fetch()) {

    if (ExecuteModuleEventEx($arEvent, array(&$lAdmin, $sTableID, $userPermission)) == false) {

        if($ex = $APPLICATION->GetException()) {

            $strError = $ex->GetString();
            ShowError($strError);
            return;
        }
    }
}

*/


//read only
if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_READ) == 0 || strcmp($userPermission, LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS) == 0) {
    
    $lAdmin->AddGroupActionTable();
    $lAdmin->AddAdminContextMenu();
    $lAdmin->bCanBeEdited = false;
     
    foreach ($lAdmin->aRows as $supplier) {   
        $supplier->aActions = array();
        
    }
    
}



CUtil::InitJSCore(array('window'));


// ???????
$events = GetModuleEvents("linemedia.auto", "OnProductsPageDisplayList");
while ($arEvent = $events->Fetch()) {
	ExecuteModuleEventEx($arEvent, array(&$lAdmin));
}


ShowError(join('<br>', $messages['error']));
ShowMessage(array('MESSAGE' => join('<br>', $messages['ok']), 'TYPE' => 'OK'));

// ?????????????? ?????.
$lAdmin->CheckListMode();


// ��������� ��������� ��������
$APPLICATION->SetTitle(GetMessage('LM_AUTO_MAIN_PRODUCTS_TITLE'));


// �� ������� ��������� ���������� ������ � �����
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');


// �������� ������ �������
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
    GetMessage("LM_AUTO_MAIN_ARTICLE"),
    "ID",
    GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE"),
    GetMessage("LM_AUTO_MAIN_TITLE"),
    GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
    GetMessage("LM_AUTO_MAIN_PRICE"),
    GetMessage("LM_AUTO_MAIN_WEIGHT"),
    GetMessage("LM_AUTO_MAIN_SUPPLIER_ID"),
  )
);
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<? $oFilter->Begin(); ?>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_ARTICLE").":"?></td>
  <td><input type="text" name="find_article" size="25" value="<?= htmlspecialchars($find_article)?>" /></td>
</tr>
<tr>
  <td>ID:</td>
  <td><input type="text" name="find_id" size="7" value="<?= htmlspecialchars($find_id)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE").":"?></td>
  <td><input type="text" name="find_original_article" size="25" value="<?= htmlspecialchars($find_original_article)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_TITLE").":"?></td>
  <td><input type="text" name="find_title" size="50" value="<?= htmlspecialchars($find_title)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_BRAND_TITLE").":"?></td>
  <td><input type="text" name="find_brand_title" size="30" value="<?= htmlspecialchars($find_brand_title)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_PRICE").":"?></td>
  <td><input type="text" name="find_price" size="15" value="<?= htmlspecialchars($find_price)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_WEIGHT").":"?></td>
  <td><input type="text" name="find_weight" size="15" value="<?= htmlspecialchars($find_weight)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_SUPPLIER_ID").":"?></td>
  <td>
	  <select name="find_supplier_id">
	  	<option value=""><?=GetMessage('NOT_SELECTED')?></option>
	  	<? foreach ($suppliers as $code => $supplier) { ?>
	  		<option value="<?= $code ?>"<?= (($code == $find_supplier_id) ? " selected" : "") ?>>
	  		   <?= $supplier['NAME'] ?>
            </option>
	  	<? } ?>
	  </select>
  </td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?
// ������� ������� ������ ���������
$lAdmin->DisplayList();



// ??????? ?????????????? ??????????
$html = '';
$events = GetModuleEvents("linemedia.auto", "OnProductsPageAfterList");
while ($arEvent = $events->Fetch()) {
	ExecuteModuleEventEx($arEvent, array(&$html, &$lAdmin));
}

echo $html;

?>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
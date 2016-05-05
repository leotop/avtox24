<?php
/**
 * Linemedia Autoportal
 * Downloader module
 * Admin file
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.autodownloader");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$POST_RIGHT = 'W';

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autodownloader")) {
    ShowError('MODULE NOT INSTALLED');
    return;
}

/*
* ������ ������
*/
if($_GET['ajax'] == 'runTask')
{
	$id = (int) $_GET['id'];
	$shedule = LinemediaAutoTaskShedule::GetByTaskId($id);
	$shedule = $shedule->Fetch();
	$shedule_obj = new LinemediaAutoTaskShedule();
	$shedule_obj->Update($shedule['id'], array('force_run_now' => 1));
	die('OK');
}


/***********************************************************/
$sTableID = "b_lm_downloader_tasks"; // ID �������
$oSort = new CAdminSorting($sTableID, "title", "asc"); // ������ ����������
$lAdmin = new CAdminList($sTableID, $oSort); // �������� ������ ������
$lAdmin->bMultipart = true; // ��� �������� ������

// �������� �������� ������� ��� �������� ������� � ��������� �������
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	
	return count($lAdmin->arFilterErrors) == 0; // ���� ������ ����, ������ false;
}

// ������ �������� �������
$FilterArr = Array(
	"find_protocol",
	"find_active",
	"find_id",
	"find_supplier_id",
	"find_title",
	);

// �������������� ������
$lAdmin->InitFilter($FilterArr);

// ���� ��� �������� ������� ���������, ���������� ���
if (CheckFilter()) {
	// �������� ������ ���������� ��� ������� LinemediaAutoTask::GetList() �� ������ �������� �������
	$arFilter = array(
		"protocol"     	=> $find_protocol,
		"active"		=> $find_active,
		"id"		    => $find_id,
		"supplier_id"   => $find_supplier_id,
		"title"		    => $find_title,
	);
}



// ��������� ��������� � ��������� ��������
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
	// ���� ������� "��� ���� ���������"
	if ($_REQUEST['action_target'] == 'selected') {
		$cData = new LinemediaAutoTask();
		$rsData = $cData->GetList(array($by => $order), $arFilter);
		while ($arRes = $rsData->Fetch()) {
			$arID[] = $arRes['id'];
        }
	}
	
	// ������� �� ������ ���������
	foreach ($arID as $ID) {
		if (strlen($ID) <= 0) {
			continue;
        }
	   	$ID = IntVal($ID);
		
		// ��� ������� �������� �������� ��������� ��������
        switch($_REQUEST['action']) {
    		// ��������
    		case "delete":
    			LinemediaAutoTask::Delete($ID);
                break;
            case "run":
    			@set_time_limit(0);
    			LinemediaAutoDownloaderMain::run($ID);
                break;
                
    		case "activate":
    		case "deactivate":
    			$cData = new LinemediaAutoTask();
    			if (($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch())) {
    				$arFields["active"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
    				if (!$cData->Update($ID, $arFields)) {
    					$lAdmin->AddGroupError(GetMessage("LM_AUTO_DOWNLOADER_SAVE_ERR").$cData->LAST_ERROR, $ID);
                    }
    			} else {
    				$lAdmin->AddGroupError(GetMessage("LM_AUTO_DOWNLOADER_SAVE_ERR")." ".GetMessage("LM_AUTO_DOWNLOADER_NO_MODEL"), $ID);
                }
                break;
        }
	}
}



// ������� ������
$cData = new LinemediaAutoTask();
$rsData = $cData->GetList(array($by => $order), $arFilter);

// ����������� ������ � ��������� ������ CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// ���������� CDBResult �������������� ������������ ���������.
$rsData->NavStart();

// �������� ����� ������������� ������� � �������� ������ $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_DOWNLOADER_MODELS_NAV")));



$lAdmin->AddHeaders(array(
  array(  "id"    =>"id",
    "content"  =>GetMessage("LM_AUTO_DOWNLOADER_ID"),
    "sort"     =>"id",
    "default"  =>true,
  ),
  array(  "id"    =>"active",
    "content"  =>GetMessage("LM_AUTO_DOWNLOADER_ACTIVE"),
    "sort"     =>"active",
    "default"  =>true,
  ),
  array(  "id"    =>"supplier_id",
    "content"  =>GetMessage("LM_AUTO_DOWNLOADER_SUPPLIER"),
    "sort"     =>"supplier_id",
    "default"  =>true,
  ),
  array(  "id"    =>"title",
    "content"  => GetMessage("LM_AUTO_DOWNLOADER_TASK_TITLE"),
    "sort"     =>"title",
    "default"  =>true,
  ),
  array(  "id"    =>"protocol",
    "content"  => GetMessage("LM_AUTO_DOWNLOADER_PROTOCOL"),
    "sort"     =>"protocol",
    "default"  =>true,
  ),
  array(  "id"    =>"interval",
    "content"  => GetMessage("LM_AUTO_DOWNLOADER_INTERVAL"),
    "sort"     =>"type",
    "default"  =>true,
  ),
  array(  "id"    =>"last_exec",
    "content"  => GetMessage("LM_AUTO_DOWNLOADER_LAST_EXEC"),
    "sort"     =>"last_exec",
    "default"  =>true,
  ),
));




/*
* ��������� ���������
*/
$protocols = LinemediaAutoDownloaderMain::getProtocols();





$times_lang = array(
	0 => GetMessage('LM_AUTO_DOWNLOADER_INTERVAL_MANUALLY'),
	3600 => GetMessage('LM_AUTO_DOWNLOADER_INTERVAL_HORLY'),
	86400 => GetMessage('LM_AUTO_DOWNLOADER_INTERVAL_DAILY'),
	604800 => GetMessage('LM_AUTO_DOWNLOADER_INTERVAL_WEEKLY'),
	2592000 => GetMessage('LM_AUTO_DOWNLOADER_INTERVAL_MONTHLY'),
);


$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList();
foreach($suppliers_res as $supplier)
{
	$suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}


while ($arRes = $rsData->NavNext(true, "f_")) {
    
    /*
    * Hklexbv ���������� ������, ����� ����� ��� ����� �������������
    */
    $shedules = array();
    $shedule_obj = LinemediaAutoTaskShedule::GetByTaskId($arRes['id']);
    while($shedule = $shedule_obj->Fetch())
    {
	    $shedules [] = $shedule;
    }
    
    /*
    * ���� � ��� ������ ���� ���������� �� ������ - �������� ���
    */
    $arRes['interval'] = $f_interval = $shedules[0]['interval'];
    $arRes['last_exec'] = $last_exec = $shedules[0]['last_exec'];
    
    
    // ������� ������. ��������� - ��������� ������ CAdminListRow
    $row =& $lAdmin->AddRow($f_id, $arRes);
  
    // ����� �������� ����������� �������� ��� ��������� � �������������� ������
    $row->AddCheckField("active");
    
    
    
    if(isset($times_lang[$f_interval]))
    {
	    $row->AddViewField("interval", $times_lang[$f_interval]);
    } else {
    	$hours = floor($f_interval / 3600);
    	$mins = floor(($f_interval - ($hours*3600)) / 60);
	    $row->AddViewField("interval", $hours . 'h - ' . $mins . 'm');
    }
    
    $supplier = $suppliers[$f_supplier_id];
    $row->AddViewField("supplier_id", "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>$f_supplier_id</a>] " . $supplier['NAME']);
  
  
    
    // ���������� ����������� ����
    $arActions = array();
    
    
    // ������
    $arActions[] = array(
      "ICON"    => "run",
      "TEXT"    => GetMessage("LM_AUTO_DOWNLOADER_RUN"),
      "ACTION"  => "runTask(".$f_id.")"
    );
    
    // �������������� ��������.
    $arActions[] = array(
      "ICON"    => "edit",
      "TEXT"    => GetMessage("LM_AUTO_DOWNLOADER_EDIT"),
      "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/linemedia.autodownloader_add.php?ID=$f_id&lang=" . LANG),
      "DEFAULT" => true
    );

    // �������� ��������
    $arActions[] = array(
      "ICON"=>"delete",
      "TEXT"=>GetMessage("LM_AUTO_DOWNLOADER_DELETE"),
      "ACTION"=>"if(confirm('".GetMessage('LM_AUTO_DOWNLOADER_CONFIRM_DELETE')."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
    );
    
    // �������� ����������� ���� � ������
    $row->AddActions($arActions);
}


// ������ �������
$lAdmin->AddFooter(
  array(
    array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // ���-�� ���������
    array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // ������� ��������� ���������
  )
);

// ��������� ��������
$lAdmin->AddGroupActionTable(Array(
  "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"), // ������� ��������� ��������
  "activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"), // ������������ ��������� ��������
  "deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // �������������� ��������� ��������
  ));
  
  
  
// ���������� ���� �� ������ ������ - ���������� ��������
$aContext = array(
  array(
    "TEXT"=>GetMessage("LM_AUTO_DOWNLOADER_ADD_TASK"),
    "LINK"=>"/bitrix/admin/linemedia.autodownloader_add.php?lang=" . LANG,
    "TITLE"=>GetMessage("LM_AUTO_DOWNLOADER_ADD_TASK"),
    "ICON"=>"btn_new",
  ),
);


// � ��������� ��� � ������
$lAdmin->AddAdminContextMenu($aContext);


CUtil::InitJSCore(array('window'));


// �������������� �����
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage('LM_AUTO_DOWNLOADER_PAGE_TITLE'));


$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.7.1/jquery.min.js');
//$APPLICATION->AddHeadScript('/bitrix/modules/linemedia.autodownloader/interface/script.js');



// �� ������� ��������� ���������� ������ � �����
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


// �������� ������ �������
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
    GetMessage("LM_AUTO_DOWNLOADER_ID"),
    GetMessage("LM_AUTO_DOWNLOADER_TASK_TITLE"),
    GetMessage("LM_AUTO_DOWNLOADER_ACTIVE"),
    GetMessage("LM_AUTO_DOWNLOADER_SUPPLIER"),
    GetMessage("LM_AUTO_DOWNLOADER_PROTOCOL"),
  )
);
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
  <td><?=GetMessage("LM_AUTO_DOWNLOADER_ID")?>:</td>
  <td>
    <input type="text" name="find_id" size="4" value="<?= htmlspecialchars($find_id)?>">
  </td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_DOWNLOADER_TASK_TITLE").":"?></td>
  <td><input type="text" name="find_title" size="25" value="<?= htmlspecialchars($find_title)?>"></td>
</tr>
<tr>
  <td><?= GetMessage("LM_AUTO_DOWNLOADER_ACTIVE") ?>:</td>
  <td>
    <select name="find_active">
        <option value=""><?=GetMessage('LM_AUTO_NOT_SELECTED')?></option>
        <option<?= $find_interval == 'Y' ?' selected' : '' ?> value="Y"><?= GetMessage('LM_AUTO_ACTIVE_Y') ?></option>
        <option<?= $find_interval == 'N' ?' selected' : '' ?> value="N"><?= GetMessage('LM_AUTO_ACTIVE_N') ?></option>
    </select>
  </td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_DOWNLOADER_SUPPLIER")?>:</td>
  <td>
    <input type="text" name="find_supplier_id" size="4" value="<?= htmlspecialchars($find_supplier_id)?>">
  </td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_DOWNLOADER_PROTOCOL")?>:</td>
  <td>
  	<select name="find_protocol">
	<option value=""><?=GetMessage('LM_AUTO_NOT_SELECTED')?></option>
	<?foreach($protocols AS $pid => $protocol){?>
		<option value="<?=htmlspecialchars($pid)?>" <?=($find_protocol == $pid) ? 'selected':''?>><?=htmlspecialchars($protocol['title'])?></option>
	<?}?>
	</select>
  </td>
</tr>



<?
$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>



<?
// ������� ������� ������ ���������
$lAdmin->DisplayList();
?>


<script>
function runTask(id)
{
	$.ajax({
	  url: "/bitrix/admin/linemedia.autodownloader_list.php?lang=<?=LANG?>&ajax=runTask&id=" + id,
	}).done(function(data) {
	  if(data == 'OK')
	  {
	  		alert('<?=GetMessage('LM_AUTO_TASK_RUN_OK')?>');
	  } else {
		  alert(data);
	  }
	});
}
</script>


<?
require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
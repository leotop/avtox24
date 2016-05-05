<? IncludeModuleLangFile(__FILE__)   ?>

<?
	$arSubTabs = array(
		array("DIV" => "linemedia_auto_order", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_ORDERS_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_ORDERS_TAB_TITLE_SET")),
		array("DIV" => "linemedia_auto_goods_statuses", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_GOODS_STATUSES_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_GOODS_STATUSES_TAB_TITLE_SET")),
		array("DIV" => "linemedia_auto_price_import", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_IMPORT_PRICE_LIST_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_IMPORT_PRICE_LIST_TAB_TITLE_SET")),
		array("DIV" => "linemedia_auto_word_forms", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_WORDFORMS_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_WORDFORMS_TAB_TITLE_SET")),
		array("DIV" => "linemedia_auto_spare", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_SPARELIST_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_SPARELIST_TAB_TITLE_SET")),	
		array("DIV" => "linemedia_auto_search_stat", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_SEARCH_STATISTICS_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_SEARCH_STATISTICS_TAB_TITLE_SET")),
		array("DIV" => "linemedia_auto_user_fields", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_USER_FIELDS_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_USER_FIELDS_TAB_TITLE_SET")),
		array("DIV" => "linemedia_auto_price_ap", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_PRICESAP_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_PRICESAP_TAB_TITLE_SET")),
	    array("DIV" => "linemedia_auto_vin", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_VIN_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_VIN_TAB_TITLE_SET")),
	    array("DIV" => "linemedia_auto_finance", "TAB" => GetMessage("LM_AUTO_MAIN_ACCESSES_FINANCE_TAB_SET"), "ICON" => "sale", "TITLE" => GetMessage("LM_AUTO_MAIN_ACCESSES_FINANCE_TAB_TITLE_SET"))
	);
	
	$tabControl1 = new CAdminViewTabControl("tabControl1", $arSubTabs);
	$tabControl1->Begin();

	$module_id = $sModuleId;
	$i = 0;
	?>
<span class="adm-detail-content-table edit-table" id="edit4_edit_table" style="opacity: 1;">	
	<?
	foreach($arSubTabs as $subTab)
	{
		$tabControl1->BeginNextTab();
		
if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_id."/admin/task_description.php");

$md = CModule::CreateModuleObject($module_id);

$arGROUPS = array();
$arFilter = Array("ACTIVE"=>"Y");
if($md->SHOW_SUPER_ADMIN_GROUP_RIGHTS != "Y")
	$arFilter["ADMIN"] = "N";

$z = CGroup::GetList($v1="sort", $v2="asc", $arFilter);
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;
}


if($i == 0) $bind = LM_AUTO_ACCESS_BINDING_ORDERS;
elseif($i == 1) $bind = LM_AUTO_ACCESS_BINDING_STATUSES;
elseif($i == 2) $bind = LM_AUTO_ACCESS_BINDING_PRICES_IMPORT;
elseif($i == 3) $bind = LM_AUTO_ACCESS_BINDING_WORDFORMS;
elseif($i == 4) $bind = LM_AUTO_ACCESS_BINDING_PRODUCTS;
elseif($i == 5) $bind = LM_AUTO_ACCESS_BINDING_STATISTICS;
elseif($i == 6) $bind = LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS;
elseif($i == 7) $bind = LM_AUTO_ACCESS_BINDING_PRICES;
elseif ($i == 8) $bind = LM_AUTO_ACCESS_BINDING_VIN;
elseif ($i == 9) $bind = LM_AUTO_ACCESS_BINDING_FINANCE;

//println($_REQUEST);

if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $USER->IsAdmin() && check_bitrix_sessid())
{	
	// установка прав групп
	if(is_array($_REQUEST["GROUP_DEFAULT_TASK"]) && !empty($_REQUEST["GROUP_DEFAULT_TASK"]))
	{			
		COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK_".$bind, $_REQUEST["GROUP_DEFAULT_TASK"][$bind], "Task for groups by default");			
		$letter = ($l = CTask::GetLetter($_REQUEST["GROUP_DEFAULT_TASK"][$bind])) ? $l : 'D';
		COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT_".$bind, $letter, "Right for groups by default");	
	}
	
	$arTasksInModule = Array();
	foreach($arGROUPS as $value)
	{		
		if(array_key_exists("CTASKS", $_REQUEST))
		{
			$l = 0;
			foreach($_REQUEST["CTASKS"] as $bindKey => $value)
			{
				
				foreach($value as $k => $val)
				{
					$arTasksInModule["CTASKS"][$l][$k] = Array('ID'=>$val);
					//$arTasksIds[] = $val;
				}
				$l++;
			}
		}
	}

	LinemediaAutoGroup::SetTasksForModule($module_id, $arTasksInModule);
}

if($bind == LM_AUTO_ACCESS_BINDING_ORDERS)
{
	echo BeginNote();
		echo GetMessage('LM_AUTO_MAIN_SETTINGS_NOTE');
	echo EndNote();
}
elseif($bind == LM_AUTO_ACCESS_BINDING_STATUSES)
{
	echo BeginNote();
		echo GetMessage('LM_AUTO_MAIN_STATUSES_NOTE');
	echo EndNote();
}


$arTasksInModule = CTask::GetTasksInModules(true,$sModuleId, $bind);
$arTasks = $arTasksInModule[$sModuleId];

//println($arTasksInModule);
  
$GROUP_DEFAULT_TASK = COption::GetOptionString($module_id, "GROUP_DEFAULT_TASK_".$bind, "");
if ($GROUP_DEFAULT_TASK == '')
{
	$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT_".$bind, "D");
	$GROUP_DEFAULT_TASK = CTask::GetIdByLetter($GROUP_DEFAULT_RIGHT, $module_id, $bind);
	
	if ($GROUP_DEFAULT_TASK)
		COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK_".$bind, $GROUP_DEFAULT_TASK[$bind]);
}
?>                 

<?if($bind != LM_AUTO_ACCESS_BINDING_STATUSES)
{?>
	<div class="setblock">
		<p class="ltd" style="float:left;"><b><?=GetMessage("MAIN_BY_DEFAULT");?></b></p>
		<p class="ltd ltd2" style="float:left;">
			<?		
				echo SelectBoxFromArray("GROUP_DEFAULT_TASK[".$bind."]", $arTasks, htmlspecialcharsbx($GROUP_DEFAULT_TASK));
				?><?=bitrix_sessid_post()?>
		</p>
		<div style="clear:both;"></div>
	</div>
<?}?>

<?
$arUsedGroups = array();
$arTaskInModule = LinemediaAutoGroup::GetTasksForModule($module_id);

$tasksCurrent = array();
foreach($arTaskInModule as $tTask)
{
	foreach($tTask as $groupId => $task)
	{
		
		if(in_array($task["ID"],$arTasks["reference_id"]))
		{
			$n[$groupId] = $task["ID"];
			$tasksCurrent[$groupId]["ID"] = $task["ID"];
		}
	}
}

if(CModule::IncludeModule('sale'))
{
if($bind != LM_AUTO_ACCESS_BINDING_STATUSES)
{
foreach($arGROUPS as $value):
	$v = (isset($tasksCurrent[$value["ID"]]['ID'])? $tasksCurrent[$value["ID"]]['ID'] : false);
	
	if($v):
		$arUsedGroups[$value["ID"]] = true;
?>
<div class="setblock setblock_<?=$bind?>">
	<p class="ltd" style="float:left;"><?=$value["NAME"]." [<a title=\"".GetMessage("MAIN_USER_GROUP_TITLE")."\" href=\"/bitrix/admin/group_edit.php?ID=".$value["ID"]."&amp;lang=".LANGUAGE_ID."\">".$value["ID"]."</a>]:"?><?
	if ($value["ID"]==1 && $md->SHOW_SUPER_ADMIN_GROUP_RIGHTS=="Y"):
		echo "<br><small>".GetMessage("MAIN_SUPER_ADMIN_RIGHTS_COMMENT")."</small>";
	endif;
	?></p>
	
	
		<p class="ltd ltd2" style="float:left;"><?
		//println($v);
		echo SelectBoxFromArray("CTASKS[".$bind."][".$value["ID"]."]", $arTasks, $tasksCurrent[$value["ID"]]["ID"], GetMessage("MAIN_DEFAULT"));
		?></p>
	
	<div style="clear:both;"></div>
</div>
<?
	endif;
endforeach;

}
else{
	$resStatuses = CSaleStatus::GetList(
	 array("sort" => "asc"),
	 array("LID" => "ru"),
	 false,
	 false,
	 array()
	);
	if($resStatuses) echo '<div class="statuses_h">'.GetMessage('LM_AUTO_MAIN_PRODUCT_STATUSES').'</div>';
    $arStTemp = array();
	while($status = $resStatuses->Fetch())
	{	
        if(!in_array($status["ID"], $arStTemp))
         echo '<div class="statuses"><a href="/bitrix/admin/sale_status_edit.php?ID='.$status["ID"].'&lang=ru&filter=Y&set_filter=Y">'.$status["NAME"].'</a></div>';
	    
        $arStTemp[] = $status["ID"];
    }
    unset($arStTemp);
}
}


if(count($arGROUPS) > count($arUsedGroups) && $bind != "linemedia_auto_goods_statuses"):
?>
<div class="setblock setblock_<?=$bind?>">
	<p class="ltd" style="float:left;"><select style="width:300px" onchange="settingsSetGroupID(this)" class="group_select">
		<option value=""><?echo GetMessage("group_rights_select")?></option>
<?
foreach($arGROUPS as $group):
	if($arUsedGroups[$group["ID"]] == true)
		continue;
?>
		<option value="<?=$group["ID"]?>"><?=$group["NAME"]." [".$group["ID"]."]"?></option>
<?endforeach?>
	</select></p>
	<p class="ltd ltd2" style="float:left;"><?
	echo SelectBoxFromArray("", $arTasks, "", GetMessage("MAIN_DEFAULT"));
	?></p>
	<div style="clear:both;"></div>
	
</div>
<div>
	<p class="ltd" style="float:left;">&nbsp;</p>
	<p class="ltd ltd2" style="padding-bottom:10px; float:left;">
<script type="text/javascript">
function settingsSetGroupID(el)
{
	var sel = el.parentNode.nextSibling.nextSibling.firstChild;
	var pId = el.parentNode.parentNode.parentNode.parentNode.getAttribute('id');	
	sel.name = "CTASKS["+pId+"]["+el.value+"]";
}

function settingsAddRights(a)
{
	var row = jsUtils.FindParentObject(a, "div");
	
	var tbl = row.parentNode;
	var closestId = a.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
	
	var cnt = a.parentNode.parentNode.parentNode.parentNode.childNodes[1].children.length;
	cnt = cnt - 5;
	
	oldNode=document.getElementsByClassName('setblock_'+closestId)[cnt];	
	tableRow=oldNode.cloneNode(true);
	tbl.insertBefore(tableRow, row);

	var sel = tableRow.getElementsByClassName('typeselect')[0];
	sel.name = "";
	sel.selectedIndex = 0;
	
	sel = tableRow.getElementsByTagName('select')[0];
	sel.selectedIndex = 0;
}
</script>
<a href="javascript:void(0)" onclick="settingsAddRights(this)" hidefocus="true" class="adm-btn"><?echo GetMessage('LM_AUTO_MAIN_ADD_ACCESS_RIGHT')?></a>
	</p>
	
	<div style="clear:both;"></div>
    
    
</div>
<?endif;
	
			
	$i++;	
	}

	$tabControl1->End();
	
	$arFields = array
	(
		"NAME" => "TEST",
		"DESCRIPTION" => "some",
		"LETTER" => 'E',
		"BINDING" => 'module',
		"MODULE_ID" => $sModuleId
	);
	
	//$ID = CTask::Add($arFields);
	
	?>
      <input type="hidden" name="active_tab" id="active_tab" value="<?if($_REQUEST['active_tab']) echo $_REQUEST['active_tab']?> "/>
</span>

<script type="text/javascript">
$( document ).ready(function() {
/*чтобы подвкладка не сбрасывалась*/
   
    var a = null;
    var a = $('#active_tab').val();
    
    if(a.length > 5) 
    {
        $('.adm-detail-subtabs').removeClass('adm-detail-subtab-active');
        $("#edit4_edit_table").children().attr('style','display:none');
        $("#" + a).addClass('adm-detail-subtab-active');
        
        var arr = a.split('_');
        var t = arr.splice(0, 2);
         
        $("#" + arr.join('_')).attr('style','display:block');        
    }
    else 
    {
        $('#view_tab_linemedia_auto_order').addClass('adm-detail-subtab-active');
        $('#linemedia_auto_order').attr('style','display:block');
    }
       
    $( ".adm-detail-subtabs" ).click(function() {
        $('#active_tab').val($(this).attr('id'));
    });
});
</script>

<style>
.setblock :first-child{}
.setblock {width: 70%; margin: auto;}
.setblock .ltd{width: 400px; text-align: right;}
.setblock .ltd2{text-align: left; margin-left: 10px;}
.setblock select{padding-left: 0px !important;}
.setblock .group_select{}

.statuses_h{position:relative; width: 45%; text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 10px;}
.statuses{margin-bottom: 10px; position:relative; width: 75%; text-align: center;}

</style>
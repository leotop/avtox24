<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");


if(!($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/user_admin.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/user_search.php");
IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("iblock")) {
    ShowError('IBLOCK MODULE NOT INSTALLED');
    return;
}

$FN = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST["FN"]);
$FC = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST["FC"]);
if($FN == "")
	$FN = "find_form";
if($FC == "")
	$FC = "USER_ID";

if (isset($_REQUEST['JSFUNC']))
{
	$JSFUNC = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['JSFUNC']);
}
else
{
	$JSFUNC = '';
}
// идентификатор таблицы
$sTableID = "tbl_user_popup";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "ID", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

// инициализация параметров списка - фильтры
$arFilterFields = Array(
	"find",
	"find_type",
	"find_id",
	"find_timestamp_1",
	"find_timestamp_2",
	"find_last_login_1",
	"find_last_login_2",
	"find_active",
	"find_login",
	"find_name",
	"find_email",
	"find_keywords",
	"find_group_id"
	);

$lAdmin->InitFilter($arFilterFields);

//инициализация массива фильтра для GetList
function CheckFilter($FilterArr) // проверка введенных полей
{
	global $strError;
	foreach($FilterArr as $f)
		global $$f;

	$str = "";
	if(strlen(trim($find_timestamp_1))>0 || strlen(trim($find_timestamp_2))>0)
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_timestamp_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_timestamp_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if (!$date1_stm && strlen(trim($find_timestamp_1))>0)
			$str.= GetMessage("MAIN_WRONG_TIMESTAMP_FROM")."<br>";
		else $date_1_ok = true;
		if (!$date2_stm && strlen(trim($find_timestamp_2))>0)
			$str.= GetMessage("MAIN_WRONG_TIMESTAMP_TILL")."<br>";
		elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
			$str.= GetMessage("MAIN_FROM_TILL_TIMESTAMP")."<br>";
	}

	if(strlen(trim($find_last_login_1))>0 || strlen(trim($find_last_login_2))>0)
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_last_login_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_last_login_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if(!$date1_stm && strlen(trim($find_last_login_1))>0)
			$str.= GetMessage("MAIN_WRONG_LAST_LOGIN_FROM")."<br>";
		else
			$date_1_ok = true;
		if(!$date2_stm && strlen(trim($find_last_login_2))>0)
			$str.= GetMessage("MAIN_WRONG_LAST_LOGIN_TILL")."<br>";
		elseif($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
			$str.= GetMessage("MAIN_FROM_TILL_LAST_LOGIN")."<br>";
	}

	$strError .= $str;
	if(strlen($str)>0)
	{
		global $lAdmin;
		$lAdmin->AddFilterError($str);
		return false;
	}

	return true;
}


 $sModuleId = "linemedia.auto";

    $arTasksFilter = array("BINDING" => "linemedia_auto_order");
    $curUserGroup = $USER->GetUserGroupArray(); //массив групп пользователя

    $maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter); //максимальная роль пользователя
    //echo "maxrole=".$maxRole;         

    $resUserGroupsPerms = LinemediaAutoGroup::getUserPermissionsForModuleBinding($sModuleId, $curUserGroup, $arTasksFilter);       
    while($aUserGroupsPerms = $resUserGroupsPerms->Fetch())
    {
        $arUserGroupsPerms[] = $aUserGroupsPerms;
    }
      
    foreach($arUserGroupsPerms as $perm)
    {
        if($maxRole == $perm["LETTER"]) $groupId = $perm["GROUP_ID"];
    }   
    /**************************************/

    if($maxRole == LM_AUTO_MAIN_ACCESS_DENIED)
    {
        $err .= GetMessage("ACCESS_DENIED");
    }
	
	$IDs = (bool) $find_id ? $find_id . ' | ' . implode(' | ', $usersID) : implode(' | ', $usersID);

$arFilter = Array();
if(CheckFilter($arFilterFields))
{
	$arFilter = Array(
		"ID"			=> $find_id,
		"TIMESTAMP_1"	=> $find_timestamp_1,
		"TIMESTAMP_2"	=> $find_timestamp_2,
		"LAST_LOGIN_1"	=> $find_last_login_1,
		"LAST_LOGIN_2"	=> $find_last_login_2,
		"ACTIVE"		=> $find_active,
		"LOGIN"			=>	($find!='' && $find_type == "login"? $find: $find_login),
		"NAME"			=>	($find!='' && $find_type == "name"? $find: $find_name),
		"EMAIL"			=>	($find!='' && $find_type == "email"? $find: $find_email),
		"KEYWORDS"		=> $find_keywords,
		"GROUPS_ID"		=> $find_group_id
		);
}

if(!$USER->CanDoOperation('view_all_users'))
{
	$arUserSubordinateGroups = array();
	$arUserGroups = CUser::GetUserGroup($USER->GetID());
	foreach($arUserGroups as $grp)
		$arUserSubordinateGroups = array_merge($arUserSubordinateGroups, CGroup::GetSubordinateGroups($grp));

	$arFilter["CHECK_SUBORDINATE"] = array_unique($arUserSubordinateGroups);
}

    $arFilterOld = $arFilter;
    
    $userIds = array();
    $flagResult = false; 
    
	if($maxRole != LM_AUTO_MAIN_ACCESS_READ && $maxRole != LM_AUTO_MAIN_ACCESS_READ_WRITE && $maxRole != LM_AUTO_MAIN_ACCESS_FULL && !$USER->IsAdmin())
	{ 
		if($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS)
		{
			$arClientsIds = LinemediaAutoGroup::getUserClients();
			if(strlen($arFilterOld["ID"]) > 0 && in_array($arFilterOld["ID"], $arClientsIds)) //Если в пользовательском фильтре пришел ид клиента и этот ид есть среди клиентов менеджера      
			{
				$arFilter["ID"] = $arFilterOld["ID"];
					
				$rsDataPrev = CUser::GetList($by, $order, $arFilter, array("ID"));
				while($arDataPrev = $rsDataPrev -> Fetch())
				{
					$userIds = $arDataPrev["ID"];
				}
				$flagResult = true;
			}
			elseif(strlen($arFilterOld["ID"]) > 0 && !in_array($arFilterOld["ID"], $arClientsIds))
			{
				$flagResult = false; //иначе устанавливаем флаг в false, чтобы в следующем гет листе не выбрались все подряд пользователи
			}
			else //если в пользовательском фильтре ничего не пришло
			{
				if(is_array($arClientsIds) && !empty($arClientsIds)) $arFilter["ID"] = implode(" | ", $arClientsIds);
				else $arFilter["ID"] = $arClientsIds;

				$rsDataPrev = CUser::GetList($by, $order, $arFilter, array("ID"));
				while($arDataPrev = $rsDataPrev -> Fetch())
				{
					$userIds[] = $arDataPrev["ID"];
				}
				$flagResult = true;
				
				if(empty($userIds)) $flagResult = false; //если ни одного своего клиента не найдено, не позволяем делать дальнейшую выборку
			
			}
		}
		elseif($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH || $maxRole == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH)
		{
			$curUserGroup = $USER->GetUserGroupArray(); //массив групп пользователя
			
			$filter = Array
			(
				"ID"             => implode("|", $curUserGroup),
				"STRING_ID"     => "branch_%", //чтобы покупатель состоял в том же филиале, что и текущий пользователь 
			);
			$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter); // выбираем группы
			while($arrGroups = $rsGroups->Fetch())
			{
				$arGroups[] = $arrGroups["ID"];
			}      
			
			$arFilter["GROUPS_ID"] =$arGroups;       
			   
			LinemediaAutoDebug::add('list filter ' . print_r($arFilter, true));
			$rsDataPrev = CUser::GetList($by, $order, $arFilter, array("ID"));
			while($arDataPrev = $rsDataPrev -> Fetch())
			{
				$userIds[] = $arDataPrev["ID"];
			}
			
			$flagResult = true;
			
			if(empty($userIds))
				$flagResult = false;
		}
		elseif($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN)
		{
			$arClientsIds = $USER -> GetId(); //свой профиль, т.к. свои заказы
			$arFilter["ID"] = $arClientsIds;

			$own_group = CUser::GetUserGroup($USER->GetId());
			foreach($own_group as $o_gr)
			{
				if(!in_array($o_gr, $arFilter["CHECK_SUBORDINATE"]))
				{
					$arFilter["CHECK_SUBORDINATE"][] = $o_gr;  
					$arFilterOld["CHECK_SUBORDINATE"][] = $o_gr;   
				}
			}
			
			//unset($arFilter["CHECK_SUBORDINATE"]);
			$rsDataPrev = CUser::GetList($by, $order, $arFilter, array("ID"));
			while($arDataPrev = $rsDataPrev -> Fetch())
			{
				$userIds[] = $arDataPrev["ID"];
			}

			$flagResult = true;
			
			if(empty($userIds))
				$flagResult = false;
		}
    
		//Получим группы пользователей 
		$groups = array();
		if(!empty($userIds))
		{
			foreach($userIds as $id)
			{
				$groups[] = CUser::GetUserGroup($id);
			}                
		}

    
		foreach($groups as $gr)
		{
			foreach($gr as $g)
			{
				$all_groups[] = $g;    
			}         
		}    

		$all_groups_u = array_unique($all_groups);
		//Выберем доступные группы
		foreach($all_groups_u as $gr)
		{
			if(in_array($gr, $arFilter["CHECK_SUBORDINATE"]))
			{
				  $all_available_groups[] = $gr;
			}
		}
			
		$ar_users_filtered = array();
    
    //Если фильтруем по группам
		if(array_key_exists("GROUPS_ID", $arFilterOld) && !empty($arFilterOld["GROUPS_ID"]) && !empty($userIds))
		{ 
		   //Получим пользователей по фильтру
			$rsDataU = CUser::GetList($by, $order, $arFilterOld, array("ID"));
			$users_by_old_filter = array();
			while ($u_res = $rsDataU->Fetch()) 
			{
				$users_by_old_filter[] = $u_res["ID"];
			}
					
			if(!empty($users_by_old_filter))
			{
				//Выберем из них тех, которые доступны по правам доступа
			
				foreach($userIds as $u)
				{
					if(in_array($u, $users_by_old_filter))
					{
						$ar_users_filtered[] = $u; 
					}
				}
				if(!empty($ar_users_filtered))
				{
					$arFilterByFilteredUsers["ID"] = implode("|", $ar_users_filtered);    
				}
			
				//Финальная выборка
				if(!empty($arFilterByFilteredUsers))
				{
					$rsData = CUser::GetList($by, $order, $arFilterByFilteredUsers, array(
					 "NAV_PARAMS"=> array("nPageSize"=>CAdminResult::GetNavSize($sTableID))
					));                
				}
	                 
			} 
		}
		else
		{
			//Фильтрации по группам в пользовательском фильтре нет, поэтому просто выберем пользователей группы заданного филиала
			if(!empty($arFilter["ID"]) || !empty($arFilter["GROUPS_ID"]))
			{
				
			}
			
			$f_groups = $arFilter["GROUPS_ID"];
			$arFilter = array_merge($arFilterOld, $arFilter);
			
			$arFilter["GROUPS_ID"] = $f_groups; 

			$rsData = CUser::GetList($by, $order, $arFilter, array(
				"NAV_PARAMS"=> array("nPageSize"=>CAdminResult::GetNavSize($sTableID))
			));

			/*$userIDS = array();
			while ($o = $rsData->Fetch()) {
				$userIDS[] = $o;
			}*/
			//_d($userIDS);
		}  
	}
	else
	{
		// инициализация списка - выборка данных
		$rsData = CUser::GetList($by, $order, $arFilter, array(
			"NAV_PARAMS"=> array("nPageSize"=>CAdminResult::GetNavSize($sTableID))
		));
	}

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установке параметров списка
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES")));

// заголовок списка
$lAdmin->AddHeaders(array(
	array("id"=>"ID",				"content"=>"ID", 	"sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X",		"content"=>GetMessage('TIMESTAMP'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"ACTIVE", 			"content"=>GetMessage('ACTIVE'),	"sort"=>"active", "default"=>true),
	array("id"=>"LOGIN", 			"content"=>GetMessage("LOGIN"),  "sort"=>"login", "default"=>true),
	array("id"=>"NAME",				"content"=>GetMessage("NAME"), "sort"=>"name",	"default"=>true),
	array("id"=>"LAST_NAME", 		"content"=>GetMessage("LAST_NAME"),  "sort"=>"last_name", "default"=>true),
	array("id"=>"EMAIL", 			"content"=>GetMessage('EMAIL'),  "sort"=>"email", "default"=>true),
	array("id"=>"LAST_LOGIN", 		"content"=>GetMessage("LAST_LOGIN"), "sort"=>"last_login", "default"=>true),
	array("id"=>"DATE_REGISTER", 		"content"=>GetMessage("DATE_REGISTER"), "sort"=>"date_register"),
	array("id"=>"PERSONAL_BIRTHDAY", 	"content"=>GetMessage("PERSONAL_BIRTHDAY"), "sort"=>"personal_birthday"),
	array("id"=>"PERSONAL_PROFESSION",	"content"=>GetMessage("PERSONAL_PROFESSION"), "sort"=>"personal_profession"),
	array("id"=>"PERSONAL_WWW", 		"content"=>GetMessage("PERSONAL_WWW"), "sort"=>"personal_www"),
	array("id"=>"PERSONAL_ICQ", 		"content"=>GetMessage("PERSONAL_ICQ"), "sort"=>"personal_icq"),
	array("id"=>"PERSONAL_GENDER", 	"content"=>GetMessage("PERSONAL_GENDER"), "sort"=>"personal_gender"),
	array("id"=>"PERSONAL_PHONE", 	"content"=>GetMessage("PERSONAL_PHONE"), "sort"=>"personal_phone"),
	array("id"=>"PERSONAL_MOBILE", 	"content"=>GetMessage("PERSONAL_MOBILE"), "sort"=>"personal_mobile"),
	array("id"=>"PERSONAL_CITY", 		"content"=>GetMessage("PERSONAL_CITY"), "sort"=>"personal_city"),
	array("id"=>"PERSONAL_STREET", 	"content"=>GetMessage("PERSONAL_STREET"), "sort"=>"personal_street"),
	array("id"=>"WORK_COMPANY", 		"content"=>GetMessage("WORK_COMPANY"), "sort"=>"work_company"),
	array("id"=>"WORK_DEPARTMENT", 	"content"=>GetMessage("WORK_DEPARTMENT"), "sort"=>"work_department"),
	array("id"=>"WORK_POSITION", 		"content"=>GetMessage("WORK_POSITION"), "sort"=>"work_position"),
	array("id"=>"WORK_WWW", 			"content"=>GetMessage("WORK_WWW"), "sort"=>"work_www"),
	array("id"=>"WORK_PHONE", 		"content"=>GetMessage("WORK_PHONE"), "sort"=>"work_phone"),
	array("id"=>"WORK_CITY", 		"content"=>GetMessage("WORK_CITY"), "sort"=>"work_city"),
	array("id"=>"XML_ID", 			"content"=>GetMessage("XML_ID"), "sort"=>"external_id"),
	array("id"=>"EXTERNAL_AUTH_ID",	"content"=>GetMessage("EXTERNAL_AUTH_ID")),
));

// построение списка
while($arRes = $rsData->GetNext())
{
	$f_ID = $arRes['ID'];
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$row->AddViewField("ID", $f_ID);
	$row->AddCheckField("ACTIVE", false);
	$row->AddViewField("LOGIN", "<a href=\"javascript:SetValue('".$f_ID."');\" title=\"".GetMessage("MAIN_CHANGE")."\">".$arRes["LOGIN"]."</a>");
	$row->AddViewField("NAME", $arRes["NAME"]);
	$row->AddViewField("LAST_NAME", $arRes["LAST_NAME"]);
	$row->AddViewField("EMAIL", TxtToHtml($arRes["EMAIL"]));
	$row->AddViewField("PERSONAL_PROFESSION", $arRes[""]);
	$row->AddViewField("PERSONAL_WWW", TxtToHtml($arRes["PERSONAL_WWW"]));
	$row->AddViewField("PERSONAL_ICQ", $arRes["PERSONAL_ICQ"]);
	$row->AddViewField("PERSONAL_GENDER", $arRes["PERSONAL_GENDER"]);
	$row->AddViewField("PERSONAL_PHONE", $arRes["PERSONAL_PHONE"]);
	$row->AddViewField("PERSONAL_MOBILE", $arRes["PERSONAL_MOBILE"]);
	$row->AddViewField("PERSONAL_CITY", $arRes["PERSONAL_CITY"]);
	$row->AddViewField("PERSONAL_STREET", $arRes["PERSONAL_STREET"]);
	$row->AddViewField("WORK_COMPANY", $arRes["WORK_COMPANY"]);
	$row->AddViewField("WORK_DEPARTMENT", $arRes["WORK_DEPARTMENT"]);
	$row->AddViewField("WORK_POSITION", $arRes["WORK_POSITION"]);
	$row->AddViewField("WORK_WWW", TxtToHtml($arRes["WORK_WWW"]));
	$row->AddViewField("WORK_PHONE", $arRes["WORK_PHONE"]);
	$row->AddViewField("WORK_CITY", $arRes["WORK_CITY"]);
	$row->AddViewField("XML_ID", $arRes["XML_ID"]);

	$arActions = array();
	$arActions[] = array(
		"ICON"=>"",
		"TEXT"=>GetMessage("MAIN_CHANGE"),
		"DEFAULT"=>true,
		"ACTION"=>"SetValue('".$f_ID."');"
	);
	$row->AddActions($arActions);
}

$lAdmin->AddAdminContextMenu(array());

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("MAIN_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php")
?>
<script language="JavaScript">
<!--
function SetValue(id)
{
	<?if ($JSFUNC <> ''){?>
	window.opener.SUV<?=$JSFUNC?>(id);
	<?}else{?>
	window.opener.document.<?echo $FN;?>["<?echo $FC;?>"].value=id;
	if (window.opener.BX)
		window.opener.BX.fireEvent(window.opener.document.<?echo $FN;?>["<?echo $FC;?>"], 'change');
	window.close();
	<?}?>
}
//-->
</script>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage('MAIN_FLT_USER_ID'),
		GetMessage('MAIN_FLT_MOD_DATE'),
		GetMessage('MAIN_FLT_AUTH_DATE'),
		GetMessage('MAIN_FLT_ACTIVE'),
		GetMessage('MAIN_FLT_LOGIN'),
		GetMessage('MAIN_FLT_EMAIL'),
		GetMessage('MAIN_FLT_FIO'),
		GetMessage('MAIN_FLT_PROFILE_FIELDS'),
		GetMessage('MAIN_FLT_USER_GROUP')
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("MAIN_FLT_SEARCH")?></b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("MAIN_FLT_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="login"<?if($find_type=="login") echo " selected"?>><?=GetMessage('MAIN_FLT_LOGIN')?></option>
			<option value="email"<?if($find_type=="email") echo " selected"?>><?=GetMessage('MAIN_FLT_EMAIL')?></option>
			<option value="name"<?if($find_type=="name") echo " selected"?>><?=GetMessage('MAIN_FLT_FIO')?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_TIMESTAMP").":"?></td>
	<td><?echo CalendarPeriod("find_timestamp_1", htmlspecialcharsbx($find_timestamp_1), "find_timestamp_2", htmlspecialcharsbx($find_timestamp_2), "find_form","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_LAST_LOGIN").":"?></td>
	<td><?echo CalendarPeriod("find_last_login_1", htmlspecialcharsbx($find_last_login_1), "find_last_login_2", htmlspecialcharsbx($find_last_login_2), "find_form","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("F_ACTIVE")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage('MAIN_ALL'));
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("F_LOGIN")?></td>
	<td><input type="text" name="find_login" size="47" value="<?echo htmlspecialcharsbx($find_login)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_EMAIL")?></td>
	<td><input type="text" name="find_email" value="<?echo htmlspecialcharsbx($find_email)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("F_NAME")?></td>
	<td><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_KEYWORDS")?></td>
	<td><input type="text" name="find_keywords" value="<?echo htmlspecialcharsbx($find_keywords)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top">
	<td><?echo GetMessage("F_GROUP")?><br><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt=""></td>
	<td><?
	
	if($maxRole != LM_AUTO_MAIN_ACCESS_READ && $maxRole != LM_AUTO_MAIN_ACCESS_READ_WRITE && $maxRole != LM_AUTO_MAIN_ACCESS_FULL && !$USER->IsAdmin())
    {   
        //Выберем все группы
        $s_groups = CGroup::GetList(($by="c_sort"), ($order="desc")); // выбираем группы
        while($a_groups = $s_groups -> Fetch())
        {
            $ar_system_groups[] = $a_groups["ID"];
        }
        
        //_d($all_available_groups);
        foreach($ar_system_groups as $s_gr)
        {
           if(!in_array($s_gr, $all_available_groups))
           {
              $n_available_groups[] = $s_gr; 
           }
        }
        
       //Исключим недоступные группы из выборки
        
        foreach($n_available_groups as $n_a)
        {
          $a_str .= "AND ID!=".$n_a." ";  
        } 
		$z = CGroup::GetDropDownList("AND ID!=2 ".$a_str); 		
    }
	else $z = CGroup::GetDropDownList("AND ID!=2 ".$a_str); 
	
	echo SelectBoxM("find_group_id[]", $z, $find_group_id, "", false, 10);
	?></td>
</tr>
<input type="hidden" name="FN" value="<?echo htmlspecialcharsbx($FN)?>">
<input type="hidden" name="FC" value="<?echo htmlspecialcharsbx($FC)?>">
<input type="hidden" name="JSFUNC" value="<?echo htmlspecialcharsbx($JSFUNC)?>">
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
// место для вывода списка
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>

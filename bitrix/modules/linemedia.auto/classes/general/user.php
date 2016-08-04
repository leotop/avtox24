<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts helper
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);


/**
 * �����, ���������� �� ������ � ��������������.
 */
class LinemediaAutoUser
{
  public static function getUserBranchByManagerId($manager_id)
  {
        global $USER;
        CModule::IncludeModule("linemedia.autobranches");
        
        //������ ���������� �������
		$managers_id = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
		
		//������ �������� �������
		 $filter = Array
		(
			"STRING_ID"  => "LM_AUTO_CABINET_DIRECTOR_DIRECTORS_GROUP",
		);
		$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter); // �������� ������
		while($arGroups = $rsGroups -> Fetch())
		{
			$director_group = $arGroups["ID"]; //� ����� ������ �������� ������� ������			
		}
       
        $manager = CUser::getByID($manager_id);
        $manager = $manager->Fetch(); 
        $branchManager = new LinemediaAutoBranchesUser($manager_id);
        
       // _d($manager);
        
        if (!empty($manager['UF_DEALER_ID']) && (in_array($managers_id, $USER->GetUserGroupArray()) || in_array($director_group, $USER->GetUserGroupArray()))) 
        { 
            $currentBranchID = $branchManager->getBranchID();    
            
            if($currentBranchID > 0)
            {
				 return $currentBranchID;
            }
			else return false;           
        } 
		else return false;		
  }

  public static function setUserBranchByManagerId($user_id, $manager_id, $is_user_new = false)
  {
        global $USER;
        CModule::IncludeModule("linemedia.autobranches");
        
       
       
        
        $managers_id = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
		
		//������ �������� �������
		 $filter = Array
		(
			"STRING_ID"  => "LM_AUTO_CABINET_DIRECTOR_DIRECTORS_GROUP",
		);
		$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter); // �������� ������
		while($arGroups = $rsGroups -> Fetch())
		{
			$director_group = $arGroups["ID"]; //� ����� ������ �������� ������� ������			
		}
       
        $manager = CUser::getByID($manager_id);
        $manager = $manager->Fetch(); 
        
        
        
        if (!empty($manager['UF_DEALER_ID']) && (in_array($managers_id, $USER->GetUserGroupArray()) || in_array($director_group, $USER->GetUserGroupArray()))) 
        {          
            // _d($manager);
             
            $branchUser = new LinemediaAutoBranchesUser($user_id);
            $branchManager = new LinemediaAutoBranchesUser($manager_id);
            
            $currentBranchID = $branchManager->getBranchID();    
            
            if($currentBranchID > 0)
            {
                if($is_user_new == true)     //���� ������������ �����
                {
                    $branchUser->setDealer($currentBranchID);     //��������� ��� ������
                }
                else
                {
                       //�������� ������ ��������
                        $filter = Array
                        (
                            "STRING_ID"  => "branch_%",
                        );
                        $rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter); // �������� ������
                        while($arGroups = $rsGroups -> Fetch())
                        {
                            if($arGroups["STRING_ID"] == "branch_".$currentBranchID)
                            {
                                $cur_filial_group = $arGroups["ID"]; //� ����� ������ �������� ������� ������
                            }
                            
                                $all_filial_groups[] = $arGroups["ID"]; //��� ������ ��������  
                        }
                        
                        $arCurUserGroups = CUser::GetUserGroup($user_id); //������ ����� �������� ������������        
                        $user_filial_groups = array_intersect($arCurUserGroups, $all_filial_groups); //������� �������� ������������
                        
                        if(!in_array($cur_filial_group, $user_filial_groups)) //���� ������� ������ ������� �� ������� ������������
                        {
                            //������ �� ����� �������
                            $branchUser->setChangeBranch($currentBranchID);
                        }                  
                }
                
                return true;               
            }
            else
            {
                return false;                
            }
            
            
        }  
      
  }

  public static function setUserManagerByManagerId($user_id, $manager_id)
  {
        $branchUser = new LinemediaAutoBranchesUser($user_id);
        if((int)$manager_id > 0)
        {    
            // ������������� ���������
            $branchUser->setManager($manager_id);        
        }  
      
  }   
}

class LinemediaAutoGroup extends CGroup
{
    /**
     * ������������� ��� ������ $module_id ������ ������ $group_id �����. $task_letter
     * @param string $module_id
     * @param int $group_id
     * @param string $task_letter
     * @param string $binding
     */
    public static function setModuleGroupTaskLetter($module_id, $group_id, $task_letter, $binding = 'module')
    {
        $arTasksFilter = array("BINDING" => $binding);
        $current_letter = LinemediaAutoGroup::getMaxPermissionId($module_id, array($group_id), $arTasksFilter);
        $current_task = CTask::GetIdByLetter($current_letter, $module_id, $binding);

        $newTaskId = CTask::GetIdByLetter($task_letter, $module_id, $binding);

        $arAllTasks = LinemediaAutoGroup::GetTasksForModule('linemedia.auto');
        $arSetTask = array('CTASKS' => array());

        foreach($arAllTasks as $k => $arGrTask) {

            foreach($arGrTask as $groupId => $aTask) {

                if($groupId == $group_id && $aTask['ID'] == $current_task) {
                    continue;
                }
                $arSetTask['CTASKS'][][$groupId]['ID'] = $aTask['ID'];
            }
        }
        $arSetTask['CTASKS'][][$group_id]['ID'] = $newTaskId;

        LinemediaAutoGroup::SetTasksForModule($module_id, $arSetTask);
    }

    /*������������ ����������� �������, ����� �������� � ��������� ������ ��� ������ � ���� �� ������*/
    public static function SetTasksForModule($module_id, $arGroupTask)
    {
        global $DB;
        $module_id = $DB->ForSql($module_id);
        $sql_str = "SELECT T.ID
            FROM b_task T
            WHERE T.MODULE_ID='".$module_id."'";
        $r = $DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        $arIds = array();
        while($arR = $r->Fetch())
            $arIds[] = $arR['ID'];

        if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
        {
            //get old values
            $arOldTasks = array();
            if(!empty($arIds))
            {
                $rsTask = $DB->Query("SELECT GROUP_ID, TASK_ID FROM b_group_task WHERE TASK_ID IN (".implode(",", $arIds).")");
                while($arTask = $rsTask->Fetch())
                    $arOldTasks[$arTask["GROUP_ID"]] = $arTask["TASK_ID"];
            }
            //compare with new ones
            foreach($arOldTasks as $gr_id=>$task_id)
                if($task_id <> $arGroupTask[$gr_id]['ID'])
                    CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $gr_id, $module_id.": (".$task_id.") => (".$arGroupTask[$gr_id]['ID'].")");
            foreach($arGroupTask as $gr_id => $oTask)
                if(intval($oTask['ID']) > 0 && !array_key_exists($gr_id, $arOldTasks))
                    CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $gr_id, $module_id.": () => (".$oTask['ID'].")");
        }
    
        //println($arIds);
        
        if(!empty($arIds))
        {
            $sql_str = "DELETE FROM b_group_task WHERE TASK_ID IN (".implode(",", $arIds).")";
            $DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        }

        $arrTasks = array();
        foreach($arGroupTask as $nameType => $t)
        {
            if($nameType == "CTASKS" || $nameType == "STASKS")
            {
                foreach($t as $k => $grArr)
                {
                    foreach($grArr as $gr_id => $oTask)
                    {                            
                        if(intval($oTask['ID']) > 0)
                        {        
                            if(!in_array($oTask['ID'], $arrTasks[$k]))
                            {
                                                
                            $DB->Query(
                                "INSERT IGNORE INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
                                "SELECT G.ID, T.ID, 'task_".intval($oTask['ID'])."_".intval($gr_id)."'". //��������� external_id, ����� ����� �� ������������
                                "FROM b_group G, b_task T ".											//������������ �-��� �������
                                "WHERE G.ID = ".intval($gr_id)." AND
                                T.ID = ".intval($oTask['ID']),
                                false, "File: ".__FILE__."<br>Line: ".__LINE__
                            );
                            $arrTasks[$k] = $oTask['ID'];
                            }
                        }
                    }
                }
            }
            else
            {
                if(intval($oTask['ID']) > 0)
                {
                    $DB->Query(
                        "INSERT IGNORE INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
                        "SELECT G.ID, T.ID, 'task_".intval($oTask['ID'])."_".intval($gr_id)."'".
                        "FROM b_group G, b_task T ".
                        "WHERE G.ID = ".intval($gr_id)." AND
                        T.ID = ".intval($oTask['ID']),
                        false, "File: ".__FILE__."<br>Line: ".__LINE__
                    );
                }
            }
        }

        /*
        * C����� �������, � ��������� ��� ��������� ���� �� ���������
        */
        $events = GetModuleEvents('linemedia.auto', 'OnAfterSetTasksForModule');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$module_id, &$arGroupTask));
        }
    }
    
    /*������������ ����������� �������, ����� �������� ����� ��� ������ � ���� �� ����� � �������� ������ ������*/
    public static function GetTasksForModule($module_id, $onlyMainTasks = true)
    {
        global $DB;

        $sql_str = "SELECT GT.TASK_ID,GT.GROUP_ID,GT.EXTERNAL_ID,T.NAME
            FROM b_group_task GT
            INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
            WHERE T.MODULE_ID='".$DB->ForSQL($module_id)."'";

        $z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

        $main_arr = array();
        $ext_arr = array();
        while($r = $z->Fetch())
        {
            if (!$r['EXTERNAL_ID'])
            {
                $main_arr[][$r['GROUP_ID']] = array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME']);
            }
			elseif($r['EXTERNAL_ID']) //���������, �.�. ���� ����� ������ ����������� ����� external id, ����� ��� ������������ ������������ �-���  
			{
				$main_arr[][$r['GROUP_ID']] = array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME']);
			}
            elseif(!$onlyMainTasks)
            {
                if (!isset($ext_arr[$r['GROUP_ID']]))
                    $ext_arr[$r['GROUP_ID']] = array();
                $ext_arr[$r['GROUP_ID']][] = array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME'],'EXTERNAL_ID'=>$r['EXTERNAL_ID']);
            }
        }

        if ($onlyMainTasks)
            return $main_arr;
        else
            return array($main_arr,$ext_arr);
    }
    
    /**
     * �������� ������������ �� (�����) �������. ���� ���� ������ �� ��������� � ������ ��� ������ ������������,
     * � ����� ������ ����� ��������� ������ ��� ������
     * � ��������� ������ ����� ��������� ������ �� ��������.
     * @param $sModuleId
     * @param $curUserGroup - ������ ����� ������������ $USER->GetUserGroupArray()
     * @param $arTasksFilter ������ �� ��������, �������� array("BINDING" => "linemedia_auto_order");
     * @return string
     */
    public static function getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter)
    {        
        global $USER;
        if($USER->isAdmin() || php_sapi_name() == 'cli') {
           return LM_AUTO_MAIN_ACCESS_FULL;
        }

        // ���� ������������� ������ � �����������, �� �� ������������ ������������� �� ������ ������� � ����������
        // ������� ���������� � ���������� �������, �������������� ����� �� �����, ������ 22.09.14
        if($arTasksFilter["BINDING"] == LM_AUTO_ACCESS_BINDING_SUPPLIERS) {

            $iblockId = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

            $maxRole = self::getMaxIblockPermissionId($iblockId, $curUserGroup);

            return $maxRole;
        }
        
        $arLetters = array();
 	   
        //������� ��� ��������
        $resUserGroupsPerms = LinemediaAutoGroup::getUserPermissionsForModuleBinding($sModuleId, $curUserGroup, $arTasksFilter);
		
        while($aUserGroupsPerms = $resUserGroupsPerms->Fetch())
        {
            $arUserGroupsPerms[$aUserGroupsPerms["GROUP_ID"]] = $aUserGroupsPerms;
			$arUserGroupsIds[] = $aUserGroupsPerms["GROUP_ID"];
        }
        
        //_d($arUserGroupsIds);
				
        $defaultRight = COption::GetOptionString($sModuleId, "GROUP_DEFAULT_RIGHT_".$arTasksFilter["BINDING"], "");                  
        if(!is_array($arUserGroupsPerms) || count($arUserGroupsPerms) < 1) //���� ������� �� ����������� � ���������� ����� ������ �� ���������
        {  
           //����� ���������� ����������. 
           LinemediaAutoDebug::add('LM_ACCESS_LEVEL ' . $arTasksFilter['BINDING'] . ' = ' . $defaultRight . ' (default)', false, LM_AUTO_DEBUG_WARNING);
       
		   return $defaultRight;
        }
        else 
        {
			if(in_array(2, $arUserGroupsIds)) //���� ���-�� �� �������� ��� �������� ������ �� ������ ��� ������������ � ���������� �������� ������ 
			{
				$GROUP_DEFAULT_RIGHT = COption::GetOptionString($sModuleId, "GROUP_DEFAULT_RIGHT_".$arTasksFilter["BINDING"], LM_AUTO_MAIN_ACCESS_DENIED);
							
				if($arUserGroupsPerms["2"]["LETTER"] != $GROUP_DEFAULT_RIGHT) // � ���� ������ �� ����� ������� �� ���������
				{			
					$arTaskInModule = LinemediaAutoGroup::GetTasksForModule($sModuleId);			
					$task_read_id = CTask::GetIdByLetter($GROUP_DEFAULT_RIGHT, $sModuleId, $arTasksFilter["BINDING"]);
					foreach($arTaskInModule as $i => $grTask)
					{
						foreach($grTask as $k => $v)
						{
							if((int)$k == 2)
							{
								$arTaskInModule[$i][$k]["ID"] = $task_read_id; // ������� ��� ������ ������� �� ���������, ��������� �� �����
							}
							
							unset($arTaskInModule[$i][$k]["NAME"]);
						}
					}
					$arTasksInModule2 = array("CTASKS" => $arTaskInModule);				
					LinemediaAutoGroup::SetTasksForModule($sModuleId, $arTasksInModule2);
				}

				foreach($arUserGroupsPerms as $k => $group)
				{
					if((int)$group["GROUP_ID"] == 2) // � ������ ��� ������ �� �������
					{
						unset($arUserGroupsPerms[$k]);
						break;
					}
				}
			}

			if(count($arUserGroupsPerms) == 0) 
			{
				$maxRole = $GROUP_DEFAULT_RIGHT;
			}
            else
			{
				foreach($arUserGroupsPerms as $perm)
				{      
					$arLetters[$perm["LETTER"]] = $perm["LETTER"];
				}
				 $maxRole = LinemediaAutoGroup::roleHierarchy($arLetters, $defaultRight);
                // _d($maxRole);
			}
		
		}
		
		//����� ���������� ����������. 
        $arResultGroups = array(); // �� ����� ������� ������������ ������� ������ ������
        foreach($arUserGroupsPerms as $perm) {
            if($perm["LETTER"] == $maxRole) {
                $arResultGroups[] = $perm["GROUP_ID"];
            }
        }
        $arResultGroups = array_unique($arResultGroups);
        LinemediaAutoDebug::add('LM_ACCESS_LEVEL ' . $arTasksFilter['BINDING'] . ' = ' . $maxRole . ' (groups: '.join(',', $arResultGroups).')', false, LM_AUTO_DEBUG_WARNING);
            //_d($maxRole);
        return $maxRole;
    }

    /**
     * ���������� ����� ������������� ������� � ���������
     * @param $iblock_id
     * @param $groups
     * @return string
     */
    public static function getMaxIblockPermissionId($iblock_id, $groups) {

        global $USER;
        if($USER->isAdmin() || php_sapi_name() == 'cli') {
            return LM_AUTO_MAIN_ACCESS_FULL;
        }

        $right_letter = LM_AUTO_MAIN_ACCESS_DENIED;

        if(CModule::IncludeModule('iblock')) {

            $rights = array();
            $task_to_letter = array();

            $ob_rights = new CIBlockRights($iblock_id);
            $rights_rows = $ob_rights->GetRights();

            $rs = CTask::GetList(
                array("LETTER"=>"asc"),
                array(
                    "MODULE_ID" => "iblock",
                    "BINDING" => "iblock",
                )
            );
            while($ar = $rs->Fetch()) {

                $letter = $ar["LETTER"];

                // ���������� ������� �������� � ������� (������������� �� ������ ���������)
                if($letter == LM_AUTO_MAIN_BRANCH_IBLOCK_READ) $letter = LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH;
                if($letter == LM_AUTO_MAIN_BRANCH_IBLOCK_READ_WRITE) $letter = LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH;

                $task_to_letter[$ar["ID"]] = $letter;
            }

            foreach($rights_rows as $row) {
                $code = $row['GROUP_CODE'];
                $letter = $task_to_letter[$row['TASK_ID']];
                if($letter) $rights[$code] = $letter;
            }

            // ������ ��� �����
            $user_code = 'U' . $USER->GetID();
            if(array_key_exists($user_code, $rights)) {
                return $rights[$user_code];
            }

            $group_rights = array();
            foreach($groups as $group) {
                $group_code = 'G' . $group;
                if(array_key_exists($group_code, $rights)) {
                    $group_rights[] = $rights[$group_code];
                }
            }

            if(count($group_rights) > 0) {
                rsort($group_rights);
                $group_letter  = array_shift($group_rights);
                return $group_letter > $right_letter ? $group_letter : $right_letter;
            }

        }

        return $right_letter;
    }
    
    /*�������� ����� ������� ��� ����������� �������� �������� ������ � ������������ � ��������� (������, ������� �������, ���������� � �.�.)*/
    public static function getUserPermissionsForModuleBinding($module_id, $arUserGroups, $arFilter)
    {
        global $DB;

        $res = $DB -> Query("SELECT *, b_operation.ID as 'OP_ID', b_operation.NAME as 'OP_NAME', b_task_operation.TASK_ID as 'OP_TASK_ID' FROM b_task
                            LEFT JOIN b_group_task ON b_task.ID=b_group_task.TASK_ID
                            LEFT JOIN b_task_operation ON b_task.ID=b_task_operation.TASK_ID 
                            LEFT JOIN b_operation ON b_task_operation.OPERATION_ID=b_operation.ID
                            WHERE b_task.MODULE_ID = '".$module_id."' AND b_task.BINDING = '".$arFilter["BINDING"]."' AND b_group_task.GROUP_ID IN (".implode(",", $arUserGroups).") ");
                            
							
							
        return $res;
    }
    
    /*�������� �����*/
    private static function roleHierarchy($arLetters, $defaultRight)
    {    
        $arOrderLetters = array(LM_AUTO_MAIN_ACCESS_DENIED, LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN, LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH, LM_AUTO_MAIN_ACCESS_READ, LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS, LM_AUTO_MAIN_ACCESS_VIN_CLIENTS, LM_AUTO_MAIN_ACCESS_VIN_BRANCH, LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS, LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH, LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS, LM_AUTO_MAIN_ACCESS_READ_WRITE, LM_AUTO_MAIN_ACCESS_FULL);
       
       //_d($arOrderLetters);
        if(is_array($arLetters) && count($arLetters) > 0)
        {
            foreach($arLetters as $letter)
            {
                $arKeys[] = array_search($letter, $arOrderLetters);
            }
            
            $maxKey = max($arKeys); 
        
            return $arOrderLetters[$maxKey];
        }
        else return $defaultRight;
    }
    
    /*�������� ������ ������ � ������������ � ������� ������*/
    public static function makeOrderFilter($accessLevel, $arFilter)
    {
        global $USER;
        
       //var_dump($arFilter);

        $userField = false;
        if($accessLevel == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS) //�������� � �������������� ����������� ��������
        {                
            $userIds = LinemediaAutoGroup::getUserClients(); //������� �������� �������� ������������
            
            if(!empty($userIds) && is_array($userIds))
            {            
                if(!empty($arFilter) && is_array($arFilter))
                {
                    $arUserIds["USER_ID"] = $userIds;
                    $arFinal = array_merge($arFilter, $arUserIds);
                }
                else $arFinal["USER_ID"] = $userIds;
                               
                return $arFinal;
            }
            else return $arFilter;                    
        }        
        elseif($accessLevel == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH || $accessLevel == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH) // �������� � �������������� ������ �������
        {
            $filter = array("ID" => $USER->GetID());
            
            $arFilialIds = LinemediaAutoGroup::getUserDealerId(); //������ �� ������� �������� ������������
                        
            if(!empty($arFilialIds["UF_DEALER_ID"]) && is_array($arFilialIds["UF_DEALER_ID"])) 
            {
                if(!empty($arFilter) && is_array($arFilter)) $arFinal = array_merge($arFilter, $arFilialIds); //�������� � ������������ ������ ���� ��� ���������� ������ �� �� �������
                else $arFinal = $arFilialIds;
                
                return $arFinal; 
            }
            else return $arFilter;    
        }
        elseif($accessLevel == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN) // �������� � �������������� ����������� �������
        {                     
            if($USER->IsAuthorized())
            {
                $arFilter["USER_ID"] = $USER->GetID();
                return $arFilter;
            }
            else return false;
        }
        else return $arFilter;
    }
    
    /*�������� �� �������, � �������� �������� ������� ������������*/
    function getUserDealerId()
    {    
        global $USER;
        $filter = array("ID" => $USER->GetID());
        $rsUsers = CUser::GetList(($by="sort"), ($order="desc"), $filter, array("SELECT" => array("UF_DEALER_ID"))); 
        if($users = $rsUsers -> Fetch())
        {
            $arUsers = $users;
            
        }
        $arDealers = array();
        if(strlen($arUsers["UF_DEALER_ID"]) > 0) 
        {
            $arDealers["UF_DEALER_ID"] = array($arUsers["UF_DEALER_ID"]);
        }
		
        return $arDealers;
    }
    
    function getUserDealerName() //������ ��� ���������
    {    
        global $USER;
        $filter = array("ID" => $USER->GetID());
        $rsUsers = CUser::GetList(($by="sort"), ($order="desc"), $filter, array("SELECT" => array("UF_DEALER_ID"))); 
        if($users = $rsUsers -> Fetch())
        {
            $arUsers[] = $users;
            
        }
        
        
        return $arUsers;
    }
    
    /*�������� ������ �� �������� �������� ������������ */
    function getUserClients()
    {
        global $USER, $USER_FIELD_MANAGER;
        $arClientsIds = array();
        $user_fields = $USER_FIELD_MANAGER->GetUserFields("USER");

        // ���� ���� ���� UF_MANAGER_ID
        if(array_key_exists('UF_MANAGER_ID', $user_fields)) { //

            $rsData = CUser::GetList(($b='ID'), ($o='ASC'), array('UF_MANAGER_ID' => $USER->GetID()), array('ID'));
            while($arUser = $rsData->Fetch())
            {
                $arClientsIds[] = $arUser["ID"];
            }
            // ��������� ���� � �������
            $arClientsIds[] = CUser::GetID();

        } else { // ���� ���, ������������� �� ������ � �������� �������������

            $arUserSubordinateGroups = array();
            $arUserGroups = CUser::GetUserGroup($USER->GetID());
            foreach($arUserGroups as $grp)
                $arUserSubordinateGroups = array_merge($arUserSubordinateGroups, CGroup::GetSubordinateGroups($grp));

            $arFilter["CHECK_SUBORDINATE_AND_OWN"] = $USER->GetID();

            $arFilter["CHECK_SUBORDINATE"] = array_unique($arUserSubordinateGroups);

            if(count($arFilter["CHECK_SUBORDINATE"]) > 0) {

                $rsData = CUser::GetList(($b='ID'), ($o='ASC'), array('UF_MANAGER_ID' => $USER->GetID()), array('ID'));
                while($arUser = $rsData->Fetch())
                {
                    $arClientsIds[] = $arUser["ID"];
                }
            }
        }

        return $arClientsIds;
	}

    /*�������� ������ �� ������� ������� */
    function getBranchOrderIds($branchId)
    {
        $arIds = array();
        CModule::IncludeModule("sale");

        $db_order = CSaleOrder::GetList(
            array("DATE_UPDATE" => "DESC"),
            array("PROPERTY_VAL_BY_CODE_BRANCH_ID" => $branchId)
        );
        while($arOrder = $db_order->Fetch())
        {
            $arIds[] = $arOrder["ID"];
        }

        return $arIds;
    }
	
	function getLMAutoLetterAndIBLetterArray() //����������� ����� �������� ������ => ����� ���������
	{							
		/*$arAllLetters = array(LM_AUTO_MAIN_ACCESS_DENIED => LM_AUTO_MAIN_ACCESS_DENIED, LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH => LM_AUTO_MAIN_ACCESS_READ, 
							LM_AUTO_MAIN_ACCESS_READ => LM_AUTO_MAIN_ACCESS_READ,
							LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH => LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS, LM_AUTO_MAIN_ACCESS_READ_WRITE => LM_AUTO_MAIN_ACCESS_READ_WRITE,
							LM_AUTO_MAIN_ACCESS_VIN_BRANCH => LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS, LM_AUTO_MAIN_ACCESS_VIN_CLIENTS => LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS);
		return $arAllLetters;*/
		
		$arAllLetters = array(
            LM_AUTO_MAIN_ACCESS_DENIED                  => LM_AUTO_MAIN_ACCESS_DENIED,
            LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH         => LM_AUTO_MAIN_ACCESS_READ,
			LM_AUTO_MAIN_ACCESS_READ                    => LM_AUTO_MAIN_ACCESS_READ,
			LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH   => LM_AUTO_MAIN_ACCESS_READ_WRITE,
            LM_AUTO_MAIN_ACCESS_READ_WRITE              => LM_AUTO_MAIN_ACCESS_READ_WRITE,
			LM_AUTO_MAIN_ACCESS_VIN_BRANCH              => LM_AUTO_MAIN_ACCESS_READ_WRITE,
            LM_AUTO_MAIN_ACCESS_VIN_CLIENTS             => LM_AUTO_MAIN_ACCESS_READ_WRITE,
            LM_AUTO_MAIN_ACCESS_FULL                    => LM_AUTO_MAIN_ACCESS_FULL,
        );

        // �������� ������� ����������� ������ ��� ��������
        // LM_AUTO_MAIN_BRANCH_IBLOCK_READ
        // LM_AUTO_MAIN_BRANCH_IBLOCK_READ_WRITE
        $tasks = false;
        $dbTask = CTask::Getlist(array(), array('MODULE_ID'=>'iblock', 'BINDING' => 'iblock'));

        while($task = $dbTask->Fetch()) {
            $letter = $task['LETTER'];
            $tasks[$letter] = $task['ID'];
        }

        if(array_key_exists(LM_AUTO_MAIN_BRANCH_IBLOCK_READ, $tasks) && array_key_exists(LM_AUTO_MAIN_BRANCH_IBLOCK_READ_WRITE, $tasks)) {
            $arAllLetters[LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH] = LM_AUTO_MAIN_BRANCH_IBLOCK_READ;
            $arAllLetters[LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH] = LM_AUTO_MAIN_BRANCH_IBLOCK_READ_WRITE;
            $arAllLetters[LM_AUTO_MAIN_ACCESS_VIN_BRANCH] = LM_AUTO_MAIN_BRANCH_IBLOCK_READ;
        }

		return $arAllLetters;
	}

}

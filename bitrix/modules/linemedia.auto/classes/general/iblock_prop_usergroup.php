<?php

/**
 * Linemedia Autoportal
 * Main module
 * Iblock property for user groups
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);


CModule::IncludeModule('iblock');


/*
 * Привязка к группам пользователей
 */
class LinemediaAutoIblockPropertyUserGroup extends CIBlockPropertyElementList
{
    const GROUP_GUEST = 'guest';
    
    
    function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'user_group',
            'DESCRIPTION' => GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERGROUP_TITLE'),
            
            'CheckFields' => array('LinemediaAutoIblockPropertyUserGroup', 'CheckFields'),
            'GetLength' => array('LinemediaAutoIblockPropertyUserGroup', 'GetLength'),
            'GetPropertyFieldHtml' => array('LinemediaAutoIblockPropertyUserGroup', 'GetEditField'),
            'GetAdminListViewHTML' => array('LinemediaAutoIblockPropertyUserGroup', 'GetFieldView'),
            'GetPublicViewHTML' => array('LinemediaAutoIblockPropertyUserGroup', 'GetFieldView'),
            'GetPublicEditHTML' => array('LinemediaAutoIblockPropertyUserGroup', 'GetEditField'),
            
            
        );
    }
    
    
    
    function CheckFields($arProperty, $value)
    {
        return array();
    }
    
    
    function GetLength($arProperty, $value)
    {
        return strlen($value['VALUE']);
    }
    
    
    function GetEditField($arProperty, $value, $htmlElement)
    {
        if(!CModule::IncludeModule('linemedia.auto')) return;
        global $USER;
        if(!$USER->CanDoOperation('view_all_users') && !$USER->IsAdmin())
        {
            $arUserSubordinateGroups = array();
            $arUserGroups = CUser::GetUserGroup($USER->GetID());
            foreach($arUserGroups as $grp)
                $arUserSubordinateGroups = array_merge($arUserSubordinateGroups, CGroup::GetSubordinateGroups($grp));   
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
        
        if($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH || $maxRole == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH)
        {
            $ar_user = LinemediaAutoGroup::getUserDealerId();  
            $filter = Array
            (
                "STRING_ID"     => "branch_".$ar_user["UF_DEALER_ID"]["0"], //чтобы покупатель состоял в том же филиале, что и текущий пользователь 
            );
            $rsGroupsBranch = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter); // выбираем группы
            while($arrGroupsBranch = $rsGroupsBranch->Fetch())
            {
                $ar_branch_group = $arrGroupsBranch["ID"];
            }
            
            //Все группы филиалов
            $filter_branch = Array
            (
                "STRING_ID"     => "branch_%", 
            );
            $rsGroupsAll = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter_branch); // выбираем группы
            while($arrGroupsAll = $rsGroupsAll->Fetch())
            {
                if($arrGroupsAll["ID"] != $ar_branch_group)
                    $ar_all_branch_groups[] = $arrGroupsAll["ID"];
            }
                    
            //_d($ar_all_branch_groups);
            
            foreach($arUserSubordinateGroups as $k => $sub)
            {
                if(in_array($sub, $ar_all_branch_groups))
                {
                    unset($arUserSubordinateGroups[$k]);
                }    
            }
            $arUserSubordinateGroups = array_unique($arUserSubordinateGroups);   
        }
        elseif($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN)
        {
            if(!$USER->CanDoOperation('view_all_users') && !$USER->IsAdmin())
            {
                $own_group = CUser::GetUserGroup($USER->GetId());
                foreach($own_group as $o_gr)
                {
                    if(!in_array($o_gr, $arUserSubordinateGroups))
                    {
                        $arUserSubordinateGroups[] = $o_gr;   
                    }
                }
            }  
        }            
        
        $str  = '<select name="' . $htmlElement['VALUE'] . '">';
        $str .= '<option value="">' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERGROUP_ALL') . '</option>';
        $str .= '<option value="'.self::GROUP_GUEST.'" '.(($value['VALUE'] == self::GROUP_GUEST) ? ('selected') : ('')).'>' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERGROUP_GUEST') . '</option>';
        
        $rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), array("ID" => implode("|", $arUserSubordinateGroups)));
        while ($group = $rsGroups->Fetch()) {
            $selected = ($value['VALUE'] == $group['ID']) ? ' selected' : '';
            $str .= '<option value="' . $group['ID'] . '"' . $selected . '>' . $group['NAME'] . '</option>';
        } 
        $str .= '</select>';
        
        return $str;
    }
    
    
    function GetFieldView($arProperty, $value, $htmlElement)
    {
        return $value['VALUE'];
    }
}

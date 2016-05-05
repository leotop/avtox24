<?php 


/**
 * Linemedia Autoportal
 * Main module
 * Product status class
 *
 * @author  Linemedia
 * @since   31/03/2014
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);

/*
* Класс для работы со статусами товаров 
*/
class LinemediaAutoProductStatus
{  
   /*
   *Получить права на действия с товаром в данном статусе для данной группы пользователей 
   */ 
   
    public function getStatusesPermissions($status, $userGroupId = false)
    {         
        global $USER;          
        if(CModule::IncludeModule("sale") && CModule::IncludeModule("linemedia.auto")) 
        {
            $arTasksFilter = array("BINDING" => "linemedia_auto_order");
            $curUserGroup = $USER->GetUserGroupArray(); //массив групп пользователя    
            $sModuleId = 'linemedia.auto';
            $maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter); //максимальная роль пользователя     
    
           // $arFilter = array("ID" => 'N', "LID" => 'ru' , "GROUP_ID" => array(1, 2, 45, 53)); 
             
            if(!$USER->IsAdmin()) $arFilter = array("ID" => $status, "LID" => LANG , "GROUP_ID" => $USER->GetUserGroupArray());
            else $arFilter = array("ID" => $status, "LID" => LANG);
            
            $db =  CSaleStatus::GetList(
             array("id"=>"asc"),
             $arFilter,
             //array(),
             false,
             false,
             array()
             );
             
            $arStatus = array();
            
            while($res = $db->Fetch())
            {        
                $arStatus[] = $res;
            }

            $rights = array();

            foreach($arStatus as $status) //Просуммируем доступы 
            {
                foreach($status as $key => $val)
                {
                    if($val == "Y" && $key != "ID") 
                    	$rights[$key] = $val;
                    	
                    if($key == "ID" && !array_key_exists("ID", $rights)) 
                    	$rights["ID"] = $val;
                    	
                    if($key == "NAME" && !array_key_exists("NAME", $rights)) 
                    	$rights["NAME"] = $val;   
                }               
            }

			if($USER->IsAdmin() || $maxRole == LM_AUTO_MAIN_ACCESS_READ || $maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE)
			{
				$rightsForAdmin = array("ID" => $rights["ID"], "NAME" => $rights["NAME"], "PERM_STATUS" => "Y", "PERM_STATUS_FROM" => "Y", "PERM_VIEW" => "Y", "PERM_UPDATE" => "Y", "PERM_DELETE" => "Y");
				
               // _d($rightsForAdmin);
                return $rightsForAdmin;
			}
            elseif(is_array($rights) && !empty($rights)){
                return $rights;
            }
            else{ 
                return false;
            }
        }
        else{ 
            return false;
        }
    }
	
	public function getAllStatusesPermissions($userGroupId)
    {
        global $USER;
        $rights = array();

        if(CModule::IncludeModule("sale"))
        {
			if($USER->IsAdmin()) return array();
            if(!$USER->IsAdmin()) $arFilter = array("LID" => LANG);
			else $arFilter = array("LID" => LANG);			

			$arFilter = array("LID" => LANG);
             
			 $db =  CSaleStatus::GetList(
             array("id"=>"asc"),
             $arFilter
             );

			 while($ar=$db->Fetch()){
				foreach($ar as $key=>$value){
				if(strpos($key,'PERM')===0)
				$perm[$key] = 'N';
				}
			 }
			 
            $db =  CSaleStatus::GetList(
             array("id"=>"asc"),
             $arFilter,
			 false,
			 false,
			 array('ID')			 
             );

			 while($res = $db->Fetch())
            {     
				$arFilter2 = array("LID" => LANG, "ID"=>$res['ID'], "GROUP_ID" => $USER->GetUserGroupArray());
				$db2 =  CSaleStatus::GetList(
				 array("id"=>"asc"),
				 $arFilter2
				 );
				 
				 $rights[$res['ID']]=$perm;
				 
				 while($res2 =$db2->Fetch())
				 {
					foreach($res2 as $key => $val)
					{
						if($val == "Y" && $key != 'ID') $rights[$res['ID']][$key] = $val;
					}
				 }
            }
			
			//println($rights);echo '222';

            if(isset($arStatus) && is_array($arStatus)) {
                foreach($arStatus as $status) //Просуммируем доступы
                {
                    foreach($status as $key => $val)
                    {
                        if($val == "Y" && $key != "ID") $rights[$key] = $val;
                        if($key == "ID" && !array_key_exists("ID", $rights)) $rights["ID"] = $val;
                        if($key == "NAME" && !array_key_exists("NAME", $rights)) $rights["NAME"] = $val;
                    }
                }
            }

            if(is_array($rights) && !empty($rights)){
                return $rights;
            }
            else{ 
                return false;
            }
        }
        else{ 
            return false;
        }
    }
      
    /*Можно ли применять какие-то действия к данному товару в заказе (смена статуса и т.д.)
    * @param int $groupId - группа пользователя
    *  $activeAction - совершаемое действие (например, status_F)
    *  $arNewStatus - данные статуса, в который хотим перевести
    *  $basketId ИД записи
    */
    
    public function isActionAllowed($groupId, $activeAction, $basketId)
    {
       $pArr = array();       
       global $USER;
       
       if($USER->isAdmin() || php_sapi_name() == 'cli')
            return true;
       
       // $activeAction состоит из А_Б, нам нужно Б
       $sId = explode("_", $activeAction); //$sId["1"]
	   
       $arNewStatus = LinemediaAutoProductStatus::getStatusesPermissions($sId["1"]); 

       if(is_array($arNewStatus) && !empty($arNewStatus))
       {
             $arProdData = LinemediaAutoBasket::getProps($basketId); //свойства текущей корзины
             $arCurStatus = LinemediaAutoProductStatus::getStatusesPermissions($arProdData["status"]["VALUE"]); //права для текущего статуса            
            
            if($sId["1"] == "pay" ||  $sId["1"] == "inner_pay" ||  $sId["1"] == "pay_no" && $arCurStatus["PERM_PAYMEN"] == "Y")
            {
                return true;  
            }
            elseif($sId["1"] == "cancel" ||  $sId["1"] == "cancel_no" && $arCurStatus["PERM_CANCEL"] == "Y") 
            {
                 return true;
            }
            elseif($sId["1"] == "delivery" ||  $sId["1"] == "delivery_no" && $arCurStatus["PERM_DELIVERY"] == "Y") 
            {
                return true;   
            }
            elseif($sId["1"] == $arNewStatus["ID"])
            {    
                if($arProdData["status"]["VALUE"] != $sId["1"])
                 {
                    if($arCurStatus["PERM_STATUS_FROM"] == "Y" && $arNewStatus["PERM_STATUS"] == "Y") return true; //может ли переводить из текущего статуса в новый
                    else return false;
                     
                 }
                 else return true;               
            } else {
	            return false; 
            }
       }
       else return false; 
    }
    
    public function getStatusIdByAction($action)
    {
         
         CModule::IncludeModule("sale");
           $db =  CSaleStatus::GetList(
             array("id"=>"asc"),
             array("LID" => "ru"),
             false,
             false,
             array()
             );
          while($res = $db->Fetch())        
          {

            $arStatus[] = $res;
            
             if(preg_match("/".mb_strtolower($res["NAME"])."/i", mb_strtolower($action), $m)) {
               return $res["ID"];            
            }

        
        }
  
   }

    /**
     * @param $maxStatusParam
     * @param string|array $param
     * @return bool|CDBResult
     */
    public function getAvailableStatuses($maxStatusParam, $param = false)
   {
       //$maxStatusParam = "PERM_STATUS_FROM";
    global $APPLICATION, $USER;
    if(!CModule::IncludeModule('sale')) die();
     //ECHO LANG;
    $saleModulePermissions = $APPLICATION->GetGroupRight("sale");
    $arFilter = array("LID" => LANG);
    $arGroupByTmp = false;
    
    //if ($saleModulePermissions < "W" && !$USER->isAdmin())
    //{
        //$arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
      
     
		
		if(!$USER->IsAdmin())
		{
			$arFilter = array("LID" => LANG, $maxStatusParam => "Y", "GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray());
            $arGroupByTmp = array("ID", "NAME", "MAX" => $maxStatusParam);
			if(is_array($param)) {
                $arGroupByTmp = array_merge($arGroupByTmp, $param);
            } else {
                $arGroupByTmp[] = $param;
            }

        }
		else
		{
			$arFilter = array("LID" => LANG);
			 $arGroupByTmp = array("ID", "NAME", "MAX" => $maxStatusParam);
		}

    //}   
    
    $dbStatusListTmp = CSaleStatus::GetList(
        array("SORT" => "ASC"),
        $arFilter,
        $arGroupByTmp,
        false,
        array()
    );
     /*while($arStatusListTmp = $dbStatusListTmp->GetNext()) 
     {
        PRINTLN($arStatusListTmp);
     }*/
   return $dbStatusListTmp; 
                                                                          

   }
   
   
   
   public function getNotAvailableStatuses($statusParam)
   {
    global $APPLICATION, $USER;
    if(!CModule::IncludeModule('sale')) die();
    $saleModulePermissions = $APPLICATION->GetGroupRight("sale");
    $arFilter = array("LID" => LANG);
    $arGroupByTmp = false;
    
    //if ($saleModulePermissions < "W" && !$USER->isAdmin())
    //{
        $arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
      
     
        
        if(!$USER->IsAdmin())
        {
		$curUserGroup = $USER->GetUserGroupArray(); 
            $arFilter = array("LID" => LANG, $statusParam => "N", "GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray());
            $arGroupByTmp = array("ID", "NAME", "MAX" => $statusParam);
        
            $dbStatusListTmp = CSaleStatus::GetList(
            array("SORT" => "ASC"),
            $arFilter,
            $arGroupByTmp,
            false,
            array()
            );
            
            return $dbStatusListTmp; 
        
        }
		 /*while($statusNA = $dbStatusListTmp -> Fetch())
 {
     //$arStatusNA[] = $statusNA["ID"];
	 println($statusNA);
     
 }*/
 //return $dbStatusListTmp;
        return array();
     //}

   }
   
   public function permAction($basketId, $perm)
   {
		global $USER;
		CModule::IncludeModule("sale");
		$arAllStatuses = LinemediaAutoProductStatus::getAllStatusesPermissions(1);
		$arBasket["PROPS"] = Array();
		$dbBasketProps = CSaleBasket::GetPropsList(
			array("SORT" => "ASC"),
			array("BASKET_ID" => $basketId),
			false,
			false,
			array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
		);
		while ($arBasketProps = $dbBasketProps->GetNext()) {
			$arBasket["PROPS"][$arBasketProps["CODE"]] = $arBasketProps;
		}
		if($arAllStatuses[$arBasket["PROPS"]["status"]["VALUE"]][$perm] != "Y" && !$USER->IsAdmin())
		{
			 return false; //$lAdmin->AddGroupError(GetMessage('SOD_NO_PERMS2CHANGE_PRODUCT_STATUS').$ID)
		}
		else return true; //$obasket->payItem($ID, 'Y');
   }
   
   
   
   /*public function getStatusById($statusId)
   {
      global $APPLICATION, $USER;
      if(!CModule::IncludeModule('sale')) die();
      $arFilter = array("LID" => LANG);
      //$arGroupByTmp = array("ID", "NAME", "PERM_STATUS", "MAX" => $maxStatusParam); 
      $dbStatus = CSaleStatus::GetList(
        array("SORT" => "ASC"),
        $arFilter,
        false,//$arGroupByTmp,
        false,
        array()
    );
    return $dbStatus;;  
   }*/

    public static function getById($statusId) {

        if(CModule::IncludeModule("sale"))
        {
            $arFilter = array("ID" => $statusId, "LID" => LANG);

            $db =  CSaleStatus::GetList(
                array("id"=>"asc"),
                $arFilter,
                false,
                false,
                array()
            );

            while($res = $db->Fetch())
            {
                return $res;
            }
        }

        return false;
    }
}

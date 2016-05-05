<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?
CModule::IncludeModule("linemedia.auto");
CModule::IncludeModule("sale");
 
$arOrder = CSaleOrder::GetByID($_REQUEST["id"]);
//println($arOrder["STATUS_ID"]);

$curStatusPerms = LinemediaAutoProductStatus::getStatusesPermissions($arOrder["STATUS_ID"]);				   				   
$dbStatusListTmp = LinemediaAutoProductStatus::getAvailableStatuses("PERM_STATUS", "PERM_STATUS");

$statusOrder = "";
 

$select = " selected";
$statusOrder .= "<option value=\"".$curStatusPerms["ID"]."\" ".$select.">[".$curStatusPerms["ID"]."] ".$curStatusPerms["NAME"]."</option>";

if($curStatusPerms["PERM_STATUS_FROM"] == "Y" || $USER->IsAdmin())
{
	while($arStatusListTmp = $dbStatusListTmp->GetNext()) 
	{
		$select = "";
		if ($arStatusListTmp["ID"] != $arOrder["STATUS_ID"]) {          
			$statusOrder .= "<option value=\"".$arStatusListTmp["ID"]."\" ".$select.">[".$arStatusListTmp["ID"]."] ".$arStatusListTmp["NAME"]."</option>";
		}
	}  
}



echo $statusOrder;         
 
  ?>

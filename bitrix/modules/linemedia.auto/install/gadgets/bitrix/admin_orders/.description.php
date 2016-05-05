<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER;
$curUserGroup = $USER->GetUserGroupArray();
$arHideGadgetOrders = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GADGET_ORDERS_HIDE'));

if(count(array_intersect($curUserGroup, $arHideGadgetOrders)) > 0 && !$USER->IsAdmin()) {
    $arDescription = Array(
        //"NAME" =>GetMessage("GD_ORDERS_NAME"),
        "DESCRIPTION" =>GetMessage("GD_ORDERS_DESC"),
        "ICON"	=>"",
        //"GROUP" => Array("ID"=>"admin_store"),
        "AI_ONLY" => true,
        "SALE_ONLY" => true
    );
} else {
    $arDescription = Array(
        "NAME" =>GetMessage("GD_ORDERS_NAME"),
        "DESCRIPTION" =>GetMessage("GD_ORDERS_DESC"),
        "ICON"	=>"",
        "GROUP" => Array("ID"=>"admin_store"),
        "AI_ONLY" => true,
        "SALE_ONLY" => true
    );
}

?>

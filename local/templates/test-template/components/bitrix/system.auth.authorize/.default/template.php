<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
Для перехода на эту страницу необходима авторизация<br/>
<br/>
<? $APPLICATION->IncludeComponent(
	"bxmod:auth.dialog", 
	"auth_page", 
	array(
		"SUCCESS_RELOAD_TIME" => "5",
		"COMPONENT_TEMPLATE" => "auth_page"
	),
	false
); ?>
<br/>
Регистрация занимает около 30 секунд<br/>
<br/>
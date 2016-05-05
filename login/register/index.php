<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация");
?><?$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.main.register", 
	".default", 
	array(
		"USE_EMAIL_AS_LOGIN" => "Y",
		"SHOW_FIELDS" => array(
			0 => "NAME",
			1 => "LAST_NAME",
			2 => "PERSONAL_PHONE",
		),
		"REQUIRED_FIELDS" => array(
			0 => "PERSONAL_PHONE",
		),
		"AUTH" => "Y",
		"USE_BACKURL" => "Y",
		"SUCCESS_PAGE" => "/personal/",
		"SET_TITLE" => "Y",
		"USER_PROPERTY" => array(
		),
		"PERSON_SALE_PROFILE_FIELDS" => array(
		),
		"GET_SUBSCRIBE" => "Y",
		"SUBSCRIBE_RUBRICS" => array(
			0 => "1",
		),
		"USER_PROPERTY_NAME" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
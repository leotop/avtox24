<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Регистрация");
?> <?$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.main.register",
	".default",
	Array(
		"USER_PROPERTY_NAME" => "",
		"USE_EMAIL_AS_LOGIN" => "N",
		"PERSON_SALE_PROFILE_FIELDS" => array(),
		"SHOW_FIELDS" => array("NAME", "LAST_NAME"),
		"REQUIRED_FIELDS" => array("NAME", "LAST_NAME"),
		"AUTH" => "Y",
		"USE_BACKURL" => "Y",
		"SUCCESS_PAGE" => "",
		"SET_TITLE" => "Y",
		"USER_PROPERTY" => array(),
		"GET_SUBSCRIBE" => "Y",
		"SUBSCRIBE_RUBRICS" => array()
	)
);?>  <? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
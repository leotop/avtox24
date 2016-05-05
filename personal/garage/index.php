<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Гараж");   
?>
<?  // Гараж пользователя.
    
    $APPLICATION->IncludeComponent(
	"linemedia.autogarage:personal.garage", 
	"garage_new", 
	array(
		"TECDOC_URL" => "/auto/tecdoc/",
		"GARAGE_URL" => "/personal/garage/",
		"ACTION_VAR" => "act",
		"SHOW_CAR_BRANDS_IN_LINK" => "Y",
		"MODIFICATIONS_SET" => "default",
		"SET_TITLE" => "Y",
		"COMPONENT_TEMPLATE" => "garage_new"
	),
	false
);   
?>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php") ?>
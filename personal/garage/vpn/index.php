<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Гараж");   
?>
<?$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.request.vin.iblock", 
	"garage", 
	array(
		"SEF_MODE" => "Y",
		"TICKETS_PER_PAGE" => "10",
		"TICKET_SORT_ORDER" => "desc",
		"SET_PAGE_TITLE" => "N",
		"SEF_FOLDER" => "/personal/garage/vpn/",
		"COMPONENT_TEMPLATE" => "garage",
		"MODIFICATIONS_SET" => "default",
		"SEF_URL_TEMPLATES" => array(
			"list" => "",
			"edit" => "#ID#/",
		)
	),
	false
);?> 

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php") ?>
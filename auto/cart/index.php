<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
$APPLICATION->SetTitle("Корзина");
?> <?$APPLICATION->IncludeComponent(
	"linemedia.auto:store.sale.basket.basket", 
	".default", 
	array(
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"PATH_TO_ORDER" => "/auto/order/",
		"HIDE_COUPON" => "N",
		"COLUMNS_LIST" => array(
			0 => "NAME",
			1 => "PRICE",
			2 => "QUANTITY",
			3 => "DELETE",
		),
		"QUANTITY_FLOAT" => "N",
		"PRICE_VAT_SHOW_VALUE" => "N",
		"AJAX_RECALC" => "Y",
		"CHECK_BASKET_ITEMS" => "N",
		"SORT_ITEM" => "DATE_INSERT",
		"SORT_DIRECTION" => "SORT_ASC",
		"HIDE_PROPERTIES" => array(
			0 => "supplier_id",
			1 => "",
		),
		"AJAX_OPTION_ADDITIONAL" => "",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
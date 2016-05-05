<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent(
	"linemedia.auto:sale.order.ajax.visual", 
	".default", 
	array(
		"PAY_FROM_ACCOUNT" => "Y",
		"COUNT_DELIVERY_TAX" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"ALLOW_AUTO_REGISTER" => "Y",
		"SEND_NEW_USER_NOTIFY" => "Y",
		"DELIVERY_NO_AJAX" => "Y",
		"DELIVERY_NO_SESSION" => "Y",
		"TEMPLATE_LOCATION" => ".default",
		"DELIVERY_TO_PAYSYSTEM" => "d2p",
		"USE_PREPAYMENT" => "N",
		"PROP_1" => array(
		),
		"PROP_2" => array(
		),
		"PATH_TO_BASKET" => "/auto/cart/",
		"PATH_TO_PERSONAL" => "/personal/",
		"PATH_TO_PAYMENT" => "payment/",
		"PATH_TO_AUTH" => "/login/",
		"SET_TITLE" => "Y",
		"COLUMNS_LIST" => array(
		),
		"HIDE_PROPERTIES" => array(
			0 => "supplier_id",
			1 => "supplier_title",
			2 => "brand_id",
			3 => "base_price",
			4 => "payed",
			5 => "payed_date",
			6 => "emp_payed_id",
			7 => "canceled",
			8 => "canceled_date",
			9 => "emp_canceled_id",
			10 => "status",
			11 => "date_status",
			12 => "emp_status_id",
			13 => "delivery",
			14 => "date_delivery",
			15 => "emp_delivery_id",
			16 => "",
		),
		"DISPLAY_IMG_WIDTH" => "90",
		"DISPLAY_IMG_HEIGHT" => "90",
		"MANAGER_TEMPLATE" => "Y",
		"PROP_PHIS_LICO" => array(
		),
		"PROP_UR_LICO" => array(
		)
	),
	false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
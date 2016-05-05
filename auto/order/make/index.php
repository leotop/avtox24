<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("linemedia.auto:sale.order.ajax", ".default", array(
	"PAY_FROM_ACCOUNT" => "Y",
	"COUNT_DELIVERY_TAX" => "N",
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
	"ALLOW_AUTO_REGISTER" => "Y",
	"SEND_NEW_USER_NOTIFY" => "Y",
	"DELIVERY_NO_AJAX" => "Y",
	"PROP_1" => array(
	),
	"PROP_2" => array(
	),
	"PATH_TO_BASKET" => "/personal/cart/",
	"PATH_TO_PERSONAL" => "/personal/orders/",
	"PATH_TO_PAYMENT" => "/personal/order/payment/",
	"PATH_TO_AUTH" => "/personal/auth/",
	"SET_TITLE" => "Y",
	"HIDE_PROPERTIES" => array(
		0 => "supplier_id",
		1 => "supplier_title",
		2 => "article",
		3 => "brand_id",
		4 => "brand_title",
		5 => "payed",
		6 => "payed_date",
		7 => "emp_payed_id",
		8 => "canceled",
		9 => "canceled_date",
		10 => "emp_canceled_id",
		11 => "status",
		12 => "date_status",
		13 => "emp_status_id",
		14 => "delivery",
		15 => "date_delivery",
		16 => "emp_delivery_id",
		17 => "",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

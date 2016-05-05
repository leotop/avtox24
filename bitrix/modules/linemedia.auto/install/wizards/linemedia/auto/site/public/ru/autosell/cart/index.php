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
	"PATH_TO_BASKET" => "/auto/cart/",
	"PATH_TO_PERSONAL" => "/auto/order/",
	"PATH_TO_PAYMENT" => "/auto/order/payment/",
	"PATH_TO_AUTH" => "/auto/auth/",
	"SET_TITLE" => "Y",
	"HIDE_PROPERTIES" => array(
		0 => "supplier_id",
		1 => "payed_date",
		2 => "emp_payed_id",
		3 => "canceled",
		4 => "canceled_date",
		5 => "emp_canceled_id",
		6 => "status",
		7 => "date_status",
		8 => "emp_status_id",
		9 => "delivery",
		10 => "date_delivery",
		11 => "emp_delivery_id",
		12 => "",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

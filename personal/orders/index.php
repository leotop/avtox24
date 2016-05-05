<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
$APPLICATION->SetTitle("Заказы");
?>
<div class="responsive-table-orders">
<?
    $APPLICATION->IncludeComponent("linemedia.auto:personal.orders", ".default", array(
	"COUNT_ON_PAGE" => "20",
	"USE_STATUS_COLOR" => "N",
	"PATH_TO_PAYMENT" => "/auto/order/",
	"UNION_BY_ORDERS" => "N",
	"SET_TITLE" => "Y"
	),
	false
);
?></div>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php") ?>
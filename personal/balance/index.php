<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("История платежей");
?>
<div class="responsive-table"><?$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.transactions",
	"",
	Array(
        "ORDERS_PATH"=>'/personal/orders/'
	)
);?></div> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
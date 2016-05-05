<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Заказы менеджера");
?>

<?
$APPLICATION->IncludeComponent(
    "linemedia.autobranches:manager.orders",
    ".default",
    array(),
    false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
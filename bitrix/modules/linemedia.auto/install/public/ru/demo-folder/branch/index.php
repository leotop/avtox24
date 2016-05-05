<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Филиал");
?>

<?
$APPLICATION->IncludeComponent(
    "linemedia.autobranches:personal.dealer.change",
    ".default",
    array(),
    false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>

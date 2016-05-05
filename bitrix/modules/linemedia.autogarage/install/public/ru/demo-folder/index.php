<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Гараж");
?>
<?
    $APPLICATION->IncludeComponent(
        "linemedia.autogarage:personal.garage",
        ".default",
        array(),
        false
    );
?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php") ?>
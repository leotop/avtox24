<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Редактирование филиала");
?>
<?  // Настройки филиала
    $APPLICATION->IncludeComponent("linemedia.autobranches:manager.cabinet", ".default", array(
	"ID" => "50935",
	"USER_ID" => "",
	"HIDE_FIELDS" => array(
		0 => "managers",
	)
	),
	false
);
?>
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
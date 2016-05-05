<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Блокнот");
?><div class="responsive-table-notepad"><?$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.notepad",
	"",
	Array(
		"ADD_SECTION_CHAIN" => "N",
		"SET_TITLE_NOTEPAD" => "Y",
		"TITLE" => "Блокнот",
		"INIT_JQUERY" => "Y"
	),
false
);?></div><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
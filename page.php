<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("test");
?><?$APPLICATION->IncludeComponent(
	"bitrix:asd.share.buttons",
	"",
	Array(
		"ASD_ID" => $_REQUEST["id"],
		"ASD_TITLE" => $_REQUEST["title"],
		"ASD_URL" => $_REQUEST["url"],
		"ASD_PICTURE" => $_REQUEST["picture"],
		"ASD_TEXT" => $_REQUEST["text"],
		"ASD_LINK_TITLE" => "Расшарить в #SERVICE#",
		"ASD_SITE_NAME" => "",
		"ASD_INCLUDE_SCRIPTS" => ""
	),
false
);?>
<div>
  <br />
</div>

<div><?$APPLICATION->IncludeComponent(
	"bitrix:main.share",
	"",
	Array(
		"HIDE" => "N",
		"HANDLERS" => array("vk","lj","twitter","facebook","delicious","mailru"),
		"PAGE_URL" => "",
		"PAGE_TITLE" => ""
	),
false
);?></div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
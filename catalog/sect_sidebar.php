<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent("bitrix:sale.viewed.product", ".default", array(
	"VIEWED_COUNT" => "3",
	"VIEWED_NAME" => "Y",
	"VIEWED_IMAGE" => "Y",
	"VIEWED_PRICE" => "Y",
	"VIEWED_CURRENCY" => "default",
	"VIEWED_CANBUY" => "N",
	"VIEWED_CANBUSKET" => "Y",
	"VIEWED_IMG_HEIGHT" => "100",
	"VIEWED_IMG_WIDTH" => "100",
	"BASKET_URL" => "/personal/basket.php",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"SET_TITLE" => "N"
	),
	false
);?>
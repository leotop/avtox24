<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"PATH_TO_ORDER" => Array(
		"NAME" => GetMessage("PATH_TO_ORDER"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
		"PARENT" => "ADDITIONAL_SETTINGS"
	),
);



$arProps = array();
/*
 * ID поставщика
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
    "CODE" => "supplier_id",
);

/*
 * Название поставщика
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
    "CODE" => "supplier_title",
);

/*
 * Артикул
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
    "CODE" => "article",
);

/*
 * ID производителя
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_ID'),
    "CODE" => "brand_id",
);

/*
 * Название производителя
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
    "CODE" => "brand_title",
);

/*
 * Закупочная цена
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
    "CODE" => "brand_title",
);

/*
 * Оплата товара
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
    "CODE" => "payed",
);

/*
 * Дата оплата товара
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
    "CODE" => "payed_date",
);

/*
 * Кем изменена оплата
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
    "CODE" => "emp_payed_id",
);

/*
 * Отмена заказа
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
    "CODE" => "canceled",
);

/*
 * Дата отмены заказа
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
    "CODE" => "canceled_date",
);

/*
 * Кем отменен заказ
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
    "CODE" => "emp_canceled_id",
);

/*
 * Статус товара
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
    "CODE" => "status",
);

/*
 * Дата изменения статуса
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
    "CODE" => "date_status",
);

/*
 * Кем изменен статус
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
    "CODE" => "emp_status_id",
);

/*
 * Возможность доставки
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
    "CODE" => "delivery",
);

/*
 * Дата изменения статуса доставки
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
    "CODE" => "date_delivery",
);

/*
 * Кем изменен статус доставки
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
    "CODE" => "emp_delivery_id",
);

foreach($arProps AS $prop)
{
    $VALUES[$prop['CODE']] = $prop['NAME'];
}

$arTemplateParameters = array(
    "HIDE_PROPERTIES" => array(
		"NAME" => GetMessage("LM_AUTO_MAIN_HIDE_PROPERTIES"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"DEFAULT" => "",
		"VALUES" => $VALUES,
		"ADDITIONAL_VALUES" => "Y",
    ),
);

<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");?>
<?php
require("include/config.php");
// записываем в лог пришедшую сделку
actionLog($_REQUEST, dirname(__FILE__) . "/logs/deals.log");

$auth = array(
	"domain"      => $_REQUEST["auth"]["domain"],
	"token"       => $_REQUEST["auth"]["access_token"],
	"event_token" => $_REQUEST["event_token"]
);

// ID сделки, котоая изменилась
$deal_id = ltrim($_REQUEST["document_id"][2], "DEAL_");

if (intval($deal_id) > 0) {
	// получаем детальные данные по этой сделке
	$deal = call($auth['domain'], "crm.deal.get", array(
		"auth"        => $auth['token'],
		"event_token" => $auth['event_token'],
		"id"          => $deal_id
		)
	);
	
	// записываем в лог пришедшую сделку
	actionLog($deal, dirname(__FILE__) . "/logs/deal_detail.log");
	
	// получаем товарные позиции из сделки
	$products = call($auth['domain'], "crm.deal.productrows.get", array(
		"auth"        => $auth['token'],
		"event_token" => $auth['event_token'],
		"ID"          => $deal_id
	));
	
	// записываем в лог товары
	actionLog($products, dirname(__FILE__) . "/logs/products.log");
	
	// собираем товары для создания счета
	$product_rows = array();
	
	foreach ($products['result'] as $item) {
		array_push(
			$product_rows,
			array(
				"ID"           => $item['ID'],
				"PRODUCT_ID"   => $item['PRODUCT_ID'],
				"PRODUCT_NAME" => $item['PRODUCT_NAME'],
				"QUANTITY"     => $item['QUANTITY'],
				"PRICE"        => $item['PRICE']
			)
		);
	}
	// записываем в лог товары
	actionLog($product_rows, dirname(__FILE__) . "/logs/products_rows.log");
	
	if ($deal['result']['COMPANY_ID']) { // компания
		$paysystem = 1;
		$person_type = 1;
		$company = call($auth['domain'], "crm.company.get", array(
			"auth"        => $auth['token'],
			"event_token" => $auth['event_token'],
			"ID"          => $deal['result']['COMPANY_ID']
		));
		// доп. ифнормация для счета
		$info = array(
			"COMPANY" => $company['result']['TITLE']
		);
	} else { // физ-лицо
		$paysystem = 3;
		$person_type = 3;
		$contact = call($auth['domain'], "crm.contact.get", array(
			"auth"        => $auth['token'],
			"event_token" => $auth['event_token'],
			"ID"          => $deal['result']['CONTACT_ID']
		));
		// доп. ифнормация для счета
		$info = array(
			"FIO"   => $contact['result']['NAME'],
	        "EMAIL" => $contact['result']['EMAIL'][0],
	        "PHONE" => $contact['result']['PHONE'][0]                    
		);
	}
	
	// создаем счет
	$bill = call($auth['domain'], "crm.invoice.add", array(
		"auth"        => $auth['token'],
		"event_token" => $auth['event_token'],
		"fields"      => array(
			"ORDER_TOPIC"        => "Счет для " . $deal['result']['TITLE'],
			"STATUS_ID"          => "N",
			"UF_DEAL_ID"         => $deal_id,
			"PAY_SYSTEM_ID"      => $paysystem,
			"UF_COMPANY_ID"      => $deal['result']['COMPANY_ID'],
			"UF_CONTACT_ID"      => $deal['result']['CONTACT_ID'],
			"PERSON_TYPE_ID"     => $person_type,
			"INVOICE_PROPERTIES" => $info,
			"PRODUCT_ROWS"       => $product_rows
		)
	));
	
	
	// отправляем смс заказчику, если заказ с сайта
	if ($deal['result']['ORIGIN_ID'] > 0) {
		$client_phone = 0;
		$message = "Ваш заказ принят. Номер заказа " . $deal['result']['ORIGIN_ID'];
		$order_props = CSaleOrderPropsValue::GetOrderProps($deal['result']['ORIGIN_ID']);
		while ($property = $order_props->fetch()) {
			if ($property['CODE'] == "PHONE") {
				$client_phone = $property['VALUE'];
				break;
			}
		}
		if ($client_phone) {
			try {
			    sendSMS($client_phone, $message);
			} catch (Exception $e) {
			    actionLog($e->getMessage(), dirname(__FILE__) . "/logs/errors.log");
			}
		}
	}
}
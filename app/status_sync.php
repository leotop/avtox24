<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");?>
<?php
require("include/config.php");
// записываем в лог пришедшую сделку
actionLog($_REQUEST, dirname(__FILE__) . "/logs/updated_deal.log");

$auth = array(
	"domain"      => $_REQUEST["auth"]["domain"],
	"token"       => $_REQUEST["auth"]["access_token"],
	"event_token" => $_REQUEST["event_token"]
);

// ID сделки, котоая изменилась
$deal_id = $_REQUEST["data"]["FIELDS"]["ID"];
// соотношение статусов сайт->CRM
$site_to_crm = array(
	"N"  => "NEW",
	"P"  => "10",
	"A"  => "6",
	"J"  => "4",
	"S"  => "1",
	"DF" => "7",
	"F"  => "WON",
	"D"  => "LOSE"
);

if (intval($deal_id) > 0) {
	// получаем детальные данные по этой сделке
	$deal = call($auth['domain'], "crm.deal.get", array(
		"auth"        => $auth['token'],
		"event_token" => $auth['event_token'],
		"id"          => $deal_id
		)
	);
	
	// записываем в лог пришедшую сделку
	actionLog($deal, dirname(__FILE__) . "/logs/updated_deal_detail.log");
	
	if (is_array($deal['result']) && $site_order_id = $deal['result']['ORIGIN_ID']) {
		// пробуем найти данный заказ на сайте
		$order_result = CSaleOrder::GetList(
			array("ID" => "ASC"),
			array("ID" => $site_order_id),
			false,
			false,
			array("ID", "STATUS_ID")
		);
		if ($order = $order_result->Fetch()) {
			// записываем заказ на сайте
			actionLog($order, dirname(__FILE__) . "/logs/site_order.log");
			$crm_stage = $deal['result']['STAGE_ID']; // статус сделки в CRM
			$site_stage = $order['STATUS_ID']; // статус заказа на сайте
			// если CRM статус есть в массиве, то мы можем обновить заказ
			if (
				in_array($crm_stage, $site_to_crm)
				&& $site_to_crm[$site_stage] != $crm_stage
				&& $new_site_stage = array_search($crm_stage, $site_to_crm)
			) {
				CSaleOrder::Update(
					$site_order_id, 
					array(
						"STATUS_ID" => $new_site_stage
					)
				);
				// записываем успешный апдейт
				actionLog(array($site_order_id => "New status " . $new_site_stage . " CRM stage " . $crm_stage), dirname(__FILE__) . "/logs/update_log.log");
			}
		}
	}
}
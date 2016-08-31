<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
// Логируем
file_put_contents(
	dirname(__FILE__) . "/api_log_test.log", 
	var_export($_REQUEST, 1)."\n", 
	FILE_APPEND
);

// добавляем товар
if (CModule::IncludeModule("sale")) {
	
	$basket_id = CSaleBasket::Add(
		array(
		    "PRODUCT_ID"            => $_REQUEST['code_order'],
		    "PRODUCT_XML_ID"        => $_REQUEST['code_order'],
		    "PRICE"                 => $_REQUEST['price'],
		    "PRODUCT_PRICE_ID"      => 1,
		    "CURRENCY"              => "RUB",
		    "WEIGHT"                => 0,
		    "QUANTITY"              => $_REQUEST['qty_order'],
		    "LID"                   => "s1",
		    "DELAY"                 => "N",
		    "CAN_BUY"               => "Y",
		    "NAME"                  => $_REQUEST['class_cat'],
		    "MODULE"                => "linemedia.auto"
		)
	);
	
	if(!$basket_id) {
	    global $APPLICATION;
	    $error = $APPLICATION->GetException();
	    ShowError($error->GetString());
	    file_put_contents(
			dirname(__FILE__) . "/api_log_test.log", 
			var_export($error->GetString(), 1)."\n", 
			FILE_APPEND
		);
	} else {
		echo $basket_id;
		file_put_contents(
			dirname(__FILE__) . "/api_log_test.log", 
			var_export($basket_id, 1)."\n", 
			FILE_APPEND
		);
	}
	
	$arFields = array(
	    "LID" =>  's1',
	    "PERSON_TYPE_ID" => 1,
	    "PAYED" => "N",
	    "CANCELED" => "N",
	    "STATUS_ID" => "N",
	    "CURRENCY" => "RUB",
	    "USER_ID" => 671,
	    "PAY_SYSTEM_ID" => 11,
	    "USER_DESCRIPTION" => $_REQUEST['comment'],
	    "COMMENTS" => "Номер запчасти: " . $_REQUEST['partnumber'] . " | Производитель: " . $_REQUEST['class_man']
	);
	$ORDER_ID = CSaleOrder::Add($arFields);
	CSaleBasket::OrderBasket($ORDER_ID, CSaleBasket::GetBasketUserID());
}
?>
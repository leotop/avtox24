<?php
/*
* Описание данных для создания заказа
*/
$structures["Struct_Order"] = array(
    //"ID" 					=> array("varType" => "integer"),
    "LID" 					=> array("varType" => "string"),
    "PERSON_TYPE_ID" 		=> array("varType" => "integer"),
    "PAYED" 				=> array("varType" => "string"),
    "DATE_PAYED" 			=> array("varType" => "string"),
    "EMP_PAYED_ID" 			=> array("varType" => "integer"),
    "CANCELED" 				=> array("varType" => "string"),
    "DATE_CANCELED" 		=> array("varType" => "string"),
    "EMP_CANCELED_ID" 		=> array("varType" => "integer"),
    "REASON_CANCELED" 		=> array("varType" => "string"),
    "STATUS_ID" 			=> array("varType" => "string"),
    "DATE_STATUS" 			=> array("varType" => "string"),
    "EMP_STATUS_ID" 		=> array("varType" => "integer"),
    "PRICE_DELIVERY" 		=> array("varType" => "float"),
    "ALLOW_DELIVERY" 		=> array("varType" => "string"),
    "DATE_ALLOW_DELIVERY" 	=> array("varType" => "string"),
    "EMP_ALLOW_DELIVERY_ID" => array("varType" => "integer"),
    "PRICE" 				=> array("varType" => "float"),
    "CURRENCY" 				=> array("varType" => "string"),
    "DISCOUNT_VALUE" 		=> array("varType" => "float"),
    "USER_ID" 				=> array("varType" => "integer"),
    "PAY_SYSTEM_ID" 		=> array("varType" => "integer"),
    "DELIVERY_ID" 			=> array("varType" => "string"),
    "DATE_INSERT" 			=> array("varType" => "string"),
    "DATE_UPDATE" 			=> array("varType" => "string"),
    "USER_DESCRIPTION" 		=> array("varType" => "string"),
    "ADDITIONAL_INFO" 		=> array("varType" => "string"),
    "PS_STATUS" 			=> array("varType" => "string"),
    "PS_STATUS_CODE" 		=> array("varType" => "string"),
    "PS_STATUS_DESCRIPTION" => array("varType" => "string"),
    "PS_STATUS_MESSAGE" 	=> array("varType" => "string"),
    "PS_SUM" 				=> array("varType" => "float"),
    "PS_CURRENCY" 			=> array("varType" => "string"),
    "PS_RESPONSE_DATE" 		=> array("varType" => "string"),
    "COMMENTS" 				=> array("varType" => "string"),
    "TAX_VALUE" 			=> array("varType" => "float"),
    "SUM_PAID" 				=> array("varType" => "float"),
    "PAY_VOUCHER_NUM" 		=> array("varType" => "string"),
    "PAY_VOUCHER_DATE" 		=> array("varType" => "string"),
    "LOCKED_BY" 			=> array("varType" => "integer"),
    "RECOUNT_FLAG" 			=> array("varType" => "string"),
    "AFFILIATE_ID" 			=> array("varType" => "integer"),
    "DELIVERY_DOC_NUM" 		=> array("varType" => "string"),
    "DELIVERY_DOC_DATE" 	=> array("varType" => "string"),
    "PROPS" 				=> array("varType" => "ArrayOfStruct_OrderProperty", "arrType" => "Struct_OrderProperty"),
);

/*
* Описание свойства заказа
*/
$structures["Struct_OrderProperty"] = array(
    "ID" 				=> array("varType" => "integer"),
    "NAME" 				=> array("varType" => "string"),
    "CODE" 				=> array("varType" => "string"),
    "ORDER_ID" 			=> array("varType" => "integer"),
    "ORDER_PROPS_ID"	=> array("varType" => "integer"),
    "VALUE" 			=> array("varType" => "string"),
);


<?php

$props = array(
    array(
        'NAME' => GetMessage('LM_AUTO_SALE_PROP_ALLOW_PAYMENT'),
        'TYPE' => 'CHECKBOX',
        'REQUIED' => 'N',
        'CODE' => 'ALLOW_PAYMENT',
        'USER_PROPS' => 'N',
        'IS_LOCATION' => 'N',
        'DEFAULT_VALUE' => 'N',
        'UTIL' => 'Y',
    ),
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_SALE_PROP_LOCATION'),
        'TYPE' => 'LOCATION',
        'REQUIED' => 'N',
        'CODE' => 'LOCATION',
        'USER_PROPS' => 'N',
        'IS_LOCATION' => 'Y',
        'IS_FILTERED' => 'Y',
        'DEFAULT_VALUE' => '',
        'UTIL' => 'Y',
    ),
	array(
	   "NAME" => GetMessage('ORDER_PROP_MANAGER'),
	   "TYPE" => "TEXT",
	   "REQUIED" => "N",
	   "DEFAULT_VALUE" => "",
	   "SORT" => 100,
	   "CODE" => "MANAGER_ID",
	   "USER_PROPS" => "N",
	   "IS_LOCATION" => "N",
	   "IS_LOCATION4TAX" => "N",
	   "PROPS_GROUP_ID" => 1,
	   "SIZE1" => 0,
	   "SIZE2" => 0,
	   "DESCRIPTION" => "",
	   "IS_EMAIL" => "N",
	   "IS_PROFILE_NAME" => "N",
	   "IS_PAYER" => "N"
	),
);

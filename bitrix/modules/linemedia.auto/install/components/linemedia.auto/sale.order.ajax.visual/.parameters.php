<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arTemplates = array();

foreach(CComponentUtil::GetTemplatesList($_GET["component_name"], $_GET["template_id"]) as $template) {
    $arTemplates[$template['NAME']] = $template['NAME'];

    if($template['TEMPLATE']) {
        $templateInfo = CSiteTemplate::GetByID($template['TEMPLATE']);
        $templateInfo = $templateInfo->Fetch();
        $arTemplates[$template['NAME']] .= ' ('.$templateInfo['NAME'].')';
    }

}


$arComponentParameters = Array(
    "PARAMETERS" => Array(
        "PATH_TO_BASKET" => Array(
            "NAME" => GetMessage("SOA_PATH_TO_BASKET"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "basket.php",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_PERSONAL" => Array(
            "NAME" => GetMessage("SOA_PATH_TO_PERSONAL"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "index.php",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_PAYMENT" => Array(
            "NAME" => GetMessage("SOA_PATH_TO_PAYMENT"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "payment.php",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_AUTH" => Array(
            "NAME" => GetMessage("SOA_PATH_TO_AUTH"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "/auth/",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PAY_FROM_ACCOUNT" => Array(
            "NAME"=>GetMessage("SOA_ALLOW_PAY_FROM_ACCOUNT"),
            "TYPE" => "CHECKBOX",
            "DEFAULT"=>"Y",
            "PARENT" => "BASE",
        ),
        "COUNT_DELIVERY_TAX" => Array(
            "NAME"=>GetMessage("SOA_COUNT_DELIVERY_TAX"),
            "TYPE" => "CHECKBOX",
            "DEFAULT"=>"N",
            "PARENT" => "BASE",
        ),
        "COUNT_DISCOUNT_4_ALL_QUANTITY" => Array(
            "NAME"=>GetMessage("SOA_COUNT_DISCOUNT_4_ALL_QUANTITY"),
            "TYPE" => "CHECKBOX",
            "DEFAULT"=>"N",
            "PARENT" => "BASE",
        ),
        "ONLY_FULL_PAY_FROM_ACCOUNT" => Array(
            "NAME"=>GetMessage("SOA_ONLY_FULL_PAY_FROM_ACCOUNT"),
            "TYPE" => "CHECKBOX",
            "DEFAULT"=>"N",
            "PARENT" => "BASE",
        ),
        "ALLOW_AUTO_REGISTER" => Array(
            "NAME"=>GetMessage("SOA_ALLOW_AUTO_REGISTER"),
            "TYPE" => "CHECKBOX",
            "DEFAULT"=>"N",
            "PARENT" => "BASE",
        ),
        "SEND_NEW_USER_NOTIFY" => Array(
            "NAME"=>GetMessage("SOA_SEND_NEW_USER_NOTIFY"),
            "TYPE" => "CHECKBOX",
            "DEFAULT"=>"Y",
            "PARENT" => "BASE",
        ),
        "DELIVERY_NO_AJAX" => Array(
            "NAME" => GetMessage("SOA_DELIVERY_NO_AJAX"),
            "TYPE" => "CHECKBOX",
            "MULTIPLE" => "N",
            "DEFAULT" => "Y",
            "PARENT" => "BASE",
        ),
        "DELIVERY_NO_SESSION" => Array(
            "NAME" => GetMessage("SOA_DELIVERY_NO_SESSION"),
            "TYPE" => "CHECKBOX",
            "MULTIPLE" => "N",
            "DEFAULT" => "N",
            "PARENT" => "BASE",
        ),
        "TEMPLATE_LOCATION" => Array(
            "NAME"=>GetMessage("SBB_TEMPLATE_LOCATION"),
            "TYPE"=>"LIST",
            "MULTIPLE"=>"N",
            "VALUES"=>array(
                ".default" => GetMessage("SBB_TMP_DEFAULT"),
                "popup" => GetMessage("SBB_TMP_POPUP")
            ),
            "DEFAULT"=>".default",
            "COLS"=>25,
            "ADDITIONAL_VALUES"=>"N",
            "PARENT" => "BASE",
        ),
        "DELIVERY_TO_PAYSYSTEM" => Array(
            "NAME" => GetMessage("SBB_DELIVERY_PAYSYSTEM"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "VALUES"=>array(
                "d2p" => GetMessage("SBB_TITLE_PD"),
                "p2d" => GetMessage("SBB_TITLE_DP")
            ),
            "PARENT" => "BASE",
        ),
        "SET_TITLE" => Array(),

        "USE_PREPAYMENT" => array(
            "NAME" => GetMessage('SBB_USE_PREPAYMENT'),
            "TYPE" => "CHECKBOX",
            "MULTIPLE" => "N",
            "DEFAULT" => "N",
            "ADDITIONAL_VALUES"=>"N",
            "PARENT" => "BASE",
        ),
        'MANAGER_TEMPLATE'=> array(
            "NAME" => GetMessage('LM_AUTO_MAIN_USE_MANAGER_TEMPLATE'),
            "TYPE" => "CHECKBOX",
            "MULTIPLE" => "N",
            "DEFAULT" => "N",
            "ADDITIONAL_VALUES"=>"N",
            "PARENT" => "BASE",
            'REFRESH' => 'Y'
        ),
        "COLUMNS_LIST" => Array(
            "NAME"=>GetMessage("LM_AUTO_ADJUSTABLE_FIELDS"),
            "TYPE"=>"LIST",
            "MULTIPLE"=>"Y",
            "VALUES"=>array(
                'DEFAULT' => GetMessage('BIN_ALL'),
                "NAME" => GetMessage("BIN_NAME"),
                "PROPS" => GetMessage("BIN_PROPS"),
                "PRICE" => GetMessage("BIN_PRICE"),
                "IMAGE" => GetMessage("BIN_IMAGE"),
                "QUANTITY" => GetMessage("BIN_QUANTITY"),
                "OVERALL_WEIGHT" => GetMessage("BIN_OVERALL_WEIGHT"),
                "WEIGHT" => GetMessage("BIN_WEIGHT"),
                "DISCOUNT" => GetMessage("BIN_DISCOUNT"),
            ),
            "DEFAULT"=>array(),
            "COLS"=>25,
            "ADDITIONAL_VALUES"=>"N",
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
    )

);

if($arCurrentValues['MANAGER_TEMPLATE'] == 'Y') {
    $arComponentParameters['PARAMETERS']['MANAGER_TEMPLATE_NAME'] = array(
        "NAME" => GetMessage("LM_AUTO_MANAGER_TEMPLATE_NAME"),
        "TYPE" => "LIST",
        "MULTIPLE" => "N",
        "VALUES" =>$arTemplates,
        "DEFAULT"=>"manager",
        "PARENT" => "BASE",

    );

    $rs_groups = CGroup::GetList($sort = "NAME", $asc = "asc", array("ACTIVE" => "Y"));
    while ($row = $rs_groups->Fetch()) {
        $groups[$row["ID"]] = $row["NAME"];
    }

    $arComponentParameters['PARAMETERS']['MANAGER_GROUPS'] = array(
        "NAME" => GetMessage("LM_AUTO_MAIN_MANAGER_GROUPS"),
        "TYPE" => "LIST",
        "MULTIPLE" => "Y",
        "DEFAULT" => "",
        "VALUES" => $groups,
        "ADDITIONAL_VALUES" => "Y",
        "PARENT" => "BASE"
    );
}

if (CModule::IncludeModule("sale")) {
    $dbPerson = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"));
    while ($arPerson = $dbPerson->GetNext()) {
        $arPers2Prop = Array("" => GetMessage("SOA_SHOW_ALL"));
        $bProp = false;
        $dbProp = CSaleOrderProps::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PERSON_TYPE_ID" => $arPerson["ID"]));
        while($arProp = $dbProp -> Fetch())
        {

            $arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
            $bProp = true;
        }

        //println($arPers2Prop);

        if ($bProp) {
            $arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  Array(
                "NAME" => GetMessage("SOA_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")",
                "TYPE"=>"LIST",
                "MULTIPLE"=>"Y",
                "VALUES" => $arPers2Prop,
                "DEFAULT"=>"",
                "COLS"=>25,
                "ADDITIONAL_VALUES"=>"N",
                "PARENT" => "BASE",
            );

            //println($arPerson);

            //$arAllPersons[] = $arPerson;

            $arAllPersons[$arPerson["ID"]] = $arPerson["NAME"];
        }
    }
}

$arComponentParameters["PARAMETERS"]["PROP_PHIS_LICO"] =  Array(
    "NAME" => GetMessage("PHYS_LICO"),
    "TYPE"=>"LIST",
    "MULTIPLE"=>"Y",
    "VALUES" => $arAllPersons,
    "DEFAULT"=>"",
    "COLS"=>25,
    "ADDITIONAL_VALUES"=>"N",
    "PARENT" => "BASE",
);

$arComponentParameters["PARAMETERS"]["PROP_UR_LICO"] =  Array(
    "NAME" => GetMessage("UR_LICO"),
    "TYPE"=>"LIST",
    "MULTIPLE"=>"Y",
    "VALUES" => $arAllPersons,
    "DEFAULT"=>"",
    "COLS"=>25,
    "ADDITIONAL_VALUES"=>"N",
    "PARENT" => "BASE",
);





$arProps = array();
/*
 * ID ??????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
    "CODE" => "supplier_id",
);

/*
 * ???????? ??????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
    "CODE" => "supplier_title",
);

/*
 * ???????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
    "CODE" => "article",
);

/*
 * ID ?????????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_ID'),
    "CODE" => "brand_id",
);

/*
 * ???????? ?????????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
    "CODE" => "brand_title",
);

/*
 * ?????????? ????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
    "CODE" => "base_price",
);

/*
 * ?????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
    "CODE" => "payed",
);

/*
 * ???? ?????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
    "CODE" => "payed_date",
);

/*
 * ??? ???????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
    "CODE" => "emp_payed_id",
);

/*
 * ?????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
    "CODE" => "canceled",
);

/*
 * ???? ?????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
    "CODE" => "canceled_date",
);

/*
 * ??? ??????? ?????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
    "CODE" => "emp_canceled_id",
);

/*
 * ?????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
    "CODE" => "status",
);

/*
 * ???? ????????? ???????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
    "CODE" => "date_status",
);

/*
 * ??? ??????? ??????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
    "CODE" => "emp_status_id",
);

/*
 * ??????????? ????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
    "CODE" => "delivery",
);

/*
 * ???? ????????? ??????? ????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
    "CODE" => "date_delivery",
);

/*
 * ??? ??????? ?????? ????????
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
    "CODE" => "emp_delivery_id",
);


$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_TIME'),
    "CODE" => "delivery_time",
);

$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_TIME'),
    "CODE" => "delivery_time",
);

$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_RETAIL_CHAIN'),
    "CODE" => "retail_chain",
);

foreach ($arProps as $prop) {
    $VALUES[$prop['CODE']] = $prop['NAME'];
}

$arComponentParameters['PARAMETERS']['HIDE_PROPERTIES'] = array(
    "NAME" => GetMessage("LM_AUTO_MAIN_HIDE_PROPERTIES"),
    "TYPE" => "LIST",
    "MULTIPLE" => "Y",
    "DEFAULT" => "",
    "VALUES" => $VALUES,
    "ADDITIONAL_VALUES" => "Y",
    "PARENT" => "ADDITIONAL_SETTINGS"
);














<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CModule::IncludeModule("iblock"))
    return;

if (CModule::IncludeModule("linemedia.autotecdoc")) {
	$sets_ids = LinemediaAutoTecdocApiModifications::getSetsIds();
	$sets_ids = array_combine($sets_ids, $sets_ids);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
                "BRAND_ID" => array(
			"PARENT" => "BASE",
			"NAME" => 'ID марки',
			"TYPE" => "STRING",
                        "MULTIPLE" => "N",
                        "DEFAULT" => ""
		),
                "MODEL_ID" => array(
			"PARENT" => "BASE",
			"NAME" => 'ID модели',
			"TYPE" => "STRING",
                        "MULTIPLE" => "N",
                        "DEFAULT" => ""
		),
                "MODIFICATION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => 'ID модификации',
			"TYPE" => "STRING",
                        "MULTIPLE" => "N",
                        "DEFAULT" => ""
		),
        'MODIFICATIONS_SET' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_MODIFICATIONS_SET'),
                "TYPE" => "LIST",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'default',
                'VALUES' => array_combine($sets_ids, $sets_ids),
        ),
	),
);
?>
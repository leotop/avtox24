<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('sale');
/*
 * Needed props
 */
$neededPropsPhys = array('FIO', 'EMAIL', 'PHONE', 'ADDRESS');
$neededPropsUr = array('EMAIL', 'PHONE', 'ADDRESS', 'COMPANY', 'CONTACT_PERSON');

$defaultPhysPropsValue = array(
	'FIO'    => GetMessage('LM_AUTO_FIO_PROP_DEFAULT'),
	'EMAIL'  => 'email@email.ru',
	'PHONE'  => '8(919)99-99-99',
	'ADDRESS' => GetMessage('LM_AUTO_ADDRESS_PROP_DEFAULT')
);

$defaultUrPropsValue = array(
	'EMAIL'  => 'email@email.ru',
	'PHONE'  => '8(919)99-99-99',
	'ADDRESS' => GetMessage('LM_AUTO_ADDRESS_PROP_DEFAULT'),
	'COMPANY' => GetMessage('LM_AUTO_COMPANY_PROP_DEFAULT'),
	'CONTACT_PERSON' => GetMessage('LM_AUTO_CONTACT_PERSON_PROP_DEFAULT')
);

/*
 * Select person types
 */
$personTypes = array();

$oPersonTypes = CSalePersonType::GetList(Array("SORT" => "ASC"), Array("LID"=>SITE_ID));

while ($personType = $oPersonTypes->Fetch()) {

    if ($personType['NAME'] == GetMessage('LM_AUTO_PERSON_TYPE_PHYS')) {
	    $personTypes['phys'] = $personType['ID'];
    }

	if ($personType['NAME'] == GetMessage('LM_AUTO_PERSON_TYPE_UR')) {
		$personTypes['ur'] = $personType['ID'];
	}

}

/*
 * Select orders props ids for phys person type
 */
$physPropsIDs = array();

$oPhysProps = CSaleOrderProps::GetList(
	array("SORT" => "ASC"),
	array(
		"PERSON_TYPE_ID" => $personTypes['phys'],
	)
);

while ($physProp = $oPhysProps->Fetch()) {
	foreach($neededPropsPhys as $neededPropPhys ) {
		if($physProp['CODE'] == $neededPropPhys) {
			$physPropsIDs[$neededPropPhys] = $physProp['ID'];
			break;
		}
	}
}

/*
 * Select orders props ids for ur person type
 */
$urPropsIDs = array();

$oUrProps = CSaleOrderProps::GetList(
	array("SORT" => "ASC"),
	array(
		"PERSON_TYPE_ID" => $personTypes['ur'],
	)
);

while ($urProp = $oUrProps->Fetch()) {
	foreach($neededPropsUr as $neededPropUr ) {
		if($urProp['CODE'] == $neededPropUr) {
			$urPropsIDs[$neededPropUr] = $urProp['ID'];
			break;
		}
	}
}

/*
 * Set default values
 */


if (is_array($arResult["ORDER_PROP"]["USER_PROPS_Y"])) {
	foreach ($arResult["ORDER_PROP"]["USER_PROPS_Y"] as $key => $prop) {

		if ($_REQUEST['PERSON_TYPE'] == $personTypes['phys'] || !$_REQUEST['PERSON_TYPE']) {
			if(in_array($prop['CODE'], $neededPropsPhys)) {
				if (empty($arResult["ORDER_PROP"]["USER_PROPS_Y"][$key]['VALUE']) || empty($_REQUEST['user'])) {
					$arResult["ORDER_PROP"]["USER_PROPS_Y"][$key]['VALUE'] = $_REQUEST['ORDER_PROP_'.$physPropsIDs[$prop['CODE']]] ?: $defaultPhysPropsValue[$prop['CODE']];
				}
			}
		}

		if ($_REQUEST['PERSON_TYPE'] == $personTypes['ur']) {
			if(in_array($prop['CODE'], $neededPropsUr)) {
				if(empty($arResult["ORDER_PROP"]["USER_PROPS_Y"][$key]['VALUE']) || empty($_REQUEST['user'])) {
					$arResult["ORDER_PROP"]["USER_PROPS_Y"][$key]['VALUE'] = $_REQUEST['ORDER_PROP_'.$urPropsIDs[$prop['CODE']]] ?: $defaultUrPropsValue[$prop['CODE']];
				}
			}
		}

	}
}

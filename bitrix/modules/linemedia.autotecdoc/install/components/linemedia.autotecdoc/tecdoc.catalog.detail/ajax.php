<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!CModule::IncludeModule("linemedia.autotecdoc")) {
    ShowError(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTO_MODULE'));
    return;
}

if (!check_bitrix_sessid('sessid')) {
    die(GetMessage('LM_AUTO_NOTEPAD_ERROR_SSID'));
}



$api = new LinemediaAutoTecdocApiDriver();




/*
* Если надо только уточнить применимость
*/
if($_REQUEST['applicability'] == 'Y')
{
	$article_id = (int) $_REQUEST['article_id'];
	$brand_id = (int) $_REQUEST['manuId'];
	$args = array(
		'part_ids' => array($article_id),
	);
	
	try {
		$response = $api->query('getPartsDetails3', $args);
		$arResult['DATA'] = $response['data'][$article_id];
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		include(dirname(__FILE__) . '/templates/.default/error.php');
		return;
	}
	/**
	* Совместимость со старыми шаблонами
	*/
	$arResult['APPLICABILITY'] = array();
	foreach($arResult['DATA']['appliance'] AS $car) {
		
		if($car['manuId'] != $manuId) {
			continue;
		}
		
		if(!isset($arResult['APPLICABILITY'][$car['ID_mod']])) {
			$arResult['APPLICABILITY'][$car['ID_mod']] = array(
				'MODEL_NAME' => $car['ModelName'],
				'MODIFICATIONS' => array(
					array(
						'carDesc' => $car['ModificationName'],
						'yearOfConstructionFrom' => $car['DateMake'],
						'powerKwFrom' => $car['Kw'],
						'powerHpFrom' => $car['Hp'],
						'cylinderCapacity' => $car['CCM'],
						'constructionType' => $car['Body'],
					),
				),
			);
		} else {
			$arResult['APPLICABILITY'][$car['ID_mod']]['MODIFICATIONS'][] = array(
				'carDesc' => $car['ModificationName'],
				'yearOfConstructionFrom' => $car['DateMake'],
				'powerKwFrom' => $car['Kw'],
				'powerHpFrom' => $car['Hp'],
				'cylinderCapacity' => $car['CCM'],
				'constructionType' => $car['Body'],
			);
		}
		
	}
	
	
	include(dirname(__FILE__) . '/templates/.default/applicability.php');
	return;

}

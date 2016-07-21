<?php

global $USER;
$arResult['IS_AUTHORIZED'] = !$USER->IsAuthorized() && $arParams['HIDE_PRICE_NO_AUTH_USER'] == 'Y' ? 2 : 1;

if (!CModule::IncludeModule('iblock')) {
	ShowError(GetMessage('ShowError'));
	return;
}


if (isset($arResult['PARTS'])) {
    if (!isset($arParams['QUANTITY_ROUNDING'])) {
        $arParams['QUANTITY_ROUNDING'] = 2;
    }
    $arParams['QUANTITY_ROUNDING'] = intval($arParams['QUANTITY_ROUNDING']);

    foreach ($arResult['PARTS'] as $group_name => &$parts) {
        foreach ($parts as &$part) {
            $part['quantity'] = round($part['quantity'], $arParams['QUANTITY_ROUNDING']);
        }
        unset($part);
    }
    unset($parts);
}

/*
*
*/

$lmfields = new LinemediaAutoCustomFields();
$arResult["CUSTOM_FIELDS"] = $lmfields->getFields();

/**
 * ????????? ??????? ??? ?????????? etsp ??? ???????? ? ????????,
 * ????? ?? ???????????? ?????? ?? ?????? ??????????? ?? ???????? ????????,
 */
foreach ($arResult['CATALOGS'] as $id => $catalog) {
	if (substr_count($catalog['source'], 'etsp')) {
		$arResult['CATALOGS'][$id]['url'] = LinemediaAutoUrlHelper::getPartUrl(
			array(
				'article' => $catalog['extra']['code'], // (!empty($catalog['article'])) ? ($catalog['article']) : ($arParams['QUERY']),
				'brand_title' => strtoupper($catalog['brand_title']),
				'extra' => $catalog['extra'],
			),
			$arParams['TYPE']
		);
	}
}


/**

 к задаче № 6291 Объединение групп аналогов. shifter.ru
 если в настройках главного модуля ввести одинаковые названия для разных групп, то в выводе они
 должны объединяться в одну группу для показа

 */

$set_of_groups = array();
foreach ($arResult['PARTS'] as $group_name => $value) {

	$group = explode('_', $group_name);
	$group_id = end($group);
	$appellation_group = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ANALOGS_GROUPS_'.$group_id, GetMessage('LM_AUTO_ANALOG_GROUP_'.$group_id));

	if ($key = array_search($appellation_group, $set_of_groups)) {

		$arResult['PARTS']['analog_type_'.$key] = array_merge($arResult['PARTS']['analog_type_'.$key], $arResult['PARTS']['analog_type_'.$group_id]);
        unset($arResult['PARTS']['analog_type_'.$group_id]);
		continue;
	}

	$set_of_groups[$group_id] = $appellation_group;

}

/**

   sort of merged parts of array

 */

//$arResult['PARTS'] = LinemediaAutoPartsHelper::sorting($arResult['PARTS'], $arParams['SORT'], $arParams['ORDER']);













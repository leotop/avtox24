<?php

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
	if (substr_count($catalog['source'], 'etsp') && substr_count($catalog['source'], 'etspnew') == 0) {
		$arResult['CATALOGS'][$id]['url'] = LinemediaAutoUrlHelper::getPartUrl(
			array(
				'article' => $catalog['extra']['code'], // (!empty($catalog['article'])) ? ($catalog['article']) : ($arParams['QUERY']),
				'brand_title' => strtoupper($catalog['brand_title']),
				'extra' => $catalog['extra'],
			),
			$arParams['SEARCH_ARTICLE_URL'],
			$arParams['TYPE']
		);
	}
}












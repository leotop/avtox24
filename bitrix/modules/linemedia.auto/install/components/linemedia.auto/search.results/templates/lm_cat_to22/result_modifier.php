<?php

if (!CModule::IncludeModule('iblock')) {
	ShowError(GetMessage('ShowError'));
	return;
}

/*
 * ??? ??????
 */
if(isset($arParams['SEO_BLOCK']) && $arParams['SEO_BLOCK'] == 'Y') {

    global $APPLICATION;

    $brand_title = strip_tags(strval($_REQUEST['brand_title'])) ?: false;

    /*
     * ???? ? ??? ?????? ?????? 1 ???????, ?? ? ???????? ?????? ????? ????? ?????? ???????????? ????????
     */
    if(!$_REQUEST['brand_title'] && $this->getPageName() == 'parts') {

        $firstOriginalBrand = false;

        if(isset($arResult['PARTS']) && is_array($arResult['PARTS']))  {
            foreach($arResult['PARTS'] as $k => $group) {
                if($k == 'analog_type_N') {
                    $firstOriginalBrand = $group[0]['brand_title'];
                }
            }
        }

        if($firstOriginalBrand) {
            $wordforms = new LinemediaAutoWordForm();
            $brand_title = $wordforms->getBrandGroup($firstOriginalBrand) ?: $firstOriginalBrand;
        }
    }

    $APPLICATION->IncludeComponent("linemedia.auto:search.results.seo", ".default", array(
            "ARTICLE" => strip_tags(strval($_REQUEST['q'])),
            "BRAND_ID" => $_REQUEST["brand_id"],
            "BRAND_TITLE" => $brand_title,
            "IBLOCK_TYPE" => "linemedia_auto",
            "IBLOCK_ID" => COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SEARCH_SEO')
        ),
        false
    );
}


if (isset($arResult['PARTS'])) {
    if (!isset($arParams['QUANTITY_ROUNDING'])) {
        $arParams['QUANTITY_ROUNDING'] = 2;
    }
    $arParams['QUANTITY_ROUNDING'] = intval($arParams['QUANTITY_ROUNDING']);

    foreach ($arResult['PARTS'] as $group_name => &$parts) {
        foreach ($parts as &$part) {
            $part['quantity'] = number_format($part['quantity'], $arParams['QUANTITY_ROUNDING']);
            $part['return'] = '';
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

/*
 * ???????? ?????? ?? ???????? ??? ?????????? ????????? etsp
 */
foreach ($arResult['CATALOGS'] as $id => $catalog) {
	if (substr_count($catalog['source'], 'etsp') && substr_count($catalog['source'], 'etspnew') == 0 && substr_count($catalog['source'], 'etsptrue') == 0) {
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












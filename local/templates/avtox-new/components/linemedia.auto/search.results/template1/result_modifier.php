<?php

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

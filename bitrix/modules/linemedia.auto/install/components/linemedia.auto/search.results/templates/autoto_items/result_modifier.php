<?php
$arResult['analog_type_N'] = false; // искомая деталь

$arResult['analog'] = array(); // аналоги

if(count($arResult['PARTS']) > 0 && array_key_exists('analog_type_N', $arResult['PARTS'])) {

    $priceKey = false;
    $arPriceSort = array();
    // найдем деталь с минимальной ценой
    foreach($arResult['PARTS']['analog_type_N'] as $part) {

        $priceKey = intval($part['price_src']);

        while(array_key_exists($priceKey, $arPriceSort)) $priceKey++;

        $arPriceSort[$priceKey] = $part;
    }

    if(count($arPriceSort) > 0) {

        ksort($arPriceSort);

        $partWithMinPrice = current($arPriceSort);

        $arResult['type_N_to_basket'] = array(
            'part_id' => $partWithMinPrice['id'],
            'article' => $partWithMinPrice['article'],
            'title' => $partWithMinPrice['title'],
            'display_article' => ($partWithMinPrice['original_article']) ? $partWithMinPrice['original_article'] : $partWithMinPrice['article'],
            'quantity' => $partWithMinPrice['quantity'],
            'supplier_id' => $partWithMinPrice['supplier_id'],
            'brand_title' => $partWithMinPrice['brand_title'],
            'extra' => $partWithMinPrice['extra'],
            'price' => $partWithMinPrice['price'],
        );
    }
}


foreach ($arResult['PARTS'] as $y => $parts) {

    if($y == 'analog_type_N') continue;

    foreach ($parts as $i => $part) {

        $arResult['analog_to_basket'][] = array(
            'part_id' => $part['id'],
            'article' => $part['article'],
            'title' => $part['title'],
            'display_article' => ($part['original_article']) ? $part['original_article'] : $part['article'],
            'quantity' => $part['quantity'],
            'supplier_id' => $part['supplier_id'],
            'brand_title' => $part['brand_title'],
            'extra' => $part['extra'],
            'price' => $part['price'],
        );

    }
}

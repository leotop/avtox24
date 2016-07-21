
<?$APPLICATION->SetAdditionalCSS('/bitrix/components/linemedia.auto/search.results/templates/.default/style.css');?>
<?
/*
* Передадим в компонент "Подходит для вашего авто"
*/
$GLOBALS['car_brand'] = $arResult['PROPERTIES']['CAR_BRAND']['VALUE'][0];
$GLOBALS['car_model'] = $arResult['PROPERTIES']['CAR_MODEL']['VALUE'][0];
$GLOBALS['this_product_id'] = $arResult['ID'];

/*echo "<pre>";
print_r($arResult);*/

?>
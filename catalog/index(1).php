<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каталог товаров");
?> 


<?
if(!$_REQUEST['ajax']){};

$APPLICATION->IncludeComponent("bitrix:catalog", "lm_auto", array(
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "3",
	"HIDE_NOT_AVAILABLE" => "N",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/catalog/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "Y",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_FILTER" => "Y",
	"CACHE_GROUPS" => "N",
	"SET_STATUS_404" => "Y",
	"SET_TITLE" => "Y",
	"ADD_SECTIONS_CHAIN" => "Y",
	"ADD_ELEMENT_CHAIN" => "N",
	"USE_ELEMENT_COUNTER" => "Y",
	"USE_FILTER" => "Y",
	"FILTER_NAME" => "",
	"FILTER_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"FILTER_PROPERTY_CODE" => array(
		0 => "CAR_BRAND",
		1 => "CAR_MODEL",
		2 => "ARTNUMBER",
		3 => "MANUFACTURER",
		4 => "MATERIAL",
		5 => "COLOR",
		6 => "SPECIALOFFER",
		7 => "NEWPRODUCT",
		8 => "SALELEADER",
		9 => "SIZE",
		10 => "VISCOSITY",
		11 => "CAPACITY",
		12 => "LOCATION_OF_USE",
		13 => "VOLTAGE",
		14 => "VOLUME",
		15 => "POLARITY",
		16 => "COMPOUND",
		17 => "TYPE",
		18 => "",
	),
	"FILTER_PRICE_CODE" => array(
		0 => "Розничная",
	),
	"FILTER_OFFERS_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"FILTER_OFFERS_PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"USE_REVIEW" => "N",
	"USE_COMPARE" => "N",
	"PRICE_CODE" => array(
		0 => "Розничная",
	),
	"USE_PRICE_COUNT" => "Y",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "N",
	"PRICE_VAT_SHOW_VALUE" => "Y",
	"CONVERT_CURRENCY" => "Y",
	"CURRENCY_ID" => "RUB",
	"BASKET_URL" => "/auto/cart/",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"USE_PRODUCT_QUANTITY" => "N",
	"ADD_PROPERTIES_TO_BASKET" => "Y",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"PARTIAL_PRODUCT_PROPERTIES" => "Y",
	"PRODUCT_PROPERTIES" => array(
	),
	"OFFERS_CART_PROPERTIES" => array(
	),
	"SHOW_TOP_ELEMENTS" => "N",
	"SECTION_COUNT_ELEMENTS" => "N",
	"SECTION_TOP_DEPTH" => "1",
	"PAGE_ELEMENT_COUNT" => COption::GetOptionInt("eshop","catalogElementCount","25",SITE_ID),
	"LINE_ELEMENT_COUNT" => "3",
	"ELEMENT_SORT_FIELD" => "sort",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_SORT_FIELD2" => "id",
	"ELEMENT_SORT_ORDER2" => "desc",
	"LIST_PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"INCLUDE_SUBSECTIONS" => "N",
	"LIST_META_KEYWORDS" => "-",
	"LIST_META_DESCRIPTION" => "-",
	"LIST_BROWSER_TITLE" => "-",
	"LIST_OFFERS_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"LIST_OFFERS_PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"LIST_OFFERS_LIMIT" => "5",
	"DETAIL_PROPERTY_CODE" => array(
		0 => "CAR_BRAND",
		1 => "CAR_MODEL",
		2 => "ARTNUMBER",
		3 => "MANUFACTURER",
		4 => "MATERIAL",
		5 => "COLOR",
		6 => "SPECIALOFFER",
		7 => "NEWPRODUCT",
		8 => "SALELEADER",
		9 => "SIZE",
		10 => "VISCOSITY",
		11 => "CAPACITY",
		12 => "LOCATION_OF_USE",
		13 => "VOLTAGE",
		14 => "VOLUME",
		15 => "POLARITY",
		16 => "RECOMMEND",
		17 => "COMPOUND",
		18 => "TYPE",
		19 => "",
	),
	"DETAIL_META_KEYWORDS" => "-",
	"DETAIL_META_DESCRIPTION" => "-",
	"DETAIL_BROWSER_TITLE" => "-",
	"DETAIL_OFFERS_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"DETAIL_OFFERS_PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"LINK_IBLOCK_TYPE" => "catalog",
	"LINK_IBLOCK_ID" => "3",
	"LINK_PROPERTY_SID" => "RECOMMEND",
	"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
	"USE_ALSO_BUY" => "N",
	"ALSO_BUY_ELEMENT_COUNT" => "3",
	"ALSO_BUY_MIN_BUYES" => "2",
	"USE_STORE" => "N",
	"OFFERS_SORT_FIELD" => "sort",
	"OFFERS_SORT_ORDER" => "asc",
	"OFFERS_SORT_FIELD2" => "id",
	"OFFERS_SORT_ORDER2" => "desc",
	"PAGER_TEMPLATE" => "",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "Товары",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_DESC_NUMBERING" => "Y",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
	"PAGER_SHOW_ALL" => "Y",
	"AJAX_OPTION_ADDITIONAL" => "",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"SEF_URL_TEMPLATES" => array(
		"sections" => "",
		"section" => "#SECTION_CODE#/",
		"element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
		"compare" => "compare/",
	)
	),
	false
);?> 

<? if(!$_REQUEST['ajax']){?> 

    <?
    /*
    * Выводим товары, которые подходят на этот автомобиль
    */
    $car_brand = $GLOBALS['car_brand'];
    $car_model = $GLOBALS['car_model'];
    $this_product_id = $GLOBALS['this_product_id'];
    
    
    $arrFilterTop = array();
    if($car_brand) $arrFilterTop['PROPERTY_CAR_BRAND'] = $car_brand;
    if($car_model) $arrFilterTop['PROPERTY_CAR_MODEL'] = $car_model;
    $arrFilterTop['!ID'] = $this_product_id;
    
    
    if($car_brand || $car_model) {
    ?>
    <hr />
    <?    
        $APPLICATION->IncludeComponent("bitrix:eshop.catalog.top", "for_car", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => "3",
	"ELEMENT_SORT_FIELD" => "RAND",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "3",
	"FLAG_PROPERTY_CODE" => "",
	"OFFERS_LIMIT" => "6",
	"OFFERS_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"OFFERS_PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"OFFERS_SORT_FIELD" => "sort",
	"OFFERS_SORT_ORDER" => "asc",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_COMPARE" => "N",
	"PRICE_CODE" => array(
		0 => "BASE",
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"CONVERT_CURRENCY" => "N",
	"OFFERS_CART_PROPERTIES" => array(
	),
	"DISPLAY_IMG_WIDTH" => "130",
	"DISPLAY_IMG_HEIGHT" => "130",
	"SHARPEN" => "30"
	),
	false
);
    
    }
    ?>


    
    <hr />
    <div class="share_this">
    <?$APPLICATION->IncludeComponent(
    	"bitrix:main.share",
    	"",
    	Array(
    		"HIDE" => "N",
    		"HANDLERS" => array("vk","lj","twitter","facebook","delicious","mailru"),
    		"PAGE_URL" => "",
    		"PAGE_TITLE" => ""
    	)
    );?>
    </div>
    
    <hr />
    <div class="additional_search">
    <h5>Искать в разделе "<?$APPLICATION->ShowTitle(false)?>" по артикулу</h5>
    <?$APPLICATION->IncludeComponent("bitrix:search.title", "search_lm", array(
    										"NUM_CATEGORIES" => "1",
    										"TOP_COUNT" => "5",
    										"ORDER" => "date",
    										"USE_LANGUAGE_GUESS" => "Y",
    										"CHECK_DATES" => "N",
    										"SHOW_OTHERS" => "Y",
    										"PAGE" => SITE_DIR."auto/search/",
    										"CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
    										"CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
    										"CATEGORY_0" => array(
    											0 => "iblock_catalog",
    										),
    										"CATEGORY_0_iblock_catalog" => array(
    											0 => "all",
    										),
    										"SHOW_INPUT" => "Y",
    										"INPUT_ID" => "title-search-input",
    										"CONTAINER_ID" => "search"
    										),
    										false
    									);?>
    </div>
<?}?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
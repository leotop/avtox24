<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Поиск");
?>
<?$APPLICATION->IncludeComponent(
	"linemedia.auto:search.results.seo", 
	".default", 
	array(
		"ARTICLE" => $_REQUEST["q"],
		"BRAND_ID" => $_REQUEST["brand_id"],
		"IBLOCK_TYPE" => "linemedia_auto",
		"IBLOCK_ID" => "6",
		"COMPONENT_TEMPLATE" => ".default",
		"BRAND_TITLE" => $_REQUEST["brand_title"]
	),
	false
);?>
<?
$hideFields=array(
            0 => "weight",
            1 => "supplier",
            2 => "modified",
            3 => "notepad",
            4 => "stats",
);
if($GLOBALS['USER']->IsAuthorized()){
    $i_UserID = intval($GLOBALS['USER']->GetID());
    if($i_UserID){            
        if(in_array( "12", CUser::GetUserGroup($i_UserID)) || in_array( "1", CUser::GetUserGroup($i_UserID)) || in_array( "5", CUser::GetUserGroup($i_UserID)) ){
            $hideFields=array(
                0 => "weight",
                1 => "notepad",
                2 => "stats",
            );
        }
    }
}
?>
<?$APPLICATION->IncludeComponent(
	"linemedia.auto:search.results", 
	".default", 
	array(
		"ACTION_VAR" => "act",
		"QUERY" => $_REQUEST["q"],
		"PART_ID" => $_REQUEST["part_id"],
		"BRAND_TITLE" => $_REQUEST["brand_title"],
		"EXTRA" => $_REQUEST["extra"],
		"AUTH_URL" => "/personal/auth/",
		"BASKET_URL" => "/auto/cart/",
		"VIN_URL" => "/auto/vin/",
		"INFO_URL" => "/auto/part-detail/#BRAND#/#ARTICLE#/",
		"PATH_NOTEPAD" => "/personal/notepad/",
		"TITLE" => "Поиск запчасти #QUERY#/#ARTICLE#",
		"HIDE_FIELDS" => array(
			0 => "weight",
			1 => "stats",
		),
		"SHOW_CUSTOM_FIELDS" => array(
		),
		"USE_GROUP_SEARCH" => "Y",
		"HIDE_PRICE_NO_AUTH_USER" => "N",
		"SHOW_SUPPLIER" => array(
			0 => "12",
		),
		"REMAPPING" => "Y",
		"SHOW_BLOCKS" => "results",
		"MERGE_GROUPS" => "N",
		"ANTI_BOTS" => "Y",
		"SORT" => "delivery",
		"ORDER" => "asc",
		"LIMIT" => "15",
		"SHOW_ANALOGS" => "Y",
		"NO_SHOW_WORDFORMS" => "N",
		"SHOW_ANALOGS_STATISTICS" => "N",
		"SEARCH_MODIFICATION_SET" => "empty",
		"USE_REQUEST_FORM" => "N",
		"SET_TITLE" => "Y",
		"QUANTITY_ROUNDING" => "0",
		"SEARCH_ARTICLE_URL" => "/auto/search/#ARTICLE#/",
		"BUY_ARTICLE_URL" => "/auto/search/?part_id=#PART_ID#",
		"ORIGINAL_CATALOGS_FOLDER" => "/auto/original/",
		"RENDER_LIMIT_SEARCH" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"SEO_BLOCK" => "Y",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO"
	),
	false
);?> 

<hr />

<?$APPLICATION->IncludeComponent(
	"bitrix:search.page", 
	"search_ext", 
	array(
		"RESTART" => "N",
		"NO_WORD_LOGIC" => "N",
		"CHECK_DATES" => "N",
		"USE_TITLE_RANK" => "N",
		"DEFAULT_SORT" => "rank",
		"FILTER_NAME" => "",
		"arrFILTER" => array(
			0 => "iblock_catalog",
		),
		"arrFILTER_iblock_catalog" => array(
			0 => "3",
			1 => "27",
		),
		"SHOW_WHERE" => "N",
		"SHOW_WHEN" => "N",
		"PAGE_RESULT_COUNT" => "25",
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "Y",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Результаты поиска",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "arrows",
		"USE_LANGUAGE_GUESS" => "N",
		"USE_SUGGEST" => "N",
		"SHOW_ITEM_TAGS" => "Y",
		"TAGS_INHERIT" => "Y",
		"SHOW_ITEM_DATE_CHANGE" => "Y",
		"SHOW_ORDER_BY" => "Y",
		"SHOW_TAGS_CLOUD" => "N",
		"SHOW_RATING" => "",
		"RATING_TYPE" => "",
		"PATH_TO_USER_PROFILE" => "",
		"AJAX_OPTION_ADDITIONAL" => "",
		"COMPONENT_TEMPLATE" => "search_ext",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO"
	),
	false
);?>


<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>

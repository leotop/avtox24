
<!-- Бренды -->
 
<div class="row-fluid brands"> 	 
  <div class="row brands_bg"> 		 
    <div class="container"> 			 
      <div class="row"> 				 
        <div class="span10 offset1"> 					<?$APPLICATION->IncludeComponent(
	"linemedia.auto:tecdoc.catalog2",
	"brends_lm",
	Array(
		"SEF_MODE" => "Y",
		"DETAIL_URL" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
		"COLUMNS_COUNT" => "4",
		"ADD_SECTIONS_CHAIN" => "Y",
		"SHOW_ORIGINAL_ITEMS" => "Y",
		"GROUP_MODELS" => "Y",
		"TECDOC_BRAND_TYPES" => array(),
		"MODIFICATIONS_SET" => "main",
		"HIDE_UNAVAILABLE" => "N",
		"DISABLE_STATS" => "N",
		"INCLUDE_PARTS_IMAGES" => "Y",
		"ANTI_BOTS" => "N",
		"SEF_FOLDER" => "/auto/tecdoc/",
		"VARIABLE_ALIASES" => Array(
		)
	)
);?> 				</div>
       			</div>
     		</div>
   	</div>
 </div>

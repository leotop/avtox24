<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
CJSCore::Init(array("jquery"));

/*
* Ускоритель загрузки
* http://instantclick.io/download
*/
//$APPLICATION->AddHeadScript($this->GetFolder() . '/js/instantclick.js');
?>

<div class="lm-page-loading">
    <div class="lm-loading-place">
        <img class="lm-loading-img" src="<?=$this->GetFolder();?>/images/lm-page-loading.gif">
        <div class="lm-loading-header">
            <?=GetMessage("LM_LOADING_MSG")?>
        </div>
    </div>
</div>

<script>


	//InstantClick.init('mousedown');
	
	
    function centerLoadingImg(){
        var loading_img = $('.lm-loading-place');
        var img_top = ($(window).height() - loading_img.height()) / 2;
        var img_left = ($(window).width() - loading_img.width()) / 2;
        loading_img.css('top',img_top).css('left',img_left);
    }

    function showLoader(){
        $('.lm-page-loading').show();
        centerLoadingImg();
    }

    $(document).ready(function(){

        $("#search form").submit(function(event){
            showLoader();
        });
        var selectors = '.lm-auto-search-catalogs,';
        selectors += '.lm-auto-search-catalogs a,';
        selectors += '#lm-auto-tecdoc-catalog-groups a,';
        selectors += '.lm-auto-search-parts .sku a,';
        selectors += 'a[href*="/auto/search/"]';

        $(selectors).click(function(){
            showLoader();
        });
    });
</script>


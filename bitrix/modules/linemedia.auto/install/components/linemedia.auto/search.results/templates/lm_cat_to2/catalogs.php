<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?global $USER;?>
<div class="lm-auto-search-parts-place">
<h2><?= GetMessage('LM_AUTO_SEARCH_CATALOG_HEADER') ?></h2>
<table class="lm-auto-search-catalogs silver-table">
    <thead>
        <tr>
            <th><?=GetMessage('LM_AUTO_SEARCH_CATALOG_BRAND_TITLE')?></th>
            <th><?=GetMessage('LM_AUTO_SEARCH_CATALOG_ITEM_TITLE')?></th>
            <th><?=GetMessage('LM_AUTO_SEARCH_CATALOG_SEARCH')?></th>
            <? if (LinemediaAutoDebug::enabled() && $USER->IsAdmin()) { ?>
                <th><?= GetMessage('LM_AUTO_SEARCH_CATALOG_DEBUG') ?></th>
            <? } ?>
        </tr>
    </thead>
    <tbody>
    <? foreach ($arResult['CATALOGS'] as $catalog) { 
	    
        $extra_brands_arr = array_map('htmlspecialchars', (array) $catalog['extra']['wf_b']);        
        $extra_brands_arr = array_diff($extra_brands_arr, array($catalog['brand_title']));
	    $extra_brands = join(', ', $extra_brands_arr);
    ?>
        <tr>
            <td class="lm-auto-search-catalogs-brand"><?=$catalog['brand_title']?>
				<?if ($arParams['NO_SHOW_WORDFORMS'] != 'Y') {?>
				<span class="extra-brands"><?=$extra_brands?></span>
				<?}?>
			</td>
            <td class="lm-auto-search-catalogs-title"><?=$catalog['title']?></td>
				<? $CurUrl = str_replace("/auto/search/","/auto/search_to/",$catalog['url']); ?>
            <td class="lm-auto-search-catalogs-go"><a href="javascript:void(0);" new_url='<?=$CurUrl;?>' data-article="<?=$arResult["QUERY"];?>" data-brand="<?=$catalog['brand_title']?>"><?=GetMessage('LM_AUTO_SEARCH_CATALOG_CONTINUE')?></a></td>
            <? if (LinemediaAutoDebug::enabled() && $USER->IsAdmin()) { ?>
                <td style="min-width:400px"><pre><? unset($catalog['url']); echo print_r($catalog, true) ?></pre></td>
            <? } ?>
        </tr>
    <? } ?>
    </tbody>
   
</table>

</div>
<script>
	$(document).ready(function(){

		$('.lm-auto-search-catalogs-go a').on("click", function() {
      	var article = $(this).attr('data-article');
         var brand = $(this).attr('data-brand');
         var it_url = $(this).attr('new_url');
         var new_url = it_url+"&r=ok";
			
			$(".modal-body").html('');
			$('.popUp-overlay').hide();
			$('.modal-popUp').hide();
			$('.ItemList .button .CArt<?=str_replace(" ", "", $_REQUEST["article"]);?>').attr("data-brand",brand).attr('new_url',new_url).delay(2000).trigger('click');
		});
	});
</script>
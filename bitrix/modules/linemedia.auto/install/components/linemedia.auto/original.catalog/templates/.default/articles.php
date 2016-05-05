
<?
/*
* http://www.jacklmoore.com/zoom
*/
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.zoom-min.js');
?>

<script type="text/javascript">
$(document).ready(function(){
    //$('.zoom').zoom({url: '<?=htmlspecialchars($arResult['images_prefix']) . $arResult['GROUP_SECTION']['Image']?>'});
});
</script>

<div class="lm-auto-catalog-original-image">
    <div class="zoom">
        <img src="<?=htmlspecialchars($arResult['GROUP_SECTION']['Image']) ?>" alt="<?=htmlspecialchars($arResult['GROUP_SECTION']['Name'])?> <?=htmlspecialchars($arResult['BRAND']['Name'])?> <?=htmlspecialchars($arResult['MODEL']['Name'])?>" />
    </div>
</div>

<table class="lm-auto-catalog-original articles">
	<thead>
		<tr>
            <th style="width: 50px;"><?=GetMessage('LM_AUTO_ORIG_ARTICLE_PIC_NUMBER')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_NUMBER')?></th>			
			<th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_TITLE')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_DETAILS')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_SEARCH')?></th>
			
		</tr>
	</thead>
	<tbody>
	    <?foreach($arResult['ARTICLES'] AS $article){?>
	    
	    <?if($article['is_group'] == 1){?>
	    
	    <tr class="lm-group">
            <th colspan="5">
	        	<?=htmlspecialchars($article['Description'])?>
	        </td>
	        
	    </tr>
	    
	    <?
	    continue;
	    }
	    ?>
	    
	    
	    
	    <tr>
            <td>
	        	<b>#<?=htmlspecialchars($article['PNC'])?></b>
	        </td>
	        
            <td><?=htmlspecialchars($article['Article'])?></td>
	        
	        <td class="ucase">
	        	<?=htmlspecialchars($article['Name'])?>
	        	<span class="add_info"><?=htmlspecialchars($article['Description'])?></span>
	        </td>
            <td>
	        	<a href="<?=$arParams['SEF_FOLDER'] ?>part-info/<?=$article['ID_art']?>/"><?=GetMessage('LM_AUTO_ORIGINAL_DETAILS')?></a>	        
	        </td>
            <td>
	        	<?if($article['PRICES']){?>
	        		<?if($article['min_price'] == $article['max_price']){?>
	        			<a href="<?=$article['search_url']?>"><?=$article['PRICES'][$article['min_price']]?></a>
	        		<?}else{?>
	        			<a href="<?=$article['search_url']?>"><?=$article['PRICES'][$article['min_price']]?> - <?=$article['PRICES'][$article['max_price']]?></a>
	        		<?}?>
	        	<?}else{?>
	        		<a href="<?=$article['search_url']?>"><?=GetMessage('LM_AUTO_ORIG_ARTICLE_SEARCH')?></a>
	        	<?}?>
	        </td>
	        
	    </tr>
	    <?}?>
	</tbody>
</table>


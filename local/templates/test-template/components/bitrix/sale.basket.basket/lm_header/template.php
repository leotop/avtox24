<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="basket_header" onclick="location.href='/auto/cart/'">
<?if (count($arResult['ITEMS']['AnDelCanBuy'])>0){?>
	<img src="<?=SITE_TEMPLATE_PATH?>/images/cart_lm.png" />
	<?if (count($arResult['ITEMS']['AnDelCanBuy'])<10){?>
		<span class="num_products"><?=count($arResult['ITEMS']['AnDelCanBuy'])?></span>
	<?}else{?>
		<span class="num_products num_products2"><?=count($arResult['ITEMS']['AnDelCanBuy'])?></span>
	<?}?>
	<b>Моя корзина</b><br />
	<a href="/auto/cart"><?=count($arResult['ITEMS']['AnDelCanBuy'])?> тов. на <?=$arResult['allSum_FORMATED']?></a>
	
<?}else{?>
	<img src="<?=SITE_TEMPLATE_PATH?>/images/cart_lm.png" />
	<?if (count($arResult['ITEMS']['AnDelCanBuy'])<10){?>
		<span class="num_products"><?=count($arResult['ITEMS']['AnDelCanBuy'])?></span>
	<?}else{?>
		<span class="num_products num_products2"><?=count($arResult['ITEMS']['AnDelCanBuy'])?></span>
	<?}?>
	<b>Моя корзина</b><br />
	<a href="/auto/cart">нет товаров</a>
<?}?>
</div>
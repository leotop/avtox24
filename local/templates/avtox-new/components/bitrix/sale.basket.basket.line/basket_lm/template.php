<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?print_r($arResult);?>
<?
if (IntVal($arResult["NUM_PRODUCTS"])>0)
{
?>
	<img src="<?=SITE_TEMPLATE_PATH?>/images/cart_lm.png" />
	<?if (IntVal($arResult["NUM_PRODUCTS"])<10){?>
		<span class="num_products"><?=intval($arResult["NUM_PRODUCTS"])?></span>
	<?}else{?>
		<span class="num_products num_products2"><?=intval($arResult["NUM_PRODUCTS"])?></span>
	<?}?>
	<a class="cart" href="<?=$arParams["PATH_TO_BASKET"]?>">
		<?echo str_replace('#NUM#', intval($arResult["NUM_PRODUCTS"]), GetMessage('YOUR_CART'))?>
	</a>
<?
}
else
{
?>
	<a href="<?=$arParams["PATH_TO_BASKET"]?>"><?echo GetMessage('YOUR_CART_EMPTY')?></a>
<?
}
?>
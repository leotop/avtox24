<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult))
	return;

/*$lastSelectedItem = null;
$lastSelectedIndex = -1;

foreach($arResult as $itemIdex => $arItem)
{
	if (!$arItem["SELECTED"])
		continue;

	if ($lastSelectedItem == null || strlen($arItem["LINK"]) >= strlen($lastSelectedItem["LINK"]))
	{
		$lastSelectedItem = $arItem;
		$lastSelectedIndex = $itemIdex;
	}
} */

?>

<ul class="main_menu">
    <!--li><a href="<?=SITE_DIR?>"></a></li-->
    
<? $count_item = count($arResult)-1; ?>
<?foreach($arResult as $itemIdex => $arItem):?>
	<li class="<?if ($itemIdex == $lastSelectedIndex):?>current <?endif;?><?=$count_item==$itemIdex ? 'last' : '';?>">
		<a href="<?=$arItem["LINK"]?>">
			<img src="<?=SITE_TEMPLATE_PATH?>/images/menu/<?=$arItem["PARAMS"]["ICON"]?>.png" alt="" /><br><?=$arItem["TEXT"]?>
		</a>
	</li>
<?endforeach;?>
</ul>
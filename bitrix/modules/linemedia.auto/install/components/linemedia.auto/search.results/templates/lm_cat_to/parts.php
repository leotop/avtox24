<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>

<div class="lm-auto-search-parts-place">

<?
global $APPLICATION;

$GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'] = $arParams['HIDE_FIELDS'];

$GLOBALS['LM_AUTO_SEARCH_RESULTS_CUSTOM_FIELDS'] = $arResult['CUSTOM_FIELDS'];
$GLOBALS['LM_AUTO_SEARCH_RESULTS_SHOW_CUSTOM_FIELDS'] = $arParams['SHOW_CUSTOM_FIELDS'];

$GLOBALS['templateFolder'] = $templateFolder;
$GLOBALS['showSupplier'] = $arResult['SHOW_SUPPLIER'];
$GLOBALS['ANTI_BOTS'] = $arParams['ANTI_BOTS'];
$GLOBALS['AUTH_URL'] = $arParams['AUTH_URL'];

/*
 * Если искомого артикула нет - напишем об этом сообщение
 */
if (count($arResult['PARTS']['analog_type_N']) == 0) {
    echo '<div class="lm-auto-main-art-sought-404">' . GetMessage('LM_AUTO_SEARCH_NO_SOUGHT_ARTICLE') . '</div>';
}

/*
 * Распечатаем группы одну за другой.
 */
$n = 0;
foreach ($arResult['PARTS'] as $group_name => $parts) {
    $group = explode('_', $group_name);
    $group_id = end($group);
    $n += count($parts);
    if ($arParams['MERGE_GROUPS']) {
        echo '<h2>', GetMessage('LM_AUTO_SEARCH_RESULTS'), '</h2>';
    } else {
    	echo '<h2 data-group-id="', strval($group_id), '">', LinemediaAutoPart::getAnalogGroupTitle($group_id), '</h2>';
    }
    printPartsTable($parts, $group_id, $arParams);
}

function printPartsTable($parts, $group_id, $params = array()) {
    global $templateFolder, $APPLICATION;
?>
<table class="table" data-analog-type="<?= $group_id ?>">
    <tr>
    	<? if (!in_array('brand', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
        	<th class="brand_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_BRAND')?></th>
        <? } ?>
         <?  if (!in_array('article', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
        	<th class="article_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_ARTICLE')?></th>
        <?  } ?>
        <?  if (!$GLOBALS['ANTI_BOTS'] && !in_array('info', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { /*?>
        	<th class="info_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_INFO')?></th>
        <? */ } ?>
        <? if (!in_array('price', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
        	<th class="price_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_PRICE')?></th>
        <? } ?>
        <? if (!in_array('supplier', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS']) || $GLOBALS['showSupplier']) { ?>
        	<th class="supplier_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_SUPPLIER')?></th>
        <? } ?>
        <? if (!in_array('delivery_time', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
        	<th class="delivery_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_DELIVERY_TIME')?></th>
        <? } ?>
         <? if (!in_array('stats', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { /*?>
        	<th class="stat_bl"><?=GetMessage('LM_AUTO_SEARCH_ITEM_BASKET')?></th>
        <? */} ?>
        <th class="action_bl">&nbsp;</th>
    </tr>

    <? foreach ($parts as $part) { ?>
     <? $hash = md5(safe_json_encode($part)); ?>
     <? $selected = ($params['SELECTED']['part_id'] == $part['id'] && $params['SELECTED']['hash'] == $part['extra']['hash']) ?>
    <tr 
    	data-supplier_id="<?= $part['supplier_id'] ?>"
    	data-brand_title="<?= $part['brand_title'] ?>"
    	data-article="<?= $part['article'] ?>" 
    	data-part_id="<?= $part["id"] ?>"
    	data-extra='<?= safe_json_encode($part['extra']) ?>'
    	class="<?= ($selected) ? ('selected') : ('') ?>"
    	style="<?= ($selected) ? ('background-color: #c7fba9') : ('') ?>"
    >
    	<? if (!in_array('brand', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
       		<td class="brand_bl">
       			<?= $part['brand']['title'] ?>
       		</td>
        <? } ?>
        <? if (!in_array('article', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
       		<td class="article_bl">
       			<?= $part['article'] ?>
       		</td>
        <? } ?>
        <?  if (!$GLOBALS['ANTI_BOTS'] && !in_array('info', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { /*?>
            <td class="info_bl">
                <? if ($part['info']) { ?>
					<?  // Детальная информация.
                        $APPLICATION->IncludeComponent(
                            "linemedia.auto:search.detail.info",
                            "ajax",
                            array(
                                'AJAX'          => 'Y',
                                'BRAND'         => $part['brand_title'],
                                'ARTICLE'       => $part['article'],
                                'ARTICLE_ID'    => $part['article_id']
                            ),
                            $component
                        );
                    ?>
                <? } ?>
            </td>
        <? */ } ?>
        <? if (!in_array('price', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
        	<td class="price_bl">
            	<span title="<?= ceil($part['price_src']) ?>">
                        <nobr><?= ($part['price']) ? : ('-') ?></nobr>
                    </span>
            </td>
        <? } ?>
        <? if (!in_array('supplier', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS']) || $GLOBALS['showSupplier']) { ?>
        	<td class="supplier_bl"><?= $part['supplier']['PROPS']['visual_title']['VALUE'] ?></td>
        <? } ?>
        <? if (!in_array('delivery_time', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { ?>
        	<td class="delivery_bl">
            	<span title="<?= $part['delivery'] ?>">
                	<?= ($part['delivery_time']) ? ($part['delivery_time']) : ('-') ?>
                </span>
            </td>
        <? } ?>
        <? if (!in_array('stats', $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) { /*?>
        	<td class="stat_bl">
            	<? $GLOBALS['APPLICATION']->IncludeComponent('linemedia.auto:supplier.reliability.statistic', '.default',
                        array(
                                'SUPPLIER_ID'=>$part['supplier_id'],
                                'WIDTH'=>'400px',
                                'HEIGHT'=>'200px'
                            ),
                        $component);
                    ?>
            </td>
        <? */ } ?>
        <td class="action_bl">
            <a href="#" class="btn btn-default btn-sm"><?= GetMessage('LM_AUTO_SELECT') ?></a>
        </td>
    </tr>
    <? } ?>
</table>
<? } ?>
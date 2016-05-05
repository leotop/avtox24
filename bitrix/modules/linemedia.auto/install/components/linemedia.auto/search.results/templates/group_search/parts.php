<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<table>
<?
global $APPLICATION;

/*
 * Распечатаем группы одну за другой
 */
$n = 0;
foreach ($arResult['PARTS'] as $group_name => $parts) {
    /*$group = explode('_', $group_name);
    $group_id = end($group);
    $n += count($parts);
    if ($arParams['MERGE_GROUPS']) {
        echo '<h2>', GetMessage('LM_AUTO_SEARCH_RESULTS'), '</h2>';
    } else {
        echo '<h2 data-group-id="', strval($group_id), '">', LinemediaAutoPart::getAnalogGroupTitle($group_id), '</h2>';
    }*/
    printPartsTable($parts, $group_id, $arParams);
}

function printPartsTable($parts, $group_id, $params = array()) {

    $request_quantity = intval($_REQUEST['quantity']);
    ?>

    <? foreach ($parts as $part) {
        $lm_part = new LinemediaAutoPart($part['id'], $part);
        $hash = $lm_part->groupSearchIdentity();
        $quantity = abs(intval($part['quantity']));
        if ($quantity < 1) {
            continue;
        }
        $part['customer_quantity'] = $request_quantity > $quantity ? $quantity : $request_quantity;
        ?>
    <tr id="<?=$hash?>" class="section-part">
        <td class="check-bl">
            <input type="checkbox" class="id_inp" name="part_id[<?=$hash?>]" value="<?=$part['id']?>" />
            <input type="hidden" class="q_inp" name="q[<?=$hash?>]" value="<?=$part['article']?>" />
            <input type="hidden" class="supplier_id_inp" name="supplier_id[<?=$hash?>]" value="<?=$part['supplier_id']?>" />
            <input type="hidden" class="brand_title_inp" name="brand_title[<?=$hash?>]" value="<?=$part['brand_title']?>" />
            <input type="hidden" class="delivery_inp" name="delivery[<?=$hash?>]" value="<?=$part['delivery']?>" />
            <input type="hidden" class="ch_id_inp" name="ch_id[<?=$hash?>]" value="<?=$part['chain_id']?>" />
            <? foreach($part['extra'] as $key => $value) { ?>
                <input type="hidden" class="extra_inp" name="extra[<?=$hash?>][<?=$key?>]" value="<?=$value?>" />
            <? } ?>
        </td>
        <td class="article-bl dblckick-sence" nowrap="nowrap">
            <span><?= $part['article'] ?></span>
        </td>
        <td class="brands-bl dblckick-sence">
            <span data-val="<?=$part['brand_title']?>"><?= $part['brand_title'] ?></span>
        </td>
        <td class="title-bl dblckick-sence">
            <span><?=$part['title'] ?></span>
        </td>
        <td class="supplier-bl dblckick-sence">
            <span data-val="<?=$part['supplier_id']?>"><?=$part['supplier']['NAME']?></span>
        </td>
        <td class="price-bl dblckick-sence">
            <span data-val="<?=$part['price_src']?>"><?=$part['price'] ?></span>
        </td>
        <td class="quantity-bl">
            <input type="hidden" name="max_quantity" value="<?=$part['quantity']?>" />
            <span style="z-index: 100;"><input type="number" class="quantity_inp" min="0" max="<?=$part['quantity']?>" step="1" size="4" name="quantity[<?=$hash?>]" value="<?=$part['customer_quantity']?>" title="max. <?=$part['quantity']?>" /></span>
        </td>
        <td class="delivery-bl dblckick-sence">
            <span data-val="<?=$part['delivery']?>"><?=$part['delivery_time'] ?></span>
        </td>
    </tr>
    <? } // foreach ?>
<? } ?>
</table>
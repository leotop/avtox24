<? // построение инпутов дл€ extra
if(!function_exists('makeExtraInput')) {

    /**
     * @param $extra - значение $extra
     * @param $index - ключ соотв. детали в массиве, передаваемом в корзину
     * @param array $depthChain - переменна€ дл€ хранени€ цепочки параметров массива $extra
     */
    function makeExtraInput($extra, $index, $depthChain = array()) {

        $strInput = '';

        $strKeysChain = '';
        if(count($depthChain) > 0) {
            $strKeysChain = '[' . join('][', $depthChain) . ']';
        }

        foreach($extra as $key => $value) {

            if(is_array($value)) { // массив
                $depthChain[] = $key;
                $strInput .= makeExtraInput($value, $index, $depthChain);
            } else if(!empty($value)) { // не массив
                $strInput .= '<input type="hidden" name="extra[' . $index . '][' . $key . ']' . $strKeysChain . '" value="' . $value . '" />';
            }
        } // foreach

        return $strInput;
    }
}
?>

<?// есть деталь которую искали
if($arResult['type_N_to_basket']) { ?>
    <tr>
        <td>
            <input type="hidden" name="q[<?=$arParams['QUERY_KEY']?>]" value="<?=$arResult['type_N_to_basket']['article']?>" />
            <input type="hidden" name="supplier_id[<?=$arParams['QUERY_KEY']?>]" value="<?=$arResult['type_N_to_basket']['supplier_id']?>" />
            <input type="hidden" name="brand_title[<?=$arParams['QUERY_KEY']?>]" value="<?=$arResult['type_N_to_basket']['brand_title']?>" />
            <input type="hidden" name="max_available_quantity[<?=$arParams['QUERY_KEY']?>]" value="<?=$arResult['type_N_to_basket']['quantity']?>" />
            <input type="checkbox" name="part_id[<?=$arParams['QUERY_KEY']?>]" value="<?=$arResult['type_N_to_basket']['part_id']?>" checked="checked" /> <?=$arResult['type_N_to_basket']['title']?>
            <?=makeExtraInput($arResult['type_N_to_basket']['extra'], $arParams['QUERY_KEY'])?>
            <? if(count($arResult['analog_to_basket']) > 0) { ?><br /><a href="#" onClick="$('.a_group_<?=$arParams['QUERY_KEY']?>').toggle(); return false;">аналоги</a><? } ?>
        <td>
            <a href="<?=$arParams['QUERY_URL']?>"><?=$arResult['type_N_to_basket']['display_article']?></a>
        </td>
        <td>
            <?=$arParams['QUERY_COMMENT']?>
        </td>
        <td><!-- ѕроверить количество -->
            <input type="text" class="quant_inp" name="quantity[<?=$arParams['QUERY_KEY']?>]" value="<?=$arParams['QUERY_QUANTITY']?>" size="2" />
        </td>
        <td>
            <?=$arResult['type_N_to_basket']['price']?>
        </td>
    </tr>
    <?
    if(count($arResult['analog_to_basket']) > 0) { ?>
        <? foreach($arResult['analog_to_basket'] as $key => $arAnalog) { ?>
            <tr class="a_group_<?=$arParams['QUERY_KEY']?>" style="display:none;">
                <td>
                    <input type="hidden" name="q[a<?=$arParams['QUERY_KEY'] . '-' . $key?>]" value="<?=$arAnalog['article']?>" />
                    <input type="hidden" name="supplier_id[a<?=$arParams['QUERY_KEY'] . '-' . $key?>]" value="<?=$arAnalog['supplier_id']?>" />
                    <input type="hidden" name="brand_title[a<?=$arParams['QUERY_KEY'] . '-' . $key?>]" value="<?=$arAnalog['brand_title']?>" />
                    <input type="hidden" name="max_available_quantity[a<?=$arParams['QUERY_KEY'] . '-' . $key?>]" value="<?=$arAnalog['quantity']?>" />
                    <input type="checkbox" name="part_id[a<?=$arParams['QUERY_KEY'] . '-' . $key?>]" value="<?=$arAnalog['part_id']?>" /> <i><?=$arAnalog['title']?></i>
                    <?=makeExtraInput($arAnalog['extra'], 'a' . $arParams['QUERY_KEY'] . '-' . $key)?>
                </td>
                <td>
                    <a href="<?=$arParams['QUERY_URL']?>"><?=$arAnalog['display_article']?></a>
                </td>
                <td>
                    <?=$arParams['QUERY_COMMENT']?>
                </td>
                <td><!-- ѕроверить количество -->
                    <input type="text" class="quant_inp" name="quantity[a<?=$arParams['QUERY_KEY'] . '-' . $key?>]" value="<?=$arParams['QUERY_QUANTITY']?>" size="2" />
                </td>
                <td>
                    <?=$arAnalog['price']?>
                </td>
            </tr>
        <? } ?>
    <? } ?>
<? } else { ?>
    <tr onclick="document.location='<?=$arParams['QUERY_URL']?>'">
        <td>
            <?=$arParams['QUERY_TITLE']?>
        </td>
        <td>
            <a href="<?=$arParams['QUERY_URL']?>"><?=$arParams['QUERY']?></a>
        </td>
        <td>
            <?=$arParams['QUERY_COMMENT']?>
        </td>
        <td>
            <?=$arParams['QUERY_QUANTITY']?>
        </td>
        <td></td>
    </tr>
<? } ?>
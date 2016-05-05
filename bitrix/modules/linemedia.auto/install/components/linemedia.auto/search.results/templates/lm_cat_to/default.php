<?
/*
 * Если искомого артикула нет - напишем об этом сообщение
 */
if (count($arResult['PARTS']['analog_type_N']) == 0) {
    echo '<div class="lm-auto-main-art-sought-404">' . GetMessage('LM_AUTO_SEARCH_NO_SOUGHT_ARTICLE') . '</div>';
}
?>

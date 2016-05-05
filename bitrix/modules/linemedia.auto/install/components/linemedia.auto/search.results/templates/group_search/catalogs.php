<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$request_quantity = intval($_REQUEST['quantity']);
$request_article = htmlspecialchars($_REQUEST['article']);
$request_table_id = htmlspecialchars($_REQUEST['table_id']);
?>
<table>
<? foreach ($arResult['CATALOGS'] as $catalog) {

$extra_brands_arr = array_map('htmlspecialchars', (array) $catalog['extra']['wf_b']);
$extra_brands_arr = array_diff($extra_brands_arr, array($catalog['brand_title']));
$extra_brands = join(', ', $extra_brands_arr);
?>
    <tr class="section-brand-link">
        <td colspan="2">&nbsp;</td>
        <td>
            <?=$catalog['brand_title']?>
        </td>
        <td>
            <?=$catalog['title']?>
        </td>
        <td colspan="3">
            <a href="javascript:void(0);" onClick="loadBrand('<?=$request_table_id?>', '<?=$request_article?>', '<?=$catalog["brand_title"]?>', '<?=$request_quantity?>'); $(this).closest('tr').remove();"><?=GetMessage('LM_AUTO_SEARCH_CATALOG_CONTINUE')?></a>
        </td>
    </tr>
<? } ?>
</table>



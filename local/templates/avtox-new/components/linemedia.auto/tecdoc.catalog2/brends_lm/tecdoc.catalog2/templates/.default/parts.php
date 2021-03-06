<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>
<?
$n = 0;
foreach ($arResult['DETAILS'] as $part) {
    if ($arResult['EDIT_MODE'] == false && $part['hidden'] == 'Y') {
        continue;
    }
    ++$n;
}
if ($n==0) {?>
    <div class="alert alert-block">
        <?=GetMessage('LM_AUTO_MAIN_TECDOC_NO_PARTS_AVAIL');?>
    </div>
<?
return;
}
?>

<div class="tecdoc_tags_select"></div>
<div class="tecdoc_tags_select_hidden" style="display: none"><?= $arResult['FILTER']?></div>

<div class="tecdoc-table-block">
<table class="tecdoc parts model_select silver_table_none_top">
	<thead>
        <tr>
            <? if ($arParams['INCLUDE_PARTS_IMAGES'] == 'Y') { ?>
                <th><?= GetMessage('HEAD_IMG') ?></th>
            <? } ?>
            <th><?= GetMessage('HEAD_NAME') ?></th>
            <th><?= GetMessage('HEAD_BRAND') ?></th>
            <th><?= GetMessage('HEAD_ARTICLE') ?></th>
            <th><?= GetMessage('HEAD_INFO') ?></th>
            <th><?= GetMessage('HEAD_BUY') ?></th>
        </tr>
    </thead>
	<tbody>
	    <? foreach ($arResult['DETAILS'] as $part) {

		    if ($arResult['EDIT_MODE'] == false && $part['hidden'] == 'Y') {
		        continue;
            }
            $product_name = ($part['articleName']) ? $part['articleName'] : $part['genericArticleName'];
	    ?>
            <tr class="tecdoc_product_item" data-filter-name="<?=$part['genericArticleName']?>">
                <? if ($arParams['INCLUDE_PARTS_IMAGES'] == 'Y') { ?>
                    <td class="lm-auto-tecdoc-img">
                    	<? if ($part['image']) { ?>
                        	<a href="<?= htmlspecialcharsEx($part['image']) ?>" class="zoom">
                        		<img src="<?= htmlspecialcharsEx($part['image']) ?>?w=50&h=50" alt="<?= htmlspecialcharsEx($part['articleName'] . ' ' . $part['brandName'] . ' ' . $part['articleNo'])?>" title="<?=htmlspecialcharsEx($part['articleName'] . ' ' . $part['brandName'] . ' ' . $part['articleNo']) ?>" />
                        	</a>
                    	<? } ?>
                    </td>
                <? } ?>
                <td class="lm-auto-tecdoc-img-desc">
                    <?
                    /*
			         * ����� ������
			         */
			        if ($arResult['EDIT_MODE']) {

                        if ($part['lm_mod_id']) {
                            // ���������������� �������.
                            echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['source_id'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                            echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '" data-mod-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                            echo '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt="" /></a>';
                        } else {
                            // ������� TecDoc.
                            echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['articleId'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                            echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                        }
			        }
                    ?>

                    <? echo $product_name; ?>
                </td>
                <td class="brand" width="110">
                    <?= $part['brandName'] ?>
                </td>
                <td width="110">
                    <?= $part['articleNo'] ?>
                </td>
                <td width="110">
                	<? if ($part['detail_url']) { ?>
                        <a href="<?= $part['detail_url'] ?>">
                            <?= GetMessage('INFO') ?>
                        </a>
                    <? } ?>
                </td>
                <td width="120">
                	<? if (count($part['PRICES']) > 0) {
	                		$min = $part['min_price'];
	                		$max = $part['max_price'];
	                		if ($min == $max) {
	                		?>
	                			<a href="<?= $part['search_url'] ?>"><?= $part['PRICES'][$min] ?></a>
	                		<? } else { ?>
	                			<a href="<?= $part['search_url'] ?>"><?= $part['PRICES'][$min] ?> - <?= $part['PRICES'][$max] ?></a>
	                		<? } ?>
                	<? } else { ?>
	                    <a href="<?= $part['search_url'] ?>"><?= GetMessage('GET_PRICE') ?></a>
                    <? } ?>
                </td>
            </tr>
        <? } ?>
	</tbody>
</table>
</div>

<? include(dirname(__FILE__) . '/footer.php'); ?>

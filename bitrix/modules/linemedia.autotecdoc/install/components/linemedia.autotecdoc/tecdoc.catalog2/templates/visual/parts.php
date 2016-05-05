<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
    var contemporaryYear = '<?=$arParams['CONTEMPORARY_YEAR']; ?>';
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>

<?CUtil::InitJSCore(array('window'));?>



<?
if (CModule::IncludeModule('linemedia.auto')) {
/*
* Если артикулы видны пользовтелям - то показываем попап проценку
*/
if(!$arResult['HIDE_ARTICLES']){?>
    <script>
    $(document).ready(function(){
        $('.get_price').click(function(event){
            event.preventDefault();
            var Dialog = new BX.CDialog({

            	content: '<div class="lm-ajax-content"><img src="<?=$this->GetFolder() . '/images/ajax-loader.gif'?>"/></div>',
            	resizable: true,
            	draggable: true,
            	height: '400',
            	width: '600',
                closeByEsc : true,
            	buttons: [BX.CDialog.btnClose]
            });
            Dialog.Show();

            var url = '/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/templates/visual/ajax-search.php';
            var article = $(this).data('article');
            var brand_title = $(this).data('brand_title');
            var clicked_id = $(this).parents('.get_price').attr('id');

            var data = 'q=' + article + '&brand_title=' + brand_title;

            $.ajax({
              url: url,
              data: data,
              success: function(data){
                $('.lm-ajax-content').html(data);
                $('.lm-page-loading').hide();
              },
              cache: false
            });

        });
    });


    </script>
<?}?>
<?}?>


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

<div class="tecdoc parts list">
    <? foreach ($arResult['DETAILS'] as $part) {
	    if ($arResult['EDIT_MODE'] == false && $part['hidden'] == 'Y') {
	        continue;
        }

        /*
        * Покажем все товары с картинками в виде маленьких карточек товаров
        */
        if(!$part['image']){
            $arResult['DETAILS_NO_IMAGE'][] = $part;
            continue;
        }

        $product_name = $part['genericArticleName'];
    ?>

    <div class="part_card polaroid tecdoc_product_item get_price" id="<?=$part['articleId']?>" data-brand_title='<?=$part['brandName']?>' data-article='<?=$part['articleNo']?>' data-filter-name="<?=$part['genericArticleName']?>">
        <div class="main_img_place">
            <a href="<?= $part['search_url'] ?>">
                    <img src="<?= htmlspecialcharsEx($part['image']) ?>?w=190&h160" alt="<?= htmlspecialcharsEx($part['articleName'] . ' ' . $part['brandName'] . ' ' . $part['articleNo'])?>" title="<?=htmlspecialcharsEx($part['articleName'] . ' ' . $part['brandName'] . ' ' . $part['articleNo']) ?>" />
            </a>
        </div>




        <a class="lm-tecdoc-product-name" href="<?= $part['search_url'] ?>"> <?=$product_name?></a>
        <div class="lm-tecdoc-part-brand"><?= $part['brandName'] ?></div>




        <?
        /*
         * Режим правки
         */
        if ($arResult['EDIT_MODE']) {

            if ($part['lm_mod_id']) {
                // Пользовательский элемент.
                echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['source_id'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '" data-mod-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                echo '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt="" /></a>';
            } else {
                // Элемент TecDoc.
                echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['articleId'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
            }
        }
        ?>


            <? if (count($part['PRICES']) > 0) {
                ?>
                <a class="lm-price" href="<?= $part['search_url'] ?>">
                <?
        		$min = $part['min_price'];
        		$max = $part['max_price'];
        		if ($min == $max) {
        		?>
        			<?= $part['PRICES'][$min] ?>
        		<? } else { ?>
        			<?= $part['PRICES'][$min] ?> - <?= $part['PRICES'][$max] ?>
        		<? } ?>
                </a>
        	<? } else { ?>
                <!--a href="<?= $part['search_url'] ?>"><?= GetMessage('GET_PRICE') ?></a-->
            <? } ?>



    </div>

    <?}?>
</div>



<?
/*
* Если есть товары без картинок - выведем их табличкой внизу
*/
if($arResult['DETAILS_NO_IMAGE']){?>
    <table class="tecdoc parts silver_table_none_top">

    	<thead>
            <tr>

                <th><?= GetMessage('HEAD_NAME') ?></th>
                <th><?= GetMessage('HEAD_BRAND') ?></th>
                <th><?= GetMessage('HEAD_ARTICLE') ?></th>
                <th><?= GetMessage('HEAD_INFO') ?></th>
                <th><?= GetMessage('HEAD_BUY') ?></th>
            </tr>
        </thead>
    	<tbody>
    	    <? foreach ($arResult['DETAILS_NO_IMAGE'] as $part) {
        		    if ($arResult['EDIT_MODE'] == false && $part['hidden'] == 'Y') {
    		        continue;
                }
                $product_name = ($part['articleName']) ? $part['articleName'] : $part['genericArticleName'];
    	    ?>
                <tr class="tecdoc_product_item" data-filter-name="<?=$part['genericArticleName']?>">

                    <td class="lm-auto-tecdoc-img-desc">
                        <?
                        /*
    			         * Режим правки
    			         */
    			        if ($arResult['EDIT_MODE']) {

                            if ($part['lm_mod_id']) {
                                // Пользовательский элемент.
                                echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['source_id'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                                echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '" data-mod-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                                echo '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt="" /></a>';
                            } else {
                                // Элемент TecDoc.
                                echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['articleId'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                                echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                            }
    			        }
                        ?>

                        <? echo($product_name); ?>

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
                    <td width="120" class="get_price" id="<?=$part['articleId']?>"  data-brand_title='<?=$part['brandName']?>' data-article='<?=$part['articleNo']?>' >
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
<?}?>

<?php

//echo '<pre>';
//print_r($arResult['DETAILS']);

?>


<? include(dirname(__FILE__) . '/footer.php'); ?>

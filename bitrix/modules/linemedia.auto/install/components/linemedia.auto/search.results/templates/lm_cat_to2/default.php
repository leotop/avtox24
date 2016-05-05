
<div class="lm-auto-search-parts-place">
<form action="<?= $arResult['FORM_ACTION'] ?>" method="get" class="lm-auto-main-search-form" name="lm-auto-main-search-form" id="lm-auto-main-search-form-id" onsubmit="$(this).attr('action',
	'<?= $arResult['FORM_ACTION'] ?>'+encodeURIComponent(encodeURIComponent($(this).find('#lm-auto-main-search-query-id').val()))+'/');return true;">
    <!-- --><div class="lm_search_block">
    <div class="lm-auto-partial-search-block">
        <input type="text" value="<?= htmlspecialchars($arParams['QUERY']) ?>" placeholder="<?= GetMessage('LM_AUTO_MAIN_SEARCH_FORM_PLACEHOLDER') ?>" id="lm-auto-main-search-query-id" data-remapping="<?= intval($arParams['REMAPPING']) ?>" />
        <input class="lm-auto-submit" type="submit" value="<?= GetMessage('LM_AUTO_MAIN_SEARCH_FORM_SUBMIT') ?>" />
    </div>
    <? if (!empty($arParams['VIN_URL'])) { ?>
        <a href="<?= $arParams['VIN_URL'] ?>"><?= GetMessage('LM_AUTO_MAIN_SEARCH_REQUEST_VIN') ?></a>
    <? } ?>
    
    <div class="search-limit">
			<?php if ($arParams['RENDER_LIMIT_SEARCH'] == 'Y'): ?>
            <div class="lm-auto-partial-search-block">
                <input type="radio" checked="checked" name="search_limit" id="search_limit_article" value="<?= \LinemediaAutoSearch::ARTICLE_LIMIT ?>" <?php echo $arParams['RENDER_LIMIT_SEARCH'] != 'Y' ? 'disabled' : '' ?> />&nbsp;&nbsp;
                <label for="search_limit_article"><?= GetMessage('CT_BST_SEARCH_ARTICLE') ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="search_limit" id="search_limit_title" value="<?= \LinemediaAutoSearch::TITLE_LIMIT ?>" <?php echo $arParams['RENDER_LIMIT_SEARCH'] != 'Y' ? 'disabled' : '' ?> />&nbsp;&nbsp;
                <label for="search_limit_title"><?= GetMessage('CT_BST_SEARCH_TITLE') ?></label>
            </div>
            <?php endif; ?>
			</div>
            
            <div class="lm-auto-partial-search-block-wrap">
                <div class="lm-auto-partial-search-block">
                <label class="lm-auto-partial-search">
                    <input type="checkbox" name="partial" value="Y" <?=$_REQUEST['partial'] == 'Y' ? 'checked="checked"' : ''?> />
                    <?= GetMessage('LM_AUTO_MAIN_PARTIAL_SEARCH') ?>
                </label>
            </div>
    </div>
    </div><!-- -->
    
</form>
</div>


<script type="text/javascript">


$(document).ready(

		function () {


                  $('input[type="radio"]').change(function () {              	  
                	  if ($(this).attr('id') == 'search_limit_title') {        	  
                		  $('input[name="partial"]').attr('disabled', true);
                	  }

                	  if ($(this).attr('id') == 'search_limit_article') {
                		  $('input[name="partial"]').attr('disabled', false);
                	  }

                      });
			
			    var checked = $.cookie('checkedRadio');
                if (checked != '') {
                    $('input').each(function () {

                    	if ($(this).val() == checked)
                        	$(this).prop('checked', true);
                        });
                } 

			
               $('.navbar-form').submit(function () {

            	   var checked_Radio = $('input:checked').prop('value');
            	   $.cookie("checkedRadio", checked_Radio, '/');

                   });
      			
			}
);

</script>
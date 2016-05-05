<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>
<? define('FIRST_CAR_YEAR', '1986') ?>
<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.cookie.js'); ?>


<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
    var contemporaryYear = <?php echo $arParams['CONTEMPORARY_YEAR']; ?>
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>


<div class="tecdoc_year">
<div class="lm_car_years_filter">
 <span class="lm-filter-button show-years"><?=GetMessage('LM_AUTO_ORIG_ALL_FILTER_YEARS')?></span>
 <span class="lm-active lm-filter-button"><?=GetMessage('LM_AUTO_ORIG_UP_TO_DATE_FILTER_YEARS')?></span>
</div>
</div>

<div class="tecdoc models list">
    <?
        foreach ($arResult['MODELS'] as $model) {

        	if ($arResult['EDIT_MODE'] == false && $model['hidden'] == 'Y') {
    			continue;
            }
            $out .= '<div class="model_card polaroid">';

            /*
             * Составим галерею
             */
            $gallery = '<div class="lm-auto-tecdoc-models-gallery" style="display:none;">';
            foreach ($model['images'] as $image) {
                $gallery .= '<img src="'.$image['url'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.htmlspecialcharsex($arResult['brand_title']).' '.htmlspecialcharsex($model['modelname']).'" />';
            }
            $gallery .= '</div>';
            $out .= $gallery;


            $out .= '<div class="main_img_place">';

            /*
             * Главная картинка
             *
             * http://images.api.auto.linemedia.ru/BRANDS/1139/9856/main.jpg?info
             * http://images.api.auto.linemedia.ru/BRANDS/1139/9856/main.jpg?w=100
             * http://images.api.auto.linemedia.ru/BRANDS/1139/9856/main.jpg?w=300&h=400
             */
            $model['main_image']['url'] = ($model['image']) ? ($model['image']) : ($model['main_image']['url']);
			
            if ($model['main_image']['url'] != '') {
    	        $out .= '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialcharsex($model['modelId']) . '/"><img src="'.$model['main_image']['url'].'?w=190" alt="'.htmlspecialcharsex($arResult['brand_title']).' '.htmlspecialcharsex($model['modelname']).'" title="'.htmlspecialcharsex($arResult['brand_title']).' '.htmlspecialcharsex($model['modelname']).'" class="lm-auto-model-img grayscale" /></a>';
            } else {
            	$filename404 = ($model['commercial']) ? '404model_commercial' : '404model';
    	        $out .= '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialcharsex($model['modelId']) . '/"><img src="'.$this->GetFolder().'/images/'.$filename404.'.png" alt="" class="lm-auto-model-img notfound" /></a>';
            }

            $out .= '</div>';

            /*
             * Режим правки
             */
            if ($arResult['EDIT_MODE']) {
                if ($model['lm_mod_id']) {
                    // Пользовательский элемент.
                    $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $model['source_id'] . ']" value="Y" ' . ($model['hidden'] != 'Y' ? 'checked':'') . ' />';
                    $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $model['modelId'] . '" data-mod-id="' . $model['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                    $out .= '<a href="javascript:;" class="tecdoc-item-delete" data-id="' . $model['id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt=""/></a>';
                } else {
                    // Элемент TecDoc.
                    $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $model['modelId'] . ']" value="Y" ' . ($model['hidden'] != 'Y' ? 'checked':'') . ' />';
                    $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $model['modelId'] . '" data-mod-id="' . $model['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                }
            }

            $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialcharsex($model['modelId']) . '/"> ' . htmlspecialcharsex($model['modelname']) . '</a>';
            $out .= '<br />';

            $out .= '<div class="years">';

            if ($model['yearOfConstrFrom']) {
    	        $out .= '<span class="year_from" data-year="'.substr($model['yearOfConstrFrom'], 0, 4).'">' . substr($model['yearOfConstrFrom'], 0, 4) . ' &mdash; </span>';
    	    } else {
    	    	$out .= '<span class="year_from" data-year="'.FIRST_CAR_YEAR.'">' . GetMessage('YEAR_UNKNOWN') . ' &mdash; </span>';
            }

    	    if ($model['yearOfConstrTo']) {
    	        $out .= '<span class="year_to" data-year="'.substr($model['yearOfConstrTo'], 0, 4).'">' . substr($model['yearOfConstrTo'], 0, 4) . '</span>';
            } else {
    	    	$out .= '<span class="year_to" data-year="'.date('Y').'">' . GetMessage('YEAR_UNKNOWN') . '</span>';
            }

    	    $out .= '</div>';
    	    $out .= '</div>';
    	}

    	foreach ($arResult['MODEL_GROUPS'] as $model_key => $model) {
            $out .= '<div class="model_card polaroid">';
            $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialcharsex($arResult['brand_id']) . '/?model_group=' . htmlspecialcharsex($model_key) . '"> ' . htmlspecialcharsex($model) . '</a>';
            $out .= '</div>';
    	}

        echo $out;
    ?>
</div>



<? include(dirname(__FILE__) . '/footer.php'); ?>

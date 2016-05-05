<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__); ?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
    var contemporaryYear = <?php echo $arParams['CONTEMPORARY_YEAR']; ?>
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>


<table class="tecdoc brands">
	<tbody>
	    <tr>
	        <td>
	        <?
	        $letters_in_column = count($arResult['BRANDS']) / $arParams['COLUMNS_COUNT'];
	        $letters_in_column = round($letters_in_column);

            $shown_letters = 0;
	        $letters = 0;
	        $out = '';
	        foreach ($arResult['BRANDS'] as $letter => $brands) {
		        ++$shown_letters;
		        if (count($brands) < 1) {
		        	continue;
                }

		        $out .= '<h2 class="letter">' . $letter . '</h2>';
		        $out .= '<ul>';
		        foreach ($brands as $brand) {
		        	if ($arResult['EDIT_MODE'] == false && $brand['hidden'] == 'Y') {
		        		continue;
                    }
		        	if ($brand['hidden'] == 'Y') {
		        		$out .= '<li class="lm-auto-hidden">';
			        } else {
			        	$out .= '<li>';
                    }

			        /*
			         * ����� ������
			         */
			        if ($arResult['EDIT_MODE']) {

                        if ($brand['lm_mod_id']) {
                            // ���������������� �������.
                            $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . htmlspecialcharsex($brand['source_id']) . ']" value="Y" ' . ($brand['hidden'] != 'Y' ? 'checked' : '') . ' />';
                            $out .= '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . htmlspecialcharsex($brand['manuId']) . '" data-mod-id="' . $brand['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                            $out .= '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="'.$brand['id'].'"><img src="' . $this->GetFolder() . '/images/delete.png" alt="'.GetMessage('LM_AUTO_DELETE').'" /></a>';
                        } else {
                            // ������� TecDoc.
    				        $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . htmlspecialcharsex($brand['manuId']) . ']" value="Y" ' . ($brand['hidden'] != 'Y' ? 'checked' : '') . ' />';
    				        $out .= '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . htmlspecialcharsex($brand['manuId']) . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                        }
			        }

			        /*
			         * ����� ���� ����?
			         */
			        $logo_filename = '/upload/linemedia.autotecdoc/images/logo/' . strtolower($brand['manuName']) . '.png';
                    $logo_class = htmlspecialcharsex(strtolower($brand['manuName']));
                    $logo_style = '';

		            if (!empty($brand['image']) && file_exists($_SERVER['DOCUMENT_ROOT'].$brand['image'])) {
                        $logo_style = 'style="background-size: cover;background-image:url(' . htmlspecialcharsex($brand['image']) . ')"';
		                $logo_class = '';
		            } else {
		                if (file_exists($_SERVER['DOCUMENT_ROOT'].$logo_filename)) {
                            $logo_style = 'style="background-image: url(' . htmlspecialcharsex($logo_filename) . ');"';
                        }
		            }
                    $out .= '<div class="car-logo ' . $logo_class . ' selflogo" '.$logo_style.'></div>';

				    $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialcharsex($brand['manuId']) . '/"> ' . htmlspecialcharsex($brand['manuName']) . '</a>';
			        $out .= '</li>';
		        }
		        $out .= '</ul>';

		        if ($letters % $letters_in_column == 0 && $letters > 0 && $shown_letters < count($arResult['BRANDS'])) {
		        	$out .= '</td><td>';
                    ++$cols;
	        	}
		        $letters ++;
	        }

	        echo $out;
	        ?>

	        </td>
	    </tr>
	</tbody>
</table>


<? include(dirname(__FILE__) . '/footer.php'); ?>

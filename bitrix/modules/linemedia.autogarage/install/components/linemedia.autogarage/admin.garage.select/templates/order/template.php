<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<? if (!empty($arResult['PROPERTIES'])) { ?>
    <script>
        $(document).ready(function() {
            <? if (!empty($arResult['ITEMS'])) { ?>
                $('input[name="lm-auto-garage-item-use"]').live('click', function() {
                    var mark_id         = $(this).data('mark-id');
                    var model_id        = $(this).data('model-id');
                    var modification_id = $(this).data('modification-id');
                    var auto_text       = $(this).data('mark-title') + ' ' + $(this).data('model-title') + ' ' + $(this).data('modification-title');
                    
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['MARK_ID'] ?>').val(mark_id);
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['MODEL_ID'] ?>').val(model_id);
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['MODIFICATION_ID'] ?>').val(modification_id);
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['AUTO_TEXT'] ?>').val(auto_text);
                });
            <? } else { ?>
                $('#lm-auto-select-brands-id, #lm-auto-select-models-id, #lm-auto-select-modifications-id').live('change', function() {
                    var mark_id         = $('#lm-auto-select-brands-id').val();
                    var model_id        = $('#lm-auto-select-models-id').val();
                    var modification_id = $('#lm-auto-select-modifications-id').val();
                    var auto_text       = $('#lm-auto-select-brands-id option:selected').text() + ' ' + $('#lm-auto-select-models-id option:selected').text() + ' ' + $('#lm-auto-select-modifications-id option:selected').text();
                    
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['MARK_ID'] ?>').val(mark_id);
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['MODEL_ID'] ?>').val(model_id);
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['MODIFICATION_ID'] ?>').val(modification_id);
                    $('#ORDER_PROP_<?= $arResult['PROPERTIES']['AUTO_TEXT'] ?>').val(auto_text);
                });
            <? } ?>
        });
    </script>

    <input type="hidden" name="ORDER_PROP_<?= $arResult['PROPERTIES']['MARK_ID'] ?>" id="ORDER_PROP_<?= $arResult['PROPERTIES']['MARK_ID'] ?>" value="" />
    <input type="hidden" name="ORDER_PROP_<?= $arResult['PROPERTIES']['MODEL_ID'] ?>" id="ORDER_PROP_<?= $arResult['PROPERTIES']['MODEL_ID'] ?>" value="" />
    <input type="hidden" name="ORDER_PROP_<?= $arResult['PROPERTIES']['MODIFICATION_ID'] ?>" id="ORDER_PROP_<?= $arResult['PROPERTIES']['MODIFICATION_ID'] ?>" value="" />
    
    <table width="100%">
        <tr>
            <td align="right" width="40%" class="adm-detail-content-cell-l" valign="top">
                <?= GetMessage('LM_AUTO_GARAGE_AUTO_TEXT') ?>:
            </td>
            <td class="adm-detail-content-cell-r" width="60%">
                <input type="text" name="ORDER_PROP_<?= $arResult['PROPERTIES']['AUTO_TEXT'] ?>" id="ORDER_PROP_<?= $arResult['PROPERTIES']['AUTO_TEXT'] ?>" value="<?=!empty($arResult['CURVAL']['AUTO_TEXT'])?$arResult['CURVAL']['AUTO_TEXT']:''?>" />
            </td>
        </tr>
        <tr>
            <? if (!empty($arResult['ITEMS'])) { ?>
                <td align="right" width="40%" class="adm-detail-content-cell-l" valign="top">
                    <?= GetMessage('LM_AUTO_GARAGE_GARAGE') ?>:
                </td>
                <td class="adm-detail-content-cell-r" width="60%">
                    <div>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <? foreach ($arResult['ITEMS'] as $item) { ?>
                                    <tr>
                                        <td width="40%" class="adm-detail-content-cell-l">
                                            <input
                                                type="radio"
                                                id="lm-auto-garage-item-use-<?= $item['ID'] ?>"
                                                name="lm-auto-garage-item-use"
                                                <?if ($arResult['CURVAL']['MARK_ID'] == $item['PROPERTY_BRAND_ID_VALUE']
                                                        && $arResult['CURVAL']['MODEL_ID'] == $item['PROPERTY_MODEL_ID_VALUE']
                                                        && $arResult['CURVAL']['MODIFICATION_ID'] == $item['PROPERTY_MODIFICATION_ID_VALUE']) echo 'checked="checked"';
                                                ?>
                                                value="<?= $item['ID'] ?>"
                                                
                                                data-mark-id="<?= $item['PROPERTY_BRAND_ID_VALUE'] ?>"
                                                data-model-id="<?= $item['PROPERTY_MODEL_ID_VALUE'] ?>"
                                                data-modification-id="<?= $item['PROPERTY_MODIFICATION_ID_VALUE'] ?>"
                                                data-mark-title="<?= $item['PROPERTY_BRAND_VALUE'] ?>"
                                                data-model-title="<?= $item['PROPERTY_MODEL_VALUE'] ?>"
                                                data-modification-title="<?= $item['PROPERTY_MODIFICATION_VALUE'] ?>"
                                            />
                                        </td>
                                        <td width="60%" class="adm-detail-content-cell-r">
                                            <label for="lm-auto-garage-item-use-<?= $item['ID'] ?>">
                                                <b>
                                                    <?= $item['PROPERTY_BRAND_VALUE'] ?>
                                                    <?= $item['PROPERTY_MODEL_VALUE'] ?>
                                                    <?= $item['PROPERTY_MODIFICATION_VALUE'] ?>
                                                </b>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%" class="adm-detail-content-cell-l"></td>
                                        <td width="60%" class="adm-detail-content-cell-r">
                                            <span class="field-name"><?= GetMessage('USER_GARAGE_VIN') ?>:</span>
                                            <?= $item['PROPERTY_VIN_VALUE'] ?>
                                        </td>
                                    </tr>
                                    <? if (!empty($item['PROPERTY_EXTRA_VALUE']['TEXT'])) { ?>
                                        <tr>
                                            <td width="40%" class="adm-detail-content-cell-l"></td>
                                            <td width="60%" class="adm-detail-content-cell-r">
                                                <span class="field-name"><?= GetMessage('USER_GARAGE_INFO') ?>:</span>
                                                <?= $item['PROPERTY_EXTRA_VALUE']['TEXT'] ?>
                                            </td>
                                        </tr>
                                    <? } ?>
                                <? } ?>
                            </tbody>
                        </table>
                    </div>
                </td>
            <? } else { ?>
                <td align="right" width="40%" class="adm-detail-content-cell-l" valign="top">
                    <?= GetMessage('LM_AUTO_GARAGE_GARAGE') ?>:
                </td>
                <td class="adm-detail-content-cell-r" width="60%">
                    <?  // Выбор машины вручную.
                        $APPLICATION->IncludeComponent(
                            'linemedia.auto:tecdoc.auto.select',
                            'ajax',
                            array()
                        );
                    ?>
                    
                </td>
            <? } ?>
        </tr>
    </table>
<? } ?>

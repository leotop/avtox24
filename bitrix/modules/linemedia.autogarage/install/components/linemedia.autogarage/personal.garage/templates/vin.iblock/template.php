<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>


<script>
    $(document).ready(function(){
        $('#lm-auto-vin-field-vin, #f_brand, #f_model, #f_modification, #lm-auto-vin-field-extra').live('change', function(){
            $('#lm-auto-vin-tr-auto-add').show();
            $('span.garage-item-checkbox').removeClass('checked').addClass('unchecked');
            $('#garage-item-id').val('');
        });

        var i = 0;
        $('span.garage-item-checkbox').each(function () { $(this).attr('data-order', i++); });


        $('span.garage-item-name').click(function(){
            $(this).siblings('span.garage-item-checkbox').trigger('click');
        });
        if ($('#lm-auto-vin-field-vin').length) {
            $('span.garage-item-checkbox').click(function(){
                $('span.garage-item-checkbox').removeClass('checked').addClass('unchecked');
                $(this).removeClass('unchecked').addClass('checked');

                /**
                     by unavailable tecdoc fill in a list of elements (brand, model, modif)
                     as we shall not be able to use an tecdoc to retrieve necessary information, regarding the auto
                **/
                if ($('.unavailable_tecdoc').data('tecdoc') == 1) {

                    var text = $.trim($(this).siblings('.garage-item-name').text());
                    var brand = $.trim(text.substr(0, text.indexOf(' ')));
                    var model = $.trim(text.substr(text.indexOf(' ') + 1, text.length));

                    var modif_tclass = $(this).parent().siblings('.details').attr('class');
                    var pos = $(this).data('order');
                    var modif_text = $.trim($('.' + modif_tclass + ' tr td:nth-child(3)').eq(pos).text());
                    var modif = $.trim(modif_text.substr(modif_text.indexOf(':') + 1, modif_text.length));

                    var auto_features = new Array({'brand' : brand, 'model' : model, 'modification' : modif});
                    $.each(auto_features, function (key, value) {
                        $.each(value, function (key, value) { $('#lm-auto-vin-field-' + key).val(value); } );
                    });

                }

                var fields = JSON.parse($(this).next('input').attr('rel'));

                $('#garage-item-id').val(fields.item_id);
                $('#lm-auto-vin-field-vin').val($(this).next('input').val());
                $('#f_brand').val(fields.brand);
                $('#f_brand_id').val(fields.brand_id);
                $('#f_model').val(fields.model);
                $('#f_model_id').val(fields.model_id);
                $('#f_modification').val(fields.modification);
                $('#f_modification_id').val(fields.modification_id);
                $('#lm-auto-vin-field-extra').val(fields.extra);
                SetBrand(fields.brand_id, fields.brand, false);
                SetModel(fields.model_id, fields.model, false);
                SetModification(fields.modification_id, fields.modification, false);
                $('#lm-auto-vin-tr-auto-add').hide();
                $('#lm-auto-vin-auto-add').attr('checked', false);

            });
        }
    });
</script>
<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error); ?>
    <? } ?>
<? } ?>
<div class="garage-wrap">
<div class="unavailable_tecdoc" style="visibility: hidden" data-tecdoc="<?= (int) $arResult['UNAVAILABLE_TECDOC'] ?>"></div>
<? if (!empty($arResult['ITEMS'])) { ?>
    <!--<p class="add_car_to_garage">
        <a class="btn btn-info" href="<?= $arParams['GARAGE_URL'].'?'.$arParams['ACTION_VAR'].'=edit' ?>?<?= $arParams['ACTION_VAR'] ?>=edit"><?= GetMessage('LM_AUTO_GARAGE_PG_ADD_AUTO') ?></a>
    </p>-->
    <? foreach ($arResult['ITEMS'] AS $item) { ?>
        <?
           $car = array(
                'brand'             =>   $item['PROPERTY_BRAND_VALUE'],
                'brand_id'          =>   $item['PROPERTY_BRAND_ID_VALUE'],
                'model'             =>   $item['PROPERTY_MODEL_VALUE'],
                'model_id'          =>   $item['PROPERTY_MODEL_ID_VALUE'],
                'modification'      =>   $item['PROPERTY_MODIFICATION_VALUE'],
                'modification_id'   =>   $item['PROPERTY_MODIFICATION_ID_VALUE'],
                'extra'             =>   $item['PROPERTY_EXTRA_VALUE']['TEXT'],
                'item_id'           =>   $item['ID'],
            );
            $car = json_encode($car);
        ?>
        <div class="garage-item">
            <span class="garage-item-title" title="<? if ($item['PROPERTY_EXTRA_VALUE']['TEXT']) { ?><?= $item['PROPERTY_EXTRA_VALUE']['TEXT'] ?><? } ?>">
                <span class="garage-item-checkbox <?=(isset($_REQUEST['garage-item-id']) && $_REQUEST['garage-item-id'] === $item['ID'])?'checked':'unchecked';?>"></span>
                <input
                    type="hidden"
                    name="garage-item[<?= $item['ID'] ?>]"
                    value="<?= $item['PROPERTY_VIN_VALUE'] ?>"
                    id="garage-item-<?= $item['ID'] ?>"
                    <?= ($first) ? ('checked') : ('') ?>
                    rel='<?= $car ?>'
                />
                <span class="garage-item-name"><?=$item['NAME']?></span>

            </span>

            <table class="details" cellpadding="2">
                <tr>
                    <? if ($item['PROPERTY_VIN_VALUE']) { ?>
                        <td><strong><?= GetMessage('LM_AUTO_GARAGE_PG_VIN') ?>:</strong> <?= $item['PROPERTY_VIN_VALUE'] ?></td>
                        <td>&nbsp; | &nbsp;</td>
                    <? } ?>
                    <? if ($item['PROPERTY_MODIFICATION_VALUE']) { ?>
                        <td><strong><?= GetMessage('LM_AUTO_GARAGE_PG_MODIFICATION') ?>:</strong> <?= $item['PROPERTY_MODIFICATION_VALUE'] ?></td>
                    <? } ?>
                </tr>
            </table>

            <? if (!empty($arParams['TECDOC_URL'])) { ?>
                    <a target="_blank" class="tecdoc linkToTecdoc" href="<?= $arParams['TECDOC_URL'] ?><?= $item['PROPERTY_BRAND_ID_VALUE'] ?>/<?= $item['PROPERTY_MODEL_ID_VALUE'] ?>/<?= $item['PROPERTY_MODIFICATION_ID_VALUE'] ?>/?from=garage"><?= GetMessage('LM_AUTO_GARAGE_PG_GOTO_CATALOG') ?></a>
           <? } ?>

        </div>
    <? } ?>
    <input type="hidden" name="garage-item-id" id="garage-item-id" value="<?=(isset($_REQUEST['garage-item-id']) && !empty($_REQUEST['garage-item-id']))?$_REQUEST['garage-item-id']:'';?>" />
<? } else { ?>
    <p>
        <?= str_replace('#LINK#', $arParams['GARAGE_URL'].'?'.$arParams['ACTION_VAR'].'=edit', GetMessage('LM_AUTO_GARAGE_PG_ADD_MESSAGE')) ?>
    </p>
<? } ?>
</div>

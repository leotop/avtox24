<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<script>
    $(document).ready(function() {
        if ($('#lm-auto-vin-input').length) {
            $('input.garage-item-radio').change(function() {
                var fields = JSON.parse($(this).attr('rel'));

                $('#lm-auto-vin-input').val($(this).val());
                $('#lm-auto-vin-extra').val('<?= GetMessage('LM_AUTO_GARAGE_BRAND') ?>: ' + fields.brand + '\n<?= GetMessage('LM_AUTO_GARAGE_MODEL') ?>: ' + fields.model + '\n<?= GetMessage('LM_AUTO_GARAGE_MODIFICATION') ?>: ' + fields.modification);
            });
        }
    });
</script>

<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error); ?>
    <? } ?>
<? } ?>

<? if (!empty($arResult['ITEMS'])) { ?>
    <p>
        <a href="<?= $arParams['GARAGE_URL'].'?'.$arParams['ACTION_VAR'].'=edit' ?>?<?= $arParams['ACTION_VAR'] ?>=edit"><?= GetMessage('LM_AUTO_GARAGE_PG_ADD_AUTO') ?></a>
    </p>
    <? foreach ($arResult['ITEMS'] as $item) { ?>
        <?
            $car = array(
                'brand' =>          $item['PROPERTY_BRAND_VALUE'],
                'model' =>          $item['PROPERTY_MODEL_VALUE'],
                'modification' =>   $item['PROPERTY_MODIFICATION_VALUE'],
            );
            $car = json_encode($car);
        ?>
        <div class="garage-item">
            <span class="garage-item-title" title="<? if ($item['PROPERTY_EXTRA_VALUE']['TEXT']) { ?><?= $item['PROPERTY_EXTRA_VALUE']['TEXT'] ?><? } ?>">
                <input
                    type="radio"
                    name="garage-item"
                    class="garage-item-radio"
                    value="<?= $item['PROPERTY_VIN_VALUE'] ?>"
                    id="garage-item-<?= $item['ID'] ?>"
                    <?= ($first) ? ('checked') : ('') ?>
                    rel='<?= $car ?>'
                />
                <label for="garage-item-<?= $item['ID'] ?>"><?=$item['NAME']?></label>
                <? if (!empty($arParams['TECDOC_URL'])) { ?>
                    <a target="_blank" class="tecdoc" href="<?= $arParams['TECDOC_URL'] ?><?= $item['PROPERTY_BRAND_ID_VALUE'] ?>/<?= $item['PROPERTY_MODEL_ID_VALUE'] ?>/<?= $item['PROPERTY_MODIFICATION_ID_VALUE'] ?>/?from=garage"><?= GetMessage('LM_AUTO_GARAGE_PG_GOTO_CATALOG') ?></a>
                <? } ?>
            </span>
            <table class="details" cellpadding="2">
                <tr>
                    <? if ($item['PROPERTY_VIN_VALUE']) { ?>
                        <td><strong><?= GetMessage('LM_AUTO_GARAGE_PG_VIN') ?>:</strong> <?= $item['PROPERTY_VIN_VALUE'] ?></td>
                    <? } ?>
                    <? if ($item['PROPERTY_MODIFICATION_VALUE']) { ?>
                        <td><strong><?= GetMessage('LM_AUTO_GARAGE_PG_MODIFICATION') ?>:</strong> <?= $item['PROPERTY_MODIFICATION_VALUE'] ?></td>
                    <? } ?>
                </tr>
            </table>
        </div>
    <? } ?>
<? } else { ?>
    <p>
        <?= str_replace('#LINK#', $arParams['GARAGE_URL'].'?'.$arParams['ACTION_VAR'].'=edit', GetMessage('LM_AUTO_GARAGE_PG_ADD_MESSAGE')) ?>
    </p>
<? } ?>

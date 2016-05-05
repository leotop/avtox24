<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error); ?>
    <? } ?>
<? } ?>

<? if (count($arResult['ITEMS'])) { ?>
    <? foreach ($arResult['ITEMS'] as $item) { ?>
        <div class="garage-item">
            <span class="garage-item-title a" href="?id=<?=$item['ID']?>"><?=$item['NAME']?></span>
            <? if ($item['PROPERTY_MODIFICATION_ID_VALUE'] && $item['PROPERTY_MODEL_ID_VALUE'] && $item['PROPERTY_BRAND_ID_VALUE']): ?>
            <? endif; ?>
            <table class="details" cellpadding="0" cellspacing="1">
                <tr>
                    <? if ($item['PROPERTY_VIN_VALUE']): ?><td> <strong><?= GetMessage('LM_AUTO_GARAGE_PG_VIN') ?>:</strong> <?=$item['PROPERTY_VIN_VALUE']?></td><? endif; ?>
                    <? if ($item['PROPERTY_MODIFICATION_VALUE']): ?><td> <strong><?= GetMessage('LM_AUTO_GARAGE_PG_MODIFICATION') ?>:</strong> <?=$item['PROPERTY_MODIFICATION_VALUE']?></td><? endif; ?>
                    <? if ($item['PROPERTY_EXTRA_VALUE']['TEXT']): ?><td> <?= $item['PROPERTY_EXTRA_VALUE']['TEXT']?></td><? endif; ?>
                </tr>
            </table>
            <?
                preg_match('/^\S+/i', $item['PROPERTY_MODEL_VALUE'], $aMName);
                $item['MODEL_GROUP'] = ($aMName[0])?$aMName[0]:'';
                $brand = (isset($arParams['SHOW_CAR_BRANDS_IN_LINK']) && $arParams['SHOW_CAR_BRANDS_IN_LINK'] == 'Y') ? strtolower($item['PROPERTY_BRAND_VALUE']) : $item['PROPERTY_BRAND_ID_VALUE'];
                $model = (isset($arParams['SHOW_CAR_BRANDS_IN_LINK']) && $arParams['SHOW_CAR_BRANDS_IN_LINK'] == 'Y') ? strtolower($item['MODEL_GROUP']) : $item['MODEL_GROUP'];
            ?>
            <? if (!empty($arParams['TECDOC_URL'])) { ?>
                <button class="gototecdoc button" onclick="javascript: document.location.href='<?= $arParams['TECDOC_URL'] ?><?= $brand ?>/<?= $item['PROPERTY_MODEL_ID_VALUE'] ?>/<?= $item['PROPERTY_MODIFICATION_ID_VALUE'] ?>/?from=garage'"><?= GetMessage('LM_AUTO_GARAGE_PG_GOTO_CATALOG') ?></button>
            <? } ?>
        </div>
    <? } ?>
    <script type="text/javascript">
        $('span.garage-item-title').click(function(){
            if ($(this.parentNode).hasClass('garage-item')) {
                this.parentNode.className = 'garage-item-max';
            } else {
                this.parentNode.className = 'garage-item';
            }
        });
    </script>
<? } ?>


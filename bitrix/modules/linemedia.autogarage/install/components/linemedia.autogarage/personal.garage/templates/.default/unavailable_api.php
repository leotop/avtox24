<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>


<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error); ?>
    <? } ?>
<? } ?>


<? if ('list' == $arResult['ACTION']) { ?>
    <? if (count($arResult['ITEMS'])) { ?>
      <p><button onclick="document.location.href='?<?= $arParams['ACTION_VAR'] ?>=edit'" class="button"><?= GetMessage('LM_AUTO_GARAGE_PG_ADD_AUTO') ?></button></p>
        <? foreach ($arResult['ITEMS'] as $item) { ?>
            <div class="garage-item">
                <p class="edit">

                     <a href="?<?= $arParams['ACTION_VAR'] ?>=edit&amp;id=<?= $item['ID'] ?>"><?= GetMessage('LM_AUTO_GARAGE_PG_CHANGE') ?></a> |
                    <a href="?<?= $arParams['ACTION_VAR'] ?>=delete&amp;id=<?= $item['ID'] ?>" onclick="return confirm('<?= GetMessage('LM_AUTO_GARAGE_PG_CONFIRM_DELETE') ?>');"><?= GetMessage('LM_AUTO_GARAGE_PG_DELETE') ?></a>
                </p>
                <span class="garage-item-title a" href="?id=<?=$item['ID']?>"><?=$item['NAME']?></span>
                <? if ($item['PROPERTY_MODIFICATION_ID_VALUE'] && $item['PROPERTY_MODEL_ID_VALUE'] && $item['PROPERTY_BRAND_ID_VALUE']): ?>
                <? endif; ?>
                <table class="details" cellpadding="0" cellspacing="1">
                    <tr>
                        <? if ($item['PROPERTY_VIN_VALUE']): ?><td><strong><?= GetMessage('LM_AUTO_GARAGE_PG_VIN') ?>:</strong> <?=$item['PROPERTY_VIN_VALUE']?></td><? endif; ?>
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
                <? if (!empty($arParams['TECDOC_URL']) && $arResult['UNAVAILABLE_TECDOC'] == false) { ?>
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
    <? } else { ?>
        <p>
            <?= str_replace('#LINK#', '?'.$arParams['ACTION_VAR'].'=edit', GetMessage('LM_AUTO_GARAGE_PG_ADD_MESSAGE')) ?>
        </p>
    <? } ?>
<? } ?>

<? if ('edit' == $arResult['ACTION']) { ?>
    <div class="garage-edit-box">
        <h2><? if ($arResult['ITEM']) { ?><?= GetMessage('LM_AUTO_GARAGE_PG_CHANGE_PARAMS') ?><? } else { ?><?= GetMessage('LM_AUTO_GARAGE_PG_NEW_AUTO') ?><? } ?></h2>
        <? if ($arResult['ERROR']) { ?>
            <? ShowError($arResult['ERROR']) ?>
        <? } ?>
        <form action="" method="post">
            <input type="hidden" name="<?= $arParams['ACTION_VAR'] ?>" value="<?= $arResult['ACTION'] ?>" />
            <input type="hidden" name="id" value="<?= $arResult['ID'] ?>" />
            <table class="garage-edit" align="center" />
                <tr>
                    <td valign="middle" class="left_col"><?= GetMessage('LM_AUTO_GARAGE_PG_VIN') ?>:</td>
                    <td><input type="text" name="vin" class="garage-vin" maxlength="17" value="<?= $arResult['ITEM_PROPERTIES']['vin']['VALUE'] ?>" /></td>
                </tr>
                 <tr>
                    <td valign="middle" class="left_col"><?= GetMessage('LM_AUTO_GARAGE_PG_BRAND') ?>:</td>
                    <td><input type="text" name="brand" class="garage-vin" maxlength="17" value="<?= $arResult['ITEM_PROPERTIES']['brand']['VALUE'] ?>" /></td>
                </tr>
                 <tr>
                    <td valign="middle" class="left_col"><?= GetMessage('LM_AUTO_GARAGE_PG_MODEL') ?>:</td>
                    <td><input type="text" name="model" class="garage-vin" maxlength="17" value="<?= $arResult['ITEM_PROPERTIES']['model']['VALUE'] ?>" /></td>
                </tr>
                 <tr>
                    <td valign="middle" class="left_col"><?= GetMessage('LM_AUTO_GARAGE_PG_MODIF') ?>:</td>
                    <td><input type="text" name="modification" class="garage-vin" maxlength="17" value="<?= $arResult['ITEM_PROPERTIES']['modification']['VALUE'] ?>" /></td>
                </tr>
                <tr>
                    <td valign="top"><?= GetMessage('LM_AUTO_GARAGE_PG_ADDITIONAL_INFO') ?>:</td>
                    <td>
                        <textarea name="extra" cols="50" rows="10" class="text"></textarea>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input class="button" type="submit" value="<? if ($arResult['ITEM']) { ?><?= GetMessage('LM_AUTO_GARAGE_PG_SAVE_CHANGES') ?><? } else { ?><?= GetMessage('LM_AUTO_GARAGE_PG_ADD_AUTO') ?><? } ?>" />&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
            </table>
        </form>
        <a href="<?= $arParams['GARAGE_URL'] ?>"><?= GetMessage('LM_AUTO_GARAGE_PG_BACK_TO_LIST') ?></a>
    </div>
    <script type="text/javascript">
        function checkInt(el) {
          var x = 0;
        }
    </script>
<? } ?>

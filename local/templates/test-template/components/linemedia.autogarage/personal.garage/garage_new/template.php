<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<?
    $APPLICATION->SetAdditionalCSS('http://yandex.st/jquery-ui/1.8.23/themes/smoothness/jquery.ui.all.min.css');
    $APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');
?>

<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error); ?>
    <? } ?>
<? } ?>

<? if ('list' == $arResult['ACTION']) { ?>
    <? if (count($arResult['ITEMS'])) { ?>
        <p><button onclick="document.location.href='?<?= $arParams['ACTION_VAR'] ?>=edit'" class="button"><?= GetMessage('LM_AUTO_GARAGE_PG_ADD_AUTO') ?></button></p>
<!--        <p><button onclick="document.location.href='/personal/garage<?//= $arParams['ACTION_VAR'] ?>/vpn/0/'" class="button"><?//= GetMessage('LM_AUTO_GARAGE_PG_ADD_AUTO') ?></button></p>
-->        <? foreach ($arResult['ITEMS'] as $item) { ?>
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
    <? } else { ?>
        <p>
            <?= str_replace('#LINK#', '?'.$arParams['ACTION_VAR'].'=edit', GetMessage('LM_AUTO_GARAGE_PG_ADD_MESSAGE')) ?>
        </p>
    <? } ?>
<? } ?>

<? if ('edit' == $arResult['ACTION']) { ?>   
<??>
    <div class="garage-edit-box">
        <h2><? if ($arResult['ITEM']) { ?><?= GetMessage('LM_AUTO_GARAGE_PG_CHANGE_PARAMS') ?><? } else { ?><?= GetMessage('LM_AUTO_GARAGE_PG_NEW_AUTO') ?><? } ?></h2>
        <? if ($arResult['ERROR']) { ?>
            <? ShowError($arResult['ERROR']) ?>
        <? } ?>
<?
function vin_show_input ($field) {

        if(!is_array($field)){
            return false;
        }
        ?>
        <tr class="lm-auto-vin-tr-prop-<?=$field['CODE'];?>">
            <td class="lm-auto-vin-td-prop-title-<?=$field['CODE'];?> left_col">
                <?if($field['PROPERTY_TYPE'] !== 'L'){?><label for="lm-auto-vin-field-<?=$field['CODE'];?>"><?}?>
                <?=$field['NAME'];?>
                <?if($field['PROPERTY_TYPE'] !== 'L'){?></label><?}?>
                <?if($field['IS_REQUIRED'] === 'Y'){?><span class="starrequired">*</span><?}?>
            </td>
            <td class="lm-auto-vin-td-prop-input-<?=$field['CODE'];?> right_col">
        <?
        switch($field['PROPERTY_TYPE']){
            default:
            case 'S':
                ?>
                <input id="lm-auto-vin-field-<?=$field['CODE'];?>" type="text" maxlength="255" name="<?=$field['CODE'];?>" value="<?= $field['VALUE'] ?>" />
                <?
            break;
            case 'L':
                if($field['LIST_TYPE'] == 'L'){
                ?>
                <select id="lm-auto-vin-field-<?=$field['CODE'];?>" name="<?=$field['CODE'];?>">
                    <?if($field['IS_REQUIRED'] === 'N'){?><option><?=GetMessage('LM_AUTO_VIN_NO_SELECTED');?></option><?}?>
                    <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                        <?foreach($field['ENUM'] AS $enum){?>
                            <option value="<?=$enum['ID'];?>"<?=($field['VALUE'] === $enum['ID'])?' selected="selected"':'';?>><?=$enum['VALUE'];?></option>
                        <?}?>
                    <?}else{?>
                        <option><?=GetMessage('LM_AUTO_VIN_NO_OPTIONS');?></option>
                    <?}?>
                </select>
                <?
                }elseif($field['LIST_TYPE'] == 'C'){
                    if($field['MULTIPLE'] === 'Y'){
                    ?>
                        <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                            <?foreach($field['ENUM'] AS $enum){?>
                                <input type="checkbox" id="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>" name="<?=$field['CODE'];?>[]" value="<?=$enum['ID'];?>"<?=(in_array($enum['ID'], $field['VALUE']))?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>"><?=$enum['VALUE'];?></label>
                            <?}?>
                        <?}else{?>
                            <?=GetMessage('LM_AUTO_VIN_NO_OPTIONS');?>
                        <?}?>
                    <?
                    }else{
                    ?>
                        <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                            <?
                            if(empty($field['VALUE']) && $field['IS_REQUIRED'] === 'Y'){
                                $first_enum = current($field['ENUM']);
                                $field['VALUE'] = $first_enum['ID'];
                                unset($first_enum);
                            }elseif($field['IS_REQUIRED'] === 'N'){
                                ?>
                                <input type="radio" id="lm-auto-vin-field-<?=$field['CODE'];?>-empty" name="<?=$field['CODE'];?>" value=""<?=(empty($field['VALUE']))?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-empty"><?=GetMessage('LM_AUTO_VIN_NO_SELECTED');?></label>
                                <?
                            }
                            foreach($field['ENUM'] AS $enum){?>
                                <input type="radio" id="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>" name="<?=$field['CODE'];?>" value="<?=$enum['ID'];?>"<?=($field['VALUE'] == $enum['ID'])?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>"><?=$enum['VALUE'];?></label>
                            <?
                            }
                            ?>
                        <?}else{?>
                            <?=GetMessage('LM_AUTO_VIN_NO_OPTIONS');?>
                        <?}?>
                    <?
                    }
                }
            break;
        }
        ?>
            </td>
        </tr>
        <?
    }?>
        <form action="" method="post">
            <input type="hidden" name="<?= $arParams['ACTION_VAR'] ?>" value="<?= $arResult['ACTION'] ?>" />
            <input type="hidden" name="id" value="<?= $arResult['ID'] ?>" />
            <table class="garage-edit" align="center" />     
                <tr>
                    <td valign="middle" class="left_col"><?= GetMessage('LM_AUTO_GARAGE_PG_VIN') ?>:</td>
                    <td><input type="text" name="vin" class="garage-vin" maxlength="17" value="<?= htmlspecialcharsEx($arResult['ITEM_PROPERTIES']['vin']['VALUE']) ?>" /></td>
                </tr>   
                <?  // Модификация авто.
                   /* $APPLICATION->IncludeComponent(
                        "linemedia.autogarage:auto.info",
                        "",
                        array(
            	            "BRAND_ID" => $arResult['ITEM_PROPERTIES']['brand_id']['VALUE'],
            	            "MODEL_ID" => $arResult['ITEM_PROPERTIES']['model_id']['VALUE'],
                            "MODIFICATION_ID" => $arResult['ITEM_PROPERTIES']['modification_id']['VALUE'],
                            'MODIFICATIONS_SET'=>$arParams['MODIFICATIONS_SET'] ? : 'default',
                        ),
                        false
                    ); */
                ?>
                <?
                
                $ambiguousFields = array('year');
                
                    $APPLICATION->IncludeComponent(
                        "linemedia.auto:tecdoc.auto.select",
                        "vin.iblock",
                        array(
                          "ACTIONS" => $info_actions,
                          "BRAND_ID" => $arResult['FIELDS']['HIDDEN']['brand_id']['VALUE'],
                          "MODEL_ID" => $arResult['FIELDS']['HIDDEN']['model_id']['VALUE'],
                          "MODIFICATION_ID" => $arResult['FIELDS']['HIDDEN']['modification_id']['VALUE'],
                          "MODIFICATIONS_SET"=>$arParams['MODIFICATIONS_SET']
                        ),
                        $component
                    );
                    echo '<br>';
                   // arshow($arResult['PROPS']);
                 foreach($arResult['PROPS'] AS $field_code => $field){
                     if (in_array($field['CODE'], $ambiguousFields)) {
                            vin_show_input($field);
                        }
                     }
                ?>
                <tr>
                    <td valign="top"><?= GetMessage('LM_AUTO_GARAGE_PG_ADDITIONAL_INFO') ?>:</td>
                    <td>
                        <textarea name="extra" cols="50" rows="10" class="text"><?= htmlspecialcharsEx($arResult['ITEM_PROPERTIES']['extra']['VALUE']['TEXT']) ?></textarea>
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
    <??>
    <script type="text/javascript">
        function checkInt(el) {
          var x = 0;
        }
    </script>
<? } ?>

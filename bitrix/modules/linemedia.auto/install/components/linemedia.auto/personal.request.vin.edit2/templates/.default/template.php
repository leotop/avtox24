<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>
<?
if(!function_exists('vin_show_input')){
    function vin_show_input ($field = false) {
        if(!is_array($field)){
            return false;
        }
        ?>
        <tr>
            <td>
                <?if($field['PROPERTY_TYPE'] !== 'L'){?><label for="lm-auto-vin-field-<?=$field['CODE'];?>"><?}?>
                <?=$field['NAME'];?>
                <?if($field['PROPERTY_TYPE'] !== 'L'){?></label><?}?>
                <?if($field['IS_REQUIRED'] === 'Y'){?><span class="starrequired">*</span><?}?>
            </td>
            <td>
        <?
        switch($field['PROPERTY_TYPE']){
            default:
            case 'S':
                ?>
                <input id="lm-auto-vin-field-<?=$field['CODE'];?>" type="text" maxlength="255" name="VIN_FIELDS[<?=$field['CODE'];?>]" value="<?=$field['VALUE'];?>" />
                <?
            break;
            case 'L':
                if($field['LIST_TYPE'] == 'L'){
                ?>
                <select id="lm-auto-vin-field-<?=$field['CODE'];?>" name="VIN_FIELDS[<?=$field['CODE'];?>]">
                    <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                        <?foreach($field['ENUM'] AS $enum){?>
                            <option value="<?=$enum['ID'];?>"<?=($field['VALUE'] === $enum['ID'])?' selected="selected"':'';?>><?=$enum['VALUE'];?></option>
                        <?}?>
                    <?}else{?>
                        <option>нет вариантов</option>
                    <?}?>
                </select>
                <?
                }elseif($field['LIST_TYPE'] == 'C'){
                    if($field['MULTIPLE'] === 'Y'){
                    ?>
                        <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                            <?foreach($field['ENUM'] AS $enum){?>
                                <input type="checkbox" id="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>" name="VIN_FIELDS[<?=$field['CODE'];?>][]" value="<?=$enum['ID'];?>"<?=(in_array($enum['ID'], $field['VALUE']))?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>"><?=$enum['VALUE'];?></label>
                            <?}?>
                        <?}else{?>
                            нет вариантов
                        <?}?>
                    <?  
                    }else{
                    ?>
                        <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                            <?
                            if(empty($field['VALUE'])){
                                $first_enum = current($field['ENUM']);
                                $field['VALUE'] = $first_enum['ID'];
                                unset($first_enum);
                            }
                            foreach($field['ENUM'] AS $enum){?>
                                <input type="radio" id="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>" name="VIN_FIELDS[<?=$field['CODE'];?>]" value="<?=$enum['ID'];?>"<?=($field['VALUE'] == $enum['ID'])?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>"><?=$enum['VALUE'];?></label>
                            <?
                            }
                            ?>
                        <?}else{?>
                            нет вариантов
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
    }
}
?>
<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error) ?>
    <? } ?>
<? } ?>

<? if (!empty($arResult['MESSAGE'])) {
    ShowMessage(array('MESSAGE' => $arResult['MESSAGE'], 'TYPE' => 'OK'));
    return;
} ?>

<div class="lm-auto-vin">
    <form id="lm-auto-vin-form" method="post">
        <?= bitrix_sessid_post() ?>
        <div>
            <table class="lm-auto-vin-table">
                <thead>
                    <tr>
                        <th colspan="2"><?= GetMessage('SUP_MAIN_HEADER') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="html">
                                <? if (!empty($arResult['HTML'])) { ?>
                                    <? foreach ($arResult['HTML'] as $html) { ?>
                                        <?= $html ?>
                                    <? } ?>
                                <? } ?>
                            </div>
                        </td>
                    </tr>
                    <?
                    if(is_array($arResult['FIELDS']['MAIN']) && count($arResult['FIELDS']['MAIN']) > 0){
                        $col_number = 1;
                        foreach($arResult['FIELDS']['MAIN'] AS $field_code => $field){
                            switch($field_code){
                                default:
                                    vin_show_input($field);
                                break;
                                case 'extra':
                                    ?>
                                    <tr>
                                        <td>
                                            <label for="lm-auto-vin-field-<?=$field_code;?>"><?=$field['NAME'];?></label>
                                            <?if($field['IS_REQUIRED'] === 'Y'){?><span class="starrequired">*</span><?}?>
                                        </td>
                                        <td><textarea id="lm-auto-vin-field-<?=$field_code;?>" name="VIN_FIELDS[<?=$field_code;?>]"><?=$field['VALUE'];?></textarea></td>
                                    </tr>
                                    <?
                                break;
                            }
                        }
                        unset($field, $field_code);
                    ?>
                    <?}?>
                </tbody>
            </table>
        </div>
        
        <div>
            <table class="lm-auto-vin-table">
                <thead>
                    <tr>
                        <th colspan="4" class="lm-auto-vin-extra-header"><a href="javascript: void(0);"><?= GetMessage('SUP_EXTRA_HEADER') ?></a></th>
                    </tr>
                </thead>
                <tbody class="lm-auto-vin-extra-tbody" id="lm-auto-vin-extra-tbody" style="display: none;">
                    <?
                    if(is_array($arResult['FIELDS']['EXTRA']) && count($arResult['FIELDS']['EXTRA']) > 0){
                        foreach($arResult['FIELDS']['EXTRA'] AS $field_code => $field){
                            switch($field_code){
                                default:
                                    vin_show_input($field);
                                break;
                            }
                        }
                        unset($field, $field_code);
                    ?>
                    <?}?>
                </tbody>
            </table>
        </div>

        <div>
            <h2></h2>
            <table class="lm-auto-vin-table" id="lm-auto-vin-table-request">
                <thead>
                    <tr>
                        <th colspan="5"><?= GetMessage('SUP_REQUEST_HEADER') ?></th>
                    </tr>
                    <tr>
                        <th colspan="5"><?= GetMessage('SUP_REQUEST_NOTICE') ?></th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><?= GetMessage('SUP_REQUEST_TITLE') ?></th>
                        <th><?= GetMessage('SUP_REQUEST_ART') ?></th>
                        <th><?= GetMessage('SUP_REQUEST_QUANTITY') ?></th>
                        <th><?= GetMessage('SUP_REQUEST_COMMENT') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?if(is_array($arResult['REQUEST']['VALUE']) && count($arResult['REQUEST']['VALUE']) > 0){
                        $i=1;
                        foreach($arResult['REQUEST']['VALUE'] AS $request_item){
                    ?>
                    <tr>
                        <td class="lm-auto-vin-td-request-action"><?if($i>1){?><a href="javascript: void(0);" class="lm-auto-vin-row-del"></a><?}?></td>
                        <td class="lm-auto-vin-td-request-title"><input type="text" name="request[title][]" value="<?=$request_item['title'];?>" /></td>
                        <td class="lm-auto-vin-td-request-art"><input type="text" name="request[art][]" value="<?=$request_item['art'];?>" /></td>
                        <td class="lm-auto-vin-td-request-quantity"><input type="text" name="request[quantity][]" value="<?=$request_item['quantity'];?>" /></td>
                        <td class="lm-auto-vin-td-request-comment"><input type="text" name="request[comment][]" value="<?=$request_item['comment'];?>" /></td>
                    </tr>
                    <?
                        $i++;
                        }
                    }else{?>
                    <tr>
                        <td class="lm-auto-vin-td-request-action"></td>
                        <td class="lm-auto-vin-td-request-title"><input type="text" name="request[title][]" value="" /></td>
                        <td class="lm-auto-vin-td-request-art"><input type="text" name="request[art][]" value="" /></td>
                        <td class="lm-auto-vin-td-request-quantity"><input type="text" name="request[quantity][]" value="" /></td>
                        <td class="lm-auto-vin-td-request-comment"><input type="text" name="request[comment][]" value="" /></td>
                    </tr>
                    <?}?>
                    <tr>
                        <td><a href="javascript: void(0);" class="lm-auto-vin-row-add"><img src="<?=$templateFolder;?>/images/plus.png" width="16" height="16" /></a></td>
                        <td colspan="4"><a href="javascript: void(0);" class="lm-auto-vin-row-add"><?= GetMessage('SUP_REQUEST_ADD_ROW') ?></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?
        if(is_array($arResult['FIELDS']['HIDDEN']) && count($arResult['FIELDS']['HIDDEN']) > 0){
            foreach($arResult['FIELDS']['HIDDEN'] AS $field_code => $field){?>
                <input id="lm-auto-vin-field-<?=$field_code;?>" type="hidden" name="VIN_FIELDS[<?=$field_code;?>]" value="" /></td>
            <?}
            unset($field, $field_code);
        ?>
        <?}?>

            <input type="submit" name="save" value="<?= GetMessage('SUP_SUBMIT') ?>" />
            <input type="reset" name="reset" value="<?= GetMessage('SUP_RESET') ?>" />
    </form>
</div>
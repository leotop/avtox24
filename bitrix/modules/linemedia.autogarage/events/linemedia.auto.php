<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Main module
 * Module events for module itself
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

CModule::IncludeModule('linemedia.auto');

class LinemediaAutoGarageEventLinemediaAuto
{
    /**
     * Добавление информации в добавление заказа.
     */
    public function OnShowOrderCreateForm_addGarageInfo($userID, $personID, $order_id)
    {
        global $APPLICATION;

        ob_start();
        
        $APPLICATION->IncludeComponent(
            'linemedia.autogarage:admin.garage.select',
            'order',
            array(
                'USER_ID'           => $userID,
                'PERSON_TYPE_ID'    => $personID,
                'ORDER_ID'=> $order_id
            )
        );
        
        $result = ob_get_contents();
        
        ob_end_clean();
        
        return $result;
        
        
        
        
        return;
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        
        $user_id = (int) $user_id;
        
        if ($user_id <= 0) {
            return;
        }
        
        $arSort = array('NAME' => 'ASC');
        $arFilter = array('CREATED_BY' => $user_id, 'IBLOCK_ID' => COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE'));
        $arSelectFields = array(
            'ID',
            'CREATED_BY',
            'NAME',
            'PROPERTY_VIN',
            'PROPERTY_BRAND',
            'PROPERTY_BRAND_ID',
            'PROPERTY_MODEL',
            'PROPERTY_MODEL_ID',
            'PROPERTY_MODIFICATION',
            'PROPERTY_MODIFICATION_ID',
            'PROPERTY_EXTRA',
        );
        
        $rsGarage = CIBlockElement::GetList($arSort, $arFilter, null, null, $arSelectFields);
        while ($arGarage = $rsGarage->Fetch()) {
            $arResult['ITEMS'] []= $arGarage;
        }
        
        if (empty($arResult['ITEMS'])) {
            return;
        }
        
        $selID = (int) $_REQUEST['auto_garage_use_auto'];
        
        ob_start();
        ?>
            <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                <tbody>
                    <tr id="tr_order_user" class="heading">
                        <td colspan="2"><?= GetMessage('USER_GARAGE') ?></td>
                    </tr>
                    <tr>
                        <td width="40%" class="adm-detail-content-cell-l">
                            <input type="radio" id="auto_garage_use_auto_0" name="auto_garage_use_auto" <?= ($selID <= 0) ? ('checked="checked"') : ('') ?> value="0" />
                        </td>
                        <td width="60%" class="adm-detail-content-cell-r">
                            <label for="auto_garage_use_auto_0">
                                <b><?= GetMessage('USER_GARAGE_NO_FROM_GARAGE') ?></b>
                            </label>
                        </td>
                    </tr>
                    <? foreach ($arResult['ITEMS'] as $item) { ?>
                        <tr>
                            <td width="40%" class="adm-detail-content-cell-l">
                                <input type="radio" id="auto_garage_use_auto_<?= $item['ID'] ?>" name="auto_garage_use_auto" <?= ($selID == $item['ID']) ? ('checked="checked"') : ('') ?> value="<?= $item['ID'] ?>" />
                            </td>
                            <td width="60%" class="adm-detail-content-cell-r">
                                <label for="auto_garage_use_auto_<?= $item['ID'] ?>">
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
        <? 
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }



    /**
     * Добавление свойств заказа.
     */
    public function OnAfterOrderAdd_addSaleProps($order_id, &$arFields)
    {
        if (isset($_REQUEST['auto_garage_use_auto']) && intval($_REQUEST['auto_garage_use_auto']) > 0) {
            if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('sale')) {
                return;
            }
            
            $arFilter = array(
                'ID' => intval($_REQUEST['auto_garage_use_auto']),
                'IBLOCK_ID' => COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE')
            );
            $arSelectFields = array(
                'ID',
                'PROPERTY_VIN',
                'PROPERTY_BRAND',
                'PROPERTY_BRAND_ID',
                'PROPERTY_MODEL',
                'PROPERTY_MODEL_ID',
                'PROPERTY_MODIFICATION',
                'PROPERTY_MODIFICATION_ID',
                'PROPERTY_EXTRA',
            );
            $rsGarage = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelectFields);
            if ($arGarage = $rsGarage->Fetch()) {
                // Получение свойств заказа.
                $dbproperties = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID']), false, false, array());
                $arProperties = array();
                while ($property = $dbproperties->Fetch()) {
                    $arProperties[$property['CODE']] = $property;
                }
                
                // Добавление значений свойств в заказ.
                $props = array(
                    array(
                        'ORDER_ID'          => $order_id,
                        'ORDER_PROPS_ID'    => $arProperties['AUTO_TEXT']['ID'],
                        'NAME'              => $arProperties['AUTO_TEXT']['NAME'],
                        'VALUE'             => $arGarage['PROPERTY_BRAND_VALUE'].' '.$arGarage['PROPERTY_MODEL_VALUE'].' '.$arGarage['PROPERTY_MODIFICATION_VALUE'],
                        'CODE'              => 'AUTO_TEXT'
                    ),
                    array(
                        'ORDER_ID'          => $order_id,
                        'ORDER_PROPS_ID'    => $arProperties['MARK_ID']['ID'],
                        'NAME'              => $arProperties['MARK_ID']['NAME'],
                        'VALUE'             => $arGarage['PROPERTY_BRAND_ID_VALUE'],
                        'CODE'              => 'MARK_ID'
                    ),
                    array(
                        'ORDER_ID'          => $order_id,
                        'ORDER_PROPS_ID'    => $arProperties['MODEL_ID']['ID'],
                        'NAME'              => $arProperties['MODEL_ID']['NAME'],
                        'VALUE'             => $arGarage['PROPERTY_MODEL_ID_VALUE'],
                        'CODE'              => 'MODEL_ID'
                    ),
                    array(
                        'ORDER_ID'          => $order_id,
                        'ORDER_PROPS_ID'    => $arProperties['MODIFICATION_ID']['ID'],
                        'NAME'              => $arProperties['MODIFICATION_ID']['NAME'],
                        'VALUE'             => $arGarage['PROPERTY_MODIFICATION_ID_VALUE'],
                        'CODE'              => 'MODIFICATION_ID'
                    ),
                );
                foreach ($props as $prop) {
                    CSaleOrderPropsValue::Add($prop);
                }
            }
        }
    }


    /**
     * Добавление информации в добавление заказа.
     */
    public function OnVinShowHTML_addGarageItems($user_id, $new)
    {
        global $APPLICATION;
    
        if (!CUser::IsAuthorized() || !$new) {
            return;
        }
        
        ob_start();
        
        $APPLICATION->IncludeComponent(
            'linemedia.autogarage:personal.garage',
            'vin',
            array(),
            false
        );
        
        $html = ob_get_contents();
        
        ob_end_clean();
        
        return $html;
    }

    /**
     * Добавление информации в запрос по VIN IBlock
     */
    public function OnVinShowIBlockHTML_addGarageItems($user_id, $new)
    {
        global $APPLICATION;
        
        if (!CUser::IsAuthorized() || !$new) {
            return;
        }
        
        ob_start();
        
        $APPLICATION->IncludeComponent("linemedia.autogarage:personal.garage", "vin.iblock", Array(), false);
        
        $html = ob_get_contents();
        
        ob_end_clean();
        
        return $html;
    }
    
    /**
     * Добавление пункта меню.
     */
    public function OnPublicMenuBuild_addLinkToDemoFolder(&$aMenuLinks)
    {
        if (strlen(COption::GetOptionString('linemedia.autogarage', 'LM_AUTO_GARAGE_DEMO_FOLDER')) > 0) {
            $aMenuLinks []= array(
                GetMessage('LM_AUTO_GARAGE_MENU_LINK'), 
                COption::GetOptionString('linemedia.autogarage', 'LM_AUTO_GARAGE_DEMO_FOLDER'), 
                array(),
                array(),
                "" 
            );
        }
    }   
    
    
    /**
     * Сокрытие свойств в заказе.
     */
    public function OnAdminShowOrderProps_HideProps($ID, &$arProperties)
    {
        unset($arProperties['MARK_ID']);
        unset($arProperties['MODEL_ID']);
        unset($arProperties['MODIFICATION_ID']);
        unset($arProperties['AUTO_TEXT']);
    }
    
    
    /**
     * Добавление автомобиля в гараж.
     */
    public function OnVinAutoAdd_addAutoToGarage($arFields)
    {
        global $USER;
        $r = false;
        $iblock_id = COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');

        if (intval($iblock_id) > 0) {
            $arPropertyData = $arFields;
            if (isset($arPropertyData['extra']) && !empty($arPropertyData['extra'])) {
                $arPropertyData['extra'] = array('VALUE' => array('TEXT' => trim($arPropertyData['extra']), 'TYPE' => 'text'));
            }
            
            $arDataFields = array(
                'IBLOCK_ID' => $iblock_id,
                'CREATED_BY' => $USER->GetID(),
                'NAME' => ($arPropertyData['brand'] && $arPropertyData['model']) ? $arPropertyData['brand'] . ' ' . $arPropertyData['model'] : '-',
                'PROPERTY_VALUES' => $arPropertyData,
            );
            $el = new CIBlockElement();
            
            if ($car_id = $el->Add($arDataFields, false, false, false)) {
                $r = $car_id;
            }
        }
        return $r;
    }
}

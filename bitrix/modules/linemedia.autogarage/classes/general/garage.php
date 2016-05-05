<?php

/**
 * Linemedia Autoportal
 * Garage module
 * Main class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/*
 * Класс для раюоты с гаражом пользователя.
 */
class LinemediaAutoGarage
{
    protected $iblock_id = null;

    protected $id = null;



    public function __construct($id)
    {
        $this->id = (int) $id;

        $this->iblock_id = (int) COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');

        if ($this->iblock_id <= 0) {
            throw new Exception(GetMessage('LM_AUTO_GARAGE_ERROR_IBLOCK_ID'));
        }
    }


    public function getID()
    {
        return $this->id;
    }


    /**
     * Удаление элемента.
     */
    public function delete()
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }

        /*
         * Событие для других модулей
         */
        $events = GetModuleEvents("linemedia.autogarage", "OnBeforeDeleteItem");
        while ($arEvent = $events->Fetch()) {
            if (ExecuteModuleEventEx($arEvent, array($this->id)) === false) {
                throw new Exception(GetMessage('LM_AUTO_GARAGE_ERROR_BEFORE_DELETE'));
            }
        }

        $result = CIBlockElement::Delete($this->id);

        /*
         * Событие для других модулей
         */
        $events = GetModuleEvents("linemedia.autogarage", "OnAfterDeleteItem");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array($this->id));
        }

        return $result;
    }


    /**
     * Проверка на существование машины в гараже.
     * 
     * $param int $user_id
     * @param int $brand_id
     * @param int $model_id
     * @param int $modification_id
     */
    public static function isExists($user_id, $brand_id, $model_id, $modification_id)
    {
    	if (intval($user_id) <= 0) {
    		return false;
    	}
    	
    	if (!CModule::IncludeModule('iblock')) {
    		return false;
    	}
    	
    	$iblock_id = (int) COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');
    	
    	$arFilter = array(
    		'CREATED_BY' => $user_id,
    		'IBLOCK_ID' => $iblock_id,
    		'PROPERTY_BRAND_ID' => $brand_id,
    		'PROPERTY_MODEL_ID' => $model_id,
    		'PROPERTY_MODIFICATION_ID' => $modification_id,
    	);
    	
    	$rsGarage = CIBlockElement::GetList(array(), $arFilter, false, array('nTopCount' => 1), array('ID'));
    	if ($arGarage = $rsGarage->Fetch()) {
    		return true;
    	}
    	return false;
    }
    

    /**
     * Получение списка автомобилей из гаража пользователя.
     *
     * @param int $user_id
     */
    public static function getUserList($user_id, $count = null)
    {
        if (intval($user_id) <= 0) {
            return false;
        }

        if (!CModule::IncludeModule('iblock')) {
            return false;
        }

        $iblock_id = (int) COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');

        $arSort = array('NAME' => 'ASC');
        $arFilter = array('CREATED_BY' => $user_id, 'IBLOCK_ID' => $iblock_id);
        $arSelectFields = array(
            'ID',
            'CREATED_BY',
            'NAME',
            'PROPERTY_VIN',
            'PROPERTY_BRAND',
            'PROPERTY_BRAND_ID',
        	'PROPERTY_YEAR',
        	'PROPERTY_MODEL',
            'PROPERTY_MODEL_ID',
            'PROPERTY_MODIFICATION',
            'PROPERTY_MODIFICATION_ID',
            'PROPERTY_EXTRA',
        );
        $arNavParams = false;
        
        if ($count > 0) {
        	$arNavParams['nTopCount'] = (int) $count;
        }
        
        $rsGarage = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelectFields);

        $items = array();
        while ($arGarage = $rsGarage->Fetch()) {
            $items []= $arGarage;
        }
        return $items;
    }
}
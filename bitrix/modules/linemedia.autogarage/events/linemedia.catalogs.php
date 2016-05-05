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

CModule::IncludeModule('linemedia.catalogs');
CModule::IncludeModule('iblock');


class LinemediaAutoGarageEventLinemediaCatalogs
{
	/**
	 * ƒобавление автомобил€ в гараж.
	 */
	public function OnApplianceSave_AddAutoToGarage($config, $elements, $code)
	{
		global $USER;
		
		$result = false;
		$iblock_id = COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');
		
		if (intval($iblock_id) > 0 && $USER->GetID()) {
			$arPropertyData = array();
			
			// ¬ гараж добавл€ем автомобиль с установленными маркой, моделью и модификацией.
			if (count($elements) < 3) {
				return;
			}
			
			foreach ($elements as $key => $value) {
				$arPropertyData[$value['type']] = $value['title'];
				$arPropertyData[$value['type'].'_id'] = $value['permanent_id'];
			}
			
			// “акой автомобиль уже есть в гараже.
			if (LinemediaAutoGarage::isExists($USER->GetID(), $elements['brand_id']['permanent_id'], $elements['model_id']['permanent_id'], $elements['modification_id']['permanent_id'])) {
				return;
			}
			
			$name = trim($arPropertyData['brand'].' '.$arPropertyData['model'].' '.$arPropertyData['modification']);
			
			$arDataFields = array(
				'IBLOCK_ID' 		=> $iblock_id,
				'CREATED_BY' 		=> $USER->GetID(),
				'NAME' 				=> $name,
				'PROPERTY_VALUES' 	=> $arPropertyData,
			);
			
			$element = new CIBlockElement();
			
			if ($cid = $element->Add($arDataFields, false, false, false)) {
				$result = $cid;
			}
		}
		return $result;
	}
	
	
	/**
	 * ƒобавление автомобилей из гаража в выбор применимости.
	 */
	public function OnPresetApplianceList_UpdateAutoList($config, $code)
	{
		global $USER;
	
		$result = array();
	
		if (CModule::IncludeModule('linemedia.autogarage') && $USER->IsAuthorized()) {
			$cars = (array) LinemediaAutoGarage::getUserList($USER->getID());
	
			foreach ($cars as $car) {
				$result []= array(
						'title'		=> $car['PROPERTY_BRAND_VALUE'].' '.$car['PROPERTY_MODEL_VALUE'].' '.$car['PROPERTY_MODIFICATION_VALUE'],
						'brand_id'	=> $car['PROPERTY_BRAND_ID_VALUE'],
						'year'		=> $car['PROPERTY_YEAR_VALUE'],
						'model_id'	=> $car['PROPERTY_MODEL_ID_VALUE'],
						'modif_id'	=> $car['PROPERTY_MODIFICATION_ID_VALUE'],
						'brand_t'	=> $car['PROPERTY_BRAND_VALUE'],
						'model_t'	=> $car['PROPERTY_MODEL_VALUE'],
						'modif_t'	=> $car['PROPERTY_MODIFICATION_VALUE'],
				);
			}
		}
		return $result;
	}
}

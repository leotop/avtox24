<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/**
 * Linemedia Autoportal
 * Main module
 * Module events for iblocks
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);


class LinemediaAutoEventIBlock
{
    
    /**
     * Установка ID поставщика.
     */
    public function OnStartIBlockElementAdd_setSupplierId(&$arFields)
    {
        global $APPLICATION;     
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
	        
            // Это обновление элемента.
            if (!empty($arFields['CREATED_BY'])) {
                return;
            }
            
            // Получение свойства ID поставщика.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
			
            $supplier_id = $arFields['PROPERTY_VALUES'][$property['ID']]['VALUE'] ? $arFields['PROPERTY_VALUES'][$property['ID']] : $arFields['PROPERTY_VALUES'][$property['ID']]['n0'];
			//Если бы мы добавляли элемент прямо в инфоблоке, то свойство было бы в $supplier_id['n0]['VALUE'],
			//но при добавлении в инфоблоке оно обязательно, поэтому не мб пустым
            $supplier_id = $supplier_id['VALUE'] ? (string) $supplier_id['VALUE'] : null;
            if (!$supplier_id) {
                $arFields['PROPERTY_VALUES'][$property['ID']] = LinemediaAutoSupplier::generateSupplierId();
            }
        }
    }
    
    /**
     * check whether limit of available suppliers is exceeded
     * @param array $arFields
     * @return boolean
     */
    public function OnBeforeIBlockElementAdd_isLimitSuppliersExceeded(&$arFields) {

        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

	    if($arFields['IBLOCK_ID'] != $supplier_iblock_id) {
		    return true;
	    }

	    // max available suppliers for current edition
        $max_suppliers_quantity = LinemediaAutoModule::getFunctionLimit('max_suppliers_quantity', 'linemedia.auto');
        $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id));
        $active_suppliers_count = $res->SelectedRowsCount();
       
        if ($max_suppliers_quantity <= $active_suppliers_count && $max_suppliers_quantity > 0) {
            $APPLICATION->ThrowException(GetMessage('LM_AUTO_ERROR_EXCEEDED_SUPPLIER_ID') . $max_suppliers_quantity);
            return false;
        }

        return true;
    }
    
    /**
     * Проверка ID поставщика на уникальность.
     */
    public function OnBeforeIBlockElementAdd_checkSupplierId($arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            // Получение свойства ID поставщика.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();

			//Если делать импорт из файла, то тогда св-во лежит в $arFields['PROPERTY_VALUES'][$property['ID']],
			//если добавлять как элемент инфоблока, то в $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'], хотя
			//оно не множественное, странно
            $supplier_id =  isset($arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE']) ?
				$arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'] : $arFields['PROPERTY_VALUES'][$property['ID']];


			/*
			 * Дата: 30.09.13 14:32
			 * Кто: Назарков Илья
			 * Задача: 5538
			 * Пояснения: проверяем id на кириллицу
			 */
			if ( mb_internal_encoding() == 'UTF-8' && !empty($supplier_id)) {
				$match = (bool) preg_match('/\p{Cyrillic}/u', $supplier_id);
				if($match) {
					$APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_CYRILLIC_SUPPLIER_ID'));
					return false;
				}
			}
            // Проверка ID поставщика на уникальность.
            if (LinemediaAutoSupplier::existsSupplierId($supplier_id)) {
                $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                return false;
            }

            if(strpos($supplier_id, '_') !== false) {
                $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_SYMBOL'));
                return false;
            }
        }
    }
    
    
    /**
     * Проверка ID поставщика на уникальность.
     */
    public function OnBeforeIBlockElementUpdate_checkSupplierId($arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            
            // Существующий поставщик.
            $supplier_property = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $supplier_iblock_id, 'ID' => $arFields['ID']),
                false,
                false,
                array('ID', 'PROPERTY_supplier_id')
            )->Fetch();
            
            // Получение свойства ID поставщика.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            // Получение ID поставщика.
            $arProps     = $arFields['PROPERTY_VALUES'][$property['ID']];
            $supplier_id = reset($arProps);
            $supplier_id = $supplier_id['VALUE'];
            
            // Очищаем статистику по поставщику.
            BXClearCache(false, '/supplier_stat/'.$supplier_id.'/');


			/*
			 * Дата: 30.09.13 14:32
			 * Кто: Назарков Илья
			 * Задача: 5538
			 * Пояснения: проверяем id на кириллицу
			 */
			if ( mb_internal_encoding() == 'UTF-8' && !empty($supplier_id)) {
				$match = (bool) preg_match('/\p{Cyrillic}/u', $supplier_id);
				if($match) {
					$APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_CYRILLIC_SUPPLIER_ID'));
					return false;
				}
			}
            
            if ($supplier_property['PROPERTY_SUPPLIER_ID_VALUE'] != $supplier_id) {
                // Проверка ID поставщика на существование.
                if (LinemediaAutoSupplier::existsSupplierId($supplier_id)) {
                    $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                    return false;
                }
            } else {
                // Проверка ID поставщика на уникальность.
                if (!LinemediaAutoSupplier::isUniqueSupplierId($supplier_id)) {
                    $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                    return false;
                }
            }

            if(strpos($supplier_id, '_') !== false) {
                $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_SYMBOL'));
                return false;
            }
        }
    }
    
    
    
    /*
     * События на чистку кеша при изменении инфоблоков
     */
    public function OnAfterIBlockElementAdd_clearCache(&$arFields)
    {
	    LinemediaAutoFileHelper::clearCache($arFields['IBLOCK_ID']);
    }
    
    public function OnAfterIBlockElementUpdate_clearCache(&$arFields)
    {
	    LinemediaAutoFileHelper::clearCache($arFields['IBLOCK_ID']);
    }
    
    public function OnIBlockElementDelete_clearCache($ID)
    {
    	$res = CIBlockElement::GetByID($ID);
    	if ($arFields = $res->Fetch()) {
		    LinemediaAutoFileHelper::clearCache($arFields['IBLOCK_ID']);
        }
    }
	
	
	// TODO: в модуль филиалов
	public function OnBeforeIBlockElementAdd_SetBranchForDiscounts(&$arFields)
	{
		
		if(!CModule::IncludeModule('linemedia.auto')) return; 
		if(!CModule::IncludeModule("linemedia.autobranches")) return; 
			
		global $USER;

		$iblockId = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_DISCOUNT");	
		if($arFields["IBLOCK_ID"] == $iblockId )
		{
			$u_branch = LinemediaAutoGroup::getUserDealerId();
			$u_branch = $u_branch["UF_DEALER_ID"]["0"];

			$arGroups = $USER->GetUserGroupArray();
			$cur =  $arGroups;
			
			
			$director_group = (int) COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR'); 
			
			$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblockId, "CODE" => "dealers"));
			while ($prop_fields = $properties->GetNext())
			{
			  $prop_id = $prop_fields["ID"];
			}
						
			if(in_array($director_group, $cur) && !$USER->IsAdmin())
			{
				$arFields["PROPERTY_VALUES"][$prop_id]["n0"]["VALUE"] = $u_branch;
			}
		}
	}
    
    
}


    

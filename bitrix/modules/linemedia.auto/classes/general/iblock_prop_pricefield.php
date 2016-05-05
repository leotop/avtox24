<?php

/**
 * Linemedia Autoportal
 * Main module
 * Iblock property for user groups
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);


CModule::IncludeModule('iblock');


/*
 * Привязка к группам пользователей
 */
class LinemediaAutoIblockPropertyPriceField extends CIBlockPropertyElementList
{
    
    function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'user_field',
            'DESCRIPTION' => GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERFIELD_TITLE'),
            
            'CheckFields' => array('LinemediaAutoIblockPropertyPriceField', 'CheckFields'),
            'GetLength' => array('LinemediaAutoIblockPropertyPriceField', 'GetLength'),
            'GetPropertyFieldHtml' => array('LinemediaAutoIblockPropertyPriceField', 'GetEditField'),
            'GetAdminListViewHTML' => array('LinemediaAutoIblockPropertyPriceField', 'GetFieldView'),
            'GetPublicViewHTML' => array('LinemediaAutoIblockPropertyPriceField', 'GetFieldView'),
            'GetPublicEditHTML' => array('LinemediaAutoIblockPropertyPriceField', 'GetEditField'),
            'ConvertToDB' => array('LinemediaAutoIblockPropertyPriceField', 'ConvertToDB'),
            'ConvertFromDB' => array('LinemediaAutoIblockPropertyPriceField', 'ConvertFromDB'),
            
            
        );
    }
    
    function ConvertToDB($arProperty, $value) {
    	
    	$value['VALUE']['VALUE'] = trim($value['VALUE']['VALUE']);
    	
    	if($value['VALUE']['VALUE'] != '' && $value['VALUE']['CODE'] != '') {
		    $value['VALUE'] = serialize($value['VALUE']);
		    return $value;
	    }
	    
    }
    function ConvertFromDB($arProperty, $value) {//_d($value);
	    $value['VALUE'] = unserialize($value['VALUE']);
	    return $value;
    }
    
    function CheckFields($arProperty, $value)
    {
        return array();
    }
    
    
    function GetLength($arProperty, $value)
    {
        return strlen($value['VALUE']);
    }
    
    
    function GetEditField($arProperty, $value, $htmlElement)
    {
        if(!CModule::IncludeModule('linemedia.auto')) return;
        
        //$value['VALUE'] = @unserialize($value['VALUE']);
        
        /*
		 * Обработчик пользовательских свойств.
		 */
		$lmfields = new LinemediaAutoCustomFields();
        $arCustomFields = $lmfields->getFields();
        
        $str  = '<select name="' . $htmlElement['VALUE'] . '[CODE]">';
        $str .= '<option value="">' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERFIELD_ALL') . '</option>';
        foreach($arCustomFields AS $custom_field) {
            $selected = ($value['VALUE']['CODE'] == $custom_field['code']) ? ' selected' : '';
            $str .= '<option value="' . $custom_field['code'] . '"' . $selected . '>' . $custom_field['name'] . '</option>';
        } 
        $str .= '</select>';
        
        $str .= ' = <input type="text" name="' . $htmlElement['VALUE'] . '[VALUE]" value="'.$value['VALUE']['VALUE'].'"/>';
        
        
        return $str;
    }
    
    
    function GetFieldView($arProperty, $value, $htmlElement)
    {
        return $value['VALUE'];
    }
}

<?php

/**
 * Linemedia Autoportal
 * Tester module
 * 
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);
 
/*
* класс отвечает за проверку необходимых компонентв системы
*/
class LinemediaAutoTest
{
    private $reqirements = array(
        'bitrix_version' => '11.0.0',
        'modules' => array(
            array(
                'module' => 'iblock',
                'min' => '10.0.0',
            ),
            array(
                'module' => 'currency',
                'min' => '10.0.0',
            ),
            array(
                'module' => 'sale',
                'min' => '10.0.0',
            ),
        ),
    );
    
    
    public function checkAll()
    {
        return 
            $this->checkBitrixVersion()
            &&
            $this->checkModules()
            ;
    }
    
    public function checkBitrixVersion()
    {
        return version_compare($this->reqirements['bitrix_version'], SM_VERSION) < 1;
    }
    
    public function checkModules($bool = true)
    {
        $result = array();
        foreach($this->reqirements['modules'] AS $module)
        {
            if (!CModule::IncludeModule($module['module']))
            {
                if($bool) return false;
                
                $result[$module['module']] = false;
            }
            
            $result[$module['module']] = true;
        }
        
        if($bool) return true;
        return $result;
    }
    
    
    
    
    public function checkSaleDelivery()
    {
        CModule::IncludeModule('sale');
        $res = CSaleDelivery::GetList();
        return $delivery = $res->Fetch();
    }
    
    
    
    public function getReqirements()
    {
        return $this->reqirements;
    }
    
}

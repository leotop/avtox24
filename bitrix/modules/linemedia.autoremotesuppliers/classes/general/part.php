<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Part object
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

/**
 * Интерфейс удалённого поставщика
 * Class LinemediaAutoRemoteSuppliersPart
 */
class LinemediaAutoRemoteSuppliersPart extends LinemediaAutoPartAll
{
    protected $remore_supplier;
    
    
    public function __construct($part_id = false, $extra = array())
    {
        
    }
    
    
    protected function load()
    {
        return;
    }

	protected function save()
	{
		return;
	}
    
    
    protected function setQuantity($quantity)
    {
        return;
    }
}

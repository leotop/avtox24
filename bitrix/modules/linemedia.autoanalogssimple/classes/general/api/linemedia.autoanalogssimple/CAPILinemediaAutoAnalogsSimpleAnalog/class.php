<?php
/**
 * Linemedia API
 * API module
 * Simple analogs class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/**
* Драйвер для работы с модулем linemedia.api (API для сайта)
*/
class CAPILinemediaAutoAnalogsSimpleAnalog extends CAPIFrame
{
	
	/**
	* Конструктор класса
	*/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	* Добавление кроссов в базу
	*
	* @param $elements Массив с кроссами (также массивы)
	*/
    public function LinemediaAutoAnalogsSimpleAnalog_Add($elements = array())
    {
    	/**
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	
    	
    	foreach($elements AS $el)
    	{
    		
    		/**
			 * Создаём объект аналога.
			 */
			try {
			    $analog = new LinemediaAutoAnalogsSimpleAnalog();
			    $id = $analog->add($el);
			} catch (Exception $e) {
			    $this->error($e->GetMessage());
			}
		}		        
		
		return true;
    }
    
    
    /**
	* Удаление запчастей по фильтру
	*
	* @param $filter Массив с условиями фильтрации
	*/
    public function LinemediaAutoAnalogsSimpleAnalog_Delete($filter = array())
    {
    	/**
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	$this->decodeArray($filter);
    	
    	
    	/**
		 * Создаём объект аналога.
		 */
		try {
		    $analog = new LinemediaAutoAnalogsSimpleAnalog();
		    $id = $analog->clear($filter);
		} catch (Exception $e) {
		    $this->error($e->GetMessage());
		}
    	
    			
		return true;
    }
        
}

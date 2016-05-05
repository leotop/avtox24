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
* ������� ��� ������ � ������� linemedia.api (API ��� �����)
*/
class CAPILinemediaAutoAnalogsSimpleAnalog extends CAPIFrame
{
	
	/**
	* ����������� ������
	*/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	* ���������� ������� � ����
	*
	* @param $elements ������ � �������� (����� �������)
	*/
    public function LinemediaAutoAnalogsSimpleAnalog_Add($elements = array())
    {
    	/**
    	* �������� ���� ������� � �������
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	
    	
    	foreach($elements AS $el)
    	{
    		
    		/**
			 * ������ ������ �������.
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
	* �������� ��������� �� �������
	*
	* @param $filter ������ � ��������� ����������
	*/
    public function LinemediaAutoAnalogsSimpleAnalog_Delete($filter = array())
    {
    	/**
    	* �������� ���� ������� � �������
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	$this->decodeArray($filter);
    	
    	
    	/**
		 * ������ ������ �������.
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

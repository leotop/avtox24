<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Protocol
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 

/**
 * ����������� ����� ��������� ����������
 * Class LinemediaAutoProtocol
 */
class LinemediaAutoProtocol
{
	public function download($test = false){}
    public static function getConfigVars(){}
    public static function available(){}
    public static function getRequirements(){}
    
    /**
    * �������� ��� ��������� �����
    * @return string
    */
    public function getOriginalFileName() 
    {
	    return basename($this->original_filename);
    }
    
    
    /**
     * ���.
     * @param $str
     */
    public static function log($str)
    {
	    echo date('G:i:s') . ' - ' . $str . "\n";
    }
    
}


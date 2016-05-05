<?php
/**
 * Linemedia Autoportal
 * Main module
 * Debug all calculations
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__); 

class LinemediaAutoFileHelper
{
    
    
    /**
     * ������ ��������� � ������ ���������� (����������).
     * 
     * @param string $path
     * @param string $search
     * @param string $replace
     * @param bool $recursively [optional]
     */
    public static function fileStrReplace($path, $search, $replace, $recursively = true)
    {
        $dir = opendir($path);
        while ($file = readdir($dir)) {
            $fullpath = rtrim($path, '/').'/'.$file;
            if (is_file($fullpath) && is_writable($fullpath)) {
                $content = file_get_contents($fullpath);
                $content = str_replace($search, $replace, $content);
                file_put_contents($fullpath, $content);
            }
            if ($recursively && is_dir($fullpath) && !in_array($file, array('.', '..'))) {
                self::fileStrReplace($fullpath, $search, $replace, $recursively);
            }
        }
        closedir($dir);
    }
    
    /**
     * ����������� ��������������� ������
     * 
     * @param string $path
     * @param string $from
     * @param string $to
     * @param bool $recursively [optional]
     */
    public static function convertEncoding($path, $from, $to, $recursively = true)
    {
        $dir = opendir($path);
        while ($file = readdir($dir)) {
            $fullpath = rtrim($path, '/').'/'.$file;
            if (is_file($fullpath) && is_writable($fullpath)) {
                $content = file_get_contents($fullpath);
                $content = mb_convert_encoding($content, $from, $to);
                file_put_contents($fullpath, $content);
            }
            if ($recursively && is_dir($fullpath) && !in_array($file, array('.', '..'))) {
                self::convertEncoding($fullpath, $from, $to, $recursively);
            }
        }
        closedir($dir);
    }
    
    
    /*
    * ������� ���������� ����
    */
    public static function clearCache($iblock_id)
    {
    	$suppliers = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
    	$discounts = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_DISCOUNT");
    	
    	switch($iblock_id)
    	{
	    	case $suppliers:
	    		BXClearCache(true, "/lm_auto/suppliers");
	    	break;
	    	case $discounts:
	    		BXClearCache(true, "/lm_auto/custom_discount");
	    	break;
    	}
	    
    }
    
    
    
    
    /**
    * Get available space on current HDD
    *
    * @return array
    */
    public static function getAvailableHDDSpace()
    {
	    return disk_free_space($_SERVER['DOCUMENT_ROOT'] . '/upload/');
    }
    
    
    
    /**
    * Print human-readable filesize
    *
    * @param int $bytes Filesize in bytes
    * @return string
    */
    public static function getPrintableFilesize($bytes)
    {
	    if ($bytes > 0)
	    {
	        $unit = intval(log($bytes, 1024));
	        $units = array('B', 'KB', 'MB', 'GB', 'TB');
	
	        if (array_key_exists($unit, $units) === true)
	        {
	            return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
	        }
	    }
	
	    return $bytes;
    }
    
}

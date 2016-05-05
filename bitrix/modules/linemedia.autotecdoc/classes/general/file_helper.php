<?php
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * LinemediaAutoTecDocFileHelper
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__); 

/**
 * file helper
 */
class LinemediaAutoTecDocFileHelper
{
    
    
    /**
     * replacing substring in file`s directory (recursively)
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
     * recursive file`s converting 
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
}

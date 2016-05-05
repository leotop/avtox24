<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Requests class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

/*
 * Requests
 */
class LinemediaAutoSuppliersRequestImporter
{
    const DIR_UPLOAD    = '/upload/linemedia.autosuppliers/upload';

    protected $request  = null;
    protected $filename = 'file';
    
    
    public function __construct()
    {
        CModule::IncludeModule('linemedia.auto');
    }
    
    
    
    /**
     * ������ �����.
     * 
     * @param string $filename
     * @return mixed
     */
    public function import($filename)
    {
        $filename = (string) $filename;
        
        // ���� �� ��������.
        if (!is_readable($filename)) {
            throw new Exception("Can't read file: $filename");
        }
        
        // �������� ��� �����.
        $fileinfo = pathinfo($filename);
        if ($fileinfo['extension'] != 'csv') {
            $filename = $this->convert2CSV($filename, $fileinfo['extension']);
        }
        
        $handle = fopen($filename, "r");
        
        // ���������� ������������� ����.
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            return;
        }
        
        // ��������� ��������.
        fgetcsv($handle, 1000);
        fgetcsv($handle, 1000);
        
        // ������� ������.
        $result = array();
        while (($data = fgetcsv($handle, 1000)) !== FALSE) {
            $brand      = (string) $data[0];
            $article    = (string) $data[1];
            
            $result[$brand][$article] = array(
                'brand'      => $brand,
                'article'    => $article,
                'title'      => (string) $data[2],
                'price'      => (float) $data[3],
                'quantityR'  => (int) $data[4],
                'quantityA'  => (int) $data[5],
            );
        }
        
        fclose($handle);
        
        return $result;
    }
    
    
    /**
     * ����������� �����.
     */
    public function convert2CSV($filename, $from = 'xls')
    {
        switch ($from) {
            case 'xls':
            case 'xlsx':
                $cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename) . '.csv';
                $cmd_result = shell_exec($cmd);
                
                unlink($filename);
                return $filename . '.csv';
                break;
            default:
                return $filename;
        }
    }
}
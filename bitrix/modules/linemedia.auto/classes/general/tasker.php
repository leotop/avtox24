<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 


/*
 * Интерфейс качальщика
 */
class LinemediaAutoTasker
{
    protected $protocol = null; 
    protected $task     = array();
    
    
    public function __construct($protocol, $protocol_data = array())
    {
        $available_protocols = self::getProtocols();
        if (!isset($available_protocols[$protocol])) {
            throw new Exception('No protocol ' . $protocol);
            return;
        }
        
        $classname = 'LinemediaAuto' . ucfirst($protocol) . 'Protocol';
        $this->protocol = new $classname($protocol_data);
    }
    
    
    /**
     * Установка данных задачи.
     */
    public function setTaskData($task = array())
    {
        $this->task = (array) $task;
    }
    
    
    /**
     * Скачивание.
     */
    public function download()
    {
        try {
            $tmp_file_name = $this->protocol->download();
        } catch (Exception $e) {
            throw new Exception('Error downloading file: ' . $e->GetMessage());
        }
        
        $new_folder = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/';
        $protocol = $this->task['connection']['protocol'];
        $original_filename = basename($this->task['connection'][$protocol]['FILENAME']);
        $new_filename = $this->task['id'] . '_' . $this->task['supplier_id'] . '_' . $original_filename;
        rename($tmp_file_name, $new_folder . $new_filename);
        
        return $new_filename;
    }
    
    
    /**
     * Получение типа (объекта) протокола для скачивания.
     */
    public static function getProtocolInstance($protocol, $data = array())
    {
        $protocols = self::getProtocols();
        
        $classname = $protocols[$protocol]['classname'];
        
        if (!class_exists($classname) || !isset($classname::$title)) {
            throw new Exception('Protocol incorrect');
        }
        $instance = new $classname($data);
        
        return $instance;
    }
    
        
    /**
     * Получение ассоциативного массива доступных протоколов.
     */
    public static function getProtocols($filter = '*')
    {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/classes/general/protocols/';
        if (!file_exists($dir)) {
            throw new Exception('Protocols folder missing');
            return;
        }
        
        $protocols = array();
        
        foreach (glob($dir . "$filter.protocol.php") as $filename) {
            require_once $filename;
            
            $code = basename($filename, '.protocol.php');
            
            $classname = "LinemediaAuto" . ucfirst($code) . "Protocol";
            
            if (!class_exists($classname) || !isset($classname::$title)) {
                continue;
            }
            $instance = new $classname();
            
            $protocols[$code] = array(
                'classname' => $classname,
                'available' => $classname::available(),
                'title'     => $instance->getTitle(),
                'config'    => $classname::getConfigVars(),
            );
        }
        
        /*
         * Создаём событие: получение протоколов.
         */
        $events = GetModuleEvents("linemedia.auto", "OnGetProtocols");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$protocols));
        }
        
        return $protocols;
    }
    
    
    /**
     * Доступна ли конвертация из XLS XLSX в CSV?
     */
    public static function isConversionSupported()
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $returnVal = shell_exec("where unzip");
        } else {
            $returnVal = shell_exec("which ssconvert");
        }

        return (empty($returnVal) ? false : true);
    }
    
    /**
     * Доступна ли распаковка ZIP?
     */
    public static function isUnzipSupported()
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $returnVal = shell_exec("where unzip");
        } else {
            $returnVal = shell_exec("which unzip");
        }
        
        return (empty($returnVal) ? false : true);
    }
    
}

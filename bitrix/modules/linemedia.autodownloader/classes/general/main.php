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
 
/**
 * ��������� ����������
 * Class LinemediaAutoDownloaderMain
 */
class LinemediaAutoDownloaderMain
{
    /**
     * ��������
     * @var string
     */
    protected $protocol;
    /**
     * ������
     * @var array
     */
    protected $task = array();


    /**
     * ������������� ������� ���������
     * @param $protocol - ��������
     * @param array $protocol_data - ������ ���������
     */
    public function __construct($protocol, $protocol_data = array())
    {
	    $available_protocols = self::getProtocols();
	    if (!isset($available_protocols[$protocol])) {
		    throw new Exception('No protocol ' . $protocol);
		    return;
	    }
	    
	    $classname = 'LinemediaAutoDownloader' . ucfirst($protocol) . 'Protocol';
	    $this->protocol = new $classname($protocol_data);
    }

    /**
     * ��������� ������ ������.
     * @param array $task
     */
    public function setTaskData($task = array())
    {
	    $this->task = (array) $task;
    }

    /**
     * ����������.
     * @return string
     * @throws Exception
     */
    public function download()
    {
    	try {
	    	$tmp_file_name = $this->protocol->download();
    	} catch (Exception $e) {
	    	throw new Exception('Error downloading file: ' . $e->GetMessage());
    	}

        if(file_exists($tmp_file_name)) {

            $new_folder = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/pending/';
            $original_filename = $this->protocol->getOriginalFileName();
            $new_filename = $this->task['id'] . '_' . $this->task['supplier_id'] . '_' . $original_filename;
            rename($tmp_file_name, $new_folder . $new_filename);

        } else {
            throw new Exception('Downloaded file "' . $tmp_file_name . '" not exists!');
        }
    	
    	return $new_filename;
    }

    /**
     * ��������� ���� (�������) ��������� ��� ����������.
     * @param $protocol
     * @return mixed
     * @throws Exception
     */
    public static function getProtocolInstance($protocol)
    {
	    $file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autodownloader/classes/general/protocols/' . $protocol . '.protocol.php';
        if (!file_exists($file)) {
            throw new Exception('Protocol file missing');
            return;
        }
        require_once($file);
        $classname = "LinemediaAutoDownloader" . ucfirst($protocol) . "Protocol";
            
        if (!class_exists($classname) || !isset($classname::$title)) {
        	throw new Exception('Protocol file incorrect');
        }
        return new $classname;
    }

    /**
     * ��������� �������������� ������� ��������� ����������.
     * @param string $filter
     * @return array
     * @throws Exception
     */
    public static function getProtocols($filter = '*')
    {
	    $dir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autodownloader/classes/general/protocols/';
        if (!file_exists($dir)) {
            throw new Exception('Protocols folder missing');
            return;
        }
        
        $protocols = array();
        foreach (glob($dir . "$filter.protocol.php") as $filename) {
            require_once $filename;
            $code = basename($filename, '.protocol.php');
            
            $classname = "LinemediaAutoDownloader" . ucfirst($code) . "Protocol";
            
            if (!class_exists($classname) || !isset($classname::$title)) {
                continue;
            }
            $protocols[$code] = array(
            	'available' => $classname::available(),
            	'title' => $classname::$title,
            	'config' => $classname::getConfigVars(),
            );
        }
        return $protocols;
    }

    /**
     * �������� �� ����������� �� XLS XLSX � CSV?
     * @return bool
     */
    public static function isConversionSupported()
    {
	    $returnVal = shell_exec("which ssconvert");
	    return (empty($returnVal) ? false : true);
    }
}

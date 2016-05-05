<?php

/**
 * Linemedia Autoportal
 * Downloader parser module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

/**
 * Протокол передачи данных file
 */
class LinemediaAutoFileProtocol extends LinemediaAutoProtocol
{
    public static $title = 'FILE';
    
    private $original_filename;
    /**
     * Загрузка файла.
     */
    public function upload($file = '')
    {
	    $tmpfilename = tempnam($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/', 'lm_auto_');

	    $uploader = new qqFileUploader();

	    $result = $uploader->handleUpload($tmpfilename);

        if (!empty($result['error'])) {
		    die(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
	    }
	    
	    $this->original_filename = $uploader->getOriginalFileName();

        return $tmpfilename;
    }
    
    
    /**
     * Загрузка файла.
     * @param bool $test - тестирование возможности загрузки.
     */
    public function download($test = false)
    {
        if ($test) {
            if (!is_writeable($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/')) {
                return 'Error write upload directory';
            }
            return true;
        }
        return false;
    }

    /**
     * Заголовок протокола подключения.
     * @return mixed
     */
    public function getTitle()
    {
        return GetMessage('FILE_TITLE');
    }
    
    
    /**
     * Дополнительные параметры.
     * @return array
     */
    public static function getConfigVars()
    {
        return array();
    }

    /**
     * Доступность: всегда.
     * @return bool
     */
    public static function available()
    {
        return true;
    }

    /**
     * Требования.
     * @return mixed
     */
    public static function getRequirements()
    {
        return GetMessage('FILE_REQUIREMENTS');
    }

    /**
     * Оригинальное имя файла
     * @return string
     */
    public function getOriginalFileName() {
        return basename($this->original_filename);
    }
}


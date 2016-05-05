<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Downloader module
 * Module events for main module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

/**
 * ����������� ������� ��� ������ linemedia.auto
 * Class LinemediaAutoDownloaderEventLinemediaAuto
 */
class LinemediaAutoDownloaderEventLinemediaAuto
{
    /**
     * ���������� ����� ���������� ��� ����������.
     * @param $protocols
     * @throws Exception
     */
    public function OnGetProtocols_InclusionProtocols(&$protocols)
    {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autodownloader/classes/general/protocols/';
        if (!file_exists($dir)) {
            throw new Exception('Protocols folder missing');
            return;
        }
        
        foreach (glob($dir . "*.protocol.php") as $filename) {
            require_once $filename;
            
            $code = basename($filename, '.protocol.php');
            
            $classname = "LinemediaAutoDownloader" . ucfirst($code) . "Protocol";
            
            if (!class_exists($classname) || !isset($classname::$title)) {
                continue;
            }
            $instance = new $classname();
            
            // ���������� ���������.
            $instance->inclusion($protocols);
        }
    }

    /**
     * ������� ���� � ������ ����������� �� �������� ������
     * @param $logs
     */
    public function OnLogsListGet_addDownloaderLogs(&$logs)
    {
        $logs['linemedia.autodownloader'] = array(
            array(
                'filename' => '/upload/linemedia.autodownloader/downloader', // .log ����������� ����
                'title' => GetMessage('LM_AUTO_DOWNLOADER_LOG_DOWNLOADER'),
            ),
            array(
                'filename' => '/upload/linemedia.autodownloader/converter', // .log ����������� ����
                'title' => GetMessage('LM_AUTO_DOWNLOADER_LOG_CONVERTER'),
            ),
        );
    }

    /**
     * ������� ��������
     * @param $check
     */
    public function OnRequirementsListGet_addDownloaderChecks(&$check)
    {
        $add = array();
        
        /*
         * ��������� ���������
         */
        $protocols = LinemediaAutoDownloaderMain::getProtocols();
        foreach ($protocols as $code => $protocol) {
            $instance = LinemediaAutoDownloaderMain::getProtocolInstance($code);
            $requirements = $instance::getRequirements();
        
            $add []= array(
                'title' => GetMessage('LM_AUTO_DOWNLOADER_PROTOCOL_AVAILABLE') .' ' . $instance::$title,
                'requirements' => $requirements,
                'status' => (bool) $protocol['available'],
            );
        }
        
        IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autodownloader/install/install-steps/init.php');
        
        /*
         * ���������
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_DOWNLOADER_CONVERTER_AVAILABLE'),
            'requirements' => GetMessage('LM_AUTO_DOWNLOADER_NO_CONVERTER') . GetMessage('LM_AUTO_PHP_NO_SHELL'),
            'status' => (bool) LinemediaAutoDownloaderMain::isConversionSupported(),
        );
        

        
        $check['linemedia.autodownloader'] = $add;
    }

    /**
     * �������� ����������������� �������
     * @param $id
     */
    public function OnBeforeCustomFieldRemove_checkTasks(&$id)
    {
        if (!CModule::IncludeModule('linemedia.autodownloader')) {
            return;
        }
        // ��������� �� �������� ������� ��� ������� �����?
    }
    
}

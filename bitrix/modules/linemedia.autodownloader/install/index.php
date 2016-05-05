<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Downloader module
 * Installation
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 


/**
 * Language
 */
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
@include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));
IncludeModuleLangFile($strPath2Lang."/install/index.php");
 

class linemedia_autodownloader extends CModule
{
    
    /*
     * Настройки модуля
     */
    var $MODULE_ID           = "linemedia.autodownloader";//без var не пускает в маркетплейс
    public $MODULE_VERSION      = '';
    public $MODULE_VERSION_DATE = '';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    
    public $MODULE_GROUP_RIGHTS;
    
    public $PARTNER_NAME = "";
    public $PARTNER_URI  = "";
    
    
    /*
     * Настройки установщика
     */
    private $install_step_id = 'init';
    private $uninstall_step_id = 'data-remove';
    private $install_settings = array();
    
    
    /*
     * Массив всех регистрируемых событий
     */
    private $lm_events = array(
    	array(
            'linemedia.auto',
            'OnLogsListGet',
            'linemedia.autodownloader',
            'LinemediaAutoDownloaderEventLinemediaAuto',
            'OnLogsListGet_addDownloaderLogs'
        ),
        array(
            'linemedia.auto',
            'OnRequirementsListGet',
            'linemedia.autodownloader',
            'LinemediaAutoDownloaderEventLinemediaAuto',
            'OnRequirementsListGet_addDownloaderChecks'
        ),
        array(
            'linemedia.auto',
            'OnGetProtocols',
            'linemedia.autodownloader',
            'LinemediaAutoDownloaderEventLinemediaAuto',
            'OnGetProtocols_InclusionProtocols'
        ),
    );
    
    
    /**
     * Инициализация модуля для страницы "Управление модулями"
     */
    public function linemedia_autodownloader()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
    
        $this->MODULE_NAME           = GetMessage('LM_AUTO_DOWNLOADER_MODULE_NAME');
        $this->MODULE_DESCRIPTION    = GetMessage('LM_AUTO_DOWNLOADER_MODULE_DESC');
        
        /*
        * версия модуля из файла version.php
        */
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        
        
        /*
         * Почему-то эти параметры надо именно установить, а не просто прописать в переменных
         */
        $this->MODULE_ID = "linemedia.autodownloader";
        $this->PARTNER_NAME = "Linemedia";
        $this->PARTNER_URI = "http://auto.linemedia.ru/";
        $this->MODULE_GROUP_RIGHTS = "Y";
    }
    
    
    
    /**
     * Устанавливаем модуль.
     */
    public function DoInstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        
        /*
         * Модуль Sale не установлен
         */
        if (!IsModuleInstalled('linemedia.auto')) {
           $APPLICATION->ThrowException('Linemedia Auto module is not installed'); 
           return false;
        }
        
        /*
         * Модуль уже установлен
         */
        if (IsModuleInstalled('linemedia.autodownloader')) {
            return false;
        }
        
        /*
         * Сессия неправильная
         */
        if (!check_bitrix_sessid()) {
            return false;
        }
        
        /*
         * Шаг установщика
         */
        if (isset($_REQUEST['install_step_id'])) {
            $this->install_step_id = strval($_REQUEST['install_step_id']);
        }
        
        /*
         * Добавим стили установщика и jQuery
         */
        //$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.autodownloader/interface/style.css");
        //$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");
        
        
        
        include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autodownloader/include.php');
        
        
        /*
         * Выбираем шаг
         */
        switch ($this->install_step_id) {
            case 'init':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_DOWNLOADER_INSTALL_STEP_1"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autodownloader/install/install-steps/init.php");
                return;
                break;
            
            case 'finish':
                include 'install-steps/init-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_DOWNLOADER_INSTALL_STEP_FINISH_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autodownloader/install/install-steps/finish.php");
                return;
                break;
            
            
            default:
                $APPLICATION->ThrowException('Incorrect step'); 
                return;
                break;
        }
    }
 
    /**
     * Удаляем модуль
     */
    public function DoUninstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        
        /*
         * Сессия неправильная
         */
        if (!check_bitrix_sessid()) {
            return false;
        }
        
        /*
         * Шаг установщика
         */
        if (isset($_REQUEST['uninstall_step_id'])) {
            $this->uninstall_step_id = strval($_REQUEST['uninstall_step_id']);
        }
        
        /*
         * Добавим стили установщика и jQuery
         */
        //$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.autodownloader/interface/style.css");
        //$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");
        
               
        
        /*
         * Выбираем шаг
         */
        switch ($this->uninstall_step_id) {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_DOWNLOADER_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autodownloader/install/uninstall-steps/data-remove.php");
                return;
                break;
            
            case 'finish':
                include 'uninstall-steps/data-remove-commit.php';
                break;
                
            default:
                $APPLICATION->ThrowException('Incorrect step'); 
                return;
                break;
        }
    }
    
    
    
    
    /**
     * Добавляем события
     *
     * @return bool
     */
    public function InstallEvents()
    {
        foreach ($this->lm_events as $event) {
            RegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
        }
        return true;
    }
    
    
    /**
     * Удаляем события
     *
     * @return bool
     */
    public function UnInstallEvents()
    {
        foreach ($this->lm_events as $event) {
            UnRegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
        }
        return true;
    }
    
    
    /**
     * Копируем файлы административной части
     *
     * @return bool
     */
    public function InstallFiles()
    {
        global $APPLICATION;
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autodownloader/install/admin", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", true
        );
        
        
        /*
         * Административные иконки
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autodownloader/install/themes/", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true
        );
        
        
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 0700, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting/', 0700, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting_error/', 0700, true);
        
        return true;
    }
    
    
    /**
     * Удаляем файлы
     *
     * @return bool
     */
    public function UnInstallFiles()
    {
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autodownloader/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
        );
        return true;
    }
    
    
    /**
     * Добавляем таблицы в БД
     *
     * @return bool
     */
    public function InstallDB()
    {
    	global $DB, $DBType;
    	$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.autodownloader/install/db/".$DBType."/structure.sql");
	    if (is_array($errors) && count($errors) > 0) {
	        foreach ($errors as $error) {
	            echo $error;
	        }
	        ShowError(GetMessage('LM_AUTO_DOWNLOADER_ERROR_CREATING_DATABASE'));
	        return false;
	    }
        return true;
    }
    
    
    /**
     * Удаляем таблицы из БД
     *
     * @return bool
     */
    public function UnInstallDB()
    {
	    global $DB, $DBType;
	    $errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.autodownloader/install/db/".$DBType."/structure-uninstall.sql");
	    if (is_array($errors) && count($errors) > 0) {
	        echo  GetMessage('LM_AUTO_DOWNLOADER_ERROR_CREATING_DATABASE');
	        foreach ($errors as $error) {
	            ShowError($error);
	        }
	        return false;
	    }
        return true;
    }
    
    
    /**
     * Добавляем агентов
     *
     * @return bool
     */
    public function InstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/addagent.php
        
        $success = CAgent::AddAgent(
            "LinemediaAutoDownloaderDownloadAgent::run();",
            "linemedia.autodownloader",
            "N",
            120
        );
        /*
        if ($success) {
        	$success = CAgent::AddAgent(
	            "LinemediaAutoDownloaderConverterAgent::run();",
	            "linemedia.autodownloader",
	            "N",
	            120
	        );
        }
        */
        return $success != false;
    }
    
    
    /**
     * Удаляем агентов
     *
     * @return bool
     */
    public function UninstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/removemoduleagents.php
        CAgent::RemoveModuleAgents('linemedia.autodownloader');
        return true;
    }
    
    
    /**
     * Добавление правил обработки адресов.
     */
    public function InstallRewrites()
    {
        return true;
    }
    
    
    
    
    /**
    *  Установка свойства инфоблока
    */
    public function InstallIblocks()
    {
	    return true;
    }
    
    
    
    
    /**
    *  Удаление свойства инфоблока
    */
    public function RemoveIblocks()
    {
	    return true;
    }
    
    
    
    /**
     * Удаление правил обработки адресов.
     */
    public function UninstallRewrites()
    {
        return true;
    }
    
    
    /**
     * Функция предустановки параметров.
     * 
     * @param array $settings
     */
    public function setInstallSettings($settings)
    {
        $this->install_settings = (array) $settings;
    }
    
}

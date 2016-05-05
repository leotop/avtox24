<?php 
/**
 * Linemedia Autoportal
 * Analogs simple module
 * Installation
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Language
 */
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
@include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));
IncludeModuleLangFile($strPath2Lang."/install/index.php");
 
/**
* Базовый класс для установки модуля
*/
class linemedia_autoanalogssimple extends CModule
{
    
    /**
     * ID модуля
     */
    var $MODULE_ID           = "linemedia.autoanalogssimple";//без var не пускает в маркетплейс
    
    /**
     * Весрия модуля
     */
    public $MODULE_VERSION      = '';
    
    /**
     * Дата создания текущей версии модуля
     */
    public $MODULE_VERSION_DATE = '';
    
    /**
     * Название модуля
     */
    public $MODULE_NAME;
    
    /**
     * Описание модуля
     */
    public $MODULE_DESCRIPTION;
    
    /**
     * Доступ к модулю по умолчанию
     */
    public $MODULE_GROUP_RIGHTS = 'Y';
    
    /**
     * Название партнёра
     */
    public $PARTNER_NAME = "";
    
    /**
     * Адрес сайта партнёра
     */
    public $PARTNER_URI  = "";
    
    /**
     * Перезаписывать ли файлы модуля
     */
    private $rewrite_module_files = true;
    
    
    /**
     * Настройки установщика (шаг)
     */
    private $install_step_id = 'analogs-db';
    
    /**
     * Настройки установщика (шаг удаления)
     */
    private $uninstall_step_id = 'data-remove';
    
    /**
     * Настройки установщика (прочее)
     */
    private $install_settings = array();
    
    
    
    
    
    /**
    * Массив всех регистрируемых событий
    */
    private $lm_events = array(
        array(
            'linemedia.auto',
            'OnSearchExecuteBegin',
            'linemedia.autoanalogssimple',
            'LinemediaAutoAnalogsSimpleEventLinemediaAuto',
            'OnSearchExecuteBegin_addSimpleAnalogs'
        ),
        array(
            'linemedia.api',
            'OnModulesScan',
            'linemedia.autoanalogssimple',
            'LinemediaAutoAnalogsSimpleEventApi',
            'OnModulesScan_AddAPI'
        ),
    );
    
    
    /**
     * Инициализация модуля для страницы "Управление модулями"
     */
    public function linemedia_autoanalogssimple()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
    
        $this->MODULE_NAME           = GetMessage( 'LM_AUTO_AS_MODULE_NAME' );
        $this->MODULE_DESCRIPTION    = GetMessage( 'LM_AUTO_AS_MODULE_DESC' );
        
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
		$this->MODULE_ID = "linemedia.autoanalogssimple";
		$this->PARTNER_NAME = "Linemedia";
        $this->PARTNER_URI = "http://auto.linemedia.ru/";
        
        /*
         * Основной модуль
         */
        if (IsModuleInstalled('linemedia.auto')) {
           CModule::IncludeModule('linemedia.auto');
        }
        
    }
 
 
 
    /**
     * Устанавливаем модуль.
     */
    public function DoInstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        
        /*
         * Основной модуль не установлен
         */
        if (!IsModuleInstalled('linemedia.auto')) {
           $APPLICATION->ThrowException('Main module missing (linemedia.auto)'); 
           return false;
        }
        
        /*
         * Модуль уже установлен
         */
        if (IsModuleInstalled('linemedia.autoanalogssimple')) {
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
		if (isset($_REQUEST['install_step_id']))
		{
		    $this->install_step_id = strval($_REQUEST['install_step_id']);
        }
        
        
        /*
         * Выбираем шаг
         */
        switch ($this->install_step_id)
        {
            case 'analogs-db':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_PARTS_DB_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autoanalogssimple/install/install-steps/analogs-db.php");
                return;
            break;
            case 'finish':
                include($DOCUMENT_ROOT."/bitrix/modules/linemedia.autoanalogssimple/install/install-steps/analogs-db-save.php");
                
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
		if (isset($_REQUEST['uninstall_step_id']))
		{
		    $this->uninstall_step_id = strval($_REQUEST['uninstall_step_id']);
        }
        
        
        /*
         * Выбираем шаг
         */
        switch ($this->uninstall_step_id)
        {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_PARTS_DB_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autoanalogssimple/install/uninstall-steps/data-remove.php");
                return;
            break;
            case 'finish':
                include($DOCUMENT_ROOT."/bitrix/modules/linemedia.autoanalogssimple/install/uninstall-steps/data-remove-commit.php");
                return;
            break;
        }
		
		
    }
    
    
    
    
    
    
    
    
    
    /**
     * Удаляем события
     *
     * @return bool
     */
    public function UnInstallEvents()
    {
        foreach($this->lm_events AS $event)
        {
            UnRegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
        }
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
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autoanalogssimple/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
        );
        return true;
    }
    
    
    /**
     * Предустановка свойств модуля.
     */
    public function presetOption()
    {
        include '../default_option.php';
        
        foreach ($linemedia_auto_analogssimple_default_option as $code => $value) {
            COption::SetOptionString($this->MODULE_ID, $code, $value);
        }
        
        return true;
    }
}

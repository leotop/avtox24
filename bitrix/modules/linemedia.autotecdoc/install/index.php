<?
/**
 * Linemedia Autoportal
 * Tecdoc module
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
 * pivotal class in setting up autotecdoc module
 * extended CModule
 * @see CModule
 *
 */
class linemedia_autotecdoc extends CModule
{
    
    /**
     * MODULE_ID
     * @var string $MODULE_ID = 'linemedia.autotecdoc'
     */
    var $MODULE_ID           = "linemedia.autotecdoc";//без var не пускает в маркетплейс
    
    /**
     * MODULE_VERSION
     * @var string $MODULE_VERSION
     */
    public $MODULE_VERSION      = '';
    
    /**
     * MODULE_VERSION_DATE
     * @var string $MODULE_VERSION_DATE
     */
    public $MODULE_VERSION_DATE = '';
    
    /**
     * MODULE_NAME
     * @var string $MODULE_NAME
     */
    public $MODULE_NAME;
    
    /**
     * MODULE_DESCRIPTION
     * @var string $MODULE_DESCRIPTION
     */
    public $MODULE_DESCRIPTION;
    
    /**
     * $MODULE_GROUP_RIGHTS
     * @var string $MODULE_GROUP_RIGHTS
     */
    public $MODULE_GROUP_RIGHTS;
    
    /**
     * PARTNER_NAME
     * @var string $PARTNER_NAME
     */
    public $PARTNER_NAME = "";
    
    /**
     * PARTNER_URI
     * @var string $PARTNER_URI
     */
    public $PARTNER_URI  = "";
    
    /**
     * rewrite_module_files
     * @var boolean $rewrite_module_files
     */
    private $rewrite_module_files = true;
    
    
    /*
     * Настройки установщика
     */
    
    /**
     * install_step_id
     * @var string $install_step_id
     */
    private $install_step_id = 0;
    
    /**
     * uninstall_step_id
     * @var string $uninstall_step_id
     */
    private $uninstall_step_id = 'data-remove';
    
    /**
     * install_settings
     * @var array $install_settings 
     */
    private $install_settings = array();
    
    /**
     * array of all registered events
     * @var array $lm_events
     */
    private $lm_events = array();
    
    
    /**
     * initiating module for page 'Module manager'
     * @return void
     */
    public function linemedia_autotecdoc()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
    
        $this->MODULE_NAME           = GetMessage('LM_AUTO_TECDOC_MODULE_NAME');
        $this->MODULE_DESCRIPTION    = GetMessage('LM_AUTO_TECDOC_MODULE_DESC');
        
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
		$this->MODULE_ID = "linemedia.autotecdoc";
		$this->PARTNER_NAME = "Linemedia";
        $this->PARTNER_URI = "http://auto.linemedia.ru/";
        		
		
		/*
		 * У нас ещё нет своих классов
		 */
		include_once($DOCUMENT_ROOT."/bitrix/modules/linemedia.autotecdoc/classes/general/file_helper.php");
    }
 
 
 
    /**
     * setting up module
     * (non-PHPdoc)
     * @see CModule::DoInstall()
     * @return void
     */
    public function DoInstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        
        /*
         * Модуль Sale не установлен
         */
        if (!IsModuleInstalled('iblock')) {
           $APPLICATION->ThrowException('Modules missing (iblock)'); 
           return false;
        }
        
        /*
         * Модуль уже установлен
         */
        if (IsModuleInstalled('linemedia.autotecdoc')) {
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
		$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.autotecdoc/interface/style.css");
		$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

		
        /*
         * Выбираем шаг
         */
        switch ($this->install_step_id) {
            case 'api':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_TECDOC_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autotecdoc/install/install-steps/api.php");
                return;
                break;
            
            case 'demo-folder':
                include 'install-steps/api-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_TECDOC_INSTALL_DEMO_FOLDER_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autotecdoc/install/install-steps/demo-folder.php");
                return;
                break;
            
            case 'iblocks':
                include 'install-steps/demo-folder-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_TECDOC_INSTALL_IBLOCKS_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autotecdoc/install/install-steps/iblocks.php");
                return;
                break;
            
            case 'finish':
                include 'install-steps/iblocks-save.php';
                include 'install-steps/install.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_TECDOC_INSTALL_STEP_FINISH_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autotecdoc/install/install-steps/finish.php");
                return;
                break;
            
            default:
                $APPLICATION->ThrowException('Incorrect step'); 
                return;
                break;
        }
    }
 
    /**
     * deleting module
     * (non-PHPdoc)
     * @see CModule::DoUninstall()
     * @return void
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
		$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.autotecdoc/interface/style.css");
		$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");
		
        
        // Удаление свойств интернет-магазина.
        $this->UninstallSaleProps();
        
        // Удаление правил обработки адресов.
        $this->UninstallRewrites();
        
        // Удаление почтовых шаблонов.
        $this->UninstallMessageTemplates();
        
        /*
         * Выбираем шаг
         */
        switch ($this->uninstall_step_id) {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_TECDOC_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autotecdoc/install/uninstall-steps/data-remove.php");
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
     * adding events
     * (non-PHPdoc)
     * @see CModule::InstallEvents()
     * @return boolean
     */
    public function InstallEvents()
    {
        foreach ($this->lm_events as $event) {
            RegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
        }
        return true;
    }
    
    
    /**
     * removing events
     * (non-PHPdoc)
     * @see CModule::UnInstallEvents()
     * @return boolean
     */
    public function UnInstallEvents()
    {
        foreach ($this->lm_events as $event) {
            UnRegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
        }
        return true;
    }
    
    
    /**
     * copying all files into administrative folder
     * (non-PHPdoc)
     * @see CModule::InstallFiles()
     * @return bool
     */
    public function InstallFiles()
    {
        global $linemedia_autotecdoc_default_option;
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autotecdoc/install/components", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", $this->rewrite_module_files, true
        );
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autotecdoc/install/admin", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", $this->rewrite_module_files
        );
        
        /*
         * Установка демо-папки
         */
        if ($this->install_settings['demo_folder']['install']) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/linemedia.autotecdoc/install/public/ru/demo-folder/", 
                $_SERVER["DOCUMENT_ROOT"] . $this->install_settings['demo_folder']['path'], $this->install_settings['demo_folder']['rewrite'], true
            );
            
            /*
             * Заменим во всех файлах // на реальный путь.
             */
            $demodir = '/'.trim($this->install_settings['demo_folder']['path'], '/').'/';
            LinemediaAutoTecDocFileHelper::fileStrReplace($_SERVER['DOCUMENT_ROOT'].$demodir, '#DEMO_FOLDER#', $demodir);
            
            /*
             * Сохраним настройки, зависящие от пути к демо-папке.
             */
            COption::SetOptionString($this->MODULE_ID, 'LM_AUTO_TECDOC_DEMO_FOLDER', $demodir);
            
            CUrlRewriter::Add(array(
                'CONDITION' => '#^/tecdoc-part-detail/([^\/]+?)/([^\/]+?)/#',
                'PATH'      => $demodir.'detail/index.php',
                'RULE'      => 'ARTICLE_ID=$1&ARTICLE_LINK_ID=$2',
                'ID'        => ''
            ));
            CUrlRewriter::Add(array(
                'CONDITION' => '#^'.$demodir.'#',
                'PATH'      => $demodir.'index.php',
                'RULE'      => '',
                'ID'        => $this->MODULE_ID.':tecdoc.catalog'
            ));
        }
        
        
        /*
         * Папка модуля в upload.
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autotecdoc/');
        
        /*
         * Папка для изображений брендов.
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autotecdoc/images/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autotecdoc/images/brands/');
        
        
        /*
         * Изображения брендов.
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autotecdoc/install/images/brands", 
            $_SERVER["DOCUMENT_ROOT"]."/upload/linemedia.autotecdoc/images/brands/", false, true
        );
        
        return true;
    }
    
    
    /**
     * removing unnecessary files 
     * (non-PHPdoc)
     * @see CModule::UnInstallFiles()
     * @return bool
     */
    public function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/linemedia.autotecdoc/");
        return true;
    }
    
    
    /**
     * adding DB tables
     * (non-PHPdoc)
     * @see CModule::InstallDB()
     * @return boolean
     */
    public function InstallDB()
    {
        return true;
    }
    
    
    /**
     * removing DB tables
     * (non-PHPdoc)
     * @see CModule::UnInstallDB()
     * @return boolean
     */
    public function UnInstallDB()
    {
        return true;
    }
    
    
    /**
     * installing agents
     * @return boolean
     */
    public function InstallAgents()
    {
        return true;
    }
    
    /**
     * uninstalling agents
     * @return boolean
     */
    public function UninstallAgents()
    {
        return true;
    }
    
    
    /**
     * creating order`s features
     * @return boolean
     */
    public function InstallSaleProps()
    {
        return true;
    }
    
    

    /**
     * removing order`s features
     * @return boolean
     */
    public function UninstallSaleProps()
    {
        return true;
    }
    
    
    /**
     * adding postal template
     * @return boolean
     */
    public function InstallMessageTemplates()
    {
        return true;
    }
    
    

    /**
     * removing postal template
     * @return boolean
     */
    public function UninstallMessageTemplates()
    {
        return true;
    }
    
    
    /**
     * adding rules of address`s processing
     * @return boolean
     */
    public function InstallRewrites()
    {
        return true;
    }
    
    
    /**
     * removing rules of address`s processing
     * @return boolean
     */
    public function UninstallRewrites()
    {
        return true;
    }
    
}

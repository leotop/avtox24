<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Garage module
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
 

class linemedia_autogarage extends CModule
{
    
    /*
     * Настройки модуля
     */
    var $MODULE_ID              = "linemedia.autogarage"; //без var не пускает в маркетплейс
    public $MODULE_VERSION      = '';
    public $MODULE_VERSION_DATE = '';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    
    public $MODULE_GROUP_RIGHTS;
    
    public $PARTNER_NAME = "";
    public $PARTNER_URI  = "";
    
    
    private $rewrite_module_files = true;
    
    
    /*
     * Настройки установщика
     */
    private $install_step_id    = 'demo-folder';
    private $uninstall_step_id  = 'data-remove';
    private $install_settings   = array();
    
    
    
    /*
     * Массив всех регистрируемых событий
     */
    public static $lm_events = array(
        array(
            'linemedia.auto',
            'OnShowOrderCreateForm',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnShowOrderCreateForm_addGarageInfo'
        ),
        array(
            'linemedia.auto',
            'OnAfterOrderAdd',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnAfterOrderAdd_addSaleProps'
        ),
        array(
            'linemedia.auto',
            'OnAfterOrderEdit',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnAfterOrderAdd_addSaleProps'
        ),
        array(
            'linemedia.auto',
            'OnVinShowHTML',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnVinShowHTML_addGarageItems'
        ),
        array(
            'linemedia.auto',
            'OnVinShowIBlockHTML',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnVinShowIBlockHTML_addGarageItems'
        ),
        array(
            'linemedia.auto',
            'OnVinAutoAdd',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnVinAutoAdd_addAutoToGarage'
        ),
        array(
            'linemedia.auto',
            'OnPublicMenuBuild',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnPublicMenuBuild_addLinkToDemoFolder'
        ),
        array(
            'linemedia.auto',
            'OnBeforeUserDelete',
            'linemedia.autogarage',
            'LinemediaAutoGarageEventLinemediaAuto',
            'OnAdminShowOrderProps_hideProps'
        ),
	    array(
		    'linemedia.catalogs',
		    'OnApplianceSave',
		    'linemedia.autogarage',
		    'LinemediaAutoGarageEventLinemediaCatalogs',
		    'OnApplianceSave_AddAutoToGarage'
	    ),
		array(
    		'linemedia.catalogs',
    		'OnPresetApplianceList',
    		'linemedia.autogarage',
    		'LinemediaAutoGarageEventLinemediaCatalogs',
    		'OnPresetApplianceList_UpdateAutoList'
    	),
    );
    
    
    /**
     * Инициализация модуля для страницы "Управление модулями"
     */
    public function linemedia_autogarage()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
    
        $this->MODULE_NAME           = GetMessage('LM_AUTO_GARAGE_MODULE_NAME');
        $this->MODULE_DESCRIPTION    = GetMessage('LM_AUTO_GARAGE_MODULE_DESC');
        
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
		$this->MODULE_ID = "linemedia.autogarage";
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
        if (IsModuleInstalled('linemedia.autogarage')) {
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
         * Выбираем шаг
         */
        switch ($this->install_step_id) {
            case 'demo-folder':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_GARAGE_INSTALL_STEP_DEMO_FOLDER_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autogarage/install/install-steps/demo-folder.php");
                return;
                break;
                
            case 'iblocks':
                include 'install-steps/demo-folder-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_GARAGE_INSTALL_STEP_IBLOCKS_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autogarage/install/install-steps/iblocks.php");
                return;
                break;
                
            case 'finish':
                include 'install-steps/iblocks-save.php';
                include 'install-steps/install.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_GARAGE_INSTALL_STEP_FINISH_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autogarage/install/install-steps/finish.php");
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
         * Выбираем шаг
         */
        switch ($this->uninstall_step_id) {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_GARAGE_INSTALL_GARAGE_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autogarage/install/uninstall-steps/data-remove.php");
                return;
                break;
            case 'finish':
                include($DOCUMENT_ROOT."/bitrix/modules/linemedia.autogarage/install/uninstall-steps/data-remove-commit.php");
                return;
                break;
        }
    }
    
    
    /**
     * Копируем файлы административной части
     *
     * @return bool
     */
    public function InstallFiles()
    {
        global $linemedia_autogarage_default_option;
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autogarage/install/components", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", $this->rewrite_module_files, true
        );
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autogarage/install/admin", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", $this->rewrite_module_files
        );
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autogarage/install/themes/", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true
        );
        
        /*
         * Установка демо-папки
         */
        if ($this->install_settings['demo_folder']['install']) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/linemedia.autogarage/install/public/ru/demo-folder/", 
                $_SERVER["DOCUMENT_ROOT"] . $this->install_settings['demo_folder']['path'], $this->install_settings['demo_folder']['rewrite'], true
            );
            
            /*
             * Заменим во всех файлах // на реальный путь.
             */
            $demodir = '/'.trim($this->install_settings['demo_folder']['path'], '/').'/';
            LinemediaAutoFileHelper::fileStrReplace($_SERVER['DOCUMENT_ROOT'].$demodir, '#DEMO_FOLDER#', $demodir);
            
            /*
             * Сохраним настройки, зависящие от пути к демо-папке.
             */
            COption::SetOptionString($this->MODULE_ID, 'LM_AUTO_GARAGE_DEMO_FOLDER', $demodir);
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
        DeleteDirFilesEx("/bitrix/components/linemedia.autogarage/");
        
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autogarage/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
        );
        
        return true;
    }
    
    
    /**
     * Добавляем события
     *
     * @return bool
     */
    public function InstallEvents()
    {
        foreach (self::$lm_events as $event) {
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
        foreach (self::$lm_events as $event) {
            UnRegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
        }
        return true;
    }
    
    
    /**
     * Добавляем таблицы в БД
     *
     * @return bool
     */
    public function InstallDB()
    {
        return true;
    }
    
    
    /**
     * Удаляем таблицы из БД
     *
     * @return bool
     */
    public function UnInstallDB()
    {
        return true;
    }
    
    
    /**
     * Добавление почтовых шаблонов.
     */
    public function InstallMessageTemplates()
    {
        return true;
    }
    
    
    /**
     * Удаление почтовых шаблонов.
     */
    public function UninstallMessageTemplates()
    {
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
     * Удаление правил обработки адресов.
     */
    public function UninstallRewrites()
    {
        return true;
    }
    
    
    /**
     * Добавляем агентов
     *
     * @return bool
     */
    public function InstallAgents()
    {
        return true;
    }
    
    
    /**
     * Удаляем агентов
     *
     * @return bool
     */
    public function UninstallAgents()
    {
        return true;
    }
    
    
    /**
     * Создание свойств заказа.
     */
    public function InstallSaleProps()
    {
        CModule::IncludeModule('sale');
        
        $sites = array();
        $rsSites = CSite::GetList($b="sort", $o="desc", array());
        while ($arSite = $rsSites->Fetch()) {
            $sites []= $arSite['ID'];
        }
        
        $dbpersons = CSalePersonType::GetList(array(), array('LID' => $sites), false, false, array('ID'));
        while ($person = $dbpersons->Fetch()) {
            $persons []= $person['ID'];
        }
        
        $groups = array();
        foreach ($persons as $person_id) {
            $group = CSaleOrderPropsGroup::GetList(array(), array('PERSON_TYPE_ID' => $person_id, 'NAME' => GetMessage('LM_AUTO_SALE_PROPS_GROUP')), false, false, array('ID'))->Fetch();
            if ($group['ID'] <= 0) {
                $group_id = CSaleOrderPropsGroup::Add(
                    array(
                        'NAME' => GetMessage('LM_AUTO_SALE_PROPS_GROUP'),
                        'PERSON_TYPE_ID' => $person_id
                    )
                );
                $groups[$person_id] = $group_id;
            } else {
                $groups[$person_id] = $group['ID'];
            }
        }
        
        /*
         * Установка свойства заказа.
         */
        include 'sale/props.php';
        
        foreach ($persons as $person_id) {
            foreach ($props as $prop) {
                $prop['PERSON_TYPE_ID'] = $person_id;
                $prop['PROPS_GROUP_ID'] = $groups[$person_id];
                $code = CSaleOrderProps::Add($prop);
                if ($code <= 0) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    
    /**
     * Удаление свойств заказа.
     */
    public function UninstallSaleProps()
    {
        CModule::IncludeModule('sale');
        
        /*
         * Установка свойства заказа.
         */
        include 'sale/props.php';
        
        foreach ($props as $prop) {
            $dbprops = CSaleOrderProps::GetList(array(), array('CODE' => $prop['CODE']), false, false, array('ID'));
            while ($property = $dbprops->Fetch()) {
                CSaleOrderProps::Delete($property['ID']);
            }
        }
        
        return true;
    }
}

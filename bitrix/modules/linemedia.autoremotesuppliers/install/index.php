<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Suppliers parser module
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
 

class linemedia_autoremotesuppliers extends CModule
{
    
    /*
     * Настройки модуля
     */
    var $MODULE_ID           = "linemedia.autoremotesuppliers";//без var не пускает в маркетплейс
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
            'OnSearchExecuteBegin',
            'linemedia.autoremotesuppliers',
            'LinemediaAutoRemoteSuppliersEventLinemediaAuto',
            'OnSearchExecuteBegin_addRemoteSuppliers'
        ),
        array(
            'linemedia.auto',
            'OnRemoteSuppliersGet',
            'linemedia.autoremotesuppliers',
            'LinemediaAutoRemoteSuppliersEventLinemediaAuto',
            'OnRemoteSuppliersGet_addRemoteSuppliers'
        ),
        array(
            'linemedia.auto',
            'OnPartObjectCreate',
            'linemedia.autoremotesuppliers',
            'LinemediaAutoRemoteSuppliersEventLinemediaAuto',
            'OnPartObjectCreate_loadRemotePart'
        ),
        array(
            'linemedia.auto',
            'OnBeforeBasketItemAdd',
            'linemedia.autoremotesuppliers',
            'LinemediaAutoRemoteSuppliersEventLinemediaAuto',
            'OnBeforeBasketItemAdd_addRemoteSuppliers'
        ),
        array(
            'iblock',
            'OnIBlockPropertyBuildList',
            'linemedia.autoremotesuppliers',
            'LinemediaAutoRemoteSuppliersIblockPropertyApi',
            'GetUserTypeDescription'
        ),
        array(
            'linemedia.auto',
            'OnRequirementsListGet',
            'linemedia.autoremotesuppliers',
            'LinemediaAutoRemoteSuppliersEventLinemediaAuto',
            'OnRequirementsListGet_addChecks',
        ),
    );
    
    
    /**
     * Инициализация модуля для страницы "Управление модулями"
     */
    public function linemedia_autoremotesuppliers()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
    
        $this->MODULE_NAME           = GetMessage('LM_AUTO_REMOTE_SUPPLIERS_MODULE_NAME');
        $this->MODULE_DESCRIPTION    = GetMessage('LM_AUTO_REMOTE_SUPPLIERS_MODULE_DESC');
        
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
        $this->MODULE_ID = "linemedia.autoremotesuppliers";
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
        if (IsModuleInstalled('linemedia.autoremotesuppliers')) {
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
         * Добавим стили установщика и jQuery
         */
        //$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.autoremotesuppliers/interface/style.css");
        //$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

        
        /*
         * Выбираем шаг
         */
        switch ($this->install_step_id) {
            case 'init':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_REMOTE_SUPPLIERS_INSTALL_STEP_1"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autoremotesuppliers/install/install-steps/init.php");
                return;
                break;
            
            case 'finish':
                include 'install-steps/init-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_SUPPLIERS_INSTALL_STEP_FINISH_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autoremotesuppliers/install/install-steps/finish.php");
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
        //$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.autoremotesuppliers/interface/style.css");
        //$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");
        
               
        
        /*
         * Выбираем шаг
         */
        switch ($this->uninstall_step_id) {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_REMOTE_SUPPLIERS_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autoremotesuppliers/install/uninstall-steps/data-remove.php");
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
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autoremotesuppliers/install/admin", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/",true
        );
        
        
        /*
         * Административные иконки
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autoremotesuppliers/install/themes/", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true
        );
        
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
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autoremotesuppliers/install/admin',
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
     * Добавляем агентов
     *
     * @return bool
     */
    public function InstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/addagent.php
        /*$success = CAgent::AddAgent(
            "LinemediaAutoRemoteSuppliersImportAgent::run();",
            "linemedia.autoremotesuppliers",
            "N",
            600
        );
        return $success != false;*/

	    $success = CAgent::AddAgent(
		    "LinemediaAutoRemoteSuppliersCacheClearAgent::run();",
		    "linemedia.autoremotesuppliers",
		    "N",
		    7200
	    );
	    if (!$success) {
		    return false;
	    }
    }
    
    
    /**
     * Удаляем агентов
     *
     * @return bool
     */
    public function UninstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/removemoduleagents.php
        CAgent::RemoveModuleAgents("linemedia.autoremotesuppliers");
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
		CModule::IncludeModule('iblock');
		$res = CIBlock::GetList(array(), array('TYPE'=>'linemedia_auto', 'CODE'=>'lm_auto_suppliers'));
		if($ar_res = $res->Fetch())
		{
		  $ib = new CIBlockProperty;
		  $prop = array (
			  'NAME' => GetMessage('LM_AUTO_REMOTE_SUPPLIERS_IBLOCK_PROP_API'),//'API поставщика',
			  'ACTIVE' => 'Y',
			  'SORT' => '100',
			  'CODE' => 'api',
			  'DEFAULT_VALUE' => '',
			  'PROPERTY_TYPE' => 'S',
			  'ROW_COUNT' => '1',
			  'COL_COUNT' => '30',
			  'LIST_TYPE' => 'L',
			  'MULTIPLE' => 'N',
			  'XML_ID' => NULL,
			  'FILE_TYPE' => '',
			  'MULTIPLE_CNT' => '5',
			  'TMP_ID' => NULL,
			  'LINK_IBLOCK_ID' => '0',
			  'WITH_DESCRIPTION' => 'N',
			  'SEARCHABLE' => 'N',
			  'FILTRABLE' => 'N',
			  'IS_REQUIRED' => 'N',
			  'VERSION' => '1',
			  'USER_TYPE' => 'supplier_api',
			  'USER_TYPE_SETTINGS' => NULL,
			  'HINT' => '',
			  'IBLOCK_TYPE_ID' => 'linemedia_auto',
			  'IBLOCK_CODE' => 'lm_auto_suppliers',
		);
		  $prop['IBLOCK_ID'] = $ar_res['ID'];
		  $ib->Add($prop);
		}
		
		return true;
	    
    }
    
    
    
    
    /**
    *  Удаление свойства инфоблока
    */
    public function RemoveIblocks()
    {
		CModule::IncludeModule('iblock');
		$res = CIBlock::GetList(array(), array('TYPE'=>'linemedia_auto', 'CODE'=>'lm_auto_suppliers'));
		if($ar_res = $res->Fetch())
		{
			$properties = CIBlockProperty::GetList(array(), Array("CODE"=>"api", "IBLOCK_ID"=>$ar_res['ID']));
			while ($prop = $properties->GetNext())
			{
				CIBlockProperty::Delete($prop['ID']);
			}
		}
		
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

<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Suppliers module
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
 

class linemedia_autosuppliers extends CModule
{
    
    /*
     * ��������� ������
     */
    var $MODULE_ID              = "linemedia.autosuppliers";//��� var �� ������� � �����������
    public $MODULE_VERSION      = '';
    public $MODULE_VERSION_DATE = '';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    
    public $MODULE_GROUP_RIGHTS = 'Y';
    
    public $PARTNER_NAME = "";
    public $PARTNER_URI  = "";
    
    
    private $rewrite_module_files = true;
    
    
    /*
     * ��������� �����������
     */
    private $install_step_id = 'install';
    private $uninstall_step_id = 'data-remove';
    private $install_settings = array();
    
    
    
    
    
    /*
    * ������ ���� �������������� �������
    */
    private $lm_events = array(
        array(
            'linemedia.auto',
            'OnRequirementsListGet',
            'linemedia.autosuppliers',
            'LinemediaAutoSuppliersEventLinemediaAuto',
            'OnRequirementsListGet_addConverterChecks'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemStatus',
            'linemedia.autosuppliers',
            'LinemediaAutoSuppliersEventLinemediaAuto',
            'OnAfterBasketItemStatus_checkRequestClose'
        ),
        array(
            'main',
            'OnBeforeEventAdd',
            'linemedia.autosuppliers',
            'LinemediaAutoSuppliersEventMain',
            'OnBeforeEventAdd_AttachePrice'
        )
    );
    
    
    /**
     * ������������� ������ ��� �������� "���������� ��������"
     */
    public function linemedia_autosuppliers()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
    
        $this->MODULE_NAME           = GetMessage('LM_AUTO_SUPPLIERS_MODULE_NAME');
        $this->MODULE_DESCRIPTION    = GetMessage('LM_AUTO_SUPPLIERS_MODULE_DESC');
        
        /*
         * ������ ������ �� ����� version.php
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
         * ������-�� ��� ��������� ���� ������ ����������, � �� ������ ��������� � ����������
         */
        $this->MODULE_ID = "linemedia.autosuppliers";
        $this->PARTNER_NAME = "Linemedia";
        $this->PARTNER_URI = "http://auto.linemedia.ru/";
        $this->MODULE_GROUP_RIGHTS = "Y";
    }
 

    /**
     * ������������� ������.
     */
    public function DoInstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        
        /*
         * ������ Sale �� ����������
         */
        if (!IsModuleInstalled('linemedia.auto')) {
           $APPLICATION->ThrowException('Modules missing (linemedia.auto)'); 
           return false;
        }
        
        /*
         * ������ ��� ����������
         */
        if (IsModuleInstalled('linemedia.autosuppliers')) {
            return false;
        }
        
        /*
         * ������ ������������
         */
        if (!check_bitrix_sessid()) {
            return false;
        }
        
        /*
         * ��� �����������
         */
        if (isset($_REQUEST['install_step_id']))
        {
            $this->install_step_id = strval($_REQUEST['install_step_id']);
        }
        
        /*
         * �������� ���
         */
        switch ($this->install_step_id) {
            case 'install':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_SUPPLIERS_INSTALL_STEP_CHOOSE_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autosuppliers/install/install-steps/install.php");
                return;
                break;
            
            case 'finish':
                include 'install-steps/install-save.php';
                $this->InstallMessageTemplates();
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_SUPPLIERS_INSTALL_STEP_FINISH_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autosuppliers/install/install-steps/finish.php");
                return;
                break;
            
            default:
                $APPLICATION->ThrowException('Incorrect step'); 
                return;
                break;
        }
    }
    
    
    /**
     * ������� ������
     */
    public function DoUninstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        
        /*
         * ������ ������������
         */
        if (!check_bitrix_sessid()) {
            return false;
        }
        
        /*
         * ��� �����������
         */
        if (isset($_REQUEST['uninstall_step_id'])) {
            $this->uninstall_step_id = strval($_REQUEST['uninstall_step_id']);
        }
        
        // �������� �������� ��������.
        $this->UninstallMessageTemplates();
        /*
         * �������� ���
         */
        switch ($this->uninstall_step_id) {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_SUPPLIERS_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autosuppliers/install/uninstall-steps/data-remove.php");
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
     * ��������� �������
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
     * ������� �������
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
     * �������� ����� ���������������� �����
     *
     * @return bool
     */
    public function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autosuppliers/install/components", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", $this->rewrite_module_files, true
        );
        
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autosuppliers/install/admin", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", $this->rewrite_module_files
        );
        
        
        /*
         * ���������������� ������
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autosuppliers/install/themes/", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true
        );
        
        /*
         * ����� ��� ������ �� ����������
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autosuppliers/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autosuppliers/upload/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autosuppliers/requests/');
        
        return true;
    }
    
    
    /**
     * ������� �����
     *
     * @return bool
     */
    public function UnInstallFiles()
    {
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.autosuppliers/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
        );
        DeleteDirFilesEx("/bitrix/components/linemedia.autosuppliers/");
        return true;
    }
    
    
    /**
     * ��������� ������� � ��
     *
     * @return bool
     */
    public function InstallDB()
    {
        return true;
    }
    
    
    /**
     * ������� ������� �� ��
     *
     * @return bool
     */
    public function UnInstallDB()
    {
        return true;
    }
    
    
    /**
     * ��������� �������
     *
     * @return bool
     */
    public function InstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/addagent.php
    }
    
    
    /**
     * ������� �������
     *
     * @return bool
     */
    public function UninstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/removemoduleagents.php
        CAgent::RemoveModuleAgents("linemedia.autosuppliers");
        return true;
    }
    
    
    
    /**
     * ���������� ������ ��������� �������.
     */
    public function InstallRewrites()
    {
        return true;
    }
    
    
    /**
     * �������� ������ ��������� �������.
     */
    public function UninstallRewrites()
    {
        return true;
    }
    
    
    /**
     * ������� ������������� ����������.
     * 
     * @param array $settings
     */
    public function setInstallSettings($settings)
    {
        $this->install_settings = (array) $settings;
    }
    
    
    /**
     * ������������� ������� ������.
     */
    public function presetOption()
    {
        include '../default_option.php';
        
        foreach ($linemedia_autosuppliers_default_option as $code => $value) {
            COption::SetOptionString($this->MODULE_ID, $code, $value);
        }
        
        return true;
    }
    
    
    /**
     * ����� ������� � ������.
     */
    public function GetModuleRightList()
    {
        $arr = array(
            'reference_id' => array('D', 'W'),
            'reference' => array(
                '[D] '.GetMessage('LM_AUTO_SUPPLIERS_FORM_DENIED'),
                '[W] '.GetMessage('LM_AUTO_SUPPLIERS_FORM_WRITE')
            )
        );
        return $arr;
    }
    /**
     * ���������� �������� ��������.
     */
    public function InstallMessageTemplates()
    {
        /*
         * ��������� ����� �������� �������.
         */
        include 'messages/ru/types.php';

        foreach ($arTypes as $arTypeLangs) {
            foreach ($arTypeLangs as $arType) {
                $type = new CEventType();
                $type->Add($arType);
            }
        }

        /*
         * ��������� �������� ��������.
         */
        include 'messages/ru/templates.php';

        $rsSites = CSite::GetList($b="sort", $o="asc", array());
        while ($arSite = $rsSites->Fetch()) {
            foreach ($arTemplates as $arTemplate) {
                $arTemplate['LID'] = $arSite['ID'];

                $message = new CEventMessage();
                $message->Add($arTemplate);
            }
        }

        return true;
    }


    /**
     * �������� �������� ��������.
     */
    public function UninstallMessageTemplates()
    {
        /*
         * �������� ����� �������� �������.
         */
        include 'messages/ru/types.php';
        foreach ($arTypes as $arTypeCode => $arTypeLangs) {
            CEventType::Delete($arTypeCode);
        }

        /*
         * �������� �������� ��������.
         */
        $templates = CEventMessage::GetList($b="id", $o="asc", array('TYPE_ID' => implode(' | ', array_keys($arTypes))));
        while ($template = $templates->Fetch()) {
            CEventMessage::Delete($template['ID']);
        }

        return true;
    }
}

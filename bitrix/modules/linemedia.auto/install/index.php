<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Main module
 * Installation
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


include ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/functions.php');


// AJAX-запрос.
if (isset($_REQUEST['AJAX']) && $_REQUEST['id'] == 'linemedia.auto') {
    ob_end_clean();
    include ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/install/ajax.php');
    exit();
}

/**
 * Language
 */
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
@include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));
IncludeModuleLangFile($strPath2Lang."/install/index.php");


class linemedia_auto extends CModule
{

    /*
     * Настройки модуля
     */
    var $MODULE_ID           = "linemedia.auto";//без var не пускает в маркетплейс
    public $MODULE_VERSION      = '';
    public $MODULE_VERSION_DATE = '';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public $MODULE_GROUP_RIGHTS = 'Y';

    public $PARTNER_NAME = "";
    public $PARTNER_URI  = "";


    private $rewrite_module_files = true;


    /*
     * Настройки установщика
     */
    private $install_step_id = 0;
    private $uninstall_step_id = 'data-remove';
    private $install_settings = array();





    /*
    * Массив всех регистрируемых событий
    */
    private static $lm_events = array(
        array(
            'iblock',
            'OnIBlockPropertyBuildList',
            'linemedia.auto',
            'LinemediaAutoIblockPropertyUserGroup',
            'GetUserTypeDescription'
        ),
        array(
            'iblock',
            'OnIBlockPropertyBuildList',
            'linemedia.auto',
            'LinemediaAutoIblockPropertyPriceField',
            'GetUserTypeDescription'
        ),
        array(
            'main',
            'OnBuildGlobalMenu',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnBuildGlobalMenu_CheckMainMenu',
            9999
        ),
        array(
            'main',
            'OnBuildGlobalMenu',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnBuildGlobal_AddMainMenu'
        ),
        array(
            'sale',
            'OnOrderAdd',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnOrderAdd_DescreasePartsCount'
        ),
        array(
            'linemedia.auto',
            'OnSearchExecuteBegin',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnSearchExecuteBegin_addLinemediaApiAnalogs'
        ),
        array(
            'linemedia.auto',
            'OnSearchExecuteBegin',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnSearchExecuteBegin_addLocalDBData'
        ),
        array(
            'linemedia.auto',
            'OnItemPriceCalculate',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnItemPriceCalculate_addSupplierMarkup'
        ),
        array(
            'linemedia.auto',
            'OnItemPriceCalculate',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnItemPriceCalculate_convertSupplierCurrency',
            999999
        ),
        array(
            'linemedia.auto',
            'OnItemPriceCalculate',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnItemPriceCalculate_customDiscounts'
        ),
        array(
            'linemedia.auto',
            'OnRequirementsListGet',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnRequirementsListGet_addChecks',
            1
        ),
        array(
            'main',
            'OnAdminInformerInsertItems',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnAdminInformerInsertItems_addUpdatesCheck',
        ),
        array(
            'iblock',
            'OnIBlockPropertyBuildList',
            'linemedia.auto',
            'LinemediaAutoIblockPropertyCurrency',
            'GetUserTypeDescription'
        ),
        array(
            'linemedia.api',
            'OnModulesScan',
            'linemedia.auto',
            'LinemediaEventApi',
            'OnModulesScan_AddAPI'
        ),
        array(
            'iblock',
            'OnStartIBlockElementAdd',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnStartIBlockElementAdd_setSupplierId'
        ),
        array(
            'iblock',
            'OnBeforeIBlockElementAdd',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnBeforeIBlockElementAdd_checkSupplierId'
        ),
        array(
            'iblock',
            'OnBeforeIBlockElementUpdate',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnBeforeIBlockElementUpdate_checkSupplierId'
        ),
        array(
            'sale',
            'OnBeforeBasketDelete',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnBeforeBasketDelete_checkBasket1CExchange'
        ),
        array(
            'linemedia.auto',
            'OnBeforeBasketItemStatus',
            'linemedia.auto',
            'LinemediaEventApi',
            'OnBeforeBasketItemStatus'
        ),
        array(
            'iblock',
            'OnIBlockPropertyBuildList',
            'linemedia.auto',
            'LinemediaAutoIBlockPropertyCheckbox',
            'GetUserTypeDescription'
        ),
        array(
            'sale',
            'OnSalePayOrder',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnSalePayOrder_checkUserGroups'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemStatus',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketItemStatus_sendMessage'
        ),
        array(
            'sale',
            'OnSaleStatusOrder',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnSaleStatusOrder_updateBasketStatuses'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketStatusesChange',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketStatusesChange_sendMessages'
        ),
        array(
            'main',
            'OnModuleUpdate',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnModuleUpdate_clearCache'
        ),
        array(
            'main',
            'OnBeforeProlog',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnBeforeProlog_addAdminPanelButtons'
        ),
        array(
            'linemedia.auto',
            'OnAfterPriceListAllImport',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterPriceListAllImport_UpdateCatalogPrices'
        ),
        array(
            'iblock',
            'OnAfterIBlockElementAdd',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnAfterIBlockElementAdd_clearCache'
        ),
        array(
            'iblock',
            'OnAfterIBlockElementUpdate',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnAfterIBlockElementUpdate_clearCache'
        ),
        array(
            'iblock',
            'OnIBlockElementDelete',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnBeforeIBlockElementDelete_clearCache'
        ),
        array(
            'sale',
            'OnSalePayOrder',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnSalePayOrder_SetPayBaskets'
        ),
        array(
            'sale',
            'OnSaleCancelOrder',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnSaleCancelOrder_SetCancelBaskets'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemCancel',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketItemCancel_moneyBack'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemCancel',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketItemCancel_checkOrderCancel'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemPay',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketItemPay_checkOrderFullyPayed'
        ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemCancel',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketItemCancelNo_checkOrderSum'
        ),
        array(
            'linemedia.auto',
            'OnSearchResultParts',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnSearchResultParts_SearchStatistics'
        ),
        array(
            'linemedia.auto',
            'OnItemPriceConstruct',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnItemPriceConstruct_selectUserGroupPriceColumn'
        ),
		array(
			'sale',
			'OnBeforeBasketAdd',
			'linemedia.auto',
			'LinemediaAutoEventSelf',
			'OnBeforeBasketAdd_addNDS'
		),
        //нет такого метода
    	//array(
    	//	'linemedia.auto',
    	//	'OnSearchExecuteEnd',
    	//	'linemedia.auto',
    	//	'LinemediaAutoEventSelf',
    	//	'CustomSearchResult_Modificator'
    	//),
        array(
            'linemedia.auto',
            'OnSearchExecuteEnd',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnSearchExecuteEnd_UniteGroupsWithSimilarApp'
        ),
        array(
    		'linemedia.auto',
    		'OnBeforeBasketItemStatus',
    		'linemedia.auto',
    		'LinemediaAutoEventSale',
    		'OnBeforeBasketItemStatus_AmountInStock'
    	),
    	array(
    		'linemedia.auto',
    		'OnBeforeBasketItemCancel',
    		'linemedia.auto',
    		'LinemediaAutoEventSale',
    		'OnBeforeBasketItemCancel_ReIncreaseAmountInStock'
    	),
		array(
    		'linemedia.auto',
    		'OnBeforeBasketStatusesChange',
    		'linemedia.auto',
    		'LinemediaAutoEventSelf',
    		'OnBeforeBasketStatusesChange_CheckPermissions'
    	),
  
        array(
            'linemedia.auto',
            'OnBeforeShowVinList',
            'linemedia.autobranches',
            'LinemediaAutoBranchesEventMain',
            'OnBeforeShowVinList_PossibilityOfAnswer'
        ),

        array(
            'linemedia.auto',
            'OnBeforeShowVinList',
            'linemedia.autobranches',
            'LinemediaAutoBranchesEventMain',
            'OnBeforeShowVinList_SettingUserManager'
        ),
        
        array(
            'linemedia.auto',
            'OnBeforeProductsPageAdd',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnBeforeProductsPageAdd_RenderOnUserRights'
		),

        array(
            'linemedia.auto',
            'OnAfterSetTasksForModule',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterSetTasksForModule_SetIblockPermissions'
        ),
	    array(
		    'currency',
		    'OnCurrencyRateUpdate',
		    'linemedia.auto',
		    'LinemediaAutoEventCurrency',
		    'OnCurrencyRateUpdate_UpdateCurrencyCache'
	    ),
	    array(
		    "linemedia.auto",
		    "OnBeforeOrderAdd",
		    'linemedia.auto',
		    'LinemediaAutoEventSelf',
		    "OnBeforeOrderAdd_RegisterUser"
	    ),
        array(
            'linemedia.auto',
            'OnAfterBasketItemStatus',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterBasketItemStatus_returnMoneyBack'
        ),
        array(
            'main',
            'OnEndBufferContent',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnEndBufferContent_AddStatCode'
        ),
        
        array(
            'sale',
            'OnBeforeOrderDelete',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnBeforeOrderDelete_AmountInStock'
        ),
        
        array(
            'sale',
            'OnBasketOrder',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnBasketOrder_AmountInStock'
        ),
        
        /*temporarily unavailable!!!!!!
        array(
            'linemedia.auto',
            'OnSearchExecuteEnd',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnSearchExecuteEnd_ModifySearchOutcome'
        ),
        */
        //нет такого метода
        //array(
        //    'linemedia.auto',
        //    'OnSearchResultParts',
        //    'linemedia.auto',
        //    'LinemediaAutoEventSelf',
        //    'OnSearchExecuteEnd_ModifySearchOutcome'
        //),
		array(
		    'linemedia.auto',
		    'OnSearchResultParts',
		    'linemedia.auto',
		    'LinemediaAutoEventSelf',
		    'OnSearchResultParts_ModifySearchOutcome'
		),
        array(
            'sale',
            'OnBeforeBasketAdd',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnBeforeBasketAdd_CartModifyByModificator'
        ),

    
    	array(
    		'sale',
    		'OnSalePayOrder',
    		'linemedia.auto',
    		'LinemediaAutoEventSale',
    		'OnSalePayOrder_PayMostObsoleteOrder'
    	),
    		
        array(
            'linemedia.auto',
            'OnApplyGroupAction',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnApplyGroupAction_CommitTransaction'
        ),
        
        array(
            'linemedia.auto',
            'OnAfterAddTransact',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnAfterAddTransact_DepositFunds'
        ),

        array(
            'main',
            'OnBeforeProlog',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnBeforeProlog_addTransaction'
        ),
		
		array(
            'linemedia.auto',
            'OnBeforeBasketUpdateStatuses',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnBeforeBasketUpdateStatuses_Unload'
        ),
        
		array(
            'main',
            'OnPageStart',
            'linemedia.auto',
            'LinemediaAutoEventMain',
            'OnPageStart_Init'
        ),
	    array(
		    'sale',
		    'OnPersonTypeAdd',
		    'linemedia.auto',
		    'LinemediaAutoEventSale',
		    'OnPersonTypeAdd_AddNewPropsToCustomPersonType'
	    ),
		array(
			'linemedia.auto',
    		'OnApplyingStatus',
    		'linemedia.auto',
    		'LinemediaAutoEventSale',
    		'OnApplyingStatus_CreateTransaction'
		),
		array(
			'iblock',
            'OnBeforeIBlockElementAdd',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnBeforeIBlockElementAdd_SetBranchForDiscounts'
		),
		array(
			'iblock',
            'OnBeforeIBlockElementUpdate',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnBeforeIBlockElementAdd_SetBranchForDiscounts'
		),
        array(
            'iblock',
            'OnBeforeIBlockElementAdd',
            'linemedia.auto',
            'LinemediaAutoEventIBlock',
            'OnBeforeIBlockElementAdd_isLimitSuppliersExceeded'
        ),
        /* обработка трнзакций */
        array(
            'linemedia.auto',
            'OnBeforeBasketItemStatus',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnBeforeBasketItemStatus_CreateTransaction'
        ),
        array(
            'linemedia.auto',
            'OnAfterAddTransact',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterAddTransact_process'
        ),
        array(
            'sale',
            'OnAfterUserAccountAdd',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnAfterUserAccountAdd_ExtendTransaction'
        ),
        array(
            'sale',
            'OnAfterUserAccountUpdate',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnAfterUserAccountUpdate_ExtendTransaction'
        ),
        /* история импрота */
        array(
            'linemedia.auto',
            'OnAfterPriceListImport',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnAfterPriceListImport_writeHistory'
        ),
        array(
            'sale',
            'OnBasketDelete',
            'linemedia.auto',
            'LinemediaAutoEventSale',
            'OnBasketDelete_Cleanup'
        ),
        array(
            'linemedia.auto',
            'OnBeginApiQuery',
            'linemedia.auto',
            'LinemediaAutoEventSelf',
            'OnBeginApiQuery_apiHandler'
        ),
        
    );


    /**
     * Инициализация модуля для страницы "Управление модулями"
     */
    public function linemedia_auto()
    {
        global $APPLICATION, $DOCUMENT_ROOT;

        $this->MODULE_NAME           = GetMessage('LM_AUTO_MAIN_MODULE_NAME');
        $this->MODULE_DESCRIPTION    = GetMessage('LM_AUTO_MAIN_MODULE_DESC');

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
        $this->MODULE_ID = "linemedia.auto";
        $this->PARTNER_NAME = "Linemedia";
        $this->PARTNER_URI = "http://auto.linemedia.ru/";
        $this->MODULE_GROUP_RIGHTS = "Y";


        /*
         * У нас ещё нет своих классов
         */
        include_once($DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/classes/general/file_helper.php");
    }



    /**
     * Устанавливаем модуль.
     */
    public function DoInstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
		
		CJSCore::Init(array("jquery"));
		
        /*
         * Модуль Sale не установлен
         */
        if (!IsModuleInstalled('sale') || !IsModuleInstalled('iblock') || !IsModuleInstalled('currency')) {
           $APPLICATION->ThrowException('Modules missing (iblock, currency, sale)');
           return false;
        }

        /*
         * Модуль уже установлен
         */
        if (IsModuleInstalled('linemedia.auto')) {
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

        $APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");


        /*
         * Выбираем шаг
         */
        switch ($this->install_step_id) {
            case 'choose':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_CHOOSE_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/choose.php");
                return;
                break;

            case 'api':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/api.php");
                return;
                break;

            case 'parts-db':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_PARTS_DB_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/parts-db.php");
                return;
                break;

            case 'demo-folder':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_DEMO_FOLDER_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/demo-folder.php");
                return;
                break;

            case 'iblocks':
                include 'install-steps/demo-folder-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_DEMO_FOLDER_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/iblocks.php");
                return;
                break;

            case 'agents':
                include 'install-steps/iblocks-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_AGENTS_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/agents.php");
                return;
                break;

            case 'finish':
                include 'install-steps/agents-save.php';
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_FINISH_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/install-steps/finish.php");
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
		CJSCore::Init(array("jquery"));
		
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

        $APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");


        // Удаление свойств интернет-магазина.
        $this->UninstallSaleProps();

        // Удаление правил обработки адресов.
        $this->UninstallRewrites();

        // Удаление почтовых шаблонов.
        $this->UninstallMessageTemplates();

        // удаление пользовательских свойств
        $this->UnInstallUserFields();

        /*
         * Выбираем шаг
         */
        switch ($this->uninstall_step_id) {
            case 'data-remove':
                $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_MAIN_INSTALL_STEP_API_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/install/uninstall-steps/data-remove.php");
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

	function GetModuleTasks()
	{
        global $DOCUMENT_ROOT;

        include_once($DOCUMENT_ROOT."/bitrix/modules/linemedia.auto/constants.php");

		return array(
			/*Заказы*/
			GetMessage('LM_AUTO_MAIN_ACCESS_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
				)
			),
			GetMessage('LM_AUTO_MAIN_FULL_ACCESS') => array(
				'LETTER' => 'X',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
					'order_read',
					'order_write',
					'order_delete',
					'order_add'
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_ALL') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
					'order_read'
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_WRITE_OWN') => array(
				'LETTER' => 'O',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
					'order_own_read',
					'order_own_write',
					'order_own_delete',
					'order_own_add'
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_OWN_FILIAL') => array(
				'LETTER' => 'F',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
					'order_filial_read',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_WRITE_OWN_FILIAL') => array(
				'LETTER' => 'P',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
					'order_filial_read',
					'order_filial_write',
					'order_filial_delete',
					'order_filial_add'
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_WRITE_CLIENTS_ORDERS') => array(
				'LETTER' => 'Q',
				'BINDING' => LM_AUTO_ACCESS_BINDING_ORDERS,
				'OPERATIONS' => array(
					'order_clients_read',
					'order_clients_write',
					'order_clients_delete',
					'order_clients_add'
				)
			),

			/*Статусы товаров*/
			GetMessage('LM_AUTO_MAIN_READ_ALL_STATUSES') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_STATUSES,
				'OPERATIONS' => array(
					'order_status_read',
				)
			),

			/*Импорт прайс-листов*/
			GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_IMPORT') => array(
				'LETTER' => 'X',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT,
				'OPERATIONS' => array(
					'price_import_read',
					'price_import_write',
					'price_import_delete',
					'price_import_add',
				)
			),
			GetMessage('LM_AUTO_MAIN_ACCESS_TO_IMPORT_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT,
				'OPERATIONS' => array(
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_TEMPLATES_ALLOWED_SUPPLIERS') => array(
				'LETTER' => 'W',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT,
				'OPERATIONS' => array(
					'price_import_read_p',
					'price_import_write_p',
					'price_import_delete_p',
				)
			),
			GetMessage('LM_AUTO_MAIN_USE_TEMPLATES_ALLOWED_SUPPLIERS') => array(
				'LETTER' => 'U',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT,
				'OPERATIONS' => array(
					'price_import_use_p',
					'price_import_use_p',
					'price_import_use_p',
				)
			),

			/*Словоформы*/
			GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_WORDFORMS') => array(
				'LETTER' => 'X',
				'BINDING' => LM_AUTO_ACCESS_BINDING_WORDFORMS,
				'OPERATIONS' => array(
					'word_forms_read',
					'word_forms_right',
					'word_forms_delete',
					'word_forms_add',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_WORDFORMS') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_WORDFORMS,
				'OPERATIONS' => array(
					'word_forms_read',
				)
			),
			GetMessage('LM_AUTO_MAIN_ACCES_TO_WORDFORMS_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_WORDFORMS,
				'OPERATIONS' => array(
				)
			),

			/*Список запчастей*/
			GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_SPARE') => array(
				'LETTER' => 'X',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS,
				'OPERATIONS' => array(
					'spare_read',
					'spare_right',
					'spare_delete',
					'spare_add',
				)
			),
			GetMessage('LM_AUTO_MAIN_ACCESS_TO_SPARE_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS,
				'OPERATIONS' => array(
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_SPARE') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS,
				'OPERATIONS' => array(
					'spare_read',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_SUPPLIERS_SPARE') => array(
				'LETTER' => 'U',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS,
				'OPERATIONS' => array(
					'spare_read_p',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_WRITE_SUPPLIERS_SPARE') => array(
				'LETTER' => 'W',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS,
				'OPERATIONS' => array(
					'spare_read_p',
					'spare_write_p',
				)
			),

			/*Статистика поиска*/
            /*
             * Полный доступ решили упразднить, т.к. он ничем не отличается от чтения
             */
//			GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_SEARCH_STAT') => array(
//				'LETTER' => 'X',
//				'BINDING' => LM_AUTO_ACCESS_BINDING_STATISTICS,
//				'OPERATIONS' => array(
//					'search_statistics_read',
//					'search_statistics_write',
//					'search_statistics_add',
//					'search_statistics_delete',
//
//				)
//			),
			GetMessage('LM_AUTO_MAIN_ACCESS_TO_SEARCH_STAT_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_STATISTICS,
				'OPERATIONS' => array(
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_SEARCH_STAT') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_STATISTICS,
				'OPERATIONS' => array(
					'search_statistics_read',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_MY_SEARCH_STAT') => array(
				'LETTER' => 'F',
				'BINDING' => LM_AUTO_ACCESS_BINDING_STATISTICS,
				'OPERATIONS' => array(
					'search_statistics_read_f',
				)
			),

			/*Пользовательские поля*/
			GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_USER_FIELDS') => array(
				'LETTER' => 'X',
				'BINDING' => LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS,
				'OPERATIONS' => array(
					'user_fields_read',
					'user_fields_right',
					'user_fields_delete',
					'user_fields_add',
				)
			),
			GetMessage('LM_AUTO_MAIN_ACCESS_TO_USER_FIELDS_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS,
				'OPERATIONS' => array(
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_USER_FIELDS') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS,
				'OPERATIONS' => array(
					'user_fields_read',
				)
			),

			/*Ценообразование*/
			GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_PRICES') => array(
				'LETTER' => 'X',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES,
				'OPERATIONS' => array(
					'price_ap_read',
					'price_ap_add',
					'price_ap_delete',
					'price_ap_write',

				)
			),
			GetMessage('LM_AUTO_MAIN_ACCESS_TO_PRICES_DENIED') => array(
				'LETTER' => 'D',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES,
				'OPERATIONS' => array(
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_ALL_PRICES') => array(
				'LETTER' => 'R',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES,
				'OPERATIONS' => array(
					'price_ap_read',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_DISCOUNTS_PRICES') => array(
				'LETTER' => 'F',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES,
				'OPERATIONS' => array(
					'price_ap_read_f',
				)
			),
			GetMessage('LM_AUTO_MAIN_READ_WRITE_DISCOUNTS_PRICES') => array(
				'LETTER' => 'P',
				'BINDING' => LM_AUTO_ACCESS_BINDING_PRICES,
				'OPERATIONS' => array(
					'price_ap_read_f',
					'price_ap_write_f',
				)
			),
		    
		    GetMessage('LM_AUTO_MAIN_ACCESS_TO_VIN_DENIED') => array(
		        'LETTER' => 'D',
		        'BINDING' => LM_AUTO_ACCESS_BINDING_VIN,
		        'OPERATIONS' => array(
		        )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_FULL_TO_VIN') => array(
		        'LETTER' => 'X',
		        'BINDING' => LM_AUTO_ACCESS_BINDING_VIN,
		        'OPERATIONS' => array(
		            'vin_read',
		            'vin_add',
		            'vin_delete',
		            'vin_write',
		        )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_OWN_CLIENTS') => array(
		        'LETTER' => 'C',
		        'BINDING' => LM_AUTO_ACCESS_BINDING_VIN,
		        'OPERATIONS' => array(
		            'vin_customers'
		        )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_FULL_ACCESS_TO_OWN_FILIALS') => array(
		        'LETTER' => 'B',
		        'BINDING' => LM_AUTO_ACCESS_BINDING_VIN,
		        'OPERATIONS' => array(
		            'vin_branches'
		        )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_ACCESS_TO_FINANCE_FULL') => array(
		    'LETTER' => 'X',
		    'BINDING' => LM_AUTO_ACCESS_BINDING_FINANCE,
		    'OPERATIONS' => array(
		    'finance_read',
		    'finance_add',
		    'finance_delete',
		    'finance_write',
		    )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_ACCESS_TO_FINANCE_DENIED') => array(
		    'LETTER' => 'D',
		    'BINDING' => LM_AUTO_ACCESS_BINDING_FINANCE,
		    'OPERATIONS' => array(
		    )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_ACCESS_TO_FINANCE_OWN_CLIENTS') => array(
		    'LETTER' => 'C',
		    'BINDING' => LM_AUTO_ACCESS_BINDING_FINANCE,
		    'OPERATIONS' => array(
		    'finance_customers'
		        )
		    ),
		    
		    GetMessage('LM_AUTO_MAIN_ACCESS_TO_FINANCE_OWN_BRANCH') => array(
		    'LETTER' => 'B',
		    'BINDING' => LM_AUTO_ACCESS_BINDING_FINANCE,
		    'OPERATIONS' => array(
		    'finance_branches'
		        )
		    ),
		    
		);
	}








    /**
     * Добавляем события
     *
     * @return bool
     */
    public function InstallEvents()
    {
        $event_manager = \Bitrix\Main\EventManager::getInstance();
		foreach (self::$lm_events as $event) {
			$event_manager->registerEventHandlerCompatible($event[0], $event[1], $event[2], $event[3], $event[4], $event[5]);
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
        $event_manager = \Bitrix\Main\EventManager::getInstance();
		foreach (self::$lm_events as $event) {
			$event_manager->unRegisterEventHandler($event[0], $event[1], $event[2], $event[3], $event[4], $event[5]);
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
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/components",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", $this->rewrite_module_files, true
        );

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/public/ru/files/this_site_support.php",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/this_site_support.php", false
        );

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/public/ru/files/cron_events.php",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/cron_events.php", true
        );

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/admin",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", $this->rewrite_module_files
        );


        /*
         * Административные иконки
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/themes/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true
        );

        /*
         * Установка папки для вывода supplierslist
         */
        if ($this->install_settings['suppliers_list']['install']) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/linemedia.auto/install/supplierslist/",
                $_SERVER["DOCUMENT_ROOT"] . $this->install_settings['suppliers_list']['path'], $this->install_settings['suppliers_list']['rewrite'], true
            );
        }


        /*
         * JS библиотеки.
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/linemedia.auto/install/themes/default/interface/js",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/linemedia.auto/", true, true
        );

        /*
         * Обработчики платежных систем.
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/payments/ru",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_payment",
            true,
            true
        );

        /*
         * Установка демо-папки
         */
        if ($this->install_settings['demo_folder']['install']) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/linemedia.auto/install/public/ru/demo-folder/",
                $_SERVER["DOCUMENT_ROOT"] . $this->install_settings['demo_folder']['path'],
                $this->install_settings['demo_folder']['rewrite'],
                true
            );

            /*
             * Заменим во всех файлах #DEMO_FOLDER# на реальный путь.
             */
            $demodir = '/'.trim($this->install_settings['demo_folder']['path'], '/').'/';
            $demodir = str_replace('//', '/', $demodir);
            LinemediaAutoFileHelper::fileStrReplace($_SERVER['DOCUMENT_ROOT'].$demodir, '#DEMO_FOLDER#', $demodir);
            LinemediaAutoFileHelper::fileStrReplace($_SERVER['DOCUMENT_ROOT'].WIZARD_SITE_DIR, '#DEMO_FOLDER#', WIZARD_SITE_DIR, false);
            /*
             * Сохраним настройки, зависящие от пути к демо-папке.
             */
            COption::SetOptionString($this->MODULE_ID, 'LM_AUTO_MAIN_DEMO_FOLDER', $demodir);
            //COption::SetOptionString($this->MODULE_ID, 'LM_AUTO_MAIN_PART_SEARCH_PAGE', $demodir.'search/#ARTICLE#/');

            CUrlRewriter::Add(array(
                "CONDITION" => '#^'.$demodir.'search/(.+?)/#',
                "PATH"      => $demodir.'search/index.php',
                "RULE"      => 'q=$1&',
                //"ID" => $arFields["ID"], // Component
            ));
            CUrlRewriter::Add(array(
                "CONDITION" => '#^'.$demodir.'search/(.+?)/(.+?)/#',
                "PATH"      => $demodir.'search/index.php',
                "RULE"      => 'q=$1&brand_title=$2&',
                //"ID" => $arFields["ID"], // Component
            ));
            CUrlRewriter::Add(array(
                "CONDITION" => '#^'.$demodir.'search/detail/(.+?)/(.+?)/#',
                "PATH"      => $demodir.'search/index.php',
                "RULE"      => 'part_id=$1&supplier_id=$2&',
                //"ID" => $arFields["ID"], // Component
            ));
            CUrlRewriter::Add(array(
                'CONDITION' => '#^'.$demodir.'tecdoc/#',
                'PATH'      => $demodir.'tecdoc/index.php',
                'RULE'      => '',
                'ID'        => $this->MODULE_ID.':tecdoc.catalog'
            ));
            CUrlRewriter::Add(array(
                'CONDITION' => '#^'.$demodir.'part-detail/([^\/]+?)/([^\/]+?)/#',
                'PATH'      => $demodir.'tecdoc/detail/index.php',
                'RULE'      => 'ARTICLE_ID=$1&ARTICLE_LINK_ID=$2',
                'ID'        => ''
            ));
        }


        /*
         * Папка модуля.
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/');

        /*
         * Папка для импорта прайсов
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/success/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/error/');

        /*
         * Папка для загрузки прайсов, ожидающих конвертации, а также для самой конвертации.
         */
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/pending/', 0700, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/price_groupsearch/', 0700, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting/', 0700, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting_error/', 0700, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/testing/', 0755, true);


        /*
         * Папка для загрузки изображений брендов.
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/images/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/images/upload/');
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/images/brands/');


        /*
         * Папка дял логов.
         */
        mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/logs/');

        /*
         * htaccess.
         */
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/.htaccess', "Deny from All\n<Directory \"images\">\nAllow from All\n</Directory>");

        /*
         * Копирование изображений брендов.
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/images/brands",
            $_SERVER["DOCUMENT_ROOT"]."/upload/linemedia.auto/images/brands/", true, true
        );

        /*
         * Копирование эталонного прайслиста.
         */
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/standard_pricelist.csv",
            $_SERVER["DOCUMENT_ROOT"]."/upload/linemedia.auto/pricelists/standard_pricelist.csv", true, true
        );

        /*
         *  Гаджеты
         */
        if(!file_exists($_SERVER["DOCUMENT_ROOT"], '/local/gadgets/')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/local/gadgets/', 0700, true);
        }

        CopyDirFiles( $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/gadgets/",
            $_SERVER["DOCUMENT_ROOT"]."/local/gadgets/", true, true
        );

        /*
         * Права на доступ к файлу со списком поставщиков.
         */
        $APPLICATION->SetFileAccessPermission('/bitrix/admin/linemedia.auto_search.php', array('*' => 'R'), true);
        $APPLICATION->SetFileAccessPermission('/bitrix/admin/linemedia.auto_supplierslist.php', array('*' => 'R'), true);

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
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
        );
        DeleteDirFilesEx("/bitrix/components/linemedia.auto/");
        DeleteDirFilesEx("/bitrix/modules/linemedia.auto/install/files/this_site_support.php");
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

        $success = CAgent::AddAgent(
            "LinemediaAutoImportAgent::run();",
            "linemedia.auto",
            "N",
            600
        );
        if (!$success) {
            return false;
        }

        $success = CAgent::AddAgent(
            "LinemediaAutoConverterAgent::run();",
            "linemedia.auto",
            "N",
            120
        );
        if (!$success) {
            return false;
        }

		$success = CAgent::AddAgent(
			"LinemediaAutoSearchStatistics::removeAgent()",
			"linemedia.auto",
			"N",
			86400
		);
		if (!$success) {
			return false;
		}

        return true;
    }


    /**
     * Удаляем агентов
     *
     * @return bool
     */
    public function UninstallAgents()
    {
        // http://dev.1c-bitrix.ru/api_help/main/reference/cagent/removemoduleagents.php
        CAgent::RemoveModuleAgents("linemedia.auto");
        return true;
    }


    /**
     * Создание свойств заказа.
     */
    public function InstallSaleProps()
    {
        if (!CModule::IncludeModule('sale')) {
            return false;
        }

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
                $property = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $person_id, 'PROPS_GROUP_ID' => $groups[$person_id], 'CODE' => $prop['CODE']), false, false, array('ID'))->Fetch();

                if ($property['ID'] <= 0) {
                    $prop['PERSON_TYPE_ID'] = $person_id;
                    $prop['PROPS_GROUP_ID'] = $groups[$person_id];
                    $code = CSaleOrderProps::Add($prop);
                    if ($code <= 0) {
                        return false;
                    }
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
        if (!CModule::IncludeModule('sale')) {
            return false;
        }

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


    /**
     * Добавление почтовых шаблонов.
     */
    public function InstallMessageTemplates()
    {
        /*
         * Установка типов почтовых событий.
         */
        include 'messages/ru/types.php';

        foreach ($arTypes as $arTypeLangs) {
            foreach ($arTypeLangs as $arType) {
                $type = new CEventType();
                $type->Add($arType);
            }
        }

        /*
         * Установка почтовых шаблонов.
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
     * Удаление почтовых шаблонов.
     */
    public function UninstallMessageTemplates()
    {
        /*
         * Удаление типов почтовых событий.
         */
        include 'messages/ru/types.php';
        foreach ($arTypes as $arTypeCode => $arTypeLangs) {
            CEventType::Delete($arTypeCode);
        }

        /*
         * Удаление почтовых шаблонов.
         */
        $templates = CEventMessage::GetList($b="id", $o="asc", array('TYPE_ID' => implode(' | ', array_keys($arTypes))));
        while ($template = $templates->Fetch()) {
            CEventMessage::Delete($template['ID']);
        }

        return true;
    }


    /**
     * Установка ТП.
     */
    public function InstallSupport()
    {
        if (!CModule::IncludeModule('support')) {
            return true;
        }

        /*
         * Установка почтовых шаблонов.
         */
        include 'support/ru/categories.php';

        $rsSites = CSite::GetList($b="sort", $o="asc", array());
        $arSites = array();
        while ($arSite = $rsSites->Fetch()) {
            $arSites []= $arSite['ID'];
        }
        foreach ($arCategories as $arCategory) {
            $arCategory['FIRST_SITE_ID'] = $arSites[0]['ID'];
            $arCategory['arrSITE'] = $arSites;

            $id = CTicketDictionary::Add($arCategory);
            COption::SetOptionInt($this->MODULE_ID, $arCategory['SID'], $id);
        }

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
     * Функция предустановки параметров.
     *
     * @param array $settings
     */
    public function setInstallSettings($settings)
    {
        $this->install_settings = (array) $settings;
    }


    /**
     * Права доступа к модулю.
     */
    public function GetModuleRightList()
    {
        $arr = array(
            'reference_id' => array(
                'D',
//                'I',
//                'O',
                'W'
            ),
            'reference' => array(
                '[D] '.GetMessage('LM_AUTO_MAIN_FORM_DENIED'),
//                '[I]'.GetMessage('LM_AUTO_MAIN_PRICELIST_IMPORT'),
//                '[O] '.GetMessage('LM_AUTO_MAIN_FORM_ORDER'),
                '[W] '.GetMessage('LM_AUTO_MAIN_FORM_WRITE')
            )
        );
        return $arr;
    }

    /**
     * Создание пользовательских свойств.
     */
    public function InstallUserFields()
    {
        global $APPLICATION;

        include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/install/users/ru/fields.php');

        $ex_fields = array();
        $res = CUserTypeEntity::GetList( array($by=>$order), array() );
        while($field = $res->Fetch())
        {
            $ex_fields[$field['FIELD_NAME']] = $field;
        }

        foreach ($arUserFields as $code => $arField) {

            if(array_key_exists($arField['FIELD_NAME'], $ex_fields)) {
                continue;
            }

            if ($arField['USER_TYPE_ID'] == 'iblock_element') {
                if (empty($arField['SETTINGS']['IBLOCK_ID']) && !empty($arField['SETTINGS']['IBLOCK_CODE'])) {
                    $arField['SETTINGS']['IBLOCK_ID'] = (int) COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_IBLOCK_' . strtoupper($arField['SETTINGS']['IBLOCK_CODE']));
                }
            }

            $field = new CUserTypeEntity();
            $id = $field->Add($arField);
            if ($id <= 0) {
                $APPLICATION->ThrowException('Error install user fields');
                return false;
            } else if($arField['FIELD_NAME'] == 'UF_CURRENCY' && CModule::IncludeModule('currency')) {

                $currency_list = array();
                $res = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
                $cnt = 0;
                while ($curr = $res->Fetch()) {

                    $currency_list['n' . $cnt] = array(
                        'XML_ID' => $curr['CURRENCY'],
                        'VALUE' => $curr['CURRENCY'],
                    );
                    $cnt++;
                }
                $obEnum = new CUserFieldEnum();
                $obEnum->SetEnumValues($id, $currency_list);
            }
        }

        return true;
    }


    /**
     * Удаление пользовательских свойств.
     */
    public function UnInstallUserFields()
    {
//        include 'users/ru/fields.php';
//
//        foreach ($arUserFields as $code => $arField) {
//            $field = new CUserTypeEntity();
//            $id = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_FIELD_'.$code);
//            $field->Delete($id);
//        }

        return true;
    }
}

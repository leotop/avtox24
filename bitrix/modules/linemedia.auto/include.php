<?php

/**
 * Linemedia Autoportal
 * Main module
 * Main include file
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

include_once('install/version.php');
include_once('constants.php');
include_once('functions.php');

global $DBType;

$classes = array(
    /*
     * Интерфейсы
     */
    'LinemediaAutoISearch'                  => "classes/general/interfaces/search.php",

    /*
     * Ключевые классы, производщие поиск
     */
    'LinemediaAutoApiDriver'                => "classes/general/api_driver.php",
    'LinemediaAutoApiDriver2'                => "classes/general/api_driver2.php",
    'LinemediaAutoApiModifications'			=> "classes/general/api_modifications.php",
    'LinemediaAutoSearch'                   => "classes/general/search.php",
    'LinemediaAutoSearchSimple'             => "classes/$DBType/search_simple.php",
    'LinemediaAutoSearchGroup'              => "classes/$DBType/search_group.php",
    'LinemediaAutoSearchPartial'            => "classes/$DBType/search_partial.php",
    'LinemediaAutoSearchPartialAll'         => "classes/$DBType/search_partial_all.php",
    'LinemediaAutoSearchByParams'           => "classes/$DBType/search_by_params.php",
    'LinemediaAutoCrossesApiDriver'         => "classes/general/api_crosses_driver.php",

    /*
     * Класс статистики поиска
     */
    'LinemediaAutoSearchStatistics' => "classes/general/searchstatistics.php",

    /*
     * Вспомогательные - служебные классы
     */
    'LinemediaAutoXML2Arr'                  => "classes/general/xml.php",
    'LinemediaAutoArr2XML'                  => "classes/general/xml.php",
    'LinemediaAutoI18N'                     => "classes/general/i18n.php",
    'LinemediaAutoDebug'                    => "classes/general/debug.php",
    'LinemediaAutoTecDocRights'             => "classes/general/tecdoc_rights.php",
    'LinemediaAutoLogger'                   => "classes/general/logger.php",
    'LinemediaAutoExcel'                    => "classes/general/excel.php",
    'LinemediaAutoSimpleCache'              => "classes/general/cache.php",

    'LinemediaAutoProtocol'                 => "classes/general/protocol.php",
    'LinemediaAutoBrowser'                  => "classes/general/browser.php",
    'LinemediaAutoConverterAgent'           => "classes/general/converter_agent.php",
    'LinemediaAutoTasker'                   => "classes/general/tasker.php",
    'LinemediaAutoTask'                     => "classes/general/task.php",
    'LinemediaAutoTaskShedule'              => "classes/general/task_shedule.php",
    'LinemediaAutoImportHistory'            => "classes/general/import_history.php",
    'LinemediaAutoAttachToMail'             => "classes/general/attach_letter_to_mail.php",
    'LinemediaAutoSearchModificator'        => 'classes/general/search_modificator.php',
    'LinemediaAutoAbstractStatus'           => 'classes/general/trans_statuses.php',
    'LinemediaAutoApprovedByDirector'       => 'classes/general/trans_statuses.php',
    'LinemediaAutoRefusedBySupplier'        => 'classes/general/trans_statuses.php',
    'LinemediaAutoRefusedInShipment'        => 'classes/general/trans_statuses.php',
    'LinemediaAutoMoneyBackApproved'        => 'classes/general/trans_statuses.php',
    'LinemediaAutoOrderDone'                => 'classes/general/trans_statuses.php',
    'LinemediaAutoCabinetAdminDepositUserAccount' => 'classes/general/deposit_funds.php',
    'qqUploadedFileForm'    => "classes/general/uploader.php",
    'qqUploadedFileXhr'     => "classes/general/uploader.php",
    'qqFileUploader'        => "classes/general/uploader.php",
    'CapableToModifyingSearchInterface' => 'classes/general/interfaces/CapableToModifyingSearchInterface/CapableToModifyingSearchInterface.php',
    'UpModificator' => 'classes/general/modificators/UpModificator.php',
    'SortModificator' => 'classes/general/modificators/SortModificator.php',
    'TrimModificator' => 'classes/general/modificators/TrimModificator.php',
    'ConcealModificator' => 'classes/general/modificators/ConcealModificator.php',
    'VaryFieldModificator' => 'classes/general/modificators/VaryFieldModificator.php',
    'LinemediaAutoTransaction' => 'classes/general/transaction/transaction.php',

    /*
     * База данных
     */
    'LinemediaAutoDatabaseAll'              => "classes/general/database.php",
    'LinemediaAutoDatabase'                 => "classes/$DBType/database.php",

    /*
     * Таблицы
     */
    'LinemediaProductsTable'                => "classes/$DBType/LinemediaProductsTable.php",

    /*
     * Импортёр
     */
    'LinemediaAutoImportAgent'              => "classes/general/import_agent.php",

    /*
     * Замены стандартных классов Bitrix
     */
    'LinemediaAutoBasket'                   => "classes/general/basket.php",
    'LinemediaAutoBrand'                    => "classes/general/brand.php",
    'LinemediaAutoOrder'                    => "classes/general/order.php",
    'LinemediaAutoPartAll'                  => "classes/general/part.php",
    'LinemediaAutoPart'                     => "classes/$DBType/part.php",
    'LinemediaAutoCustomFieldsAll'          => "classes/general/custom_fields.php",
    'LinemediaAutoCustomFields'             => "classes/$DBType/custom_fields.php",
    'LinemediaAutoGroupTransfer'            => "classes/general/group_transfer.php",
    'LinemediaAutoPrice'                    => "classes/general/price.php",
    'LinemediaAutoSupplier'                 => "classes/general/supplier.php",
    'LinemediaAutoModule'                   => "classes/general/module.php",
    'LinemediaAutoBasketProperty'			=> "classes/general/basket_property.php",

    /*
     * Хелперы
     */
    'LinemediaAutoUrlHelper'                => "classes/general/url_helper.php",
    'LinemediaAutoPartsHelper'              => "classes/general/parts_helper.php",
    'LinemediaAutoUserHelper'				=> 'classes/general/user_helper.php', // вспомогательный класс для работы с пользователями
    'LinemediaAutoFileHelper'               => "classes/general/file_helper.php",


    'LinemediaAutoDirections'               => "classes/general/directions.php",
    'LinemediaAutoUser'                     => "classes/general/user.php",

    'LinemediaAutoReturnGoods'              => "classes/general/return_goods.php",

    'LinemediaAutoOrderDocuments'           => "classes/general/order_documents.php",
    'LinemediaAutoStatus'					=> "classes/general/status.php",


    /*
     * Новые свойства инфоблоков
     */
    'LinemediaAutoIblockPropertyUserGroup'  => "classes/general/iblock_prop_usergroup.php",
    'LinemediaAutoIblockPropertyCurrency'   => "classes/general/iblock_prop_currency.php",
    'LinemediaAutoIBlockPropertyCheckbox'	=> "classes/general/iblock_prop_checkbox.php",
    'LinemediaAutoIblockPropertyPriceField' => "classes/general/iblock_prop_pricefield.php",

    /*
     * Новые второстепенные сущности
     */
    'LinemediaAutoWordForm'     			=> "classes/general/wordform.php",
    'LinemediaAutoCustomDiscount'           => "classes/general/custom_discount.php",
    'LinemediaAutoBasketFilter'             => "classes/general/basket_filter.php",
    'LinemediaAutoCSVChecker'               => "classes/general/csv_checker.php",
    'LinemediaAutoAttach'                   => "classes/general/attach.php",
    'LinemediaAutoRights'                   => "classes/general/rights.php",
    'LinemediaAutoNotepad'                 	=> "classes/general/notepad.php",// Класс для работы с блокнотом
    'LinemediaAutoBrands'                   => "classes/$DBType/brands.php",
    'CustomizedSortSearch'                  => 'classes/general/modificator_stradegy.php',
    'CustomizedTruncateSearch'              => 'classes/general/modificator_stradegy.php',
    'CustomizedConcealSearch'               => 'classes/general/modificator_stradegy.php',
    'CustomizedAscendingSearch'             => 'classes/general/modificator_stradegy.php',
    'AbstractModificator'                   => 'classes/general/modificator_stradegy.php',
    'Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase'   =>  'classes/general/amount_stock.php',

    /*
     * События модулей
     */
    'LinemediaAutoEventMain'                => "events/main.php", // Класс событий главного модуля.
    'LinemediaAutoEventSale'                => "events/sale.php", // Класс событий магазина.
    'LinemediaAutoEventIBlock'              => "events/iblock.php", // Класс событий инфоблоков.
    'LinemediaAutoEventSelf'                => "events/self.php", // Класс событий этого модуля.
    'LinemediaEventApi'                  	=> "events/linemedia.api.php", // Класс событий модуля API.
    'LinemediaAutoEventProductStatus'       => "events/prod_status.php", // Класс событий модуля API.
    'LinemediaAutoEventCurrency'            => "events/currency.php", // Класс событий модуля API.


    /*
    * Административные интерфейсы
    */
    'CLMAdminResult'						=> 'classes/general/admin_lib.php',



    /*Доступы к элементам linemedia*/
    'LinemediaAutoRightsEntity'				=> 'classes/general/rights_entity.php',

    /*Статусы товаров*/
    'LinemediaAutoProductStatus'			=> 'classes/general/product_status.php',

    /*Статистика*/
    'LinemediaAutoStat'						=> 'classes/general/stat.php',

    /*Мониторинг*/
    'LinemediaAutoMonitoring'               => 'classes/general/monitoring.php',

    /* Аккаунт пользователя  */
    'LinemediaAutoUserAccount'                => 'classes/general/account.php',

    /* Фильтр представления заказов */
    'LinemediaAutoOrdersViewFilter'         => 'classes/general/orders_view_filter.php',
);

/*Работа с группами пользователей*/
if(CheckVersion(SM_VERSION, "16.0.1")) {
    $classes['LinemediaAutoGroup'] = 'classes/general/user_static.php';
} else {
    $classes['LinemediaAutoGroup'] = 'classes/general/user.php';
}




CModule::AddAutoloadClasses(
    "linemedia.auto",
    $classes
);

/*
 * Некоторые события надо запускать при каждом хите (в основном администратора)
 * Чтобы показать необходимые уведомления
 */
LinemediaAutoEventMain::OnAdminInformerInsertItems_addUpdatesCheck();
LinemediaAutoEventMain::OnAdminInformerInsertItems_addLinemediaAccountCheck();


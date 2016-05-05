<?php

/**
 * Linemedia Autoportal
 * Branches module
 * Module settings
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/*
 * Module settings are available for administrator only
 */
if (!$USER->IsAdmin()) {
    return;
}

/**
 * Идентификатор модуля
 */
$sModuleId  = 'linemedia.autoanalogssimple';
 
/**
 * Подключаем модуль (выполняем код в файле include.php)
 */
CModule::IncludeModule($sModuleId);
 
/**
 * Языковые константы (файл lang/ru/options.php)
 */
global $MESS;
IncludeModuleLangFile( __FILE__ );
 
 




if ($REQUEST_METHOD == 'POST' && $_POST['Update'] == 'Y') {
    
    /*
    * событие для других модулей
    */
    $events = GetModuleEvents("linemedia.autoanalogssimple", "OnBeforeOptionsSave");
    while ($arEvent = $events->Fetch())
    {
        try {
            ExecuteModuleEventEx($arEvent, array(&$_POST));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    /*
     * Если форма была сохранена, устанавливаем значение опций модуля.
     */
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoanalogssimple/options/common-save.php');
        
    /*
     * событие для других модулей
     */
    $events = GetModuleEvents("linemedia.autoanalogssimple", "OnAfterOptionsSave");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array($_POST));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
}




 
/*
 * Описываем табы административной панели битрикса.
 */
$aTabs = array(
    array(
        'DIV'   => 'common',
        'TAB'   => GetMessage('LM_AUTO_GARAGE_COMMON_TAB_SET'),
        'ICON'  => 'common',
        'TITLE' => GetMessage('LM_AUTO_GARAGE_COMMON_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'rights',
        'TAB'   => GetMessage('LM_AUTO_GARAGE_RIGHTS_TAB_SET'),
        'ICON'  => 'rights',
        'TITLE' => GetMessage('LM_AUTO_GARAGE_RIGHTS_TAB_TITLE_SET')
    ),
);



/*
 * Событие для других модулей
 */
$events = GetModuleEvents("linemedia.autoanalogssimple", "OnOptionsTabsAdd");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$aTabs));
    } catch (Exception $e) {
        throw $e;
    }
}


/**
 * Инициализируем табы
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);
$oTabControl->Begin();


/**
 * Ниже пошла форма страницы с настройками модуля
 */
?>

<form method="POST" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialchars($sModuleId) ?>&lang=<?= LANG ?>&mid_menu=1">
<?
    echo bitrix_sessid_post();
    
    
    /* COMMON SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoanalogssimple/options/common.php');
    $oTabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
    $oTabControl->EndTab();
    
    
    /*
     * Событие для других модулей
     */
    $events = GetModuleEvents("linemedia.autoanalogssimple", "OnOptionsTabsShow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$oTabControl));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    
    $oTabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?= GetMessage('LM_AUTO_GARAGE_BUTTON_SAVE') ?>" />
    <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_GARAGE_BUTTON_RESET') ?>" />
    <input type="hidden" name="Update" value="Y" />
    <? $oTabControl->End(); ?>
</form>

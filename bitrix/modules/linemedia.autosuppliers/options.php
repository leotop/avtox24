<?php

/**
 * Linemedia Autoportal
 * Suppliers module
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
 * ������������� ������
 */
$sModuleId  = 'linemedia.autosuppliers';
// /bitrix/modules/main/admin/group_rights.php
global $module_id;
$module_id = 'linemedia.autosuppliers';
 
/**
 * ���������� ������
 */
CModule::IncludeModule($sModuleId);
CModule::IncludeModule('sale');
 
/**
 * �������� ��������� (���� lang/ru/options.php)
 */
global $MESS;
IncludeModuleLangFile( __FILE__ );
 
 




if ($REQUEST_METHOD == 'POST' && $_POST['Update'] == 'Y') {
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents($sModuleId, "OnBeforeOptionsSave");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$_POST));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    /*
     * ���� ����� ���� ���������, ������������� �������� ����� ������.
     */
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $sModuleId . '/options/orders-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $sModuleId . '/options/steps-save.php');
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents($sModuleId, "OnAfterOptionsSave");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array($_POST));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
}




 
/*
 * ��������� ���� ���������������� ������ ��������.
 */
$aTabs = array(
    array(
        'DIV'   => 'orders',
        'TAB'   => GetMessage('LM_AUTO_SUPPLIERS_ORDERS_TAB_SET'),
        'ICON'  => 'api_settings',
        'TITLE' => GetMessage('LM_AUTO_SUPPLIERS_ORDERS_TAB_SET')
    ),
    array(
        'DIV'   => 'steps',
        'TAB'   => GetMessage('LM_AUTO_SUPPLIERS_STEPS_TAB_SET'),
        'ICON'  => 'api_settings',
        'TITLE' => GetMessage('LM_AUTO_SUPPLIERS_STEPS_TAB_SET')
    ),
    array(
        'DIV'   => 'rights',
        'TAB'   => GetMessage('LM_AUTO_SUPPLIERS_RIGHTS_TAB_SET'),
        'ICON'  => 'api_settings',
        'TITLE' => GetMessage('LM_AUTO_SUPPLIERS_RIGHTS_TAB_SET')
    ),
);



/*
 * ������� ��� ������ �������
 */
$events = GetModuleEvents($sModuleId, "OnOptionsTabsAdd");
while ($arEvent = $events->Fetch()) {
    try {
	    ExecuteModuleEventEx($arEvent, array(&$aTabs));
	} catch (Exception $e) {
	    throw $e;
	}
}


/**
 * �������������� ����
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);
$oTabControl->Begin();


/**
 * ���� ����� ����� �������� � ����������� ������
 */
?>

<form method="POST" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialchars($sModuleId) ?>&lang=<?= LANG ?>&mid_menu=1">
<?
    echo bitrix_sessid_post();
    
    
    /* ORDERS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $sModuleId . '/options/orders.php');
    $oTabControl->EndTab();
    
    /* STEPS */
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $sModuleId . '/options/steps.php');
    $oTabControl->EndTab();

    /* RIGHTS */
    $oTabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
    $oTabControl->EndTab();
    
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents($sModuleId, "OnOptionsTabsShow");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$oTabControl));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    
    $oTabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?= GetMessage('LM_AUTO_SUPPLIERS_BUTTON_SAVE') ?>" />
    <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_SUPPLIERS_BUTTON_RESET') ?>" />
    <input type="hidden" name="Update" value="Y" />
    <? $oTabControl->End(); ?>
</form>

<?php

/**
 * Linemedia Autoportal
 * Sphinx module
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
$sModuleId  = 'linemedia.autoremotesuppliers';
 
/**
 * ���������� ������ (��������� ��� � ����� include.php)
 */
CModule::IncludeModule($sModuleId);
 
/**
 * �������� ��������� (���� lang/ru/options.php)
 */
global $MESS;
IncludeModuleLangFile( __FILE__ );
 
 




if ($REQUEST_METHOD == 'POST' && $_POST['Update'] == 'Y') {
    
    /*
    * ������� ��� ������ �������
    */
    $events = GetModuleEvents("linemedia.autoremotesuppliers", "OnBeforeOptionsSave");
	while ($arEvent = $events->Fetch())
	{
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$_POST));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    /*
     * ���� ����� ���� ���������, ������������� �������� ����� ������.
     */
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/options/main-save.php');
    
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents("linemedia.autoremotesuppliers", "OnAfterOptionsSave");
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
        'DIV'   => 'main',
        'TAB'   => GetMessage('LM_AUTO_REMOTE_SUPPLIERS_MAIN_TAB_SET'),
        'ICON'  => 'main_settings',
        'TITLE' => GetMessage('LM_AUTO_REMOTE_SUPPLIERS_MAIN_TAB_TITLE_SET')
    ),
);



/*
 * ������� ��� ������ �������
 */
$events = GetModuleEvents("linemedia.autoremotesuppliers", "OnOptionsTabsAdd");
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
    
    
    /* sphinx */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/options/main.php');
    $oTabControl->EndTab();
    
    
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents("linemedia.autoremotesuppliers", "OnOptionsTabsShow");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$oTabControl));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    
    $oTabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?= GetMessage('LM_AUTO_REMOTE_SUPPLIERS_BUTTON_SAVE') ?>" />
    <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_REMOTE_SUPPLIERS_BUTTON_RESET') ?>" />
    <input type="hidden" name="Update" value="Y" />
    <? $oTabControl->End(); ?>
</form>

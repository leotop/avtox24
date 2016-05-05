<?php

/**
 * Linemedia Autoportal
 * Main module
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
$sModuleId  = 'linemedia.autotecdoc';
 
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
    $events = GetModuleEvents("linemedia.autotecdoc", "OnBeforeOptionsSave");
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
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autotecdoc/options/api-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autotecdoc/options/common-settings-save.php');
    
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents("linemedia.autotecdoc", "OnAfterOptionsSave");
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
        'DIV'   => 'api',
        'TAB'   => GetMessage('LM_AUTO_TECDOC_API_TAB_SET'),
        'ICON'  => 'api_settings',
        'TITLE' => GetMessage('LM_AUTO_TECDOC_API_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'common-settings',
        'TAB'   => GetMessage('LM_AUTO_TECDOC_COMMON_TAB_SET'),
        'ICON'  => 'common-settings_settings',
        'TITLE' => GetMessage('LM_AUTO_TECDOC_COMMON_TAB_TITLE_SET')
    ),
);



/*
 * ������� ��� ������ �������
 */
$events = GetModuleEvents("linemedia.autotecdoc", "OnOptionsTabsAdd");
while ($arEvent = $events->Fetch()) {
    try {
	    ExecuteModuleEventEx($arEvent, array(&$aTabs));
	} catch (Exception $e) {
	    throw $e;
	}
}


/*
 * �������������� ����
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);
$oTabControl->Begin();


/*
 * ���� ����� ����� �������� � ����������� ������
 */
?>
<? $APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js') ?>
<? $APPLICATION->AddHeadScript('/bitrix/modules/linemedia.autotecdoc/interface/options/script.js') ?>
<? $APPLICATION->SetAdditionalCSS('/bitrix/modules/linemedia.autotecdoc/interface/options/style.css') ?>

<form method="POST" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialchars($sModuleId) ?>&lang=<?= LANG ?>&mid_menu=1">
<?
    echo bitrix_sessid_post();
    
    
    /* API */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autotecdoc/options/api.php');
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autotecdoc/options/common-settings.php');
    $oTabControl->EndTab();
    
    /*
     * ������� ��� ������ �������
     */
    $events = GetModuleEvents("linemedia.autotecdoc", "OnOptionsTabsShow");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$oTabControl));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    $oTabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?= GetMessage('LM_AUTO_TECDOC_BUTTON_SAVE') ?>" />
    <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_TECDOC_BUTTON_RESET') ?>" />
    <input type="hidden" name="Update" value="Y" />
    <? $oTabControl->End(); ?>
</form>

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
 * ������������� ������
 */
$sModuleId  = 'linemedia.autoanalogssimple';
 
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
     * ���� ����� ���� ���������, ������������� �������� ����� ������.
     */
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoanalogssimple/options/common-save.php');
        
    /*
     * ������� ��� ������ �������
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
 * ��������� ���� ���������������� ������ ��������.
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
 * ������� ��� ������ �������
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
    
    
    /* COMMON SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoanalogssimple/options/common.php');
    $oTabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
    $oTabControl->EndTab();
    
    
    /*
     * ������� ��� ������ �������
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

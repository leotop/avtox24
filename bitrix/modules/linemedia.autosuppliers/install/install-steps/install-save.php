<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);


// ��������� ������ �� ���������.
if (!$this->presetOption()) {
    ShowError(GetMessage('LM_AUTO_SUPPLIERS_ERROR_INSTALL_OPTIONS'));
}

// ��������� ���� ������.
if (!$this->InstallDB()) {
    ShowError(GetMessage('LM_AUTO_SUPPLIERS_ERROR_INSTALL_DB'));
}

// ��������� �������.
if (!$this->InstallEvents()) {
    ShowError(GetMessage('LM_AUTO_SUPPLIERS_ERROR_INSTALL_EVENTS'));
}

// ��������� ������.
if (!$this->InstallFiles()) {
    ShowError(GetMessage('LM_AUTO_SUPPLIERS_ERROR_INSTALL_FILES'));
}



mkdir($_SERVER['DOCUMENT_ROOT'] . "/upload/linemedia.autosuppliers/requests/", 0700, true);


/*
 * ��������� �������
 */
global $DB, $DBType;
$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.autosuppliers/install/db/".$DBType."/structure.sql");
if (is_array($errors) && count($errors) > 0) {
    foreach ($errors as $error) {
        echo $error;
    }
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CREATING_PARTS_DATABASE'));
    exit;
}



/*
* ��������� ��������
*/
//if($_POST['install-statuses'] == 'Y') {
    CModule::IncludeModule('sale');
    
    
    $ok = CSaleStatus::Add(array(
        'ID' => 'R',
        'LANG' => array(
            array(
                'LID' => LANG,
                'NAME' => GetMessage('LM_AUTO_SUPPLIERS_REQUESTED_STATUS'),
                'DESCRIPTION' => GetMessage('LM_AUTO_SUPPLIERS_REQUESTED_STATUS_DESCRIPTION')
            ),
            array(
                'LID' => 'en',
                'NAME' => 'requested',
                'DESCRIPTION' => 'requested from supplier '
            )
        ),
        'PERMS' => array()
    ));
    
    if($ok != 'R')
        $GLOBALS["APPLICATION"]->ThrowException('Error install requested status');
    
    $ok = CSaleStatus::Add(array(
        'ID' => 'A',
        'LANG' => array(
            array(
                'LID' => LANG,
                'NAME' => GetMessage('LM_AUTO_SUPPLIERS_APPROVED_STATUS'),
                'DESCRIPTION' => GetMessage('LM_AUTO_SUPPLIERS_APPROVED_STATUS_DESCRIPTION')
            ),
            array(
                'LID' => 'en',
                'NAME' => 'supplier approved',
                'DESCRIPTION' => 'supplier approved'
            )
        ),
        'PERMS' => array()
    ));
    
    if($ok != 'A')
        $GLOBALS["APPLICATION"]->ThrowException('Error install approved status');
    
    $ok = CSaleStatus::Add(array(
        'ID' => 'S',
        'LANG' => array(
            array(
                'LID' => LANG,
                'NAME' => GetMessage('LM_AUTO_SUPPLIERS_STOCK_STATUS'),
                'DESCRIPTION' => GetMessage('LM_AUTO_SUPPLIERS_STOCK_STATUS_DESCRIPTION')
            ),
            array(
                'LID' => 'en',
                'NAME' => 'in stock',
                'DESCRIPTION' => 'in stock'
            )
        ),
        'PERMS' => array()
    ));
    
    if($ok != 'S')
        $GLOBALS["APPLICATION"]->ThrowException('Error install stock status');
        
    /*
    * �������� � ��������� ������ ����� �������
    */
    COption::SetOptionString("linemedia.autosuppliers", 'REQUESTED_GOODS_STATUS', 'R');
    COption::SetOptionString("linemedia.autosuppliers", 'APPROVED_GOODS_STATUS', 'A');
    COption::SetOptionString("linemedia.autosuppliers", 'STOCK_GOODS_STATUS'   , 'S');
//}



/* 
 * �������������� ��������� ������.
 * 
 * ����������� ���������� �� ��������� �����, �.�. ����� ��� �������� ������� ������.
 * ��� ���� ������� �� ���������� �������� ���� ���������� ����� ����������� ����� �����.
 */
RegisterModule('linemedia.autosuppliers');

// ���������� ������ ����� ������ ���� ������ ��� ���������� (!)
if (!$this->InstallAgents()) {
    ShowError(GetMessage('LM_AUTO_SUPPLIERS_ERROR_INSTALL_AGENTS'));
}

header('Refresh: 1; URL=/bitrix/admin/settings.php?lang=' . LANG . '&mid=linemedia.autosuppliers&mid_menu=1');


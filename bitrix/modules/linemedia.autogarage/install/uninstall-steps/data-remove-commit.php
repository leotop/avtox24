<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/*
 * Удаление поставщика
 */
if ($_POST['REMOVE_GARAGE'] == 'Y') {
    CModule::IncludeModule('iblock');
    $iblock_id = COption::GetOptionInt("linemedia.autogarage", "LM_AUTO_IBLOCK_GARAGE");
    
    $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $iblock_id));
    if ($el = $res->Fetch()) {
        CIBlockElement::Delete($el['ID']);
    }
}


if (!$this->UnInstallEvents() || !$this->UnInstallFiles() || !$this->UninstallSaleProps()) {
    return;
}

UnRegisterModule($this->MODULE_ID);

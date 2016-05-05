<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError('LM_AUTO_MODULE_NOT_INSTALLED');
    return;
}
if (!CModule::IncludeModule("linemedia.autogarage")) {
    ShowError('LM_AUTO_GARAGE_MODULE_NOT_INSTALLED');
    return;
}

$right = LinemediaAutoRights::getUserRight(CUser::getID(), 'linemedia.autogarage');

$modulePermissions = $APPLICATION->GetGroupRight("linemedia.autogarage");
if ($right == 'D' && $modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(GetMessage("LM_AUTO_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>

<?= GetMessage('LM_AUTO_GARAGE_ADMIN_HELP') ?>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>
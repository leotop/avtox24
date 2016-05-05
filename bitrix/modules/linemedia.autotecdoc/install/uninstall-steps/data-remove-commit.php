<?php
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * data-remove-commit
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */

/*
 * Удаление инфоблоков
 */
if ($_POST['REMOVE_IBLOCKS'] == 'Y') {
    global $DB;
    CModule::IncludeModule('iblock');
    
    $DB->StartTransaction();
    if (!CIBlockType::Delete('linemedia_autotecdoc')) {
        $DB->Rollback();
        ShowError('Error removing iblocks');
    }
    $DB->Commit();
}

global $DBType, $DB;
$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.autotecdoc/install/db/".$DBType."/mod-uninstall.sql");
if (is_array($errors) && count($errors) > 0) {
	echo  GetMessage('LM_AUTO_TECDOC_ERROR_REMOVING_DB');
	foreach ($errors as $error) {
		ShowError($error);
	}
}


if (!$this->UnInstallDB() || !$this->UnInstallEvents() || !$this->UnInstallFiles()) {
    return;
}
UnRegisterModule($this->MODULE_ID);

<?php 
/**
 * Linemedia Autoportal
 * Analogs simple module
 * db create
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/*
 * ����� �� ���������
 */
$this->presetOption();


/*
 * ������������ �������
 */
foreach ($this->lm_events as $event) {
    RegisterModuleDependences($event[0], $event[1], $event[2], $event[3], $event[4]);
}


/*
 * �������� ����� ���������������� �����
 */
CopyDirFiles(
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autoanalogssimple/install/admin", 
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", $this->rewrite_module_files
);


/*
 * ���������������� ������
 */
CopyDirFiles(
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autoanalogssimple/install/themes/", 
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true
);



/*
* ����� ��� ������� �������
*/
mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autoanalogssimple/');


/*
 * �������
 */
// $database = new LinemediaAutoDatabase();
global $DBType, $DB;
$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.autoanalogssimple/install/db/".$DBType."/analogs-simple-structure.sql");
if (is_array($errors) && count($errors) > 0) {
    $GLOBALS['LM_AUTO_SA_SIMPLE'] = $errors;
    $APPLICATION->IncludeAdminFile(GetMessage("LM_AUTO_AS_INSTALL_STEP_ANALOGS_DB_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/linemedia.autoanalogssimple/install/install-steps/analogs-db.php");
    return false;
}
// $database->Disconnect();


RegisterModule('linemedia.autoanalogssimple');

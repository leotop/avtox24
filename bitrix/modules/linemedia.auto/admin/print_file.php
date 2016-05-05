<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// проверка прав доступа
global $USER, $APPLICATION;

if(!$USER->IsAuthorized()) die('Not authorized');

$sModuleId = "linemedia.auto";
// получаем роль текущего пользователя
$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_ORDERS);
$curUserGroup = $USER->GetUserGroupArray();  //массив групп пользователя
$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter); //максимальная роль пользователя
if($maxRole == "D") die('Access denied');


$folder = $_REQUEST['folder'];
$file = $_REQUEST['file'];

$path_parts = pathinfo($file);

$file_path = $_SERVER['DOCUMENT_ROOT'] . LinemediaAutoOrderDocuments::$LM_AUTO_UPLOAD_DOC_FOLDER . LinemediaAutoOrderDocuments::safeFolderName($folder) . '/' . $path_parts['basename'];

if(file_exists($file_path)) {

    $ext = strtolower($path_parts['extension']);

    switch($ext) {
        case 'odt' : {
            header('Content-Type: application/vnd.oasis.opendocument.text');
        } break;
        case 'ods' : {
            header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
        } break;
        case 'doc' : {
            header('Content-Type: application/msword');
        } break;
        case 'docx' : {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        } break;
        case 'xls' : {
            header('Content-Type: application/vnd.ms-excel');
        } break;
        case 'xlsx' : {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } break;
        case 'pdf' : {
            header('Content-Type: application/pdf');
        }
        default : {
            header('Content-Type: application/octet-stream');
        }
    }

    header('Content-Disposition: inline; filename="' . $file . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    ob_clean();
    flush();
    readfile($file_path);
    exit;
} else {
    die('file not found');
}
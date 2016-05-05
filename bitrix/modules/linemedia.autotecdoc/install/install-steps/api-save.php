<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Main module
 * ajax api registration
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

define('AJAX', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

IncludeModuleLangFile(__FILE__);

/*
 * Сессия неправильная
 */
if (!check_bitrix_sessid()) {
    die('Incorrect session');
}

/*
 * Подключить модуль нельзя, потому что он ещё не установлен
 * Надо добавить классы в autoloading руками
 */
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autotecdoc/include.php');

/*
 * Что отправлять для регистрации?
 */
$version = array(
    'sitename' => (string) $_REQUEST['send_sitename'],
);


/*
 * Запрос на регистрацию в API
 */
$api = new LinemediaAutoTecDocApiDriver();
$response = $api->query('requestNewAccount', $version);
if ($response['status'] == 'error') {
    die (GetMessage('LM_AUTO_TECDOC_API_REGISTER_ERROR') . ': ' . $response['error']['error_text']);
} else {
    $id = (int) $response['data']['id'];
    $secret = (string) $response['data']['secret'];
    
    COption::SetOptionInt("linemedia.autotecdoc", "LM_AUTO_TECDOC_API_ID", $id);
    COption::SetOptionString("linemedia.autotecdoc", "LM_AUTO_TECDOC_API_KEY", $secret);
}

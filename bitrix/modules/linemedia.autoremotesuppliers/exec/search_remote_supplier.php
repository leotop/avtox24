<?php
/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Supplier cli script
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/**
 * ������ ����������� �������:
 *
 * 01. ����������� ������ ��������, ������� �� ������� ��������
 *
 * 1. ��������� ������� ����������
 * 2. �������� ���������� ������
 * 3. ���������� �������
 * 4. ��������� ����������
 *
 * ��������� �������� ������ LinemediaAutoRemoteSuppliersSupplier
 *
 * public static function load($code) - ��������� �� ��������� �������� ��������� ����������\
 * public static function loadByID($id)
 * ������ ��� ����� ������������ public static function loadByApi($arApi)
 */
/********************************************************************************************************/

define('LM_AUTO_DEBUG_NOTICE', 1);
define('LM_AUTO_DEBUG_WARNING', 10);
define('LM_AUTO_DEBUG_USER_ERROR', 15);
define('LM_AUTO_DEBUG_ERROR', 20);
define('LM_AUTO_DEBUG_CRITICAL', 30);

define('LANGUAGE_ID', 'ru');

/********************************* �������� ��� ������������ ����� ***************************************/

error_reporting(E_ERROR || E_PARSE || E_COMPILE_ERROR);

include_once('bitrix_lang_function.php');
include_once('bitrix_other_functions.php');


if ($argc < 5) {
    die('Commandline execution only');
}

$time_start = microtime(true);

//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_STRICT);

/*
 * ����� set_time_limit �� �������� � CLI
 * ������ ����� ����� ������� CMD �������� timeout 30
 */
//set_time_limit(30);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/classes/general/parts_helper.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/classes/general/debug.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/browser.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/cache.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/phpQuery.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/remote_supplier.php');

/*
 * ������� ���������
 */
$api 			= trim($argv[1]);
$supplier_id	= trim($argv[2]);
$query 			= trim($argv[3]);
$brand_title 	= trim($argv[4]);
$extra 			= trim($argv[5]);
$api_property 	= trim($argv[6]);

/*
 * $api_property ���������� � ������� JSON
 */
if ($api_property != '') {
    try {
        $api_property = json_decode($api_property, true);
    } catch (Exception $e) {
        $api_property = array();
    }
}

/*
* extra ��������� � ������� JSON
*/
if ($extra != '') {
    try {
        $extra = json_decode($extra, true);
    } catch (Exception $e) {
        $extra = array();
    }
}


/*
* ��������� ����������� ����������
*/
try {
    $remote_supplier = LinemediaAutoRemoteSuppliersSupplier::loadByApi($api_property);
} catch (Exception $e) {
    die(json_encode(array(
        'error' => 1,
        'error_level' => LM_AUTO_DEBUG_ERROR,
        'text' => "Error init $api: " . $e->GetMessage(),
    )));
}

/*
 * �������������
 */
try {
    $remote_supplier->init();
} catch (Exception $e) {
    die(json_encode(array(
        'error' => 1,
        'error_level' => LM_AUTO_DEBUG_ERROR,
        'text' => "Error init supplier #$supplier_id($api) " . $e->GetMessage() . " object: not created",
    )));
}

/*
* ������� ������ (���� �� �� �������� � �������)
*/
try {
    $remote_supplier->login();
} catch (Exception $e) {
    die(json_encode(array(
        'error' => 1,
        'error_level' => LM_AUTO_DEBUG_WARNING,
        'text' => "Error login $api: " . $e->GetMessage(),
    )));
}


/*
 * ������ �� ��������
 */
$remote_supplier->setQuery($query);


/*
 * ������ �� ����� ������
 */
$remote_supplier->setBrandTitle($brand_title);


/*
 * ������ �� extra
 */
foreach ($extra as $key => $val) {
    $remote_supplier->setExtra($key, $val);
}


/*
* ������� ��������� �����
*/
try {
    $remote_supplier->search();
} catch (Exception $e) {
    $level = $e->getCode();
    if($level < 1)
        $level = LM_AUTO_DEBUG_ERROR;
    die(json_encode(array(
        'error' => 1,
        'error_level' => $level,
        'text' => "Error search $api: " . $e->GetMessage(),
    )));
}


$response = null;
$response_type = $remote_supplier->getResponseType();
switch ($response_type) {
    case 'parts':
    case 'catalogs':
        /* ��-�� ��������� ��������, ��� ������������ �������� ����� ���������� � ���� � ���� ���������� ������.
        ������� ������ ���������� � �������� � ������.*/
        $parts = $remote_supplier->getParts();

        foreach ($parts as $section => $section_parts) {
            foreach ($section_parts as $i => $part) {
                $parts[$section][$i]['supplier_id'] = $supplier_id;//$supplier['PROPS']['supplier_id']['VALUE'];
            }
        }

        $catalogs = $remote_supplier->getCatalogs();

        $response = array(
            'parts' => $parts,
            'catalogs' => $catalogs
        );
        break;
    case '404':

        $response = array('404' => '404');
        break;
    default:
        die(json_encode(array(
            'error' => 1,
            'error_level' => LM_AUTO_DEBUG_ERROR,
            'text' => "unknown response type ($response_type)",
        )));
}

$time = microtime(true) - $time_start;
$response['time'] = $time;

die(json_encode($response));
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
 * Задачи консольного скрипта:
 *
 * 01. Подключение класса браузера, браузер не требует битрикса
 *
 * 1. Получение объекта поставщика
 * 2. Передача параметров поиска
 * 3. Выполнение запроса
 * 4. Получение результата
 *
 * Доработки базового класса LinemediaAutoRemoteSuppliersSupplier
 *
 * public static function load($code) - избавится от получения элемента инфоблока поставщика\
 * public static function loadByID($id)
 * вместо нее будем использовать public static function loadByApi($arApi)
 */
/********************************************************************************************************/

define('LM_AUTO_DEBUG_NOTICE', 1);
define('LM_AUTO_DEBUG_WARNING', 10);
define('LM_AUTO_DEBUG_USER_ERROR', 15);
define('LM_AUTO_DEBUG_ERROR', 20);
define('LM_AUTO_DEBUG_CRITICAL', 30);

define('LANGUAGE_ID', 'ru');

/********************************* основной код исполняемого файла ***************************************/

error_reporting(E_ERROR || E_PARSE || E_COMPILE_ERROR);

include_once('bitrix_lang_function.php');
include_once('bitrix_other_functions.php');


if ($argc < 5) {
    die('Commandline execution only');
}

$time_start = microtime(true);

//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_STRICT);

/*
 * Вызов set_time_limit НЕ работает в CLI
 * Вместо этого перед вызовом CMD дописано timeout 30
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
 * Входные параметры
 */
$api 			= trim($argv[1]);
$supplier_id	= trim($argv[2]);
$query 			= trim($argv[3]);
$brand_title 	= trim($argv[4]);
$extra 			= trim($argv[5]);
$api_property 	= trim($argv[6]);

/*
 * $api_property передается в формате JSON
 */
if ($api_property != '') {
    try {
        $api_property = json_decode($api_property, true);
    } catch (Exception $e) {
        $api_property = array();
    }
}

/*
* extra передаётся в формате JSON
*/
if ($extra != '') {
    try {
        $extra = json_decode($extra, true);
    } catch (Exception $e) {
        $extra = array();
    }
}


/*
* Первичное подключение поставщика
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
 * Инициализация
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
* Попытка логина (если он не объединён с поиском)
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
 * Фильтр по артикулу
 */
$remote_supplier->setQuery($query);


/*
 * Фильтр по имени бренда
 */
$remote_supplier->setBrandTitle($brand_title);


/*
 * Фильтр по extra
 */
foreach ($extra as $key => $val) {
    $remote_supplier->setExtra($key, $val);
}


/*
* Попытка выполнить поиск
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
        /* из-за словоформ возможно, что возвращённые каталоги будут объединены в один и надо показывать детали.
        поэтому всегда возвращаем и каталоги и детали.*/
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
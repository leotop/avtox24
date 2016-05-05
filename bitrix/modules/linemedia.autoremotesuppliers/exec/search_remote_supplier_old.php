<?php


if ($argc < 5) {
	die('Commandline execution only');
}

$time_start = microtime(true);


/*
 * Вызов set_time_limit НЕ работает в CLI
 * Вместо этого перед вызовом CMD дописано timeout 30
 */
//set_time_limit(30);


// /bitrix/modules/linemedia.autoremotesuppliers/exec/filename
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

/*
 * Подключим Bitrix
 */
define("NO_KEEP_STATISTIC", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if (!CModule::IncludeModule('linemedia.auto')) {
	die('No module linemedia.auto');
}

if (!CModule::IncludeModule('linemedia.autoremotesuppliers')) {
	die('No module linemedia.autoremotesuppliers');
}

/*
 * Входные параметры
 */
$api 			= trim($argv[1]);
$supplier_id	= trim($argv[2]);
$query 			= trim($argv[3]);
$brand_title 	= trim($argv[4]);
$extra 			= trim($argv[5]);


/*
* extra передаётся в формате JSON
*/
if ($extra != '') {
	try {
		$extra = json_decode($extra);
	} catch (Exception $e) {
		$extra = array();
	}
}





/*
* Первичное подключение поставщика
*/
try {
      $remote_supplier = LinemediaAutoRemoteSuppliersSupplier::load($supplier_id);
    if (is_object($remote_supplier)) {
        $remote_supplier->init();
    } else {
    die(json_encode(array(
        'error' => 1,
        'error_level' => LM_AUTO_DEBUG_ERROR,
        'text' => "Error init supplier #$supplier_id($api) object: not created",
    )));
    }
} catch (Exception $e) {
	die(json_encode(array(
		'error' => 1,
		'error_level' => LM_AUTO_DEBUG_ERROR,
		'text' => "Error init $api: " . $e->GetMessage(),
	)));
}






/*
* Проверим необходимость запуска запроса, может данные уже есть в кеше?
*/
$supplier_cache_time = (float) $remote_supplier->getProfileOption('cache_time');
$cache_time = (float) COption::GetOptionString("linemedia.autoremotesuppliers", "cache_time", "0");
if($cache_time > 0 || $supplier_cache_time > 0)
{
	/*
	* Установка поставщика приоритетнее глобальной
	* Но float от пустой строки равен нулю, а поэтому проверим, что у нас не отключено кеширование при включённом глобальном кеше
	*/
	$cache_time = $remote_supplier->getProfileOption('cache_time') != '' ? $supplier_cache_time : $cache_time;

	$cache_time *= 60;

	$cache = new CPHPCache();
	$cache_id = $api.'|'.$supplier_id.'|'.$query.'|'.$brand_title.'|'.serialize($extra);

	if ($cache_time > 0 && $cache->InitCache($cache_time, $cache_id, '/lm_auto/remote_suppliers/'))
	{
	   $response = $cache->GetVars();
	   die(json_encode($response));
	}
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



/*
* записываем данные в кеш
*/
if($cache_time > 0)
{
	$response_save = $response;
	$response_save['time'] = 0;
	$cache->StartDataCache();
	$cache->EndDataCache($response_save);
}


die(json_encode($response));
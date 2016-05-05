<?php
/*
 * компонент выводит автокаталог текдока из нашего API
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*
* Проверка наличия необходимых модулей
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}
/*
* Подключаемся к API
*/
$api = new LinemediaAutoApiDriver();
try {
	$data = $api->query('getAccountInfo', array());
} catch (Exception $e) {
	echo $e->GetMessage();
	return;
}

$arResult['catalogs'] = array();
$catalogs = (array) $data['data']['original_catalogs'];

foreach($catalogs as $catalog) {
	if(!$catalog['available'])
		continue;
	
	$catalog['URL'] = $arParams['CATALOG_' . strtoupper($catalog['brand_code'])];
	if($catalog['URL'] == '' ) {
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
        $path = parse_url($uri, PHP_URL_PATH);
        $path = str_replace(basename($_SERVER["SCRIPT_FILENAME"]), '', $path);/* prevent "/index.php/" addresses*/

        if (strrpos($path, '/') == strlen($path)-1) {
            $catalog['URL'] = $path . $catalog['brand_code'] . '/';
			if ($arParams['SEF_MODE'] == 'Y' && !empty($arParams["SEF_FOLDER"])) {
				$catalog['URL'] = $arParams["SEF_FOLDER"] . $catalog['brand_code'] . '/';
			}
        }
    }
	$arResult['catalogs'][ strtolower($catalog['brand_code']) ] = $catalog;
}

/*не нашёл, как заставить битрикс понимать рег.выражения. за сим так. 7 параметров должно хватить.*/
    $arUrlTemplates = array(
        "list" => "index.php",
        "catalog" => "#BRAND#/",
        "catalog1" => "#BRAND#/#A#/",
        "catalog2" => "#BRAND#/#A#/#B#/",
        "catalog3" => "#BRAND#/#A#/#B#/#C#/",
        "catalog4" => "#BRAND#/#A#/#B#/#C#/#D#/",
        "catalog5" => "#BRAND#/#A#/#B#/#C#/#D#/#E#",
        "catalog6" => "#BRAND#/#A#/#B#/#C#/#D#/#E#/#F#/",
        "catalog7" => "#BRAND#/#A#/#B#/#C#/#D#/#E#/#F#/#G#/",
    );

$arVariables = array();

/**
* small bitrix black magic...
*/

$page = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"],
                                         $arUrlTemplates, $arVariables);
/**
*    если $page === false, то у нас ни один из $arUrlTemplates не подошёл.
*    будем считать, что это возможно в случае, когда у нас нет завершающего запрос слеша.
*    поэтому редиректим на страницу со слешем на конце (но только в случае включённого чпу).
*/

$arResult['BRAND'] = '';

$template = '';

if ($page == false && $arParams['SEF_MODE']=='Y') {
    $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
    $path = parse_url($uri, PHP_URL_PATH);
    $path = str_replace('index.php', '', $path);/* prevent "/index.php/" addresses*/
    if (strrpos($path, '/') != strlen($path)-1) {
        $q = parse_url($uri, PHP_URL_QUERY);
        if (strlen($q))
            LocalRedirect($path.'/?'.$q, 1, '301 Moved Permanently');
        else
            LocalRedirect($path.'/', 1, '301 Moved Permanently');
        return;
    }
} else {
    /**
        после удачного вызова ParseComponentPath() в $arVariables лежат полученные из шаблона пути переменные.
        превратим их в переменные.
    */
    extract($arVariables);
    if (isset($BRAND) && !empty($BRAND)) {
        
        if (isset( $arResult['catalogs'][ strtolower($BRAND) ] )) {
            /*
                добавим в цепочку навигации бренд, т.к. компоненты ориг.каталогов бренд не выводят.
            */
            $APPLICATION->AddChainItem(strtoupper($BRAND), $arParams['SEF_FOLDER'].'/'.strtolower($BRAND).'/');
            $arResult['BRAND'] = strtolower($BRAND);
            $template = 'brand';
        } else {
            CHTTP::SetStatus('404 Not Found');
            define('ERROR_404', 'Y');
            return;
        }
    }
}

/*
* Подключение шаблона. если бренд пришёл в запросе -- то подключается соотв. компонент.
*/
$this->IncludeComponentTemplate($template);

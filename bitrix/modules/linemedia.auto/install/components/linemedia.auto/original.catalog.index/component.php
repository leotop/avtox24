<?php
/*
 * ��������� ������� ����������� ������� �� ������ API
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*
* �������� ������� ����������� �������
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}
/*
* ������������ � API
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

/*�� �����, ��� ��������� ������� �������� ���.���������. �� ��� ���. 7 ���������� ������ �������.*/
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
*    ���� $page === false, �� � ��� �� ���� �� $arUrlTemplates �� �������.
*    ����� �������, ��� ��� �������� � ������, ����� � ��� ��� ������������ ������ �����.
*    ������� ���������� �� �������� �� ������ �� ����� (�� ������ � ������ ����������� ���).
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
        ����� �������� ������ ParseComponentPath() � $arVariables ����� ���������� �� ������� ���� ����������.
        ��������� �� � ����������.
    */
    extract($arVariables);
    if (isset($BRAND) && !empty($BRAND)) {
        
        if (isset( $arResult['catalogs'][ strtolower($BRAND) ] )) {
            /*
                ������� � ������� ��������� �����, �.�. ���������� ����.��������� ����� �� �������.
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
* ����������� �������. ���� ����� ������ � ������� -- �� ������������ �����. ���������.
*/
$this->IncludeComponentTemplate($template);

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Более 80 000 0000 товаров на сайте.");
$APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
$APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
$APPLICATION->SetPageProperty("title", "Автозапчасти для иномарок");
$APPLICATION->SetTitle("AvtoX24.ru");
?>
<?php

// Include soap request class
include('guayaquillib/data/requestOem.php');
// Include catalog list view
include('guayaquillib/render/catalogs/3coltable.php');

include('extender.php');

class CatalogExtender extends CommonExtender
{
    function FormatLink($type, $dataItem, $catalog, $renderer)
    {
        $link = 'catalog.php?&c=' . $dataItem['code'] . '&ssd=' . $dataItem['ssd'];

        if (CommonExtender::isFeatureSupported($dataItem, 'wizardsearch2'))
            $link .= '&spi2=t';

        return $link;
    }
}

// Create request object
$request = new GuayaquilRequestOEM('', '', Config::$catalog_data);
if (Config::$useLoginAuthorizationMethod) {
    $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
}

// Append commands to request
$request->appendListCatalogs();

// Execute request
$data = $request->query();

// Check errors
if ($request->error != '') {
    echo $request->error;
} else {   
    // Create GuayaquilCatalogsList object. This class implements default catalogs list view
    $renderer = new GuayaquilCatalogsList(new CatalogExtender());

    // Configure columns
    $renderer->columns = array('icon', 'name', 'version');

    // Draw catalogs list
    echo $renderer->Draw($data[0]);    
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
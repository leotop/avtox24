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
include('guayaquillib'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'requestOem.php');
// Include catalog list view
include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'applicability'.DIRECTORY_SEPARATOR.'applicability.php');

include('extender.php');

class CatalogExtender extends CommonExtender
{
    function FormatLink($type, $dataItem, $catalog, $renderer)
    {
        if ($type == 'unit')
            return 'unit.php?&uid='.$dataItem['unitid'].'&c='.$catalog.'&ssd='.$dataItem['ssd'].'&oem='.$renderer->oem;

        return 'applicability.php?&oem='.$dataItem['oem'].'&brand='.$dataItem['brand'];
    }
}

$brand = $_GET['brand'];
$oem = $_GET['oem'];

// Create request object
$request = new GuayaquilRequestOEM('', '', Config::$catalog_data);
if (Config::$useLoginAuthorizationMethod) {
    $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
}

// Append commands to request
$request->appendFindDetailApplicability($oem, $brand);

// Execute request
$data = $request->query();

//echo '<pre>'; print_r($data); echo '</pre>';
// Check errors
if ($request->error != '')
{
    echo $request->error;
}
else
{
    // Create GuayaquilCatalogsList object. This class implements default catalogs list view
    $renderer = new GuayaquilApplicability(new CatalogExtender(), $oem);
    $renderer->columns = array('name', 'date', 'datefrom', 'dateto', 'model', 'framecolor', 'trimcolor', 'modification', 'grade', 'frame', 'engine', 'engineno', 'transmission', 'doors', 'manufactured', 'options', 'creationregion', 'destinationregion', 'description', 'remarks');

    // Draw catalogs list
    echo $renderer->Draw($data[0]);
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
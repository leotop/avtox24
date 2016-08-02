<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Более 80 000 0000 товаров на сайте.");
$APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
$APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
$APPLICATION->SetPageProperty("title", "Автозапчасти для иномарок");
$APPLICATION->SetTitle("AvtoX24.ru");
?>
<?php
include('am_searchpanel.php');
// Include soap request class
include('guayaquillib'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'requestAm.php');

$detail_id = $_GET['detail_id'];
$options = $_GET['options'];

$request = new GuayaquilRequestAM('en_US');
if (Config::$useLoginAuthorizationMethod) {
    $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
}

$request->appendFindDetail($detail_id, $options);

$data = $request->query();

if ($request->error != '')
{
    echo $request->error;
}
else
{
    $data = simplexml_load_string($data);
    $data = $data[0]->FindDetails->detail;

    foreach ($data as $detail)
    {
        echo '<hr>';
        echo '<div><a href="am_manufacturerinfo.php?manufacturerid='.$detail['manufacturerid'].'">'.$detail['manufacturer'].'</a> <a href="am_finddetail.php?detail_id='.$detail['detailid'].'">'.$detail['formattedoem'].'</a> '.$detail['name'].'</div>';

        $weight = (float)$detail['weight'];
        if ($weight)
            echo '<div>Weight '.$weight.'</div>';

        $volume = (float)$detail['volume'];
        if ($volume)
            echo '<div>Volume '.$volume.'</div>';

        $dimensions = (float)$detail['dimensions'];
        if ($dimensions)
            echo '<div>Weight '.$dimensions.'</div>';

        foreach ($detail->properties->property as $property) {
            echo '<div>'.$property['property'].' '.$property['locale'].' '.$property['value'].'</div>';
        }
        foreach ($detail->images->image as $image) {
            echo '<div>'.$image['filename'].'</div>';
        }
        foreach ($detail->replacements->replacement as $replacement) {
            echo '<div>'.$replacement['type'].' '.$replacement['way'].' ';

            echo '<a href="am_manufacturerinfo.php?manufacturerid='.$replacement->detail['manufacturerid'].'">'.$replacement->detail['manufacturer'].'</a> <a href="am_finddetail.php?detail_id='.$replacement->detail['detailid'].'">'.$replacement->detail['formattedoem'].'</a> '.$replacement->detail['name'];

            $weight = (float)$replacement->detail['weight'];
            if ($weight)
                echo 'Weight '.$weight;

            $volume = (float)$replacement->detail['volume'];
            if ($volume)
                echo 'Volume '.$volume;

            $dimensions = (float)$replacement->detail['dimensions'];
            if ($dimensions)
                echo 'Weight '.$dimensions;

            echo '</div>';
        }
    }
}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
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
    // Include view class
    include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'unit'.DIRECTORY_SEPARATOR.'default.php');
    include('extender.php');

    class DetailExtender extends CommonExtender
    {
        function FormatLink($type, $dataItem, $catalog, $renderer)
        {
            if ($type == 'filter')
                $link = 'detailfilter.php?c=' . $catalog . '&vid=' . $renderer->vehicle_id . '&uid=' . $_GET['uid'] .  '&cid=' . $_GET['cid'] . 'did=' . $dataItem['detailid'] . '&ssd=' . $dataItem['ssd'] . '&f=' . urlencode($dataItem['filter']);
            else {
                $link = Config::$redirectUrl;
                $link = str_replace('$oem$', urlencode($dataItem['oem']), $link);
            }

            return $link;
        }    
    }

    // Create request object
    $request = new GuayaquilRequestOEM($_GET['c'], $_GET['ssd'], Config::$catalog_data);
    if (Config::$useLoginAuthorizationMethod) {
        $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
    }

    // Append commands to request
    $request->appendGetUnitInfo($_GET['uid']);
    $request->appendListDetailByUnit($_GET['uid']);
    $request->appendListImageMapByUnit($_GET['uid']);
    // Append commands to request
    $request->appendGetVehicleInfo($_GET['vid']);

    // Execute request
    $data = $request->query();

    // Check errors
    if ($request->error != '')
    {
        echo $request->error;
    }
    else
    {
        $unit = $data[0]->row;
        $imagemap = $data[2];
        $details = $data[1];
        $vehicle = $data[3]->row;



    ?>
    <div id="pagecontent" class="laximo-wrapper"> 

        <h2><?echo xml_attribute($vehicle, 'brand');?>, год выпуска: <?echo xml_attribute($vehicle->attribute[10], 'value');?></h2>

        <div class="laximo-car-info-line">
            <div class="laximo-car-info-item">
                <span class="prop_name">Модель:</span>
                <span class="prop_value"><?=xml_attribute($vehicle, 'name')?></span>
            </div>

            <div class="laximo-car-info-item">
                <span class="prop_name">VIN:</span>
                <span class="prop_value"><?=trim(htmlspecialcharsbx($_GET["vin"]))?></span>
            </div>

            <div class="laximo-car-info-item">
                <span class="prop_name">Двигатель / КПП:</span>
                <span class="prop_value"><?echo xml_attribute($vehicle->attribute[7], 'value');?> / <?echo xml_attribute($vehicle->attribute[6], 'value');?></span>
            </div>
        </div>

        <div class="laximo-content-line"></div>

        <h2 class="unit-page-h2"><?=CommonExtender::FormatLocalizedString('UnitName', (string)$unit['name']);?></h2>

        <div class="laximo-unit-page-wrapper">
            <?                                                                      
                $renderer = new GuayaquilUnit(new DetailExtender());
                $renderer->detaillistrenderer = new GuayaquilDetailsList($renderer->extender);
                $renderer->detaillistrenderer->columns = array('PNC' => 3, 'Name' => 3, "pricelink" => 3);
                echo $renderer->Draw($_GET['c'], $unit, $imagemap, $details, NULL, NULL);   
            ?>
        </div>
    </div>
    <?

        $pnc = array();
        if (array_key_exists('coi', $_GET))
            $pnc = explode(',', $_GET['coi']);

        if (array_key_exists('oem', $_GET) && $_GET['oem']) {
            $oem = $_GET['oem'];
            foreach ($details as $detail) {
                if ((string)$detail['oem'] == $oem) {
                    $pnc[] = (string)$detail['codeonimage'];
                }
            }
        }
        if (count($pnc)) {?>
        <script type="text/javascript">
            <?php
                foreach ($pnc as $code)
                    echo 'jQuery(\'.g_highlight[name='.$code.']\').addClass(\'g_highlight_lock\');';
            ?>
        </script>
        <?php }

    }
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
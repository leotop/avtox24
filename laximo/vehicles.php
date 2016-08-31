<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetPageProperty("description", "Более 80 000 0000 товаров на сайте.");
    $APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
    $APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
    $APPLICATION->SetPageProperty("title", "Результаты поиска");
?>
<?php
    // Include soap request class
    include('guayaquillib/data/requestOem.php');
    // Include view class
    include('guayaquillib/render/vehicles/vehicletable.php');

    include('extender.php');

    class VehiclesExtender extends CommonExtender
    {
        function FormatLink($type, $dataItem, $catalog, $renderer)
        {    
            if (!$catalog)
                $catalog = $dataItem['catalog'];
            $link = ($renderer->qg == 1 ? 'qgroups' : 'vehicle') . '.php?c=' . $catalog . '&vid=' . $dataItem['vehicleid'] . "&vin=". $_GET["vin"] . '&ssd=' . $dataItem['ssd'] . ($renderer->qg == -1 ? '&checkQG': ''). '&path_data=' . urlencode(base64_encode(substr($dataItem['vehicle_info'], 0, 300)));
            return $link;
            //return 'vehicle.php?c='.$catalog.'&vid='.$dataItem['vehicleid'].'&ssd='.$dataItem['ssd'];
        }
    }

    // Create request object
    $catalogCode = array_key_exists('c', $_GET) ? $_GET['c'] : false;
    $request = new GuayaquilRequestOEM($catalogCode, array_key_exists('ssd', $_GET) ? $_GET['ssd'] : '', Config::$catalog_data);
    if (Config::$useLoginAuthorizationMethod) {
        $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
    }

    // Append commands to request
    $findType = $_GET['ft'];
    if ($findType == 'findByVIN')
        $request->appendFindVehicleByVIN($_GET['vin']);
    else if ($findType == 'findByFrame')
        $request->appendFindVehicleByFrame($_GET['frame'], $_GET['frameNo']);
        else if ($findType == 'execCustomOperation')
            $request->appendExecCustomOperation($_GET['operation'], $_GET['data']);
            else if ($findType == 'findByWizard2')
                $request->appendFindVehicleByWizard2($_GET['ssd']);

                if ($catalogCode) {
        $request->appendGetCatalogInfo();
    }
    // Execute request
    $data = $request->query();

    // Check errors
    if ($request->error != '') {
        echo $request->error;
    } else {
        $vehicles = $data[0];
        $cataloginfo = $catalogCode ? $data[1]->row : false;

        if (is_object($vehicles) == false || $vehicles->row->getName() == '') {
            if ($_GET['ft'] == 'findByVIN')
                echo CommonExtender::FormatLocalizedString('FINDFAILED', $_GET['vin']);
            else
                echo CommonExtender::FormatLocalizedString('FINDFAILED', $_GET['frame'] . '-' . $_GET['frameNo']);
        } else {
            echo '<h2>' . CommonExtender::LocalizeString('Cars') .'</h2><br>';
        ?>
        <div class="vin-search-results-wrap">
            <?
                // Create data renderer
                $renderer = new GuayaquilVehiclesList(new VehiclesExtender());
                $renderer->columns = array('name', 'date', 'datefrom', 'dateto', 'model', 'framecolor', 'trimcolor', 'modification', 'grade', 'frame', 'engine', 'engineno', 'transmission', 'doors', 'manufactured', 'options', 'creationregion', 'destinationregion', 'description', 'remarks');

                $renderer->qg = !$cataloginfo ? -1 : (CommonExtender::isFeatureSupported($cataloginfo, 'quickgroups') ? 1 : 0);

                // Draw data
                echo $renderer->Draw($catalogCode, $vehicles);
            ?>
        </div>
        <?

        }   


    }

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
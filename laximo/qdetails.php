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
    include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'qdetails'.DIRECTORY_SEPARATOR.'default.php');
    include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'qgroups'.DIRECTORY_SEPARATOR.'default.php');
    // Include view class
    include('extender.php');

    class QuickDetailsExtender extends CommonExtender
    {
        function FormatLink($type, $dataItem, $catalog, $renderer)
        {
            if ($type == 'vehicle')
                $link = 'vehicle.php?c='.$catalog.'&vid='.$renderer->vehicleid. '&ssd=' . $renderer->ssd. "&vin=".$_GET['vin'];
            elseif ($type == 'category')
                $link = 'vehicle.php?c=' . $catalog . '&vid=' . $renderer->vehicleid . '&cid=' . $dataItem['categoryid'] . '&ssd=' . $dataItem['ssd']. "&vin=".$_GET['vin'];
            elseif ($type == 'unit')
            {
                $coi = array();
                foreach ($dataItem->Detail as $detail)
                {
                    if ((string)$detail['match']) {
                        $i = (string)$detail['codeonimage'];
                        $coi[$i] = $i;     
                    }
                }

                $link = 'unit.php?c=' . $catalog . '&vid=' . $renderer->vehicleid . '&uid=' . $dataItem['unitid'] .  '&cid=' . $renderer->currentunit['categoryid'] . '&ssd=' . $dataItem['ssd'] . '&coi=' . implode(',', $coi) . "&vin=".$_GET['vin'];
            }
            elseif ($type == 'detail') {
                $link = Config::$redirectUrl;
                $link = str_replace('$oem$', urlencode($dataItem['oem']), $link);
            }

            return $link;
        }
    }

    class QuickGroupsExtender extends CommonExtender
    {
        function FormatLink($type, $dataItem, $catalog, $renderer)
        {
            if ($type == 'vehicle')
                $link = 'vehicle.php?c='.$catalog.'&vid='.$renderer->vehicleid. '&ssd=' . $renderer->ssd . "&vin=".$_GET['vin'];
            else
                $link = 'qdetails.php?c='.$catalog.'&gid='.$dataItem['quickgroupid']. '&vid=' . $renderer->vehicleid. '&ssd=' . $renderer->ssd . "&vin=".$_GET['vin'];

            return $link;
        }
    }

    // Create request object
    $request = new GuayaquilRequestOEM($_GET['c'], $_GET['ssd'], Config::$catalog_data);
    if (Config::$useLoginAuthorizationMethod) {
        $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
    }

    // Append commands to request
    $request->appendGetVehicleInfo($_GET['vid']);
    $request->appendListCategories($_GET['vid'], isset($_GET['cid']) ? $_GET['cid'] : -1);
    $request->appendListQuickDetail($_GET['vid'], $_GET['gid'], 1);
    $request->appendListQuickGroup($_GET['vid']);

    // Execute request
    $data = $request->query();

    // Check errors
    if ($request->error != '')
    {
        echo $request->error;
    }
    else
    {
        $vehicle = $data[0]->row;
        $categories = $data[1];
        $details= $data[2];
        $groups = $data[3];


    ?>

    <div id="pagecontent" class="laximo-wrapper"> 

        <h2><?echo CommonExtender::FormatLocalizedString('GroupDetails', $vehicle['name']);?></h2>

        <div class="laximo-car-info-line">
            <div class="laximo-car-info-item">
                <span class="prop_name">Модель:</span>
                <span class="prop_value"><?=xml_attribute($vehicle, 'name')?></span>
            </div>

            <div class="laximo-car-info-item">
                <span class="prop_name">VIN:</span>
                <span class="prop_value"><?=trim($_GET["vin"])?></span>
            </div>

            <div class="laximo-car-info-item">
                <span class="prop_name">Двигатель / КПП:</span>
                <span class="prop_value"><?echo xml_attribute($vehicle->attribute[7], 'value');?> / <?echo xml_attribute($vehicle->attribute[6], 'value');?></span>
            </div>
        </div>

        <div class="laximo-content-line"></div>

        <div class="laximo-page-headers">
            <div class="laximo-left-menu-title">Классификатор:</div>
            <div class="laximo-content-title">Категории</div>
        </div>

        <div class="laximo-left-menu-wrap">

            <ul class="laximo-left-menu-tabs-titles">
                <li class="active">Общий</li>
                <?/*<li>От производителя</li>*/?>
            </ul>

            <?
                $renderer = new GuayaquilQuickGroupsList(new QuickGroupsExtender());
                echo $renderer->Draw($groups, $_GET['c'], $_GET['vid'], $_GET['ssd']);    
            ?>
        </div>  

        <div class="laximo-page-content-wrap">
            <?
                $renderer = new GuayaquilQuickDetailsList(new QuickDetailsExtender());
                $renderer->detaillistrenderer = new GuayaquilDetailsList($renderer->extender);
                $renderer->detaillistrenderer->group_by_filter = 1;
                echo $renderer->Draw($details, $_GET['c'], $_GET['vid'], $_GET['ssd']);
            ?>
        </div>

    </div>   

    <?       
    }
?>
<script>
    $(function(){
        //раскрываем дерево при загрузке страницы
        var group_id = <?=intval($_GET["gid"]);?>;
        if (group_id > 0) {
            $("#qgTree a").each(function(){
                var url = $(this).attr("href");
                if (url.indexOf("gid=" + group_id) > 0) {
                    $(this).parents("li").toggleClass("qgExpandClosed").toggleClass("qgExpandOpen"); 
                }
            })
        }
    })
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
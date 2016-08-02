<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetPageProperty("description", "Более 80 000 0000 товаров на сайте.");
    $APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
    $APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
    $APPLICATION->SetPageProperty("title", "Каталог оригинальных запчастей");
?> 

<script>
    <?if (!$_GET["gid"]) { //если в урле нет ID группы?>  
        $(function(){
            buildBlocksFromTree();       
        })      
        <?} else {?>
        loadDetailsInfo("qdetails.php?<?=$_SERVER["QUERY_STRING"]?>"); 
        <?}?>     
</script>     
<?php

    // Include soap request class
    include('guayaquillib'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'requestOem.php');
    // Include view class
    include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'qdetails'.DIRECTORY_SEPARATOR.'default.php');
    include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'qgroups'.DIRECTORY_SEPARATOR.'default.php');
    // Include view class
    include('extender.php');

    class QuickGroupsExtender extends CommonExtender
    {
        function FormatLink($type, $dataItem, $catalog, $renderer)
        {
            if ($type == 'vehicle')
                $link = 'vehicle.php?c='.$catalog.'&vid='.$renderer->vehicleid. '&ssd=' . $renderer->ssd;
            else
                $link = 'qdetails.php?c='.$catalog.'&gid='.$dataItem['quickgroupid']. '&vid=' . $renderer->vehicleid. '&ssd=' . $renderer->ssd;

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
        $groups= $data[1];


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

        </div>

    </div>
    <?}?>
        
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
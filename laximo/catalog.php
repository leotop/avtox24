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
    include('extender.php');

    // Create request object
    $request = new GuayaquilRequestOEM($_GET['c'], $_GET['ssd'], Config::$catalog_data);
    if (Config::$useLoginAuthorizationMethod) {
        $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
    }

    // Append commands to request
    $request->appendGetCatalogInfo();
    if (@$_GET['spi2'] == 't')
        $request->appendGetWizard2();

    // Execute request
    $data = $request->query();

    // Check errors
    if ($request->error != '') {
        echo $request->error;
    } else {
        $cataloginfo = $data[0]->row;
    ?>
    <div id="catalogSearchPageWrapper" class="catalogSearchWrapper">

        <div>       

            <div class="catalogSearchFormWrapper framesearch">
                <?
                    $formframe = $formframeno = '';
                    include('forms/framesearch.php');    
                ?>
            </div>

            <div class="catalogSearchFormWrapper">
                <?include('forms/vinsearch.php');?>                   

            </div>

            <div class="catalogSearchFormWrapper">
                <? 
                    if ($cataloginfo->extensions->operations) {
                        foreach ($cataloginfo->extensions->operations->operation as $operation) {
                            if ($operation['kind'] == 'search_vehicle') {
                                include('forms/operation.php');
                            }
                        }
                    }       
                ?>
            </div>    

        </div>

        <div>
            <div class="catalogSearchFormWrapper" style="border: none;">
                <?                
                    $wizard = $data[1];
                    include('forms/wizardsearch2.php');     
                ?>
            </div>
        </div>    
    </div>
    <?
    }
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * Обязательно к применению
 */
include "../../_lib.php";
include "../api.php";

$mcct        = MCCT::instance();                        // Библиотека каталога
$vin        = $mcct->rcv('vin');                    // Параметр 'VIN' запроса серверу
$mark        = $mcct->rcv('mark');                    // Параметр 'МАРКА' запроса серверу

$server    =    $mcct->getMcctSearch('vin',$vin); // Обращение к серверу

$map        = json_decode(json_encode($server->map),true);

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBread('search/vin','Поиск по VIN'.($vin?':'.$vin:''));

$mcct->addSearch($server);
?>
<link href="../../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../../media/js/holder.min.js"></script>

<div class="container-fluid">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <?php include WWW_ROOT."helpers/search.php"; /// Подключаем форму поиска?>

<?php

?>
    <table class="dataTable no-footer" role="grid" aria-describedby="dataTable_info">
        <thead>
            <tr>
                <th>Фото</th>
                <th>VIN</th>
                <th>Модель кузова</th>
                <th>Каталог</th>
                <th>Модельный ряд</th>
                <th>Регион</th>
            </tr>
        </thead>
        <tbody>
        <?
        foreach((array)$server->table as $v)
        {
            if(in_array($v->catalog->vehicle_type_code,['C','S']))
                $type=strtolower($v->catalog->vehicle_type_code);
            else
                $type='';

            $param['mark']=$mark;
            $param['type']=$type;
            $param['region']=$v->reg;
            $param['family']=$v->family;
            $param['catalog']=$v->data->catalogue_code;
            $param['model']=$v->data->model;
            $param['vin']=$v->data->vin;
            $url=$mcct->urlMcct('model',$param);
        ?>
            <tr onclick="window.location='<?=$url?>'; return false;">
                <td>
                    <img src="<?=$v->catalog->image?>" data-src="holder.js/100x50?text=<?=$v->family?>" alt="<?=$v->family?>">
                </td>
                <td>VIN: <a href="<?=$url?>"><?=$v->data->vin?></a></td>
                <td><?=$v->model->model?></td>
                <td><?=$v->catalog->cat_name?></td>
                <td>
                <? $families=array_keys((array)$v->families); foreach($families as $family) { ?>
                    <?=$family?>
                <? } ?>
                </td>
                <td>
                <? $regions=(array)$v->regions; foreach($regions as $reg=>$region) { ?>
                    <?=$region?>,
                <? } ?>
                </td>
            </tr>
        <? } ?>
        </tbody>
    </table>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
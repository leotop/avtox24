<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php

include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс Fiat

$oFiat = new Fiat();
$brand = $oFiat->rcv('brand');
$model = $oFiat->rcv('model');
$res = $oFiat->getFIATProduction($brand, $model);
if( ($aErrors = A2D::property($res,'errors')) ) $res->error($aErrors,404);

A2D::$arrActions = ['brand']; A2D::$showMark = FALSE;
Fiat::constructBreadcrumbs($res->meta->breadcrumbs);
?>
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fiat.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../media/js/dataTable.js"></script>

<div>

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <h1>Список модификаций <?=mb_strtoupper($brand)?> <?=$res->meta->model_name?></h1>

    <table id="dataTable" class="dataTable">
        <thead>
        <tr>
            <th>Описание</th>
            <th>Код</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $res->prod AS $prod ){?>
            <tr onclick="window.location.href ='/fiat/groups.php?brand=<?=$brand?>&model=<?=$model?>&production=<?=$prod->cat_cod?>';">
                <td><a href="/fiat/groups.php?brand=<?=$brand?>&model=<?=$model?>&production=<?=$prod->cat_cod?>"><?=$prod->cat_dsc?></a></td>
                <td><a href="/fiat/groups.php?brand=<?=$brand?>&model=<?=$model?>&production=<?=$prod->cat_cod?>"><?=$prod->cat_cod?></a></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php

include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс Fiat

$oFiat = new Fiat();
$brand = $oFiat->rcv('brand');
$model = $oFiat->rcv('model');
$production = $oFiat->rcv('production');
$res = $oFiat->getFIATGroup($brand, $model, $production);
$meta = $res->meta;
if( ($aErrors = A2D::property($res,'errors')) ) $res->error($aErrors,404);

A2D::$arrActions = ['brand','model']; A2D::$showMark = FALSE;
Fiat::constructBreadcrumbs($res->meta->breadcrumbs);
?>

<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fiat.css" media="all" rel="stylesheet" type="text/css">


<div id="detailsG1">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <h1>Основные узлы деталей для <?=$meta->brand_name?> <?=$meta->model_name?> <?=$meta->production_name?></h1>

    <?php foreach( $res->groups AS $group ){?>
        <a href="/fiat/subGroups.php?brand=<?=$brand?>&model=<?=$model?>&production=<?=$production?>&group=<?=$group->code?>" class="fl defBorder">
            <img src="<?=$group->g_img?>"><br/>
            <span><?=$group->code?> <?=$group->descr?></span>
        </a>
    <?php }?>
    <hr class="clear">

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
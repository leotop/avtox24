<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php

include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс Fiat

$oFiat = new Fiat();
$brand = $oFiat->rcv('brand');
$model = $oFiat->rcv('model');
$production = $oFiat->rcv('production');
$group = $oFiat->rcv('group');
$res = $oFiat->getFIATSubGroup($brand, $model, $production, $group);
$meta = $res->meta;
if( ($aErrors = A2D::property($res,'errors')) ) $res->error($aErrors,404);

A2D::$arrActions = ['brand','model', 'production', 'groups']; A2D::$showMark = FALSE;
Fiat::constructBreadcrumbs($res->meta->breadcrumbs);
?>

<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fiat.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>

<div>

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <h1><?=$meta->group_name?> для <?=$meta->brand_name?> <?=$meta->model_name?> <?=$meta->production_name?></h1>

    <table id="dataTable" class="dataTable">
        <thead>
        <tr>
            <th>Группа</th>
            <th>Подгруппа</th>
            <th>Применяемость</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $res->subGroups AS $p ):?>
            <tr class="withoutHrHover">
                <td rowspan="<?=count($p->board)+1?>">
                    <?=$p->subGroup->sgrp_dsc?>
                </td>
            </tr>
            <?php foreach( $p->board AS $b ):?>
                <tr>
                    <td>
                        <a href="/fiat/draw.php?brand=<?=$brand?>&model=<?=$model?>&production=<?=$production?>&group=<?=$group?>&subGroup=<?=$p->subGroup->sgrp_cod?>&tableCod=<?=urlencode(base64_encode($b->table_cod))?>">
                            <?=$b->dsc?>
                        </a>
                        <br>
                    </td>
                    <td>
                        <a href="/fiat/draw.php?brand=<?=$brand?>&model=<?=$model?>&production=<?=$production?>&group=<?=$group?>&subGroup=<?=$p->subGroup->sgrp_cod?>&tableCod=<?=urlencode(base64_encode($b->table_cod))?>">
                            <?=$b->pattern?>
                        </a>
                        <br>
                    </td>
                </tr>
            <?php endforeach?>
        <?php endforeach?>
        </tbody>
    </table>
</div>

<div>
    <?php if(count($meta->patterns)):?>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="explanation mt40">
                    <div class="expTable">
                        <div class="eTableHead">Расшифровка сокращений</div>
                        <div class="eTableBody">
                            <?php foreach( $meta->patterns as $pattern ):?>
                                <span class="sign"><?=$pattern->name?></span> = <span class="desc"><?=$pattern->description?></span>
                                <br/>
                            <?php endforeach?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
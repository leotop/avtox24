<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php

include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс Fiat

$oFiat = new Fiat();
$brand = $oFiat->rcv('brand');
$oModels = $oFiat->getFIATModels($brand);
if( ($aErrors = A2D::property($oModels,'errors')) ) $oFiat->error($aErrors,404);

/// "Хлебные крошки" не родные - изменяем ассоциативный массив для имен под переменные
A2D::$arrActions = ['typeID','markID'];
/// На точки входа нет переменных для крошек, строим их самостоятельно
A2D::$aBreads = A2D::toObj([
    'types' => [
        "name" => 'Каталог',
        "breads" => []
    ],
    'marks' => [
        "name" => 'Легковые (иномарки)',
        "breads" => [ 0 => 9 ]
    ],
    'models' => [
        "name" => A2D::$markName,
        "breads" => []
    ],
]); ///$oBMW->e($aBreads);
/// Текущие крошки ведут в корень, зануляем корневой каталог для старта скриптов
A2D::$catalogRoot = "";

Fiat::constructBreadcrumbs($oModels->models->meta->breadcrumbs);
?>
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fiat.css" media="all" rel="stylesheet" type="text/css">

<div>
    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>
    <h1>Оригинальные каталоги запчастей <?=mb_strtoupper($brand)?></h1>
    <?php foreach( $oModels->models AS $model ):?>
        <a href="/fiat/productions.php?brand=<?=$brand?>&model=<?=$model->cmg_cod?>" class="cardLink fl mb10">
            <img src="<?=$model->img?>"><br/>
            <?=$model->name?>
        </a>
    <?php endforeach?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
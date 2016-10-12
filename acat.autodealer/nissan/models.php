<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * User: lans
 * Date: 08.04.16
 * Time: 11:30
 */

/** Обязательно к применению */
include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс TOY

/// Устанавливаем объект $oNIS - объект для работы с каталогом Toyota
$oNIS = NIS::instance();

/// Получаем данные с перехода по ссылке из etka/markets.php
$mark   = $oNIS->rcv('mark');
$market = $oNIS->rcv('market');

/// Получаем спосок моделей по марке и рынку. Второй строкой останавливаемся при ошибках с сервера
$NISModels = $oNIS->getNisModels($mark,$market);
if( ($aErrors = A2D::property($NISModels,'errors')) ) $oNIS->error($aErrors,404);

/// С "хлебными крошками" никто не работает можно сразу передать в объект для конструктора
$aBreads = A2D::property($NISModels,'aBreads',[]);
NIS::constructBreadcrumbs($aBreads,NIS::filename(__FILE__));

/// Получаем модели из общего объекта
$aModels = A2D::property($NISModels,'aModels',[]);

/// Базовая часть пути для перехода на следующий этап
$url = "/nissan/modifs.php?market={$market}&model=";

?>

<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../media/js/dataTable.js"></script>


<div id="NISModels" class="AutoDealer">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <h1>Список моделей</h1>

    <table id="dataTable" class="dataTable">
        <thead>
        <tr>
            <th>Модель</th>
            <th>Серия</th>
            <th>Период производства</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $aModels AS $_v ){?>
            <tr onclick="window.location.href ='<?=$url.$_v->series?>';">
                <td><a href="<?=$url.$_v->series?>"><?=$_v->model?></a></td>
                <td><a href="<?=$url.$_v->series?>"><?=$_v->series?></a></td>
                <td><?=$_v->date?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
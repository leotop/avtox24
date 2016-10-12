<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php

/**
 * User: lans
 * Date: 08.04.16
 * Time: 12:01
 */
/** Обязательно к применению */
include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс TOY
/// Устанавливаем объект $oNIS - объект для работы с каталогом Toyota
$oNIS = NIS::instance();
/// Получаем данные с перехода по ссылке из nissan/models.php
$market = $oNIS->rcv('market');
$mark = (stripos(strtolower($market),'inf') > 1)?'infiniti':'nissan';//определяю Марку по рынку
$model  = $oNIS->rcv('model');
/// Вернет объект: опции, расшифровка сокращений и "хлебные крошки"
$NISModifs = $oNIS->getNisModiff($market,$model);
/// Останавливаемся при ошибках с сервера
if( ($aErrors = A2D::property($NISModifs,'errors')) ) $oNIS->error($aErrors,404);
/// Получаем крошки сперва в простую переменную, пригодится для работы
$aBreads = A2D::property($NISModifs,'aBreads',[]);
/// Получаем доступные опции из общего объекта, что вернул сервер
$aModifs = A2D::property($NISModifs,'aModif');
/// Формирую шапку для таблицы
$aFields = [];
foreach($aModifs[0] as $k=>$v){
    if(!in_array($k,['compl','code','prod','other'])){
        $aFields[] = NIS::translate($k);
    }
}
/// Не сгруппировать по типу запчастей(двигатель) поэтому составляем список всех сокращений
$shrts=[];
if(!empty($aModifs)) {
    foreach ($aModifs AS $k => $aModif) {
        foreach ($aModif as $k2 => $value) {
            if (!in_array($k2, ['compl', 'code', 'prod', 'other'])) {//исключаю постоянные поля, они не меняются

                $aList[$k2] = [];
                if ($k == 0) $shrts[$k2][] = $value;
                elseif (($k > 0) && (!in_array($value, $shrts[$k2]))) {
                    $shrts[$k2][] = $value;
                }
            } elseif ($k2 == 'other' && !empty($value)) {
                if ($k == 0 && $k2 == 0) $shrts['other'] = [];//для сравнения in_array
                foreach ($value as $item) {
                    if (!in_array($item, $shrts['other'])) $shrts['other'][] = $item;
                }
            }
        }
    }
}
/// Расшифровка сокращений
$_list = A2D::property($NISModifs,'info');
if(!empty($_list)) {//бывает что список пуст...
    foreach ($_list as $k => $item) {
        foreach ($shrts as $k2 => $short) {
            if (in_array($item->ABBRSTR, $short)) {
                $aList[$k2][$item->ABBRSTR] = $item->DESCRSTR;
            }
        }
    }
}
/// Делаем ключи русскими
if(!empty($aList)) {
    foreach ($aList as $k => $item) {
        $aList[NIS::translate($k)] = $aList[$k];
        unset($aList[$k]);
    }
}
/// Формируем заголовок H1 для страницы, используя данные из "хлебных крошек"
if(!empty($aBreads)) {
    $sMarket = $aBreads->models->name;
    $sModel = (!empty($aBreads->modifs))?$aBreads->modifs->name:$model;
    $sMark = ucfirst($mark);
    $h1 = "Запчасти для $sMark $sModel, список комплектаций ($sMarket)";
}
/// Данным значениям (static::$arrActions) в helpers/breads.php сопоставляются последовательно параметрам из A2D::$aBreads
A2D::$arrActions = ['','','market']; A2D::$showMark = FALSE;
NIS::constructBreadcrumbs($aBreads,NIS::filename(__FILE__));
/// Базовая часть пути для переходя на следующий этап
$url = "/nissan/groups.php?market={$market}&model={$model}&modif=";
?>
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../media/js/dataTable.js"></script>

<div id="NISModifs">
    <?php include WWW_ROOT."helpers/breads.php"; /// Продключаем хлебные крошки?>
    <h1><?=$h1?></h1>
    <table id="dataTable" class="dataTable">
        <thead>
        <tr>
            <th>Производство</th>
            <?php foreach($aFields as $value){?>
                <th><?=$value?></th>
            <?php } ?>
            <th>Другое</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $aModifs AS $aModif ){ $fullURL = $url.$aModif->compl;?>
            <tr onclick="window.location.href ='<?=$fullURL?>'">
                <td class="tl"><? echo $aModif->prod?></td>
                <?php foreach($aModif as $key =>$value){
                    if(!in_array($key,['compl','code','prod','other'])){ ?>
                        <td><?=$aModif->$key ?></td>
                    <?php }
                }
                if(!empty($aModif->other) && is_array($aModif->other)){?>
                    <td><?=implode(' ',$aModif->other)?></td>
                <?php }elseif(!empty($aModif->other) && !is_array($aModif->other)){?>
                    <td><?=$aModif->other?></td>
                <?php }else{ echo "<td></td>"; }?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php if(!empty($aList)){?>
        <div class="explanation">
            <span class="cBlue">Расшифровка сокращений</span>
            <div class="expTable">
                <?php foreach( $aList AS $n=>$s ){
                    if($s){?>
                        <div class="eTableHead"><?=$n?></div>
                        <div class="eTableBody">
                            <?php foreach( $s AS $k=>$a ){?>
                                <span class="sign"><?=$k?></span> = <span class="desc"><?=$a?></span><br/>
                            <?php }?>
                        </div>
                    <?php }
                }?>
            </div>
        </div>
    <?php } ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
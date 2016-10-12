<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * User: lans
 * Date: 08.04.16
 * Time: 13:49
 */

/** Обязательно к применению */
include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс TOY

/// Устанавливаем объект $oNIS - объект для работы с каталогом Toyota
$oNIS = NIS::instance();

/// Получаем данные с перехода по ссылке из nissan/modifs.php
$market = $oNIS->rcv('market');
$mark = (stripos(strtolower($market),'inf') > 1)?'infiniti':'nissan';//определяю Марку по рынку
$model  = $oNIS->rcv('model');
$modif  = $oNIS->rcv('modif');

$aModInfo = $oNIS->getNisModInfo($market,$model,$modif);

$oGroups  = A2D::property($aModInfo,'aModInfo');
$aBreads  = A2D::property($aModInfo,'aBreads');
if(!empty($aBreads)){
    $h1 = "Cписок групп модификации $modif для $mark ".$aBreads->modifs->name;
}
$srcImg = A2D::property($aModInfo,'Img');//mainImg

/// После работы с переменной/крошками, передаем ее в объект для конструктора
A2D::$arrActions = ['','','market','model','modif']; A2D::$showMark = FALSE;
NIS::constructBreadcrumbs($aBreads,NIS::filename(__FILE__));

/// Базовая часть пути для переходя на следующий этап
$url = "/nissan/subgroups.php?market={$market}&model={$model}&modif=$modif&group=";?>

<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../media/js/dataTable.js"></script>

<div id="NISGroups">
    <?php include WWW_ROOT."helpers/breads.php"; /// Продключаем хлебные крошки?>
    <?if(!empty($h1)){ echo '<h1>'.$h1.'</h1>'; }
    /// У японской структуры нет изображения для данного уровня
    if(strtolower($market) != 'jp') { ?>
        <img src="<?=$srcImg?>" USEMAP='#groupmap' border='1'>
    <? } ?>

    <table border="1" id="dataTable" class="dataTable tabGroups">
        <thead>
        <tr>
            <th>Группа</th>
            <th>Название</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach( $oGroups AS $k=>$p ):
            if($k == 0) $map_content = '';//контейнер для точек
            if(strtolower($market) != 'jp') {//у японцев на этом уровне нет изображения
                $map_content .= "<AREA SHAPE='RECT' COORDS='$p->X,$p->Y,$p->X2,$p->Y2' TITLE='$p->GroupName' id='Group$p->Group' HREF='" . $url . strtolower($p->Group) . "'> </AREA>\n";
            }?>
            <tr style="cursor: pointer;" onclick="window.location.href ='<?=$url.strtolower($p->Group)?>';">
                <td><a href="<?=$url.strtolower($p->Group)?>"><?=strtoupper($p->Group)?></a></td>
                <td><a href="<?=$url.strtolower($p->Group)?>"><?=$p->GroupName?></a></td>
            </tr>
        <?php endforeach?>
        </tbody>
    </table>
    <!--отрисовываю все точки-->
    <MAP NAME='groupmap' ><?=$map_content?></MAP>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * User: lans
 * Date: 08.04.16
 * Time: 14:53
 */

/** Обязательно к применению */
include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс NIS

/// Устанавливаем объект $oNIS - объект для работы с каталогом Nissan
$oNIS = NIS::instance();

/// Получаем данные с перехода по ссылке из nissan/groups.php
$market = $oNIS->rcv('market');
$model  = $oNIS->rcv('model');
$modif  = $oNIS->rcv('modif');
$group  = $oNIS->rcv('group');



$aModInfo = $oNIS->getNisGroup($market,$model,$modif,$group);
$aGroups  = A2D::property($aModInfo,'aGroup');
$aBreads  = A2D::property($aModInfo,'aBreads');

$srcImg = A2D::property($aModInfo,'Img');//картинка для всех кроме японии(у японцев много изображений может быть)

/// После работы с переменной/крошками, передаем ее в объект для конструктора
A2D::$arrActions = ['','','market','model','modif','group']; A2D::$showMark = FALSE;
NIS::constructBreadcrumbs($aBreads,NIS::filename(__FILE__));
/// Базовая часть пути для переходя на следующий этап
$nextUrl = "/nissan/illustration.php?market={$market}&model={$model}&modif=$modif&group=$group&figure=";?>

<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../media/js/dataTable.js"></script>

<div id="NISSubGroups"><?
    include WWW_ROOT."helpers/breads.php"; /// Продключаем хлебные крошки
/// Все страны, КРОМЕ Японии
    if(strtolower($market) != 'jp') { ?>
    <img src="<?=$srcImg?>" USEMAP='#groupmap' border='1'>

        <table border="1" id="dataTable" class="dataTable tabGroups">
            <thead>
            <tr>
                <th>№ Фигуры</th>
                <th>Название</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $nc = [];
            foreach( $aGroups AS $k=>$p ):
                if($k == 0) $map_content = '';//контейнер для точек
                //пишем все точки в контейнер
                $map_content .= "<AREA SHAPE='RECT' COORDS='$p->X,$p->Y,$p->X2,$p->Y2' TITLE='$p->PName' HREF='".$nextUrl.strtolower($p->figure)."'> </AREA>\n";
                //если повторяется - пропускаем в таблице, на рисунке должны быть все точки
                if(array_key_exists($k+1,$oGroups) && ($p->figure == $oGroups[$k+1]->figure) && ($p->PName == $oGroups[$k+1]->PName)) continue;
                ?>
                <tr style="cursor: pointer;" onclick="window.location.href ='<?=$nextUrl.strtolower($p->figure)?>';">
                    <td><a href="<?=$nextUrl.strtolower($p->figure)?>"><?=strtoupper($p->figure)?></a></td>
                    <td><a href="<?=$nextUrl.strtolower($p->figure)?>"><?=$p->PName?></a></td>
                </tr>
            <?php endforeach?>
            </tbody>
        </table>
        <!--отрисовываю все точки-->
        <MAP NAME='groupmap' ><?=$map_content?></MAP>
    <?
//ЭТО ЯПОНСКАЯ СИСТЕМА (несколько изображений на 1 группу)
    }else{ ?>
        <script>
            $( document ).ready(function() {
                $("#tabs a").click(function() {
                    $("#tabs a").removeClass('activeTab cBlue');
                    $(".figPart").each(function() {
                        $(this).hide();
                    });
                    $(".mapCord").each(function() {
                        $(this).hide();
                    });
                    $(this).addClass('activeTab cBlue');
                    $( '#figPart_'+$(this).attr('data') ).show();
                    $( '#mapCord_'+$(this).attr('data') ).show();
                });
            });
        </script>
        <div id="detailsMap">
            <div id="tabs">
                <?php
                $cnt = count((array)$oGroups);
                if($cnt <=4 )
                    $widthTabs = 24.5;
                else
                    $widthTabs = (100/ $cnt) - 0.5;
                for ($i = 1; $i <= $cnt; $i++) {
                    $class=($i == 1)?" class='activeTab cBlue'":"";?>
                    <a style="width:<?=$widthTabs?>%" <?=$class?> id="tabs_tab tabN" data="<?=$i?>">Часть <?=$i?> </a>
                <? } ?>
            </div>
            <? foreach($aGroups as $key => $group){ $figures = A2D::property($group,'figures');?>
                <? $displ = ($key > 1)?"style=\"display:none;\"":''; ?>

                <div class="figPart <?=$key?>" <?=$displ?> id="figPart_<?=$key?>">

                    <img src="<?=$group->Img?>" USEMAP='#groupmap<?=$key?>' id="mapImg_<?=$key?> mapImg" border='1' class="jpPartImg mapImg_<?=$key?>">

                    <table border="1" id="dataTable" class="dataTable tabGroups">
                        <thead>
                        <tr>
                            <th>№ Фигуры</th>
                            <th>Название</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $figures AS $k => $p ):
                            if($k == 0) $map_content = '';//чистка, т.к. карт больше 2 на каждую группу
                            //пишу все точки
                            $map_content .= "<AREA SHAPE='RECT' COORDS='$p->X,$p->Y,$p->X2,$p->Y2' TITLE='$p->PName' HREF='".$nextUrl.strtolower($p->figure)."'> </AREA>\n";
                            ?>
                            <tr style="cursor: pointer;" onclick="window.location.href ='<?=$nextUrl.strtolower($p->figure)?>';">
                                <td><a href="<?=$nextUrl.strtolower($p->figure)?>"><?=strtoupper($p->figure)?></a></td>
                                <td><a href="<?=$nextUrl.strtolower($p->figure)?>"><?=$p->PName?></a></td>
                            </tr>
                        <?php endforeach?>
                        </tbody>
                    </table>
                    <!--отрисовываю все точки-->
                    <MAP NAME='groupmap<?=$key?>' <?=$displ?> class="mapCord" id="mapCord_<?=$key?>"><?=$map_content?></MAP>

                </div>
            <? } ?>
        </div>
    <? } ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
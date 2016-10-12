<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * User: lans
 * Date: 08.04.16
 * Time: 15:41
 */

/** Обязательно к применению */
include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс NIS

/// Устанавливаем объект $oNIS - объект для работы с каталогом Nissan
$oNIS = NIS::instance();

/// Получаем данные с перехода по ссылке из nissan/subgroups.php
$market = $oNIS->rcv('market');
$mark = (stripos(strtolower($market),'inf') > 1)?'infiniti':'nissan';//определяю Марку по рынку
$model  = $oNIS->rcv('model');
$modif  = $oNIS->rcv('modif');
$group  = $oNIS->rcv('group');
$figure = $oNIS->rcv('figure');
$subfig = $oNIS->rcv('subfig');
$sec = $pic  = $oNIS->rcv('sec');
/// Получаем данные
$oIllustration = $oNIS->getNisPic($market,$model,$modif,$group,$figure,$subfig,$sec);

if( ($errors = A2D::property($oIllustration,'errors')) ) {
    if( $errors->msg=="_Nissan_Pic Empty_params" ) $msg = "По данному запросу нет применяемых запчастей";
    if( $errors->msg=="_Nissan_Pic Empty_Result" ) $msg = "По Вашему запросу ничего не найдено";
}else {
    if(!empty($oIllustration)){
        $aBreads = A2D::property($oIllustration, 'aBreads');
        /// "хлебные крошки" можно сразу передать в объект для конструктора
        A2D::$arrActions = ['','','market','model','modif','group','figure']; A2D::$showMark = FALSE;
        $oNIS->constructBreadcrumbs($aBreads,NIS::filename(__FILE__));
        $aDetails = A2D::property($oIllustration, 'details', []);
        $aTabs = A2D::property($oIllustration, 'tabs', []);
        $aTCount = 0; ///Количество закладок ("табов")
        $subFlag = false; /// для определения какого типа будут закладки(у инфинити и остальных по разному)
        $part = A2D::get($_GET, 'part');

        $redirect = TRUE; /// Используется в случае если перешли с поиска
        /**встретил ситуацию когда секция одинаковая, отличается только subgroup */

        //это значит что у нас не 1 фигура, как во многих случаях, а больше 2
        //и закладки по секции уже не определишь? поэтому поделил на подфигуры=>секции
        if (count((array)$aTabs) > 1) $subFlag = true;
        foreach ($aTabs as $key => $tab1) {
            foreach ($tab1 as $tab) {
                $aTCount++;
                if(empty($subfigure)) $subfigure = $tab->figure;
                if(empty($mdldir)) $mdldir = $tab->MDLDIR;
                if($tab->secno == $sec && $aTCount > 1) $redirect = FALSE; /// Если табы совпадут и это не первый таб - редирект уже был
            }
        }
        if(!empty($part) && $aTCount > 1){
            $url = DS.'nissan/illustration.php?market='.$market.'&model='.$model.'&modif='.$modif.'&group='.$group.'&figure='.$figure;
            foreach($aDetails as $key=>$aD){
                foreach($aD as $aDetail) {
                    if($aDetail->number == $part){ $redirect = false; continue; }
                }

                if($redirect) {
                    if ($aTCount == 2) { $tabCnt = 0;
                        foreach ($aTabs as $tab1) {
                            foreach ($tab1 as $tab) {
                                $tabCnt++;
                                if ($tabCnt == 2) $endRedir = '&subfig='.$tab->figure . '&sec=' . $tab->secno;
                            }
                        }
                        $url .= $endRedir . '&part=' . $part;
                    }else{
                        $answ = $oNIS->getNisPicRedirect($market, $mdldir, $subfigure, $part);
                        if(!empty($answ)) {
                            $doNotRed = TRUE;
                            $url .= '&subfig=' . $subfigure . '&sec=' . $answ->sec . '&part=' . $part;
                        }
                    }
                    if(empty($doNotRed)) $oNIS->redirect($url);
                }
            }
        }
        
        
        /// Получаем данные для построение иллюстрации из общего объекта, что вернул сервер:
        $imgInfo = A2D::property($oIllustration, 'imgInfo');    /// Объект:
        $iSID    = A2D::property($imgInfo, 'iSID');          /// Ключ, нужен для построение картинки
        $imgUrl  = A2D::property($imgInfo, 'url');           /// Адрес иллюстрации на сервере
        $width   = A2D::property($imgInfo, 'width');         /// Ширина изображения
        $height  = A2D::property($imgInfo, 'height');        /// Высота изображения
        $attrs   = A2D::property($imgInfo, 'attrs');         /// Те же данные одним атрибутом
        $prc     = A2D::property($imgInfo, 'percent') / 100; /// Ограничения показов с одного агента на IP( показы/100 < 1 = показывать)
        $limit   = A2D::property($imgInfo, 'limit');         /// Ваше число ограничений для отображения пользователю, у которого сработало ограничение

        /// Корневой элемент для зума
        $rootZoom = "imageLayout";

        /// действие для следующего/предыдущего изображения
        $_ACTION = "/nissan/illustration.php";

        /// Адрес для получения информации
        $nextUrl = "/nissan/detailInfo.php?market={$market}&model={$model}&modif=$modif&group=$group&figure=$figure&subfig=";
        /// Адрес для закладок
        $nextSecUrl = "/nissan/illustration.php?market={$market}&model={$model}&modif=$modif&group=$group&figure=$figure&subfig=";
        /// Адрес для перехода к другому изображению
        $secUrl = "/nissan/illustration.php?market={$market}&model={$model}&modif=$modif&group=$group&figure=";

        $markName = urlencode($mark);
        $modelName = urlencode($aBreads->modifs->name);
    }else{
        $msg = "По Вашему запросу ничего не найдено";
    }
}?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">

<? include WWW_ROOT."helpers/illustration.php"; /// Продключаем функции для иллюстрации
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<div id="detailsMap" xmlns:ng="http://www.w3.org/1999/xhtml">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <? if(!empty($aErrors) || !empty($msg)){?>
        <h2 ><?=$msg?></h2>
    <? }else{ ?>

        <div id="tabs">
            <?php $i=0;
            if( $aTCount<=4 ) $widthTabs = 24.5;
            else            $widthTabs = (100/$aTCount)-0.5;
            if($subFlag) $tabsKeys = array_keys((array)$aTabs);
            foreach( $aTabs AS $t ){
                foreach($t as $k=>$tab){ $i++;
                    if($subFlag && $subfig && $pic) $class=($subfig==$tab->figure && $pic==$tab->secno)?" class='activeTab cBlue'":"";
                    else $class=($pic==$tab->secno || (!$subfig && !$pic && $i==1))?" class='activeTab cBlue'":""; ?>
                    <a style="width:<?=$widthTabs?>%" href="<?=$nextSecUrl.$tab->figure.'&sec='.$tab->secno?>"<?=$class?>>Вид <?=$i?> </a>
                    <?php
                    if(empty($subfig) && (int)$k == 1) $currfig = $tab->figure;
                    if(empty($pic)  && (int)$k == 1) $secno = $tab->secno;
                }
                if(empty($currfig)) $currfig = $tab->figure; if(empty($secno)) $secno = $tab->secno; //вдруг пусто, встречал такое
            }
            //для таблицы адрес нужен, а подфигура и секция в другом массиве(не с деталями)
            if(empty($subfig)) $subfig = $currfig; if(empty($pic)) $pic = $secno;
            $nextUrl .=$subfig.'&sec='.$pic;
            ?>
        </div>

        <?php $px = 5; $py = 5; ?>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <div class="defBorder imgArea mb30" id="imageArea">
            <?php if( $prc<1 ){?>
                <div class="isLimit">Превышен лимит показов в сутки (<?=$limit?>)</div>
            <?php }?>
            <div id="imageLayout" style="position:absolute;left:0;top:0;width:<?=$width?>px;height:<?=$height?>px">
                <canvas id="canvas" <?=$attrs?>style="margin:0;padding:0;"></canvas>
                <?php
                $prevNamber = FALSE;
                foreach($aDetails as $aDetail) {
                    foreach ($aDetail AS $_v) {
                        /** у инфинити бывает такое, что на последнем уровне с изобр есть еще "секция"*/
                        $title = (strtoupper($market) == 'JP' || empty($_v->desc_en)) ? $_v->number : "$_v->number - $_v->desc_en";
                        $lLeft = $_v->x1 * $prc - $px * $prc;
                        $lTop = $_v->y1 * $prc - $py * $prc;
                        $lWidth = ($_v->x2 * $prc - $lLeft) + $px * $prc * 2;
                        $lHeight = ($_v->y2 * $prc - $lTop) + $py * $prc * 2 - 10;

                        $currNumber = $_v->number;
                        $number = ($currNumber == $prevNamber) ? $currNumber : $currNumber;
                        $prevNamber = $currNumber;
                        ?>
                        <div id="l<?= $oNIS->repl($number) ?>" class="l<?= $oNIS->repl($number) ?> mapLabel" title="<?= $title ?>"
                             style="
                                 position:absolute;
                                 left:<?= $lLeft ?>px;
                                 top:<?= $lTop ?>px;
                                 min-width:<?= $lWidth ?>px;
                                 min-height:<?= $lHeight ?>px;
                                 padding:<?= $py ?>px <?= $px ?>px;
                                 "
                             onclick="labelClick(this,false)"
                             ondblclick="labelClick(this,true)"
                        >
                        </div>
                    <?php }
                }?>
            </div>
            <? include WWW_ROOT."helpers/zoomer.php"; /// Подключаем функцию панели с зумером ?>
        </div>

        <div id="detailsList">
            <table class="simpleTable">
                <thead>
                <tr>
                    <th class="NisBttnInfo"></th>
                    <th class="NisDetailNumber">Номер детали</th>
                    <th class="NisDetailQuantity">Кол-во</th>
                    <th>Наименование</th>
                </tr>
                </thead>
                <tbody>
                <?php $nc = [];
                foreach($aDetails as $detail) { $detail = (array)$detail;
                    for ($i = 0; $i < count($detail); ++$i) { $_v = $detail[$i];
                        $currNumber = ($prc == 1) ? $_v->number : "*******";
                        $nextNumber = @$detail[($i + 1)]->number;

                        $c = A2D::get($nc, $currNumber);
                        if (!$c) $nc[$currNumber] = 1;
                        else $nc[$currNumber] = $c + 1;

                        if ($currNumber == $nextNumber) continue; //если одинаковый - пропускаем
                        $detailName = $_v->desc_en;
                        $type = $_v->type;
                        ?>
                        <tr>
                            <td colspan="<?= (4 + (int)!$acat) ?>">
                                <table class="innerTable">
                                    <tr id="d<?= $oNIS->repl($currNumber) ?>" data-position="<?= $oNIS->repl($currNumber) ?>"
                                        class="none anime pointer"
                                        ondblclick="trClick(this,1)"
                                        onclick="
                                            trClick(this,0);
                                        <? if ((int)$type == 0){ ?>
                                            clickOnTR('<?= $nextUrl.'&pnc=' . $oNIS->repl($_v->number) ?>','d<?= $oNIS->repl($currNumber) ?>','','<?= $_v->desc_en ?>');
                                        <? } ?>
                                        "
                                    >
                                        <td class="NisBttnInfo">
                                            <? if ((int)$type == 0) {
                                                ?><span class="information anime"
                                                        onclick="bttnClick('<?= $nextUrl.'&pnc=' . $oNIS->repl($_v->number) ?>',this,'<?= $_v->desc_en ?>')">
                                                    i</span><?
                                            } ?>
                                        </td>
                                        <td class="NisDetailNumber <?php if ((int)$type == 1) { ?> c2cValue<?php } ?>"
                                            id="c2cValue_<?= $oNIS->repl($currNumber) ?>">
                                            <? if ((int)$type == 1) { ?>
                                                <?= A2D::callBackLink($oNIS->repl($currNumber), A2D::$callback, ['markName' => $mark]) ?>
                                                <img title="Скопировать" id="c2cBttn_<?=$currNumber?>" src="/media/images/copy_20x20.png" />
                                            <? } else { ?>
                                                <span><?= $currNumber ?></span>
                                            <? } ?>
                                        </td>
                                        <td class="NisDetailQuantity"><?= $nc[$currNumber] ?></td>
                                        <? if ((int)$type != 2) {?>
                                            <td class="tl"><?= $detailName ?></td>
                                        <? } else { ?>
                                            <td class="tl"> <a href="<?= $secUrl . $_v->number ?>">*** <?= $detailName ?></a> </td>
                                        <? } ?>
                                    </tr>
                                    <tr id="response" style="display:none">
                                        <td colspan="<?= (4 + (int)!$acat) ?>">
                                            <table class="w100p"></table>
                                        </td>
                                    </tr>
                                    <?= (!empty($part) && $part == $currNumber) ? "<script>
                                            $(window).load(function() {trClick($('#d" . $oNIS->repl($currNumber) . "'),0)});
                                        </script>" : ''; ?>
                                </table>
                            </td>
                        </tr>
                    <?php }
                }?>
                </tbody>
            </table>
        </div>

        <script>
            var offline = '<?=A2D::$offline?>',
                isLimit = '<?=($prc<1)?TRUE:FALSE;?>',
                callback = '<?=A2D::$callback?>'
                ;

            function clickOnTR(url,tr,detail){
                if( !jQueryA2D(tr).next('.response').hasClass('response') ){
                    var bttn = jQueryA2D(tr).find('span.information');
                    bttnClick(url,bttn,detail);
                }
            }

            function bttnClick(url,bttn,detail){
                jQueryA2D.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json"
                }).done(function( r ){
                    ///console.log('Response:'); console.log(r);
                    jQueryA2D(bttn).removeAttr('onclick');
                    var innerTable = jQueryA2D(bttn).parents('.innerTable'),
                        responseTR = innerTable.find('#response'),
                        response   = responseTR.find('td table'),
                        msg
                        ;
                    if( !r ){
                        bttn.remove();
                        msg = ""+
                            "<div class='noResponse'>"+
                            "   <span class='red'>Не применяется</span>"+
                            "</div>"+
                            "";
                        response.html( msg );
                        responseTR.addClass('response').show(500);
                    }
                    else{

                        msg = ""+
                            "<tr>"+
                            "   <th>Номер детали</th>"+
                            "   <th>Дата</th>"+
                            "   <th>Применяемость</th>"+
                            "</tr>"+
                            "";

                        $.each( r, function( k, v ){
                            var analog  = v.analog;
                            var serial = ( !isLimit ) ? v.serialNumber :"*******";
                            if( !offline ){
                                var callBackLink = createCallBackLink(serial,callback);
                                msg += ""+
                                    "<tr>"+
                                    "   <td id=\"c2cValue_"+ serial +"\" class=\"c2cValuePNC\">"+
                                    "       "+ callBackLink +
                                    "   </td>"+
                                    "   <td>"+ v.Date +"</td>"+
                                    "   <td>"+ v.analog +"</td>"+
                                    "</tr>"+
                                    "";
                            }else{
                                var url = offline+"&number="+serial+"&detail="+detail;
                                msg += ""+
                                    "<tr>" +
                                    "   <td id='"+ serial +"'>"+ Offline.Instance().getLink( url, serial ) +"</td>"+
                                    "   <td>"+ v.quantity +"</td>"+
                                    "   <td>"+ v.prodaction +"</td>"+
                                    "   <td>"+ dAnalog +"</td>"+
                                    "</tr>"+
                                    "";
                            }

                        });

                        response.html( msg );
                        if( !offline ) responseTR.addClass('response').show(500).setC2C('class','c2cValuePNC');
                        else           responseTR.addClass('response').show(500);
                    }
                } ).error(function(e){
                } ).fail(function(e){
                });

            }

            function createCallBackLink(name,callback){
                var r;
                if( callback ){
                    var _callBack = callback.replace(/{{DetailNumber}}/g,name);
                    r = "<a target='_blank' href='"+_callBack+"'><span class='c2c'>"+name+"</span></a>";
                }
                else{
                    r = "<span class='c2c'>"+name+"</span>";
                }
                return r;
            }
        </script>
    <? } ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
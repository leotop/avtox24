<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php

include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс Fiat

$oFiat = new Fiat();

$brand = $oFiat->rcv('brand');
$model = $oFiat->rcv('model');
$production = $oFiat->rcv('production');
$group = $oFiat->rcv('group');
$subGroup = $oFiat->rcv('subGroup');
$tableCod = urldecode(base64_decode($oFiat->rcv('tableCod')));

$parts = $oFiat->getFIATPartDrawData($production, $group, $subGroup, $tableCod);
$variant = $oFiat->rcv('variant', $parts->partDrawData->variants[0]->variante);
$res = $oFiat->getFIATDraw($brand, $model, $production, $group, $subGroup, $tableCod, $variant, 0.5);

$aDetails = A2D::property($res, 'draw', []);

/// Получаем данные для построение иллюстрации из общего объекта, что вернул сервер:
$imgInfo = A2D::property($res, 'imgInfo'); /// Объект:
$aLabels = A2D::property($imgInfo, 'labels', []);
$aComments = A2D::property($imgInfo, 'comments', []);
$iSID = A2D::property($imgInfo, 'iSID');        /// Ключ, нужен для построение картинки
$imgUrl = A2D::property($imgInfo, 'url');         /// Адрес иллюстрации на сервере
$width = A2D::property($imgInfo, 'width');       /// Ширина изображения
$height = A2D::property($imgInfo, 'height');      /// Высота изображения
$attrs = A2D::property($imgInfo, 'attrs');       /// Те же данные одним атрибутом
$prc = A2D::property($imgInfo, 'percent')/100; /// Коэффициент в каком соотношение вернулась иллюстрация, нужно для ограничения показов с одного агента на IP
$limit = A2D::property($imgInfo, 'limit');       /// Ваше число ограничений для отображения пользователю, у которого сработало ограничение

$rootZoom = "imageLayout";

foreach( $parts->partDrawData->variants as $pd ){
    $tabs[] = [
        'url' => "/fiat/draw.php?brand=$brand&model=$model&production=$production&group=$group&subGroup=$subGroup&tableCod=".urlencode(base64_encode($tableCod))."&variant=".$pd->variante,
        'description' => $pd->variante
    ];
}

if( ($aErrors = A2D::property($res,'errors')) ) $res->error($aErrors,404);

A2D::$arrActions = ['brand','model', 'production', 'group', 'subGroups']; A2D::$showMark = FALSE;
Fiat::constructBreadcrumbs($res->meta->breadcrumbs);
?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fiat.css" media="all" rel="stylesheet" type="text/css">

<?php include WWW_ROOT."helpers/illustration.php"; /// Подключаем функции для иллюстрации?>
<div class="fiat_draw" id="detailsMap">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <div class="row">

        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

            <?php if(count($tabs) > 1):?>

                <div id="tabs">
                    <?php
                    $count = count($tabs);
                    if( $count <= 4 ){
                        $widthTabs = 23;
                    }else {
                        $widthTabs = 100 / $count - 1;
                    }

                    foreach( $tabs AS $t ):?>
                        <a <?php if($res->meta->variante == $t['description']):?>class="activeTab cBlue"<?php endif?> style="width:<?=$widthTabs?>%" href="<?=$t['url']?>">
                            Вид <?=$t['description']?>
                        </a>
                    <?php endforeach?>
                </div>

            <?php endif?>

            <div class="defBorder imgArea mb30" id="imageArea">
                <?php if( $prc<1 ){ $zoom=$prc; ?>
                    <div class="isLimit">Превышен лимит показов в сутки (<?=$limit?>)</div>
                <?php }else $zoom=1 ?>
                <div id="imageLayout" style="position:absolute;left:0;top:0;width:<?=$width?>px;height:<?=$height?>px">
                    <canvas id="canvas" width="<?=$width?>" height="<?=$height?>" style="margin:0;padding:0;"></canvas>
                    <?php foreach( $aLabels AS $_v ): ?>
                        <div id="l<?=$_v->label?>"
                             class="l<?=$_v->label?> mapLabel"
                             style="
                                 position:absolute;
                                 left:<?=$_v->topX?>px;
                                 top:<?=$_v->topY?>px;
                                 min-width:<?=$_v->width?>px;
                                 min-height:<?=$_v->height?>px;
                                 "
                             onclick="labelClick(this,false)"
                             ondblclick="labelClick(this,true)"
                            >
                        </div>
                    <?php endforeach ?>
                </div>
                <?php include WWW_ROOT."helpers/zoomer.php"; /// Подключаем функцию панели с зумером?>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="relative">
                <table width="100%" class="defTable">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Код</th>
                        <th>Описание</th>
                        <th>Применяемость</th>
                        <th>Модификация</th>
                        <th>Кол-во</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach( $aDetails AS $p ):?>
                        <tr id="d<?=$p->tbd_rif?>" data-position="<?=$p->tbd_rif?>"
                            onclick = "trClick(this, 0)"
                            ondblclick = "trClick(this, 1)"
                            >
                            <td><b><?=$p->tbd_rif?></b></td>
                            <td class="c2cValue text-right" id="c2cValue_<?=$p->tbd_rif?>">
                                <?=A2D::callBackLink($p->prt_cod,A2D::$callback)?>
                                &ensp;<img title="Скопировать" id="c2cBttn_<?=$p->prt_cod?>" src="/media/images/copy_20x20.png">
                            </td>
                            <td><?=$p->cds_dsc?></td>
                            <td><?=$p->tbd_val_formula?></td>
                            <td><?=$p->modif?></td>
                            <td><?=$p->tbd_qty?></td>
                        </tr>
                    <?php endforeach?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php if(isset($aDetails->meta->patterns)):?>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="explanation mt40">
                    <div class="expTable">
                        <div class="eTableHead">Расшифровка сокращений</div>
                        <div class="eTableBody">
                            <?php foreach( $aDetails->meta->patterns as $pattern ):?>
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
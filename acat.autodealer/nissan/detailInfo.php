<?php
/**
 * Created by PhpStorm.
 * User: lans
 * Date: 11.04.16
 * Time: 17:00
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
$sec    = $oNIS->rcv('sec');
$pnc    = $oNIS->rcv('pnc');

/// Запрашиваем информацию о детали согласно переданным критериям
$aPic = $oNIS->getNisPnc($market,$model,$modif,$group,$figure,$subfig,$sec,$pnc);

/// Получаем из общего свойства объекта
$aDetInfo  = A2D::property($aPic,'detInfo');

/// "Хлебные крошки" тоже есть, но сейчас мы делаем запрос через AJAX

/// Если ничего не вернулось, сообщаем
if( !$aDetInfo ) die( json_encode("NULL REQUEST") );
/// При результате отдаем все в виде JSON строки
die( json_encode($aDetInfo) );
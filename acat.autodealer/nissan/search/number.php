<?php
/**
 * Created by PhpStorm.
 * User: lans
 * Date: 14.04.16
 * Time: 13:53
 */
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
/** Обязательно к применению */
include "../../_lib.php"; /// После подключения доступен класс A2D
include "../api.php";     /// После подключения доступен класс NIS

/// Устанавливаем объект $oNIS - объект для работы с каталогом Nissan
$oNIS = NIS::instance(); $mark = A2D::get($_GET,'mark');
$number = A2D::get($_GET,'number'); $market = A2D::get($_GET,'market');
$model = A2D::get($_GET,'model');   $modif = A2D::get($_GET,'modif');

if(!A2D::ajaxRequest()) $ajaxRequest = FALSE; /// для теста можно поставить TRUE

if(!empty($model) && !empty($modif) && (A2D::ajaxRequest() || $ajaxRequest)){  ///Level 2
    $search = $oNIS->searchNISNumber($number,A2D::get($_GET,'market'),$model,$modif);
    $modelModifs  = A2D::property($search,'groups');
    if( !$modelModifs ) $oNIS->jEcho(FALSE);
    $oNIS->jEcho($modelModifs);

}elseif(!empty($model) && (A2D::ajaxRequest() || $ajaxRequest)){              ///Level 1
    $search = $oNIS->searchNISNumber($number,A2D::get($_GET,'market'),$model);
    $modelModifs  = A2D::property($search,'models');
    if( !$modelModifs ) $oNIS->jEcho(FALSE);
    $oNIS->jEcho($modelModifs);
}

$url = "/nissan/search/number.php?fromNumber=1&mark=$mark&number=";
$allMarkets = (array)$oNIS->getNisMarkets('');
foreach($allMarkets as $key=>$market)   $markets[] = $key;
/// Ошибки на случай неверного ввода
if(empty($number)) $msg[] = 'Укажите номер';
if(empty($market)) $msg[] = 'Выберите страну';
if(!empty($number) && (strlen($number) < 3 || strlen($number) > 12))     $msg[] = 'Длинна номера должна быть 3-12 символа';

/// Получаем данные если нет ошибок
if(empty($msg)) $searchRezult = $oNIS->searchNISNumber($number,A2D::get($_GET,'market'));
/// Хлебные крошки
A2D::$aBreads = A2D::toObj([
    'types' => [
        "name" => 'Каталог',
        "breads" => []
    ],
    'marks' => [
        "name" => 'Легковые (иномарки)',
        "breads" => [ 0 => '&typeID='.NIS::$_typeID ]
    ],
    'nissan/markets' => [
        'name' => strtoupper($mark),
        'breads' => [
            0 => '&mark='.strtoupper($mark)
        ]
    ],
    'vin' => [
        'name' => 'Поиск по номеру',
        'breads' =>[]
    ]
]);
A2D::$catalogRoot = "";A2D::$showMark = FALSE;
//$oNIS->e($searchRezult);exit;
if( ($errors = A2D::property($searchRezult,'errors')) ) {
    if( $errors->msg=="_Nissan_Search_VIN VIN_Empty" )      $msg = "Не указан VIN";
    if( $errors->msg=="_Nissan_Search_VIN Empty_Response" ) $msg = "По Вашему запросу ничего не найдено";
}else {
    $aTree = A2D::property($searchRezult,'models',[]); //$this->e($aRezult);
    $about = A2D::property($searchRezult,'about',[]); //$this->e($aRezult);

//    $oNIS->e($aTree);exit;
    if(empty($aTree)) $msg = "Результаты не найдены";
    if(!empty($aTree) && ($about->count*1 == 1)) {
        $url = '/nissan/illustration.php?market='.strtoupper(A2D::get($_GET,'market')).'&model=';
        foreach ($aTree as $model) {
            $url .= $model->name.'&modif=';unset($model->name);
            foreach($model as $key=>$mdl){ $a = '0';
                $url .= $key.'&group='.$mdl->$a->group.'&figure='.$mdl->$a->figure.'&subfig='.$mdl->$a->subfig.'&sec='.$mdl->$a->secno.'&part='.urlencode($mdl->$a->partcode);
            }
        }
        $oNIS->redirect($url);
    }
}
if(!empty($_GET['market'])){ $view = (strtolower($_GET['market']) == 'jp')?"index_jp":'index';}
include_once "view/number/$view.php";     /// Подключаю отображение
?>
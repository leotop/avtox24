<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * User: lans
 * Date: 08.04.16
 * Time: 11:00
 */
/** Обязательно к применению */
include "../_lib.php"; /// После подключения доступен класс A2D
include "api.php";     /// После подключения доступен класс NISSAN
/// Устанавливаем объект $oNIS - объект для работы с каталогом Nissan
$oNIS = NIS::instance();
/// Получаем марку из адреса, дописав ?mark=nissan ИЛИ ?mark=infiniti
$markName = $oNIS->rcv('mark');
$mark = $oNIS->rcv('mark');
$mark = $oNIS->rcv('mark');
/// Получаем доступные рынки. Второй строкой останавливаемся при ошибках с сервера
$NISMarkets = $oNIS->getNisMarkets($mark);

if( ($aErrors = A2D::property($NISMarkets,'errors')) ) $oNIS->error($aErrors,404);
/// "Хлебные крошки" не родные - изменяем ассоциативный массив для имен под переменные

/// На точки входа нет переменных для крошек, строим их самостоятельно
A2D::$aBreads = A2D::toObj([
	'types' => [
		"name" => 'Каталог',
		"breads" => []
	],
	'marks' => [
		"name" => 'Легковые (иномарки)',
		"breads" => [ 0 => NIS::$_typeID ]
	],
	'markets' => [
		'name' => strtoupper($mark),
		'breads' => [
			0 => $mark
		]
	]
]);
A2D::$arrActions = ['typeID']; A2D::$catalogRoot = "";

/// Базовая часть пути для переходя на следующий этап
$url = "/nissan/models.php?mark={$mark}&market=";
?>

<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>



<div id="NISCatalog" class="AutoDealer">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

    <?php include WWW_ROOT."helpers/search.php"; /// Подключаем форму поиска?>
	
	<div class="catalogMarkets mb20">
		<ul>
			<?php
			foreach( $NISMarkets AS $k=>$v ){ ?>
				<li class="fl">
				<a href="<?=$url?><?=strtoupper($k)?>">
					<img src="/media/images/nissan/markets/<?=strtolower($k)?>.png" alt="" class="mb5"/><br />
					<?=$v?>
				</a>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * Обязательно к применению
 */
include "../_lib.php";
include "api.php";

$mcct		= MCCT::instance();						// Библиотека каталога
$mark		= $mcct->rcv('mark');					// Параметр 'МАРКА' запроса серверу
$type		= $mcct->rcv('type');					// Параметр 'ТИП' запроса серверу
$region	= $mcct->rcv('region','cis');	// Параметр 'РЕГИОН' запроса серверу

$server	=	$mcct->getMcctIndex($type,$region); // Обращение к серверу

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);

$mcct->addSearch($server);
?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>

<style>
.mcct-flag .fl a.inactive img{
    filter: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\'><filter id=\'grayscale\'><feColorMatrix type=\'matrix\' values=\'0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0\'/></filter></svg>#grayscale");
    filter: gray alpha(opacity=100); /* IE6-9 */
    -webkit-filter: grayscale(100%); /* Chrome 19+ & Safari 6+ */
    -webkit-transition: all .6s ease; /* Fade to color for Chrome and Safari */
    -webkit-backface-visibility: hidden; /* Fix for transition flickering */
}

.mcct-flag .fl a.inactive:hover img{
	filter: none;
	-webkit-filter: grayscale(0%);
}
</style>
<div class="container-fluid">

	<?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

	<?php include WWW_ROOT."helpers/search.php"; /// Подключаем форму поиска?>

	<div class="catalogMarkets mcct-flag">
		<ul>
<?php foreach($server->regions as $i=>$v){ ?>
			<li class="fl pv30">
				<a href="<?=$mcct->urlMcct('index',['mark'=>$mark,'type'=>$type,'region'=>strtolower($i)])?>" class="<?=($i==$server->region?'active':'inactive')?>">
					<img src="/media/images/mcct/region/<?=strtolower($i)?>.png" alt="<?=$v?>">
					<div class="mt15"><?=$v?></div>
				</a>
			</li>
<?php } ?>
		</ul>
	</div>

	<h1>Модельный ряд</h1>

	<div class="row">
<?php foreach((array)$server->family as $i=>$v){ ?>
			<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 mb10">
				<a href="<?=$mcct->urlMcct('catalogs',['mark'=>$mark,'type'=>$type,'region'=>strtolower($region),'family'=>$i])?>" class="btn btn-block btn-lg btn-default">
					<?=$v?>
				</a>
			</div>
<?php } ?>
	</div>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
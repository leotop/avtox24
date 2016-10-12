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
$region	= $mcct->rcv('region');				// Параметр 'РЕГИОН' запроса серверу
$family	= $mcct->rcv('family');				// Параметр 'МОДЕЛЬНЫЙ РЯД' запроса серверу
$catalog= $mcct->rcv('catalog');			// Параметр 'КАТАЛОГ' запроса серверу
$model	= $mcct->rcv('model');				// Параметр 'МОДЕЛЬ' запроса серверу
$vin		= $mcct->rcv('vin');					// Параметр 'VIN' запроса серверу (необязательный)
$major	= $mcct->rcv('major');				// Параметр 'КАТЕГОРИЯ' запроса серверу

$server	=	$mcct->getMcctMajor($type,$region,$family,$catalog,$model,$vin,$major); // Обращение к серверу

$vinName= $server->model->model.($vin?' (VIN:'.$vin.')':'');

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBreadFamily();
$mcct->addMcctBread('models',$server->catalog->cat_name,[$mark,$type,$region,$family,$catalog]);
$mcct->addMcctBread('model',$vinName,[$mark,$type,$region,$family,$catalog,$model,$vin]);
$mcct->addMcctBread('major',$server->major->lex_desc,[$mark,$type,$region,$family,$catalog,$model,$vin,$major]);

$params=['mark'=>$mark,'type'=>$type,'region'=>$region,'family'=>$family,'catalog'=>$catalog,'model'=>$model,'vin'=>$vin,'major'=>$major];
$title=MCCT::txtMcctFamilyName($family);
?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/holder.min.js"></script>

<div class="container-fluid">

	<?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

	<div class="text-center">
		<img src="<?=$server->catalog->image?>" data-src="holder.js/150x80?text=<?=$title?>" alt="<?=$title?>" class="img-rounded">
		<img src="<?=$server->major->image?>" data-src="holder.js/150x80?text=<?=$major?>" alt="<?=$major?>" height="80">
	</div>

	<h1>Категория: <?=$server->major->lex_desc?></h1>
	<?php if($vin){?><h1>VIN: <?=$vin?></h1><?php }?>
	<h1>Модель: <?=$server->model->model?></h1>
	<h2>Каталог: <?=$server->catalog->cat_name?></h2>

	<h1>Категории:</h1>

	<div class="catalogMarkets">
		<ul>
<?php
foreach($server->table as $i=>$v)
{
	$param=$params;
	$param['minor']=$v->minor_sect;
	$url=$mcct->urlMcct('minor',$param);
?>
			<li class="fl pv30" style="width:150px; height:230px;">
				<a href="<?=$url?>">
					<img src="<?=$v->image?>" data-src="holder.js/140x110?text=<?=$v->minor_sect?>" alt="<?=$v->minor_desc?>" height="110">
					<br>
					<span>
						<?=$v->sector_format?>
						<?=$v->sector_part?>
						<?=str_replace('.','. ',$v->minor_desc)?>
					</span>
				</a>
			</li>
<?php }?>
		</ul>
	</div>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
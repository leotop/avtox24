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
$vin		= $mcct->rcv('vin');					// Параметр 'VIN' запроса серверу (может быть пустым)

$server	=	$mcct->getMcctModel($type,$region,$family,$catalog,$model,$vin); // Обращение к серверу

$ucc		= json_decode(json_encode($server->ucc),true);
$uccM		= json_decode(json_encode($server->model->ucc),true);
$vinName= $server->model->model.($vin?' (VIN:'.$vin.')':'');

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBreadFamily();
$mcct->addMcctBread('models',$server->catalog->cat_name,[$mark,$type,$region,$family,$catalog]);
$mcct->addMcctBread('model',$vinName,[$mark,$type,$region,$family,$catalog,$model,$vin]);

$params=['mark'=>$mark,'type'=>$type,'region'=>$region,'family'=>$family,'catalog'=>$catalog,'model'=>$model,'vin'=>$vin];
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
		<img src="<?=$server->catalog->image?>" data-src="holder.js/300x200?text=<?=$title?>" alt="<?=$title?>" height="200" class="img-rounded">
	</div>

	<?php if($vin){?><h1>VIN: <?=$vin?></h1><?php }?>
	<h1>Модель: <?=$server->model->model?></h1>
	<h2>Каталог: <?=$server->catalog->cat_name?></h2>

	<div class="text-center">
		<table align="center">
			<tbody>
<?php if($vin){ ?>
				<tr>
					<td class="text-right"><b>VIN:</b></td>
					<td class="text-left">&nbsp;<?=$vin?></td>
				</tr>
<?php } ?>
<?php foreach($uccM as $i=>$v){ if(empty($ucc[$i])||empty($ucc[$i][$v])){continue;} ?>
				<tr>
					<td class="text-right"><b><?=$ucc[$i][$v]['name']?>:</b></td>
					<td class="text-left">&nbsp;<?=$ucc[$i][$v]['value']?></td>
				</tr>
<?php } ?>
			</tbody>
		</table>
	</div>

	<h1>Категории:</h1>

	<div class="catalogMarkets">
		<ul>
<?php
foreach($server->table as $i=>$v)
{
	$param=$params;
	$param['major']=$v->major_sect;
	$url=$mcct->urlMcct('major',$param);
?>
			<li class="fl pv30" style="width:170px;">
				<a href="<?=$url?>">
					<img src="/media/images/mcct/blank.gif" width="166" height="129" style="background:url(<?=$v->image?>) no-repeat center center;">
					<br>
					<span><?=$v->lex_desc?></span>
				</a>
			</li>
<?php }?>
		</ul>
	</div>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
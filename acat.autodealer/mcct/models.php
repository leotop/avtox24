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

$server	=	$mcct->getMcctModels($type,$region,$family,$catalog); // Обращение к серверу
$cat_name = $server->catalog->cat_name;
$ucc		= json_decode(json_encode($server->ucc),true);

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBreadFamily();
$mcct->addMcctBread('models',$cat_name,[$mark,$type,$region,$family,$catalog]);

$title=MCCT::txtMcctFamilyName($family);
?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/holder.min.js"></script>
<script type="text/javascript" src="../media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../media/js/dataTable.js"></script>

<div class="container-fluid">

	<?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

	<h1><?=$server->catalog->cat_name?></h1>
	<h2>Каталог: <?=$catalog?><?=$server->catalog->cat_name?></h2>
	<div class="text-center">
		<img src="<?=$server->catalog->image?>" data-src="holder.js/150x100?text=<?=$title?>" alt="<?=$title?>" class="img-rounded">
	</div>

	<h1>Модели каталога: <?=$cat_name?></h1>

	<table id="dataTable" class="dataTable">
		<thead>
			<tr>
				<th>Модель</th>
				<th nowrap>Период пр-ва</th>
<?php
$uccrow=[];
foreach($ucc as $i=>$arr)
{
	$name='';
	$empty=true;

	foreach($server->table as $v)
	{
		$u=json_decode(json_encode($v->ucc),true);

		if(!empty($u[$i]))
			$empty=false;
	}

	if($empty)
		continue;

	$uccrow[$i]=$i;

	foreach($arr as $um)
	{
		$name=$um['name'];
		break;
	}
?>
				<th><?=$name?></th>
<?php } ?>
			</tr>
		</thead>
		<tbody>
<?php
foreach($server->table as $i=>$v)
{
	$url=$mcct->urlMcct('model',['mark'=>$mark,'type'=>$type,'region'=>$region,'family'=>$family,'catalog'=>$catalog,'model'=>$v->model]);
	$yearFrom=($v->date_from?preg_replace('/(.{4})(.{2})(.{2})/','$3.$2.$1',$v->date_from):'-');
	$yearTo=($v->date_to?preg_replace('/(.{4})(.{2})(.{2})/','$3.$2.$1',$v->date_to):'по н.в.');
?>
			<tr onclick="window.location.href='<?=$url?>';">
				<td><a href="<?=$url?>"><?=$v->model?></a></td>
				<td nowrap>
					<div><?=$yearFrom?></div>
					<div><?=$yearTo?></div>
				</td>
	<?php
	$u=json_decode(json_encode($v->ucc),true);

	foreach($uccrow as $k)
	{
		$value='-';

		if(isset($u[$k])&&isset($ucc[$k][$u[$k]]))
			$value=$ucc[$k][$u[$k]]['value'];
	?>
				<td><?=$value?></td>
	<?php } ?>
			</tr>
<?php }?>
		</tbody>
	</table>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
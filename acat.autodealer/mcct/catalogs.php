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

$server	=	$mcct->getMcctCatalogs($type,$region,$family); // Обращение к серверу

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBreadFamily();

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

	<h1>Каталоги</h1>

	<table id="dataTable" class="dataTable">
		<thead>
			<tr>
				<th>Фото</th>
				<th>Модельный ряд</th>
				<th>Период пр-ва</th>
			</tr>
		</thead>
		<tbody>
<?php
foreach( $server->table as $i=>$v )
{
	$url=$mcct->urlMcct('models',['mark'=>$mark,'type'=>$type,'region'=>$region,'family'=>$family,'catalog'=>$v->catalogue_code]);
?>
			<tr onclick="window.location.href='<?=$url?>';">
				<td style="padding:0px;"><a href="<?=$url?>"><img src="<?=$v->image?>" data-src="holder.js/100px50?text=<?=$title?>" alt="<?=$title?>"></a></td>
				<td width="80%"><a href="<?=$url?>"><?=$v->cat_name?></a></td>
				<td width="20%" class="text-center"><?=($v->from_year?:'')?> - <?=($v->to_year?:'по н.в.')?></td>
			</tr>
<?php }?>
		</tbody>
	</table>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
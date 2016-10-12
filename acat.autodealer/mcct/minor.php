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
$minor	= $mcct->rcv('minor');				// Параметр 'ПОДКАТЕГОРИЯ' запроса серверу

$server	=	$mcct->getMcctMinor($type,$region,$family,$catalog,$model,$vin,$major,$minor); // Обращение к серверу

$vinName= $server->model->model.($vin?' (VIN:'.$vin.')':'');
$ucc		= json_decode(json_encode($server->ucc),true);
$option	=	json_decode(json_encode($server->option),true);
$color	=	json_decode(json_encode($server->color),true);

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBreadFamily();
$mcct->addMcctBread('models',$server->catalog->cat_name,[$mark,$type,$region,$family,$catalog]);
$mcct->addMcctBread('model',$vinName,[$mark,$type,$region,$family,$catalog,$model,$vin]);
$mcct->addMcctBread('major',$server->major->lex_desc,[$mark,$type,$region,$family,$catalog,$model,$vin,$major]);
$mcct->addMcctBread('minor',$server->minor->minor_desc,[$mark,$type,$region,$family,$catalog,$model,$vin,$major,$minor]);

$params=['mark'=>$mark,'type'=>$type,'region'=>$region,'family'=>$family,'catalog'=>$catalog,'model'=>$model,'vin'=>$vin,'major'=>$major,'minor'=>$minor];
$title=MCCT::txtMcctFamilyName($family);

/// Метки на иллюстрации
$aLabels = A2D::property($server,'labels',[]);
/// Список номенклатуры к изображению
$aDetails = A2D::property($server,'details',[]);
/// Получаем данные для построение иллюстрации из общего объекта, что вернул сервер:
$imgInfo= A2D::property($server,'imgInfo');				/// Объект:
$iSID		= A2D::property($imgInfo,'iSID');					/// Ключ, нужен для построение картинки
$imgUrl	= A2D::property($imgInfo,'url');					/// Адрес иллюстрации на сервере
$width	= A2D::property($imgInfo,'width');				/// Ширина изображения
$height	= A2D::property($imgInfo,'height');				/// Высота изображения
$attrs	= A2D::property($imgInfo,'attrs');				/// Те же данные одним атрибутом
$percent= A2D::property($imgInfo,'percent')/100;	/// Коэффициент в каком соотношение вернулась иллюстрация, нужно для ограничения показов с одного агента на IP
$limit	= A2D::property($imgInfo,'limit');				/// Ваше число ограничений для отображения пользователю, у которого сработало ограничение
/// Корневой элемент для зума
$rootZoom = "imageLayout";

$cnt1	=[];
$cnt2	=0;
$uid	=0;
?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../media/js/holder.min.js"></script>
<?php include WWW_ROOT."helpers/illustration.php"; /// Продключаем функции для иллюстрации?>

<div class="container-fluid">
	<?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>
	<h1>Иллюстрация: <?=$server->minor->minor_desc?></h1>
</div>

<div class="container-fluid" id="detailsMap">
	<!-- Illustration -->
	<div class="defBorder imgArea mb30" id="imageArea">
		<? if( $percent<1 ){ $zoom=$percent; ?>
			<div class="isLimit">Превышен лимит показов в сутки (<?=$limit?>)</div>
		<? }else $zoom=1 ?>
		<div id="imageLayout" style="position:absolute;left:0;top:0;width:<?=$width?>px;height:<?=$height?>px">
			<canvas id="canvas" width="<?=$width?>" height="<?=$height?>" style="margin:0;padding:0;"></canvas>
			<? foreach( $aLabels AS $_v ){ $cnt1[$_v->label]=$_v->label; ?>
				<div id="l<?=$_v->label?>" title="<?=$_v->name?>"
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
			<? } ?>
		</div>
		<?php include WWW_ROOT."helpers/zoomer.php"; /// Подключаем функцию панели с зумером?>
	</div>
	<div id="detailsList">
		<table class="simpleTable innerTable">
			<thead>
				<tr>
					<th nowrap>№</th>
					<th nowrap>PNC</th>
					<th nowrap>Коды</th>
					<th nowrap>Описание</th>
					<th nowrap>Кол-во</th>
					<th nowrap>Руль</th>
					<th nowrap>Дата пр-ва</th>
				</tr>
			</thead>
			<tbody>
			<?
			foreach((array)$server->table as $pnc=>$data)
			{
				$cnt2++;

				foreach((array)$data->table as $number=>$part)
				{
					$uid++;
					$id=$part->ref;
					$from=($part->production_from?preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})/si','$3.$2.$1',$part->production_from):'');
					$to=($part->production_to?preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})/si','$3.$2.$1',$part->production_to):'по н.в.');
					$part->ucc=json_decode(json_encode($part->ucc),true);
					$part->option=json_decode(json_encode($part->option),true);
					$part->color=json_decode(json_encode($part->color),true);
					$info=($part->ucc||$part->option||$part->minus||$part->color['ext']||$part->color['int']);
			?>
				<tr id="d<?=$id?>" data-position="<?=$id?>"
					onclick = "trClick(this, 0)"
					ondblclick = "trClick(this, 1)"
				>
					<td nowrap><b><?=$id?></b></td>
					<td nowrap><?=$pnc?></td>
					<td class="text-right" nowrap>
						<div class="c2cValue" id="c2cValue_<?=$number?>" title="<?=$part->name_desc?>" nowrap>
							<?=MCCT::callBackLink($number,MCCT::$callback)?>
							&ensp;<img title="Скопировать" id="c2cBttn_<?=$number?>" src="/media/images/copy_20x20.png">
						</div>
					</td>
					<td class="text-left">
						<div>
						<? if($info){ ?>
							<a href="#part-ucc-<?=$uid?>" class="information anime text-center" onclick="$('#part-ucc-<?=$uid?>').slideToggle('fast'); return false;" style="float:right;">i</a>
						<? } ?>
							<?=$part->name_desc?>
						</div>
						<? if($info){ ?>
						<div id="part-ucc-<?=$uid?>" style="display:none;">
							<? if($part->ucc){ ?>
							<div class="small text-danger">Комплектация:</div>
							<? foreach($part->ucc as $iu=>$vu){ if(empty($ucc[$iu])||empty($ucc[$iu][$vu])){ ?>
							<div class="small" title="[<?=$iu?>:<?=$vu?>]">???</div>
							<? }else{ ?>
							<div class="small" title="Комплектация - <?=$ucc[$iu][$vu]['name']?>: <?=$ucc[$iu][$vu]['value']?>"><b><?=$ucc[$iu][$vu]['name']?>:</b> <?=$ucc[$iu][$vu]['value']?></div>
							<? }}} ?>
							<? if($part->option){ ?>
							<div class="small text-danger">Опции авто:</div>
							<? foreach($part->option as $vu){ ?>
							<div class="small" title="[<?=$vu?>]">(<?=(empty($option[$vu])?'?':$option[$vu]['name1'])?>)</div>
							<? }} ?>
							<? if($part->minus){ ?>
							<div class="small text-danger">Несовместим с опциями:</div>
							<? foreach($part->minus as $vu){ ?>
							<div class="small text-danger" title="[<?=$vu?>]"><b>(<?=(empty($option[$vu])?'?':$option[$vu]['name1'])?>)</b></div>
							<? }} ?>
							<? if($part->color['ext']){ ?>
							<div class="small text-danger">Цвет экстерьера:</div>
							<? foreach($part->color['ext'] as $vu){ ?>
							<div class="small" title="[<?=$vu?>]">(<?=(empty($color['ext'][$vu])?'?':$color['ext'][$vu]['name_up'].' / '.$color['ext'][$vu]['name_down'])?>)</div>
							<? }} ?>
							<? if($part->color['int']){ ?>
							<div class="small text-danger">Цвет интерьера:</div>
							<? foreach($part->color['int'] as $vu){ ?>
							<div class="small" title="[<?=$vu?>]">(<?=(empty($color['int'][$vu])?'?':$color['int'][$vu]['name'])?>)</div>
							<? }} ?>
						</div>
						<? } ?>
					</td>
					<td class="text-center">
						<?=$part->quantity?>
					</td>
					<td class="text-center">
						<? if(isset($part->ucc['DT'])&&in_array($part->ucc['DT'],['L','R'])){ ?>
							<?=($part->ucc['DT']=='L'?'Левый':'Правый')?>
						<? }else{ ?>
							-
						<? } ?>
					</td>
					<td class="text-center" nowrap>
						<?=$from?> - <?=$to?>
					</td>
				</tr>
			<?
				}
			}

			foreach((array)$server->sector as $i=>$v)
			{
				$cnt2++;
				$id=$v->sector_format;
				$param=$params;
				$param['major']=$v->major_sect;
				$param['minor']=$v->minor_sect;
				$name=mb_convert_case($v->minor_desc,MB_CASE_TITLE,'UTF-8');
				$url=$mcct->urlMcct('minor',$param);
			?>
				<tr id="d<?=$id?>" data-position="<?=$id?>"
					onclick = "trClick(this, 0)"
					ondblclick = "trClick(this, 1)"
				>
					<td nowrap><b><?=$id?></b></td>
					<td colspan="2">
						<img src="<?=$v->image?>" data-src="holder.js/125x75?text=<?=$v->minor_sect?>" alt="<?=$name?>" height="75">
					</td>
					<td colspan="4" class="text-left">
						<a href="<?=$url?>" class="btn btn-xs btn-primary pull-right">Перейти</a>
						Сектор:
						<?=$v->major_desc?>
						/
						<b><?=$v->sector_format?></b>
						<b><?=$name?></b>
						<b><?=$v->sector_part?></b>
					</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
	</div>
</div>
<script>
$(document).ready(function()
{
	var dh=window.location.hash.substring(1);

	if(dh)
	{
		$('.imgArea div#l'+dh).each(function(i,e)
		{
			labelClick(this,true);
			return false;
		});
	}
});
</script>
<p><br></p>
<div class="container-fluid">
	<div class="text-center">
		<img src="<?=$server->catalog->image?>" data-src="holder.js/150x80?text=<?=$title?>" alt="<?=$title?>" class="img-rounded">
		<img src="<?=$server->major->image?>" data-src="holder.js/150x80?text=<?=$major?>" alt="<?=$major?>" height="80">
		<img src="<?=$server->minor->image?>" data-src="holder.js/150x80?text=<?=$minor?>" alt="<?=$minor?>" height="80">
	</div>
	<h1>Иллюстрация: <?=$server->minor->minor_desc?></h1>
	<h1>Категория: <?=$server->major->lex_desc?></h1>
	<?php if($vin){?><h1>VIN: <?=$vin?></h1><?php }?>
	<h1>Модель: <?=$server->model->model?></h1>
	<h2>Каталог: <?=$server->catalog->cat_name?></h2>
</div>
<p><br><br><br></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
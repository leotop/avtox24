<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
/**
 * Обязательно к применению
 */
include "../../_lib.php";
include "../api.php";

$mcct		= MCCT::instance();						// Библиотека каталога
$detail	= $mcct->rcv('detail');				// Параметр 'НОМЕР' запроса серверу
$region	= $mcct->rcv('region');				// Параметр 'РЕГИОН' запроса серверу
$mark		= $mcct->rcv('mark');					// Параметр 'МАРКА' запроса серверу

$server	=	$mcct->getMcctSearch('part',$detail,'',$region); // Обращение к серверу

$map		= json_decode(json_encode($server->map),true);
$part		= json_decode(json_encode($server->part),true);

$mcct->addMcctBreadRoot();
$mcct->addMcctBreadIndex($server);
$mcct->addMcctBread('search/detail','Поиск по номеру детали');

$mcct->addSearch($server);
?>
<link href="../../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../../media/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../../media/js/holder.min.js"></script>

<div class="container-fluid">

	<?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"?>

	<?php include WWW_ROOT."helpers/search.php"; /// Подключаем форму поиска?>

<?php
$n1=0;

foreach($map['family'] as $family=>$fData)
{
	$n1++;
	$id1=$mark.'-'.$n1;
?>
	<div class="treeBranch">
		<div id="plus<?=$id1?>" class="plusBranch anime" onclick="branchToggle('plus<?=$id1?>','items<?=$id1?>')">+</div>
		<div class="itemsBranchL">
			<div class="headBranchLVL1 anime" onclick="branchToggle('plus<?=$id1?>','items<?=$id1?>')">
				<?=strtoupper($fData['name'])?>
			</div>
			<div id="items<?=$id1?>" style="display:none;">
<?php
	$n2=0;
	foreach($fData['table'] as $cat)
	{
		$n2++;
		$id2=$id1.'-'.$n2;

		$cData=$map['catalog'][$cat];
		$cData['cat_folder']=$cat;
		$cid=$cData['id'];
		$title=htmlspecialchars($cData['name']);
		$reg=(($region&&$region!='all')?$region:null);

		if(empty($map['model'][$cid]))
			continue;

?>
				<div class="headBranchLVL1 anime pl201" onclick="$('#items<?=$id2?>').slideToggle(600)">
					<div class="media">
						<div class="pull-left">
							<img src="<?=$cData['image']?>" data-src="holder.js/100x75?text=<?=$cid?>" alt="<?=$title?>" height="75" class="img-rounded">
						</div>
						<div class="media-body">
							<div>
								<b><?=$cData['name']?></b>
								<?=$cData['family']?>
							</div>
							<div class="small">
								Каталог: <?=$cid?><!-- (<?//=$cat?>)-->
							</div>
							<div class="small">
								Регионы:
							<?php foreach($cData['regions'] as $ri=>$rv){ if(!$reg){ $reg=$ri; } ?>
								<img src="/media/images/mcct/region/<?=strtolower($ri)?>.png" alt="<?=$rv?>" height="16">
								<?=$rv?>
								&nbsp;&nbsp;&nbsp;
							<?}?>
							</div>
						</div>
					</div>
				</div>
				<div id="items<?=$id2?>" class="text-left ml40 pb20" style="display:none;">
<?php
		$n3=0;

		foreach($map['model'][$cid] as $model=>$mData)
		{
			$n3++;
			$id3=$id2.'-'.$n3;
			$date1=($mData['date_from']?preg_replace('/(.{4})(.{2})(.{2})/','$1.$2.$3',$mData['date_from']):'-');
			$date2=($mData['date_to']?preg_replace('/(.{4})(.{2})(.{2})/','$1.$2.$3',$mData['date_to']):'по н.в.');
?>
					<div class="headBranchLVL1 anime pl40 pt15 pb20" onclick="$('#items<?=$id3?>').slideToggle(600)">
						<div>
							Модель кузова: <b><?=$model?></b>
							<span class="small1">
								Дата пр-ва: <?=$date1?> - <?=$date2?>
							</span>
						</div>
						<?php if($mData['ucc']){ ?>
						<div class="small">
							<span class="label label-primary">Комплектация:</span>
							<?php
							foreach($mData['ucc'] as $ut=>$uv)
							{
								if(empty($map['ucc'][$cid])||empty($map['ucc'][$cid][$ut])||empty($map['ucc'][$cid][$ut][$uv]))
									continue;

								$u=$map['ucc'][$cid][$ut][$uv];
							?>
							<span class="label label-default"><?=$u['name']?>: <b><?=$u['value']?></b></span>
							<?php }?>
						</div>
						<?php }?>
					</div>
					<div id="items<?=$id3?>" class="text-left ml40 pb20" style="display:none;">
<?php
			$n4=0;

			foreach($map['major'][$cat] as $major=>$maData)
			{
				$n4++;
				$id4=$id3.'-'.$n4;
				$maName=$maData['name'];
?>
						<div class="headBranchLVL1 anime1 pl40 pt151 pb201" style="padding-top:5px; padding-bottom:5px;" onclick="$('#items<?=$id4?>').slideToggle(600)">
							Категория: <b><?=$maName?></b>
						</div>
						<div id="items<?=$id4?>" class="text-left ml40 pb201" style1="display:none;">
<?php
				$n5=0;

				foreach($map['minor'][$cat][$major] as $minor=>$miData)
				{
					$n5++;
					$id5=$id4.'-'.$n5;
?>
							<div id="items<?=$id5?>" class="text-left ml401 pb201" style1="display:none;">
<?php
					foreach($part[$cat][$major][$minor] as $pData)
					{
						if($cData['type']=='C'||$cData['type']=='S')
							$type=strtolower($cData['type']);
						else
							$type='';

						$param=[];
						$param['mark']=$mark;
						$param['type']=$type;
						$param['region']=strtolower($reg);
						$param['family']=MCCT::txtMcctFamilyUrl($family);
						$param['catalog']=$cid;
						$param['model']=$model;
						$param['major']=$major;
						$param['minor']=$minor;
						$url=$mcct->urlMcct('minor',$param).'#'.$pData['ref'];
?>
								<div class="headBranchLVL1 anime pl20 pt151 pb201" style="padding-top:5px; padding-bottom:5px;" onclick="window.open('<?=$url?>','_blank'); return false;">
									<div>
										<a href="<?=$url?>" target="_blank">
											<b><?=$server->one->name?></b>
											<?=$server->one->desc?>
										</a>
									</div>
									<div class="small">
										Иллюстрация:
										<?=$miData['sector']?>
										<?=$miData['name']?>
										<?=$miData['part']?>
										<?=$miData['desc']?>
									</div>
									<div class="small">
										PNC / Number:
										<?=$pData['pnc']?> / <?=$pData['number']?>
									</div>
								</div>
					<?php }?>
							</div>
				<?php }?>
						</div>
			<?php }?>
					</div>
		<?php }?>
				</div>
	<?php }?>
			<div class="clear"></div>
			</div>
		</div>
	</div>
<?php }?>
</div>
<script>
function branchToggle(plusBranch,itemsBranch)
{
	var $plusBranch=$('#'+plusBranch);
	var $itemsBranch=$('#'+itemsBranch);

	if($plusBranch.html()!=='+')
	{
		$plusBranch.html('+');
		$itemsBranch.slideUp(700);
	}
	else
	{
		$plusBranch.html('&ndash;');
		$itemsBranch.slideDown(700);
	}
}
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
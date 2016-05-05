<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}



$api = new LinemediaAutoApiDriver();



/*
* ƒополнительный запрос на получение ключа дл€ проведени€ платежа
*/
if($_GET['ajax'] == 'getPaymentForm')
{
	try {
		$response = $api->getPaymentForm($_POST);
	} catch (Exception $e) {
		die($e->GetMessage());
	}
	die($response['data']['html']);
}






try {
	$response = $api->getAccountInfo();
} catch (Exception $e) {
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
	ShowError($e->GetMessage());
	require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
	exit;
}

$account = $response['data'];

$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

$APPLICATION->SetTitle(GetMessage("LM_AUTO_LINEMEDIA_ACCOUNT_PAGETITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>


<?= BeginNote() ?>
	<b><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MESSAGE') ?></b>
<?= EndNote() ?>


<table class="lm-auto-account">
	<tbody>
		<tr>
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ID')?></th>
			<td><?=intval($account['id'])?></td>
		</tr>
		<tr class="title">
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_TITLE')?></th>
			<td><?=htmlspecialchars($account['title'])?></td>
		</tr>
		<tr class="active">
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ACTIVE')?></th>
			<td><?=($account['active']) ? GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y') : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N')?></td>
		</tr>
		<tr>
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ACTIVE_BEFORE')?></th>
			<td><?=($account['active_before']) ? $account['active_before'] : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N')?></td>
		</tr>
		<tr class="tecdoc">
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_TECDOC_AVAILABLE')?></th>
			<td>
				<?if(!$account['tecdoc']['available']){?>
					<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N')?>
					<input type="button" class="payment pay" data-type="tecdoc" value="<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAY')?>"/>
				<?}else{?>
					<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y')?>
					<?if($account['tecdoc']['available_before']){?>
						<span class="tecdoc-before"><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE')?> <?=$account['tecdoc']['available_before']?></span>
						<input type="button" class="payment prolong" data-type="tecdoc" value="<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PROLONG')?>"/>
					<?}?>
				<?}?>
			</td>
		</tr>
		<tr class="lm-crosses">
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_LINEMEDIA_CROSSES')?></th>
			<td>
				<?=($account['linemedia_crosses']['available']) ? GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y') : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N')?>
				<?if($account['linemedia_crosses']['available_before']){?>
					<span class="tecdoc-before"><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE')?> <?=$account['linemedia_crosses']['available_before']?></span>
				<?}?>
			</td>
		</tr>
		<tr>
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MAX_DAILY_REQUESTS')?></th>
			<td><?=($account['max_daily_requests']) ? number_format($account['max_daily_requests'], 0, '.', ' ') : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_UNLIMITED')?></td>
		</tr>
		<tr>
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_TODAY_REQUESTS')?></th>
			<td><?=intval($account['today_requests'])?> (<?=number_format($account['today_requests'] * 100 / $account['max_daily_requests'], 2, '.', ' ')?>%)</td>
		</tr>
		<tr class="original-catalogs">
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ORIGINAL_CATALOGS')?></th>
			<td>
				<?
				foreach($account['original_catalogs'] AS $catalog){
					echo '<div>';
					echo '<span class="brand">'.$catalog['brand_title'] . '</span>';
					if($catalog['available']) {
						echo GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y');
						
						echo $catalog['available_before'] ? $catalog['available_before'] : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_UNLIMITED');
						
						?>
						<input type="button" class="payment pay" data-type="original-catalog" data-code="<?=htmlspecialchars($catalog['brand_code'])?>" value="<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PROLONG')?>"/>
						<?
						
					} else {
						echo GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N');
						?>
						<input type="button" class="payment prolong" data-type="original-catalog" data-code="<?=htmlspecialchars($catalog['brand_code'])?>" value="<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAY')?>"/>
						<?
					}
					echo '</div>';
				}
				?>
			</td>
		</tr>
		<tr class="ips">
			<th><?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_IPS')?></th>
			<td>
				<?
					foreach($account['ips'] AS $ip) {
						echo '<div>';
						if($ip == $_SERVER['SERVER_ADDR']) {
							echo '<b>'.htmlspecialchars($ip).'</b>';
						} else {
							echo htmlspecialchars($ip);
						}
						echo '</div>';
					}
				?>
			</td>
		</tr>
	</tbody>
</table>


<div id="lm-auto-payment" style="display:none">
	<form action="" method="post" target="_blank" id="lm-auto-payment-frm">
		
		<input type="hidden" name="service" value="" id="lm-auto-srv" />
		<input type="hidden" name="service-code" value="" id="lm-auto-srv-code" />
		
		<div class="pay-service">
			<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_SERVICE')?>
			<select id="lm-auto-period" name="period"></select>
		</div>
		
		<div class="pay-system">
			<?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAYSYSTEM')?>
			<select id="lm-auto-paysystem" name="paysystem">
				<?foreach($account['payments']['paysystems'] AS $code => $data){?>
					<option value="<?=$code?>"><?=$data['title']?> (+<?=$data['markup']?>%)</option>
				<?}?>
			</select>
		</div>
		
		<input type="submit" value="<?=GetMessage("LM_AUTO_LINEMEDIA_ACCOUNT_PAYBUTTON")?>">
	</form>
</div>


<div id="lm-auto-payment-process"></div>

<script>
var payments = <?=json_encode($account['payments'])?>;
$(document).ready(function(){
	$('input.payment').click(function(){
		
		/*
		* ѕри клике на выбор услуги
		* —крываем форму оплаты и показываем форму уточнени€€ выбора
		*/
		$('#lm-auto-payment-process').hide();
		$('#lm-auto-payment').show();
		
		var type = $(this).data('type');
		var code = $(this).data('code');
		
		
		$('#lm-auto-period').html('');
		
		
		if(type == 'original-catalog')
		{
			var item = payments['services']['original-catalog-' + code];
		} else {
			var item = payments['services'][type];
		}
					
		for(var i in item['period'])
		{
			$('#lm-auto-period').append('<option value="'+i+'">'+item['title']+' - '+i+' <?=GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_DAYS')?> - '+item['period'][i]+' <?=$account['payments']['currency']?></option>');
		}
		
		$('#lm-auto-srv').val(type);
		$('#lm-auto-srv-code').val(code);
		
	});
	
	/*
	* ѕри клике на сабмит запрашиваем с сервера форму оплаты выбранной услуги
	* —крываем эту форму и показываем новую
	*/
	$('#lm-auto-payment-frm').submit(function(){
		
		$('#lm-auto-payment').hide();
		$('#lm-auto-payment-process').show();
		
		$.ajax({
		  url: "/bitrix/admin/linemedia.linemedia_account.php?lang=<?=LANG?>&ajax=getPaymentForm",
		  type: 'post',
		  data: $('#lm-auto-payment-frm').serialize()
		}).done(function(html) {
		  $('#lm-auto-payment-process').html(html);
		});
		
		return false;
	})
	
})
</script>

<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

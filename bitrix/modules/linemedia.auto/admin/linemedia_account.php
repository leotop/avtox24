<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->AddHeadScript("http://bernii.github.io/gauge.js/dist/gauge.min.js");



IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
	ShowError('LM_AUTO MODULE NOT INSTALLED');
	return;
}

/*
 * очистим сессию платных услуг каталога
 */
unset($_SESSION['CATALOG_PAY_FUNC']);

/*
 * API.
*/
$api = new LinemediaAutoApiDriver();


/*
 * Дополнительный запрос на получение ключа для проведения платежа.
 */
if ($_GET['ajax'] == 'getPaymentForm') {
	try {
		$response = $api->getPaymentForm2($_POST);
	} catch (Exception $e) {
		die($e->GetMessage());
	}
	die($response['data']['html']);
}



try {
	$response = $api->getAccountInfo2();
} catch (Exception $e) {
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
	ShowError($e->GetMessage());
	require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
	exit;
}

$account = $response['data'];

// Хостинг проверим отдельно.
$hosting = $account['payments']['services']['main']['items']['hosting'];
unset($account['payments']['services']['main']['items']['hosting']);
unset($account['payments']['services']['linemedia.auto']['items']['max_suppliers_quantity']);
unset($account['payments']['services']['linemedia.auto']['items']['max_goods_quantity']);
unset($account['payments']['services']['modules']);

//$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");
CJSCore::Init(array("jquery"));

$APPLICATION->SetTitle(GetMessage("LM_AUTO_LINEMEDIA_ACCOUNT_PAGETITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>

<?= BeginNote() ?>
	<b><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MESSAGE') ?></b>
<?= EndNote() ?>


<div class="paysystems-images">
	<? foreach (array_slice(scandir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/icons/linemedia.auto/paysystems/'), 2) as $img) { ?>
		<img src="/bitrix/themes/.default/icons/linemedia.auto/paysystems/<?= $img ?>" alt="" />
	<? } ?>
</div>


<?
/*
 * Общая нагрузка на систему с учётом количества поставщиков, товаров и т.д.
 */
$load_matrix = LinemediaAutoModule::getAutoexpertLoadMatrix();
?>
<div class="spidometer">
    <h4><?= GetMessage('LM_AUTO_LINEMEDIA_CURRENT_LOAD', array('#LOAD#' => $load_matrix['global'])) ?></h4>
    <? if ($load_matrix['global'] > 80) { ?>
        <div class="upgrade-notice"><?= GetMessage('LM_AUTO_LINEMEDIA_CURRENT_LOAD_HIGH') ?></div>
    <? } ?>
    <canvas width="220" height="70" id="gauge"></canvas>
</div>
<script>
	var opts = {
	  lines: 12, // The number of lines to draw
	  angle: 0.15, // The length of each line
	  lineWidth: 0.44, // The line thickness
	  pointer: {
	    length: 0.9, // The radius of the inner circle
	    strokeWidth: 0.035, // The rotation offset
	    color: '#000000' // Fill color
	  },
	  limitMax: 'false',   // If true, the pointer will not go past the end of the gauge
	  colorStart: '#6FADCF',   // Colors
	  colorStop: '#8FC0DA',    // just experiment with them
	  strokeColor: '#E0E0E0',   // to see which ones work best for you
	  generateGradient: true,
	  percentColors : [[0.0, "#a9d70b" ], [0.50, "#f9c802"], [1.0, "#ff0000"]]
	};
	var target = document.getElementById('gauge');
	var gauge = new Gauge(target).setOptions(opts);
	gauge.maxValue = 100;
	gauge.animationSpeed = 15;
	gauge.set(<?=$load_matrix['global']?>);
</script>



<table class="lm-auto-account">
	<tbody>
		<tr>
			<th>
				<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ID') ?>
			</th>
			<td colspan="2">
				<?= intval($account['id']) ?>
			</td>
		</tr>
		<tr class="title">
			<th><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_TITLE') ?></th>
			<td colspan="2">
				<b><?= htmlspecialchars($account['title']) ?></b>
			</td>
		</tr>
		<tr class="active">
			<th><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ACTIVE') ?></th>
			<td colspan="2">
				<?= ($account['active']) ? GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y') : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N')?>
			</td>
		</tr>
		<tr>
			<th><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_ACTIVE_BEFORE') ?></th>
			<td colspan="2">
				<?= ($account['active_before']) ? $account['active_before'] : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N')?>
			</td>
		</tr>
		
		<? foreach ($account['payments']['services'] as $module => $group) { ?>
			<tr>
				<th colspan="3">
					<?= $group['title'] ?>
				</th>
			</tr>
			<? foreach ($group['items'] as $function => $service) { ?>
				<? $data = $account['services'][$module][$function] ?>
				<? $hasvariants = ($service['type'] == 'periods' || $service['type'] == 'counts') ?>
				
				<tr class="linemedia-service">
					<td width="20%">
						<? if ($module == 'modules') { ?>
							<? $service['title'] = GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MODULE').' "'.LinemediaAutoModule::getModuleTitle('linemedia.'.$function).'"'; ?>
						<? } ?>
						<?= $service['title'] ?>
						<? if (!empty($data['type']) && $module != 'linemedia.autooriginalcatalogs') { ?>
							<? $type = $function_data['types'][$data['type']]['title']; ?>
							<i class="code">[<?= ($type) ? ($type) : ($data['type']) ?>]</i>
						<? } ?>
					</td>
					<td>
						<? if ($module == 'modules') { ?>
							<? if (IsModuleInstalled('linemedia.'.$function)) { ?>
								<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y') ?>
							<? } else { ?>
								<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N') ?>
								
								<input type="button" class="payment pay" data-module="<?= $module ?>" data-service="<?= $function ?>" data-type="<?= $type ?>" data-service-type="<?= $service['type'] ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BUY', array('#PRICE#' => $service['price'])) ?>"/>
							<? } ?>
						<? } else { ?>
							
							<? if (!$data['available']) { ?>
								<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_N') ?>
							<? } else { ?>
								<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_Y') ?>
								<? if ($function != 'tecdoc') { ?>
									<? if ($service['type'] == 'counts') { ?>
										<span>
											&nbsp;&nbsp; <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_COUNT') ?>
											<b><?= $data['limit'] ?></b>
										</span>
									<? } else { ?>
										<? if (strtotime($data['available_before']) > 0) { ?>
											<span>
												&nbsp;&nbsp; <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE') ?>
												<b><time><?= date('H:i d.m.Y', strtotime($data['available_before'])) ?></time></b>
											</span>
										<? } ?>
									<? } ?>
								<? } ?>
							<? } ?>
							
							<? // Отдельный вывод для TecDoc // ?>
							<? if ($function == 'tecdoc') { ?>
								<? if ($data['available_before']) { ?>
									<? if (strtotime($data['available_before']) < time()) { ?>
										<span class="tecdoc-before expired">
											&nbsp;&nbsp; <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE') ?>
											<b><time><?= date('H:i d.m.Y', strtotime($data['available_before'])) ?></time></b>
										</span>
									<? } else { ?>
										<span class="tecdoc-before">
											&nbsp;&nbsp; <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE') ?>
											<b><time><?= date('H:i d.m.Y', strtotime($data['available_before'])) ?></time></b>
										</span>
									<? } ?>
								<? } ?>
							<? } ?>
							
							
							<? // Оплата или продление //?>
							<? if ($hasvariants) { ?>
							
								<? if ($service['type'] == 'counts') { ?>
									<input type="button" class="payment prolong counts" data-module="<?= $module ?>" data-service="<?= $function ?>" data-type="<?= $type ?>" data-service-type="<?= $service['type'] ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAY') ?>"/>
								<? } else { ?>
									<? if (strtotime($data['available_before']) > time()) { ?>
										<input type="button" class="payment prolong" data-module="<?= $module ?>" data-service="<?= $function ?>" data-type="<?= $type ?>" data-service-type="<?= $service['type'] ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PROLONG') ?>"/>
									<? } else { ?>
										<input type="button" class="payment pay" data-module="<?= $module ?>" data-service="<?= $function ?>" data-type="<?= $type ?>" data-service-type="<?= $service['type'] ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAY') ?>"/>
									<? } ?>
								<? } ?>
							<? } else { ?>
								<? if (!$data['available']) { ?>
									<input type="button" class="payment pay" data-module="<?= $module ?>" data-service="<?= $function ?>" data-type="<?= $type ?>" data-service-type="<?= $service['type'] ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BUY', array('#PRICE#' => $service['price'])) ?>"/>
								<? } ?>
							<? } ?>
						
						<? } ?>
						
					</td>
					<td align="left" width="32%">
						<? // История оплат // ?>
						<? if (!empty($data['history']) && $module != 'modules') { ?>
							<a href="javascript:void(0)" class="link-history-show" rel="<?= $function ?>"><?= GetMessage('LM_AUTO_LINEMEDIA_HISTORY') ?></a>
							<div class="payment-history" style="display: none;" id="history-wrap-<?= $function ?>">
								<? foreach ($data['history'] as $payment) { ?>
									<div>
										<? if ($service['type'] == 'counts') { ?>
											<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_SINCE') ?>  <?= date('H:i d.m.Y', strtotime($payment['active_since'])) ?>&nbsp;
											<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE') ?> <?= date('H:i d.m.Y', strtotime($payment['active_before'])) ?>&nbsp;
											<b><?= $payment['variant'] ?></b>
										<? } else { ?>
											<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_SINCE') ?>  <?= date('H:i d.m.Y', strtotime($payment['active_since'])) ?>&nbsp;
											<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_BEFORE') ?> <?= date('H:i d.m.Y', strtotime($payment['active_before'])) ?>&nbsp;
										<? } ?>
										№ <b><?= $payment['payment_id'] ?></b>
									</div>
								<? } ?>
							</div>
						<? } ?>
					</td>
				</tr>
			<? } ?>
		<? } ?>
		
		<? // Справочная информация // ?>
		<tr>
			<th>
				<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MAX_DAILY_REQUESTS') ?>
			</th>
			<td colspan="2">
				<?= ($account['max_daily_requests']) ? number_format($account['max_daily_requests'], 0, '.', ' ') : GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_UNLIMITED') ?>
			</td>
		</tr>
		<tr>
			<th>
				<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_TODAY_REQUESTS') ?>
			</th>
			<td colspan="2">
				<?= intval($account['today_requests']) ?> (<?= number_format($account['today_requests'] * 100 / $account['max_daily_requests'], 2, '.', ' ') ?>)%
			</td>
		</tr>
		<tr class="ips">
			<th><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_IPS') ?></th>
			<td colspan="2">
				<?
					foreach ($account['ips'] as $ip) {
						echo '<div>';
						if ($ip == $_SERVER['SERVER_ADDR']) {
							echo '<b>'.htmlspecialchars($ip).'</b>';
						} else {
							echo htmlspecialchars($ip);
						}
						echo '</div>';
					}
				?>
			</td>
		</tr>
		
		<? // Хостинг // ?>
		<? $data = $account['services']['main']['hosting'] ?>
		<? $type = $data['type'] ?>
		<? if ($type) { ?>
			<? $hosttype = $hosting['types'][$type] ?>
			<tr class="tecdoc">
				<th><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_HOSTING') ?> (<?= $hosttype['title'] ?>)</th>
				<td colspan="2">
					<span class="tecdoc-before">
						<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAYED_UNTIL') ?>
						<b><time><?= date('H:i d.m.Y', strtotime($data['available_before'])) ?></time></b>
					</span>
					<? if (strtotime($data['available_before']) > time()) { ?>
						<input type="button" class="payment pay" data-module="main" data-service="hosting" data-type="<?= $type ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PROLONG') ?>"/>
					<? } else { ?>
						<input type="button" class="payment pay" data-module="main" data-service="hosting" data-type="<?= $type ?>" value="<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAY') ?>"/>
					<? } ?>
				</td>
			</tr>
		<? } ?>
		
	</tbody>
</table>

<script type="text/javascript">
	var payments = <?= json_encode($account['payments']) ?>;
	
	var Dialog = new BX.CDialog({
		title: '<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_POPUP_TITLE') ?>',
		content: '<div id="lm-auto-payment" style="display: none;">' +
					'<form action="" class="form-horizontal" method="post" target="_blank" id="lm-auto-payment-frm">' +
						'<input type="hidden" name="module" value="" id="lm-auto-module" />' +
						'<input type="hidden" name="service" value="" id="lm-auto-service" />' +
						'<input type="hidden" name="type" value="" id="lm-auto-type" />' +
						'<input type="hidden" name="period" id="lm-auto-period" value="-" />' + 
						'<input type="hidden" name="variant" id="lm-auto-variant" />' + 
						'<div class="control-group pay-service">' +
							'<label class="control-label"><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_SERVICE') ?></label>' +
							'<div class="controls"><select id="lm-auto-select">' +
							'</select>' +
							'</div>' +
						'</div>' +
						'<div class="control-group pay-system">' +
							'<label class="control-label"><?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_PAYSYSTEM') ?></label>' +
							'<div class="controls"><select id="lm-auto-paysystem" name="paysystem">'+
								'<? foreach ($account['payments']['paysystems'] as $code => $data) { ?>'+
								'<option value="<?= $code ?>"><?= $data['title'] ?></option>'+
								'<? } ?>'+
								'</select>' +
							'</div>' +
						'</div>' +
						'<div class="form-actions">' +
							'<input type="submit" value="<?= GetMessage("LM_AUTO_LINEMEDIA_ACCOUNT_PAYBUTTON") ?>">' +
						'</div>' +
					'</form>' +
				'</div>' +
				'<div id="lm-auto-payment-process"></div>',
		resizable: false,
		draggable: true,
		height: '160',
		width: 'auto'
	});
	
	$(document).ready(function() {

		$('#lm-auto-select').live('change', function() {
			var period  = $(this).find('option:selected').data('period');
			var variant = $(this).find('option:selected').data('variant');


			if (period != undefined) {
				$('#lm-auto-period').val(period);
			}
			if (variant != undefined) {
				$('#lm-auto-variant').val(variant);
			}
		});
		
		/*
		 * При клике на выбор услуги.
		 * Скрываем форму оплаты и показываем форму уточненияя выбора.
		 */
		$('input.payment').click(function() {
			$('#lm-auto-payment-process').hide();
			$('#lm-auto-payment').show();
			$('#lm-auto-select').html('');

			var servicetype = $(this).data('service-type');
			
			var module  = $(this).data('module'); 	// Модуль
			var service = $(this).data('service'); 	// Сервис
			var type    = $(this).data('type'); 	// Тип
			
			var item = payments['services'][module]['items'][service];
			
			if (item['variants']) {
				$('#lm-auto-select').append('<option>- <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_CHOOSE') ?> -</option>');
				for (var i in item['variants']) {
					if (i == '-') {
						// Permanent.
						$('#lm-auto-select').append('<option data-period="' + i + '">' + item['title'] + ' - ' + item['variants'][i] + ' <?= $account['payments']['currency'] ?></option>');
					} else {
						if (servicetype == 'counts') {
							// Additional.
							for (var j in item['variants'][i]) {
								var c = (i > 0) ? (i) : ('&infin;');
								$('#lm-auto-select').append('<option data-variant="' + i + '" data-period="' + j + '">' + item['title'] + ' - ' + c + ' (' + j + ' <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MONTHS') ?>) - ' + item['variants'][i][j] + ' <?= $account['payments']['currency'] ?></option>');
							}
						} else {
							// Prolongation.
							$('#lm-auto-select').append('<option data-period="' + i + '">' + item['title'] + ' - ' + i + ' <?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_MONTHS') ?> - ' + item['variants'][i] + ' <?= $account['payments']['currency'] ?></option>');
						}
					}
				}
			} else {
				$('#lm-auto-select').append('<option data-period="-">' + item['title'] + ' - <?= $account['payments']['currency'] ?></option>');
			}
			
			$('#lm-auto-module').val(module);
			$('#lm-auto-service').val(service);
			$('#lm-auto-type').val(type);
			
			Dialog.Show();
		});

		
		/*
		 * Показ полной истории оплат.
		 */
		$('.link-history-show').click(function() {
			$('#history-wrap-' + $(this).attr('rel')).toggle();
		});
		
	
		/*
		 * При клике на сабмит запрашиваем с сервера форму оплаты выбранной услуги.
		 * Скрываем эту форму и показываем новую.
		 */
		$('#lm-auto-payment-frm').live("submit", function(e){
			e.preventDefault();
	
			$('#lm-auto-payment').hide();
			$('#lm-auto-payment-process').show();
			$('#lm-auto-payment-process').html('<?= GetMessage('LM_AUTO_LINEMEDIA_ACCOUNT_LOADING') ?>');
	
			$.ajax({
				url: "/bitrix/admin/linemedia.linemedia_account.php?lang=<?= LANG ?>&ajax=getPaymentForm",
				type: 'post',
				data: $('#lm-auto-payment-frm').serialize()
			}).done(function(html) {
				$('#lm-auto-payment-process').html(html);
			});
			
			return false;
		});
	});
</script>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

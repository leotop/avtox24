<?php
/**
 * Административный скрипт проверки удаленных поставщиков
 */
/**
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
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
CModule::IncludeModule('linemedia.autoremotesuppliers');



CUtil::InitJSCore(array('jquery'));

// $APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");


$remote_suppliers = LinemediaAutoRemoteSuppliersSupplier::getList();
$remote_suppliers_objects = array();
foreach ($remote_suppliers as $code => $title) {
	$remote_suppliers[$code] = array(
		'title' => $title,
		'object' => LinemediaAutoRemoteSuppliersSupplier::load($code),
		'status' => false,
		'errors' => array(),
		'results' => array(),
	);
}


/*
 * Подключения у реальных поставщиков
 */
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', '!PROPERTY_api' => false));
foreach ($suppliers_res as $S) {
	$api = $S['PROPS']['api']['VALUE']['LMRSID'];
	if (!isset($remote_suppliers[$api])) {
		continue;
	}
	
	//$remote_suppliers[$api]['supplier_object'] = new LinemediaAutoSupplier($S['PROPS']['supplier_id']['VALUE']);
    /*
     * Правка по задаче 14131
     * Она решает проблему с отсутствием данных (login, password) на этапе проверки метода init ниже
     */
    $remote_suppliers[$api]['object']->setOptions($S['PROPS']['api']['VALUE']);

    $remote_suppliers[$api]['supplier_data'] = $S;
}



/*
 * Проверка одного постащика.
 */
if ($_GET['ajax'] = 'check_search' && $_GET['code']) {
	
	$time_start = microtime(1);
	
	$check_supplier = (string) $_GET['code'];
	
	
	$article = (string) $_POST['article'];
	$brand_title = (string) $_POST['brand_title'];
	
	
	// init
	try {
		$remote_suppliers[$check_supplier]['object']->init();
	} catch (Exception $e) {
		$remote_suppliers[$check_supplier]['errors'][] = $e->GetMessage();
	}
	
	//login
	try {
	    $remote_suppliers[$check_supplier]['object']->login();
	} catch (Exception $e) {
		$remote_suppliers[$check_supplier]['errors'][] = $e->GetMessage();
	}
	
	if (empty($remote_suppliers[$check_supplier]['errors'])) {
		
		// search
		try {
			
			/*
			 * Запишем подключение, прописанное в инфоблоке
			 */
			$remote_suppliers[$check_supplier]['object']->setOptions($remote_suppliers[$check_supplier]['supplier_data']['PROPS']['api']['VALUE']);
	
			$remote_suppliers[$check_supplier]['object']->setQuery($article);
			$remote_suppliers[$check_supplier]['object']->setBrandTitle($brand_title);
			
			
			$remote_suppliers[$check_supplier]['object']->search();
			//$remote_suppliers[$check_supplier]['results']->getParts();
		} catch (Exception $e) {
			$remote_suppliers[$check_supplier]['errors'][] = $e->GetMessage();
		}
	}
	
	if (count($remote_suppliers[$check_supplier]['errors']) == 0) {
		$remote_suppliers[$check_supplier]['status'] = true;
	}
	$remote_suppliers[$check_supplier]['time'] = number_format(microtime(1) - $time_start, 2);
	
	foreach ($remote_suppliers as &$S) {
		unset($S['object']);
	}
	die(json_encode((array)$remote_suppliers[$check_supplier]/*, JSON_UNESCAPED_UNICODE*/));// causes error sometimes even in 5.3
}


$APPLICATION->SetTitle(GetMessage("LM_AUTO_RSCHECK_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>


<?= BeginNote() ?>
	<b><?=GetMessage('LM_AUTO_RSCHECK_MESSAGE')?></b>
<?= EndNote() ?>


<div class="rem-supp-check-art">
	<label><?=GetMessage('LM_AUTO_RSCHECK_ARTICLE')?> <input type="text" id="article" value="gdb1550" /></label>
	<label><?=GetMessage('LM_AUTO_RSCHECK_BRAND')?> <input type="text" id="brand_title" value="TRW" /></label>
</div>
<br />
<div class="adm-list-table-wrap adm-list-table-without-header adm-list-table-without-footer">
		
	<table class="suppliers-check-list adm-list-table">
		<thead>
			<tr class="adm-list-table-header">
				<td class="adm-list-table-cell">
					<div class="adm-list-table-cell-inner">
						<?= GetMessage('LM_AUTO_RSCHECK_TITLE') ?>
					</div>
				</td>
				<td class="adm-list-table-cell">
					<div class="adm-list-table-cell-inner">
						<?= GetMessage('LM_AUTO_RSCHECK_URL') ?>
					</div>
				</td>
				<td class="adm-list-table-cell">
					<div class="adm-list-table-cell-inner">
						<?= GetMessage('LM_AUTO_RSCHECK_STATUS') ?>
					</div>
				</td>
				<td class="adm-list-table-cell" style="width:100%">
					<div class="adm-list-table-cell-inner">
						<?= GetMessage('LM_AUTO_RSCHECK_ERRORS') ?>
					</div>
				</td>
				<td class="adm-list-table-cell">
					<div class="adm-list-table-cell-inner">
						<?= GetMessage('LM_AUTO_RSCHECK_RESULTS') ?>
					</div>
				</td>
				<td class="adm-list-table-cell">
					<div class="adm-list-table-cell-inner">
						<?= GetMessage('LM_AUTO_RSCHECK_TIME') ?>
					</div>
				</td>
			</tr>
		</thead>
		<tbody>
			<? foreach ($remote_suppliers as $code => $supplier) { ?>
				<tr id="tr-<?= $code ?>" class="adm-list-table-row">
					<td class="adm-list-table-cell">
						<? if ($supplier['supplier_data']['ID']) { ?>
							<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=<?= LinemediaAutoSupplier::getIblockId() ?>&type=linemedia_auto&ID=<?= $supplier['supplier_data']['ID'] ?>&lang=<?= LANGUAGE_ID ?>&find_section_section=0&WF=Y"><?= $supplier['title'] ?></a>
						<? } else { ?>
							<?= $supplier['title'] ?>
						<? } ?>
					</td>
					<td class="adm-list-table-cell">
						<?= $supplier['object']->url ?>
					</td>
					<td class="adm-list-table-cell status">
						<a href="javascript:checkSupplierByCode('<?= $code ?>');">Check</a>
					</td>
					<td class="adm-list-table-cell errors"></td>
					<td class="adm-list-table-cell results"></td>
					<td class="adm-list-table-cell time"></td>
				</tr>
			<? } ?>
		</tbody>
	</table>
</div>
<br />
<input type="button" id="start-check" value="<?= GetMessage('LM_AUTO_RSCHECK_START_CHECK') ?>" />


<script>
	var sup_codes = <?=json_encode(array_keys($remote_suppliers))?>;
	var sup_index = 0;
	$(document).ready(function(){
		$('#start-check').click(function(){
			$('#start-check').prop('disabled', true);
			
			checkAllSuppliers(0);
		});
		
		function checkAllSuppliers(index)
		{	
			if (!sup_codes[index]) {
				$('#start-check').prop('disabled', false);
				return;
			}
			var code = sup_codes[index];
			
			checkSupplierByCode(code, function(){
			    checkAllSuppliers(++index);
			});
		}
	})
	
	
	function checkSupplierByCode(code, callback)
	{
	    callback = callback || function(){};
	    $.ajax({
			dataType: "json",
			url: '/bitrix/admin/linemedia.autoremotesuppliers_check.php?lang=<?=LANGIAGE_ID?>&ajax=check_search&code=' + code,
			data: {'article':$('#article').val(), 'brand_title':$('#brand_title').val()},
			type:'post',
			success: function(json) {
				var status = (json.status) ? '<span class="ok">Ok</span>' : '<span class="error">Error</span>';
				$('#tr-' + code + ' .status').html(status);
				
				var errors = '';
				for(var i in json.errors)
					errors += json.errors[i] + '<br>';
				
				$('#tr-' + code + ' .errors').html(errors);
				
				
				var time = json.time;
				if(time > 1)
					time = '<b>' + time + '</b>';
				$('#tr-' + code + ' .time').html(time);
				
				callback(json);
			},
			fail: function() {
				alert('JSON failed');
			}
		});
	}
</script>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>
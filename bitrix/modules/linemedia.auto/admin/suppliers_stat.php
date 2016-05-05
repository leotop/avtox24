<?php 

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");



global $USER, $APPLICATION;



IncludeModuleLangFile(__FILE__);

CJSCore::Init(array('jquery', 'window', 'ajax'));





$autoModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");





if ($autoModulePermissions == 'D') {

    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

}







if (!CModule::IncludeModule("linemedia.auto")) {

    ShowError('LM_AUTO_MODULE NOT INSTALLED');

    return;

}







$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIER_STAT_LIST_TITLE'));



$PARTS_DB = new LinemediaAutoDatabase();



$suppliers = LinemediaAutoSupplier::GetList(array("SORT" => "NAME"), array("ACTIVE" => "Y"));

















/*

 * Проверка одного постащика.

 */

if ($_GET['ajax'] = 'check_search' && $_GET['code']) {

	$APPLICATION->RestartBuffer();

	

	if(!check_bitrix_sessid()) {

		die('bad session');

	}

	

	$time_start = microtime(1);

	

	$supplier_id = (string) $_GET['code'];

	

	$stat = array();

	$res = $PARTS_DB->query('SELECT COUNT(*) AS cnt, MAX(modified) AS last_update FROM `b_lm_products` WHERE supplier_id = "'.$supplier_id.'"');

	$res = $res->fetch();

	$stat['parts_count'] = number_format($res['cnt'], 0, '.', ' ');

	$stat['last_update'] = FormatDate('x', strtotime($res['last_update']));

	

	// last update older than 2 days

	if(time() - strtotime($res['last_update']) > 86400*2) {

		$stat['last_update'] = '<span class="warning">' . $stat['last_update'] . '</span>';

	}

	

	if(strtotime($res['last_update']) == 0) {

		$stat['last_update'] = '-';

	}

	

	

	// tasks

	$tasks_html = array();

	$task_res = LinemediaAutoTask::GetList(array(), array('supplier_id' => $supplier_id));

	while($task = $task_res->fetch()) {

		$shedules = array();

	    $shedule_obj = LinemediaAutoTaskShedule::GetByTaskId($task['id']);

	    while ($shedule = $shedule_obj->Fetch()) {

	        $shedules []= $shedule;

	    }

	    //$f_interval = $shedules[0]['interval'];

	    $last_exec = strtotime($shedules[0]['last_exec']);

	    $last_exec = strtotime($last_exec) > 0 ? FormatDate('x', $last_exec) : '';

	    

	    $tasks_html[] = '<a href="/bitrix/admin/linemedia.auto_task_list.php?lang='.LANGUAGE_ID.'&set_filter=Y&find_id='.$task['id'].'" target="_blank">'.$task['title'].' ('.$task['protocol'].')</a> ' . $last_exec . '';

	    

	}

	$stat['tpls'] = join('<br>', $tasks_html);

	

	die(json_encode($stat));

}









$suppliers_codes = array();

foreach($suppliers AS $supplier) {

	$supplier_id = $supplier['PROPS']['supplier_id']['VALUE'];

	$suppliers_codes[] = $supplier_id;

}



require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");?>



<input type="button" id="start-check" value="<?=GetMessage('LM_AUTO_SUPPLIER_STAT_COLLECT')?>">



<table class="supplier-stat-table adm-list-table">

	<thead>

		<tr class="adm-list-table-header">

			<th class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_SUPPLIER_STAT_SUPPLIER')?></div></th>

			<th class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_SUPPLIER_STAT_API')?></div></th>

			<th class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_SUPPLIER_STAT_PARTS')?></div></th>

			<th class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_SUPPLIER_STAT_LAST_UPDATE')?></div></th>

			<th class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_SUPPLIER_STAT_TPLS')?></div></th>

			<th class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_SUPPLIER_STAT_CHECK')?></div></th>

		</tr>

	</thead>

	<tbody>

		<?foreach($suppliers AS $supplier){$supplier_id = $supplier['PROPS']['supplier_id']['VALUE'];?>

		<tr id="sid-<?=$supplier_id?>" class="adm-list-table-row">

			<td class="adm-list-table-cell"><?=$supplier['NAME']?></td>

			<td class="adm-list-table-cell"><?=$supplier['PROPS']['api']['VALUE']['LMRSID']?></td>

			<td class="parts_count adm-list-table-cell"></td>

			<td class="last_update adm-list-table-cell"></td>

			<td class="tpls adm-list-table-cell"></td>

			<td class="chk adm-list-table-cell"><input type="button" value="<?=GetMessage('LM_AUTO_SUPPLIER_STAT_COLLECT_SINGLE')?>" class="check-single-supplier" data-code="<?=$supplier_id?>"></td>

		</tr>

		<?}?>

	</tbody>

</table>







<script>

	var sup_codes = <?=json_encode($suppliers_codes)?>;

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

	

	

	$('.check-single-supplier').click(function(){

		var code = $(this).data('code');

		checkSupplierByCode(code, function(){

			

		})

	})

	

	

	function checkSupplierByCode(code, callback)

	{

	    callback = callback || function(){};

	    

	    $('#sid-' + code + ' .chk input').prop('disabled', true);

	    

	    $.ajax({

			dataType: "json",

			url: '/bitrix/admin/linemedia.auto_suppliers_stat.php?lang=<?=LANGUAGE_ID?>&ajax=check_search&code=' + code + '&<?=bitrix_sessid_get()?>',

			data: {},

			success: function(json) {

				

				$('#sid-' + code + ' .parts_count').html(json.parts_count);

				$('#sid-' + code + ' .last_update').html(json.last_update);

				$('#sid-' + code + ' .tpls').html(json.tpls);

				$('#sid-' + code + ' .chk input').prop('disabled', false);

				

				callback(json);

			},

			fail: function() {

				alert('JSON failed');

			}

		});

	}

</script>







<style>

.supplier-stat-table {border-collapse: collapse;border: 1px solid #ddd; border-spacing: 0;
	border-collapse: collapse; margin-top:20px;
}

.supplier-stat-table th, .supplier-stat-table td {border: 1px solid #ddd; padding: 8px;
	line-height: 1.42857143;
	vertical-align: top;
	border-top: 1px solid #ddd;
	text-align:left;
	background-color:#fff;
}
.supplier-stat-table tr:nth-child(odd)>td {
	background-color: #f9f9f9;
}
.supplier-stat-table tr:nth-child(odd)>th {
	background-color: #fff;
}
span.warning{color:red}

</style>







<?php require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
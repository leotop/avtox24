<?php

// run from cmd
if(defined('STDIN')) {
	define("NOT_CHECK_PERMISSIONS",true); 
	$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	IncludeModuleLangFile(__FILE__);
	
	$check = (string) $argv[1];
	switch($check) {
		case 'downloader':
			CModule::IncludeModule('linemedia.autodownloader');
			$agent = new LinemediaAutoDownloaderDownloadAgent;
		break;
		case 'convert':
			$agent = new LinemediaAutoConverterAgent;
		break;
		case 'import':
			$agent = new LinemediaAutoImportAgent;
		break;
		default:
			die('incorrect check');
	}
	
	
	// set php settings
	ini_set('display_errors', true);
	set_time_limit(0);
	
	// run agent
	$agent::$output_debug = true;
	$agent::run();
	
	die(GetMessage('LM_AUTO_AGENT_FINISHED') . ' [FINISH]');
}



require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO_MODULE_NOT_INSTALLED');
    return;
}


/*
 * Запуск процесса
 */
if ($_POST['ajax'] == 'start_agent') {
	ini_set('display_errors', true);
	
	$type = trim((string) $_POST['type']);
	if(!in_array($type, array('downloader', 'convert', 'import'))) {
		$type = 'import';
	}
	
	$run_file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/admin/agents_check.php';
	$log_file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/linemedia_auto_agents_' . $type . '_check.log';
	if(file_exists($log_file))
		unlink($log_file);
	
	$cmd = "/usr/bin/php $run_file $type > $log_file 2>&1 &";
	system($cmd);
	exit;
}


/*
* Получение лога
*/
if ($_POST['ajax'] == 'get_log') {
	$type = trim((string) $_POST['type']);
	if(!in_array($type, array('downloader', 'convert', 'import'))) {
		$type = 'import';
	}
	$log_file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/linemedia_auto_agents_'.$type.'_check.log';
	if(!file_exists($log_file))
		die('No log file exist! Is /bitrix/tmp/ folder exist? ');
	
	$log = file_get_contents($log_file);
	
	$log = explode("\n", $log);
	foreach($log AS $k => $str) {
		if(preg_match('#GConf Error#is', $str))
			unset($log[$k]);
		if(preg_match('#Unknown namespace uri#is', $str))
			unset($log[$k]);
		if(trim($str)=='')
			unset($log[$k]);
	}
	
	$log = join("\n", $log);
	
	$log = nl2br($log);
	die($log);
}

$APPLICATION->SetTitle(GetMessage("LM_AUTO_AGENTS_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
CUtil::InitJSCore(array('window', 'jquery'));
?>


<?= BeginNote() ?>
	<b><?= GetMessage('LM_AUTO_AGENTS_CHECK_MESSAGE') ?></b>
<?= EndNote() ?>


<div class="agents-check">
	<div class="agent-downloader">
		<input type="button" class="start-process" data-type="downloader" value="<?=GetMessage('LM_AUTO_AGENT_DOWNLOADER_START_BUTTON')?>" />
		<div class="agent-log" id="lm-auto-agents-downloader-check-result"></div>
	</div>
	<div class="agent-convert">
		<input type="button" class="start-process" data-type="convert" value="<?=GetMessage('LM_AUTO_AGENT_CONVERT_START_BUTTON')?>" />
		<div class="agent-log" id="lm-auto-agents-convert-check-result"></div>
	</div>
	<div class="agent-import">
		<input type="button" class="start-process" data-type="import" value="<?=GetMessage('LM_AUTO_AGENT_IMPORT_START_BUTTON')?>" />
		<div class="agent-log" id="lm-auto-agents-import-check-result"></div>
	</div>
</div>


<script>
	var refresh_downloader = null;
	var refresh_import = null;
	var refresh_convert = null;
	$('.start-process').click(function(){
		
		$(this).prop('disabled', true);
		var type = $(this).data('type');
		$('#lm-auto-agents-'+type+'-check-result').html('');
		
		$.ajax({
		  type: "POST",
		  url: '/bitrix/admin/linemedia.auto_agents_check.php?lang=<?=LANGUAGE_ID?>',
		  data: {ajax:'start_agent', type:type},
		  success: function(html){
			  
			  
			  $('#lm-auto-agents-'+type+'-check-result').show().html(html);
			  $('.start-'+type+'-process').prop('disabled', false);
			  
			  
			  var refresh = setInterval(function(){
				  $.ajax({
					  type: "POST",
					  url: '/bitrix/admin/linemedia.auto_agents_check.php?lang=<?=LANGUAGE_ID?>',
					  data: {ajax:'get_log', type:type},
					  success: function(html){
						  $('#lm-auto-agents-'+type+'-check-result').html(html);
						  
						  if(html.indexOf('[FINISH]') > 0) {
							  switch(type) {
								  case 'downloader':
								  	clearInterval(refresh_downloader);
								  	$('.agent-downloader .start-process').prop('disabled', false);
								  break;
								  case 'convert':
								  	clearInterval(refresh_convert);
								  	$('.agent-convert .start-process').prop('disabled', false);
								  break;
								  case 'import':
								  	clearInterval(refresh_import);
								  	$('.agent-import .start-process').prop('disabled', false);
								  break;
							  }
						  }
						  
					  }
					});
			  }, 1000);
			  
			  switch(type) {
				  case 'downloader':
				  	refresh_downloader = refresh;
				  break;
				  case 'convert':
				  	refresh_convert = refresh;
				  break;
				  case 'import':
				  	refresh_import = refresh;
				  break;
			  }
			  
		  }
		});
	})
</script>

<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

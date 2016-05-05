<?
/**
 * Linemedia Autoportal
 * Downloader module
 * Admin file
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autodownloader")) {
    ShowError('MODULE NOT INSTALLED');
    return;
}


if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}

$linemedia_autodownloaderModulePermissions = $APPLICATION->GetGroupRight("linemedia.autodownloader");
if ($linemedia_autodownloaderModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autodownloader/include.php");

ClearVars();



/*
 * Доступные поставщики
 */
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList();
foreach ($suppliers_res as $supplier) {
	$suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}

/*
 * Доступные протоколы
 */
$protocols = LinemediaAutoDownloaderMain::getProtocols();

/*
 * Варианты колонок
 */
$columns = array(
	'brand_title',
    'article',
    'title',
    'price',
    'quantity',
//    'bulk',
    'weight'
);

/*
 * Названия колонок
 */
$column_titles = array();

foreach ($columns as $column) {
    $column_titles []= GetMessage('LM_AUTO_DOWNLOADER_SOURCE_COLUMN_'.strtoupper($column));
}


/*
 * Дополнительные пользовательские колоноки
 */
$lmfields = new LinemediaAutoCustomFields(); 

$custom_fields = $lmfields->getFields();
 
foreach ($custom_fields as $custom_field) {
    $columns        []= $custom_field['code'];
    $column_titles  []= $custom_field['name'];
}




/*
 * Возможные валюты
 */
$currencies = array();
$lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
while ($cur = $lcur->Fetch()) {
    $currencies[$cur['CURRENCY']] = $cur;
}


/*
 * Проверка подключения
 */
if ($_GET['ajax'] == 'checkConnection') {
	$protocol = trim(strval($_POST['protocol']));
	if(!isset($protocols[$protocol]))
		die(GetMessage('ERROR_PROTOCOL'));
	
	$protocol_data = array();
	foreach ($protocols[$protocol]['config'] as $code => $data) {
		$protocol_data[$code] = $_POST[$protocol . '_' . $code];
	}
	
	$classname = 'LinemediaAutoDownloader' . ucfirst($protocol) . 'Protocol';
	$instance = new $classname($protocol_data);
	
	$error = false;
	try {
		$error = $instance->download(true);
		if ($error === true) {
			die('OK');
        }
	} catch (Exception $e) {
		$error = $e->GetMessage();
	}
	
	if ($error) {
		die($error);
    }
	die('OK');
}




$ID = IntVal($ID);
if ($ID > 0) {
	$task = LinemediaAutoTask::GetByID($ID);
	$task = $task->Fetch();
	try {
		$task['connection'] = unserialize($task['connection']);
		$task['conversion'] = unserialize($task['conversion']);
	} catch (Exception $e) {
		// а пофиг, прсто чтоб ошибка не вылезала
		$task['connection'] = array();
		$task['conversion'] = array();
	}
	
	$task['shedule'] = LinemediaAutoTaskShedule::GetByTaskID($task['id']);
	$task['shedule'] = $task['shedule']->Fetch();
	
	$task['shedule']['days'] = array_filter(array_map('intval', explode(',',  $task['shedule']['days'])));
	
	$start_time = explode(':', $task['shedule']['start_time']);
	$task['shedule']['start_hour'] = $start_time[0];
	$task['shedule']['start_minute'] = $start_time[1];
}
//_d($task);
	
$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $linemedia_autodownloaderModulePermissions=="W" && check_bitrix_sessid())
{


	/*
	 * Main
	 */
	$title = trim(strval($_POST['title']));
	if($title == '')
		$strError .= GetMessage('ERROR_TITLE') .'<br>';
	
	$active = ($_POST['active'] == 'Y') ? 'Y' : 'N';
	
	$interval = (int) $_POST['interval'];
	if ($interval < 0)
		$strError .= GetMessage("ERROR_INTERVAL")."<br>";
	
	$supplier_id = trim(strval($_POST['supplier_id']));
	if($supplier_id == '')
		$strError .= GetMessage('ERROR_SUPPLIER') .'<br>';

	
	/*
	 * Connection
	 */
	$protocol = trim(strval($_POST['protocol']));
	if(!isset($protocols[$protocol]))
		$strError .= GetMessage('ERROR_PROTOCOL') .'<br>';
	
	$protocol_data = array();
	foreach ($protocols[$protocol]['config'] as $code => $data) {
		$protocol_data[$code] = $_POST[$protocol . '_' . $code];
	}
	
	$connection = array(
		'protocol' => $protocol,
		$protocol => $protocol_data,
	);
	
	
	
	/*
	* Conversion
	*/
	$source_type = trim(strval($_POST['source_type']));
	$source_encoding = trim(strval($_POST['source_encoding']));
	$source_skip_lines = intval($_POST['source_skip_lines']);
	$source_separator = strval($_POST['source_separator']);
	
	$column_replacements = array();
	$column_replacements_src = (array) $_POST['conversion']['column_replacements'];
	foreach ((array) $column_replacements_src['column'] AS $i => $column) {
		$column = trim(strval($column));
		$what = trim(strval($column_replacements_src['what'][$i]));
		$with = trim(strval($column_replacements_src['with'][$i]));
		if ($column != '' && $what != '') {
			$column_replacements[$column][$what] = $with;
		}
	}
	
	
	$column_replacements_all = array();
	$column_replacements_all_src = (array) $_POST['conversion']['column_replacements_all'];
	foreach ($column_replacements_all_src as $column => $replacement) {
		$column = trim(strval($column));
		$replacement = trim(strval($replacement));
		if ($column != '' && $replacement != '') {
			$column_replacements_all[$column] = $replacement;
		}
	}
	
	
	$conversion = array(
		'type' => $source_type,
		'encoding' => $source_encoding,
		'skip_lines' => $source_skip_lines,
		'separator' => $source_separator,
		'column_replacements' => $column_replacements,
		'column_replacements_all' => $column_replacements_all,
	);
	foreach ($columns as $column) {
		$conversion['columns'][$column] = (int) $_POST['conversion']['columns'][$column];
    }
	
	/*
	 * Shedule
	 */
	$interval 						= intval($_POST['interval']);
	$interval_daily_start_hour 		= intval($_POST['interval_daily_start_hour']);
    $interval_daily_start_minute 	= intval($_POST['interval_daily_start_minute']);
    $interval_daily_days 			= array_map('intval', (array) $_POST['interval_daily_days']);
    $interval_monthly_start_day 	= strval($_POST['interval_monthly_start_day']);
    $interval_monthly_start_hour 	= intval($_POST['interval_monthly_start_hour']);
    $interval_monthly_start_minute 	= intval($_POST['interval_monthly_start_minute']);
	$shedule = array(
		'interval' 		=> $interval,
		'days'			=> '', // обязательнос сбросить лишние значения ждля простоты выборки записей из БД
		'start_time'	=> '',
		'start_day'		=> '',
	);
	
	if ($interval == 86400) {
		$shedule['start_time'] = $interval_daily_start_hour . ':' . $interval_daily_start_minute . ':00';
		$shedule['days'] = join(',', $interval_daily_days);
	} elseif ($interval == 2592000) {
		$shedule['start_time'] = $interval_monthly_start_hour . ':' . $interval_monthly_start_minute . ':00';
		$shedule['start_day'] = $interval_monthly_start_day;
	}
	
	

	if (strlen($strError) <= 0)
	{
		unset($arFields);
		$arFields = array(
			"title" => $title,
			"active" => $active,
			"supplier_id" => $supplier_id,
			"protocol" => $protocol,
			"connection" => $connection,
			"conversion" => $conversion,
		);
		
		
		if ($ID>0)
		{
			$task_obj = new LinemediaAutoTask;
			if (!$task_obj->Update($ID, $arFields))
				$strError .= GetMessage("ERROR_EDIT")."<br>";
			
			$shed_obj = new LinemediaAutoTaskShedule;
			$shedule['task_id'] = $ID;
			$SHEDULE_ID = $task['shedule']['id'];
			if (!$shed_obj->Update($SHEDULE_ID, $shedule)) {
				$strError .= GetMessage("ERROR_EDIT")."<br>";
            }
		} else {
			$task_obj = new LinemediaAutoTask;
			$ID = $task_obj->Add($arFields);
			if ($ID<=0)
				$strError .= GetMessage("ERROR_ADD")."<br>";
			
			$shedule['task_id'] = $ID;
			$shed_obj = new LinemediaAutoTaskShedule;
			$SHEDULE_ID = $shed_obj->Add($shedule);
			if ($ID<=0)
				$strError .= GetMessage("ERROR_ADD")."<br>";
		}
	}
	
	
	if (strlen($strError)>0) $bInitVars = True;

	if (strlen($save)>0 && strlen($strError)<=0)
		LocalRedirect("linemedia.autodownloader_list.php?lang=".LANG.GetFilterParams("filter_", false));
	
	if (strlen($apply)>0 && strlen($strError)<=0)
		LocalRedirect("linemedia.autodownloader_list.php?ID=".$ID."&lang=".LANG.GetFilterParams("filter_", false));
}

/*if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_lm_downloader_tasks", "", "str_");
}*/







$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("LM_AUTO_DOWNLOADER_EDIT_TASK")) : GetMessage("LM_AUTO_DOWNLOADER_NEW_TASK");
$APPLICATION->SetTitle($sDocTitle);

$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.7.1/jquery.min.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>
<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("LM_AUTO_DOWNLOADER_TASKS_LIST"),
				"LINK" => "/bitrix/admin/linemedia.autodownloader_list.php?lang=".LANG,
				"ICON" => "btn_list"
			)
	);

if ($ID > 0 && $linemedia_autodownloaderModulePermissions >= "W") {
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("LM_AUTO_DOWNLOADER_NEW_TASK"),
			"LINK" => "/bitrix/admin/linemedia.autodownloader_add.php?lang=".LANG.GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("LM_AUTO_DOWNLOADER_DELETE_TASK"),
			"LINK" => "javascript:if(confirm('".GetMessage("LM_AUTO_DOWNLOADER_DELETE_TASK_CONFIRM")."')) window.location='/bitrix/admin/linemedia.autodownloader_add.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
			"ICON" => "btn_delete"
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?php
if(strlen($strError)>0)
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$strError, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SDEN_ERROR"), "HTML"=>true));?>

<form method="POST" action="/bitrix/admin/linemedia.autodownloader_add.php?lang=<?=LANG?>&ID=<?=$ID?>" name="form1" id="lm-auto-down-add-task-frm">
<?= GetFilterHiddens("filter_"); ?>
<input type="hidden" name="Update" value="Y" />
<input type="hidden" name="lang" value="<?= LANG ?>" />
<input type="hidden" name="ID" value="<?= $ID ?>" />
<?= bitrix_sessid_post() ?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LM_AUTO_DOWNLOADER_TAB_MAIN"), "ICON" => "linemedia.autodownloader.main", "TITLE" => GetMessage("LM_AUTO_DOWNLOADER_TAB_MAIN")),
	array("DIV" => "edit2", "TAB" => GetMessage("LM_AUTO_DOWNLOADER_TAB_CONNECTION"), "ICON" => "linemedia.autodownloader.main", "TITLE" => GetMessage("LM_AUTO_DOWNLOADER_TAB_CONNECTION")),
	array("DIV" => "edit3", "TAB" => GetMessage("LM_AUTO_DOWNLOADER_TAB_CONVERSION"), "ICON" => "linemedia.autodownloader.main", "TITLE" => GetMessage("LM_AUTO_DOWNLOADER_TAB_CONVERSION")),
	array("DIV" => "edit4", "TAB" => GetMessage("LM_AUTO_DOWNLOADER_TAB_SHEDULE"), "ICON" => "linemedia.autodownloader.main", "TITLE" => GetMessage("LM_AUTO_DOWNLOADER_TAB_SHEDULE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<? if ($ID > 0) { ?>
		<tr>
			<td width="40%"><?= GetMessage('LM_AUTO_DOWNLOADER_ID') ?>:</td>
			<td width="60%"><?= $ID ?></td>
		</tr>
	<? } ?>

	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("LM_AUTO_DOWNLOADER_TASK_TITLE") ?>:</td>
		<td width="60%"><input type="text" name="title" value="<?=$task['title']?>" size="40"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("LM_AUTO_DOWNLOADER_ACTIVE")?>:</td>
		<td width="60%">
			<input type="checkbox" name="active" value="Y" <?=($task['active'] == "N") ? '' :  "checked";?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SUPPLIER");?>:</td>
		<td width="60%" valign="top">
			<select name="supplier_id">
			<?foreach($suppliers AS $sid => $supplier){?>
				<option value="<?=htmlspecialchars($sid)?>" <?=$task['supplier_id'] == $sid ? 'selected':''?>><?=htmlspecialchars($supplier['NAME'])?></option>
			<?}?>
			</select>
		</td>
	</tr>


<?
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_PROTOCOL");?>:</td>
		<td width="60%" valign="top">
			<select id="protocol" name="protocol">
			<? foreach ($protocols AS $pid => $protocol) { ?>
				<option value="<?=htmlspecialchars($pid)?>" <?=($task['protocol'] == $pid) ? 'selected':''?><?=($protocol['available'])?'':' disabled'?>><?=htmlspecialchars($protocol['title'])?></option>
			<? } ?>
			</select>
			
			<input type="button" value="<?=GetMessage("LM_AUTO_DOWNLOADER_TEST_PROTOCOL_BUTTON")?>" id="protocol-test-btn">
			<div id="connection-status-marker" style="display:none"></div>
		</td>
	</tr>

<?php
foreach ($protocols as $pid => $protocol) {
		$vals = (array) $task['connection'][$pid];
	
		/*
         * Все настройки протокола
         */
        foreach($protocol['config'] as $config_code => $config_data) {
        
        	$id = $pid . '_' . $config_code;
            
            if ($ID > 0) {
	            $value = $vals[$config_code];
            } else {
            	$value = COption::GetOptionString('linemedia.autodownloader', $id, $config_data['default']);
			}
			
			?>
			<tr class="<?=$config_data['required']?'adm-detail-required-field ':''?>protocol protocol-<?=htmlspecialchars($pid)?>" style="display:none">
			    <td width="40%" valign="top">
			        <label for="<?=$id?>"><?=$config_data['title']?>:</label>
			    </td>
			    <td width="60%" valign="top">
			        <?
			        $placeholder = ($config_data['placeholder'] != '') ? ' placeholder="'.$config_data['placeholder'].'"' : '';
			        $size = ($config_data['size'] > 0) ? ' size="'.$config_data['size'].'"' : '';
			        switch ($config_data['type']) {
			            case 'password':
			                echo '<input type="password" name="' . $id . '" id="' . $id . '" value="' . $value . '" '.$placeholder.$size.'/>';
                            break;
			            case 'checkbox':
			                echo '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="true" ' . ($value ? 'checked' : '') . ' />';
                            break;
			            case 'string':
			            default:
			                echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" '.$placeholder.$size.'/>';
			        }
			        ?>
			        <? if ($config_data['description'] != '') { ?>
                        <p><?= $config_data['description'] ?></p>
			        <? } ?>
			    </td>
			</tr>
			<?
        }
}
?>


<? $tabControl->BeginNextTab(); ?>


<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_TYPE") ?>:</td>
	<td width="60%" valign="top">
		<select name="source_type">
			<option value=""<?=($task['conversion']['type'] == '') ? ' selected' : ''?>><?=GetMessage("LM_AUTO_DOWNLOADER_SOURCE_TYPE_AUTODETECT")?></option>
			<option value="csv"<?=($task['conversion']['type'] == 'csv') ? ' selected' : ''?>>CSV</option>
			<? if (LinemediaAutoDownloaderMain::isConversionSupported()) { ?>}
    			<option value="xls"<?=($task['conversion']['type']=='xls') ? ' selected' : ''?>>XLS</option>
    			<option value="xlsx"<?=($task['conversion']['type']=='xslx') ? ' selected' : ''?>>XLSX</option>
			<? } else { ?>
    			<option value="xls" disabled>XLS</option>
    			<option value="xlsx" disabled>XLSX</option>
			<? } ?>
		</select>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_ENCODING") ?>:</td>
	<td width="60%" valign="top">
		<select name="source_encoding">
			<option value=""<?=($task['conversion']['encoding']=='')?' selected':''?>><?=GetMessage("LM_AUTO_DOWNLOADER_SOURCE_ENCODING_AUTODETECT")?></option>
			<option value="Windows-1251"<?=($task['conversion']['encoding']=='Windows-1251')?' selected':''?>>Windows-1251</option>
			<option value="utf-8"<?=($task['conversion']['encoding']=='utf-8')?' selected':''?>>utf-8</option>
		</select>
	</td>
</tr>


<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_SKIP_LINES") ?>:</td>
	<td width="60%" valign="top">
		<input type="text" name="source_skip_lines" value="<?=$task['conversion']['skip_lines']?>" placeholder="0" size="5" />
	</td>
</tr>


<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_SEPARATOR") ?>:</td>
	<td width="60%" valign="top">
		<input type="text" name="source_separator" size="2" value="<?=($task['conversion']['separator']) ? $task['conversion']['separator'] : ';'?>" placeholder=";">
	</td>
</tr>


<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_COLUMNS") ?>:</td>
	<td width="60%" valign="top">
		<? foreach ($columns as $i => $column) { ?>
			<?$val = $task['conversion']['columns'][$column] ? $task['conversion']['columns'][$column] : $i+1 ?>
			<input type="text" id="col_<?= $column ?>" name="conversion[columns][<?=$column?>]" value="<?= $val ?>" size="5" />
			<label for="col_<?= $column ?>"><?= $column_titles[$i] ?></label>
			<br/>
		<? } ?>
	</td>
</tr>


<tr class="heading" id="tr_IBLOCK_ELEMENT_PROP_VALUE">
    <td colspan="2"><?= GetMessage("LM_AUTO_DOWNLOADER_REPLACEMENTS") ?></td>
</tr>


<?/*******************************************************************************************************/?>

<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_DOWNLOADER_SOURCE_COLUMNS_REPLACE")?>:</td>
	<td width="60%" valign="top">
		<div class="conversion-replacement">
			<select name="conversion[column_replacements][column][]">
    			<? foreach ($columns as $i => $column) { ?>
    				<option value="<?= $column ?>"><?= $column_titles[$i] ?></option>
    			<? } ?>
			</select>
			<input type="text" name="conversion[column_replacements][what][]" value="" placeholder="<?=GetMessage('REPLACE_WHAT')?>" size="15" />
			<input type="text" name="conversion[column_replacements][with][]" value="" placeholder="<?=GetMessage('REPLACE_WITH')?>" size="15" />
			<input type="button" value="-" class="conversion-replacement-del" />
		</div>
		
		<? foreach ($task['conversion']['column_replacements'] as $column => $resplacements) { ?>
			<? foreach ($resplacements as $what => $with){?>
				<div class="conversion-replacement">
					<select name="conversion[column_replacements][column][]">
					<? foreach ($columns as $i => $r_column) { ?>
						<option value="<?=$r_column?>"<?=$column==$r_column ? ' selected':''?>>
                            <?= $column_titles[$i] ?>
                        </option>
					<? } ?>
					</select>
					<input type="text" name="conversion[column_replacements][what][]" value="<?=htmlspecialchars($what)?>" placeholder="<?=GetMessage('REPLACE_WHAT')?>" size="15" />
					<input type="text" name="conversion[column_replacements][with][]" value="<?=htmlspecialchars($with)?>" placeholder="<?=GetMessage('REPLACE_WITH')?>" size="15" />
					<input type="button" value="-" class="conversion-replacement-del" />
				</div>
			<?}?>
		<?}?>
		<input type="button" value="+" id="conversion-replacement-add" />
	</td>
</tr>


<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_COLUMNS_REPLACE_ALL") ?>:</td>
	<td width="60%" valign="top">
		<? foreach ($columns as $i => $column) { ?>
			<?$val = $task['conversion']['column_replacements_all'][$column]?>
			<input size="12" type="text" id="col_<?=$column?>" name="conversion[column_replacements_all][<?=$column?>]" value="<?=$val?>" size="5" />
			<label for="col_<?= $column ?>"><?= $column_titles[$i] ?></label>
			<br/>
		<? } ?>
	</td>
</tr>

<? $tabControl->BeginNextTab(); ?>

<tr>
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL")?>:</td>
	<td width="60%" valign="top">
		<select name="interval" id="interval">
			<option value=""<?=($task['shedule']['interval'] <= 0)?' selected':''?>><?=GetMessage("LM_AUTO_INTERVAL_NOT_SELECTED")?></option>
			<option value="3600"<?=($task['shedule']['interval'] == 3600)?' selected':''?>><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_HORLY")?></option>
			<option value="86400"<?=($task['shedule']['interval'] == 86400)?' selected':''?>><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_DAILY")?></option>
			<option value="2592000"<?=($task['shedule']['interval'] == 2592000)?' selected':''?>><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_MONTHLY")?></option>
		</select>
	</td>
</tr>

<tr style="display:none" class="interval interval-86400">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_TIME")?>:</td>
	<td width="60%" valign="top">
		<select name="interval_daily_start_hour">
			<? for ($i = 0; $i < 24; $i++) { ?>
				<option value="<?=sprintf('%02d', $i)?>"<?=($task['shedule']['start_hour'] == $i)?' selected':''?>><?=sprintf('%02d', $i)?></option>
			<? } ?>
		</select>
		<select name="interval_daily_start_minute">
			<? for ($i = 0; $i < 60; $i += 5) { ?>
				<option value="<?=sprintf('%02d', $i)?>"<?=($task['shedule']['start_minute'] == $i)?' selected':''?>><?=sprintf('%02d', $i)?></option>
			<? } ?>
		</select>
	</td>
</tr>
<tr style="display:none" class="interval interval-86400">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_DAY")?>:</td>
	<td width="60%" valign="top">
		<? for ($i = 1; $i < 8; $i++) { ?>
			<input type="checkbox" name="interval_daily_days[]" value="<?=$i?>" id="interval_daily_day<?=$i?>" value="<?=$i?>" <?=in_array($i, $task['shedule']['days'])?' checked':''?> />
			<label for="interval_daily_day<?=$i?>"><?=GetMessage("DAY_" . $i)?></label>
			<br>
		<? } ?>
	</td>
</tr>
<tr style="display:none" class="interval interval-2592000">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_DAY")?>:</td>
	<td width="60%" valign="top">
		<select name="interval_monthly_start_day">
			<? for ($i = 1; $i < 28; $i++) { ?>
				<option value="<?=$i?>"<?=($task['shedule']['start_day'] == $i)?' selected':''?>><?=$i?></option>
			<? } ?>
			<option value="last"><?=GetMessage("LM_AUTO_DOWNLOADER_LAST_DAY")?></option>
		</select>
	</td>
</tr>
<tr style="display:none" class="interval interval-2592000">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_DOWNLOADER_INTERVAL_TIME")?>:</td>
	<td width="60%" valign="top">
		<select name="interval_monthly_start_hour">
			<? for ($i = 0; $i < 24; $i++) { ?>
				<option value="<?=sprintf('%02d', $i)?>"<?=($task['shedule']['start_hour'] == $i)?' selected':''?>><?=sprintf('%02d', $i)?></option>
			<? } ?>
		</select>
		<select name="interval_monthly_start_minute">
			<? for ($i = 0; $i < 60; $i += 5) { ?>
				<option value="<?=sprintf('%02d', $i)?>"<?=($task['shedule']['start_minute'] == $i)?' selected':''?>><?=sprintf('%02d', $i)?></option>
			<? } ?>
		</select>
	</td>
</tr>



<? $tabControl->EndTab(); ?>

<?
$tabControl->Buttons(array(
	"disabled" => ($linemedia_autodownloaderModulePermissions < "W"),
	"back_url" => "/bitrix/admin/linemedia.autodownloader_delivery.php?lang=".LANG.GetFilterParams("filter_")
));
?>

<? $tabControl->End(); ?>

</form>


<script>
    $(document).ready(function() {
    	showProtocolFields();
    	$('#protocol').change(function() {
    		showProtocolFields();
    	});
    	
    	$('#protocol-test-btn').click(function() {
    		$(this).attr('disabled', true);
    		$.ajax({
    		  url: "/bitrix/admin/linemedia.autodownloader_add.php?lang=<?=LANG?>&ajax=checkConnection",
    		  type : 'POST',
    		  data: $('#lm-auto-down-add-task-frm').serialize()
    		}).done(function(data) {
    			$('#connection-status-marker').show();
    		  if(data == 'OK')
    		  {
    			  $('#connection-status-marker').removeClass('connection-status-marker-error');
    			  $('#connection-status-marker').addClass('connection-status-marker-ok');
    		  } else {
    			  $('#connection-status-marker').addClass('connection-status-marker-error');
    			  $('#connection-status-marker').removeClass('connection-status-marker-ok');
    			  alert(data);
    		  }
    		});
    		$(this).attr('disabled', false);
    	});
    	
    	showIntervalFields();
    	$('#interval').change(function(){
    		showIntervalFields();
    	});
    	
    });
    
    function showProtocolFields()
    {
    	$('.protocol').hide();
    	var protocol = $('#protocol').val();
    	$('.protocol-' + protocol).show();
    }
    
    function showIntervalFields()
    {
    	var interval = $('#interval').val();
    	$('.interval').hide();
    	$('.interval-' + interval).show();
    }
    
    
    $('#conversion-replacement-add').click(function(){
    	var $div = $('.conversion-replacement:last');
    	var html = '<div class="conversion-replacement">' + $div.html() + '</div>';
    	$div.after(html);
    });
    
    $('.conversion-replacement-del').click(function(){
    	$(this).parent().remove();
    });
</script>

<? require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php"); ?>
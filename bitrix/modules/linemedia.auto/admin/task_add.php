<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}


$linemedia_ModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($linemedia_ModulePermissions < "W") {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

global $USER, $APPLICATION;

$supplierId = 0;
//$accessibleSuppliers = array();

$lm_rights = new LinemediaAutoRightsEntity(LinemediaAutoRightsEntity::$ENTITY_TYPE_PRICE);

$userPermissions = $lm_rights->getDefaultRights();

// эквивалентно $lm_rights->getDefaultRights();
//$userPermissions = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT));


$readAccess = array(
    LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS,
   // LM_AUTO_MAIN_ACCESS_READ
);


if (strcmp($userPermissions, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// доступы у поставщиков проверяются внутри класса!!! никаких отдельных фильтров не нужно !!!
//foreach (\LinemediaAutoSupplier::getAllowedSuppliers() as $supplier) {
//
//    \CModule::IncludeModule('iblock');
//    $dbRes = \CIBlockElement::GetProperty(\COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), $supplier, array(), array('CODE' => 'supplier_id'))->Fetch();
//    $accessibleSuppliers[] = $dbRes['VALUE'];
//
//}


IncludeModuleLangFile(__FILE__);

ClearVars();

/*
 * Настройки страницы
 */
$arPageSettings = array(
    'LIST_PAGE' => 'linemedia.auto_task_list.php',
    'ADD_PAGE' => 'linemedia.auto_task_add.php',
);

/*
 * Cоздаём событие
 */
$events = GetModuleEvents('linemedia.auto', 'OnBeforeTaskAddPageBuild');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arPageSettings));
}


/*
 * Доступные поставщики
 */
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList();
foreach ($suppliers_res as $supplier) {
	$suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}

$events = GetModuleEvents("linemedia.auto", "AfterGetSuppliersList");
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$suppliers));
}

/*
 * Доступные протоколы
 */
$protocols = LinemediaAutoTasker::getProtocols();

/*
 * Варианты колонок
 */
$columns = array(
	'brand_title',
    'article',
    'title',
    'price',
    'quantity',
    'group_id',
    'weight'
);

/*
 * Названия колонок
 */
$column_titles = array();

foreach ($columns as $column) {
    $column_titles []= GetMessage('LM_AUTO_SOURCE_COLUMN_'.strtoupper($column));
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

// объект контроля доступа
$lm_rights = new LinemediaAutoRightsEntity(LinemediaAutoRightsEntity::$ENTITY_TYPE_PRICE);

/*
 * Проверка подключения.
 */
if ($_GET['ajax'] == 'checkConnection') {
	$protocol = trim(strval($_POST['protocol']));
	if (!isset($protocols[$protocol])) {
		die (GetMessage('ERROR_PROTOCOL'));
    }
	$protocol_data = array();
	foreach ($protocols[$protocol]['config'] as $code => $data) {
		$protocol_data[$code] = $_POST[$protocol . '_' . $code];
	}

    // Объект класса выбранного протокола.
    $instance = LinemediaAutoTasker::getProtocolInstance(strtolower($protocol), $protocol_data);

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



/*
 * Изменилось поле, надо обновить данные
 */
if ($_GET['ajax'] == 'formUpdated') {

	$protocol = trim(strval($_GET['protocol']));
	if (!isset($protocols[$protocol])) {
		die (GetMessage('ERROR_PROTOCOL'));
    }
	$protocol_data = array();
	foreach ($protocols[$protocol]['config'] as $code => $data) {
		$protocol_data[$code] = $_POST[$protocol . '_' . $code];
	}

    // Объект класса выбранного протокола.
    $instance = LinemediaAutoTasker::getProtocolInstance(strtolower($protocol), $protocol_data);


    $method = 'ajax' . ucfirst(strval($_GET['func']));
    if(!method_exists($instance, $method) || substr($method, 0, 2) == '__')
    	die('no method');

	$error = false;
	try {
		$js = $instance->$method($protocol_data);
		die($js);
	} catch (Exception $e) {
		$error = $e->GetMessage();
	}

	if ($error) {
		die($error);
    }
	die('OK');
}


$ID = intval($ID);

if ($ID > 0) {

    $element_right = $lm_rights->getRight($ID);

    if($element_right < LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS) {
        $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
    }

    $task = LinemediaAutoTask::GetByID($ID);
	$task = $task->Fetch();

	$supplierId = $task['supplier_id'];

    $task['connection'] = unserialize(strVal($task['connection']));
    $task['conversion'] = unserialize(strVal($task['conversion']));

	if(!is_array($task['connection'])) $task['connection'] = array();
    if(!is_array($task['conversion'])) $task['conversion'] = array();

	$task['shedule'] = LinemediaAutoTaskShedule::GetByTaskID($task['id']);
	$task['shedule'] = $task['shedule']->Fetch();

	$task['shedule']['days'] = array_filter(array_map('intval', explode(',',  $task['shedule']['days'])));

	$start_time = explode(':', $task['shedule']['start_time']);
	$task['shedule']['start_hour'] = $start_time[0];
	$task['shedule']['start_minute'] = $start_time[1];

} else if($userPermissions < LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


$strError = "";
$bInitVars = false;
if ((strlen($save) > 0 || strlen($apply) > 0) && $REQUEST_METHOD == "POST" && $linemedia_ModulePermissions == "W" && check_bitrix_sessid()) {

	/*
	 * Основные настройки.
	 */
	$title = trim(strval($_POST['title']));
	if ($title == '') {
		$strError .= GetMessage('ERROR_TITLE') .'<br>';
    }
	$active = ($_POST['active'] == 'Y') ? 'Y' : 'N';

	$interval = (int) $_POST['interval'];
	if ($interval < 0) {
		$strError .= GetMessage("ERROR_INTERVAL")."<br>";
    }

	$supplier_id = trim(strval($_POST['supplier_id']));
	if ($supplier_id == '') {
		$strError .= GetMessage('ERROR_SUPPLIER') .'<br>';
    }

    $mode  = trim(strval($_POST['mode']));
    $email = trim(strval($_POST['email']));

	/*
	 * Соединение.
	 */
	$protocol = trim(strval($_POST['protocol']));
	if (!isset($protocols[$protocol])) {
		$strError .= GetMessage('ERROR_PROTOCOL') .'<br>';
	}

	$protocol_data = array();
	foreach ($protocols[$protocol]['config'] as $code => $data) {
		$protocol_data[$code] = $_POST[$protocol . '_' . $code];
	}

	$connection = array(
		'protocol' => $protocol,
		$protocol => $protocol_data,
	);


	/*
	 * Конвертация.
	 */
	$zip_content_filename = trim(strval($_POST['zip_content_filename']));
	$source_type = trim(strval($_POST['source_type']));
	$source_encoding = trim(strval($_POST['source_encoding']));
	$source_skip_lines = intval($_POST['source_skip_lines']);
	$source_separator = strval($_POST['source_separator']);

	$column_replacements = array();
	$column_replacements_src = (array) $_POST['conversion']['column_replacements'];
	foreach ((array) $column_replacements_src['column'] AS $i => $column) {
		$column = trim(strval($column));
		$what = strval($column_replacements_src['what'][$i]);
		$with = strval($column_replacements_src['with'][$i]);
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


	$source_resave = (bool) $_POST['conversion']['source_resave'];

	$conversion = array(
		'zip_content_filename' => $zip_content_filename,
		'type' => $source_type,
		'encoding' => $source_encoding,
		'skip_lines' => $source_skip_lines,
		'separator' => $source_separator,
		'column_replacements' => $column_replacements,
		'column_replacements_all' => $column_replacements_all,
		'source_resave'	=> $source_resave
	);

	foreach ($columns as $column) {
	    $index = (int) $_POST['conversion']['columns'][$column];

		$conversion['columns'][$column] = ($index > 0) ? ($index) : ('');
    }

	/*
	 * Расписание.
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


	if (strlen($strError) <= 0) {
		unset($arFields);
		$arFields = array(
			"title"          => $title,
			"active"         => $active,
			"supplier_id"    => $supplier_id,
			"protocol"       => $protocol,
			"connection"     => $connection,
			"conversion"     => $conversion,
			"mode"           => $mode,
			"email"          => $email,
		);


		if ($ID > 0) {
			$task_obj = new LinemediaAutoTask();
			if (!$task_obj->Update($ID, $arFields)) {
				$strError .= GetMessage("ERROR_EDIT")."<br>";
            }
			$shed_obj = new LinemediaAutoTaskShedule();
			$shedule['task_id'] = $ID;
			$SHEDULE_ID = $task['shedule']['id'];
			if (!$shed_obj->Update($SHEDULE_ID, $shedule)) {
				$strError .= GetMessage("ERROR_EDIT")."<br>";
            }
		} else {
			$task_obj = new LinemediaAutoTask();
			$ID = $task_obj->Add($arFields);
			if ($ID <= 0) {
				$strError .= GetMessage("ERROR_ADD")."<br>";
            }
			$shedule['task_id'] = $ID;
			$shed_obj = new LinemediaAutoTaskShedule();
			$SHEDULE_ID = $shed_obj->Add($shedule);
			if ($ID <= 0) {
				$strError .= GetMessage("ERROR_ADD")."<br>";
            }
		}
	}

	if (strlen($strError) > 0) {
	    $bInitVars = true;
    }

    $lm_rights->saveFromForm($ID);

    /*
     * Cоздаём событие
     */
    $events = GetModuleEvents('linemedia.auto', 'OnAfterPriceTaskAdd');
    while ($arEvent = $events->Fetch()) {
        ExecuteModuleEventEx($arEvent, array(&$ID, &$arFields));
    }

	if (strlen($save) > 0 && strlen($strError) <= 0) {
		LocalRedirect($arPageSettings['LIST_PAGE'] . "?lang=".LANG.GetFilterParams("filter_", false));
    }
	if (strlen($apply) > 0 && strlen($strError) <= 0) {
		LocalRedirect($arPageSettings['ADD_PAGE'] . "?ID=".$ID."&lang=".LANG.GetFilterParams("filter_", false));
    }
}



$sDocTitle = ($ID > 0) ? str_replace("#ID#", $ID, GetMessage("LM_AUTO_EDIT_TASK")) : GetMessage("LM_AUTO_NEW_TASK");

$APPLICATION->SetTitle($sDocTitle);

//$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.7.1/jquery.min.js');
CUtil::InitJSCore(array('window', 'jquery'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// доступы у поставщиков проверяются внутри класса!!! никаких отдельных фильтров не нужно !!!
//if ($supplierId != 0 && !in_array($supplierId, $accessibleSuppliers)) {
//    ShowError(GetMessage('ERROR_ACCESS_TO_APPROPRIATE_SUPPLIER_FORBIDDEN').$ID);
//    return;
//}

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>
<?


$aMenu = array(
	array(
		"TEXT" => GetMessage("LM_AUTO_TASKS_LIST"),
		"LINK" => "/bitrix/admin/" . $arPageSettings['LIST_PAGE'] . "?lang=".LANG,
		"ICON" => "btn_list"
	)
);


if ($ID > 0 && $linemedia_ModulePermissions >= "W" && !in_array($userPermissions, $readAccess)) {
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("LM_AUTO_NEW_TASK"),
		"LINK" => "/bitrix/admin/" . $arPageSettings['ADD_PAGE'] . "?lang=".LANG.GetFilterParams("filter_"),
		"ICON" => "btn_new"
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("LM_AUTO_DELETE_TASK"),
		"LINK" => "javascript:if(confirm('".GetMessage("LM_AUTO_DELETE_TASK_CONFIRM")."')) window.location='/bitrix/admin/" . $arPageSettings['ADD_PAGE'] . "?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete"
    );
}

$events = GetModuleEvents("linemedia.auto", "BeforeTaskAddShowMenu");
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$aMenu));
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<? if (!LinemediaAutoTasker::isConversionSupported()) { ?>
    <?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('LM_AUTO_ERROR_CONVERTING'), 'TYPE' => 'ERROR', 'HTML' => true)) ?>
<? } ?>

<?
if (strlen($strError) > 0) {
	echo CAdminMessage::ShowMessage(Array("DETAILS" => $strError, "TYPE" => "ERROR", "MESSAGE" => GetMessage("SDEN_ERROR"), "HTML" => true));
}
?>
<form method="POST" action="/bitrix/admin/<?=$arPageSettings['ADD_PAGE']?>?lang=<?= LANG ?>&ID=<?= $ID ?>" name="form1" id="lm-auto-down-add-task-frm" enctype="multipart/form-data">
<?= GetFilterHiddens("filter_") ?>
<input type="hidden" name="Update" value="Y" />
<input type="hidden" name="lang" value="<?= LANG ?>" />
<input type="hidden" name="ID" value="<?= $ID ?>" />
<?= bitrix_sessid_post() ?>
<?

// есть не только загрузка файла, но и скачка по расписанию
$has_shedule_tab = count($protocols) > 1;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LM_AUTO_TAB_MAIN"), "TITLE" => GetMessage("LM_AUTO_TAB_MAIN")),
	array("DIV" => "edit2", "TAB" => GetMessage("LM_AUTO_TAB_CONNECTION"), "TITLE" => GetMessage("LM_AUTO_TAB_CONNECTION")),
	array("DIV" => "edit3", "TAB" => GetMessage("LM_AUTO_TAB_CONVERSION"), "TITLE" => GetMessage("LM_AUTO_TAB_CONVERSION")),
);
if($has_shedule_tab) {
	$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("LM_AUTO_TAB_SHEDULE"), "TITLE" => GetMessage("LM_AUTO_TAB_SHEDULE"));
}


$showAccessTab = true;
$events = GetModuleEvents("linemedia.auto", "BeforeTasksAddAccessTabShow");
while ($arEvent = $events->Fetch()) {
    if(!ExecuteModuleEventEx($arEvent, array())) {
        $showAccessTab = false;
    }
}
if($showAccessTab && $userPermissions == LM_AUTO_MAIN_ACCESS_FULL) {
    $aTabs[] = array("DIV" => "rights", "TAB" => GetMessage("LM_AUTO_TAB_RIGHTS"), "TITLE" => GetMessage("LM_AUTO_TAB_RIGHTS"));
}


$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<? if ($ID > 0) { ?>
		<tr>
			<td width="40%"><?= GetMessage('LM_AUTO_ID') ?>:</td>
			<td width="60%"><?= $ID ?></td>
		</tr>
	<? } ?>

	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("LM_AUTO_TASK_TITLE") ?>:</td>
		<td width="60%"><input type="text" name="title" value="<?=$task['title']?>" size="40" <?= in_array($userPermissions, $readAccess) ? 'disabled' : ''; ?> /></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("LM_AUTO_ACTIVE") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="active" value="Y" <?= ($task['active'] == "N") ? '' :  "checked" ?> <?= in_array($userPermissions, $readAccess) ? 'disabled' : ''; ?> >
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SUPPLIER") ?>:</td>
		<td width="60%" valign="top">
			<select name="supplier_id" <?= in_array($userPermissions, $readAccess) ? 'disabled' : ''; ?>>
			<? foreach ($suppliers as $sid => $supplier) { ?>
				<option value="<?= htmlspecialchars($sid) ?>" <?= $task['supplier_id'] == $sid ? 'selected' : '' ?>>
				    <?= htmlspecialchars($supplier['NAME'])?:filter_var($supplier['NAME'], FILTER_SANITIZE_STRING) ?>
			    </option>
			<? } ?>
			</select>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
        <td width="40%"><?= GetMessage("LM_AUTO_TEST_MODE")?>:</td>
        <td width="60%">
            <input type="checkbox" name="mode" id="lm-auto-test-mode-id" value="<?= LinemediaAutoTask::MODE_TEST ?>" <?= ($task['mode'] == LinemediaAutoTask::MODE_TEST) ? "checked" : "" ?> <?= in_array($userPermissions, $readAccess) ? 'disabled' : ''; ?> />
        </td>
    </tr>
    <tr class="adm-detail-required-field">
        <td width="40%"><?= GetMessage("LM_AUTO_TEST_EMAIL")?>:</td>
        <td width="60%">
            <input type="text" name="email" id="lm-auto-email-id" size="40" value="<?= $task['email'] ?>" <?= ($task['mode'] != LinemediaAutoTask::MODE_TEST) ? ('disabled') : ('') ?> />
        </td>
    </tr>

<? $tabControl->BeginNextTab() ?>

	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?= GetMessage("LM_AUTO_PROTOCOL");?>:</td>
		<td width="60%" valign="top">
			<select id="protocol" name="protocol" <?= in_array($userPermissions, $readAccess) ? 'disabled' : ''; ?> >
    			<? foreach ($protocols as $pid => $protocol) { ?>
    				<option value="<?= htmlspecialchars($pid) ?>" <?= ($task['protocol'] == $pid) ? 'selected' : '' ?><?= ($protocol['available'])?'':' disabled' ?>>
    				    <?=htmlspecialchars($protocol['title'])?:filter_var($protocol['title'], FILTER_SANITIZE_STRING)?>
    			    </option>
    			<? } ?>
			</select>

			<input type="button" value="<?= GetMessage("LM_AUTO_TEST_PROTOCOL_BUTTON") ?>" id="protocol-test-btn"  <?= in_array($userPermissions, $readAccess) ? 'disabled' : ''; ?> />
			<div id="connection-status-marker" style="display:none"></div>
		</td>
	</tr>

<?php
foreach ($protocols as $pid => $protocol) {
		$vals = (array) $task['connection'][$pid];


		/*
         * Все настройки протокола
         */
        foreach ($protocol['config'] as $config_code => $config_data) {

        	$id = $pid . '_' . $config_code;

            if ($ID > 0) {
	            $value = $vals[$config_code];
            } elseif(isset($_POST[$id])) {
            	$value = htmlspecialchars(strval($_POST[$id]));
            } else {
            	$value = COption::GetOptionString('linemedia.autodownloader', $id, $config_data['default']);
			}


			$onchange = ($config_data['onchange']) ? ' onkeyup="lmProtoFieldOnchange(\''.$pid.'\',\''.$config_data['onchange'].'\', this)"' : '';

			?>
			<tr class="<?= $config_data['required'] ? 'adm-detail-required-field ' : '' ?>protocol protocol-<?= htmlspecialchars($pid) ?> protocol-<?= htmlspecialchars($pid) ?>-<?=strtolower($config_code)?>" style="display:none">
			    <td width="40%" valign="top">
			        <label for="<?= $id ?>"><?= $config_data['title'] ?>:</label>
			    </td>
			    <td width="60%" valign="top">
			        <?
			        $placeholder = ($config_data['placeholder'] != '') ? ' placeholder="'.$config_data['placeholder'].'"' : '';
			        $size = ($config_data['size'] > 0) ? ' size="'.$config_data['size'].'"' : '';
			        switch ($config_data['type']) {
                        case 'file':
                            echo '<input type="file" name="' . $id . '" id="' . $id .' />';
                            CAdminFileDialog::ShowScript(array(
                                "event" => "BtnClick",
                                "arResultDest" => array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => $id),
                                "arPath" => array("SITE" => SITE_ID, "PATH" => $protocol['uopload']),
                                "select" => 'F',// F - file only, D - folder only
                                "operation" => 'O',// O - open, S - save
                                "showUploadTab" => true,
                                "showAddToMenuTab" => false,
                                "fileFilter" => 'csv',
                                "allowAllFiles" => true,
                                "SaveConfig" => true,
                            ));
                            break;
			            case 'password':
			                echo '<input  type="password" name="' . $id . '" id="' . $id . '" value="' . $value . '" '.$placeholder.$size.'/>';
                            break;
			            case 'checkbox':
			                echo '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="true" ' . ($value ? 'checked' : '') . ' />';
                            break;
			            case 'string':
			            default:
			                echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" '.$placeholder.$size.$onchange.'/>';
			        }
			        ?>
			        <? if ($config_data['description'] != '') { ?>
                        <span><?= $config_data['description'] ?></span>
			        <? } ?>
			    </td>
			</tr>
			<?
        }
}
?>

<? $tabControl->BeginNextTab() ?>


<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_UNZIP") ?>:</td>
	<td width="60%" valign="top">
		<input <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> type="text" name="zip_content_filename" value="<?= $task['conversion']['zip_content_filename'] ?>" placeholder="" size="15">
		<span><?= GetMessage("LM_AUTO_UNZIP_DESCR") ?></span>
	</td>
</tr>

<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SOURCE_TYPE") ?>:</td>
	<td width="60%" valign="top">
		<select name="source_type" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> >
			<option value=""<?= ($task['conversion']['type'] == '') ? ' selected' : '' ?>>
                <?= GetMessage("LM_AUTO_SOURCE_TYPE_AUTODETECT")?>
            </option>
			<option value="csv"<?= ($task['conversion']['type'] == 'csv') ? ' selected' : '' ?>>CSV</option>
			<? if (LinemediaAutoTasker::isConversionSupported()) { ?>}
    			<option value="xls"<?= ($task['conversion']['type'] == 'xls') ? ' selected' : '' ?>>XLS</option>
    			<option value="xlsx"<?= ($task['conversion']['type'] == 'xlsx') ? ' selected' : '' ?>>XLSX</option>
			<? } else { ?>
    			<option value="xls" disabled="disabled">XLS</option>
    			<option value="xlsx" disabled="disabled">XLSX</option>
			<? } ?>
		</select>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SOURCE_ENCODING") ?>:</td>
	<td width="60%" valign="top">
		<select name="source_encoding" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> >
			<option value=""<?= ($task['conversion']['encoding']=='')?' selected' : '' ?>><?= GetMessage("LM_AUTO_SOURCE_ENCODING_AUTODETECT") ?></option>
			<option value="Windows-1251"<?= ($task['conversion']['encoding']=='Windows-1251') ? ' selected' : '' ?>>Windows-1251</option>
			<option value="utf-8"<?= ($task['conversion']['encoding']=='utf-8')?' selected' : '' ?>>utf-8</option>
		</select>
	</td>
</tr>


<?	// xls resave supported
	$xls_resave_supported = LinemediaAutoModule::isXLSResaveSupported();
?>
<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_DOWNLOADER_SOURCE_RESAVE") ?>:</td>
	<td width="60%" valign="top">
		<input type="checkbox" name="conversion[source_resave]" <?=$task['conversion']['source_resave'] == true ? 'checked':''?> <?=$xls_resave_supported ? '':'disabled'?>/>
	</td>
</tr>


<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SOURCE_SKIP_LINES") ?>:</td>
	<td width="60%" valign="top">
		<input type="text" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> name="source_skip_lines" value="<?= $task['conversion']['skip_lines'] ?>" placeholder="0" size="5" />
	</td>
</tr>

<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SOURCE_SEPARATOR") ?>:</td>
	<td width="60%" valign="top">
		<input type="text" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> name="source_separator" size="2" value="<?= ($task['conversion']['separator']) ? $task['conversion']['separator'] : ';' ?>" placeholder=";">
	</td>
</tr>

<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SOURCE_COLUMNS") ?>:</td>
	<td width="60%" valign="top">
		<? foreach ($columns as $i => $column) { ?>
            <? $val = (isset($task['conversion']['columns'])) ? ($task['conversion']['columns'][$column]) : ($i + 1) ?>
			<input type="text" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> id="col_<?= $column ?>" name="conversion[columns][<?=$column?>]" value="<?= $val ?>" size="5" />
			<label for="col_<?= $column ?>"><?= $column_titles[$i] ?></label>
			<br/>
		<? } ?>

		<?=BeginNote()?>
		<?= GetMessage("LM_AUTO_SOURCE_IGNORE_EMTY_COLS_XLS") ?>
		<?=EndNote()?>
	</td>
</tr>


<tr class="heading" id="tr_IBLOCK_ELEMENT_PROP_VALUE">
    <td colspan="2"><?= GetMessage("LM_AUTO_REPLACEMENTS") ?></td>
</tr>


<?/*******************************************************************************************************/?>

<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_SOURCE_COLUMNS_REPLACE")?>:</td>
	<td width="60%" valign="top">
		<div class="conversion-replacement">
			<select name="conversion[column_replacements][column][]" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> >
    			<? foreach ($columns as $i => $column) { ?>
    				<option value="<?= $column ?>"><?= $column_titles[$i] ?></option>
    			<? } ?>
			</select>
			<input type="text" name="conversion[column_replacements][what][]" value="" placeholder="<?= GetMessage('REPLACE_WHAT') ?>" size="15" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> />
			<input type="text" name="conversion[column_replacements][with][]" value="" placeholder="<?= GetMessage('REPLACE_WITH') ?>" size="15" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> />
			<input type="button" value="-" class="conversion-replacement-del" <?= strcmp($userPermissions, 'U') == 0 ? 'disabled' :'' ?> />
		</div>

		<? foreach ($task['conversion']['column_replacements'] as $column => $resplacements) { ?>
			<? foreach ($resplacements as $what => $with) { ?>
				<div class="conversion-replacement">
					<select name="conversion[column_replacements][column][]" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> >
					<? foreach ($columns as $i => $r_column) { ?>
						<option value="<?= $r_column ?>"<?= $column == $r_column ? ' selected' : '' ?>>
                            <?= $column_titles[$i] ?>
                        </option>
					<? } ?>
					</select>
					<input type="text" name="conversion[column_replacements][what][]" value="<?= safe_htmlspecialchars($what) ?>" placeholder="<?= GetMessage('REPLACE_WHAT') ?>" size="15" />
					<input type="text" name="conversion[column_replacements][with][]" value="<?= safe_htmlspecialchars($with) ?>" placeholder="<?= GetMessage('REPLACE_WITH') ?>" size="15" />
					<input type="button" value="-" class="conversion-replacement-del" />
				</div>
			<?}?>
		<? } ?>
		<input type="button" value="+" id="conversion-replacement-add" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> />
	</td>
</tr>


<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_SOURCE_COLUMNS_REPLACE_ALL") ?>:</td>
	<td width="60%" valign="top">
		<? foreach ($columns as $i => $column) { ?>
			<? $val = isset($task['conversion']['column_replacements_all'][$column]) ? $task['conversion']['column_replacements_all'][$column] : '' ?>
			<input size="12" type="text" id="col_<?= $column ?>" name="conversion[column_replacements_all][<?= $column ?>]" value="<?=$val?>" size="5" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?>  />
			<label for="col_<?= $column ?>"><?= $column_titles[$i] ?></label>
			<br/>
		<? } ?>
	</td>
</tr>


<? if($has_shedule_tab) { ?>

<? $tabControl->BeginNextTab(); ?>

<tr>
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_INTERVAL") ?>:</td>
	<td width="60%" valign="top">
		<select name="interval" id="interval" <?= in_array($userPermissions, $readAccess) ? 'disabled' :'' ?> >
			<option value=""<?= ($task['shedule']['interval'] <= 0)?' selected':''?>><?=GetMessage("LM_AUTO_INTERVAL_NOT_SELECTED")?></option>
			<option value="3600"<?= ($task['shedule']['interval'] == 3600)?' selected':''?>><?=GetMessage("LM_AUTO_INTERVAL_HORLY")?></option>
			<option value="86400"<?= ($task['shedule']['interval'] == 86400)?' selected':''?>><?=GetMessage("LM_AUTO_INTERVAL_DAILY")?></option>
			<option value="2592000"<?= ($task['shedule']['interval'] == 2592000)?' selected':''?>><?=GetMessage("LM_AUTO_INTERVAL_MONTHLY")?></option>
		</select>
	</td>
</tr>

<tr style="display:none" class="interval interval-86400">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_INTERVAL_TIME") ?>:</td>
	<td width="60%" valign="top">
		<select name="interval_daily_start_hour">
			<? for ($i = 0; $i < 24; $i++) { ?>
				<option value="<?= sprintf('%02d', $i) ?>"<?= ($task['shedule']['start_hour'] == $i) ? ' selected' : '' ?>><?= sprintf('%02d', $i) ?></option>
			<? } ?>
		</select>
		<select name="interval_daily_start_minute">
			<? for ($i = 0; $i < 60; $i += 5) { ?>
				<option value="<?= sprintf('%02d', $i) ?>"<?= ($task['shedule']['start_minute'] == $i) ? ' selected' : '' ?>><?= sprintf('%02d', $i) ?></option>
			<? } ?>
		</select>
	</td>
</tr>
<tr style="display:none" class="interval interval-86400">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_INTERVAL_DAY") ?>:</td>
	<td width="60%" valign="top">
		<? for ($i = 1; $i < 8; $i++) { ?>
			<input type="checkbox" name="interval_daily_days[]" value="<?= $i ?>" id="interval_daily_day<?= $i ?>" value="<?=$i?>" <?=in_array($i, $task['shedule']['days'])?' checked':''?> />
			<label for="interval_daily_day<?= $i ?>"><?= GetMessage("DAY_" . $i) ?></label>
			<br>
		<? } ?>
	</td>
</tr>
<tr style="display:none" class="interval interval-2592000">
	<td width="40%" valign="top"><?=GetMessage("LM_AUTO_INTERVAL_DAY")?>:</td>
	<td width="60%" valign="top">
		<select name="interval_monthly_start_day">
			<? for ($i = 1; $i < 28; $i++) { ?>
				<option value="<?= $i ?>"<?= ($task['shedule']['start_day'] == $i) ? ' selected' : '' ?>>
				    <?= $i ?>
			    </option>
			<? } ?>
			<option value="last"><?= GetMessage("LM_AUTO_LAST_DAY") ?></option>
		</select>
	</td>
</tr>
<tr style="display:none" class="interval interval-2592000">
	<td width="40%" valign="top"><?= GetMessage("LM_AUTO_INTERVAL_TIME") ?>:</td>
	<td width="60%" valign="top">
		<select name="interval_monthly_start_hour">
			<? for ($i = 0; $i < 24; $i++) { ?>
				<option value="<?= sprintf('%02d', $i) ?>"<?= ($task['shedule']['start_hour'] == $i) ? ' selected' : ''?>>
				    <?= sprintf('%02d', $i) ?>
			    </option>
			<? } ?>
		</select>
		<select name="interval_monthly_start_minute">
			<? for ($i = 0; $i < 60; $i += 5) { ?>
				<option value="<?= sprintf('%02d', $i) ?>"<?= ($task['shedule']['start_minute'] == $i) ? ' selected' : ''?>>
				    <?= sprintf('%02d', $i) ?>
			    </option>
			<? } ?>
		</select>
	</td>
</tr>

<? } // if($has_shedule_tab) ?>
<?
$showAccessTab = true;
$events = GetModuleEvents("linemedia.auto", "BeforeTasksAddAccessTabShow");
while ($arEvent = $events->Fetch()) {
    if(!ExecuteModuleEventEx($arEvent, array())) {
        $showAccessTab = false;
    }
}

if($showAccessTab && $userPermissions == LM_AUTO_MAIN_ACCESS_FULL) {
    $tabControl->BeginNextTab();
    $lm_rights->showRightsTab($task['id']);
}
?>
<? $tabControl->EndTab(); ?>

<?


if(!in_array($userPermissions, $readAccess)) {

   $tabControl->Buttons(array(
       "disabled" => ($linemedia_ModulePermissions < "W"),
       "back_url" => "/bitrix/admin/" . $arPageSettings['LIST_PAGE'] . "?lang=".LANG.GetFilterParams("filter_")
   ));
}

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
    		  url: "/bitrix/admin/<?=$arPageSettings['ADD_PAGE']?>?lang=<?= LANG ?>&ajax=checkConnection",
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

    $('#lm-auto-test-mode-id').live('click', function() {
        if ($(this).is(':checked')) {
            $('#lm-auto-email-id').removeAttr('disabled');
        } else {
            $('#lm-auto-email-id').attr('disabled', 'disabled');
        }
    });

    $('#conversion-replacement-add').click(function() {
    	var $div = $('.conversion-replacement:last');
    	var html = '<div class="conversion-replacement">' + $div.html() + '</div>';
    	$div.after(html);
    });

    $('.conversion-replacement-del').live('click', function() {
        if ($('.conversion-replacement').length > 1) {
            $(this).closest('.conversion-replacement').remove();
        }
    });



    function lmProtoFieldOnchange(protocol, funcname, _this)
    {
    	$.ajax({
		  url: "/bitrix/admin/<?=$arPageSettings['ADD_PAGE']?>?lang=<?= LANG ?>&ajax=formUpdated&protocol=" + protocol + "&func=" + funcname,
		  type : 'POST',
		  data: $('#lm-auto-down-add-task-frm').serialize()
		}).done(function(data) {
			eval( data );
		});
    }

</script>

<?

require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

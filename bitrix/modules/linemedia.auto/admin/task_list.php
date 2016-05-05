<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


$POST_RIGHT = 'W';


if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

global $USER, $APPLICATION;
// объект контроля доступа
$lm_rights = new LinemediaAutoRightsEntity(LinemediaAutoRightsEntity::$ENTITY_TYPE_PRICE);

$userPermission = $lm_rights->getDefaultRights();

//$userPermission = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT));

if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


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
$events = GetModuleEvents('linemedia.auto', 'OnBeforeTaskListPageBuild');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arPageSettings));
}

/*
 * Запуск задачи
 */
if ($_REQUEST['ajax'] == 'runTask') {
    $id = (int) $_GET['id'];
    $shedule = LinemediaAutoTaskShedule::GetByTaskId($id);
    $shedule = $shedule->Fetch();
    $shedule_obj = new LinemediaAutoTaskShedule();
    $shedule_obj->Update($shedule['id'], array('force_run_now' => 1));
    die('OK');
}


/*
 * Загрузка файла задачи.
 */
if ($_REQUEST['ajax'] == 'uploadTask') {

    if (!empty($_FILES) && !empty($_FILES['qqfile']) || !empty($_GET['qqfile'])) {

        $id = (int) $_REQUEST['id'];

        $task = LinemediaAutoTask::GetById($id)->Fetch();

        $protocol = LinemediaAutoTasker::getProtocolInstance($task['protocol']);

        $ext = strtolower(end(explode('.', $_FILES['qqfile']['name'])));

	    if(!$ext) {
		    $ext = strtolower(end(explode('.', $_GET['qqfile'])));
	    }

        // Загрузка файла.
        $filename = $protocol->upload();

        $new_folder   = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/pending/';
        $new_filename = $task['id'].'_'.$task['supplier_id'].'_'.md5($filename).'.'.$ext;

        rename($filename, $new_folder.$new_filename);

        $shedule = new LinemediaAutoTaskShedule();
        $shedule->Update($task['shedule_ids'], array('last_exec' => date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL')))));
    }
    die('OK');
}





/***********************************************************/
$sTableID = "b_lm_tasks"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "title", "asc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка
$lAdmin->bMultipart = true; // для загрузки файлов

// Проверку значений фильтра для удобства вынесем в отдельную функцию.
function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) {
        global $$f;
    }
    return (count($lAdmin->arFilterErrors) == 0); // если ошибки есть, вернем false;
}

/*
 * Поставщики
 */
$suppliers = LinemediaAutoSupplier::GetList(array(), array(), false, false, array(), 'supplier_id');
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

// Опишем элементы фильтра.
$FilterArr = Array(
    "find_protocol",
    "find_active",
    "find_id",
    "find_supplier_id",
    "find_title",
);

// Инициализируем фильтр.
$lAdmin->InitFilter($FilterArr);

// Если все значения фильтра корректны, обработаем его.
if (CheckFilter()) {
    // Создадим массив фильтрации для выборки LinemediaAutoTask::GetList() на основе значений фильтра.
    if(!$find_supplier_id) $find_supplier_id = array_keys($suppliers);


    $arFilter = array(
        "protocol"      => $find_protocol,
        "active"        => $find_active,
        "id"            => $find_id,
        "supplier_id"   => $find_supplier_id,
        "title"         => $find_title,
    );
}

// доступы у поставщиков проверяются внутри класса!!! никаких отдельных фильтров не нужно !!!
//
//$suppliers = array();
//$suppliers_res = LinemediaAutoSupplier::GetList();
//foreach ($suppliers_res as $supplier) {
//    $suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
//}



// Обработка одиночных и групповых действий.
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == 'W') {
    // Если выбрано "Для всех элементов".
    if ($_REQUEST['action_target'] == 'selected') {
        $cData = new LinemediaAutoTask();
        $rsData = $cData->GetList(array($by => $order), $arFilter);
        while ($arRes = $rsData->Fetch()) {
            $arID []= $arRes['id'];
        }
    }

    // Пройдем по списку элементов.
    foreach ($arID as $ID) {
        if (strlen($ID) <= 0) {
            continue;
        }
        $ID = intval($ID);

        // Для каждого элемента совершим требуемое действие.
        switch ($_REQUEST['action']) {
            // Удаление.
            case "delete":
                LinemediaAutoTask::Delete($ID);
                break;

            // Запуск.
            case "run":
                @set_time_limit(0);
                LinemediaAutoTasker::run($ID);
                break;

            // Загрузка.
            case "upload":
                ini_set('upload_max_filesize', '2G');
                LinemediaAutoTasker::download($ID);
                break;

            // Активация / деактивация.
            case "activate":
            case "deactivate":
                $cData = new LinemediaAutoTask();
                if (($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch())) {
                    $arFields["active"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
                    if (!$cData->Update($ID, $arFields)) {
                        $lAdmin->AddGroupError(GetMessage("LM_AUTO_SAVE_ERR").$cData->LAST_ERROR, $ID);
                    }
                } else {
                    $lAdmin->AddGroupError(GetMessage("LM_AUTO_SAVE_ERR")." ".GetMessage("LM_AUTO_NO_MODEL"), $ID);
                }
                break;
        }
    }
}

// Выберем список.
$cData = new LinemediaAutoTask();
$rsData = $cData->GetList(array($by => $order), $arFilter);

// Преобразуем список в экземпляр класса CAdminResult.
$rsData = new CAdminResult($rsData, $sTableID);

// Аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// Отправим вывод переключателя страниц в основной объект $lAdmin.
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_MODELS_NAV")));


$lAdmin->AddHeaders(array(
    array(
        "id"       => "id",
        "content"  => GetMessage("LM_AUTO_ID"),
        "sort"     => "id",
        "default"  => true,
    ),
    array(
        "id"       => "active",
        "content"  => GetMessage("LM_AUTO_ACTIVE"),
        "sort"     => "active",
        "default"  => true,
    ),
    array(
        "id"       => "supplier_id",
        "content"  => GetMessage("LM_AUTO_SUPPLIER"),
        "sort"     => "supplier_id",
        "default"  => true,
    ),
    array(
        "id"       => "title",
        "content"  => GetMessage("LM_AUTO_TASK_TITLE"),
        "sort"     => "title",
        "default"  => true,
    ),
    array(
        "id"       => "protocol",
        "content"  => GetMessage("LM_AUTO_PROTOCOL"),
        "sort"     => "protocol",
        "default"  => true,
    ),
    array(
        "id"       => "interval",
        "content"  => GetMessage("LM_AUTO_INTERVAL"),
        "sort"     => "type",
        "default"  => true,
    ),
    array(
        "id"       => "last_exec",
        "content"  => GetMessage("LM_AUTO_LAST_EXEC"),
        "sort"     => "last_exec",
        "default"  => true,
    ),
    array(
        "id"       => "test",
        "content"  => GetMessage("LM_AUTO_TEST_MODE"),
        "sort"     => "test",
        "default"  => true,
    ),
));



/*
 * Доступные протоколы.
 */
$protocols = LinemediaAutoTasker::getProtocols();


$times_lang = array(
    0 => GetMessage('LM_AUTO_INTERVAL_MANUALLY'),
    3600 => GetMessage('LM_AUTO_INTERVAL_HORLY'),
    86400 => GetMessage('LM_AUTO_INTERVAL_DAILY'),
    604800 => GetMessage('LM_AUTO_INTERVAL_WEEKLY'),
    2592000 => GetMessage('LM_AUTO_INTERVAL_MONTHLY'),
);

// max upload limit
ini_set('upload_max_filesize', '2G');
$max_upload = (int)(ini_get('upload_max_filesize'));
if(strpos(ini_get('upload_max_filesize'), 'G') !== false) $max_upload *= 1024;
$max_post = (int)(ini_get('post_max_size'));
if(strpos(ini_get('post_max_size'), 'G') !== false) $max_post *= 1024;
$memory_limit = (int)(ini_get('memory_limit'));
if(strpos(ini_get('memory_limit'), 'G') !== false) $memory_limit *= 1024;
$upload_mb = min($max_upload, $max_post, $memory_limit);

$uploadIDs = array();

while ($arRes = $rsData->NavNext(true, "f_")) {
    /*
     * Рлдучим расписания задачи, чтобы вывод был более информативным.
     */
    $shedules = array();
    $shedule_obj = LinemediaAutoTaskShedule::GetByTaskId($arRes['id']);
    while ($shedule = $shedule_obj->Fetch()) {
        $shedules []= $shedule;
    }

    /*
     * Пока у нас только одно расписание на задачу - упростим код.
     */
    $arRes['interval'] = $f_interval = $shedules[0]['interval'];
    // берется из запроса
    //$arRes['last_exec'] = $last_exec = $shedules[0]['last_exec'];

    // Проверим доступ
    $row_right = $lm_rights->getRight($arRes['id']);
    if($row_right < LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS) {
        continue;
    }
    // нарушает постраничную навигацию. поставщики добавлены в фильтр
    if(!array_key_exists($f_supplier_id, $suppliers)) {
        //continue;
    }

    // Создаем строку. результат - экземпляр класса CAdminListRow.
    $row =& $lAdmin->AddRow($f_id, $arRes);

    // Далее настроим отображение значений при просмотре и редаткировании списка.
    $row->AddCheckField("active");

    // Протокол.
    $row->AddField("protocol", GetMessage('PROTOCOL_'.strtoupper($f_protocol)));

    // Путь к файлу.
    //$connection = unserialize($arRes['connection']);
    //$file_path = '<b>'.$connection['protocol'].'</b>: '.$connection[$connection['protocol']]['FILENAME'];
    //$row->AddViewField("file_path", $file_path);

    // Тестовый режим.
    $row->AddViewField("test", ($f_mode == LinemediaAutoTask::MODE_TEST) ? (GetMessage('LM_AUTO_YES')) : (GetMessage('LM_AUTO_NO')));

    if (isset($times_lang[$f_interval])) {
        $row->AddViewField("interval", $times_lang[$f_interval]);
    } else {
        $hours = floor($f_interval / 3600);
        $mins = floor(($f_interval - ($hours * 3600)) / 60);
        $row->AddViewField("interval", $hours . 'h - ' . $mins . 'm');
    }

    $supplier = $suppliers[$f_supplier_id];
    $row->AddViewField("supplier_id", "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>$f_supplier_id</a>] " . $supplier['NAME']);


    // Сформируем контекстное меню.
    $arActions = array();

    if (strtoupper($arRes['protocol']) == LinemediaAutoFileProtocol::$title) {
        $uploadIDs  []= $f_id;

        // Загрузка.
        $arActions[] = array(
            "ID"      => "upload",
            "ICON"    => "upload-pc",
            "TEXT"    => '<span data-supplier="'.htmlspecialchars($supplier['NAME']).'" class="upload-id" id="upload-file-'.$f_id.'">'.str_replace('#UPLOAD_LIMIT#', $upload_mb, GetMessage("LM_AUTO_UPLOAD")).'</span>',
            "ACTION"  => "javascript: uploadTask(".$f_id.")"
        );
    } else {
        // Запуск.
        $arActions[] = array(
            "ICON"    => "run",
            "TEXT"    => GetMessage("LM_AUTO_RUN"),
            "ACTION"  => "javascript: runTask(".$f_id.")"
        );
    }


    // Очередь файлов на конвертацию прайслистов.
    $fine_name = $f_id.'_'.$f_supplier_id.'_';
    $arActions[] = array(
        "ICON"    => "convert-queue",
        "TEXT"    => GetMessage("LM_AUTO_CONVERT_QUEUE"),
        "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/fileman_admin.php?path=/upload/linemedia.auto/pricelists/pending/&find_type=F&find_name=".$fine_name."&set_filter=Y&lang=".LANGUAGE_ID),
        "DEFAULT" => true
    );


    // Очередь файлов на импорт прайслистов.
    // $fine_name = $f_supplier_id.'_'.$f_id.'_'.$f_supplier_id.'_';
    $fine_name = $f_supplier_id.'_';
    $arActions[] = array(
        "ICON"    => "convert-queue",
        "TEXT"    => GetMessage("LM_AUTO_IMPORT_QUEUE"),
        "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/fileman_admin.php?path=/upload/linemedia.auto/pricelists/new/&find_type=F&find_name=".$fine_name."&set_filter=Y&lang=".LANGUAGE_ID),
        "DEFAULT" => true
    );

    if($row_right > LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS) {
        // Редактирование элемента.
        $arActions[] = array(
            "ICON"    => "edit",
            "TEXT"    => GetMessage("LM_AUTO_EDIT"),
            "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/" . $arPageSettings['ADD_PAGE'] . "?ID=$f_id&lang=".LANGUAGE_ID),
            "DEFAULT" => true
        );

        // Удаление элемента.
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => GetMessage("LM_AUTO_DELETE"),
            "ACTION" => "if(confirm('".GetMessage('LM_AUTO_CONFIRM_DELETE')."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
        );
    } else {
       $row->bReadOnly = true;
   }

    // Применим контекстное меню к строке.
    $row->AddActions($arActions);
}


// Резюме таблицы.
$lAdmin->AddFooter(
    array(
        array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()), // кол-во элементов
        array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"), // счетчик выбранных элементов
    )
);

// Групповые действия.
$lAdmin->AddGroupActionTable(array(
    "delete"        => GetMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
    "activate"      => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"), // активировать выбранные элементы
    "deactivate"    => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // деактивировать выбранные элементы
));


if($userPermission > LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS) {
    // Сформируем меню из одного пункта - добавление рассылки.
    $aContext = array(
        array(
            "TEXT" => GetMessage("LM_AUTO_ADD_TASK"),
            "LINK" => "/bitrix/admin/" . $arPageSettings['ADD_PAGE'] . "?lang=" . LANG,
            "TITLE" => GetMessage("LM_AUTO_ADD_TASK"),
            "ICON" => "btn_new",
        ),
    );
}


// И прикрепим его к списку.
$lAdmin->AddAdminContextMenu($aContext, false, true);


//rendering list comprised in admin sheet depending on users privileges
/*
$events = GetModuleEvents('linemedia.auto', 'OnBeforeProductsPageAdd');
while ($arEvent = $events->Fetch()) {

    if (ExecuteModuleEventEx($arEvent, array(&$lAdmin, \Linemedia\Auto\Privilege\PageID::PRICE)) == false) {

        if($ex = $APPLICATION->GetException()) {

            $strError = $ex->GetString();
            ShowError($strError);
            return;
        }

    }
}
*/


//read available suppliers only
/*if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS) == 0) {
    
    $actions = array_slice(current($lAdmin->aRows)->aActions, 0, 3);
    
    $lAdmin->AddGroupActionTable();
    $lAdmin->AddAdminContextMenu();
    $lAdmin->bCanBeEdited = FALSE;
//
//    $accessibleSuppliers = array();
//
//    \CModule::IncludeModule('iblock');
//
//    foreach (\LinemediaAutoSupplier::getAllowedSuppliers() as $supplier) {
//
//        $dbRes = \CIBlockElement::GetProperty(\COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), $supplier, array(), array('CODE' => 'supplier_id'))->Fetch();
//
//        if ($dbRes != NULL) {
//            $accessibleSuppliers[] = $dbRes['VALUE'];
//        }
//    }
//
//
//    foreach ($lAdmin->aRows as $key => $item) {
//        if (!in_array($item->arRes['supplier_id'], $accessibleSuppliers)) {
//            unset($lAdmin->aRows[$key]);
//        }
//    }
    
    foreach ($lAdmin->aRows as $row) {
        $row->AddActions($actions);
    }
    
}*/



CUtil::InitJSCore(array('window', 'jquery'));


// Альтернативный вывод.
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage('LM_AUTO_PAGE_TITLE'));

$APPLICATION->SetAdditionalCSS('/bitrix/js/linemedia.auto/uploader/style.css');
$APPLICATION->AddHeadScript('/bitrix/js/linemedia.auto/uploader/style.js');

//$APPLICATION->AddHeadScript('http://ajax.googleapis.com/ajax/libs/mootools/1.2.2/mootools.js');

// Не забудем разделить подготовку данных и вывод.
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');


// Создадим объект фильтра.
$oFilter = new CAdminFilter(
    $sTableID.'_filter',
    array(
        GetMessage('LM_AUTO_ID'),
        GetMessage('LM_AUTO_TASK_TITLE'),
        GetMessage('LM_AUTO_ACTIVE'),
        GetMessage('LM_AUTO_SUPPLIER'),
        GetMessage('LM_AUTO_PROTOCOL'),
    )
);


/*
 * Эмуляция IE (обработка ошибки INVALID_CHARACTER_ERR (5) в Internet Explorer 9)
 * http://iamsky.ru/?p=131
 */
header('X-UA-Compatible: IE=EmulateIE7');

?>

<form id="find_form" name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" id="file-task-id" value="" />

    <? $oFilter->Begin() ?>
    <tr>
        <td><?= GetMessage("LM_AUTO_ID") ?>:</td>
        <td>
            <input type="text" name="find_id" size="4" value="<?= htmlspecialchars($find_id) ?>" />
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("LM_AUTO_TASK_TITLE").":" ?></td>
        <td><input type="text" name="find_title" size="25" value="<?= htmlspecialchars($find_title) ?>" /></td>
    </tr>
    <tr>
        <td><?= GetMessage("LM_AUTO_ACTIVE") ?>:</td>
        <td>
            <select name="find_active">
                <option value=""><?= GetMessage('LM_AUTO_NOT_SELECTED') ?></option>
                <option<?= $find_interval == 'Y' ? ' selected' : '' ?> value="Y"><?= GetMessage('LM_AUTO_ACTIVE_Y') ?></option>
                <option<?= $find_interval == 'N' ? ' selected' : '' ?> value="N"><?= GetMessage('LM_AUTO_ACTIVE_N') ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("LM_AUTO_SUPPLIER") ?>:</td>
        <td>
            <select name="find_supplier_id">
                <option value=""><?= GetMessage('LM_AUTO_NOT_SELECTED') ?></option>
                <? foreach ($suppliers as $code => $supplier) { ?>
                    <option value="<?= htmlspecialchars($code) ?>"<?= (($code == $find_supplier_id) ? " selected" : "") ?>>
                       <?= htmlspecialchars($supplier['NAME']) ?>
                    </option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("LM_AUTO_PROTOCOL") ?>:</td>
        <td>
            <select name="find_protocol">
                <option value=""><?= GetMessage('LM_AUTO_NOT_SELECTED') ?></option>
                <? foreach ($protocols as $pid => $protocol) { ?>
                    <option value="<?= htmlspecialchars($pid) ?>" <?= ($find_protocol == $pid) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($protocol['title']) ?>
                    </option>
                <? } ?>
            </select>
        </td>
    </tr>

    <? $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form")) ?>
    <? $oFilter->End() ?>
</form>

<?/* foreach ($uploadIDs as $id) { ?>
    <div class="uploader">
        <div class="file-attach-list" id="file-attach-list-<?= $id ?>"></div>
    </div>
<? }*/ ?>

<? if (!LinemediaAutoTasker::isConversionSupported()) { ?>
    <?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('LM_AUTO_ERROR_CONVERTING'), 'TYPE' => 'ERROR', 'HTML' => true)) ?>
<? } ?>


<? $lAdmin->DisplayList() ?>

<script type="text/javascript">
    var uploaders = {};

    jQuery.noConflict();

    jQuery(document).ready(function() {

		lang_download = '<?=str_replace('#UPLOAD_LIMIT#', $upload_mb, GetMessage("LM_AUTO_UPLOAD"))?>';
        /*
         * Привяжем создание объекта загрузчика к контекстному меню.
         */
        jQuery('.adm-list-table-popup').live('mouseup', function() {
            bindUploader();
        });

        /*
         * Удалим контекстное меню для строк таблицы.
         * Оно вызывает многократное копирование контекстного меню,
         * из-за чего к нему нельзя привязать загрузчик.
         */
        jQuery('.adm-list-table-popup-block').each(function() {
            var tr = jQuery(this).parent();
            if (tr.attr('oncontextmenu') != undefined) {
                jQuery(this).attr('onclick', 'BX.adminList.ShowMenu(this.firstChild, '+ tr.attr('oncontextmenu').replace('return', '').slice(0, -1) + ', this.parentNode);');
                tr.attr('oncontextmenu', null);
            }
        });

        jQuery('.adm-list-table-row').live('mouseenter', function() {
            var context = jQuery(this).attr('oncontextmenu');

            if (context != undefined) {
                var td = jQuery(this).children('td.adm-list-table-popup-block');

                td.attr('onclick', 'BX.adminList.ShowMenu(this.firstChild, '+ context.replace('return', '').slice(0, -1) + ', this.parentNode);');
                jQuery(this).attr('oncontextmenu', null);
            }
        });


        /*
         * Привязка загрузчика файлов к контекстному меню.
         */
        function bindUploader()
        {
            setTimeout(
                function() {
	                var obj = jQuery('div.bx-core-popup-menu:visible span.upload-id').attr('id');

	                if(typeof(obj) != 'undefined') {
		                var id = parseInt(obj.split('-').pop());
		                var supplier = jQuery('div.bx-core-popup-menu:visible span.upload-id').data('supplier');

		                // Удалим копии контекстного меню, содержащие повторяющиеся id.
		                jQuery('.bx-core-popup-menu-level0:hidden').remove();

		                setUploader(id, supplier);
	                }

                }, 100
            );
        }
    });


    function setUploader(id, supplier)
    {
	    /**
	     * Uploader instance.
	     *
	     * 
	     */
	    uploaders[id] = new qq.FileUploader({
		    // pass the dom node (ex. $(selector)[0] for jQuery users)
		    element: document.getElementById('upload-file-' + id),
		    // path to server-side upload script
		    action: '<?= $APPLICATION->GetCurPage() ?>',
		    params: {"id":id, 'ajax':'uploadTask'},
		    debug: false,
		    onProgress: function(id, fileName, loaded, total){
			    jQuery('.bar-'+hashCode(fileName)).width(Math.ceil(loaded*100/total)+'%');
			    jQuery('.percent-'+hashCode(fileName)).html(Math.round(loaded*100/total * Math.pow(10, 2))+'%');

				jQuery('.filling-line-'+hashCode(fileName)).width(Math.ceil(loaded/total * Math.pow(10, 2))+'%');
				jQuery('.percentage-'+hashCode(fileName)).html(Math.round(loaded/total * Math.pow(10, 2)));

			},
		    onSubmit: function(id, fileName){
			    var results;

				results = '<div class="percentage-filling-content-block percentage-filling-content-block-'+hashCode(fileName)+' offset-b-20">';
				results += '<div class="filling-line filling-line-'+hashCode(fileName)+'" style="width:0%"></div>';
				results += '<div class="text"><span class="percentage percentage-'+hashCode(fileName)+'">0</span>% <?= GetMessage('LM_AUTO_PROGRESS_UPLOAD') ?></div>';
				results += '</div>';

			    jQuery('#b_lm_tasks_result_div').before(results);
		    },
		    onComplete: function(id, fileName, responseJSON){

				var message;
				if (typeof(responseJSON.error) == 'undefined') {
					message  = '<div class="adm-info-message-wrap adm-info-message-green">';
					message += '<div class="adm-info-message">';
					message += '<div class="adm-info-message-title"><?= GetMessage('LM_AUTO_FILE') ?> &laquo;' + fileName + '&raquo; <?= GetMessage('LM_AUTO_FILE_UPLOAD') ?></div>';
					message += '<div class="adm-info-message-icon"></div>';
					message += '</div></div>';
				} else {
					message  = '<div class="adm-info-message-wrap adm-info-message-red">';
					message += '<div class="adm-info-message">';
					message += '<div class="adm-info-message-title"><?= GetMessage('LM_AUTO_FILE_ERROR') ?> &laquo;' + fileName + '&raquo;. <?= GetMessage('LM_AUTO_ERROR_UPLOAD') ?>: '+responseJSON.error+'</div>';
					message += '<div class="adm-info-message-icon"></div>';
					message += '</div></div>';

					jQuery('.percentage-filling-content-block-'+hashCode(fileName)).hide();
				}

				jQuery('#b_lm_tasks_result_div').before(message);


		    },
		    onCancel: function(id, fileName){
			    var message;
				message  = '<div class="adm-info-message-wrap adm-info-message-red">';
				message += '<div class="adm-info-message">';
				message += '<div class="adm-info-message-title"><?= GetMessage('LM_AUTO_FILE_ERROR') ?> &laquo;' + fileName + '&raquo;. <?= GetMessage('LM_AUTO_ERROR_UPLOAD') ?>: '+responseJSON.error+'</div>';
				message += '<div class="adm-info-message-icon"></div>';
				message += '</div></div>';

				jQuery('.percentage-filling-content-block-'+hashCode(fileName)).hide();

			    jQuery('#b_lm_tasks_result_div').before(message);
		    },
		    onError: function(id, fileName, xhr){
				var message;
				message  = '<div class="adm-info-message-wrap adm-info-message-red">';
				message += '<div class="adm-info-message">';
				message += '<div class="adm-info-message-title"><?= GetMessage('LM_AUTO_FILE_ERROR') ?> &laquo;' + fileName + '&raquo;. <?= GetMessage('LM_AUTO_ERROR_UPLOAD') ?>: '+responseJSON.error+'</div>';
				message += '<div class="adm-info-message-icon"></div>';
				message += '</div></div>';

				jQuery('.percentage-filling-content-block-'+hashCode(fileName)).hide();

				jQuery('#b_lm_tasks_result_div').before(message);
		    }
	    });
    }

    hashCode = function(s){
	    return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);
    }

    function uploadTask(id)
    {

    }


    function runTask(id)
    {
        jQuery.ajax({
            url: "/bitrix/admin/<?=$arPageSettings['LIST_PAGE']?>?lang=<?= LANGUAGE_ID ?>&ajax=runTask&id=" + id
        }).done(function(data) {
            if (data == 'OK') {
                //alert('<?= GetMessage('LM_AUTO_TASK_RUN_OK') ?>');
            } else {
                //alert(data);
            }
        });
    }

</script>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
<?php
/**
 * Административный файл для просмотра списка кроссов
*/

/**
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");


if(!CModule::IncludeModule('linemedia.auto'))
{
    ShowError(GetMessage('LM_AUTO_AS_NO_MAIN_MODULE'));
    return;
}

if(!CModule::IncludeModule('linemedia.autoanalogssimple'))
{
    ShowError(GetMessage('LM_AUTO_AS_NO_MODULE'));
    return;
}



$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.autoanalogssimple");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage('LM_AUTO_AS_LIST_TITLE'));



$sTableID = "tbl_simple_analogs_list";


$oSort = new CAdminSorting($sTableID, 'import_id', 'DESC', 'sOrBy', 'sOrOrder');
$lAdmin = new CAdminList($sTableID, $oSort);

// Группы аналогов.
$arAnalogGroups = LinemediaAutoPart::getAnalogGroups();


$arFilterFields = array(
    "filter_import_id",
    "filter_group",
    "filter_added_from",
    "filter_added_to",
    "filter_article",
    "filter_brand_title",
);


/*
 * Фильтрация.
 */
$analog = new LinemediaAutoAnalogsSimpleAnalog();




/*
 * Групповые операции.
 */
$arID = array();
if ($saleModulePermissions >= 'U') {
    
    // Типы операций.
    switch ($_REQUEST['action_button']) {
        case 'delete':
            if ($_REQUEST['action_target'] == 'selected') {
                $analog->clear(array());
            } else {
                $arID = (array) $_REQUEST['ID'];
                foreach ($arID as $id) {
                    $analog->clear(array('id' => intval($id)));
                }
            }
        break;
    }
}



$lAdmin->InitFilter($arFilterFields);



$conditions = array();

if ($filter_import_id != '') {
    $conditions['import_id'] = (string) $filter_import_id;
}
if ($filter_group != '') {
    $conditions['group'] = (string) $filter_group;
}
if ($filter_article != '') {
    $conditions['article'] = LinemediaAutoPartsHelper::clearArticle((string) $filter_article);
}
if ($filter_added_from != '') {
    $conditions['added']['from'] = $filter_added_from;
}
if ($filter_added_to != '') {
    $conditions['added']['to'] = $filter_added_to;
}
if ($filter_brand_title != '') {
    $conditions['brand_title'] = (string) $filter_brand_title;
}


/*
 * Условия для выборки: выбираем только значения для текущей страницы в пагинаторе
 */
$conditions['limit'] = CAdminResult::GetNavSize($sTableID); //-- убрано, т.к. постраничка отваливается. полмиллиона строк это дело тянет, так что пока хватит.

$page = (int) $_REQUEST['PAGEN_1'] ?: (int) $_REQUEST['PAGEN_2'];

if ($page > 0) {
	$conditions['start'] = ($page - 1) * $conditions['limit'];
}


/*
 * Выводимые поля
 */
$arHeaders = array(
    array('id' => 'import_id',              'content' => GetMessage('LM_AUTO_AS_IMPORT_ID'),        'sort' => 'import_id',       'default' => true),
    array('id' => 'group_original',                  'content' => GetMessage('LM_AUTO_AS_ANALOG_TYPE_ORIG'),     'sort' => 'group_original',       'default' => true),
    array('id' => 'article_original',       'content' => GetMessage('LM_AUTO_AS_ART_ORIG'),         'sort' => 'article_original',       'default' => true),
    array('id' => 'brand_title_original',   'content' => GetMessage('LM_AUTO_AS_BRAND_TITLE_ORIG'), 'sort' => 'brand_title_original',          'default' => true),
    
    array('id' => 'group_analog',          'content' => GetMessage('LM_AUTO_AS_ANALOG_TYPE_ANALOG'), 'sort' => 'group_analog',       'default' => true),
    array('id' => 'article_analog',         'content' => GetMessage('LM_AUTO_AS_ART_ANALOG'),       'sort' => 'article_analog',       'default' => true),
    array('id' => 'brand_title_analog',     'content' => GetMessage('LM_AUTO_AS_BRAND_TITLE_ANALOG'),'sort' => 'brand_title_analog',    'default' => true),
    array('id' => 'added',                  'content' => GetMessage('LM_AUTO_AS_ADDED'),            'sort' => 'added',    'default' => true),
);


/*
 * Создание событий для модуля
 */
$events = GetModuleEvents("linemedia.autoanalogssimple", "OnBeforeAdminShowAnalogsList");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$arHeaders));
    } catch (Exception $e) {
        throw $e;
    }
}


$lAdmin->AddHeaders($arHeaders);



$arGroupByTmp = false;
$arSelectFields = array();



$analogs_list = $analog->find($conditions);

// Инициализация списка - выборка данных.
$analogs_list = new CAdminResult($analogs_list, $sTableID);

$analogs_list->NavStart();
/*
 * Выбираем к-во аналогов, согласно фильтру, потом инифиулизируе пустой массив с таким же
 * в-ком элементов, делаем из него объект бд и используем его для построения строки навигации
 */
$analogs_all = $analog->counts($conditions);
$arrayCountAnalogs= array_fill(0, CAdminResult::GetNavSize($sTableID), '');
$analogs_paginator = new CDBResult;
$analogs_paginator->InitFromArray($arrayCountAnalogs);
$analogs_paginator->NavRecordCount = $analogs_all; // change count
$analogs_paginator = new CLMAdminResult($analogs_paginator, $sTableID);// change class
$analogs_paginator->NavStart();

// Установка строки навигации.

$lAdmin->NavText($analogs_paginator->GetNavPrint(GetMessage('LM_AUTO_AS_ANALOGS_LIST')));

// Добавление контекстного меню.
$lAdmin->AddAdminContextMenu(array());


while ($analog = $analogs_list->NavNext(true, "f_")) {
    
    // Формирование строки для вывода.
    $row =& $lAdmin->AddRow($analog['id'], $analog);
    
    $row->AddViewField("group_original", $arAnalogGroups[$analog['group_original']]);
    $row->AddViewField("group_analog", $arAnalogGroups[$analog['group_analog']]);
    $row->AddViewField("article_original", $analog['article_original'] . ' [' . $analog['brand_title_original'] . ']');
    $row->AddViewField("article_analog", $analog['article_analog'] . ' [' . $analog['brand_title_analog'] . ']');
    
    /*
     * Создание событий для модуля
     */
    $events = GetModuleEvents("linemedia.autoanalogssimple", "OnBeforeAdminShowAnalogRow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$row, &$analog));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    
    
    /*
     * Добавление лействий.
     */
    $arActions = array();
    
    // Удаление элемента.
    $arActions []= array(
        'ICON' => 'edit',
        'DEFAULT' => true,
        'TEXT' => GetMessage('ACTION_EDIT'),
        'ACTION' => $lAdmin->ActionRedirect("linemedia.autoanalogssimple_add.php?ID=".$analog['id']."&lang=".LANGUAGE_ID.GetFilterParams("filter_")),
        'DEFAULT' => true
    );
    
    $arActions []= array(
        'ICON' => 'delete',
        'DEFAULT' => true,
        'TEXT' => GetMessage('ACTION_DELETE'),
        'ACTION' => $lAdmin->ActionRedirect("linemedia.autoanalogssimple_list.php?ID[]=".$analog['id']."&action_button=delete&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
    );
    
    
    /*
     * Создание событий для модуля
     */
    $events = GetModuleEvents("linemedia.autoanalogssimple", "OnAfterAdminShowAnalogRow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$row, &$analog, &$arActions));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    $row->AddActions($arActions);
}








// Подвал списка
$lAdmin->AddFooter(
    array(
        array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $analogs_list->SelectedRowsCount()),
        array("title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0", "counter" => true),
    )
);


// Групповые операции.
$arGroupActions = array(
    'delete' => GetMessage("GROUP_ACTION_DELETE"),
);


$lAdmin->AddGroupActionTable($arGroupActions);



// сформируем меню из одного пункта - добавление рассылки
$aContext = array(
  array(
    "TEXT"=>GetMessage("LM_AUTO_AS_ADD"),
    "LINK"=>"/bitrix/admin/linemedia.autoanalogssimple_add.php?lang=" . LANG,
    "TITLE"=>GetMessage("LM_AUTO_AS_ADD"),
    "ICON"=>"btn_new",
  ),
);


// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>







<form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
<?
$arFilterFieldsTmp = array(
    GetMessage("LM_AUTO_AS_IMPORT_ID"),
    GetMessage("LM_AUTO_AS_ADDED"),
    GetMessage("LM_AUTO_AS_ARTICLE"),
    GetMessage("LM_AUTO_AS_BRAND_TITLE"),
);

$oFilter = new CAdminFilter(
    $sTableID."_filter",
    $arFilterFieldsTmp
);

$oFilter->Begin();
?>

<tr>
    <td><b><?= GetMessage("LM_AUTO_AS_IMPORT_ID") ?>:</b></td>
    <td>
       <input type="text" name="filter_import_id" value="<?=htmlspecialchars($filter_import_id)?>" />
    </td>
</tr>
<tr>
    <td><?= GetMessage("LM_AUTO_AS_ADDED") ?>:</td>
    <td>
        <?= CalendarPeriod("filter_added_from", $filter_added_from, "filter_added_to", $filter_added_to, "find_form", "Y") ?>
    </td>
</tr>
<tr>
    <td><b><?= GetMessage("LM_AUTO_AS_ARTICLE") ?>:</b></td>
    <td>
       <input type="text" name="filter_article" value="<?=htmlspecialchars($filter_article)?>" />
    </td>
</tr>
<tr>
    <td><b><?= GetMessage("LM_AUTO_AS_BRAND_TITLE") ?>:</b></td>
    <td>
       <input type="text" name="filter_brand_title" value="<?=htmlspecialchars($filter_brand_title)?>" />
    </td>
</tr>


<?
$oFilter->Buttons(
    array(
        "table_id" => $sTableID,
        "url" => $APPLICATION->GetCurPage(),
        "form" => "find_form"
    )
);
$oFilter->End();
?>

</form>

<!-- Данные -->
<? $lAdmin->DisplayList(); ?>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>


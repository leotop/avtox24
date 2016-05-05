<?
if (!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();

$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID'])>0?intval($arParams['IBLOCK_ID']):0;
$arParams['WHAT_SHOW'] = strlen(trim($arParams['WHAT_SHOW'])) > 0?trim($arParams['WHAT_SHOW']):'SEO_BLOCK_1';

$arSelect = array('ID', 'IBLOCK_ID', 'NAME');
foreach (array('SET_KEYWORDS'=>'keywords','SET_TITLE'=>'title','SET_DESCRIPTION'=>'description','SET_H1'=>'h1') as $key=>$prop) {
    $arParams[ $key ] = ($arParams[ $key ]==='Y');
    if ($arParams[ $key ]) {
        $arSelect[] = 'PROPERTY_'.$prop;
    }
}//foreach

CModule::IncludeModule('iblock');

$url = trim(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT));
$rs = CIBlockElement::GetList(array(), array('IBLOCK_TYPE'=>$arParams['IBLOCK_TYPE'], 'ACTIVE'=>'Y','IBLOCK_ID'=>$arParams['IBLOCK_ID'], 'NAME'=>$url), 0, 0, $arSelect);
$arResult['FOUND'] = $rs->SelectedRowsCount() < 1;
/**
  ["edit"]=>
  array(1) {
    ["add_element"]=>
    array(7) {
      ["TEXT"]=>
      string(31) "Добавить элемент"
      ["TITLE"]=>
      string(31) "Добавить элемент"
      ["ACTION"]=>
      string(323) "javascript:(new BX.CAdminDialog({'content_url':'/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=282&type=linemedia_auto&lang=ru&force_catalog=&filter_section=0&IBLOCK_SECTION_ID=0&bxpublic=Y&from_module=iblock&return_url=%2F%3Fbitrix_include_areas%3DY%26login%3Dyes%26clear_cache%3DY','width':'700','height':'400'})).Show()"
      ["ACTION_URL"]=>
      string(235) "/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=282&type=linemedia_auto&lang=ru&force_catalog=&filter_section=0&IBLOCK_SECTION_ID=0&bxpublic=Y&from_module=iblock&return_url=%2F%3Fbitrix_include_areas%3DY%26login%3Dyes%26clear_cache%3DY"
      ["ONCLICK"]=>
      string(312) "(new BX.CAdminDialog({'content_url':'/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=282&type=linemedia_auto&lang=ru&force_catalog=&filter_section=0&IBLOCK_SECTION_ID=0&bxpublic=Y&from_module=iblock&return_url=%2F%3Fbitrix_include_areas%3DY%26login%3Dyes%26clear_cache%3DY','width':'700','height':'400'})).Show()"
      ["ICON"]=>
      string(30) "bx-context-toolbar-create-icon"
      ["ID"]=>
      string(30) "bx-context-toolbar-add-element"
    }
  }
*/
// $arButtons = array();

if ($arResult['FOUND']) {
    $arResult['ELEMENT'] = $rs->Fetch();
  /*  $arButtons['edit'] = array(
                                'edit_seoblock'=> array(
                                                    'TEXT'=>'Редактировать элемент',
                                                    'TITLE'=>'Редактировать элемент',
                                                    'ICON'=>'bx-context-toolbar-create-icon',
                                                    'ID'=>'lm-auto-edit-seo-block',
                                                    'ONCLICK'=>'(alert("click works!"))',
                                                    'ACTION'=>'(alert("action works!"))',
                                                    "ACTION_URL"=>'/index.php'
                                                )
                              );
*/
} else {
  /*  $arButtons['add'] = array(
                                'add_seoblock'=> array(
                                                    'TEXT'=>'Добавить элемент',
                                                    'TITLE'=>'Добавить элемент',
                                                    'ICON'=>'bx-context-toolbar-create-icon',
                                                    'ID'=>'lm-auto-edit-seo-block',
                                                    'ONCLICK'=>'(alert("add click works!"))',
                                                    'ACTION'=>'(alert("add action works!"))',
                                                    "ACTION_URL"=>'/index.php'
                                                )
                              );
*/
}

if ($APPLICATION->GetShowIncludeAreas()) {
    $this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
}

$this->IncludeComponentTemplate();
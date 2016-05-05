<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
$APPLICATION->AddHeadScript($templateFolder . '/js/tablesorter.js');
$APPLICATION->AddHeadScript($templateFolder.'/js/jquery.cookie.js');

if(LinemediaAutoCrossesApiDriver::isEnabled() && !in_array('info', (array) $GLOBALS['LM_AUTO_SEARCH_RESULTS_HIDE_FIELDS'])) {
    $APPLICATION->SetAdditionalCSS('/bitrix/components/linemedia.auto/search.detail.info.cross/templates/ajax/style.css', true);
}
?>

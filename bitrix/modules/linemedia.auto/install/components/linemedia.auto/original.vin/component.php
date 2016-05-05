<?php
/*
 * Обертка одноименного компонента из linemedia.autooriginalcatalogs
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


global $APPLICATION;

$APPLICATION->IncludeComponent("linemedia.autooriginalcatalogs:original.vin", ".default", $arParams, false);
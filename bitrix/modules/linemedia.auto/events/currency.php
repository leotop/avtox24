<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/**
 * Linemedia Autoportal
 * Main module
 * Module events for main bitrix module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


class LinemediaAutoEventCurrency
{

	/**
	 * Update currency cache
	 */
	public function OnCurrencyRateUpdate_UpdateCurrencyCache($ID, $arFields)
	{
		$obCache = new CPHPCache();
		$life_time = 24 * 60 * 60;
		$cache_id = 'price-currencies-'.date('d.m.Y');
		/*
		 * Set method name from another event OnItemPriceCalculate_convertSupplierCurrency:/linemedia.auto/events.php
		 */
		$obCache->InitCache($life_time, $cache_id, "/OnItemPriceCalculate_convertSupplierCurrency/");

		if (!CModule::IncludeModule('currency')) {
			$base_currency = CCurrency::GetBaseCurrency();
			$lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
			while ($lcur_res = $lcur->Fetch()) {
				$currencies[ $lcur_res["CURRENCY"] ] = CCurrencyRates::GetConvertFactor($lcur_res['CURRENCY'], $base_currency);
			}

			if ($obCache->StartDataCache()) {
				$obCache->EndDataCache(array('currencies' => $currencies, 'base' => $base_currency));
			}
		}
	}
}

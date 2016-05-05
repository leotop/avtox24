<?php

/**
 * Linemedia Autoportal
 * Main module
 * transaction title class
 * @author Linemedia
 * @since 22/01/2012
 * @link http://auto.linemedia.ru/
 */


//include lang file
IncludeModuleLangFile(__FILE__);


/**
 * class LinemediaAutoTransactionTitle provides transactions title
 */
class LinemediaAutoTransactionTitle
{
	/**
	 * transactions description participating in open interface (bitrix transactions)
	 */  
	const COMMODITY_IN_RESERVE = 'chargingOffCommodityInReserve';
	const DEPOSIT_REFUSED_IN_SHIPMENT = 'depositRefuseInShipment';
	const DEPOSIT_REFUSED_BY_SUPPLIER = 'depositRefuseBySupplier';
	const DEPOSIT_RETURN_COMMODITY = 'depositReturnCommodity';
	const DEAL_CLOSED_BY_COMMODITY = 'dealClosedByCommodity';
	const DEPOSIT_FUNDS = 'depositFunds';
	
	/**
	 * closed transactions
	 */
	const CLOSED_BY_MONEY = 'ordersClosedByMoney';
	const CLOSED_BY_COMMODITIES = 'ordersClosedByCommodities';
	
	/**
	 * description for creating transaction during depositing funds on account
	 * @var string
	 */
	const DEPOSIT_FUNDS_DESCRIPTION_TRANS = 'depositFunds';
	
	/**
	 * mapper from eng to rus
	 * @return array
	 */
	public static function transateFromEngToRus() {
		return array(
			self::COMMODITY_IN_RESERVE => GetMessage('COMMODITY_IN_RESERVE'),
			self::DEPOSIT_REFUSED_IN_SHIPMENT => GetMessage('DEPOSIT_REFUSED_IN_SHIPMENT'),
			self::DEPOSIT_REFUSED_BY_SUPPLIER => GetMessage('DEPOSIT_REFUSED_BY_SUPPLIER'),
			self::DEPOSIT_RETURN_COMMODITY => GetMessage('DEPOSIT_RETURN_COMMODITY'),
			self::DEAL_CLOSED_BY_COMMODITY => GetMessage('DEAL_CLOSED_BY_COMMODITY'),
			self::DEPOSIT_FUNDS => GetMessage('DEPOSIT_FUNDS'),
			
		    self::CLOSED_BY_COMMODITIES => GetMessage('CLOSED_BY_COMMODITIES'),
			self::CLOSED_BY_MONEY => GetMessage('CLOSED_BY_MONEY')
		);
	}
	
}


<?php

/**
 * Linemedia Autoportal
 * Main module
 * auxiliary class
 * @author Linemedia
 * @since 22/01/2012
 * @link http://auto.linemedia.ru/
 */


/**
 * class LinemediaAutoTransactionAuxiliaryClass provides auxiliary methods
 * for solving abitrary tasks in LinemediaAutoTransactionFactory class
 */
class LinemediaAutoTransactionAuxiliaryClass
{
	
	/**
	 * extract both transaction and all carts, comprised in this transaction
	 * @param int $ID
	 * @param \LinemediaAutoTransactionFactory $factory
	 * @return array
	 */
	public static function extractTransactionWithProperBasket($ID, \LinemediaAutoTransactionFactory $factory) {

		$params = (array) CSaleUserTransact::GetByID($ID);
		$basket = $factory->getTransactionEntity()->findByConditions(array(
				'ID_BITRIX_TRANSACTION' => $ID
		))->Fetch();

		return array(
			'transaction' => $params,
			'basket' => $basket
		);
	
	}
	
	/**
	 * successive udpate both transaction and carts
	 * @param int $basketID
	 * @param array $transaction
	 * @param \LinemediaAutoTransactionFactory $factory
	 * @param int $directorID
	 * @param float $funds
	 * @return void
	 */
	public static function updateEntities($basketID, $transaction, \LinemediaAutoTransactionFactory $factory, $userID, $funds = 0) {
	
		$basket = CSaleBasket::GetByID($basketID);
		$factory->getTransactionEntity()->update(
				$basketID, 
				array(
					'PAID' => 'Y'
				)
		);
		
		self::updateTransaction(
			$transaction['ID'], 
			array(
				'DESCRIPTION' => $transaction['description']
			)
		);
		
		self::updateUserAccount($userID, $funds, 'RUB');
		
	}
	
	/**
	 * an attempt to update user account. in case of success should return code of account or fizzle accordingly
	 * @param int $userId
	 * @param float $writtenFunds
	 * @param string $currency
	 * @param string $description
	 * @param string $orderId
	 * @param string $notes
	 * @return boolean|number
	 */
	public static function updateUserAccount($userId, $writtenFunds, $currency, $description = '', $orderId = '', $notes = '') {
		
		global $DB;
		$userId = IntVal($userId);
		if ($userId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_EMPTYID"), "EMPTY_USER_ID");
			return False;
		}
		
		$dbUser = CUser::GetByID($userId);
		if (!$dbUser->Fetch())
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $userId, GetMessage("SKGU_NO_USER")), "ERROR_NO_USER_ID");
			return False;
		}
		
		$writtenFunds = str_replace(",", ".", $writtenFunds);
		$writtenFunds = DoubleVal($writtenFunds);
		
		$currency = trim($currency);
		if (strlen($currency) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_EMPTY_CUR"), "EMPTY_CURRENCY");
			return False;
		}
	
		$result = false;
		
		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userId, "CURRENCY" => $currency)
		);
		
		if ($arUserAccount = $dbUserAccount->Fetch())
		{
			$arFields = array(
					"CURRENT_BUDGET" => $arUserAccount["CURRENT_BUDGET"] + $writtenFunds
			);

			$result = CSaleUserAccount::Update($arUserAccount["ID"], $arFields);
		}
		else
		{
			$arFields = array(
					"USER_ID" => $userId,
					"CURRENT_BUDGET" => $writtenFunds,
					"CURRENCY" => $currency,
					"LOCKED" => "Y",
					"DATE_LOCKED" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)))
			);
			$result = CSaleUserAccount::Add($arFields);
		}
	
		if ($result)
		{
			$arFields = array(
					"USER_ID" => $userId,
					"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
					"AMOUNT" => $writtenFunds,
					"CURRENCY" => $currency,
					"DEBIT" => 'N',
					"ORDER_ID" => (($orderId > 0) ? $orderId : False),
					"DESCRIPTION" => ((strlen($description) > 0) ? $description : False),
					"NOTES" => ((strlen($notes) > 0) ? $notes : False),
					"EMPLOYEE_ID" => ($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : False)
			);
		}
			
		return (bool) $result;
	}
	
	/**
	 *  an attempt to update transaction. in case of success should return code of account or fizzle accordingly
	 * @param $array
	 * @param int $transId
	 * @param array $array
	 * @return boolean
	 */
	public static function updateTransaction($transId, $array) {
		return (bool) CSaleUserTransact::Update($transId, $array);
	}
	
	/**
	 * an attempt to update transaction. in case of success should return code of account or false of fizzle accordingly
	 * @param $array
	 * @return boolean
	 */
	public static function addTransaction($array) {
		return (bool) CSaleUserTransact::Add($array);
	}
	
	/**
	 * transfer basket to given status accordingly
	 * @param int $ID
	 * @param string $status
	 */
	public static function transferBasketToStatus($ID, $status) {
	
		global $APPLICATION;
		$status_error = false;
		$obasket = new \LinemediaAutoBasket();
	
		//set to force upon each event handler not to send letter on each alteration
		$_SESSION['LM_AUTO_CABINET_ADMIN_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET'] = true;
	
		//transfer basket to given status
		$obasket->statusItem($ID, $status);
	
		if (!$status_error) {
	
			//event on status sending
			$events = GetModuleEvents("linemedia.auto", "OnAfterBasketStatusesChange");
			while ($arEvent = $events->Fetch()) {
				ExecuteModuleEventEx($arEvent, array(&$ID, &$status));
			}
		}
	
		unset($_SESSION['LM_AUTO_CABINET_ADMIN_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET']);
		return !$status_error;
	}

	
	/**
	 * get transaction in following status ChargingOffCommodityInReserve
	 * @param int $userID
	 * @return array
	 */
	public static function getAppropriateTransactions($userID) {
	
		$translator = \LinemediaAutoTransactionTitle::transateFromEngToRus();
		$transactions = array();
		$fetched = CSaleUserTransact::GetList(
				array(),
				array(
					'USER_ID' => $userID,
					'DESCRIPTION' => array(
							$translator[\LinemediaAutoTransactionTitle::COMMODITY_IN_RESERVE]
					)
				)
		);
	
		while ($o = $fetched->Fetch()) {
			$transactions[] = $o;
		}
	
		return $transactions;
	}
	
	
	/**
	 * pay basket from incoming funds untill amount great than zero;
	 * if amount is insufficient to close basket than transfer remaining funds in position sum_paid else
	 * close fields -> sum_paid, paid
	 * @param float $funds
	 * @param \LinemediaAutoTransactionBDTable $trans
	 * @param int $transID
	 * @return array
	 */
	 public static function fillBasketByIncomingFunds(&$funds, \LinemediaAutoTransactionBDTable $trans, $transID) {
	
	 	global $DB;
	 	$basket = $trans->findByConditions(array(
	 			'ID_BITRIX_TRANSACTION' => $transID
	 	))->Fetch();

	 	if (strcmp($basket['PAID'], 'Y') != 0) {
	 		if ($funds >= $basket['PRICE'] - $basket['SUM_PAID']) {
	 			$trans->update(
	 				$basket['BASKET_ID'],
	 				array(
	 					'PAID' => 'Y',
	 					'DATE_TO_PAID' => date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))), 
	 					'SUM_PAID' => $basket['PRICE']
	 				)
	 			);
	 			$funds -= ($basket['PRICE'] - $basket['SUM_PAID']);
	 			
	 		} else {
	 			$trans->update(
	 				$basket['BASKET_ID'], 
	 					array(
	 						'SUM_PAID' => $funds + $basket['SUM_PAID']
	 					)
	 				);
	 			$funds = 0;
	 		}
	 	}
	
	 	return array(
	 			$funds, 
	 			$basket['BASKET_ID']
	 	);
	 }
	
	
	 /**
	  * retrieve basket comprised in transaction and transfer it to status if PAID = 'Y' -> ordersClosedByMoney, SHIPED = 'Y' -> ordersClosedByCommodities
	  * if both set in position 'Y' -> dealClosedByCommodity
	  * @param int $transID
	  * @param \LinemediaAutoTransactionBDTable $trans
	  * @return void
	  */
	 public static function trasnferTransactionToStatus($transID, \LinemediaAutoTransactionBDTable $trans) {
	
	 	$basket = $trans->findByConditions(array(
	 			'ID_BITRIX_TRANSACTION' => $transID
	 	))->Fetch();
	    $transalator = \LinemediaAutoTransactionTitle::transateFromEngToRus();
	
	 	if ($basket['PAID'] == 'Y') {
	 		CSaleUserTransact::Update(
	 		    $transID, 
	 		    array(
	 		       'DESCRIPTION' => $transalator[\LinemediaAutoTransactionTitle::CLOSED_BY_MONEY]
	 		    )
	 		);
	 		if ($basket['DONE'] == 'Y') {
	 			CSaleUserTransact::Update(
	 			    $transID, 
	 			    array(
	 			       'DESCRIPTION' => $transalator[\LinemediaAutoTransactionTitle::DEAL_CLOSED_BY_COMMODITY]
	 			    )
	 			);
	 		}
	 	}
	 	
	 }
	
}
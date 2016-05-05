<?php

/**
 * Linemedia Autoportal
 * Main module
 * Transaction management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 * 
 * seeking for the most ancient transaction kind of WriteOffGoodsReserve or ChangeWriteOffReserveClosed
 * put on payment.If transaction is paid partially point it in summary if quite do same plus vary its status to
 * ChangeWriteOffReservePaid or ChangeWriteOffReserveClosedPaid accordingly
 * If deposit was more than found unpaid transaction iterate algorithm dropping off amount by amount of paid transaction
 *
 * $depositedFunds -> $transParam(userid, amount)
 * $transId
 *
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoCabinetAdminDepositUserAccount
{


	//debug variables
	//private static $countfillBasketAmount = 0;
	//private static $countvaryStatusTransaction = 0;

	/**
	 * pay each cart from incoming transaction untill amount great than zero;
	 * if amount is insufficient to close cart than transfer remaining funds in pos sum_paid else
	 * close fields -> sum_paid, paid
	 * @param float $amount
	 * @param \LinemediaAutoTransactions $obj
	 * @param int $transId
	 * @return float
	 */
	private static function fillBasketAmount(&$amount, \LinemediaAutoTransactions &$obj, $transId) {

		global $DB;
		$unwroughtCarts = $obj->retrievedByFilter(array('ID_BITRIX_TRANSACTION' => $transId));
		$cartIter = new \ArrayIterator();

		while ($basket = $unwroughtCarts->Fetch()) {
			$cartIter->append($basket);
		}


		while ($cartIter->valid() && $amount > 0) {

			if (strcmp($cartIter->current()['PAID'], 'Y') != 0) {

				if ($amount >= $cartIter->current()['PRICE'] - $cartIter->current()['SUM_PAID']) {

					$obj->update($cartIter->current()['BASKET_ID'], array('PAID' => 'Y', 'DATE_TO_PAID' => date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))), 'SUM_PAID' => $cartIter->current()['PRICE']));
					$amount -= ($cartIter->current()['PRICE'] - $cartIter->current()['SUM_PAID']);
				}
				else {

					$obj->update($cartIter->current()['BASKET_ID'], array('SUM_PAID' => $amount + $cartIter->current()['SUM_PAID']));
					$amount = 0;
				}
			}

			$cartIter->next();
		}

		return $amount;
	}


	/**
	 * retrieve all carts regarding transactions id if all value of both paid and done set in value 'Y'
	 * transfer it to status ChangeWriteOffReserveClosedPaid if all paid set in value 'Y', and have one done in value 'N' -> ChangeWriteOffReservePaid
	 * else do nothing
	 * @param int $transId
	 * @param \LinemediaAutoTransactions $obj
	 * @return void
	 */
	private static function varyStatusTransaction($transId, \LinemediaAutoTransactions $obj) {

		$arrayOfCart = new \ArrayIterator();
		$unwroughtCart = $obj->retrievedByFilter(array('ID_BITRIX_TRANSACTION' => $transId));

		$done = true;
		$paid = true;

		while ($basket = $unwroughtCart->Fetch()) {
			$arrayOfCart->append($basket);
		}

		foreach ($arrayOfCart as $cart) {


			if ($done && $cart['DONE'] == 'N') {
				$done = false;
			}

			if ($paid && $cart['PAID'] == 'N') {
				$paid = false;
			}

		}

		$transDescr = LinemediaAutoAbstractStatus::getTransactionTtitle();
		
		if (false) {
			CSaleUserTransact::Update($transId, array('DESCRIPTION' => $transDescr['ChangeWriteOffReserveClosedPaid']));
		}

		if ($paid && !$done) {
			CSaleUserTransact::Update($transId, array('DESCRIPTION' => $transDescr['ChangeWriteOffReservePaid']));
		}
	}


	/**
	 * get transaction in following status ChargingOffCommodityInReserve
	 * @param int $userID
	 * @return array
	 */
	private static function getAppropriateTransactions($userID) {

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
			$transactions[] = $obj;
		}

		return $arrayOfTrans;
	}


	/**
	 * triggered on adding custom transaction. Need to transfer into function to extract overall transaction -> cartId or directorId at once
	 * and retrievable funds (USER_ID == DIRECTOR_ID)
	 * @param int $transId
	 * @param float $amount
	 */
	public static function execute($userId, $amount) {

		$arrayOfTrans =  self::getAppropriateTransactions($userId);

		//loop through set of transactions
		while ($arrayOfTrans->valid() && $amount > 0) {

		    $bulkTrans = $arrayOfTrans->current();
			$amount = self::fillBasketAmount($amount, new \LinemediaAutoTransactions(), $bulkTrans['ID']);
		    self::varyStatusTransaction($bulkTrans['ID'], new \LinemediaAutoTransactions());
			$arrayOfTrans->next();
		}

		return true;

	}

}




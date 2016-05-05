<?php

/**
 *
 * Linemedia Autoportal
 * Main module
 * Transaction management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 *
 * status, id basket, then create and write down a transaction with appropriate arguments
 * id basket, id order, timestamp when order is closed, paid - partial payment, overall cost of order, done - when order is taken an final status as F,
 * User id, type
 */


IncludeModuleLangFile(__FILE__);

/**
 * abstract class 
 */
abstract class LinemediaAutoAbstractStatus
{

    
    public static function getTransactionTtitle() {
        return array(
            'WriteOffGoodsReserve'            => GetMessage('LM_AUTO_WRITEOFF_TRANSACTION'),
            'ChangeWriteOffReserveClosed'     => GetMessage('LM_AUTO_WRITE_OFF_RESERVE_CLOSED_TRANSACTION'),
            'ChangeWriteOffReservePaid'       => GetMessage('LM_AUTO_WRITE_OFF_RESERVE_PAID_TRANSACTION'),
            'ChangeWriteOffReserveClosedPaid' => GetMessage('LM_AUTO_WRITE_OFF_RESERVE_CLOSED_PAID_TRANSACTION'),
            'AdditionCredit'                  => GetMessage('LM_AUTO_ADD_CREDIT_TRANSACTION'),
            'AdditionCash'                    => GetMessage('LM_AUTO_ADD_CASH_TRANSACTION'),
            'AdditionGoodsReturn'             => GetMessage('LM_AUTO_ADD_GOODS_RETURN_TRANSACTION'),
            'AdditionShipmentRejection'       => GetMessage('LM_AUTO_ADD_SHIP_REJ_TRANSACTION'),
            'AdditionSupplierRejection'       => GetMessage('LM_AUTO_ADD_SUPPLIER_REJ_TRANSACTION')
       );
    }
    
	/**
	 * instance of LinemediaAutoTransactions
	 * @var \LinemediaAutoTransactions $transaction
	 */
	protected $transaction;

	/**
	 * accodance between status and inherited class from AbstractStatus
	 * @var string accodance
	 */
	private static $accordance = '';

	/**
	 * initiate an instance of LinemediaAutoTransactions
	 */
	public function __construct() {
		$this->transaction = new \LinemediaAutoTransactions();
	}


	/**
	 * get an transaction instance
	 * @return instance
	 */
	public function getTransaction() {
		return $this->transaction;
	}


	/**
	 * return director Id of branch belonged to current admin
	 * @return int
	 */
	protected static function getDirectorId() {
	    
		global $USER;
		$outcome = CIBlockElement::GetProperty(
		    COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_IBLOCK_BRANCHES'),
		    \LinemediaAutoBranchesBranch::getAcceptableBranch($USER->GetID()),
		    array(), 
		    array('CODE' => 'director')
		)->Fetch();
		
		return $outcome['VALUE'];

	}


	/**
	 * return an inherited class from AbstractStatus dependent on conveyed status
	 * @param $status
	 * @return AbstractStatus
	 */
	public static function createStatus($status) {
		if (self::$accordance == '') {
			self::$accordance = unserialize(COption::GetOptionString('linemedia.auto', 'FINAL_STATUSES'));
		}

		return new self::$accordance[$status]();
	}

	/**
	 * compound method, fetching both transaction and all carts, having in this transaction
	 * @param int $transId
	 * @param \AbstractStatus $obj
	 * @return array
	 */
	protected static function extractMixTransCarts($transId, \LinemediaAutoAbstractStatus $obj) {

		$carts = array();
		$transFields = array(CSaleUserTransact::GetByID($transId));
		$unwroughtCarts = $obj->getTransaction()->retrievedByFilter(array('ID_BITRIX_TRANSACTION' => $transId));

		while ($c = $unwroughtCarts->Fetch()) {
			array_push($carts, $c);
		}

		return array_merge($transFields, array($carts));

	}

	/**
	 * successive udpate both transaction and carts
	 * @param int $cartId
	 * @param array $array
	 * @param \AbstractStatus $obj
	 * @param int $dirId
	 * @param float $price
	 * @param float $funds
	 * @param string $app
	 * @return array
	 */
	protected static function updateMixTransCarts($cartId, $array, \LinemediaAutoAbstractStatus $obj, $dirId, $price, $funds = 0, $app = '') {

		global $USER, $DB;
        $arrayCart = CSaleBasket::GetByID($cartId);
        $trans = (current($array));
        $paramUpdateTrans = end($array);
        
        $transDescript = self::getTransactionTtitle();
        
		if (is_array($trans) && !(bool) self::updateTransaction($trans['ID'], array('DESCRIPTION' => $transDescript[$trans['DESCRIPTION']]))) {
			return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_UPDATE_TR_FAILED'));
		}

		if (!$obj->getTransaction()->update($cartId, $paramUpdateTrans)) {
			return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_UPDATE_CART_FAILED'));
		}


		if (strcmp('OrderDone', static::$description) != 0) {

			if (!(bool) self::updateUserAccount($dirId, -$arrayCart['PRICE'], 'RUB', static::$description, $arrayCart['ORDER_ID'])) {
				return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_CORRUPT_USER'));
			}


			$transArg = array(
					'USER_ID' => $dirId, 'AMOUNT' => $price, 'CURRENCY' => 'RUB', 'DEBIT' => 'Y', 'NOTES' => '', 'ORDER_ID' => $arrayCart['ORDER_ID'],
					'EMPLOYEE_ID' => $USER->GetID(), 'TRANSACT_DATE' => date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))), 'DESCRIPTION' => $app
			);

			$arrayCartSettings = array(
					'BASKET_ID' => $cartId, 'DIRECTOR_ID' => $dirId, 'PRICE' => $arrayCart['PRICE']*$arrayCart['QUANTITY'], 'DONE' => '-', 'PAID' => '-',
					'ORDER_ID' => $arrayCart['ORDER_ID'], 'DATE_TO_DONE' => 0, 'DATE_TO_PAID' => 0, 'SUM_PAID' => 0
			);



			if (!(bool) $obj->getTransaction()->createItems(
			               array_merge(array($transArg), array($arrayCartSettings))
			            )
			) {
				return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_CORRUPTED_TRANS'));
			}


			if ((int) $funds != 0) {
				\LinemediaAutoCabinetAdminDepositUserAccount::execute($dirId, $funds);
			}

		}

		return array(true);
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
	protected static function updateUserAccount($userId, $writtenFunds, $currency, $description = '', $orderId = '', $notes = '') {

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

		$orderId = IntVal($orderId);
		if (!CSaleUserAccount::Lock($userId, $currency))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_ACCOUNT_NOT_WORK"), "ACCOUNT_NOT_LOCKED");
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
					"CURRENT_BUDGET" => $arUserAccount["CURRENT_BUDGET"] - $writtenFunds
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

		if (!CSaleUserAccount::UnLock($userId, $currency)) {

			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_ACCOUNT_NOT_WORK"), "ACCOUNT_NOT_LOCKED");
			return False;
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
	protected static function updateTransaction($transId, $array) {
		return (bool) CSaleUserTransact::Update($transId, $array);
	}

	/**
	 * an attempt to update transaction. in case of success should return code of account or false of fizzle accordingly
	 * @param $array
	 * @return boolean
	 */
	protected static function addTransaction($array) {
		return (bool) CSaleUserTransact::Add($array);
	}

	/**
	 * for each cart create an additional property - status and transfer it to given status accordingly
	 * @param array $arrayBasketId
	 * @param char $status
	 */
	protected static function transferCartToStatus($arrayBasketId, $status) {

		global $APPLICATION;
		$status_error = false;
		$obasket = new \LinemediaAutoBasket();

		//set to force upon each event handler not to send letter on each alteration
		$_SESSION['LM_AUTO_CABINET_ADMIN_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET'] = true;

		//transfer each cart to given status
		foreach ($arrayBasketId as $id) {

			$obasket->statusItem($id, $status);
			if ($ex = $APPLICATION->GetException()) {
				ShowError(GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_CART_ERROR').': '.$ex->GetString()); //TODO awful row
				$status_error = true;
			}
		}

		if (!$status_error) {

			//event on status sending
			$events = GetModuleEvents("linemedia.auto", "OnAfterBasketStatusesChange");
			while ($arEvent = $events->Fetch()) {
				ExecuteModuleEventEx($arEvent, array(&$arrayBasketId, &$status));
			}
		}

		unset($_SESSION['LM_AUTO_CABINET_ADMIN_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET']);

		return !$status_error;
	}

	/**
	 * abstract function of creating appropriate strategy in subclasses
	 * @param array $arrayBasketId
	 * @param char $status
	 */
	abstract public function createTransaction($arrayBasketId, $status);

}

/**
 *
 * writing off funds out of customer , negative transaction
 * when director transfer to this status, from directors account funds is written off,
 * and if his current limit is exceeded then cast an exception, comprising his current limit, amount on which limit was exceeded
 * if everyhing was gone smoothly than transaction is being recorded into database within arguments described above
 * @author Sonny
 *
 */
class LinemediaAutoApprovedByDirector extends LinemediaAutoAbstractStatus
{

    
     public static $count = 0;
    
	/**
	 * appellation of class
	 * @var string class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * transactions description
	 * @var string $description
	 */
    public static $description = 'approvedByDirector';

    /**
     * call parent constructor
     */
    public function __construct() {
    	parent::__construct();
    }

	/**
	 * (non-PHPdoc)
	 * @see AbstractStatus::createTransaction()
	 */
	public function createTransaction($arrayBasketId, $status) {
	    
		global $USER, $DB;
		$branchDirector = new LinemediaAutoBranchesDirector();
        $cost = 0;
        $directorGroup = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR');
        
        
        //whether current user belong to directorgroup
        if (!in_array($directorGroup, $USER->GetUserGroupArray())) {
            return array(false, GetMessage('LM_AUTO_DIRECTOR_ALLOWED'));
        }
        
		//whether delay is up-to-date
		if ($branchDirector->getCurrrentDelay() < 0) {
			return array(false, GetMessage('LM_AUTO__DIRECTOR_CLOSE_DELAY'));
		}


		$arrayCartSettings = array();
		$arrayBasketIdIter = new \ArrayIterator($arrayBasketId);

		/**
		 *  going through each ID
		 */

		while ($id = $arrayBasketIdIter->current()) {

			$fetchedBasket = CSaleBasket::GetByID($id);
			$cost += $fetchedBasket['PRICE'] * $fetchedBasket['QUANTITY'];

			if ($branchDirector->getCurrentLimit() < $cost) {
				return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_NOT_ENOUGH_MONEY') . ($cost - $branchDirector->getCurrentLimit()));
			}

			$arrayCartSettings[] = array(
			     'BASKET_ID' => $arrayBasketIdIter->current(), 'DIRECTOR_ID' => $USER->GetID(), 'PRICE' => $fetchedBasket['PRICE'] * $fetchedBasket['QUANTITY'],
				 'DONE' => 'N', 'PAID' => 'N', 'ORDER_ID' => $fetchedBasket['ORDER_ID'], 'DATE_TO_DONE' => null, 'DATE_TO_PAID' => null, 'SUM_PAID' => 0
			);

			$arrayBasketIdIter->next();

		}
		
		
		/*
		 * if transaction for current user id exists then update it otherwise one should be created
		 *  descr -> type of transaction
		 *  notes -> json supplementary data
		 *  done - status
		 *  cost - cost of commodities
		 *  status - in which status order was transfered
		 */
		$transArg[] = array(
				'USER_ID' => $USER->GetID(), 'AMOUNT' => $cost, 'CURRENCY' => 'RUB', 'DEBIT' => 'N', 'NOTES' => '', 'ORDER_ID' =>  $fetchedBasket['ORDER_ID'],
				'EMPLOYEE_ID' => $USER->GetID(), 'TRANSACT_DATE' => date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))), 'DESCRIPTION' => 'WriteOffGoodsReserve'
		);

		
		if (!(bool) $this->transaction->createItems(array_merge($transArg, $arrayCartSettings))) {
			return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_CORRUPTED_TRANS'));
		}

		if (!(bool) self::updateUserAccount($USER->GetID(), $cost, 'RUB', self::$description, $fetchedBasket['ORDER_ID'])) {
			return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_CORRUPT_USER'));
		}

	    if (!(bool) self::transferCartToStatus($arrayBasketId, $status)) {
	    	return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_DIRECTOR_CART_ERROR'));
	    }
	    
	    
		return array(true);
	}
}

/**
 * return funds back to customer , positive transaction
 */
class LinemediaAutoRefusedBySupplier extends LinemediaAutoAbstractStatus
{

    /**
     * title of class
     * @var string CLASS_NAME
     */
    const CLASS_NAME = __CLASS__;
    
	/**
	 * status for class
	 * @var string $appStatus
	 */
	public static $appStatus = 'AdditionSupplierRejection';

	/**
	 * will be passed to transactions argument as description (without it not pass)
	 * @var string $description
	 */
	public static $description = 'RefusedBySupplier';

	/**
     *  call parent constructor
     */
    public function __construct() {
    	parent::__construct();
    }

	/**
	 * (non-PHPdoc)
	 * @see AbstractStatus::createTransaction()
	 */
	public function createTransaction($arrayBasketId, $status) {

		global $USER, $DB;
        $cost = 0;
        $managerGroups = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
        
        //whether current user belong to logistgroup
        if (!in_array($managerGroups, $USER->GetUserGroupArray())) {
            return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_ALLOWED'));
        }
        
		$arrayCartSettings = array();
		$arrayBasketIdIter = new \ArrayIterator($arrayBasketId);
		
		/**
		 *  going through each ID
		*/

		while ($id = $arrayBasketIdIter->current()) {

		    
			$fetchedCartFields = $this->transaction->retrievedById($id)->GetNext();
			list($trans, $carts) = self::extractMixTransCarts($fetchedCartFields['ID_BITRIX_TRANSACTION'], $this);
            $funds = 0;
            $price = 0;

            foreach ($carts as $key => &$cart) {

            	if ($cart['BASKET_ID'] == $id) {

            		$funds = $cart['SUM_PAID'];
            		$price = $cart['PRICE'];
            		$cart['SUM_PAID'] = $cart['PRICE'];
            		break;
            	}
            }

            foreach ($carts as $key => $cart) {

            	if (InvariableStatusTransaction::$done && strcmp($cart['DONE'], 'N') == 0) {
            		InvariableStatusTransaction::$done = 2;
            	}

            	if (InvariableStatusTransaction::$paid && strcmp($cart['PAID'], 'N') == 0) {
            		InvariableStatusTransaction::$paid = 3;
            	}

            }

            $oldDescr = $trans['DESCRIPTION'];
            $trans['DESCRIPTION'] = InvariableStatusTransaction::getInvariableStatus(InvariableStatusTransaction::$done, InvariableStatusTransaction::$paid, $oldDescr);

            $cartUpdate = array(
            		'DONE' => 'Y', 'DATE_TO_DONE' =>  date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))),
            		'PAID' => 'Y', 'DATE_TO_PAID' =>  date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))),
            		'SUM_PAID' => $price
            );

            $trans = strcmp($oldDescr, $trans['DESCRIPTION']) == 0 ? $trans['ID'] : $trans;

            $success = self::updateMixTransCarts($id, array_merge(array($trans), array($cartUpdate)), $this, $fetchedCartFields['DIRECTOR_ID'], $price, $funds, self::$appStatus);

            if(!current($success)) {
            	return $success;
            }

			$arrayBasketIdIter->next();

		}
		
		return array(true);

	}

}

/**
 * return funds back to customer , positive transaction
 */
class LinemediaAutoRefusedInShipment extends LinemediaAutoAbstractStatus
{

    /**
     * title of class
     * @var string CLASS_NAME
     */
    const CLASS_NAME = __CLASS__;
    
	/**
	 * status for class
	 * @var string $appStatus
	 */
	public static $appStatus = 'AdditionShipmentRejection';

	/**
	 * will be passed to transactions argument as description (without it not pass)
	 * @var string $description
	 */
	public static $description = 'RefusedInShipment';

	 /**
     * calling parent constructor
     */
    public function __construct() {
    	parent::__construct();
    }

	/**
	 * (non-PHPdoc)
	 * @see AbstractStatus::createTransaction()
	 */
	public function createTransaction($arrayBasketId, $status) {

		global $USER, $DB;
		$cost = 0;
	    $managerGroups = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
        
        //whether current user belong to logistgroup
        if (!in_array($managerGroups, $USER->GetUserGroupArray())) {
            return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_ALLOWED'));
        }
        
		
		$arrayCartSettings = array();
		$arrayBasketIdIter = new \ArrayIterator($arrayBasketId);
		
		/**
		 *  going through each ID
		*/

		while ($id = $arrayBasketIdIter->current()) {

			$fetchedCartFields = $this->transaction->retrievedById($id)->GetNext();		
			list($trans, $carts) = self::extractMixTransCarts($fetchedCartFields['ID_BITRIX_TRANSACTION'], $this);
			$funds = 0;
			$price = 0;

			foreach ($carts as $key => &$cart) {

				if ($cart['BASKET_ID'] == $id) {

					$funds = $cart['SUM_PAID'];
					$price = $cart['PRICE'];
					$cart['SUM_PAID'] = $cart['PRICE'];
					break;
				}
			}

			foreach ($carts as $key => $cart) {

				if (InvariableStatusTransaction::$done && strcmp($cart['DONE'], 'N') == 0) {
					InvariableStatusTransaction::$done = 2;
				}

				if (InvariableStatusTransaction::$paid && strcmp($cart['PAID'], 'N') == 0) {
					InvariableStatusTransaction::$paid = 3;
				}

			}

			$oldDescr = $trans['DESCRIPTION'];	
			$trans['DESCRIPTION'] = InvariableStatusTransaction::getInvariableStatus(InvariableStatusTransaction::$done, InvariableStatusTransaction::$paid, $oldDescr);

			$cartUpdate = array(
					'DONE' => 'Y', 'DATE_TO_DONE' =>  date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))),
					'PAID' => 'Y', 'DATE_TO_PAID' =>  date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))),
					'SUM_PAID' => $price
			);

			$trans = strcmp($oldDescr, $trans['DESCRIPTION']) == 0 ? $trans['ID'] : $trans;

			$success = self::updateMixTransCarts($id, array_merge(array($trans), array($cartUpdate)), $this, $fetchedCartFields['DIRECTOR_ID'], $price, $funds, self::$appStatus);

			if(!current($success)) {
				return $success;
			}

			$arrayBasketIdIter->next();

		}

		return array(true);

	}

}

/**
 * return funds back to customer , positive transaction
 */
class LinemediaAutoMoneyBackApproved extends LinemediaAutoAbstractStatus
{

    /**
     * title of class
     * @var CLASS_NAME
     */
    const CLASS_NAME = __CLASS__;
    
	/**
	 * status for class
	 * @var string $appStatus
	 */
	public static $appStatus = 'AdditionGoodsReturn';

	/**
	 * will be passed to transactions argument as description (without it not pass)
	 * @var string $description
	 */
	public static $description = 'MoneyBackApproved';

	 /**
     * calling parent constructor
     */
    public function __construct() {
    	parent::__construct();
    }

	/**
	 * (non-PHPdoc)
	 * @see AbstractStatus::createTransaction()
	 */
	public function createTransaction($arrayBasketId, $status) {

		global $USER, $DB;
		$cost = 0;
	    $managerGroups = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
        
        //whether current user belong to logistgroup
        if (!in_array($managerGroups, $USER->GetUserGroupArray())) {
            return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_ALLOWED'));
        }
        
		
		$arrayCartSettings = array();
		$arrayBasketIdIter = new \ArrayIterator($arrayBasketId);


		/**
		 *  going through each ID
		*/

		while ($id = $arrayBasketIdIter->current()) {

			$fetchedCartFields = $this->transaction->retrievedById($id)->GetNext();
			list($trans, $carts) = self::extractMixTransCarts($fetchedCartFields['ID_BITRIX_TRANSACTION'], $this);
			$funds = 0;
			$price = 0;

			foreach ($carts as $key => &$cart) {

				if ($cart['BASKET_ID'] == $id) {

					$funds = $cart['SUM_PAID'];
					$price = $cart['PRICE'];
					$cart['SUM_PAID'] = $cart['PRICE'];
					break;
				}
			}

			foreach ($carts as $key => $cart) {

				if (InvariableStatusTransaction::$done && strcmp($cart['DONE'], 'N') == 0) {
					InvariableStatusTransaction::$done = 2;
				}

				if (InvariableStatusTransaction::$paid && strcmp($cart['PAID'], 'N') == 0) {
					InvariableStatusTransaction::$paid = 3;
				}

			}

			$oldDescr = $trans['DESCRIPTION'];
			$trans['DESCRIPTION'] = InvariableStatusTransaction::getInvariableStatus(InvariableStatusTransaction::$done, InvariableStatusTransaction::$paid, $oldDescr);

			$cartUpdate = array(
					'DONE' => 'Y', 'DATE_TO_DONE' =>  date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))),
					'PAID' => 'Y', 'DATE_TO_PAID' =>  date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL'))),
					'SUM_PAID' => $price
			);

			$trans = strcmp($oldDescr, $trans['DESCRIPTION']) == 0 ? $trans['ID'] : $trans;

			$success = self::updateMixTransCarts($id, array_merge(array($trans), array($cartUpdate)), $this, $fetchedCartFields['DIRECTOR_ID'], $price, $funds, self::$appStatus);

			if(!current($success)) {
				return $success;
			}

			$arrayBasketIdIter->next();

		}

		return array(true);

	}

}

/**
 *  vary basket field DONE to value 'Y' and if each carts in transaction have similar field in value 'Y'  than
 *  transfer trans discription to status if it was 'WriteOffGoodsReserve' => became 'ChangeWriteOffReserveClosed'
 *   was 'ChangeWriteOffReservePaid' => became 'ChangeWriteOffReserveClosedPaid', positive transaction
 */
class LinemediaAutoOrderDone extends LinemediaAutoAbstractStatus
{

    /**
     * title of class
     * @var CLASS_NAME
     */
    const CLASS_NAME = __CLASS__;
    
	/**
	 * will be passed to transactions argument as description (without it not pass)
	 * @var string $description
	 */
	public static $description = 'OrderDone';

	 /**
     * calling parent constructor
     */
    public function __construct() {
    	parent::__construct();
    }

	/**
	 * (non-PHPdoc)
	 * @see AbstractStatus::createTransaction()
	 */
	public function createTransaction($arrayBasketId, $status) {

		global $USER, $DB;
        $trigger = true;
        $managerGroups = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
        
        //whether current user belong to logistgroup
        if (!in_array($managerGroups, $USER->GetUserGroupArray())) {
            return array(false, GetMessage('LM_AUTO_CABINET_ADMIN_ALLOWED'));
        }
        
        
	    $arrayCartSettings = array();
		$arrayBasketIdIter = new \ArrayIterator($arrayBasketId);


		/**
		 *  going through each ID
		 */

		while ($id = $arrayBasketIdIter->current()) {

			$fetchedCartFields = $this->transaction->retrievedById($id)->GetNext();
            list($trans, $carts) = self::extractMixTransCarts($fetchedCartFields['ID_BITRIX_TRANSACTION'], $this);

            foreach ($carts as &$cart) {

            	if ($cart['BASKET_ID'] == $id) {
            		$fetchedCartFields['DONE'] = $cart['DONE'] = 'Y';
            	    break;
            	}
            }

            foreach ($carts as $key => &$cart) {

            	if ($trigger && strcmp($cart['DONE'], 'Y') != 0) {
            		$trigger = false;
            		break;
            	}
            }
           
            $trans['DESCRIPTION'] = array_search($trans['DESCRIPTION'], self::getTransactionTtitle());
            $trans['DESCRIPTION'] = $trigger ? InvariableStatusTransaction::getStatusOrderDone($trans['DESCRIPTION']) : $trans['DESCRIPTION'];         
            $success = self::updateMixTransCarts($id, array_merge(array($trans), array(array('DONE' => $fetchedCartFields['DONE'], 'DATE_TO_DONE' => date($DB->DateFormatToPHP(CLang::GetDateFormat('FULL')))))), $this, 0, 0, self::$description);

            if(!current($success)) {
            	return $success;
            }

			$arrayBasketIdIter->next();

		}

		return array(true);
	}

}

/**
 * auxiliary class, returning statuses depending on conveyed parameters
 */
class InvariableStatusTransaction
{
    
    /**
     * @var $done
     */
    public static $done = 1;
    
    /**
     * @var $paid
     */
    public static $paid = 1;

    /**
     * accordance for RefusedBySupplier, RefusedInShipmen, MoneyBackApproved
     * @var $accordance
     */
    public static $accordance = array(
        1 => 'ChangeWriteOffReserveClosedPaid', 2 => 'ChangeWriteOffReservePaid',
        3 => 'ChangeWriteOffReserveClosed'
    );

    /**
     * accordance for OrderDone
     * @var $accordanceDone
    */
    public static $accordanceDone = array(
        'WriteOffGoodsReserve' => 'ChangeWriteOffReserveClosed', 'ChangeWriteOffReservePaid' => 'ChangeWriteOffReserveClosedPaid'
    );


    /**
     * returning transaction description in accordance with conveyed argument especially
     * $done, $paid
     * @param int $done
     * @param int $paid
     * @param string $defaultDescr
     * @return string
     */
    public static function getInvariableStatus($done, $paid, $defaultDescr) {
        return array_key_exists($done*$paid, self::$accordance) ? self::$accordance[($done)*($paid)] : $defaultDescr;
    }

    /**
     * returning value of $accordanceDone
     * @param string $status
     * @return string
     */
    public static function getStatusOrderDone($status) {
        return self::$accordanceDone[$status];
    }

}





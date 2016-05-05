<?php

/**
 * Linemedia Autoportal
 * Main module
 * transaction factory class
 * @author Linemedia
 * @since 22/01/2012
 * @link http://auto.linemedia.ru/
 * 
 * 
 * mutable action and proper transaction -> 
 * negative transaction where funds are written off - APPROVED BY DIRECTOR (commodities in reserve)
 * bulk of positive transaction where funds are deposited - (DECLINE BY ADM, SUPPLIERS REFUSAL(either own store or remote supplier), COMMODITY RETURN)
 * 
 */


/**
 * class LinemediaAutoTransactionFactory creates transaction by given status
 */
class LinemediaAutoTransactionFactory
{
	
	/**
	 * class title
	 * @var string
	 */
	const TITLE = __CLASS__;
	
	/**
	 * titles of createRefuseInShipment transaction
	 * @var string
	 */
	const ACTION_SHIPMENT = 'createRefuseInShipment';
	
	/**
	 * titles of createRefuseBySupplier transaction
	 * @var string
	 */
	const ACTION_SUPPLIER = 'createRefuseBySupplier';
	
	/**
	 * titles of createReturnCommodity transaction
	 * @var string
	 */
	const ACTION_COMMODITY_RETURN = 'createReturnCommodity';
	
	/**
	 * titles of createDealClosedByCommodity transaction
	 * @var string
	 */
	const ACTION_DEAL_CLOSED = 'createDealClosedByCommodity';
	
	/**
	 * titles of createReserve transaction
	 * @var string
	 */
	const ACTION_RESERVE = 'createReserve';
	
	/**
	 * informative message
	 * @var string
	 */
	private $informativeMessage = '';
	
	/**
	 * @var string
	 */
	private $status;
	
	/**
	 * @var array
	 */
	private $basketsID;
	
	/**
	 * @var \LinemediaAutoTransactionBDTable
	 */
	private $transMapper;
	
	/**
	 * @var array
	 */
	private $statusToAction;
	
	/**
	 * @var array
	 */
	private $translator;
	
	/**
	 * @var CUser
	 */
	private $user;
	
	/**
	 * @var unknown
	 */
	private $db;
	
	private static $actionToTransDescription = array(
		self::ACTION_SUPPLIER => \LinemediaAutoTransactionTitle::DEPOSIT_REFUSED_BY_SUPPLIER,
	    self::ACTION_SHIPMENT => \LinemediaAutoTransactionTitle::DEPOSIT_REFUSED_IN_SHIPMENT,
		self::ACTION_COMMODITY_RETURN => \LinemediaAutoTransactionTitle::DEPOSIT_RETURN_COMMODITY
	);
	
	/**
	 * @param \LinemediaAutoTransactionBDTable $transaction
	 * @param array $basketsID
	 * @param string $status
	 * @param array $statusToAction
	 * @param CUser $userInstance
	 * @param $dbInstance
	 * @return void
	 */
	public function __construct(\LinemediaAutoTransactionBDTable $transMapper, $userInstance, $dbInstance, $status, array $basketsID, array $statusToAction) {
		
		$this->transMapper = $transMapper;
		$this->basketsID = $basketsID;
		$this->status = $status;
		$this->statusToAction = $statusToAction;
		$this->translator = \LinemediaAutoTransactionTitle::transateFromEngToRus();
		$this->user = $userInstance;
		$this->db = $dbInstance;
		
	}
	
	/**
	 * get instance of LinemediaAutoTransactionBDTable
	 * @return LinemediaAutoTransactionBDTable
	 */
	public function getTransactionEntity() {
		return $this->transMapper;
	}
	
	/**
	 * create transaction if possible
	 * @return boolean
	 */
	public function createTransaction() {
		
		switch ($action = $this->statusToAction[$this->status]) {
			
			case self::ACTION_RESERVE:
				return $this->createReserve();
			case self::ACTION_SHIPMENT:
			case self::ACTION_COMMODITY_RETURN:
			case self::ACTION_SUPPLIER:
				return $this->createDepositingFundsOnAccount($action);			
		}
	}
	

	public function getInformativeMessage() {
		return $this->informativeMessage;
	}
	
	
	/**
	 * writing off funds of customer , negative transaction. When order is transfered to this status, funds is written off,
	 * until funds arent exhausted
     * @return boolean
	 */
	private function createReserve() {
		
		$cost = 0;
		$totalCost = 0;
		$director = new LinemediaAutoBranchesDirector($this->user->GetID());
		$directorGroup = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR');
		
		//whether current user belong to directorgroup
		if (!in_array($directorGroup, $this->user->GetUserGroupArray())) {
			return false;
		}
		
		//whether delay is up-to-date
		if ($director->getCurrrentDelay() < 0) {
			return false;
		}
		
		while (($id = current($this->basketsID)) != null) {
		
			$basketsParams = array();
			$fetchedBasket = CSaleBasket::GetByID($id);
			$cost = $fetchedBasket['PRICE'] * $fetchedBasket['QUANTITY'];
		
			if ($director->getCurrentLimit() < $cost) {
				return false;
			}
			
			$basketsParams[] = array(
					'BASKET_ID' => current($this->basketsID),
					'DIRECTOR_ID' => $this->user->GetID(), 
					'PRICE' => $cost,
					'DONE' => 'N', 
					'PAID' => 'N', 
					'ORDER_ID' => $fetchedBasket['ORDER_ID'], 
					'DATE_TO_DONE' => null, 
					'DATE_TO_PAID' => null, 
					'SUM_PAID' => 0
			);
		
			$transParams = array(
					'USER_ID' => $this->user->GetID(),
					'AMOUNT' => $cost,
					'CURRENCY' => 'RUB',
					'DEBIT' => 'N',
					'NOTES' => '',
					'ORDER_ID' =>  $fetchedBasket['ORDER_ID'],
					'EMPLOYEE_ID' => $this->user->GetID(),
					'TRANSACT_DATE' => date($this->db->DateFormatToPHP(\CLang::GetDateFormat('FULL'))),
					'DESCRIPTION' => \LinemediaAutoTransactionTitle::COMMODITY_IN_RESERVE
			);
			
			$dbParams['transaction'] = $transParams;
			$dbParams['baskets'] = $basketsParams;
			$totalCost += $cost;
			\LinemediaAutoTransactionAuxiliaryClass::transferBasketToStatus($id, $this->status);
			$this->transMapper->create($dbParams);
			next($this->basketsID);
		
		}
		
		\LinemediaAutoTransactionAuxiliaryClass::updateUserAccount($this->user->GetID(), -$totalCost, 'RUB');
		
		return true;
		
	}	
	
	/**
	 * positive transaction. Return funds back to account
	 * @param string $transTitle
	 * @return void
	 */
	private function createDepositingFundsOnAccount($transTitle) {

 // 	$director = new \LinemediaAutoBranchesDirector($this->user->GetID());		
	    $translator = \LinemediaAutoTransactionTitle::transateFromEngToRus();

		while (($id = current($this->basketsID)) != null) {
		
			$fetched = $this->transMapper->findByID($id)->GetNext();
			$mixedTransBasket = \LinemediaAutoTransactionAuxiliaryClass::extractTransactionWithProperBasket($fetched['ID_BITRIX_TRANSACTION'], $this);
			$trans = $mixedTransBasket['transaction'];
			$basket = $mixedTransBasket['basket'];
		    $returningFunds = (int) $basket['SUM_PAID'] ? $basket['SUM_PAID'] : $basket['PRICE'];
		    $trans['description'] = $translator[self::$actionToTransDescription[$transTitle]];
		    
		    //update both transaction and basket
			\LinemediaAutoTransactionAuxiliaryClass::updateEntities(
					$id,
					$trans,
					$this,
					$fetched['DIRECTOR_ID'],
					$returningFunds
			);
			next($this->basketsID);
		
		}

	}
	
	
	/**
	 * seek for the most ancient transaction kind of either .... or ....
     * put on payment.If transaction is paid partially point it in summary if quite do same plus vary its status to
     * ChangeWriteOffReservePaid or ChangeWriteOffReserveClosedPaid accordingly
     * If deposit was greater than found unpaid transaction iterate algorithm dropping off amount by amount of unpaid transaction
     * @param int $userID
     * @param float $funds
     * @return boolean
	 */
	public static function depositFunds($userID, $funds) {
		
		$transactions =  \LinemediaAutoTransactionAuxiliaryClass::getAppropriateTransactions($userID);
		$transMapper = new \LinemediaAutoTransactionBDTable();
		
		//loop through set of transactions
		while (current($transactions) != null && $funds > 0) {
			$transaction = current($transactions);
			list($funds, $basketID) = \LinemediaAutoTransactionAuxiliaryClass::fillBasketByIncomingFunds($funds, $transMapper, $transaction['ID']);
			\LinemediaAutoTransactionAuxiliaryClass::trasnferTransactionToStatus($transaction['ID'], $transMapper);
			next($transactions);
		}
		
	}

}


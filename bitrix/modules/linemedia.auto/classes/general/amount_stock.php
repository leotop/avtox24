<?php
/**
 * Linemedia Autoportal
 * Main module
 *
 * @author  Linemedia
 * @since   26/02/2014
 *
 * @link    http://auto.linemedia.ru/
 */

namespace Linemedia\Auto\Amountstock;

use CSaleBasket;
use COption;
use LinemediaAutoOrder;
use LinemediaAutoDatabase;
use Exception;

IncludeModuleLangFile(__FILE__);


/**
 * class for processing events (OnBeforeBasketItemStatus, OnBeforeBasketItemCancel)
 * @author Sonny
 */
class LinemediaAutoVaryAmountGoodsInDatabase
{

	const INCREASE = 1;
	const DECREASE = 2;

	/**
	 * array of accordance between string and anonymous function
	 * @var arrray
	 */
	private $closure = array();

	/**
	 * constructor
	 */
	public function __construct() {

	    $this->closure = array(
	        self::INCREASE => function ($x, $y) { return $x + $y; },
	        self::DECREASE => function ($x, $y) { return $x - $y; }
	    );
	}


	/**
	 * extract properties in depending on what string was conveyed in array
	 * @param int $cartId
	 * @param array $arrayProps
	 * @return array
	 */
	public static function getAppropriateProps($cartId, $arrayProps) {
	    
		$props = array();
		$incomeProps = new \ArrayIterator($arrayProps);
		
		$unwroughtProps = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $cartId));
		
		while ($prop = $unwroughtProps->Fetch()) {

			if (strcmp($prop['CODE'], $incomeProps->current()) == 0) {
				array_push($props, $prop['VALUE']);

				if (!$incomeProps->valid()) {
					break;
				}
				
				$incomeProps->next();
			}
		}
		
		return $props;
	}


	/**
	 * get statuses set in settings
	 * @return array
	 */
    public static function getDecreasingStatuses() {        
    	return (array) unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING', ''));
    }

	/**
	 * whether order is locked or not
	 * @param int $orderId
	 * @return boolean
	 */
	public static function statusIsLocked($cartId, $incomingStatus) {

		$statusesGroupSet = current(
		    array(
		        array(
		            self::getDecreasingStatuses(), array_diff(array_keys(LinemediaAutoOrder::getStatusesList()), self::getDecreasingStatuses())
		        )
		    )
	    );
		
		list($status) = self::getAppropriateProps($cartId, array('status'));
        $leftStatuses = array_diff(array_keys(LinemediaAutoOrder::getStatusesList()), self::getDecreasingStatuses());
		
		if ($status == $incomingStatus) {
		    return TRUE;
		}
		elseif (in_array($status, self::getDecreasingStatuses()) && in_array($incomingStatus, self::getDecreasingStatuses())) {
		    return TRUE;
		}
		elseif (in_array($status, $leftStatuses) && in_array($incomingStatus, $leftStatuses)) {
		    return TRUE;
		}
		
		return FALSE;
		
	//	$statusesGroup = array_merge($statusesGroupSet[1], $statusesGroupSet[0]);
		/*
		while (current($statusesGroup) != null) {

			if (in_array($incomingStatus, current($statusesGroup)) && in_array($status, current($statusesGroup))) {
				return true;
			}

			next($statusesGroup);
		}

		return false;
		*/
	}


	/**
	 * loop through each cart and in depending on amount of commodity and type of closure,
	 * increase or decrease amount in stock
	 * @param int $orderId
	 * @param anonymous function $closure
	 */
	public function execute($cartId, $appelletion) {

	    
		$database = new LinemediaAutoDatabase();
		$cart = CSaleBasket::GetByID($cartId);

		@set_time_limit(0);
		$database->StartTransaction();

		$quantity = $database->Query('SELECT quantity FROM `b_lm_products` WHERE `id` = '. (int) $cart['PRODUCT_ID'])->Fetch();
		
		if ($quantity != false) {

		    $closure = $this->closure[$appelletion];
		    $dropoffQuantity = $closure((float) $quantity['quantity'], (float) $cart['QUANTITY']);
		    
		    $outcome = $database->Query('UPDATE `b_lm_products` SET quantity = '. $dropoffQuantity .' WHERE `id` = '. (int) $cart['PRODUCT_ID'] .' LIMIT 1;');

		    if ($outcome) {
		        $database->Commit();
		    }
		    else{
		        $database->Rollback();
		    }
		}
		else {
		    throw new Exception(str_replace('#ID#', $cart['PRODUCT_ID'], GetMessage('LM_AUTO_SUPPLIER_PRODUCT_NOT_FOUND')));
		}


	}
}








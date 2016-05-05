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
use ArrayIterator;
use COption;
use LinemediaAutoOrder;
use LinemediaAutoDatabase;

IncludeModuleLangFile(__FILE__);


/**
 * class for processing events (OnBeforeBasketItemStatus, OnBeforeBasketItemCancel)
 * @author Sonny
 */
class IncreaseDecreaseAmountInStock
{

	const INCREASE = 1;
	const DECREASE = 2;

	/**
	 * array of accordance between string and anonymous function
	 * @var arrray
	 */
	private $closure = array();

	/**
	 * extract properties in depending on what string was conveyed in array
	 * @param int $cartId
	 * @param array $arrayProps
	 * @return array
	 */
	public static function getAppropriateProps($cartId, ArrayIterator $arrayProps) {

		$props = array();
		$unwroughtProps = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $cartId));

		while ($prop = $unwroughtProps->Fetch()) {

			if (strcmp($prop['CODE'], $arrayProps->current()) == 0) {
				array_push($props, $prop['VALUE']);

				if ($arrayProps->next() && $arrayProps->valid()) {
					break;
				}
				else {
					continue;
				}
			}
		}

		return $props;
	}


	/**
	 * get statuses set in settings
	 * @return array
	 */
    public static function getDecreasingStatuses() {
    	return unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING', ''));
    }

	/**
	 * whether order is locked or not
	 * @param int $orderId
	 * @return boolean
	 */
	public static function statusIsLocked($cartId, $incomingStatus) {

		$statusesGroup = new ArrayIterator(array(self::getDecreasingStatuses(), array_diff(array_keys(LinemediaAutoOrder::getStatusesList()), self::getDecreasingStatuses())));
		list($status) = self::getAppropriateProps($cartId, new ArrayIterator(array('status')));

		while ($statusesGroup->valid()) {

			if (in_array($incomingStatus, $statusesGroup->current()) && in_array($status, $statusesGroup->current())) {
				return true;
			}

			$statusesGroup->next();
		}

		return false;
	}

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

		$quantity = $database->Query('SELECT quantity FROM `b_lm_products` WHERE `id` = '. $cart['PRODUCT_ID'])->Fetch();
		$dropoffQuantity = $this->closure[$appelletion]($quantity['quantity'], $cart['QUANTITY']);
		$outcome = $database->Query('UPDATE `b_lm_products` SET quantity = '. $dropoffQuantity .' WHERE `id` = '. $cart['PRODUCT_ID'] .' LIMIT 1;');

		if ($outcome) {
			$database->Commit();
		}
		else{
			$database->Rollback();
		}

	}
}








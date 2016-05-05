<?php

/**
 * Linemedia API
 * API module
 * Product class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
* Основной класс модуля
*/
class CAPILinemediaAutoBasket extends CAPIFrame
{
    public static $BUY_FROM_HASH_TTL = 3600;

	public function __construct()
	{
		parent::__construct();
	}

    /**
     * смена статуса заказа
     * @param $basket_id
     * @param $newStatus
     * @return bool
     */
    public function LinemediaAutoBasket_statusItem($basket_id, $newStatus)
    {
    	/*
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);
		if (!CModule::includeModule('linemedia.auto'))
            return false;
        $basket = new LinemediaAutoBasket();
        return $basket->statusItem($basket_id, $newStatus);
    }

    /**
     * Добавление в корзину
     * @param $buy_hash
     * @param int $quantity
     * @param null $price
     * @param $user_id
     * @return bool
     */
    public function LinemediaAutoBasket_addItem($buy_hash, $quantity, $price = null, $user_id = null) {

        /*
        * Проверка прав доступа к функции
        */
        $this->checkPermission(__METHOD__);
        if (!CModule::includeModule('linemedia.auto'))
            return false;

        $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/buy_from_api/'));

        if(empty($buy_hash)) return false;
        if(intval($quantity) < 1) $quantity = 1;

        if($part = $lmCache->getData($buy_hash, self::$BUY_FROM_HASH_TTL)) {

            $basket = new LinemediaAutoBasket($user_id);

            $supplier_id = $part['supplier_id'];
            $additional = array(
                'article'       => $part['article'],
                'brand_title'   => $part['brand_title'],
                'extra'         => $part['extra'],
                'max_available_quantity' => $part['quantity'],
            );

            if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
                // торговая цепочка и полная информация о детали из сессии
                if(!isset($part['chain'])) {
                    throw new Exception('No part chain');
                }
                $chain = $part['chain'];
                $additional = $chain['part'] + $additional; // порядок важен
                $supplier_id = $chain['part']['supplier_id'];
            }

            return $basket->addItem($part['id'], $supplier_id, $quantity, $price, $additional);

        } else {
            return false;
        }
     }
}

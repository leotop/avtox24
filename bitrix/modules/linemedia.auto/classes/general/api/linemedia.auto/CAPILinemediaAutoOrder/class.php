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
class CAPILinemediaAutoOrder extends CAPIFrame
{

	public function __construct()
	{
		parent::__construct();
	}


    /**
     * @param array $order_fields
     * @param array $basket_ids
     * @return bool|int|void
     */
    public function LinemediaAutoOrder_makeOrder($order_fields) {

        global $USER;

        if(!CModule::IncludeModule('linemedia:api')) {
            return $this->error('Modile linemedia.api not installed');
        }

        if(!is_array($order_fields) || count($order_fields) < 1) {
            return $this->error('Order fields is empty');
        }

        $user_id = (int) $order_fields['USER_ID'];
        if($user_id < 1) {
            $user_id = $USER->GetID();
        }

        $basket_obj = new LinemediaAutoBasket($user_id);
        $fuser_id = $basket_obj->getFuserId();

        if(!$fuser_id) {
            return $this->error('SaleUser is undefined');
        }

        $site_id = $order_fields['SITE_ID'];
        if(!$site_id) {
            return $this->error('SITE_ID is undefined');
        }

        $res = CSaleBasket::GetList(
            array("PRICE" => "DESC"),
            array("FUSER_ID" => $fuser_id, "LID" => $site_id, "ORDER_ID" => 0),
            false, false,
            array('ID')
        );
        if(!($basket = $res->Fetch())) {
            return $this->error('User has no baskets');
        }

        $sale_order = new CAPISaleOrder();

        if($order_id = $sale_order->Order_Add($order_fields)) {

            if(CSaleBasket::OrderBasket($order_id, $fuser_id, $site_id, false)) {
                return $order_id;
            } else {
                $this->error('Basket order error');
            }

        } else {

            return $this->error('Order create error');
        }
    }
}

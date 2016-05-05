<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/**
 * Linemedia Autoportal
 * Main module
 * Module events for sale
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/**
 * use as alias in OnBeforeBasketItemStatus_AmountInStock,
 * OnBeforeBasketItemCancel_ReIncreaseAmountInStock
 */
IncludeModuleLangFile(__FILE__);
 
use Exception;

class LinemediaAutoEventSale
{
    /**
     * При добавлении товара необходимо уменьщить количество деталей в базе
     * TODO: а также проверить их доступность
     */
    function OnOrderAdd_DescreasePartsCount($ID, $arFields)
    {
        /*
         * В настройках модуля может быть отключено уменьшение количества деталей
         */
        $decrease_quantity = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING') == 'Y';
        if (!$decrease_quantity) {
            return;
        }

        /*
         * Получим список корзин
         */
        $order = new LinemediaAutoOrder($ID);
        $baskets = $order->getBaskets();

        $search = new LinemediaAutoSearch();
        foreach ($baskets as $basket) {
            /*
             * Найдём запчасть из корзины
             */
            $search_part = array(
                'article'       => $basket['PROPS']['article']['VALUE'],
                'brand_title'   => $basket['PROPS']['brand_title']['VALUE'],
                'supplier_id'   => $basket['PROPS']['supplier_id']['VALUE']
            );
            $part_data = $search->getLocalDatabaseArticle($search_part);

            LinemediaAutoDebug::add('Decrease part count', print_r($part_data, 1));

            /*
             * Уменьшим её количество
             */
            $part = new LinemediaAutoPart($part_data['id']);
            $part->setQuantity($part_data['quantity'] - $basket['QUANTITY']);
        }
    }

    /**
     * Проверка на удаление корзины при обмене с 1C.
     */
    function OnBeforeBasketDelete_checkBasket1CExchange($ID)
    {
        if (strpos($_SERVER['PHP_SELF'], '/bitrix/admin/1c_exchange.php') !== false) {
            return false;
        }
    }
    
    
    function OnBasketDelete_Cleanup($basket_id)
    {
	    $prop_obj = new LinemediaAutoBasketProperty();
        $prop_obj->deleteById($basket_id);
    }


    /**
     * Автоперевод пользователя в группы.
     */
    function OnSalePayOrder_checkUserGroups($ID, $pay)
    {
        $order = CSaleOrder::GetByID($ID);

        // Переводить ли пользователя в группы при отмене оплаты.
        $goback = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GROUP_TRANSFER_BACK', 'N');

        if ($goback != 'N' || $pay != 'N') {
            //return;


            try {
                $transfer = new LinemediaAutoGroupTransfer($order['USER_ID']);
                $groups = $transfer->getUserGroups();

                $user = new CUser();
                $user->Update($order['USER_ID'], array('GROUP_ID' => $groups));

            } catch (Exception $e) {
                // nothing...
            }
        }
    }


    /**
     * Изменение статуса заказа.
     */
    function OnSaleStatusOrder_updateBasketStatuses($ID, $val)
    { 
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt', '9999');  
        if (!isset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET']) || $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET'] != true) {
            $basket = new LinemediaAutoBasket();
            $order  = new LinemediaAutoOrder($ID);

            $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER'] = true;
            // Проверка статусов корзин.
            $arBaskets = $order->getBaskets();
            foreach ($arBaskets as $arBasket) {
                $basket->statusItem($arBasket['ID'], $val);
            }
            unset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER']);
        }
    }


    /**
     * Изменение статуса "оплачен" в заказах linemdia при оплате в магазин -> заказы.
     */
    public function OnSalePayOrder_SetPayBaskets($ID, $val)
    {
        $order    = new LinemediaAutoOrder($ID);
        $lmbasket = new LinemediaAutoBasket();

        // Список корзин заказа.
        $baskets = $order->getBaskets();

        foreach ($baskets as $key => $basket) {
            $lmbasket->payItem($basket['ID'], $val);
        }
    }


    /**
     * Изменение статуса "отменен" в заказах linemdia при отмене в магазин -> заказы.
     */
    public function OnSaleCancelOrder_SetCancelBaskets($ID, $val)
    {
        $order    = new LinemediaAutoOrder($ID);
        $lmbasket = new LinemediaAutoBasket();

        // Список корзин заказа.
        $baskets = $order->getBaskets();

        foreach ($baskets as $key => $basket) {
            $lmbasket->cancelItem($basket['ID'], $val);
        }
    }

    /**
     * on being varyied an order to given status amount of commodity in stock is decreased by value of each cart in order
     * @param int $orderId
     * @param letter $status
     */
    public function OnBeforeBasketItemStatus_AmountInStock($cartId, $incomingStatus) {
         
    	$obj = new Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase();
    	list($cancel) = Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getAppropriateProps($cartId, array('canceled'));

    	
    	if (strcmp($cancel, 'N') == 0) {

            $arr = Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getDecreasingStatuses();
            $var1 = Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::statusIsLocked($cartId, $incomingStatus);
         
            
    		if (!Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::statusIsLocked($cartId, $incomingStatus) && in_array($incomingStatus, $arr)) {

    		    try {
    		        $obj->execute($cartId, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::DECREASE);
    		    }
    		    catch(Exception $ex) {
    		        ShowNote($ex->getMessage());
                    //throw new Exception($ex->getMessage());
    		    }
    		}
    		elseif (!Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::statusIsLocked($cartId, $incomingStatus) && !in_array($incomingStatus, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getDecreasingStatuses())) {

    		    try {
    		        $obj->execute($cartId, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::INCREASE);
    		    }
    		    catch(Exception $ex) {
                    ShowNote($ex->getMessage());
                    //throw new Exception($ex->getMessage());
    		    }
    		}

    	}

    }


    /**
     *  during repeated restoraton of order amount of detail is being decreased iteratively
     * @param int $orderId
     * @param letter $flag
     */
    public function OnBeforeBasketItemCancel_ReIncreaseAmountInStock($cartId, $incomingCancel) {
        
    	$obj = new Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase();    	
    	list($cancel, $status) = Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getAppropriateProps($cartId, array('canceled', 'status'));
    	
        if (
            strcmp($incomingCancel, 'Y') == 0 &&
            strcmp($cancel, $incomingCancel) != 0 &&
            in_array($status, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getDecreasingStatuses())
    	) {

            try {
                $obj->execute($cartId, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::INCREASE);
            }
            catch(Exception $ex) {
                ShowError($ex->getMessage());
            }
        }
        elseif (
            strcmp($incomingCancel, 'N') == 0 &&
            strcmp($cancel, $incomingCancel) != 0 && 
            in_array($status, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getDecreasingStatuses())
    	) {

            try {
                $obj->execute($cartId, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::DECREASE);
            }
            catch(Exception $ex) {
                ShowError($ex->getMessage());
            }
        }

    }
    
    public function OnBasketOrder_AmountInStock($id) {
        
        $order = CSaleOrder::GetByID($id);
        
        if (!in_array($order['STATUS_ID'], Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getDecreasingStatuses())) {
            return;
        }
        
        $b = array();
        $obj = new Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase();
        $baskets = CSaleBasket::GetList(array(), array("ORDER_ID" => $id));
        
        while ($r = $baskets->Fetch()) {
            
            try {
                
                $obj->execute($r['ID'], Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::DECREASE);
            }
            catch(Exception $ex) {
                ShowError($ex->getMessage());
            }
        }     
    }

    public function OnBasketOrder_CreateTransaction($id) {

        global $APPLICATION, $USER;

        $transaction = new LinemediaAutoTransaction();

        /*
         * Получим список корзин
         */
        $order = new LinemediaAutoOrder($id);
        $baskets = $order->getBaskets();

        $basket = new LinemediaAutoBasket($USER->GetID());

        foreach ($baskets as $basket_item) {

            $status = $basket_item['PROPS']['status']['VALUE'];
            $isSuccess = $transaction->createTransaction($basket_item['ID'], $status, $basket);
            if(!$isSuccess) {
                $APPLICATION->ThrowException($transaction->getInformativeMessage());
            }
        }
    }

    public function OnBeforeOrderDelete_AmountInStock($id, $success) {
               
        $obj = new Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase();
        $baskets = CSaleBasket::GetList(array(), array("ORDER_ID" => $id));
        
        while ($r = $baskets->Fetch()) {
            
            list($canceled, $status) = Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getAppropriateProps($r['ID'], array('canceled', 'status'));
            
            try {
                
                if (in_array($status, Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::getDecreasingStatuses()) && strcmp($canceled, 'N') == 0) {
                    $obj->execute($r['ID'], Linemedia\Auto\Amountstock\LinemediaAutoVaryAmountGoodsInDatabase::INCREASE);
                }
            }
            catch(Exception $ex) {
                ShowError($ex->getMessage());
            }
        }
    }
    
    
    public function OnBeforeBasketAdd_CartModifyByModificator(&$cart) {
    
        if (isset($_SESSION['PRICE_MODIFY'])) {
            $cart['PRICE'] = $_SESSION['PRICE_MODIFY'];
            unset($_SESSION['PRICE_MODIFY']);
        }
        elseif (isset($_SESSION['title'])) {
            $cart['NAME'] = $_SESSION['title'];
            unset($_SESSION['title']);
        }
        elseif (isset($_SESSION['article']) || isset($_SESSION['original_article'])) {
            foreach ($cart['PROPS'] as &$prop) {
                if (strcmp($prop['CODE'], 'article') == 0) {
                    $prop['VALUE'] = $_SESSION['article'];
                    unset($_SESSION['article']);
                }
            }
        }
        elseif (isset($_SESSION['brand_title'])) {
            foreach ($cart['PROPS'] as &$prop) {
                if (strcmp($prop['CODE'], 'brand_title') == 0) {
                    $prop['VALUE'] = $_SESSION['brand_title'];
                    unset($_SESSION['brand_title']);
                }
            }
        }
    }

    /**
     * payment of most obsolete orders  
     * @param int $orderID
     * @param string $flag
     */
    public function OnSalePayOrder_PayMostObsoleteOrder($orderID, $flag)
    {
    	/*if ($flag == 'Y') {
    		
    		$transMapper = new \LinemediaAutoTransactionBDTable();
    		$transaction = $transMapper->findByConditions(array(
    			'ORDER_ID' => $orderID
    		));
    		
    		while ($o = $transaction->Fetch()) {

    			$transMapper->update(
    					$o['BASKET_ID'],
    					array(
    							'PAID' => 'Y'
    					)
    			);
    			
    			\LinemediaAutoTransactionAuxiliaryClass::trasnferTransactionToStatus(
    					$o['ID_BITRIX_TRANSACTION'],
    					$transMapper
    			);
    			
    		}
    	}*/
    }

	/**
	 * OnPersonTypeAdd_AddNewPropsToCustomPersonType
	 *
	 * @param int $personTypeId
	 * @param array $personProps
	 * @return bool
	 */
	public function OnPersonTypeAdd_AddNewPropsToCustomPersonType($personTypeId, $personProps) {

		include  $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/install/sale/props.php';
		CModule::IncludeModule('sale');

		$typeGroup = 0;

		$group = CSaleOrderPropsGroup::GetList(array(), array('PERSON_TYPE_ID' => $personTypeId, 'NAME' => GetMessage('LM_AUTO_SALE_PROPS_GROUP')), false, false, array('ID'))->Fetch();

		if ($group['ID'] <= 0) {

			$group_id = CSaleOrderPropsGroup::Add(array('NAME' => GetMessage('LM_AUTO_SALE_PROPS_GROUP'), 'PERSON_TYPE_ID' => $personTypeId));
			$typeGroup = $group_id;
		}
		else {
			$typeGroup = $group['ID'];
		}


		foreach ($personProps as $prop) {

			$prop['PERSON_TYPE_ID'] = $personTypeId;
			$prop['PROPS_GROUP_ID'] = $typeGroup;

			$code = CSaleOrderProps::Add($prop);

			if ($code <= 0) {
				return false;
			}
		}

		return true;

	}

    public function OnAfterUserAccountAdd_ExtendTransaction($id, $fields) {

        $account = CSaleUserAccount::GetByID($id);

        $transactions = LinemediaAutoTransaction::getLastBxTransaction($account['USER_ID']);

        $lm_transaction = new LinemediaAutoTransaction();

        foreach($transactions as $trans) {
            $lm_transaction->extendTransaction($trans);
        }
    }

    public function OnAfterUserAccountUpdate_ExtendTransaction($id, $fields) {

        $account = CSaleUserAccount::GetByID($id);

        $transactions = LinemediaAutoTransaction::getLastBxTransaction($account['USER_ID']);

        $lm_transaction = new LinemediaAutoTransaction();

        foreach($transactions as $trans) {
            $lm_transaction->extendTransaction($trans);
        }
    }
}


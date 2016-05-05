<?php

/**
 * Linemedia Autoportal
 * Main module
 * Basket management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/*
 * �����-������� ��� ������ � ��������.
 */
class LinemediaAutoBasket
{
    protected $USER;
    protected $fuser_id = null;
    
    
    /**
     * ������� � ������������ ������ ������������
     */
    public function __construct($user_id = null)
    {
        if (is_null($user_id)) {
            global $USER;
            $user_id = $USER->GetID();
        }

        $this->USER = CUser::getByID(intval($user_id))->Fetch();

        CModule::IncludeModule('sale');
    }


    /**
     * ��������� ������ �������.
     */
    public function getData($id)
    {
        return CSaleBasket::getByID(intval($id));
    }


    /**
     * ��������� ��������� FUSER_ID.
     */
    public function getFuserId()
    {
        global $USER;

        // ivan 22.01.15
        //���� �� �������� � �������� �� �������� ������������, � �������������
        if (is_null($this->fuser_id)) {

            if(is_array($this->USER) && intval($this->USER['ID']) > 0 && $this->USER['ID'] != $USER->GetID()) {
                $sale_user = CSaleUser::GetList(array('USER_ID' => $this->USER['ID']));
                if(!$sale_user['ID']) {
                    $sale_user['ID'] = CSaleUser::_Add(array("USER_ID" => $this->USER['ID']));
                }
                $this->fuser_id = $sale_user['ID'];

            } else {
                $this->fuser_id = CSaleBasket::GetBasketUserID();
            }
        }
        return $this->fuser_id;
    }


    /**
     * ��������� ������� �������.
     */
    public static function getProps($basket_id)
    {
        CModule::IncludeModule('sale');

        $dbprops = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => intval($basket_id)), false, false, array());
        $props = array();
        while ($prop = $dbprops->Fetch()) {
            $props[$prop['CODE']] = $prop;
        }
        
        
        $prop_obj = new LinemediaAutoBasketProperty();
        $dbprops = $prop_obj->getByBasketId($basket_id);
        while ($prop = $dbprops->Fetch()) {
            $props[$prop['CODE']] = $prop;
        }
        
        
        return $props;
    }


    /**
     * ��������� �������� �������� �������.
     *
     * @param int $basket_id - ID �������
     * @param array $properties - ������ ������� ��� ��������� (��������� ���������� Bitrix)
     */
    public static function setProperty($basket_id, $properties)
    {
        $props = self::getProps($basket_id);

        foreach ($props as $code => $prop) {
            unset($props[$code]['ID']);
            unset($props[$code]['BASKET_ID']);
        }

        foreach ($properties as $property) {
            $props[$property['CODE']] = $property;
            
            if(strlen($property['VALUE']) >= 255) {
	        	$property['BASKET_ID'] = $basket_id;
		        $prop_obj = new LinemediaAutoBasketProperty();
		        $prop_obj->Add($property);
	        }
            
        }
        return CSaleBasket::Update($basket_id, array('PROPS' => $props));
    }
    
    
    
    /**
     * �������� �������� �������.
     *
     * @param int $basket_id - ID �������
     * @param array $code - ��� ��������
     */
    public static function removeProperty($basket_id, $remove_code)
    {
        $props = self::getProps($basket_id);

        foreach ($props as $code => $prop) {
            unset($props[$code]['ID']);
            unset($props[$code]['BASKET_ID']);
        }
        unset($props[$remove_code]);
        
        $prop_obj = new LinemediaAutoBasketProperty();
		$prop_obj->deleteByBasketIdAndCode($basket_id, $remove_code);
	    
        return CSaleBasket::Update($basket_id, array('PROPS' => $props));
    }


    /**
     * ��������� ������ ������.
     */
    public function payItem($basket_id, $payed = 'Y')
    {
        $arProps = array();

        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemPay");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$payed));
        }

        // ���� ������.
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
            "CODE" => "payed",
            "VALUE" => (string) $payed
        );

        // ���� ������ ������
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
            "CODE" => "payed_date",
            "VALUE" => date('d.m.Y H:i:s')
        );

        // ��� �������� ������
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
            "CODE" => "emp_payed_id",
            "VALUE" => $this->USER['ID']
        );

        self::setProperty($basket_id, $arProps);

        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemPay");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$payed));
        }
    }


    /**
     * ��������� ������ ������.
     */
    public function cancelItem($basket_id, $canceled = 'Y', $description = "")
    {
        $arProps = array();

        /*
         * �������� �������� �� ��������� ������, ���� ��� - ������� �������, ����� �� ������� ������ ��� �������, � �� ������� �����������
         */
        $props = self::getProps($basket_id);
        foreach($props as $prop) {
            if($prop['CODE'] == 'canceled' && $prop['VALUE'] == $canceled) {
                return;
            }
        }

        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemCancel");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$canceled));
        }
        
        
        // ����� �������� ��������� "C����� ����������� ������"
        $cancelStatus = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CANCEL_STATUS_ID');
        // ���� ������ �������� �� �� ����� �������
        if(!empty($cancelStatus) && !defined('SET_CANCEL_ON_STATUS')) {
            define('SET_STATUS_ON_CANCEL', true);
        	$this->statusItem($basket_id, $cancelStatus);
        }
        
        
        
        // ������ ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
            "CODE" => "canceled",
            "VALUE" => (string) $canceled
        );

        // ���� ������ ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
            "CODE" => "canceled_date",
            "VALUE" => date('d.m.Y H:i:s')
        );

        // ��� ������� �����
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
            "CODE" => "emp_canceled_id",
            "VALUE" => $this->USER['ID']
        );

        self::setProperty($basket_id, $arProps);

        if(!empty($description)) {

            self::setComment($basket_id, $description, GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DESCR'));
        }

        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemCancel");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$canceled, &$description));
        }
    }

    /*
     * �������� ����������� ������ ������ ��� ������� � ��������� �����������
     */
    public static function isClientCancelEnabled($status) {

        $arCancelableStatuses = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST'));

        if(is_array($arCancelableStatuses) && strlen($status) > 0) {
            return in_array($status, $arCancelableStatuses);
        }
        return false;
    }

    /*
     * ��������� ���� �� �������� �������
     */
    public static function isCanceled($basket_id) {

        $props = self::getProps($basket_id);

        foreach($props as $prop) {
            if($prop['CODE'] == 'canceled' && $prop['VALUE'] == 'Y') {
                return true;
            }
        }

        return false;
    }

    /**
     * ��������� �������.
     */
    public function statusItem($basket_id, $status, $description = "")
    {
        $arProps = array();

        /*
         * ������ ������� "��������� ������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemStatus");
        $break = false; 
        while ($arEvent = $events->Fetch()) {
            $res = ExecuteModuleEventEx($arEvent, array(&$basket_id, &$status, &$this));
            if($res === false) $break = true;
        }

        if($break) return false;

        // ������ ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
            "CODE" => "status",
            "VALUE" => (string) $status
        );

        // ���� ��������� �������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
            "CODE" => "date_status",
            "VALUE" => date('d.m.Y H:i:s')
        );

        // ��� ������� ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
            "CODE" => "emp_status_id",
            "VALUE" => $this->USER['ID']
        );

        $ret = self::setProperty($basket_id, $arProps);

        if(!empty($description)) {

            self::setComment($basket_id, $description, GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DESCR'));
        }

        // ����� �������� ��������� "����������� ������ ��� �������� � ���� ������"
        $cancelStatus = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CANCEL_STATUS_ID');
        $cancelOnStatus = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CANCEL_ON_STATUS', 'N');
        // ���� ����� ������� �������� �� � ������
        if($cancelStatus == $status && $cancelOnStatus == 'Y' && !defined('SET_STATUS_ON_CANCEL')) {
            define('SET_CANCEL_ON_STATUS', true);
            $this->cancelItem($basket_id);
        }

        /*
         * ������ ������� "��������� ������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemStatus");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$status));
        }
        return $ret;
    }


    /**
     * ��������� ��������.
     */
    public function deliveryItem($basket_id, $delivery)
    {
        $arProps = array();

        /*
         * ������ ������� "��������� �������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemDelivery");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$delivery));
        }

        // ����������� ��������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
            "CODE" => "delivery",
            "VALUE" => (string) $delivery
        );

        // ���� ��������� ������� ��������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
            "CODE" => "date_delivery",
            "VALUE" => date('d.m.Y H:i:s')
        );

        // ��� ������� ������ ��������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
            "CODE" => "emp_delivery_id",
            "VALUE" => $this->USER['ID']
        );

        self::setProperty($basket_id, $arProps);


        /*
         * ������ ������� "��������� �������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemDelivery");
        while ($arEvent = $events->Fetch()) {
            
           ExecuteModuleEventEx($arEvent, array(&$basket_id, &$delivery));
        }
    }


    /**
     * ���������� ������ � ������� ������������
     * supplier_id ����� ������� �� ID ��������, �� ���� ����������, �������� ������� �� �������� � ��
     * ��� ����������� � ���������� ����������
     */
    public function addItem($part_id, $supplier_id = null, $quantity = 1, $price = null, $additional = array())
    {

    	/*
    	 * ���� ��� �� ����� ��������� � �������.
    	 */
    	if (LinemediaAutoUserHelper::isSearchRobot()) {
    		CHTTP::SetStatus(404);
	    	exit;
    	}


        $arFields = array();

        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
        	try {
            	ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$arFields, &$additional));
            } catch (Exception $e) {
                LinemediaAutoDebug::add('Add to basket error ', $e->GetMessage(), LM_AUTO_DEBUG_WARNING);
	            return false;
            }
        }

        /*
         * ����.
         */
        $site_id = (!empty($additional['SITE_ID'])) ? (strval($additional['SITE_ID'])) : (SITE_ID);
        
        
        /*
        * �������������� ��������
        */
        $additional_props = (array) $additional['PROPS'];
        unset($additional['PROPS']);

        /*
         * ����� ��������
         */
        $part = new LinemediaAutoPart($part_id, $additional);
        

        /*
         * ����� ����������
         */
        $supplier = new LinemediaAutoSupplier($supplier_id);


        /*
         * �������� ����������
         */
        $quantity = $part->fixQuantity($quantity);


        /*
         * ��������� ����
         */
        $price_obj = new LinemediaAutoPrice($part);


        /*
         * ������� �����
         */
        $brand_title = $part->get('brand_title');


        /*
         * ���� ��������
         */
        $delivery_time  = (int) $additional['delivery_time'];
        
        if('Y' == COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N')) {
        	// already calculated in session
        } else {
	        $delivery_time += (int) $supplier->get('delivery_time');
        }
        

        /*
         * ���� � ������ ��������
         */
        $url = $additional;
        $url['article'] = $part->get('article');
        $part_path = LinemediaAutoUrlHelper::getPartUrl($url);


        if (!is_null($price)) {
            $price      = (float) $price;
            $currency   = CCurrency::GetBaseCurrency();
        } else {
            $price      = (float) $price_obj->calculate();
            $currency   = $price_obj->getCurrency();
        }
        /**
         * ���� �������� �������
         */
        $retail_chain = $price_obj->getRetailChain();
        
        if('Y' == COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N')) {
        	$retail_chain = $additional['retail_chain'];
        	if(isset($additional['delivery_time'])) {
        		$delivery_time = $additional['delivery_time'];
        	}
        	if(isset($additional['price'])) {
        		$price = $additional['price'];
        	}
        }

        $arFields = array_merge_recursive(
            array(
                "PRODUCT_ID"            => $part_id,
                "PRODUCT_XML_ID"        => $part_id,
                "FUSER_ID"              => $this->getFuserId(),
                "PRICE"                 => $price,
                "CURRENCY"              => $currency,
                "WEIGHT"                => $part->get('weight'),
                "QUANTITY"              => $quantity,
                "LID"                   => $site_id,
                "DELAY"                 => "N",
                "CAN_BUY"               => "Y",
                "NAME"                  => $brand_title . ' [' . $part->get('article') . '] ' . $part->get('title'),
                "MODULE"                => "linemedia.auto",
                "NOTES"                 => "",
                "DETAIL_PAGE_URL"       => $part_path,
            ),
            $arFields
        );

        $arProps = array();

        /*
         * ID ����������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
            "CODE" => "supplier_id",
            "VALUE" => $supplier_id
        );

        /*
         * �������� ����������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
            "CODE" => "supplier_title",
            "VALUE" => $supplier->get('visual_title')
        );

        /*
         * �������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
            "CODE" => "article",
            "VALUE" => $part->get('article')
        );
        
        /*
         * ������������ �������
         */
        $arProps[] = array(
			"NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ORIGINAL_ARTICLE'),
        	"CODE" => "original_article",
        	"VALUE" => $part->get('original_article')
        );

        /*
         * �������� �������������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
            "CODE" => "brand_title",
            "VALUE" => $brand_title
        );

        /*
         * ���������� ����
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
            "CODE" => "base_price",
            "VALUE" => $part->get('price')
        );

        /*
         * �������� �������
         */
        if(count($retail_chain) > 1) {

            $arProps[] = array(
                "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_RETAIL_CHAIN'),
                "CODE" => "retail_chain",
                "VALUE" => json_encode($retail_chain),
            );
        }

        /*
         * ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
            "CODE" => "payed",
            "VALUE" => 'N'
        );

        /*
         * ���� ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
            "CODE" => "payed_date",
            "VALUE" => ''
        );

        /*
         * ��� �������� ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
            "CODE" => "emp_payed_id",
            "VALUE" => ''
        );

        /*
         * ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
            "CODE" => "canceled",
            "VALUE" => 'N'
        );

        /*
         * ���� ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
            "CODE" => "canceled_date",
            "VALUE" => ''
        );

        /*
         * ��� ������� �����
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
            "CODE" => "emp_canceled_id",
            "VALUE" => ''
        );

        /*
         * ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
            "CODE" => "status",
            "VALUE" => 'N'
        );

        /*
         * ���� ��������� �������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
            "CODE" => "date_status",
            "VALUE" => date('d.m.Y H:i:s')
        );

        /*
         * ��� ������� ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
            "CODE" => "emp_status_id",
            "VALUE" => $this->USER['ID']
        );

        /*
         * ����������� ��������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
            "CODE" => "delivery",
            "VALUE" => 'N'
        );

        /*
         * ���� ��������� ������� ��������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
            "CODE" => "date_delivery",
            "VALUE" => ''
        );

        /*
         * ��� ������� ������ ��������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
            "CODE" => "emp_delivery_id",
            "VALUE" => ''
        );

        /*
         * time of delivery
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_TIME'),
            "CODE" => "delivery_time",
            "VALUE" => $delivery_time
        );

        /**
         * max acceptable commodities
         *
         */

        $arProps[] = array(
        		"NAME" => GetMessage('LM_AUTO_MAIN_BASKET_AVAILABLE_COMMODITY'),
        		"CODE" => "max_available_quantity",
        		"VALUE" => $additional['max_available_quantity']
        );
		
		$arProps[] = array(
			"NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PROPERTY_PART_TITLE'),
			"CODE" => "part_title",
			"VALUE" => $part->get('title'),                   
		);

        if(intval($part->get('multiplication_factor')) > 0) {
            $arProps[] = array(
                "NAME" => GetMessage('LM_AUTO_ORDER_MULTIPLICATION_FACTOR'),
                "CODE" => "multiplication_factor",
                "VALUE" => intval($part->get('multiplication_factor')),
            );
        }

        $arFields['PROPS'] = array_merge((array)$arFields['PROPS'], $arProps, $additional_props);
        
        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$arFields, &$additional));
        }


        $basket_id = CSaleBasket::Add($arFields);
        
        if(!$basket_id) {
	        global $APPLICATION;
	        $error = $APPLICATION->GetException();
	        ShowError($error->GetString());
        }
        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$basket_id, &$arFields));
        }
        
        
        /**
        * ������� �������� ���������� ���������
        */
        foreach($arFields['PROPS'] AS $prop) {
	        if(strlen($prop['VALUE']) >= 255) {
	        	$prop['BASKET_ID'] = $basket_id;
		        $prop_obj = new LinemediaAutoBasketProperty();
		        $prop_obj->Add($prop);
	        }
        }

        return $basket_id;
    }


    /**
     * ��������� ������ ������, ������� � ������.
     */
    public function getOrderedBaskets($ID = false)
    {
        $filter = array('ORDER_ID' => $ID);

        if (!$ID) {
            $filter['FUSER_ID'] = $this->getFuserId();
        }
        $dbbaskets = CSaleBasket::GetList(array(), $filter, false, false, array());
        $baskets = array();
        while ($basket = $dbbaskets->Fetch()) {
            $baskets[$basket['ID']] = $basket;
        }
        return $baskets;
    }


    /**
     * ����������� ���������� � �������.
     */
    public function fixQuantity($quantity, LinemediaAutoPart $part)
    {
        $quantity       = (int) $quantity;
        $partquantity   = (int) $part->get('quantity');

        if ($quantity <= 0) {
            $quantity = 1;
        }
        if ($quantity > $partquantity) {
            $quantity = $partquantity;
        }

        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBasketFixQuantity");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$quantity, &$part, &$this));
        }

        return $quantity;
    }

    public function setComment($basket_id, $text, $title = '') {

        $props = self::getProps($basket_id);

        $comment = '';

        $props = self::getProps($basket_id);
        foreach($props as $prop) {
            if($prop['CODE'] == 'comment') {
                $comment = $prop['VALUE'];
                if(strlen($comment) > 0) {
                    $comment .= "\r\n\r\n";
                }
            }
        }

        $comment .= ConvertTimestamp(false, 'FULL');
        if(!empty($title)) $comment .= ' ' . $title;
        $comment .= "\r\n" . $text;

        $arProps = array();

        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_COMMENT'),
            "CODE" => "comment",
            "VALUE" => $comment
        );

        self::setProperty($basket_id, $arProps);
    }
}

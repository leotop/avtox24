<?php


/**
 * Linemedia Autoportal
 * Main module
 * Price calculation class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);



/**
 * Класс, отвечающий за работу с заказами.
 */
class LinemediaAutoOrder
{
    /*
     * ID заказа
     */
    protected $id = null;
    
    
    public function __construct($id = false)
    {
        $this->id = (int) $id;
        
        CModule::IncludeModule('sale');
    }
    
    
    public function getID()
    {
        return $this->id;
    }
    
    
    /**
     * Получение свойств заказа.
     */
    public function getProps($code = 'ID')
    {
        $dbprops = CSaleOrderPropsValue::GetOrderProps($this->getID());
        
        $properties = array();
        while ($property = $dbprops->Fetch()) {
            $properties[$property[strval($code)]] = $property;
        }
        return $properties;
    }
    
    
    /**
     * Получить корзины заказа со всеми свойствами.
     */
    public function getBaskets()
    {
        $baskets = array();
        $dbBasketItems = CSaleBasket::GetList(array(), array('ORDER_ID' => $this->id), false, false, array('ID', 'PRODUCT_ID', 'STATUS_ID', 'WEIGHT', 'QUANTITY', 'NAME', 'NOTES', 'FUSER_ID', 'DETAIL_PAGE_URL', 'PRICE'));
        while ($basket = $dbBasketItems->Fetch()) {
            $db_res = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket['ID']));
            while ($prop = $db_res->Fetch()) {
               $basket['PROPS'][$prop['CODE']] = $prop;
            }
            $baskets []= $basket;
        }
        return $baskets;
    }
    
    
    /**
     * Разрешена ли оплата по заказу.
     */
    public function getAllowPayemnt($order_id)
    {
        if (!CModule::IncludeModule('sale')) {
            return;
        }
        
        $dbproperty = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $this->id, 'CODE' => 'ALLOW_PAYMENT'), false, false, array());
        $property = $dbproperty->Fetch();
        
        return $property['VALUE'];
    }
    
    
    /**
     * Установка разрешения оплаты по заказу.
     */
    public function setAllowPayemnt($allow)
    {
        if (!CModule::IncludeModule('sale')) {
            return;
        }
        
        $dbproperty = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $this->id, 'CODE' => 'ALLOW_PAYMENT'), false, false, array());
        
        if ($property = $dbproperty->Fetch()) {
            if (CSaleOrderPropsValue::Update($property['ID'], array('VALUE' => (string) $allow))) {
                return true;
            }
        } else {
            $arOrder = CSaleOrder::getByID($this->id);
            
            $prop = CSaleOrderProps::getList(array(), array('CODE' => 'ALLOW_PAYMENT', 'PERSON_TYPE_ID' => $arOrder['PERSON_TYPE_ID']), false, false, array('ID', 'CODE'))->Fetch();
            
            $arFields = array(
                'ORDER_ID'          => $this->id,
                'ORDER_PROPS_ID'    => $prop['ID'],
                'NAME'              => GetMessage('LM_AUTO_MAIN_ALLOW_PAYMENT'),
                'CODE'              => 'ALLOW_PAYMENT',
                'VALUE'             => $allow
            );
            
            if (CSaleOrderPropsValue::Add($arFields)) {
                return true;
            }
        }
        return false;
    }
    
    
    
    /**
     * Получение списка статусов.
     */
    public static function getStatusesList()
    {
        return LinemediaAutoStatus::getList();
        /*CModule::IncludeModule('sale');
        
        $statuses = array();
        
        $dbstatuses = CSaleStatus::GetList(
            array('SORT' => 'ASC'),
            array('LID' => LANGUAGE_ID),
            false,
            false,
            array('ID', 'NAME')
        );
        
        while ($status = $dbstatuses->Fetch()) {
            $statuses[$status['ID']] = $status;
        }
        
        return $statuses;*/
    }


    /**
     * Получение списка платежных систем.
     */
    public static function getPaysystemsList()
    {
        CModule::IncludeModule('sale');
        
        $paysystems = array();
        
        $dbpaysystems = CSalePaySystem::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array());
        
        while ($paysystem = $dbpaysystems->Fetch()) {
            $paysystems[$paysystem['ID']] = $paysystem;
        }
        
        return $paysystems;
    }


    /**
     * Получение списка доставок.
     */
    public static function getDeliveryList()
    {
        CModule::IncludeModule('sale');
        
        $deliveries = array();
        
        $dbdeliveries = CSaleDelivery::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array());
        
        while ($delivery = $dbdeliveries->Fetch()) {
            $deliveries[$delivery['ID']] = $delivery;
        }
        
        return $deliveries;
    }
    
                                
    /**
     * Получение списка типов плательщиков.
     */
    public static function getPersonTypesList()
    {
        CModule::IncludeModule('sale');
        
        $persons = array();
        
        $dbpersons = CSalePersonType::GetList(
            array('SORT' => 'ASC'),
            array(),
            false,
            false,
            array('ID', 'NAME')
        );
        
        while ($person = $dbpersons->Fetch()) {
            $persons[$person['ID']] = $person;
        }
        
        return $persons;
    }
    
    /*
    * Получение прав доступа к заказу для текущего пользователя (проверяются права главного модуля Автоэксперта, поставщики, статусы)
    * @param string $permLetter Access level letter taken from 
    * @param string $type Type of 
    * @param array $arPermFilter Type of 
    * @return bool is allowed or not
    * @throws CLinemediaException
    */
    public function getUserPermissionsForOrder($permLetter, $type, $arPermFilter)
    {          
        global $USER;
        $buyerId = false; 
        $arBasketSupplierAccesses = array();

        if($USER->isAdmin() || php_sapi_name() == 'cli' || $permLetter == LM_AUTO_MAIN_ACCESS_FULL)
            return true; 

        // проверка доступа к поставщикам в заказе
        //if(!$this->isSupplierRights()) return false;
        
        $arBasket = $this->getBaskets(); //Все корзины данного заказа
        foreach($arBasket as $basket)
        {
            //Массив доступов к поставщику на каждый товар в корзине
            $arBasketSupplierAccesses[$basket["ID"]] = $this->isProductSupplierAvailable($basket['PROPS']['supplier_id']['VALUE']);  
        }       
        
        $db_order = CSaleOrder::GetList(
        array("DATE_UPDATE" => "DESC"),
        array("ID" => $this->id)
        );
        
        if ($arOrder = $db_order->Fetch())
        {
            $status_id = $arOrder["STATUS_ID"];
        }
        
        $curStatusPerms = LinemediaAutoProductStatus::getStatusesPermissions($status_id);
                   
        if($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN)
        {   
            $db_order = CSaleOrder::GetList(
            array("DATE_UPDATE" => "DESC"),
            array("ID" => $this->id)
            );
            
            if ($arOrder = $db_order->Fetch())
            {
                $buyerId = $arOrder["USER_ID"];
            }
            
            if($type == "read")
            {
                 if($buyerId == CUser::GetId() && $buyerId && in_array("Y", $arBasketSupplierAccesses) && $curStatusPerms["PERM_VIEW"] == 'Y') return true;
                 else return false; 
            }
            elseif($type == "write")
            {
                 if($buyerId == CUser::GetId() && $buyerId && in_array("Y", $arBasketSupplierAccesses) && $curStatusPerms["PERM_UPDATE"] == 'Y') return true;
                 else return false;      
            }
           
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_DENIED)
        {     
            return false;
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH || $permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH)
        {        
            $db_order = CSaleOrder::GetList(
                array("DATE_UPDATE" => "DESC"),
                array("ID" => $this->id)
                );
            if ($arOrder = $db_order->Fetch())
            {
               $db_props = CSaleOrderProps::GetList(
                    array("SORT" => "ASC"),
                    array(            
                        )
                );
               if ($arProps = $db_props->Fetch())
               {
                  $db_vals = CSaleOrderPropsValue::GetList(
                        array("SORT" => "ASC"),
                        array(
                                "ORDER_ID" => $this->id,
                                "CODE" => "BRANCH_ID",
                            )
                    );
                  if($arVals = $db_vals->Fetch())
                  {  
                    $orderFilialId = $arVals["VALUE"]; //Филиал в заказе       
                  }
               }
            }
            
            $arUserFilialId = LinemediaAutoGroup::getUserDealerId();   //Филиал пользователя            
                  
            if($type == "read")
            {  
                //Если филиал доступен пользователю и среди всех товаров в заказе есть хотя бы один с доступным поставщиком, возвращаем true
                if(is_array($arUserFilialId["UF_DEALER_ID"]) && !empty($arUserFilialId["UF_DEALER_ID"]) && in_array($orderFilialId, $arUserFilialId["UF_DEALER_ID"]) 
                && ($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH ||$permLetter == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH) && in_array("Y", $arBasketSupplierAccesses) && $curStatusPerms["PERM_VIEW"] == 'Y') return true;
                else return false;
            }
            elseif($type == "write")
            {
                if(is_array($arUserFilialId["UF_DEALER_ID"]) && !empty($arUserFilialId["UF_DEALER_ID"]) && in_array($orderFilialId, $arUserFilialId["UF_DEALER_ID"]) 
                && $permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH && $curStatusPerms["PERM_UPDATE"] == 'Y') return true;
                else return false;
            }
            else return false;
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS)
        {

            $arUserIds = LinemediaAutoGroup::getUserClients();    
            
            $db_order = CSaleOrder::GetList(
                array("DATE_UPDATE" => "DESC"),
                array("ID" => $this->id)
                );
            if ($arOrder = $db_order->Fetch())
            {
                $buyerId = $arOrder["USER_ID"];
            }
            
            
            //file_put_contents($_SERVER['DOCUMENT_ROOT']."/test.txt", print_r($arBasketSupplierAccesses, true)) ;
            
            if($type == "read")
            {
                if((int)$buyerId > 0 && in_array($buyerId, $arUserIds) && in_array("Y", $arBasketSupplierAccesses) && $curStatusPerms["PERM_VIEW"] == 'Y') return true;
                else return false;    
            }
            elseif($type == "write")
            {
                if((int)$buyerId > 0 && in_array($buyerId, $arUserIds) && in_array("Y", $arBasketSupplierAccesses) && $curStatusPerms["PERM_UPDATE"] == 'Y') return true;
                else return false;    
            }            
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE || $permLetter == LM_AUTO_MAIN_ACCESS_READ)
        {        
            if($type == "read")
            {
                if($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE || $permLetter == LM_AUTO_MAIN_ACCESS_READ) return true;
            }
            elseif($type == "write")
            {
                if($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE) return true;
                else return false;
            }
            else return false;
        }    
    }

    /**
     * Проверка доступа пользователя к поставщикам в заказе
     * @return bool
     */
    public function isSupplierRights() {

        $allowedSuppliers = LinemediaAutoSupplier::getAllowedSuppliers('supplier_id');

        $arBasket = $this->getBaskets();
        
        foreach($arBasket as $arProd) {
            $supplierId = $arProd['PROPS']['supplier_id']['VALUE'];
            if(!in_array($supplierId, $allowedSuppliers)) return false;
        }

        return true;
    }
    
    public function isProductSupplierAvailable($supplierId) //доступен ли пользователю поставщик данного товара в заказе
    {
        global $USER;
             
        if($USER->IsAdmin() || php_sapi_name() == 'cli')
            return "Y";
            
        $allowedSuppliers = LinemediaAutoSupplier::getAllowedSuppliers('supplier_id'); //доступные поставщики
                
        foreach($allowedSuppliers as $a)
        {
            if(mb_strtolower((string)$a) === mb_strtolower((string)$supplierId)) 
            {
                return "Y";
            }
        }
        return "N";
  
    }
    
    public function convertHistoryToNewFormat($arFields) //скопировано из Битрикса из модуля sale
    {
        foreach ($arFields as $fieldname => $fieldvalue)
        {
            if (strlen($fieldvalue) > 0)
            {
                foreach (CSaleOrderChangeFormat::$arOperationTypes as $code => $arInfo)
                {
                    if (in_array($fieldname, $arInfo["TRIGGER_FIELDS"]))
                    {
                        $arData = array();
                        foreach ($arInfo["DATA_FIELDS"] as $field)
                            $arData[$field] = $arFields["$field"];

                        return array(
                            "ID" => $arFields["ID"],
                            "ORDER_ID" => $arFields["H_ORDER_ID"],
                            "TYPE" => $code,
                            "DATA" => serialize($arData),
                            "DATE_CREATE" => $arFields["H_DATE_INSERT"],
                            "DATE_MODIFY" => $arFields["H_DATE_INSERT"],
                            "USER_ID" => $arFields["H_USER_ID"]
                        );
                    }
                }
            }
        }

        return false;
    }
    
    public function getUserPermissionsForBasket($permLetter, $type, $arPermFilter, $basket_id)
    {          
        global $USER;
        $flag_allowed = false;
        $ar_order_info = array();
        $basket_available = false;
     
        if($USER->isAdmin() || php_sapi_name() == 'cli')
            return true; 
                
        $arListSuppliers = LinemediaAutoSupplier::getList();   //массив доступных поставщиков
           
        foreach ($arListSuppliers as $arSupplier) 
        {
            $arSuppliers[] = $arSupplier["PROPS"]["supplier_id"]["VALUE"];
        }        
                        
        if($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN)
        {   
            $ar_basket = array();
            $fuser_id = CSaleBasket::GetBasketUserID();

            //Проверим, что поставщик данной корзины доступен                             
            $order_id = NULL; 
            $db_basket = CSaleBasket::GetList(
                array("DATE_UPDATE" => "DESC"),
                array("ID" => $basket_id)
                );
            if ($arBasket = $db_basket->Fetch())
            {
                $props = array();
                                
                $db_res = CSaleBasket::GetPropsList(
                    array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                        ),
                    array("BASKET_ID" => $basket_id, "CODE" => "supplier_id", "VALUE" => $arSuppliers)
                );
                while ($ar_res = $db_res->Fetch())
                {                    
                    $props[] = $ar_res;
                }
                
                
                   //_d($props);
                
                
                
                if(!empty($props))
                {
                    if($arBasket["ORDER_ID"] != NULL)
                    {   
                        $basket_available = LinemediaAutoOrder::getPermByBasketStatus($basket_id);                
     
                        if($basket_available == true)
                        {
                            $order_id = $arBasket["ORDER_ID"];
                            $ar_basket = $arBasket;
                        }
                        else return false; //Статус корзины не доступен
                    }
                    else $ar_basket = $arBasket;                   
                }                   
            }
            if(empty($ar_basket)) return false; //Поставщик не доступен         
            
            if($order_id != NULL)
            {
                //Получим заказ в соответствии с доступом к статусу и принадлежащий текущему пользователю
                $arOrderByUserIds = array();                        
                $dbOrders = CSaleOrder::GetList(false, array("ID" => $order_id, "USER_ID" => $USER->GetID()), false, false, array("ID", "STATUS_ID", "USER_ID"));                        
                
                while($order = $dbOrders->Fetch())
                {                              
                    $curStatusPerms = LinemediaAutoProductStatus::getStatusesPermissions($order["STATUS_ID"]);                           
                    if($curStatusPerms['PERM_VIEW'] == 'Y' || $USER->IsAdmin())
                    {
                       $ar_order_info[] = $order;
                    }       
                }
                 
               if(!empty($ar_order_info))
               {
                   $flag_allowed = true;                   
               }
            }
            elseif($ar_basket["FUSER_ID"] == $fuser_id) //Для null заказа сверим fuser_id
            {                
               $flag_allowed = true;   
            }
    
            if(($type == "read" || $type == "write") && $flag_allowed == true)
            {
                return true;
            }
            else return false;
           
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_DENIED)
        {     
            return false;
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH || $permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH)
        {        
            $ar_basket = array();
            //Проверим, что поставщик данной корзины доступен                              
            $order_id = NULL; 
            $db_basket = CSaleBasket::GetList(
                array("DATE_UPDATE" => "DESC"),
                array("ID" => $basket_id)
                );
            if ($arBasket = $db_basket->Fetch())
            {
                $props = array();
                $db_res = CSaleBasket::GetPropsList(
                    array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                        ),
                    array("BASKET_ID" => $basket_id, "CODE" => "supplier_id", "VALUE" => $arSuppliers)
                );
                while ($ar_res = $db_res->Fetch())
                {                    
                    $props[] = $ar_res;
                }
                
                if(!empty($props))
                {
                    if($arBasket["ORDER_ID"] != NULL)
                    {   
                        $basket_available = LinemediaAutoOrder::getPermByBasketStatus($basket_id);   
                        
                        if($basket_available == true)
                        {
                            $order_id = $arBasket["ORDER_ID"];
                            $ar_basket = $arBasket;    
                        }
                        else return false;
                        
                    }
                    else $ar_basket = $arBasket;                   
                }                   
            }
            if(empty($ar_basket)) return false; //Поставщик не доступен 
            
            if($order_id != NULL)
            {
                //Получим филиал пользователя
                $arFilialIds = LinemediaAutoGroup::getUserDealerId();
            
                //Получим заказ в соответствии с филиалом и доступом к статусу
            
                $arOrderByUserIds = array();                        
                $dbOrders = CSaleOrder::GetList(false, array("ID" => $order_id, "PROPERTY_VAL_BY_CODE_BRANCH_ID" => $arFilialIds["UF_DEALER_ID"]["0"]), false, false, array("ID", "STATUS_ID", "USER_ID"));                        
                
                while($order = $dbOrders->Fetch())
                {                              
                    $curStatusPerms = LinemediaAutoProductStatus::getStatusesPermissions($order["STATUS_ID"]);                           
                    if($curStatusPerms['PERM_VIEW'] == 'Y' || $USER->IsAdmin())
                    {
                       $ar_order_info[] = $order;
                    }       
                }
                //_d($ar_order_info);
               
               if(!empty($ar_order_info))
               {
                   $flag_allowed = true;                   
               }  
            }
            else
            {
                //Если null заказ, филиал и статус нам не интересен    
                $flag_allowed = true;
            }   
          
            if($permLetter == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH && $type == "read" && $flag_allowed == true)
            {
                return true;
            }
            elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH && ($type == "read" || $type == "write") && $flag_allowed == true)
            {  
              return true;  
            }
            else return false;
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS)
        {                     
            $ar_basket = array();
            //Проверим, что поставщик данной корзины доступен                              
            $order_id = NULL; 
            $db_basket = CSaleBasket::GetList(
                array("DATE_UPDATE" => "DESC"),
                array("ID" => $basket_id)
                );
            if ($arBasket = $db_basket->Fetch())
            {
                $props = array();
                $db_res = CSaleBasket::GetPropsList(
                    array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                        ),
                    array("BASKET_ID" => $basket_id, "CODE" => "supplier_id", "VALUE" => $arSuppliers)
                );
                while ($ar_res = $db_res->Fetch())
                {                    
                    $props[] = $ar_res;
                }
                
                if(!empty($props))
                {
                    if($arBasket["ORDER_ID"] != NULL)
                    {   
                        $basket_available = LinemediaAutoOrder::getPermByBasketStatus($basket_id);
                        
                        if($basket_available == true)
                        {
                            $order_id = $arBasket["ORDER_ID"];
                            $ar_basket = $arBasket;    
                        }
                        else return false;
                        
                    }
                    else $ar_basket = $arBasket;                   
                }                   
            }
            
            if(empty($ar_basket)) return false; //Поставщик не доступен
            
            //Получим клиентов менеджера            
            $arUserIds = LinemediaAutoGroup::getUserClients();
            
            $ar_order_info = array();
            if($order_id != NULL) //Проверим, что заказ принадлежит клиенту менеджера, если заказ не null  
            {                       
                $dbOrders = CSaleOrder::GetList(false, array("ID" => $order_id, "USER_ID" => $arUserIds), false, false, array("ID", "STATUS_ID", "USER_ID"));                        
                while($order = $dbOrders->Fetch())
                {                              
                    $curStatusPerms = LinemediaAutoProductStatus::getStatusesPermissions($order["STATUS_ID"]);                           
                    if($curStatusPerms['PERM_VIEW'] == 'Y' || $USER->IsAdmin())
                    {
                       $ar_order_info[] = $order;
                    }       
                }
               
               if(!empty($ar_order_info))
               {
                   $flag_allowed = true;                   
               }   
            } 
            else
            {
               /**
               * @todo проставлять fuser_id клиента при создании корзины
               */
               
               //Если null заказ, то проверим, что корзина принадлежит клиенту менеджера.               
               foreach($arUserIds as $user_id)
               {
                    $fUser = CSaleUser::GetList(array('USER_ID' => $user_id));                  
                    $arFUser[] = $fUser["ID"];      
               } 
    
               if(in_array($ar_basket["FUSER_ID"], $arFUser))
               {
                  $flag_allowed = true;       
               }
            }

            if(($type == "read" || $type == "write") && $flag_allowed == true)
            {
              return true;
            }
            else return false;                          
        }
        elseif($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE || $permLetter == LM_AUTO_MAIN_ACCESS_READ)
        {      
            
            if($type == "read")
            {                
                $ar_basket = array();
                //Проверим, что поставщик данной корзины доступен                              
                $order_id = NULL; 
                $db_basket = CSaleBasket::GetList(
                    array("DATE_UPDATE" => "DESC"),
                    array("ID" => $basket_id)
                    );
                if ($arBasket = $db_basket->Fetch())
                {
                    $props = array();
                    $db_res = CSaleBasket::GetPropsList(
                        array(
                                "SORT" => "ASC",
                                "NAME" => "ASC"
                            ),
                        array("BASKET_ID" => $basket_id, "CODE" => "supplier_id", "VALUE" => $arSuppliers)
                    );
                    while ($ar_res = $db_res->Fetch())
                    {                    
                        $props[] = $ar_res;
                    }
                    
                    if(!empty($props))
                    {
                        if($arBasket["ORDER_ID"] != NULL)
                        {   
                            $order_id = $arBasket["ORDER_ID"];
                            $ar_basket = $arBasket;
                        }
                        else $ar_basket = $arBasket;                   
                    }                   
                }
                
                if(empty($ar_basket)) return false; //Поставщик не доступен    
                
                if($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE || $permLetter == LM_AUTO_MAIN_ACCESS_READ) return true;
            
            }
            elseif($type == "write")
            {
                if($permLetter == LM_AUTO_MAIN_ACCESS_READ_WRITE) return true;
                else return false;
            }
            else return false;
        }    
    }
    
    
    function getPermByBasketStatus($basket_id)
    {
        global $USER;
        $basket_available = false;
        //Доступ к статусу корзины
        $db_res2 = CSaleBasket::GetPropsList(
            array(
                    "SORT" => "ASC",
                    "NAME" => "ASC"
                ),
            array("BASKET_ID" => $basket_id, "CODE" => "status")
        );
        while ($ar_res2 = $db_res2->Fetch())
        {                            
            $props2["STATUS"] = $ar_res2;                                                            
        }
         
        if(is_array($props2["STATUS"]))   //Доступы по статусу корзины
        {     
            $curStatusPerms2 = LinemediaAutoProductStatus::getStatusesPermissions($props2["STATUS"]["VALUE"]);

            if($curStatusPerms2['PERM_VIEW'] == 'Y' || $USER->IsAdmin())
            { 
                $basket_available = true;                                                   
            }
        }
        else  $basket_available = true; 
        
        return $basket_available; 
    }
}
 
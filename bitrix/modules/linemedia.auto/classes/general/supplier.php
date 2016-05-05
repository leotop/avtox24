<?php

/**
 * Linemedia Autoportal
 * Main module
 * Suppliers
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
 * Поставщики.
 */
class LinemediaAutoSupplier
{
    protected $id;
    
    protected $loaded = false;
    
    protected $data = array();
    
    static $cache;
    
    protected $ignore_permissions = false;
    
    
    public function __construct($id)
    {
        /*
         * Вывод отладочной информации.
         */
        LinemediaAutoDebug::add('Supplier object created (ID ' . $id . ')');
        
        $this->id = (string) $id;
    }

    public function ignorePermissions()
    {
	    $this->ignore_permissions = true;
    }
    
    /**
     * Загрузка данных о поставщике.
     */
    protected function load()
    {
        if (!isset(self::$cache[$this->id])) {
        
	        global $USER;
	
	        if (empty($this->id)) {
	            return;
	        }
	        
	        if ($this->loaded) {
	            return;
	        }
	        
	        $this->loaded = true;

            // Доступы к поставщикам определяются исключительно доступами инфоблоков
//	        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto'LinemediaAutoAllGroup
//	        if(!$this->ignore_permissions) {
//		        $accessRight = self::getUserAccesRight();
//		        if($accessRight == 'D') {
//		            throw  new Exception('Access to suppliers denied!');
//		        }
//	        }
	        $obCache = new CPHPCache();
			$life_time = 30 * 60; 
			$cache_id = 'supplier_' . $this->id . '_' . md5(json_encode($USER->GetUserGroupArray())) . '-' . $this->ignore_permissions;
			if ($obCache->InitCache($life_time, $cache_id, "/lm_auto/suppliers")) {
			    $cache = $obCache->GetVars();
			    $supplier = $cache['supplier'];
			} else {
		        CModule::IncludeModule('iblock');
		        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
	
	            $arFilter = array(
	                'IBLOCK_ID' => $iblock_id,
	                'PROPERTY_supplier_id' => $this->id,
	            );
	            $arFilter['CHECK_PERMISSIONS'] = 'Y';
	            
	            // для запуска из консоли не проверяем доступ
	            if (php_sapi_name() == 'cli') $arFilter['CHECK_PERMISSIONS'] = 'N';

	            // если у неавторизованных пользователей стоит филиальный доступ - не проверяем доступ,
	            // ограничение делается на уровне события в модуле филиалов
//	            if(!$USER->IsAuthorized() &&
//	                ($accessRight == LM_AUTO_BRANCH_ACCESS_READ_OWN_BRANCH || $accessRight == LM_AUTO_BRANCH_ACCESS_READ_WRITE_OWN_BRANCH)) {
//	                $arFilter['CHECK_PERMISSIONS'] = 'N';
//	            }
	            
	            if($this->ignore_permissions) {
	            	$arFilter['CHECK_PERMISSIONS'] = 'N';
	            	$accessRight = 'R';
	            }
	            
	            /*
	             * Cоздаём событие
	             */
	            $events = GetModuleEvents('linemedia.auto', 'OnBeforeSuppliersListLoaded');
	            while ($arEvent = $events->Fetch()) {
	                ExecuteModuleEventEx($arEvent, array(&$arFilter, &$accessRight));
	            }
				
		        $supplier_res = CIBlockElement::GetList(
		            array(),
	                $arFilter,
		            false,
		            false,
		            array('ID', 'NAME', 'CODE', 'ACTIVE')
		        );
		        
		        if ($supplier = $supplier_res->Fetch()) {
		            
		            $db_props = CIBlockElement::GetProperty($iblock_id, $supplier['ID'], array('sort' => 'asc'));
		            
		            while ($prop = $db_props->Fetch()) {
		                $supplier['PROPS'][$prop['CODE']] = $prop;
		            }
		            
		            /*
		             * Вывод отладочной информации
		             */
		            LinemediaAutoDebug::add('Supplier object loaded (ID ' . $this->id . ')', print_r($supplier, true));
		            
		        } else {
		            /*
		             * Вывод отладочной информации
		             */
		            LinemediaAutoDebug::add('Supplier object load error 404 (ID ' . $this->id . ')');
		            //$obCache->AbortDataCache();
		        }
		        
		        if ($obCache->StartDataCache()) {
			        $obCache->EndDataCache(array(
			        	'supplier' => $supplier,
			        ));
		        }
			}
			
			/*
	         * Cоздаём событие
	         */
	        $events = GetModuleEvents('linemedia.auto', 'OnAfterSupplierLoaded');
		    while ($arEvent = $events->Fetch()) {
			    ExecuteModuleEventEx($arEvent, array(&$supplier));
		    }
			
			$this->data = self::$cache[$this->id] = array_filter((array) $supplier);
			
		} else { // isset in static cache
			$this->data = self::$cache[$this->id];
		}
                
    }
    
    
    public function getID()
    {
        return $this->id;
    }
    
    
    /**
     * Получение поля.
     */
    public function get($field)
    {
        $this->load();
        if (isset($this->data[$field])) {
            return $this->data[$field];
        }
        return $this->data['PROPS'][$field]['VALUE'];
    }
    
    
    /**
     * Получить все поля.
     */
    public function getArray()
    {
        $this->load();
        return $this->data;
        
    }
    
    
    /**
     * Существование поставщика.
     */
    public function exists()
    {
        $this->load();
        return count($this->data) > 0;
    }
    
    
    /**
     * Получение списка поставщиков.
     * 
     * @return array
     */
    public static function GetList($order = array("SORT" => "ASC"), $filter = array(), $group = false, $nav = false, $select = array('ID', 'NAME', 'CODE', 'ACTIVE'), $code = 'id')
    {
    	global $USER, $DB;

        // Доступы к поставщикам определяются исключительно доступами инфоблоков
        // проверка прав доступа, согласно настройкам главного модуля linemedia.auto
//        $accessRight = self::getUserAccesRight();
//        if ($accessRight == 'D') {
//            return array();
//        }
		
        $args = md5(json_encode(array_merge(func_get_args(), array('group' => $USER->GetUserGroupArray()))));
        if (isset(self::$cache[__METHOD__][$args])) {
            return self::$cache[__METHOD__][$args];
        }
		
        if (!CModule::IncludeModule('iblock')) {
            return array();
        }
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS', 0);
        
        if (!$iblock_id) {
        	return array();
        }
        
        $filter['IBLOCK_ID'] = $iblock_id;
		
        // Учитывать права доступа, задаваемые для групп филиалов и поставщиков.
		if (!isset($filter['CHECK_PERMISSIONS'])) {
			$filter['CHECK_PERMISSIONS'] = 'Y';
		}
		
		/* if (!isset($filter['ACTIVE'])) {
			$filter['ACTIVE'] = 'Y';
		} */
		
		// Для запуска из консоли не проверяем доступ.
		if (php_sapi_name() == 'cli') {
			$filter['CHECK_PERMISSIONS'] = 'N';
		}
		
		// Если у неавторизованных пользователей стоит филиальный доступ - не проверяем доступ,
		// ограничение делается на уровне события в модуле филиалов.
		// Если админ - также не проверяем доступ.
		if ($USER->IsAdmin()) {
			$filter['CHECK_PERMISSIONS'] = 'N';
		}
		
        /*
         * Cоздаём событие.
         */
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeSuppliersListLoaded');
        while ($arEvent = $events->Fetch()) {
             ExecuteModuleEventEx($arEvent, array(&$filter));
        }
        
        // Experimental.
    	if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
			if (!isset($filter['PROPERTY_branch_owner'])) {
				$b_user = new LinemediaAutoBranchesUser(CUser::getID());
				$my_branch_id = $b_user->getBranchId();
			} else {
				$my_branch_id = $filter['PROPERTY_branch_owner'];
			}
			if (!$USER->IsAdmin()) {
				$filter['PROPERTY_branch_owner'] = $my_branch_id;
			}
		}
		
        LinemediaAutoDebug::add('Suppliers filter', print_r($filter, true), LM_AUTO_DEBUG_WARNING);
		
        $dbsuppliers = CIBlockElement::GetList($order, $filter, $group, $nav, $select);
        $suppliers   = array();
        while ($supplier = $dbsuppliers->Fetch()) {
            $dbprops = CIBlockElement::GetProperty($iblock_id, $supplier['ID'], array('sort' => 'asc'));
            while ($prop = $dbprops->Fetch()) {
            	$supplier['PROPS'][$prop['CODE']] = $prop;
            }
            unset($prop, $dbprops);
            
            // Ключ
            $key = $supplier['ID'];
            switch ($code) {
                case 'id':
                    $key = $supplier['ID'];
                    break;
                case 'supplier_id':
                    $key = $supplier['PROPS']['supplier_id']['VALUE'];
                    break;
            }
            $suppliers[$key] = $supplier;
        }



        /*
         * Cоздаём событие.
         */
        $events = GetModuleEvents('linemedia.auto', 'OnAfterSupplierListLoaded');
        while ($arEvent = $events->Fetch()) {
        	ExecuteModuleEventEx($arEvent, array(&$suppliers));
        }
        self::$cache[__METHOD__][$args] = $suppliers;
        
        return $suppliers;
    }

    
    /**
     * Получение списка ID доступных пользователю поставщиков.
     * 
     * @param string $code
     * @return array
     */
    public static function getAllowedSuppliers($code = 'id')
    {
    	$args = md5(json_encode(func_get_args()));
        if (isset(self::$cache[__METHOD__][$args])) {
            return self::$cache[__METHOD__][$args];
        }
    	
    	
        $arIds = self::GetList(array(), array(), false, false, array('ID'), $code);

        $result = count($arIds) > 0 ? array_keys($arIds) : array();
        
        self::$cache[__METHOD__][$args] = $result;
        return $result;
    }

    /**
     * Простой метод, возвращающий булево значение доступа к поставщикам.
     * 
     * @return bool
     */
    public static function isSuppliersAccessed()
    {
        $suppliers = self::getAllowedSuppliers();
        if (count($suppliers) < 1) {
            LinemediaAutoDebug::add('LM_ACCESS_LEVEL suppliers access denied', false, LM_AUTO_DEBUG_WARNING);
            return false;
        }
        return true;
    }

    /**
     * Проверка доступа к конкретному поставщику
     * @param int|string $supplierId - Идентификатор поставщика
     * @param string $code - код идентификатора поставщика id | supplier_id
     */
    public static function isSupplierAccesRight($supplierId, $code = 'id')
    {
	    $access = false;

	    if (!array_key_exists(__METHOD__.$supplierId, self::$cache)) {
		    $suppliers = self::getAllowedSuppliers($code);

		    foreach($suppliers as $key => $value) {
			    if (ToLower($supplierId) === ToLower($value)) {
				    $access = true;
				    break;
			    }
		    }
		    self::$cache[__METHOD__.$supplierId] = $access;
	    }

        return self::$cache[__METHOD__.$supplierId];
    }


    /**
     * Получение списка удаленных поставщиков.
     */
    public static function getRemoteSuppliersList()
    {
        $suppliers = array();
        $events = GetModuleEvents('linemedia.auto', 'OnRemoteSuppliersGet');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$suppliers));
        }
        return $suppliers;
    }
    
    
    /**
     * Проверка ID поставщика на уникальность.
     * 
     * @param string $id
     * @return bool
     */
    public static function isUniqueSupplierId($id)
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $id = (string) $id;
        
        if (empty($id)) {
            return false;
        }
        
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        $db = CIBlockElement::GetList(
            array(),
            array('IBLOCK_ID' => $iblock_id, 'PROPERTY_supplier_id' => $id),
            false,
            false,
            array('ID')
        );
        
        return ($db->SelectedRowsCount() <= 1);
    }
    
    
    /**
     * Проверка существует ли такой ID поставщика.
     * 
     * @param string $id
     * @return bool
     */
    public static function existsSupplierId($id)
    {
        $arIds = self::GetList(array(), array(), false, false, array('ID'), 'supplier_id');

        return array_key_exists($id, $arIds);

//        if (!CModule::IncludeModule('iblock')) {
//            return;
//        }
//        $id = (string) $id;
//
//        if (empty($id)) {
//            return false;
//        }
//
//        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
//
//        $db = CIBlockElement::GetList(
//            array(),
//            array('IBLOCK_ID' => $iblock_id, 'PROPERTY_supplier_id' => $id),
//            false,
//            false,
//            array('ID')
//        );
//
//        return ($db->SelectedRowsCount() > 0);
    }
    
    
    /**
     * Генерация ID поставщика.
     */
    public static function generateSupplierId()
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        $db = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $iblock_id),
                false,
                false,
                array('ID', 'PROPERTY_supplier_id')
        );
        $supplier_ids = array();
        while ($item = $db->Fetch()) {
            $supplier_ids []= (string) $item['PROPERTY_SUPPLIER_ID_VALUE'];
        }
        
        $id = '1';
        while (in_array($id, $supplier_ids)) {
            $id++;
        }
        return strval($id);
    }


    public static function getIDByBitrixID($BID)
    {
	    $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
	    $res = CIBlockElement::GetProperty($iblock_id, $BID, array(), array('CODE' => 'supplier_id'));
	    $res = $res->Fetch();
		return $res['VALUE'];
    }


    /**
     * Получаем статистику отказов поставщика и статистику скорости доставки поставщиком (по дням).
     * 
     * @param int $id
     * @return array
     */
    public static function getStat($id)
    {
	    if (isset(self::$cache[__METHOD__][$id])) {
		    return self::$cache[__METHOD__][$id];
	    }

        if (!CModule::IncludeModule('sale')) {
            return array();
        }

        $c = new CPHPCache;
        $cache_id = 'supplier_stat_'.$id;
        if (FALSE && $c->InitCache(12 * 3600, $cache_id, '/supplier_stat/'.$id.'/')) {
            return $c->GetVars();
        } else {
            $stock_id       = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_STORED", "");
            $rejected_id    = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_REJECTED", "");
            $requested_id   = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_REQUESTED", "");
            
            if (!($stock_id && $rejected_id && $requested_id)) {
                return array();
            }
            $rs = CSaleBasket::GetPropsList(array('BASKET_ID' => 'ASC'), array('CODE' => 'supplier_id', 'VALUE' => $id), 0, 0, array('BASKET_ID'));
            $basket_ids = array();

            while ($cart = $rs->Fetch()) {
                $basket_ids[] = $cart['BASKET_ID'];
            }
            if (empty($basket_ids)) { // пока что нет данных, по которым считать статистику.
                return array();
            }
            $counters = array();
	        $supplier_baskets = array();


	        /*
	         * массив свойств времени установки статуса выполнения и отказа
	         */
	        $arrStatus = array("status_time_" . $stock_id, "status_time_" . $rejected_id);

			/*
			 * Получаем список записей у которых был получен хотя бы один из необходимых статусов(выполнен или отказ)
			 */
            $rs = CSaleBasket::GetPropsList( array('BASKET_ID'=>'ASC'), array('BASKET_ID'=>$basket_ids,'CODE' => $arrStatus), false, false, array('NAME', "VALUE", "BASKET_ID"));

            while ($cart = $rs->Fetch()) {
                if (!in_array($cart['NAME'], array("Status change time " . $stock_id, "Status change time " . $rejected_id))) {
                    continue;
                }
	            $supplier_baskets[$cart["BASKET_ID"]][$cart["NAME"]] = $cart["VALUE"];
            }
	        $total = 0;

	        /*
	         * Перебираем корзины и формируем массив с
	         * послединим установленным статусом из двух необходимых(выполнен или отказ)
	         * для каждой корзины
	         */
            $status_count = array();

	        foreach ($supplier_baskets as $b_id => $statuses){
		        $time_max = 0;
		        foreach($statuses as $status_name => $status_time){
			        if($status_time > $time_max){
				        $time_max = $status_time;
				        $supplier_baskets[$b_id]['LAST_STATUS_NAME'] = $status_name;
			        }
		        }
		       $status_count[] =  $supplier_baskets[$b_id]['LAST_STATUS_NAME'];
	        }

	        /*
	         * @counters - Массив с количеством корзин по каждому статусу
	         * @total - общее количество корзин
	         */

	        foreach($status_count as $k => $status_name){
		        $counters[$status_name]++;
	        }
	        $total = count($status_count);

            /*
             * статистика по времени доставки. сначала получим время перевода в статус "на складе", потом вычтем из него время,когда создавали запрос поставщику.
             * и соберём полученное в массив кол-во дней=> % выполненных заказов.
             */
            $rs = CSaleBasket::GetPropsList(
                array('BASKET_ID' => 'ASC'),
                array('BASKET_ID' => $basket_ids, 'CODE' => 'status_time_'.$stock_id ),
                false,
                false,
                array('CODE', 'BASKET_ID', 'VALUE')
            );
            $timings = array();

            while ($cart = $rs->Fetch()) {
                $timings[ $cart['BASKET_ID'] ] = $cart["VALUE"];
            }
            
            $rs = CSaleBasket::GetPropsList(
                array('BASKET_ID' => 'ASC'),
                array('BASKET_ID' => $basket_ids, 'CODE' => 'status_time_'.$requested_id ),
                false,
                false,
                array('CODE', 'BASKET_ID', 'VALUE')
            );

            $n = 0;

            while ($cart = $rs->Fetch()) {
                $key = (int)round( ((int)$timings[ $cart['BASKET_ID'] ] - (int)$cart["VALUE"]) / (3600 * 24) ); // секунды в дни
                if ($key < 0) {
                    continue; // Явно ошибочные значения
                }
                if (!isset($counters['timings'][ $key ])) {
                    $counters['timings'][ $key ] = 0;
                }
                $counters['timings'][ $key ]++;
                $n++;
            }

            $n = $n ? 100 / $n : 0; // Чтобы много раз не умножать потом на 100 для получения процентов.

            /*
             * Пробежимся и превратим абсолютные значения в проценты.
             */
            $counters['timings'] = (array) $counters['timings'];

            foreach ($counters['timings'] as $k => $v) {
                $counters['timings'][$k] = round($v * $n);
            }

            unset($timings);

            ksort($counters['timings']);

            $ret = array(
                'delivery_time' => $counters['timings'],
                'rejected'      => $total ? round(intval($counters["Status change time " . $rejected_id]) / $total * 100) : 0,
                'completed'     => $total ? round(intval($counters["Status change time " . $stock_id]) / $total * 100) : 0
            );
	        $c->StartDataCache();
            $c->EndDataCache($ret);

	        self::$cache[__METHOD__][$id] = $ret;

            return $ret;
        }
    }
    
    
    public static function getIblockId()
    {
	    return (int) COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
    }
	

	/**
	 * Добавляем нового поставщика.
	 * 
	 * @param mixed $data
	 * @return int
	 */
	public static function add($data)
	{
		CModule::IncludeModule('iblock');
		CModule::IncludeModule('currency');
		$iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
		
		
		$data['NAME'] = ($data['NAME']) ? $data['NAME'] : 'unnamed';
		$data['supplier_id'] = ($data['supplier_id']) ? $data['supplier_id'] : self::generateSupplierId();
		$data['markup'] = ($data['markup']) ? $data['markup'] : 0;
		$data['delivery_time'] = ($data['delivery_time']) ? $data['delivery_time'] : 0;
		$data['visual_title'] = ($data['visual_title']) ? $data['visual_title'] : $data['supplier_id'];
		$data['email'] = ($data['email']) ? $data['email'] : '';
		$data['api'] = ($data['api']) ? $data['api'] : '';
		$data['currency'] = !empty($data['currency']) ? $data['currency'] : CCurrency::GetBaseCurrency();
		$data['css'] = ($data['css']) ? $data['css'] : '';

		$el = new CIBlockElement;
		$ID = $el->Add(array(
			'IBLOCK_ID' => $iblock_id,
			'NAME' => $data['NAME'],
			'ACTIVE' => 'Y',
			'PROPERTY_VALUES' => array(
				'supplier_id' => $data['supplier_id'],
				'markup' => $data['markup'],
				'delivery_time' => $data['delivery_time'],
				'visual_title' => $data['visual_title'],
				'email' => $data['email'],
				'api' => $data['api'],
				'currency' => $data['currency'],
				'css' => $data['css'],
			),
		));
		return $ID;
	}


    /**
     * Доступ текущего пользователя к поставщикам согласно настройкам главного модуля
     * @return string
     */
    // Доступы к поставщикам определяются исключительно доступами инфоблоков
//    private static function getUserAccesRight()
//    {
//        global $USER;
//
//        if ($USER->IsAdmin()) {
//        	return 'X';
//        }
//
//        $curUserGroup = $USER->GetUserGroupArray();
//
//        $sModuleId = "linemedia.auto";
//
//        $arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_SUPPLIERS);
//
//        $key = md5(json_encode(array(
//        	$sModuleId,
//        	$curUserGroup,
//        	$arTasksFilter
//        )));
//
//        if (isset(self::$cache['permissions'][$key])) {
//	        return self::$cache['permissions'][$key];
//        }
//
//        $maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
//
//        self::$cache['permissions'][$key] = $maxRole;
//
//        return $maxRole;
//    }

    // Доступы к поставщикам определяются исключительно доступами инфоблоков
//    public static function getUnauthorizedAccessRight()
//    {
//        $curUserGroup = array(2);
//        $sModuleId = "linemedia.auto";
//        $arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_SUPPLIERS);
//
//        $key = md5(json_encode(array(
//            $sModuleId,
//            $curUserGroup,
//            $arTasksFilter
//        )));
//
//        if(isset(self::$cache['permissions'][$key])) {
//            return self::$cache['permissions'][$key];
//        }
//
//        $maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
//
//        self::$cache['permissions'][$key] = $maxRole;
//
//        return $maxRole;
//    }

    /**
     * Проверяет доступ определенной группы для поставщика.
     * @param int $supplier_id - ИД поставщика
     * @param int|false $group_id - ИД группы пользователей
     */
    public static function getRightByGroup($supplier_id, $group_id) {

        $right = LM_AUTO_MAIN_ACCESS_DENIED;

        if(CModule::IncludeModule('iblock')) {

            $rs = CTask::GetList(
                array("LETTER"=>"asc"),
                array(
                    "MODULE_ID" => "iblock",
                    "BINDING" => "iblock",
                )
            );
            $letters = array();
            while($ar = $rs->Fetch())
                $letters[$ar["ID"]] = $ar["LETTER"];

            $ids = self::GetList(array(), array(), false, false, array('ID'), 'supplier_id');

            //_d($ids);

            $id = $ids[$supplier_id]['ID'];
            $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

            //$obElementRights = new CIBlockElementRights($iblock_id, $id);
            $right_list = array();
            $res =  CIBlockElementRights::GetList(array('IBLOCK_ID' => $iblock_id, 'ITEM_ID' => $id));

            $is_branch_group = false;
            while($right_row = $res->Fetch()) {
                $right_list[] = $right_row;
                if($right_row['GROUP_CODE'] == 'G2' && !$is_branch_group) {
                    $right = $right_row['TASK_ID'];
                } else if($group_id && $right_row['GROUP_CODE'] == 'G' . $group_id) {
                    $right = $right_row['TASK_ID'];
                    $is_branch_group = true;
                }
            }

            if(is_numeric($right)) {
                $right = $letters[$right];
            }
        }
        LinemediaAutoDebug::add('LM_SUPPLIER_GROUP_ACCESS ID ' . $supplier_id . ' GROUP ' . $group_id . ' = ' . $right , false, LM_AUTO_DEBUG_WARNING);
        return $right;
    }
}




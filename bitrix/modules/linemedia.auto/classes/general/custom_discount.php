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


/*
 * Класс, отвечающий за работу со скидками.
 */
class LinemediaAutoCustomDiscount
{ 
    protected $part     = null;

    protected $groups    = array();
    protected $user_id   = array();
    protected $date;

    protected $iblock_id = null;

    protected $discounts        = array();
    protected $discount_types   = array();
    protected $supplier_ids     = array();

    /**
     * Массив дополнительных параметров расчета цены - в него можно добавлять значения из других модулей
     * @var
     */
    protected $external = array();

    /*
     * ЧПН отладка расчёта цен
     */
    protected $debug = array();
    
    
    
    static $cache = array();
    

    public function __construct(LinemediaAutoPartAll $part, $user_id = null, $external = null)
    {
        $this->part = $part;
        $this->user_id = (intval($user_id) > 0) ? (intval($user_id)) : (CUser::GetID());
        $this->external = $external;
        $this->date = time();

        $this->iblock_id = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_DISCOUNT");

        // если заданы внешние параметры просчета - укажем их для отладки
        if(is_array($this->external) && count($this->external) > 0) {
            $external_params = array();
            $external_string = '';
            foreach($this->external as $key => $value) {
                $external_params[] = $key . '=' . $value;
            }
            if(count($external_params) > 0) {
                $external_string = ', ' . join(', ', $external_params);
            }
            //LinemediaAutoDebug::add('Linemedia Price custom discount [user=' . $this->user_id . $external_params . ']');
        } else {
            //LinemediaAutoDebug::add('Linemedia Price custom discount [user=' . $this->user_id . ']');
        }
        $this->loadDiscounts();
    }



    private function loadDiscounts()
    {

    	if (!isset(self::$cache['discounts'])) {

			$obCache = new CPHPCache();
			$life_time = 30 * 60;
			$cache_id = 'iblock/custom_discounts' . $this->user_id; // CUser::GetID();
			if ($obCache->InitCache($life_time, $cache_id, "/lm_auto/custom_discount")) {
			    $cache = $obCache->GetVars();
			    $discounts = $cache['discounts'];
			    $discount_types = $cache['discount_types'];
			    $supplier_ids = $cache['supplier_ids'];
			} else {

		        /*
		         * Выборка скидок из инфоблока согласно составленному фильтру
		         */
		        CModule::IncludeModule('iblock');
		        $IBLOCK_ID = $this->iblock_id;
		        $discounts = array();

		        /*
		         * Скидки для текущего пользователя
		         */
		        $filter_cust_disc = array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y');
		        if ($this->user_id) {
			        $filter_cust_disc[] = array(
				        "LOGIC" => "OR",
				        array("PROPERTY_user_id" => false),
				        array("PROPERTY_user_id" => $this->user_id)
				    );
		        } else {
			        $filter_cust_disc['PROPERTY_user_id'] = false;
		        }
		        
		        
		        
		        if($this->branch_id) {
			        $filter_cust_disc['PROPERTY_branch_owner'] = $this->branch_id;
		        }
		        
		        
		        

		        $res = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter_cust_disc, false, false, array('ID', 'NAME'));
		        while ($discount = $res->Fetch()) {
		        	$props_res = CIBlockElement::GetProperty($IBLOCK_ID, $discount['ID']);
		        	while ($prop = $props_res->Fetch()) {
		        		if ($prop['MULTIPLE'] == 'Y') {
                            // не добавляем нулевые значения
			        		if(!is_null($prop['VALUE'])) {
                                $discount['PROPS'][$prop['CODE']][] = $prop['VALUE'];
                            }
		        		} else {
			        		$discount['PROPS'][$prop['CODE']] = $prop['VALUE'];
		        		}
		        	}
		            $discounts []= $discount;
		        }

		        /*
		         * Выборка типов скидок
		         */
		        $discount_types = array();
		        $property_enums = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => "discount_type"));
				while ($enum_fields = $property_enums->Fetch()) {
					$discount_types[$enum_fields["ID"]] = $enum_fields["XML_ID"];
				}


		        /*
		         * Выборка ID поставщиков
		         */
		        $IBLOCK_ID = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS");
		        $supplier_ids = array();
		        $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $IBLOCK_ID), false, false, array('ID', 'CODE', 'PROPERTY_supplier_id'));
		        while ($supplier = $res->Fetch()) {
		            $supplier_ids[$supplier['PROPERTY_SUPPLIER_ID_VALUE']] = $supplier['ID'];
		        }
				
		        if ($obCache->StartDataCache()) {
			        $obCache->EndDataCache(array(
			        	'discounts' => $discounts,
			        	'discount_types' => $discount_types,
			        	'supplier_ids' => $supplier_ids,
			        ));
		        }
			}

			/*
			* Уберём лишние скидки для пользователя
			* Если скидок очень много, 90: что они индивидуальны для пользователя,
			* тогда можно отлючить все, которые для других пользователей и повысить производительность
			* при дальнейших расчётах
			*
			* ЗАМЕНЕНО ФИЛЬТРОМ ПО ПОЛЬЗОВАТЕЛЮ ВЫШЕ
			*/
			/*$filtered_discounts = array();
			$cur_user_id = CUser::GetID();
			foreach($discounts AS $discount) {

				$users = (array) $discount['PROPS']['user_id'];
				$users = array_filter($users);

				if($cur_user_id > 0) { // для авторизованного
					if(count($users) > 0 AND !in_array($cur_user_id, $users))
						continue;
				} else { // для гостя
					if(count($users) > 0)
						continue;
				}

				$filtered_discounts[] = $discount;
			}*/
		
			foreach ($discounts as &$discount) {		
				/*
				 * Артикул
				 */
				$discount['PROPS']['article'] = (array) $discount['PROPS']['article'];
				$discount['PROPS']['article'] = array_map('mb_strtolower', $discount['PROPS']['article']);
				$discount['PROPS']['article'] = array_map('trim', $discount['PROPS']['article']);
				$discount['PROPS']['article'] = array_map('LinemediaAutoPartsHelper::clearArticle', $discount['PROPS']['article']);
				$discount['PROPS']['article'] = array_filter($discount['PROPS']['article']);

				/*
				 * Наименование производителя
				 */
				$discount['PROPS']['brand_title'] = (array) $discount['PROPS']['brand_title'];
				$discount['PROPS']['brand_title'] = array_map('mb_strtoupper', $discount['PROPS']['brand_title']);
				$discount['PROPS']['brand_title'] = array_map('trim', $discount['PROPS']['brand_title']);
				$discount['PROPS']['brand_title'] = array_filter($discount['PROPS']['brand_title']);
				
				/*
				 * Группа пользователей
				 */
				$discount['PROPS']['user_group'] = (array) $discount['PROPS']['user_group'];
				$discount['PROPS']['user_group'] = array_filter($discount['PROPS']['user_group']);
				
				/*
				 * Пользователь
				 */
				$discount['PROPS']['user_id'] = (array) $discount['PROPS']['user_id'];
				$discount['PROPS']['user_id'] = array_filter($discount['PROPS']['user_id']);
				
				/*
				 * Поставщик
				 */
				$discount['PROPS']['supplier_id'] = (array) $discount['PROPS']['supplier_id'];
				$discount['PROPS']['supplier_id'] = array_map('mb_strtolower', $discount['PROPS']['supplier_id']);
				$discount['PROPS']['supplier_id'] = array_filter($discount['PROPS']['supplier_id']);
				
				/*
				 * Минимальная базовая цена
				 */
				$discount['PROPS']['price_min'] = (float) str_replace(",", ".", $discount['PROPS']['price_min']);

				/*
				 * Максимальная базовая цена
				 */
				$discount['PROPS']['price_max'] = (float) str_replace(",", ".", $discount['PROPS']['price_max']);
				
				
				/**
				* Поля прайслиста
				*/
				$discount['PROPS']['price_fields'] = array_filter($discount['PROPS']['price_fields']);

                $price_fields = array();

                if(is_array($discount['PROPS']['price_fields'])) {
                    foreach($discount['PROPS']['price_fields'] as $price_field) {
                        if(!isset($price_fields[$price_field['CODE']])) {
                            $price_fields[$price_field['CODE']] = array(
                                'CODE' => $price_field['CODE'],
                                'VALUE' => array($price_field['VALUE'])
                            );
                        } else {
                            $price_fields[$price_field['CODE']]['VALUE'][] = $price_field['VALUE'];
                        }
                    }
                }

                $discount['PROPS']['price_fields'] = $price_fields;
			}
			
			
			
			/*
			 * Событие для других модулей.
			 * Позволяет обработать список скидок
			 */
			$events = GetModuleEvents("linemedia.auto", "OnSaleDiscountsLoad");
			while ($arEvent = $events->Fetch()) {
				$result = ExecuteModuleEventEx(
					$arEvent,
					array(
						&$discounts,
						&$discount_types,
						&$supplier_ids,
						$this->user_id
					)
				);
			}
		
			self::$cache['discounts'] = array(
	        	'discounts' => $discounts,
	        	'discount_types' => $discount_types,
	        	'supplier_ids' => $supplier_ids,
	        );
		}
		
		$discounts		= self::$cache['discounts']['discounts'];
		$discount_types	= self::$cache['discounts']['discount_types'];
		$supplier_ids	= self::$cache['discounts']['supplier_ids'];

		$this->discounts = $discounts;
		$this->discount_types = $discount_types;
		$this->supplier_ids = $supplier_ids;
    }


    public function getPart()
    {
        return $this->part;
    }


    public function getGroups()
    {
        if (empty($this->groups)) {
            global $USER;

            if ($USER->GetID() != $this->user_id) {
            	$this->groups = CUser::GetUserGroup($this->user_id);
            } else {
	            $this->groups = $USER->GetUserGroupArray();
            }

            /*
             * Учет группы неавторизованных пользователей.
             * Если запуск из консли для проценки в случае модуля каталогов, то $this->user_id == 0
             * Если запуск идет из модуля скачки прайсов, то там $this->user_id не 0, а указан явно
             */
            if (!$USER->IsAuthorized() && !$this->user_id) {
                $this->groups []= LinemediaAutoIblockPropertyUserGroup::GROUP_GUEST;
            }
        }
        return $this->groups;
    }


    public function setGroups($groups)
    {
        $this->groups = (array) $groups;
    }


    public function setUserId($user_id)
    {
        $this->user_id = (int) $user_id;
    }

    public function setBranchId($branch_id)
    {
        $this->branch_id = (int) $branch_id;
    }

    public function setDate($date)
    {
        $this->date = (int) $date;
    }

    /**
     * Установка внешних параметров
     */
    public function setExternal($key, $value)
    {
        $this->external[$key] = $value;
    }


    /**
     * Рассчет цены.
     *
     * @param float $price
     */
    public function calculate($price)
    {

        /*
         * Соберём информацию для фильтра, который ищет подходящие скидки
         */

        /*
         * Группы
         */
        $user_id        = $this->user_id;
        $groups         = $this->getGroups();

        $article                = $this->part->get('article');
        $brand_title            = $this->part->get('brand_title');

        /*
         * Сюда может прийти запчасть с part_id и в этом случае brand_title берется
         * из базы без словоформ
         */
        $wordform = new LinemediaAutoWordForm();
        $brand_title = $wordform->getBrandGroup($brand_title) ?: $brand_title;

        
        $original_brand_title   = $this->part->get('original_brand_title');
        $supplier_id            = $this->part->get('supplier_id');
        $base_price             = $this->part->get('price');


        /*
         * Создаём событие
         */
        if(!isset(self::$cache['events']['BeforeItemPriceCalculate'])) {
            $events = GetModuleEvents("linemedia.auto", "BeforeItemPriceCalculate");
            while ($arEvent = $events->Fetch()) {
                self::$cache['events']['BeforeItemPriceCalculate'][] = $arEvent;
            }
        }
        $events = self::$cache['events']['BeforeItemPriceCalculate'];

        foreach ($events AS $arEvent) {
            ExecuteModuleEventEx($arEvent, array(
                &$this->part, &$article, &$brand_title, &$user_id, &$groups, &$supplier_id, &$base_price
            ));
        }


        // Скидка применяется к оригинальному бренду.
        if (COption::getOptionString('linemedia.auto', 'LM_AUTO_MAIN_USE_WORDFORM_DISCOUNT') != 'Y' && !empty($original_brand_title)) {
            $brand_title = $original_brand_title;
        }

        /*
         * $supplier_id  должен быть битриксовый, потому что в скидках битрикс выбирает свой ID
         * TODO: закешировать или переделать этот алгоритм, а то он очень часто вызывается и это неоптимально!
         * Лучше сделать свойство IB, которое будет вставлять правильный ID
         */
        if ($supplier_id != '') {
            $supplier_id = $this->supplier_ids[$supplier_id];
        }

        /*
         * Выборка скидок из инфоблока согласно составленному фильтру
         */
        $applied_discounts = array();
		
		$temp = array();
		$temp_main_branch = array();
		
		
		$iblockId = $this->iblock_id;

        // Отсортируем элементы так, чтобы вначале применялись скидки и наценки главного филиала, затем всех остальных
        foreach ($this->discounts as $discount) {
            if ($discount['PROPS']['main_dealer'] == 'Y') {
                $temp_main_branch []= $discount;
            } else {
                $temp[] = $discount;
            }
        }
 		
		unset($this->discounts);
		
		if (!empty($temp_main_branch) && !empty($temp)) {
			$this->discounts = array_merge($temp_main_branch, $temp);
		} elseif (!empty($temp_main_branch) && empty($temp)) {
			$this->discounts = $temp_main_branch;
		} elseif (empty($temp_main_branch) && !empty($temp)) {
			$this->discounts = $temp;
		}

		foreach ($this->discounts as $discount) {
		
			/*
        	 * Артикул
        	 
        	$filter_articles = (array) $discount['PROPS']['article'];
        	$filter_articles = array_map('mb_strtolower', $filter_articles);
        	$filter_articles = array_map('trim', $filter_articles);
        	$filter_articles = array_map('LinemediaAutoPartsHelper::clearArticle', $filter_articles);
        	$filter_articles = array_filter($filter_articles);*/
	        if (count($discount['PROPS']['article']) > 0) {
	        	$article = strtolower($article);
		        if (!in_array($article, $discount['PROPS']['article'])) {
		        	continue;
                }
	        }
	        
	        
	        if($this->branch_id) {
	        	if($discount['PROPS']['branch_owner'] != $this->branch_id) {
		        	continue;
	        	}
	        }

	        /*
        	 * Наименование производителя
        	
        	$filter_brand_titles = (array) $discount['PROPS']['brand_title'];
        	$filter_brand_titles = array_map('mb_strtoupper', $filter_brand_titles);
        	$filter_brand_titles = array_map('trim', $filter_brand_titles);
        	$filter_brand_titles = array_filter($filter_brand_titles); */
	        if (count($discount['PROPS']['brand_title']) > 0) {
	        	$brand_title = trim(strtoupper($brand_title));
		        if (!in_array($brand_title, $discount['PROPS']['brand_title'])) {
		        	continue;
                }
	        }

	        /*
        	 * Группа пользователей
        	
        	$filter_user_groups = (array) $discount['PROPS']['user_group'];
        	$filter_user_groups = array_filter($filter_user_groups); */
	        if (count($discount['PROPS']['user_group']) > 0) {
		        if (count(array_intersect($groups, $discount['PROPS']['user_group'])) == 0) {
		        	continue;
                }
	        }

	        /*
        	 * Пользователь
        	
        	$filter_user_ids = (array) $discount['PROPS']['user_id'];
        	$filter_user_ids = array_filter($filter_user_ids); */
	        if (count($discount['PROPS']['user_id']) > 0) {
		        if (!in_array($user_id, $discount['PROPS']['user_id'])) {
		        	continue;
                }
	        }

	        /*
        	 * Поставщик
        	
        	$filter_supplier_ids = (array) $discount['PROPS']['supplier_id'];
        	$filter_supplier_ids = array_map('mb_strtolower', $filter_supplier_ids);
        	$filter_supplier_ids = array_filter($filter_supplier_ids); */
	        if (count($discount['PROPS']['supplier_id']) > 0) {
	        	$supplier_id = strtolower($supplier_id);
		        if (!in_array($supplier_id, $discount['PROPS']['supplier_id'])) {
		        	continue;
                }
	        }

	        /*
        	 * Минимальная базовая цена
        	
        	$filter_min_price = (float) $discount['PROPS']['price_min']; */
	        if ($discount['PROPS']['price_min'] > 0) {
		        if ($base_price < $discount['PROPS']['price_min']) {
		        	continue;
                }
	        }

	        /*
        	 * Максимальная базовая цена
        	
        	$filter_max_price = (float) $discount['PROPS']['price_max']; */
	        if ($discount['PROPS']['price_max'] > 0) {
		        if ($base_price > $discount['PROPS']['price_max']) {
		        	continue;
                }
	        }
			

			
			/**
			* Кастомные поля прайслиста
			*/            
			if (count($discount['PROPS']['price_fields']) > 0) {
		        foreach($discount['PROPS']['price_fields'] AS $price_field_filter) {
			        $val = $this->part->get($price_field_filter['CODE']);
                   
			        if (!in_array($val, $price_field_filter['VALUE'])) {
			        	continue 2;
	                }
		        }
		        
	        }

			

			/*
			* Событие для других модулей.
			* Если модуль возвращает false - пропускаем скидку.
			*/
			if(!isset(self::$cache['events']['OnSaleDiscountsCheck'])) {
				self::$cache['events']['OnSaleDiscountsCheck'] = array();
				$events = GetModuleEvents("linemedia.auto", "OnSaleDiscountsCheck");
				while ($arEvent = $events->Fetch()) {
					self::$cache['events']['OnSaleDiscountsCheck'][] = $arEvent;
				}
			}
            
            $events = self::$cache['events']['OnSaleDiscountsCheck'];
            foreach ($events AS $arEvent) {
	            $result = ExecuteModuleEventEx(
                    $arEvent,
                    array(
                        &$discount,
                        &$user_id,
                        &$groups,
                        &$article,
                        &$brand_title,
                        &$supplier_id,
                        &$base_price,
                        &$this->debug,
                        &$this->external,
                        $this->part
                    )
                );
                if (!$result) {
                    continue 2;
                }
            }
			
	        /*
	         *  Если мы до сюда дошли, значит скидка подходит.
	         */
	        $applied_discounts[] = $discount;
        }
		
        /*
         * Нет подходящих скидок
         */
        if (count($applied_discounts) == 0) {
            return $price;
        }

        /*
         * Скидка - в процентах от наценки поставщика
         */
         $supplier_id = $this->part->get('supplier_id');
        if(!isset(self::$cache['markups'][$supplier_id])) {
	        $supplier = new LinemediaAutoSupplier($this->part->get('supplier_id'));
	        self::$cache['markups'][$supplier_id] = $markup = (float) $supplier->get('markup');
        } else {
	        $markup = self::$cache['markups'][$supplier_id];
        }

        /*
         * Пройдём по каждой скидке
         */
        foreach ($applied_discounts as $discount) {

            $discount_type_id = (int) $discount['PROPS']['discount_type'];
            $discount_type = $this->discount_types[$discount_type_id];

            $discount_percent = (float) $discount['PROPS']['discount'];

            switch ($discount_type) {
                /*
                 * Скидка от наценки поставщика
                 */
                case 'SUPPLIER_MARKUP_DISCOUNT':
                    $supplier_markup_value = $base_price / 100 * $markup;
                    $diff = ($supplier_markup_value / 100) * $discount_percent;
                    $new_price = $price - $diff;

                    // ЧПН отладка пересчёта
                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_SUPPLIER_MARKUP_DISCOUNT', array('#DISCOUNT#' => $discount_percent, '#MARKUP#' => $supplier_markup_value. ' ('.$markup.'%)', '#DIFF#' => $diff, '#RESULT#' => $new_price, '#DISCOUNT_NAME#' => $discount['NAME'], '#DISCOUNT_ID#' => $discount['ID'], '#MARKUP_VALUE#' => $supplier_markup_value));
                    $price = $new_price;

                    break;

                /*
                 * Скидка от конечной цены
                 */
                case 'FINAL_PRICE_DISCOUNT':
                    $new_price = $price - ($price / 100) * $discount_percent;

                    // ЧПН отладка пересчёта
                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_FINAL_PRICE_DISCOUNT', array('#DISCOUNT#' => $discount_percent, '#RESULT#' => $new_price, '#DISCOUNT_ID#' => $discount['ID'], '#DISCOUNT_NAME#' => $discount['NAME'], '#MINUS#' => ($price / 100) * $discount_percent));
                    $price = $new_price;

                    break;

                /*
                 * Наценка от базовой цены
                 */
                case 'BASE_PRICE_MARKUP':
                    $new_price = $price + ($base_price / 100) * $discount_percent;

                    // ЧПН отладка пересчёта
                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_BASE_PRICE_MARKUP', array('#DISCOUNT#' => $discount_percent, '#RESULT#' => $new_price, '#DISCOUNT_ID#' => $discount['ID'], '#DISCOUNT_NAME#' => $discount['NAME']));
                    $price = $new_price;

                    break;

                /*
                 * Наценка от просчитанной цены
                 */
                case 'CALCULATED_PRICE_MARKUP':
                    $new_price = $price + ($price / 100) * $discount_percent;

                    // ЧПН отладка пересчёта

                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_CALCULATED_PRICE_MARKUP', array('#DISCOUNT#' => $discount_percent, '#RESULT#' => $new_price, '#DISCOUNT_ID#' => $discount['ID'], '#DISCOUNT_NAME#' => $discount['NAME']));

                    $price = $new_price;

                    break;
            }


            /*
             * Событие для других модулей.
             * Расчет скидки.
             */
            
            
            if(!isset(self::$cache['events']['OnSaleDiscountCalculate'])) {
	            self::$cache['events']['OnSaleDiscountCalculate'] = array();
	            $events = GetModuleEvents("linemedia.auto", "OnSaleDiscountCalculate");
	            while ($arEvent = $events->Fetch()) {
	            	self::$cache['events']['OnSaleDiscountCalculate'][] = $arEvent;
	            }
            }
            $events = self::$cache['events']['OnSaleDiscountCalculate'];
            
            foreach ($events AS $arEvent) {
                $result = ExecuteModuleEventEx(
                    $arEvent,
                    array(
                        &$this->part,
                        &$discount,
                        &$price,
                        &$base_price,
                        &$user_id,
                        &$groups,
                        &$article,
                        &$brand_title,
                        &$supplier_id,
                        &$this->debug,
                        &$this->external
                    )
                );
            }

            /*
             * Не применять дальше
             */
            if ($discount['PROPS']['last'] == 'Y') {
            	// ЧПН отладка пересчёта
                $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_LAST');
            	break;
            }
			
        }

        return $price;
    }
	
	
    public function getDebug()
    {
	    return $this->debug;
    }
}

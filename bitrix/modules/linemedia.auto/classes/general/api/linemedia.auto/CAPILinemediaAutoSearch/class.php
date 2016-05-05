<?php

/**
 * Linemedia API
 * API module
 * Search class
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
class CAPILinemediaAutoSearch extends CAPIFrame
{

	public function __construct()
	{
		parent::__construct();
	}

	
	/*
	 * Поиск по запчастям
	 */
    public function LinemediaAutoSearch_Search($query, $brand_title = '', $extra = array(), $type = false) 
    {
    	/*
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);

        // флаг для классов поиска, указывающий что нужно кешировать результат, для последующего добавления в корзину
        define('LM_API_QUERY', true);
    	
    	/*
		 * Создаём объект поиска.
		 */
		try {
		    $search = new LinemediaAutoSearch();
		} catch (Exception $e) {
		    $this->error($e->GetMessage());
		}
		
		/*
		 * Устанавливаем поисковый запрос.
		 */
		$search->setSearchQuery($query);
		
		/*
		 * Устанавливаем бренд.
		 */
		if ($brand_title != '') {
		    $search->setSearchCondition('brand_title', $brand_title);
		}  
		
		/*
		 * Установить extra
		 */
		if (count($extra) > 0) {
		    $search->setSearchCondition('extra', $extra);
		}
		
		/*
		 * Установим тип поиска
		 */
		switch($type)
		{
			case 'SEARCH_GROUP':
				$type = LinemediaAutoSearch::SEARCH_GROUP;
			break;
			case 'SEARCH_PARTIAL':
				$type = LinemediaAutoSearch::SEARCH_PARTIAL;
			break;
			default:
				$type = LinemediaAutoSearch::SEARCH_SIMPLE;
		}
		$search->setType($type);
		
		
		
		/*
		 * Выполняем запрос.
		 */
		try {
		    $search->execute();
		} catch (Exception $e) {
		    $this->error($e->GetMessage());
		}
		
		/*
		 * Ошибки от модулей.
		 */
		$modules_exceptions = $search->getThrownExceptions();
		
		/*
		 * Что пришло в ответ?
		 */
		switch ($search->getResultsType())
		{
		    case 'catalogs':
		        $catalogs = $search->getResultsCatalogs();
		        foreach ($catalogs as $id => $catalog) {
		            $catalogs[$id]['url'] = LinemediaAutoUrlHelper::getPartUrl(
		                array(
		                    'article' => $query, // (!empty($catalog['article'])) ? ($catalog['article']) : ($arParams['QUERY']),
		                    'brand_title' => $brand_title,
		                    'extra' => $extra,
		                ),
		                $type
		            );
		        }
		        
		        
		        $result = array();
		        foreach ($catalogs as $i => $catalog) {
			        $this->encodeArray($catalogs[$i]['extra']);
			        
			        /*
		    		 * Bitrix путает обязательность параметров
		    		 * /bitrix/modules/webservice/classes/general/soap/soapcodec.php 334
		    		 * Обращение # 337967
		    		 */
		    		foreach ($catalogs[$i]['extra'] as $y => $extra) {
			    		$catalogs[$i]['extra'][$y]['CODE'] = trim($extra['CODE']);
			    		$catalogs[$i]['extra'][$y]['VALUE'] = trim($extra['VALUE']);
		    		}
			        
			        $this->formatResponse($catalogs[$i], 'Struct_LinemediaAuto_SearchCatalog');
		        }
		        $catalogs = array_values($catalogs);
		        
		        $response = array(
		        	'type' => 'catalogs',
		        	'catalogs' => $catalogs,
		        	'parts' => array()
		        );
		        
		        $this->formatResponse($response, 'Struct_LinemediaAuto_SearchResults');
		        
		        return $response;
		        
		    case '404':
		    	$response = array(
		        	'type' => '404',
		        	'catalogs' => array(),
		        	'parts' => array()
		        );
		    	break;
		    case 'parts':
		    
		        $source_parts = $search->getResultsParts();
		        
		        /*
		         * Сортировка групп деталей.
		         */
		        asort($source_parts);
		        if (isset($source_parts['analog_type_N'])) {
		            $N['analog_type_N'] = $source_parts['analog_type_N'];
		            unset($source_parts['analog_type_N']);
		            $source_parts = array_merge_recursive($N, $source_parts);
		        }
		        
		        /*
		         * Пробежимся по запчастям и ...
		         */
		        foreach ($source_parts as $group_id => $parts) {

		            foreach ($parts as $i => $part) {
		                /*
		                 * Сформируем путь для покупки
		                 */
		                $part['part_id']        = (int) $part['id'];
		                $part['supplier_id']    = (string) $part['supplier_id'];

		                /*
		                 * Объект запчасти
		                 */
		                $part_obj = new LinemediaAutoPart($part['id'], $part);

		                /*
		                 * Посчитаем цену товара
		                 */
		                $price = new LinemediaAutoPrice($part_obj);
                        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
                            $price_calc = $price->calculate();
                        } else {
                            $price_calc = $part['price'];
                        }

		                $source_parts[$group_id][$i]['price'] = (float) $price_calc;
		                $source_parts[$group_id][$i]['currency'] = $price->getCurrency();

                        /*
                         * Для отладки добавим цену товара в линк
                         * Цена из линка при покупке НЕ учитывается
                        */
                        $source_parts[$group_id][$i]['part_buy_url'] .= '&p=' . $price_calc;
                        $source_parts[$group_id][$i]['part_buy_url'] .= '&ch_id=' . $source_parts[$group_id][$i]['chain_id'];
		                	                
		                /*
		                 * Бренд
		                 */
		                $source_parts[$group_id][$i]['brand']['title'] = $part['brand_title'];
		                
		                /*
		                 * Поставщик
		                 */
		                $supplier = new LinemediaAutoSupplier($part['supplier_id']);
		                $source_parts[$group_id][$i]['supplier'] = $supplier->getArray();
		                
		                /*
		                 * Вес
		                 */
		                $source_parts[$group_id][$i]['weight'] = (float) $parts[$group_id][$i]['weight'];
		                
		                /*
		                 * Срок доставки
		                 */
		                if (!$source_parts[$group_id][$i]['delivery_time']) {
		                    $source_parts[$group_id][$i]['delivery_time'] = (int) $supplier->get('delivery_time');
		                } else {
		                    $source_parts[$group_id][$i]['delivery_time'] += (int) $supplier->get('delivery_time');
		                }

                        //$source_parts[$group_id][$i]['extra'] = $part['extra'];
		                
		                /*
		                 * URL поиска запчасти.
		                 */
		                $part_search_url = LinemediaAutoUrlHelper::getPartUrl(array(
		                    'article'     => $part['article'],
		                    'brand_title' => $part['brand_title'],
		                    'extra'       => $part['extra'],
		                ));
		                $source_parts[$group_id][$i]['part_search_url'] = $part_search_url;
		                
		                
		                $this->formatResponse($source_parts[$group_id][$i], 'Struct_LinemediaAuto_SearchPart');
		            }
		        }
		        
		        /*
		        * Переделаем массив на правильный тип данных
		        */
		        $result = array();
		        foreach ($source_parts as $group => $parts) {
			        $result[] = array(
			        	'analog_type' => $group,
			        	'parts' => $parts,
			        );
		        }
		        
		        $response = array(
		        	'type' => 'parts',
		        	'parts' => $result,
		        	'catalogs' => array()
		        );
		        
		        return $response;
		    	break;
		    default:
		        $this->error('System error 1');
		        break;
		}		
    }

    /**
     * @param $request
     * @param string $priority
     * @param bool $include_analogs
     * @return array|string
     * @throws Exception
     */
    public function LinemediaAutoSearch_groupSearch($request, $priority = 'price', $include_analogs = false) {

        global $USER;

        $suppliers = array();

        /*
        * Проверка прав доступа к функции
        */
        $this->checkPermission(__METHOD__);

        if(!CModule::IncludeModule('sale'))
            $this->error('no sale module');

        if(!CModule::IncludeModule('linemedia.auto'))
            $this->error('no linemedia.auto module');

        if(!is_array($request) || count($request) < 1) {
            throw new Exception('Empty request');
        }

        // флаг для классов поиска, указывающий что нужно кешировать результат, для последующего добавления в корзину
        define('LM_API_QUERY', true);

        $spares = array();
        foreach($request as $tmp) {
            $tmp['article'] = trim(ToLower($tmp['article']));
            $tmp['brand'] = trim(ToLower($tmp['brand']));
            $tmp['quantity'] = intval($tmp['quantity']) > 0 ? intval($tmp['quantity']) : 1;

            $spares[$tmp['article'] . '|' . $tmp['brand']] = $tmp;
        }

        $queryArticle = join(',', array_keys($spares));

        $search = new LinemediaAutoSearchGroup();
        $arSuppliers = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', 'PROPERTY_api' => false), false, false, array('ID', 'PROPERTY_supplier_id'), 'supplier_id');
        $search->setSuppliers(array_keys($arSuppliers));
        $retrievedSpares = $search->searchLocalDatabaseForPart(array('article' => $queryArticle), true);

        if(is_array($retrievedSpares) && count($retrievedSpares) > 0) {

            $order = array();
            foreach($retrievedSpares as $id => $spare) {
                if($priority == 'price') {
                    $order[$id] = $spare['price'];
                } else if($priority == 'delivery') {
                    $order[$id] = $spare['delivery_time'];
                } else {
                    $order[$id] = 1;
                }
            }

            asort($order);

            foreach($spares as $key => $item) {

                $spares[$key]['result'] = array();
                $cnt = 0;
                foreach($order as $spare_key => $val) {

                    $part = $retrievedSpares[$spare_key];
                    if(ToLower($part['article']) == ToLower($item['article']) &&
                        (empty($item['brand']) || ToLower($part['brand_title']) == ToLower($item['brand']))) {

                        $part_obj = new LinemediaAutoPart($part['id'], $part);

                        /*
                         * Посчитаем цену товара
                        */

                        $price = new LinemediaAutoPrice($part_obj);
                        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
                            $price_calc = $price->calculate();
                        } else {
                            $price_calc = $part['price'];
                        }
                        $part['price_src'] = $price_calc;

                        $currency = $price->getCurrency();
                        $user_currency = $USER->GetParam('CURRENCY');
                        if(strlen($user_currency) == 3 && $currency != $user_currency) {
                            $currency = $user_currency;
                            $price_calc = LinemediaAutoPrice::userPrice($price_calc);
                        }

                        $part['price'] = CurrencyFormat($price_calc, $currency);

                        $need_quantity = (int)$item['quantity'] - $cnt;
                        $quantity = (int)$part['quantity'];

                        if($quantity >= $need_quantity) {
                            $part['customer_quantity'] = $need_quantity;
                            $cnt += $need_quantity;
                        } else {
                            $part['quantity'] = (int) $part['quantity'];
                            $part['customer_quantity'] = $part['quantity'];
                            $cnt += $quantity;
                        }

                        /*
                         * Поставщик
                        */
                        if(!isset($suppliers[$part['supplier_id']])) {
                            $supplier = $suppliers[$part['supplier_id']] = new LinemediaAutoSupplier($part['supplier_id']);
                        } else {
                            $supplier = $suppliers[$part['supplier_id']];
                        }

                        $title = $supplier->get('visual_title');
                        if(empty($title)) $title = $supplier->get('NAME');

                        $part['supplier_title'] = $title;

                        /*
                         * Срок доставки
                        */
                        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
                            if (!$part['delivery_time']) {
                                $part['delivery_time'] = (int) $supplier->get('delivery_time');
                            } else {
                                $part['delivery_time'] += (int) $supplier->get('delivery_time');
                            }
                        }
                        $part['delivery'] = $part['delivery_time'];

                        /*
                         * Пересчитаем в дни
                        */
                        $delivery_time = $part['delivery_time'];
                        if ($delivery_time >= 24) {
                            $days = round($delivery_time / 24);
                            $delivery_time = '&asymp; ' . $days . ' ' . GetMessage('LM_AUTO_MAIN_DAYS');
                        } else {
                            $delivery_time .= ' ' . GetMessage('LM_AUTO_MAIN_HOURS');
                        }
                        $part['delivery_time'] = $delivery_time;

                        $spares[$key]['parts'][] = $part;

                        if($cnt >= $item['quantity']) break;
                    }
                }
            } // foreach

            //TODO: доберем аналогами, если можно и нужно
            if($include_analogs) {

            }

            // remove keys
            $spares = array('result' => array_values($spares));


            $this->formatResponse($spares, 'Struct_LinemediaAuto_groupSearchResult');
            return $spares;

        } else {

            return 'not found';
        }
    }
}

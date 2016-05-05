<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Module events
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoRemoteSuppliersEventLinemediaAuto
{
	
	
	/**
	* Статический кеш
	*/
	static $cache;
	
    /**
     * Добавление товаров от удаленных поставщиков.
     * @param $search_conditions
     * @param $articles_to_search
     * @param $catalogs_to_search
     * @param $search_article_results
     * @param $type
     * @return bool
     */
    public function OnSearchExecuteBegin_addRemoteSuppliers(&$search_conditions, &$articles_to_search, &$catalogs_to_search, &$search_article_results, $type)
    {
    	/*
    	 * Игнорировать удалённых поставщиков
    	 * Используется при запросах к данному клиенту как удалённому поставщику
    	 */
    	if (defined('LM_AUTO_IGNORE_REMOTE_SUPPLIERS') && LM_AUTO_IGNORE_REMOTE_SUPPLIERS == true) {
    		return;
        }
    	
    	/*
    	 * Гугл бот не может использовать удалённых поставщиков, потому что они платные
    	 */
    	if (LinemediaAutoUserHelper::isSearchRobot()) {
	    	return false;
    	}

        /*
         * Включим файловый лог
         */
        //LinemediaAutoDebug::$filename = $_SERVER['DOCUMENT_ROOT'] . '/lm_remote_debug.txt';
    	
    	/*
    	 * Если тип поиска групповой или по части артикула, не ищем вудаленных поставщиках.
    	 */
    	if (in_array($type, array(LinemediaAutoSearch::SEARCH_GROUP, LinemediaAutoSearch::SEARCH_PARTIAL))) {
    		return;
    	}
    	
    	/*
    	 * Почистим артикул
    	 */
    	$query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);
        if(empty($query)) {
            return;
        }
        
        /*
        * подготовим параллельный запуск запросов
        */
    	$thread = new LinemediaAutoSuppliersThread();
    	
    	
    	/*
         * Словоформы.
         */
        $wordforms = new LinemediaAutoWordForm();
        
        
        /*
         * Использование кроссов от удаленных поставщиков.
         */
        $use_remote_crosses = (COption::GetOptionString('linemedia.autoremotesuppliers', 'LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES', 'N') == 'Y');
        
        
        /*
         * Необходимо подключить к результатам поиска всех поставщиков, которые активны и используют API
         */
        $suppliers = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', '!PROPERTY_api' => false));

        $jobs = array();
                
        /*
        * Массив для хранения кешей ответов от удалённых поставщиков (экономим треды)
        */
        $cached_jobs = array();
        
        foreach ($suppliers as $supplier) {
            /*
             * Сохраним ID поставщиков
             */
            $prop_api = $supplier['PROPS']['api']['VALUE'];
            if (is_array($prop_api)) {
                $api = $prop_api['LMRSID'];//.'_'.$supplier['ID'];
            } else {
                $api = $prop_api;
                $prop_api = array('LMRSID' => $api, 'cache_time' => '');
            }
            $supplier_id = $supplier['PROPS']['supplier_id']['VALUE'];

	        $supplier_timeout = abs((int) $prop_api['timeout']);
	        $supplier_timeout = $supplier_timeout > 0 ? $supplier_timeout : 30;

            /*
             * Таймаут кеширования для поставщика. Берется только из настроек данного поставщика.
             */
            //$cache_time = (int) COption::GetOptionString("linemedia.autoremotesuppliers", "cache_time", "0");
            $cache_time = intval($prop_api['cache_time']) > 0 ? intval($prop_api['cache_time'])*60 : 0;
            //$cache_time *= 60;
            
            /*
	         * Вывод отладочной информации
	         */
	        LinemediaAutoDebug::add($api . ' added', false, LM_AUTO_DEBUG_WARNING);
                        
	        /*
	         * Используем тот бренд, что вернул именно этот поставщик
	         */
	        $brand_title = isset($search_conditions['extra'][hash("crc32", $api, false).'bt']) ? $search_conditions['extra'][hash("crc32", $api, false).'bt'] : $search_conditions['brand_title'];
	        
            /*
             * Проверим наличие словоформ.
             */

			/*
			 * Если бренд находится в группе словоформ, то выбираем название группы и подменяем
			 * текущий бренд на него, чтобы поиск осуществлялся по всем брендам в словоформе.
			 */

			$brand_title = (string) $wordforms->getBrandGroup($brand_title) ?: $brand_title;

            $brand_titles = (array) $wordforms->getGroupWordforms($brand_title);
            
            
            /*
            * $brand_title может быть не один, если он тоже попал под словоформы на этапе группировки каталога
            *  [avdmotorsbt] => Array
            *    (
            *        [0] => TOYOTA
            *        [1] => DAIHATSU
            *        [2] => LEXUS
            *    )
            */
            if (is_array($brand_title)) {
            	$brand_titles = array_merge($brand_titles, $brand_title);
            } else {
	            $brand_titles []= $brand_title;
            }
            
            $brand_titles = array_unique($brand_titles);
            
	        /*
	         * Бывает такое, что надо сделать несколько запросов к одному поставщику
	         * Например, автопитер находит отдельно TRW и LUCAS/TRW
	         */
        	for ($i = 0; $i < count($brand_titles); $i++) {
		        /*
	        	 * Переделаем массивы в экстре на нормальные значения для каждого из запросов
	        	 */
	        	$extra = $search_conditions['extra'];
		        foreach ($extra as $k => $v) {
			        if (is_array($v)) {
			        	$extra[$k] = $v[$i];
                    }
		        }

		        /*
		        * у нас теперь может быть несколько профилей для одного поставщика, поэтому добавляем supplier_id
                */
		        $job_code = (count($brand_titles) == 1) ? $api. '_' .$supplier_id : $api . '_' .$supplier_id.'_'. $i;
		        
		        
		        $jobs[$job_code] = array(
		        	'supplier_id' => $supplier_id,
		        	'api' 		  => $api,
		        );

                /*
                 * Работа с кешем
                 */
                $cache_id = $job_code . serialize($search_conditions);

                //храним проинициализированный объект кеша в массиве
                $cached_jobs[$job_code] = array(
                    'data' => false,
                    'cache_id' => $cache_id,
                    'cache_time' => $cache_time,
                    'cache_object' => new CPHPCache(),
                );

                $cache = &$cached_jobs[$job_code]['cache_object'];
                
                if ($cache_time > 0 && $cache->InitCache($cache_time, $cache_id, '/lm_auto/remote_suppliers/'.$supplier_id.'/')) {

                    $cached_jobs[$job_code]['data'] = $cache->GetVars();

                } else {

                    // #8825 добавим сериализованный $prop_api
                    $thread->addJob($job_code, '/bitrix/modules/linemedia.autoremotesuppliers/exec/search_remote_supplier.php', array(
                        $api,
                        $supplier_id,
                        $search_conditions['query'],
                        $brand_titles[$i],
                        json_encode($extra),
                        json_encode($prop_api)
                    ),
	                    $supplier_timeout
                    );

                    // старый вызов
//                    $thread->addJob($job_code, '/bitrix/modules/linemedia.autoremotesuppliers/exec/search_remote_supplier.php', array(
//                        $api,
//                        $supplier_id,
//                        $search_conditions['query'],
//                        $brand_titles[$i],
//                        json_encode($extra)
//                    ));
                }

		        /*
		         * Вывод отладочной информации
		         */
		         LinemediaAutoDebug::add($api . ' request', '<b>' . $search_conditions['query'] . '</b> [<b>'.$brand_titles[$i].'</b>]<br>'.print_r($extra, 1), LM_AUTO_DEBUG_WARNING);
	        }
        }
            
        
        /*
         * Запуск скриптов
         */
        $thread->execute();
        
        LinemediaAutoDebug::add('Remote suppliers requests finished');
        
        /*
         * Получаем результат
         */
        $results = $thread->getResults();
       
        /*
         * Добавим ключи из кеша
         */
        foreach($cached_jobs as $job_code => $arCache) {
            // если в кеше есть данные то добавляем ключ с пустым значением, что значит если в $results есть пустое значение - будем проверять его наличие в кеше
            if($arCache['data']) $results[$job_code] = false;
        }


        /*
         * Полученные удаленные аналоги.
         */
        $remote_analogs = array();
        
        foreach ($results as $job_code => $response) {
        	
        	$job 			= $jobs[$job_code];
        	$supplier_id 	= $job['supplier_id'];
        	$api 			= $job['api'];
        	
            $remote_analogs[$api] = array();

            if(!$response) {
                // может все уже есть в кеше ?
                $api_result = $cached_jobs[$job_code]['data'];

                $jsonError = false;
            } else {
                /*
                 * Правильный ли JSON вернулся или ошибка?
                 * Если вернулся не JSON - либо сработал тайсаут, либо парс еррор или что-то ещё нереальное
                 */
                try {
                    $api_result = json_decode($response, true);
                } catch (Exception $e) {
                    /*if (trim($response) == '') {
                        LinemediaAutoDebug::add($job_code . ' timeout', false, LM_AUTO_DEBUG_ERROR);
                    } else {
                        LinemediaAutoDebug::add($job_code . ' ' . $response, false, LM_AUTO_DEBUG_ERROR);
                    }*/
                    continue;
                } // catch
                // Обработка ошибок парсинга ответа json.
                $jsonError = json_last_error();
            }

            if (!empty($jsonError)) {
                switch ($jsonError) {
                    case JSON_ERROR_DEPTH:
                        LinemediaAutoDebug::add($job_code . ' maximum stack depth exceeded', $response, LM_AUTO_DEBUG_ERROR);
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        LinemediaAutoDebug::add($job_code . ' underflow or the modes mismatch', $response, LM_AUTO_DEBUG_ERROR);
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        LinemediaAutoDebug::add($job_code . ' unexpected control character found', $response, LM_AUTO_DEBUG_ERROR);
                        break;
                   case JSON_ERROR_SYNTAX:
                        LinemediaAutoDebug::add($job_code . ' syntax error', $response, LM_AUTO_DEBUG_ERROR);
                        break;
                    case JSON_ERROR_UTF8:
                        LinemediaAutoDebug::add($job_code . ' malformed UTF-8 characters', $response, LM_AUTO_DEBUG_ERROR);
                        break;
                    default:
                        LinemediaAutoDebug::add($job_code . ' unknown response error', $response, LM_AUTO_DEBUG_ERROR);
                        break;
                }
                continue;
            }
            
	        // Вернулась обработанная ошибка
	        if ($api_result['error']) {
		        LinemediaAutoDebug::add($api_result['text'], false, $api_result['error_level']);
		        continue;
	        }
	        
	        
	        // Если даже JSON распарсился, не факт, что он правильный
	        if (!is_array($api_result)) {
	        	LinemediaAutoDebug::add($job_code . ' empty response' . $response, false, LM_AUTO_DEBUG_ERROR);
	        	continue;
            } else { // Закешируем результат

                if(!$cached_jobs[$job_code]['data'] && $cached_jobs[$job_code]['cache_time'] > 0) {

                    $cache = &$cached_jobs[$job_code]['cache_object'];
                    $cache->StartDataCache();
                    $cached_data = $api_result;
                    $cached_data['time'] = 0;
                    $cache->EndDataCache($cached_data);
                }
            }

	        // Время, затраченное на выполнение запроса без учёта оверхеда от многопоточногости
	        $time = '<b>' . number_format($api_result['time'], 2) . ' s.</b>';
	        
	        
            // Отправлять ли данные по аналогам в API?
            $sendapi[$api]        = true;
            $remote_brands[$api]  = '';
            
	        /*
	         * Есть ли каталоги?
	         */
	        if (isset($api_result['catalogs'])) {
	        	$catalogs = $api_result['catalogs'];
                
                // Если каталогов больше одного - не отправляем данные в API.
                if (count($catalogs) > 1 && empty($search_conditions['brand_title'])) {
                    $sendapi[$api] = false;
                }
	        	foreach ($catalogs as $i => $catalog) {
	        		if (!defined('BX_UTF') || BX_UTF !== true) {
						$catalogs[$i]['article'] 	 = iconv('UTF-8', 'CP1251', $catalogs[$i]['article']);
						$catalogs[$i]['title']   	 = iconv('UTF-8', 'CP1251', $catalogs[$i]['title']);
						$catalogs[$i]['brand_title'] = iconv('UTF-8', 'CP1251', $catalogs[$i]['brand_title']);
					}

		        	$catalogs[$i]['source'] = $job_code;
		        	$catalogs[$i]['extra'][hash("crc32", $api, false). 'bt'] = $catalogs[$i]['brand_title'];
	        	}
	        	
		        $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
		        LinemediaAutoDebug::add("$job_code - catalogs [$time]", print_r($api_result['catalogs'], 1), LM_AUTO_DEBUG_WARNING);
	        }
	        
	        /*
	         * Есть ли запчасти?
	         */
	        if (isset($api_result['parts'])) {
	        	$groups = $api_result['parts'];
                
	        	foreach ($groups as $group => $parts) {
	        	    $grouptype = str_replace('analog_type_', '', $group);
                    
                    if ($grouptype == 'N') {
                        $first_part   = reset($parts);
                        $remote_brands[$api] = $first_part['brand_title'];
                    }
                    
		        	foreach ($parts as $i => $part) {
		        		if (!defined('BX_UTF') || BX_UTF !== true) {
							$groups[$group][$i]['article'] 		= iconv('UTF-8', 'CP1251', $groups[$group][$i]['article']);
							$groups[$group][$i]['title']   		= iconv('UTF-8', 'CP1251', $groups[$group][$i]['title']);
							$groups[$group][$i]['brand_title']  = iconv('UTF-8', 'CP1251', $groups[$group][$i]['brand_title']);


					        /*
					         * For Ixora 25.07.14 Nazarkov Ilya
					         */
					        $regionName = $groups[$group][$i]['extra']['regionname'];
					        if(isset($regionName) && $regionName) {
						        $groups[$group][$i]['extra']['regionname'] = iconv('UTF-8', 'CP1251', $regionName);
					        }
				        }
                        
                        // Пропускаем оригинальные артикулы.
                        if ($grouptype != 'N') {
                            $remote_item = array(
                                'title'         => $part['title'],
                                'article'       => $part['article'],
                                'brand_title'   => $part['brand_title'],
                                'analog_type'   => $grouptype,
                                'analog-source' => $part['data-source'],
                                'extra'         => $part['extra']
                            );
                            if ($sendapi[$api]) {
                                $remote_analogs[$api] []= $remote_item;
                            } else {
                                unset($remote_brands[$api]);
                            }
                            
                            // Использовать кроссы внешних поставщиков.
                            if ($use_remote_crosses) {
                                $key = LinemediaAutoPartsHelper::clearArticle($part['article']) . '|' . LinemediaAutoPartsHelper::clearBrand($part['brand_title']);
                            	if(!isset($articles_to_search[$key])) {
                                	$articles_to_search[$key] = $remote_item;
                                }
                            }
                        }
			        	$groups[$group][$i]['supplier_id'] = $supplier_id;
		        	}
	        	}
                
                
                
	        	$search_article_results = array_merge_recursive($search_article_results, $groups);
	        	LinemediaAutoDebug::add("$job_code - parts [$time]", print_r($groups, 1), LM_AUTO_DEBUG_WARNING);
		    }
		    if (isset($api_result['404'])) {
	        	LinemediaAutoDebug::add("$job_code - no parts found [$time]", false, LM_AUTO_DEBUG_WARNING);
		    }
        } // END FOREACH
        
        
        /*
         * Аналоги сторонних поставщиков.
         */
        $apiremote = array(
            'query'     => $search_conditions,
            'brands'    => $remote_brands,
            'analogs'   => $remote_analogs,
        );
        
        // Проверим есть ли аналоги от внешних поставщиков.
        $sendapi = false;
        foreach ($remote_analogs as $remote_analog) {
            if (!empty($remote_analog)) {
                $sendapi = true;
                break;
            }
        }
        
        if ($sendapi) {
            try {
                $api = new LinemediaAutoApiDriver();
                $api->query('addRemoteSuppliersAnalogs', $apiremote);
            } catch (Exception $e) {
                // nothing...
            }
        }

        /*
         * Выключим файловый лог
         */
        LinemediaAutoDebug::$filename = false;
    }

    /**
     * Загрузка детали
     * @param $part_id
     * @param $obj_data
     * @param $data
     * @param $loaded
     */
    public function OnPartObjectCreate_loadRemotePart($part_id, &$obj_data, &$data, &$loaded)
    {
        if(!isset(self::$cache['suppliers_list'])) {
	    	$suppliers = self::$cache['suppliers_list'] = LinemediaAutoRemoteSuppliersSupplier::getList();
    	} else {
	    	$suppliers = self::$cache['suppliers_list'];
    	}
        
        /*
         * Эта запчасть нам не интересна
         */
        if (!isset($suppliers[$part_id])) {
            return;
        }
        
        /*
         * У удалённых поставщиков вся нужная информация уже содержится в массиве $data
         */
        $loaded = true;
    }

    /**
     * Обработка данных и добавление в корзину.
     * Когда мы кладём товар в корзину, то мы не знаем его цену, потому что она не передаётся через GET
     * Зато у нас есть extra, с помощью которой можно вновь выдернуть всю информацию о запчасти из удалённого поставщика и записать её в массив
     * $additional из которого будет создан объект part
     * @param $part_id
     * @param $supplier_id
     * @param $quantity
     * @param $arFields
     * @param $additional
     * @throws Exception
     */
    public function OnBeforeBasketItemAdd_addRemoteSuppliers(&$part_id, &$supplier_id, &$quantity, &$arFields, &$additional)
    {
        $supplier = new LinemediaAutoSupplier($supplier_id);
        $api = $supplier->get('api');
        
        /*
         * Эта запчасть нам не интересна
         */
        if ($api == '') {
            return;
        }

		/*
		* Эта запчасть от удалённого поставщика, но она в базе из прайса!
		*/
		if($part_id > 0) {
			return;
		}

        $r_supplier = LinemediaAutoRemoteSuppliersSupplier::load($supplier_id);
        
        try {
	        $data = $r_supplier->getPartData($additional);
        } catch (Exception $e) {
	        //LinemediaAutoDebug::add($e, $additional, LM_AUTO_DEBUG_ERROR);
	        throw $e;
        }
        
        
        $data['supplier_id'] = $supplier->get('supplier_id');
        if (isset($data['multiplication_factor']) && intval($data['multiplication_factor']) > 1) {
            if (!is_array($arFields['PROPS'])) $arFields['PROPS'] = array();
            $arFields['PROPS'][] = array('NAME' => GetMessage('LM_AUTO_ORDER_MULTIPLICATION_FACTOR'), 'CODE' => 'multiplication_factor', 'VALUE' => $data['multiplication_factor']);
        }

        if (!defined('BX_UTF') || BX_UTF !== true) {
            $data['article']     = iconv('UTF-8', 'CP1251', $data['article']);
            $data['title']       = iconv('UTF-8', 'CP1251', $data['title']);
            $data['brand_title'] = iconv('UTF-8', 'CP1251', $data['brand_title']);
        }
        $data['quantity'] = 0;
        
        // Добавление данных о сроке доставки.
        $additional['delivery_time'] = $data['delivery_time'];
        
        /*
         * Добавим товар в БД, чтобы с ним можно было работать
         */
        $part = new LinemediaAutoPart(false, $data);
        try {
	        $part_id = $part->save();
        } catch (Exception $e) {
	        throw $e;
        }
    }

    /**
     * Добавление в список удалённых поставщиков.
     * @param $remote_suppliers
     */
    public function OnRemoteSuppliersGet_addSuppliers(&$remote_suppliers)
    {
        $suppliers = LinemediaAutoRemoteSuppliersSupplier::getList();
        foreach ($suppliers as $code => $title) {
            $remote_suppliers[$code] = array(
                'title'             => $title,
                // 'part_classname'    => 'LinemediaAutoRemoteSuppliersPart'
            );
        }
    }

    /**
     * Добавим проверки
     * @param $check
     */
    public function OnRequirementsListGet_addChecks(&$check)
	{
		$add = array();

    	/*
    	 * SOAP
    	 */
    	$add[] = array(
    		'title' => GetMessage('LM_AUTO_SOAP'),
    		'requirements' => GetMessage('LM_AUTO_SOAP_HOWTO'),
    		'status' => class_exists('SoapClient'),
		);
		
		/*
    	 * DOM
    	 */
    	$add[] = array(
    		'title' => GetMessage('LM_AUTO_DOM'),
    		'requirements' => GetMessage('LM_AUTO_DOM_HOWTO'),
    		'status' => class_exists('DOMDocument'),
		);

        /*
         * timeout command
         */
        $ret_code = 0;
        $ret = system("timeout", $ret_code);

        $add[] = array(
            'title' => GetMessage('LM_AUTO_TIMEOUT'),
            'requirements' => GetMessage('LM_AUTO_TIMEOUT_HOWTO'),
            'status' => $ret_code == 125
        );
        
        
        /*
        * PHP console version
        */
        $ret = shell_exec('php -v');
        $ret = explode("\n", $ret);
        $version = preg_match('#(\d).(\d).(\d)#is', $ret[0], $matches);
        $version = $matches[0];
        
        $add[] = array(
            'title' => GetMessage('LM_AUTO_PHP_CONSOLE_VER'),
            'requirements' => GetMessage('LM_AUTO_PHP_CONSOLE_VER_HOWTO', array('#VERSION#' => $version)),
            'status' => version_compare($version, '5.3.0') >= 0,
        );
        
        
        
        /*
        * popen enabled
        */
        $disabled = array_filter(explode(',', ini_get('disable_functions')));
        $add[] = array(
            'title' => GetMessage('LM_AUTO_POPEN_ENABLED'),
            'requirements' => GetMessage('LM_AUTO_POPEN_ENABLED_HOWTO'),
            'status' => !in_array('popen', $disabled),
        );

        
        
        
    	$check['linemedia.autoremotesuppliers'] = $add;
	}
    
}

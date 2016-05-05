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
	* ����������� ���
	*/
	static $cache;
	
    /**
     * ���������� ������� �� ��������� �����������.
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
    	 * ������������ �������� �����������
    	 * ������������ ��� �������� � ������� ������� ��� ��������� ����������
    	 */
    	if (defined('LM_AUTO_IGNORE_REMOTE_SUPPLIERS') && LM_AUTO_IGNORE_REMOTE_SUPPLIERS == true) {
    		return;
        }
    	
    	/*
    	 * ���� ��� �� ����� ������������ �������� �����������, ������ ��� ��� �������
    	 */
    	if (LinemediaAutoUserHelper::isSearchRobot()) {
	    	return false;
    	}

        /*
         * ������� �������� ���
         */
        //LinemediaAutoDebug::$filename = $_SERVER['DOCUMENT_ROOT'] . '/lm_remote_debug.txt';
    	
    	/*
    	 * ���� ��� ������ ��������� ��� �� ����� ��������, �� ���� ���������� �����������.
    	 */
    	if (in_array($type, array(LinemediaAutoSearch::SEARCH_GROUP, LinemediaAutoSearch::SEARCH_PARTIAL))) {
    		return;
    	}
    	
    	/*
    	 * �������� �������
    	 */
    	$query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);
        if(empty($query)) {
            return;
        }
        
        /*
        * ���������� ������������ ������ ��������
        */
    	$thread = new LinemediaAutoSuppliersThread();
    	
    	
    	/*
         * ����������.
         */
        $wordforms = new LinemediaAutoWordForm();
        
        
        /*
         * ������������� ������� �� ��������� �����������.
         */
        $use_remote_crosses = (COption::GetOptionString('linemedia.autoremotesuppliers', 'LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES', 'N') == 'Y');
        
        
        /*
         * ���������� ���������� � ����������� ������ ���� �����������, ������� ������� � ���������� API
         */
        $suppliers = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', '!PROPERTY_api' => false));

        $jobs = array();
                
        /*
        * ������ ��� �������� ����� ������� �� �������� ����������� (�������� �����)
        */
        $cached_jobs = array();
        
        foreach ($suppliers as $supplier) {
            /*
             * �������� ID �����������
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
             * ������� ����������� ��� ����������. ������� ������ �� �������� ������� ����������.
             */
            //$cache_time = (int) COption::GetOptionString("linemedia.autoremotesuppliers", "cache_time", "0");
            $cache_time = intval($prop_api['cache_time']) > 0 ? intval($prop_api['cache_time'])*60 : 0;
            //$cache_time *= 60;
            
            /*
	         * ����� ���������� ����������
	         */
	        LinemediaAutoDebug::add($api . ' added', false, LM_AUTO_DEBUG_WARNING);
                        
	        /*
	         * ���������� ��� �����, ��� ������ ������ ���� ���������
	         */
	        $brand_title = isset($search_conditions['extra'][hash("crc32", $api, false).'bt']) ? $search_conditions['extra'][hash("crc32", $api, false).'bt'] : $search_conditions['brand_title'];
	        
            /*
             * �������� ������� ���������.
             */

			/*
			 * ���� ����� ��������� � ������ ���������, �� �������� �������� ������ � ���������
			 * ������� ����� �� ����, ����� ����� ������������� �� ���� ������� � ����������.
			 */

			$brand_title = (string) $wordforms->getBrandGroup($brand_title) ?: $brand_title;

            $brand_titles = (array) $wordforms->getGroupWordforms($brand_title);
            
            
            /*
            * $brand_title ����� ���� �� ����, ���� �� ���� ����� ��� ���������� �� ����� ����������� ��������
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
	         * ������ �����, ��� ���� ������� ��������� �������� � ������ ����������
	         * ��������, ��������� ������� �������� TRW � LUCAS/TRW
	         */
        	for ($i = 0; $i < count($brand_titles); $i++) {
		        /*
	        	 * ���������� ������� � ������ �� ���������� �������� ��� ������� �� ��������
	        	 */
	        	$extra = $search_conditions['extra'];
		        foreach ($extra as $k => $v) {
			        if (is_array($v)) {
			        	$extra[$k] = $v[$i];
                    }
		        }

		        /*
		        * � ��� ������ ����� ���� ��������� �������� ��� ������ ����������, ������� ��������� supplier_id
                */
		        $job_code = (count($brand_titles) == 1) ? $api. '_' .$supplier_id : $api . '_' .$supplier_id.'_'. $i;
		        
		        
		        $jobs[$job_code] = array(
		        	'supplier_id' => $supplier_id,
		        	'api' 		  => $api,
		        );

                /*
                 * ������ � �����
                 */
                $cache_id = $job_code . serialize($search_conditions);

                //������ ��������������������� ������ ���� � �������
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

                    // #8825 ������� ��������������� $prop_api
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

                    // ������ �����
//                    $thread->addJob($job_code, '/bitrix/modules/linemedia.autoremotesuppliers/exec/search_remote_supplier.php', array(
//                        $api,
//                        $supplier_id,
//                        $search_conditions['query'],
//                        $brand_titles[$i],
//                        json_encode($extra)
//                    ));
                }

		        /*
		         * ����� ���������� ����������
		         */
		         LinemediaAutoDebug::add($api . ' request', '<b>' . $search_conditions['query'] . '</b> [<b>'.$brand_titles[$i].'</b>]<br>'.print_r($extra, 1), LM_AUTO_DEBUG_WARNING);
	        }
        }
            
        
        /*
         * ������ ��������
         */
        $thread->execute();
        
        LinemediaAutoDebug::add('Remote suppliers requests finished');
        
        /*
         * �������� ���������
         */
        $results = $thread->getResults();
       
        /*
         * ������� ����� �� ����
         */
        foreach($cached_jobs as $job_code => $arCache) {
            // ���� � ���� ���� ������ �� ��������� ���� � ������ ���������, ��� ������ ���� � $results ���� ������ �������� - ����� ��������� ��� ������� � ����
            if($arCache['data']) $results[$job_code] = false;
        }


        /*
         * ���������� ��������� �������.
         */
        $remote_analogs = array();
        
        foreach ($results as $job_code => $response) {
        	
        	$job 			= $jobs[$job_code];
        	$supplier_id 	= $job['supplier_id'];
        	$api 			= $job['api'];
        	
            $remote_analogs[$api] = array();

            if(!$response) {
                // ����� ��� ��� ���� � ���� ?
                $api_result = $cached_jobs[$job_code]['data'];

                $jsonError = false;
            } else {
                /*
                 * ���������� �� JSON �������� ��� ������?
                 * ���� �������� �� JSON - ���� �������� �������, ���� ���� ����� ��� ���-�� ��� ����������
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
                // ��������� ������ �������� ������ json.
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
            
	        // ��������� ������������ ������
	        if ($api_result['error']) {
		        LinemediaAutoDebug::add($api_result['text'], false, $api_result['error_level']);
		        continue;
	        }
	        
	        
	        // ���� ���� JSON �����������, �� ����, ��� �� ����������
	        if (!is_array($api_result)) {
	        	LinemediaAutoDebug::add($job_code . ' empty response' . $response, false, LM_AUTO_DEBUG_ERROR);
	        	continue;
            } else { // ���������� ���������

                if(!$cached_jobs[$job_code]['data'] && $cached_jobs[$job_code]['cache_time'] > 0) {

                    $cache = &$cached_jobs[$job_code]['cache_object'];
                    $cache->StartDataCache();
                    $cached_data = $api_result;
                    $cached_data['time'] = 0;
                    $cache->EndDataCache($cached_data);
                }
            }

	        // �����, ����������� �� ���������� ������� ��� ����� �������� �� �����������������
	        $time = '<b>' . number_format($api_result['time'], 2) . ' s.</b>';
	        
	        
            // ���������� �� ������ �� �������� � API?
            $sendapi[$api]        = true;
            $remote_brands[$api]  = '';
            
	        /*
	         * ���� �� ��������?
	         */
	        if (isset($api_result['catalogs'])) {
	        	$catalogs = $api_result['catalogs'];
                
                // ���� ��������� ������ ������ - �� ���������� ������ � API.
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
	         * ���� �� ��������?
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
                        
                        // ���������� ������������ ��������.
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
                            
                            // ������������ ������ ������� �����������.
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
         * ������� ��������� �����������.
         */
        $apiremote = array(
            'query'     => $search_conditions,
            'brands'    => $remote_brands,
            'analogs'   => $remote_analogs,
        );
        
        // �������� ���� �� ������� �� ������� �����������.
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
         * �������� �������� ���
         */
        LinemediaAutoDebug::$filename = false;
    }

    /**
     * �������� ������
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
         * ��� �������� ��� �� ���������
         */
        if (!isset($suppliers[$part_id])) {
            return;
        }
        
        /*
         * � �������� ����������� ��� ������ ���������� ��� ���������� � ������� $data
         */
        $loaded = true;
    }

    /**
     * ��������� ������ � ���������� � �������.
     * ����� �� ����� ����� � �������, �� �� �� ����� ��� ����, ������ ��� ��� �� ��������� ����� GET
     * ���� � ��� ���� extra, � ������� ������� ����� ����� ��������� ��� ���������� � �������� �� ��������� ���������� � �������� � � ������
     * $additional �� �������� ����� ������ ������ part
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
         * ��� �������� ��� �� ���������
         */
        if ($api == '') {
            return;
        }

		/*
		* ��� �������� �� ��������� ����������, �� ��� � ���� �� ������!
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
        
        // ���������� ������ � ����� ��������.
        $additional['delivery_time'] = $data['delivery_time'];
        
        /*
         * ������� ����� � ��, ����� � ��� ����� ���� ��������
         */
        $part = new LinemediaAutoPart(false, $data);
        try {
	        $part_id = $part->save();
        } catch (Exception $e) {
	        throw $e;
        }
    }

    /**
     * ���������� � ������ �������� �����������.
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
     * ������� ��������
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

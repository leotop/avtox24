<?php

IncludeModuleLangFile(__FILE__);

class LinemediaAutoModule
{
	
	static $cache = array();
	
	
	public static function checkUpdates($module_id = 'linemedia.auto')
	{
		$cache = new CPHPCache();
		$life_time = 1800; 
		$cache_id = __CLASS__ . __METHOD__ . $module_id;
		
		if ($cache->InitCache($life_time, $cache_id, "/lm_auto/mod_updates")) {
		    $vars = $cache->GetVars();
		    
		    return $vars['response'];
		} else {
	        $response = self::__checkUpdates($module_id);
	        
	        if ($cache->StartDataCache()) {
		        $cache->EndDataCache(array(
		        	'response' => $response,
		        ));
	        }
		}
		return $response;
	}
	
	
	public static function __checkUpdates($module_id)
	{
		include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php');
		$response = CUpdateClientPartner::GetUpdatesList();
		$modules = (array) $response['MODULE'];
		foreach ($modules as $module) {
			if ($module['@']['ID'] != $module_id) {
				continue;
			}
			$updates = array();
			foreach ($module['#']['VERSION'] as $ver) {
				$updates[$ver['@']['ID']] = $ver['#']['DESCRIPTION'][0]['#'];
			}
			return $updates;	
		}
		return false;
	}
	
	
	/**
	 * Is some logical function available for current client
	 * @return bool
	 */
	public static function isFunctionEnabled($funcname, $module_id = 'linemedia.auto')
	{
        $cache_key = md5(__METHOD__);
		if (self::$cache[$cache_key]) {
			$functions = self::$cache[$cache_key];
		} else {
	        $cache = new CPHPCache();
	        $life_time = 600;
	        $cache_id = 'linemedia.auto-functions-enabled';
			
	        if ($cache->InitCache($life_time, $cache_id, "/lm_auto")) {
	            $vars = $cache->GetVars();
				
	            $functions = $vars['functions'];
	        } else {
	            $api = new LinemediaAutoApiDriver();
	            try {
	                $response = $api->getAccountInfo2();
	            } catch (Exception $e) {
	                $response['data'] = array();
	            }
	            $functions = $response['data']['services'];
	            
	            if ($functions) {
	                $cache->EndDataCache(array(
	                    'functions' => $response['data']['services'],
	                ));
	            }
	            
	        }
	        self::$cache[$cache_key] = $functions;
        }
		$available = (bool) $functions[$module_id][$funcname]['available'];
		
		return $available;
	}
	
	
	/**
	 * Is some logical function available for current client and what is the limit for it?
	 * Copy of isFunctionEnabled (upper)
	 * @return float
	 */
	public static function getFunctionLimit($funcname, $module = 'linemedia.auto')
	{
		$cache = new CPHPCache();
		$life_time = 1800; 
		$cache_id = 'linemedia.auto-functions-enabled';
		
		if ($cache->InitCache($life_time, $cache_id, "/lm_auto")) {
		    $vars = $cache->GetVars();
		    
		    $services = $vars['services'];
		} else {
	        $api = new LinemediaAutoApiDriver();
	        try {
		        $response = $api->getAccountInfo2();
		    } catch(Exception $e) {
			    $response['data'] = array();
		    }
		    $services = $response['data']['services']; 
		    
	        if ($services) {
		        $cache->EndDataCache(array(
		        	'services' => $response['data']['services'],
		        ));
	        } 
		}
		$limit = (float) $services[$module][$funcname]['limit'];
		
		if ($limit < 0) {
			return INF;
		}
		return $limit;
	}
	
	
	/**
	 * ѕолучение названи€ модул€ по его ID
	 */
	public static function getModuleTitle($module_id)
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/install/index.php';
		$classname = str_replace('.', '_', $module_id);
		if (!class_exists($classname)) {
			return $module_id;
		}
		$instance = new $classname;
		return $instance->MODULE_NAME;
	}
	
	
	/**
	 *  оличество €дер процессора.
	 * ƒл€ ограничени€ количества одновременно работающих кронов/
	 */
	public static function getServerCPUCoresCount()
	{
		return (int) shell_exec("cat /proc/cpuinfo  | grep processor | wc -l");
	}
	
	
	/**
	 *  оличество запущенных на данный момент кронах.
	 */
	public static function getServerRunningCronCount()
	{
        $result = 0;

        $filename = '/tmp/cron_events.lock';

        $previous_pids = trim(file_get_contents($filename));

        $previous_pids = explode(",", $previous_pids);

        $current_pid = getmypid();

        foreach($previous_pids as $pid_key => $previous_pid) {

            $output = array();
            
            $previous_pid = str_replace(" ", "", $previous_pid);

            if($previous_pid == $current_pid) {
                continue; //это текущий крон, не провер€ем его, т.к. эта проверка может быть запущена из разных агентов
            }

            if(empty($previous_pid)) {
                unset($previous_pids[$pid_key]);
                continue;
            }

            exec("ps {$previous_pid}",$output);

            if (count($output) >= 2) { //процесс запущен, нужно именно 2! ибо если процесса нет значение будет 1
                $result++;
            } else {
                unset($previous_pids[$pid_key]);
            }
        }

        $previous_pids[] = $current_pid;

        file_put_contents($filename, implode(",", array_unique($previous_pids)));

        return $result;
	}


	/**
	 * ћожно ли запускать ещЄ один крон? 
	 */
	public static function canRunAnotherCron()
	{
		$crons_count = self::getServerRunningCronCount();
		$cores_count = self::getServerCPUCoresCount();
		echo "CHECK ($crons_count)\n";

		/*
		 * —колько €дер должно быть свободно дл€ обработки прочих задач
		 */
		$min_free_cores = 2;

		/*
		 * ≈сли €дер столько же, сколько всего нужно свободных, то крон всЄ равно надо запускать
		 * Ќо только один
		 */
		if ($min_free_cores >= $cores_count) { //$cores_count <= 2
			// крон уже работает
			if ($crons_count > 0) {
				return false;
			}
		/*
		 * ≈сли €дер больше, чем нужно пользовател€м, то крон можно запускать, если есть свободные €дра
		 */
		} else {
			if ($crons_count + $min_free_cores >= $cores_count) {
				return false;
			}
		}
		
		echo "OK\n";
		return true;
	}
	
	
	
	public static function getAutoexpertLoadMatrix()
	{
	    // goods
	    $database = new LinemediaAutoDatabase();
	    $res = $database->Query("SELECT COUNT(*) AS cnt FROM `b_lm_products`");
	    $res = $res->fetch();
	    $goods_count = $res['cnt'];
	    $max_goods_quantity = LinemediaAutoModule::getFunctionLimit('max_goods_quantity', 'linemedia.auto');
	    $goods_percent = ($goods_count * 100) / $max_goods_quantity;
	    
	    
	    // suppliers
	    $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
	    $max_suppliers_quantity = LinemediaAutoModule::getFunctionLimit('max_suppliers_quantity', 'linemedia.auto');
	    $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id));
        $active_suppliers_count = $res->SelectedRowsCount();
        $suppliers_percent = ($active_suppliers_count * 100) / $max_suppliers_quantity;
        
        // branches
	    $branches_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_BRANCHES');
	    $max_branches_quantity = LinemediaAutoModule::getFunctionLimit('max_branches_quantity', 'linemedia.autobranches');
	    $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $branches_iblock_id));
        $active_branches_count = $res->SelectedRowsCount();
        $branches_percent = ($active_branches_count * 100) / $max_branches_quantity;
		
		// global percent
		
		$global = max(array($goods_percent, $suppliers_percent, $branches_percent));
		$global = ($global > 100) ? 100 : $global;
		
		return array(
			'global' => (double) $global,
			'goods_percent' => $goods_percent,
			'suppliers_percent' => $suppliers_percent,
			'branches_percent' => $branches_percent,
		);
	}


    /**
     * ƒоступен ли LibreOffice?
     * @return bool
     */
    public static function isXLSResaveSupported()
    {
        $returnVal = shell_exec("which libreoffice");
        return (empty($returnVal) ? false : true);
    }

    /**
     * ƒоступен ли LibreOffice?
     * @return bool
     */
    public static function isJavaSupported()
    {
        return shell_exec("java -version") || shell_exec('which java');
    }
	
	/**
	* ƒоступен ли API?
	*/
	public static function getApiConnectionTime()
	{
        if(function_exists('curl_init')) {
            $ch = curl_init('http://api.auto-expert.info');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	1);
            curl_setopt($ch, CURLOPT_HEADER, 			1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 			2);
            curl_setopt($ch, CURLOPT_FAILONERROR, 		1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 		1);
            curl_setopt($ch, CURLOPT_ENCODING,			'gzip');
            $start = microtime(true);
            curl_exec($ch);
            $result = microtime(true) - $start;

            if($error = curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }
            return $result;
        } else {
            throw new Exception(GetMessage('LM_AUTO_CURL_HOWTO'));
        }

	}
	
	
}

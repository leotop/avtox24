<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Main module
 * Module events for module itself
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

class LinemediaAutoEventSelf
{
	
	/**
	* Статический кеш
	*/
	static $cache;

	/**
	 * Добавляем ндс к товарам при оформлении корзины
	 */
	public function OnBeforeBasketAdd_addNDS(&$arFields)
	{
		$NDSType = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_TYPE_NDS');
		$typesNDS = array();
		if ($NDSType) {
			CModule::IncludeModule('catalog');
			$dbResultList = CCatalogVat::GetList(
				array('CSORT' => 'ASC'),
				array('ACTIVE' => 'Y')
			);

			while ($typeNDS = $dbResultList->Fetch()) {
				$typesNDS[$typeNDS['ID']] = $typeNDS;
			}
			if (empty ($arFields['VAT_RATE'])) {
				$arFields['VAT_RATE'] = $typesNDS[$NDSType]['RATE']/100;
			}
		}
	}

    /**
     * Добавляем в данные поиска информацию от Linemedia API
     */
	public function OnSearchExecuteBegin_addLinemediaApiAnalogs(
	    &$search_conditions,
	    &$articles_to_search,
	    &$catalogs_to_search,
	    &$search_article_results,
	    &$type,
	    &$result_info,
	    &$modificator_set,
	    &$search_limit
	)
    {

        if (strcmp($search_limit, LinemediaAutoSearch::TITLE_LIMIT) == 0) {
            return;
        }
        
        if(defined('LM_AUTO_FORCE_SKIP_API_ANALOGS')) {
            return;
        }

        /*
         * Если поиск не по совпадению артикула - не ищем во внешних поставщиках.
         */
        if ($type != LinemediaAutoSearch::SEARCH_SIMPLE && $type != LinemediaAutoSearch::SEARCH_BY_PARAMS) {
            return;
        }

        /*
         * Если в артикуле есть кириллические символы не делаем поиск аналогов
         */
        if (mb_internal_encoding() == 'UTF-8' && !empty($search_conditions['query'])) {
            if((bool) preg_match('/\p{Cyrillic}/u', $search_conditions['query'])) {
                return;
            }
        }

        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Linemedia API search module added');

        /*
         * Объект доступа в API
         */
        $api = new LinemediaAutoApiDriver();

        /*
         * Аргументы запроса
         * Почистим артикул
         */
        $query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);
        $api_request_args = array(
            'article' => $query
        );


        /*
         * Если присутствует родной бренд TecDoc и его специфические аргументы запроса.
         */
        if ($search_conditions['brand_title']) {
            $api_request_args['brand_title'] = $search_conditions['brand_title'];
        }
        if ($search_conditions['extra']['gid']) {
            $api_request_args['generic_article_id'] = $search_conditions['extra']['gid'];
        }

        /*
         * У нас может быть множественный запрос, если словоформы объединили много брендов в один.
         * В таком случае в эестре присутствует массив всех брендов (wf_b) и genericArticleId (gid).
         */
        if (is_array($search_conditions['extra']['gid']) || is_array($search_conditions['extra']['wf_b'])) {

            // 20.09.15 Ioannes
            // $search_conditions['extra']['gid'] может быть скаляром, когда
            // $search_conditions['extra']['wf_b'] - массив
            if(is_array($search_conditions['extra']['gid'])) {
                $gids 	= array_map('intval', $search_conditions['extra']['gid']);
            }

            $brands = array_map('strval', $search_conditions['extra']['wf_b']);

            // Добавим группу в искомый бренд.
            if(!in_array($search_conditions['brand_title'], $brands))
                $brands[] = $search_conditions['brand_title'];

            $api_arguments = array();
            for ($i = 0; $i < count($brands); $i++) {
                if(is_array($search_conditions['extra']['gid'])) {
                    $api_request_args['generic_article_id'] = $gids[$i];
                }
                $api_request_args['brand_title'] = $brands[$i];
                $api_arguments[] = $api_request_args;
            }
        } elseif(!isset($search_conditions['extra']['wf_b']) AND $search_conditions['brand_title'] != '') {
        	/*
        	* Поиск по бренду, но словоформы переданы не были!
        	* например это клик по ссылке, а не переход из выбра каталогов
        	*/
        	$wordforms = new LinemediaAutoWordForm;
        	$brands = $wordforms->getBrandWordforms($search_conditions['brand_title']);


        	// Добавим группу в искомый бренд.
            if(!in_array($search_conditions['brand_title'], $brands))
                $brands[] = $search_conditions['brand_title'];

        	$api_arguments = array();
            for ($i = 0; $i < count($brands); $i++) {
                $api_request_args['brand_title'] = $brands[$i];
                $api_arguments[] = $api_request_args;
            }

        } else {
            /*
             * Множественного запроса нет.
             * Но чтобы не плодить функции мы всё равно используем множественный вызов,
             * который аргументом принимает массив запросов.
             */
            $api_arguments = array($api_request_args);
        }


        /*
         * Используем настройки модуля (вкладка поиск),
         * чтобы определить, какие аналоги мы хотим получить в ответе.
         *
         * Варианты:
         * 	Искать прямые кроссы в TecDoc
         *	Искать оригинальные кроссы в TecDoc
         *	Искать кроссы в БД Linemedia
         *	Искать кроссы в локальной БД    ----   используется в модуле простых аналогов
         */
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES 			= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES', 			'Y');
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL	= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL', 'Y');
        $LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES			= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES', 		'Y');
        $LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY			= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY', 		'N');

        foreach ($api_arguments as &$api_argument) {
            $api_argument['tecdoc_crosses'] = ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES == 'Y');
            $api_argument['tecdoc_crosses_original'] = ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL == 'Y');
            $api_argument['linemedia_crosses'] = ($LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES == 'Y');
            $api_argument['oem_sought_only'] = ($LM_AUTO_MAIN_SEARCH_OEM_SOUGHT_ONLY == 'Y');
        }

        /*
         * Запрос.
         */
        try {
            $response = $api->query('getAnalogs2Multiple', $api_arguments);
            LinemediaAutoDebug::add('api->query getAnalogs2Multiple', print_r($api_arguments, true), LM_AUTO_DEBUG_WARNING);
            LinemediaAutoDebug::add('api->query getAnalogs2Multiple response', print_r($response, true), LM_AUTO_DEBUG_WARNING);
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Search Linemedia API:' . $e->GetMessage(), false, LM_AUTO_DEBUG_ERROR);
            return;
        }

		/*
		 * Дата: 18.10.13 12:24
		 * Кто: Назарков Илья
		 * Задача: 5410
		 * Пояснения: добавляем массив аналогов в глобальную переменную, чтобы далее
		 * в компоненте linemedia.auto:search.results посчитать их
		 */
		$result_info['tecdocAndLinemediaAnalogs'] = (array) $response['data'];

        /*
         * Если запрос не прошёл.
         */
        if ($response['status'] == 'error') {
            LinemediaAutoDebug::add('Linemedia API error:' . $response['error']['code'] . '('.$response['error']['error_text'].')', false, LM_AUTO_DEBUG_USER_ERROR);
            return;
        }

        /*
         * Объединим результат множественного запроса в одну простыню.
         * Потому что мы отсылали словоформы, и нам не важно на какую деталь какой ответ.
         */
        $parts = array();
        $catalogs = array();
        foreach ($response['data'] as $req) {
            $parts 		= array_merge_recursive($parts, 	(array) $req['analogs']['parts']);
            $catalogs 	= array_merge_recursive($catalogs, 	(array) $req['analogs']['catalogs']);
        }

        $response['data'] = array(
            'parts' => $parts,
            'catalogs' => $catalogs,
        );

        /*
         * В ответе каталоги или детали?
         */
        $api_catalogs = (array) $response['data']['catalogs'];

        /*
         ***************************************************************************************************
         */

        /*
         * А не объединятся ли каталоги в словоформе потом?
         * ВЫПОЛНЯЕМ ПОВТОРНЫЙ ЗАПРОС!!!!!!!!
         */
        if (count($api_catalogs) && count(LinemediaAutoSearch::getIntersectCatalogs($api_catalogs)) <= 1) {
            $request = array();
            foreach ($api_catalogs as $cat) {
                $request []= array(
                    'article' => $query,
                    'brand_title' => $cat['brand_title'],
                    'generic_article_id' => $cat['generic_article_id'],

                    'tecdoc_crosses' => ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES == 'Y'),
                    'tecdoc_crosses_original' => ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL == 'Y'),
                    'linemedia_crosses' => ($LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES == 'Y'),
                );
            }

            /*
             * Запрос
             */
            try {
                $response = $api->query('getAnalogs2Multiple', $request);
            } catch (Exception $e) {
                LinemediaAutoDebug::add('Search Linemedia API:' . $e->GetMessage(), false, LM_AUTO_DEBUG_ERROR);
                return;
            }

            /*
             * Если запрос не прошёл
             */
            if ($response['status'] == 'error') {
                LinemediaAutoDebug::add('Linemedia API error:' . $response['error']['code'] . '('.$response['error']['error_text'].')', false, LM_AUTO_DEBUG_USER_ERROR);
                return;
            }

            /*
             * Объединим результат множественного запроса в одну простыню.
             * Потому что мы отсылали словоформы, и нам не важно на какую деталь какой ответ.
             */
            $parts = array();
            $catalogs = array();
            foreach ($response['data'] as $req) {
                $parts 		= array_merge_recursive($parts, 	(array) $req['analogs']['parts']);
                $catalogs 	= array_merge_recursive($catalogs, 	(array) $req['analogs']['catalogs']);
            }

            foreach ($parts as &$item) {
                if (empty($item['analog_type'])) {
                    $item['analog_type'] = LinemediaAutoPart::ANALOG_GROUP_COMPARABLE;
                }
            }

            $response['data'] = array(
                'parts' => $parts,
                'catalogs' => $catalogs,
            );

            $api_catalogs = array();

        }


        /*
         ***************************************************************************************************
         */

        /*
         * Если передан brand_title - значит это уже уточнение и показать каталоги нельзя
         */
        if ($search_conditions['brand_title'] != '') {
            $api_catalogs = array();
        }

        if (count($api_catalogs) > 0) {
            $catalogs = array();
            foreach ($api_catalogs as $catalog) {
                $catalogs []= array(
                    'title' 		=> $catalog['title'],
                    'brand_title' 	=> $catalog['brand_title'],
                    'source' 		=> $catalog['source'],
                    'analog-source' => 'linemedia-api',
                    'extra' => array(
                        'gid'  => $catalog['generic_article_id'],
                    ),
                );

                $catalogs = self::getIntersectCatalogs($catalogs);
            }

            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Linemedia API returned catalogs', print_r($catalogs, 1));

            $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);

            return;
        }


        /*
         * Вернём результат (детали)
         */
        $analogs = array();
        $catalogs = array();
        $brands_cache = array();

        $query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);

        $generic_ids = array();

        foreach ($response['data']['parts'] as $item) {
            $part = array(
                'title'         => $item['title'],
                'article'       => $item['article'],
                'source' 		=> $item['source'],
                'analog_type'   => (!empty($item['analog_type'])) ? ($item['analog_type']) : (LinemediaAutoPart::ANALOG_GROUP_COMPARABLE),
                'analog-source' => 'linemedia-api',
                'extra' => array(
                    'gid'  => $item['generic_article_id'],
                ),
            );

            if ($item['brand_title'] != '') {
                $part['brand_title'] = $item['brand_title'];
            }

            if(LinemediaAutoPartsHelper::clearArticle($item['article']) == $query) {
	            $catalogs[$part['brand_title']] = $part;
            }


            /*
             * Если детали пришли из TecDoc, значит по ним есть дополнительная информация.
             * Распределим информацию по бренду и артикулу.
             */
            $brand   = strtoupper($part['brand_title']);
            $article = LinemediaAutoPartsHelper::clearArticle($part['article']);
            $result_info[$brand][$article]['tecdoc'] = array(
                'article_id'            => $item['article_id'],
                'oem'                   => $item['oem'],
                'generic_article_id'    => $item['generic_article_id']
            );

            $generic_ids[$item['generic_article_id']] = $item['generic_article_id'];

            $analogs []= $part;
        }

        /*
         * #18706
         * Если в деталях оказалось несколько номенклатур
         */
        if(count($generic_ids) > 1) {

            if($search_conditions['brand_title'] != '') {
                /*
                 * текдок выдал неправильный результат (пример - поиск артикула 02126) - из разных номенклатур
                 * при указании бренда игнорируем аналоги
                 */
                $analogs = array(); //
                foreach($result_info['tecdocAndLinemediaAnalogs'] as $id => &$data) {
                    $data['analogs']['parts'] = array();
                }
            }
        }

        /*
         * Объединим данные, которые уже были, с новыми
         */
        $articles_to_search = array_merge_recursive($articles_to_search, $analogs);
        $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
    }




    /**
     * Добавляем в результаты поиска информацию от локальной БД
     */
    public function OnSearchExecuteBegin_addLocalDBData(
        &$search_conditions,
        &$articles_to_search,
        &$catalogs_to_search,
        &$search_article_results,
        $type,
        &$result_info,
        &$modificator_set,
        &$search_limit
    )
    {
            
        if (strcmp($search_limit, LinemediaAutoSearch::TITLE_LIMIT) == 0) {
            return;
        }
     
        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Linemedia Local DB search module added');

        /*
         * Если у нас не задан бренд и в БД есть более одного производителя с таким артикулом - надо показать каталоги
         */
        if ($search_conditions['query'] != '' && $search_conditions['brand_title'] == '') {
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('No brand, but article exists, check for local catalogs', false, LM_AUTO_DEBUG_WARNING);

            /*
             * Поиск.
             * В параметре $type содержится название класса по типу поиска.
             */
            if ($type == LinemediaAutoSearch::SEARCH_GROUP) {
                return;
            }

            $query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);

            $search = new $type();
            if(method_exists($search, 'setConditions')) {
                $conditions = $search_conditions;
                $conditions['limit'] = $search_limit;
                $search->setConditions($conditions);
            }
            $result = $search->searchLocalDatabaseForPart(array('article' => $query), true);


            /*
             * Найдены локальные запчасти
             * Если есть каталоги - добавим к каталогам
             * Если нет - то покажем каталоги, если запчасть более чем одна у разных брендов
             */
            if (count($result) > 0) {
                // if (count($result) > 1 || count($catalogs_to_search) > 0) { // Ilya Pyatin 04.04.13 kodauto 6554V5 	tiket 3213
                $brands = array();
                $catalogs = array();
                foreach ($result as $part) {
                    $brands[$part['brand_title']] = false;
                    $catalogs[$part['brand_title']] = $part;
                }

                /*
                 * Каталогов оказалось много.
                 * Для группового поиска не объединяем по каталогам, выводим все вместе.
                 */
                if (count($catalogs) > 0 || count($brands) > 1) {
                    $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
                    LinemediaAutoDebug::add('Local catalogs added', print_r($catalogs, 1), LM_AUTO_DEBUG_WARNING);
                }



                LinemediaAutoDebug::add('Local parts added', print_r($result, 1), LM_AUTO_DEBUG_WARNING);

                /*
                * Добавить запчасть из нашей базы к результатам поиска
                */
                foreach($result AS $local_part) {
	                $search_article_results['analog_type_N'][] = $local_part;
                }
            }
        }
    }



   /**
     * В просчёт цены добавляется наценка поставщика
     */
    public function OnItemPriceCalculate_addSupplierMarkup(&$part, &$price, &$currency, &$user_id, &$date, &$debug_calculations_results, &$external, &$price_calc_obj)
    {
        /*
         * Наценка - в процентах
         */
        $supplier_id = $part->get('supplier_id');

        /*
         * Закешируем данные
         */
        if (!isset(self::$cache['suppl_markup'][$supplier_id])) {
            $supplier = new LinemediaAutoSupplier($supplier_id);
            if($price_calc_obj->isChain()) {
            	$supplier->ignorePermissions();
            }
            $markup = (float) $supplier->get('markup');
            self::$cache['suppl_markup'][$supplier_id] = $markup;
            self::$cache['suppl_id'][$supplier_id] = $supplier->get('ID');
        } else {
            $markup = self::$cache['suppl_markup'][$supplier_id];
        }

        $new_price = $price + ($price * ($markup / 100));

        // отладка о наценке поставщика
        $debug_calculations_results[] = GetMessage('LM_AUTO_SUPPLIER_MARKUP_DEBUG', array('#MARKUP#' => $markup, '#MARKUP_VALUE#' => ($price * ($markup / 100)), '#RESULT#' => $new_price, '#SUPPLIER_ID#' => self::$cache['suppl_id'][$supplier_id]));

        $price = $new_price;
    }




    /**
     * Цена конвертируется в соответствии с валютой поставщика
     * Важно делать это в самом конце после всех просчётов
     */
    public function OnItemPriceCalculate_convertSupplierCurrency(&$part, &$price, &$currency, &$user_id, &$date, &$debug_calculations_results, &$external)
    {
        /*
         * Наценка - в процентах
         */
        $supplier_id = $part->get('supplier_id');

        /*
         * Закешируем данные
         */
        if (!isset(self::$cache['suppliers'][$supplier_id])) {
            self::$cache['suppliers'][$supplier_id] = $supplier = new LinemediaAutoSupplier($supplier_id);
        } else {
	        $supplier = self::$cache['suppliers'][$supplier_id];
        }


        /*
         * Также нужно пересчитать валюту поставщика
         *
         * Загрузим валюты
         */
        if (!isset(self::$cache['currencies'])) {
	        $obCache = new CPHPCache();
	        $life_time = 24 * 60 * 60;
	        $cache_id = 'price-currencies-'.date('d.m.Y');
	        if ($obCache->InitCache($life_time, $cache_id, "/".__FUNCTION__.'/')) {
	            $data = $obCache->GetVars();
	            $currencies = $data['currencies'];
	            $base_currency = $data['base'];
	        } else {
	            if (!CModule::IncludeModule('currency')) {
	                LinemediaAutoDebug::add('Error price calculation, no currencies module!', false, LM_AUTO_DEBUG_ERROR);
	            }
	            $base_currency = CCurrency::GetBaseCurrency();
	            $lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
	            while ($lcur_res = $lcur->Fetch()) {
	                $currencies[ $lcur_res["CURRENCY"] ] = CCurrencyRates::GetConvertFactor($lcur_res['CURRENCY'], $base_currency);
	            }

	            if ($obCache->StartDataCache()) {
	                $obCache->EndDataCache(array('currencies' => $currencies, 'base' => $base_currency));
	            }
	        }

	        self::$cache['currencies'] = $currencies;
	        self::$cache['base_currency'] = $base_currency;

        } else {
        	$currencies = self::$cache['currencies'];
        	$base_currency = self::$cache['base_currency'];
        }


        /*
         * Сравним валюты
         */
        $supplier_currency_id = $supplier->get('currency');

        if ($supplier_currency_id !== $base_currency && $supplier_currency_id != '') {
            $price = $price * $currencies[$supplier_currency_id];

            // Отладка о конвертации валюты
            $debug_calculations_results[] = GetMessage('LM_AUTO_SUPPLIER_CURRENCY_DEBUG', array('#AMOUNT#' => $currencies[$supplier_currency_id], '#SUPPLIER_CUR#' => $supplier_currency_id, '#BASE_CUR#' => $base_currency)) . ' <b>' . $price . '</b>';
        } else {
            // Конвертация валюты не требуется
            $debug_calculations_results[] = GetMessage('LM_AUTO_SUPPLIER_CURRENCY_NOT_APPLIED_DEBUG');
        }
    }


    /**
     * Расчет скидок.
     */
    public function OnItemPriceCalculate_customDiscounts(&$part, &$price, &$currency, &$user_id, &$date, &$debug_calculations_results, &$external, &$price_obj)
    {
        $odiscount = new LinemediaAutoCustomDiscount($part, $user_id, $external);
        $odiscount->setUserId($user_id);
        $odiscount->setDate($date);
        if(is_array($external)) {
            foreach($external as $key => $value) {
                $odiscount->setExternal($key, $value);
            }
        }
        
        // оптовая торговля
        if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
	        if($price_obj->isChain()) {
		        $chain = $price_obj->getChain();
		        $odiscount->setBranchId($chain['branch_id']);
	        }
        }
        
        
        $price = $odiscount->calculate($price);
        $debug_calculations_results = array_merge((array)$debug_calculations_results, $odiscount->getDebug());
        unset($odiscount);
    }


    /**
     * Добавление проверок системы.
     */
    public function OnRequirementsListGet_addChecks(&$check)
    {
        $add = array();
		
        /*
         * Крон
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_CRONTAB'),
            'requirements' => GetMessage('LM_AUTO_CRONTAB_HOWTO'),
            'status' => (bool) LinemediaAutoImportAgent::checkCron(),
        );

        /*
         * CURL
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_CURL'),
            'requirements' => GetMessage('LM_AUTO_CURL_HOWTO'),
            'status' => function_exists('curl_init'),
        );


        /*
         * JSON
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_JSON'),
            'requirements' => GetMessage('LM_AUTO_CRONTAB_JSON'),
            'status' => function_exists('json_decode'),
        );


        /*
         * PHP 5.3
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_PHP53'),
            'requirements' => GetMessage('LM_AUTO_PHP53_HOWTO'),
            'status' => version_compare(PHP_VERSION, '5.3.0') >= 0,
        );


        /*
         * Сколько прайслистов ожидают импорта
         */
        $files = (array) LinemediaAutoImportAgent::getNewFiles();
        $add []= array(
            'title' => GetMessage('LM_AUTO_PRICELISTS_IMPORT_WAITING'),
            'requirements' => GetMessage('LM_AUTO_PRICELISTS_IMPORT_WAITING_HOWTO') . join(', ', $files),
            'status' => count($files) < 2,
        );
        
        
        /*
         * AOP
         */
        /* Убрано по задаче 18663
        $add []= array(
            'title' => GetMessage('LM_AUTO_AOP'),
            'requirements' => GetMessage('LM_AUTO_AOP_HOWTO'),
            'status' => extension_loaded('aop') && version_compare(phpversion('aop'), '0.3.0') >= 0,
        );
        */
        
        
        /*
         * Pinba
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_PINBA'),
            'requirements' => GetMessage('LM_AUTO_PINBA_HOWTO'),
            'recomendation' => true,
            'status' => extension_loaded('pinba') && ini_get('pinba.enabled') == 1,
        );



        /*
         * HDD space
         */
        $available_free_space = LinemediaAutoFileHelper::getAvailableHDDSpace();
		$min_free_space = 1024*1024*1024; // 1G
		$available_free_space_print = LinemediaAutoFileHelper::getPrintableFilesize($available_free_space);
		$min_free_space_print = LinemediaAutoFileHelper::getPrintableFilesize($min_free_space);

        $add []= array(
            'title' => GetMessage('LM_AUTO_HDD_SPACE', array('#MIN#' => $min_free_space_print, '#AVAILABLE#' => $available_free_space_print)),
            'requirements' => GetMessage('LM_AUTO_HDD_SPACE_HOWTO', array('#MIN#' => $min_free_space_print, '#AVAILABLE#' => $available_free_space_print)),
            'status' => ($available_free_space > $min_free_space),
        );



        /*
         * LibreOffice
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_LIBREOFFICE_AVAILABLE'),
            'requirements' => GetMessage('LM_AUTO_NO_LIBREOFFICE') . GetMessage('LM_AUTO_PHP_NO_SHELL'),
            'status' => (bool) LinemediaAutoModule::isXLSResaveSupported(),
        );

        /*
         * Java
         */
        $add []= array(
            'title' => GetMessage('LM_AUTO_JAVA_AVAILABLE'),
            'requirements' => GetMessage('LM_AUTO_NO_JAVA') . GetMessage('LM_AUTO_PHP_NO_SHELL'),
            'status' => (bool) LinemediaAutoModule::isJavaSupported(),
        );
        
        
        /*
         * Connection to API
         */
        $api_ok = false;
        $api_error = false;
        try {
	        $api_time = LinemediaAutoModule::getApiConnectionTime();
	        $api_time = number_format($api_time, 3);
	        if($api_time <= 0.050) {
		        $api_ok = true;
	        }
        } catch (Exception $e) {
	        $api_error = $e->getMessage();
        }
        
        $add []= array(
            'title' => isset($api_time) ? GetMessage('LM_AUTO_API_AVAILABLE_TIME', array('#TIME#' => $api_time)) : GetMessage('LM_AUTO_API_AVAILABLE'),
            'requirements' => $api_error ?: GetMessage('LM_AUTO_API_RECOMMENDATIONS'),
            'status' => (bool) $api_ok,
            'recomendation' => $api_error || $api_ok ? false : true
        );

        $check['linemedia.auto'] = $add;
    }


    /**
     * Отправка сообщения о смене статуса.
     */
    public function OnAfterBasketItemStatus_sendMessage(&$basket_id, &$status)
    {

    	if (in_array($status, unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_SEND_EMAIL_FORBIDDEN')))) {
    		return;
    	}
    	
        $basket = new LinemediaAutoBasket();

        $arData  = $basket->getData($basket_id); 
        
       // _d($arData);
        $arProps = $basket->getProps($basket_id);
        $arOrder = CSaleOrder::GetByID($arData['ORDER_ID']);
        $arUser  = CUser::GetByID($arOrder['USER_ID'])->Fetch();

       
        
        // Если письма о смене статуса не отправлено как смена статуса заказа.
        if (!isset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER']) || $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER'] != true) {
            $order = new LinemediaAutoOrder($arOrder['ID']);
            
        
        
            // Проверка статусов корзин.
            $arBaskets = $order->getBaskets();
            $same = true;
            foreach ($arBaskets as $arBasket) {
                $arBasketProps = $basket->getProps($arBasket['ID']);
                if ($arBasketProps['status']['VALUE'] != $status) {
                    $same = false;
                    break;
                }
            }
          
            // Если все статусы одинаковые - меняем статус заказа.
            if ($same) {
                $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET'] = true;

                CSaleOrder::StatusOrder($order->getID(), $status);

                unset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET']);
            } else {
                // Если нет групповой смены статусов товаров.
                if (!isset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET']) || $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET'] != true) {
                    self::sendBasketItemStatusMessage($basket_id, $status);
                } 
            }
        }
    }


    /**
     * Отправка сообщений о смене статусов товаров.
     */
    public function OnAfterBasketStatusesChange_sendMessages(&$basket_ids, &$status)
    {
        $basket = new LinemediaAutoBasket();

        // Получение списка ID заказов от всех корзин.
        $order_ids = array();
        foreach ($basket_ids as $basket_id) {
            $arData = $basket->getData($basket_id);
            if (!in_array($arData['ORDER_ID'], $order_ids)) {
                $order_ids []= (int) $arData['ORDER_ID'];
            }
        }

        // Пройдем по всем заказам и посмотрим о каких товарах отправлять писма.
        foreach ($order_ids as $order_id) {
            // Общий заказ.
            $order = new LinemediaAutoOrder($order_id);

            // Проверка заказанных товаров.
            $arBaskets = $order->getBaskets();

            $order_basket_ids = array();
            foreach ($arBaskets as $arBasket) {
                $order_basket_ids []= (int) $arBasket['ID'];
            }

            // Посмотрим, все ли товары из заказа были выбраны.
            $send_basket_ids = array_intersect($basket_ids, $order_basket_ids);
            sort($send_basket_ids);
            sort($order_basket_ids);

            if (count($send_basket_ids) == 1) {

                // Проверка статусов корзин.
                $same = true;
                foreach ($arBaskets as $arBasket) {
                    $arBasketProps = $basket->getProps($arBasket['ID']);
                    if ($arBasketProps['status']['VALUE'] != $status) {
                        $same = false;
                        break;
                    }
                }
                if ($same) {
                    return;
                }
            }

            /*
             * Если изменились не все статусы товаров заказа - отправляем письма по товарам.
             * В противном случае уйдет письмо об изменении статуса самого заказа.
             */
            if ($send_basket_ids != $order_basket_ids) {
                foreach ($send_basket_ids as $basket_id) {
                    self::sendBasketItemStatusMessage($basket_id, $status);
                }
            }
        }
    }
	
	 /**
     * Проставлять свойство Отгрузка в корзины при переводе товара в определенный статус
     */	
	public function OnBeforeBasketUpdateStatuses_Unload($basket_ids)
	{	
		CModule::IncludeModule("sale");
		CModule::IncludeModule("linemedia.auto");
		$unload_status = COption::GetOptionString("linemedia.auto", 'LM_AUTO_MAIN_STATUS_TO_UNLOAD');
		
		$i = 0;
		foreach($basket_ids as $ID => $status)	
		{	
			if($unload_status == $status)
			{	
				if($i == 0)
				{
					$unload = COption::GetOptionString('linemedia.auto', "TO_UNLOAD", "");
					if(strlen($unload) == 0)
					{
						COption::SetOptionString('linemedia.auto', "TO_UNLOAD", "1");   
						$unload = COption::GetOptionString('linemedia.auto', "TO_UNLOAD", "");				
					}
					else
					{
						$unload += 1;
						COption::SetOptionString('linemedia.auto', "TO_UNLOAD", $unload);            
					}
				}

				$dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $ID), false, false, array());

				while($basket = $dbBasketItems->Fetch())
				{
					$props = array();
					$db_res = CSaleBasket::GetPropsList(
						array(
								"SORT" => "ASC",
								"NAME" => "ASC"
							),
						array("BASKET_ID" => $basket['ID'])
					);
					while ($ar_res = $db_res->Fetch())
					{
                        unset($ar_res['ID']);
                        unset($ar_res['BASKET_ID']);

						$props[] = $ar_res;
						
						if($ar_res["CODE"] != "p_unload")
						{
							$props_needed[] = array(
								"NAME" => $ar_res["NAME"],
								"CODE" => $ar_res["CODE"],
								"VALUE" => $ar_res["VALUE"],
							
							);    
						}
						
					}
		 
					$baskets[] = $basket;
				}
				   
				$props_needed[] = array(
					"NAME" => GetMessage("LM_AUTO_BASKET_UNLOAD2"),
					"CODE" => "p_unload",
					"VALUE" => $unload,                   
				);

                $props_needed = array_merge_recursive($props, $props_needed);


				$arFields = array("PROPS" => $props_needed);
				
				$basket = new CSaleBasket;
				$success = $basket->Update($ID, $arFields);
				if ($success === true)
				{
				   
				} else {
					$lAdmin->AddGroupError(GetMessage('LM_AUTO_BAKET_UPDATE_FAILED').$ID);
				}
			}
			
			$i++;
		}		
	} 
	 



	 
	public function OnBeforeBasketUpdate_Unload($basket_id, &$arFields)
	{
		/*CModule::IncludeModule("sale");
		//Статус, при котором проставлять отгрузку
		$unload_status = COption::GetOptionString("linemedia.auto", 'LM_AUTO_MAIN_STATUS_TO_UNLOAD');
		
		//Новый статус корзины
		foreach($arFields["PROPS"] as $prop)
		{
			if($prop["CODE"] == "status")
			{
				$status = $prop["VALUE"];
			}			
		}
		
		$arItems = CSaleBasket::GetByID($basket_id);
		
		$db_res = CSaleBasket::GetPropsList(
			array(
					"SORT" => "ASC",
					"NAME" => "ASC"
				),
			array("BASKET_ID" => $basket_id)
		);
		while ($ar_res = $db_res->Fetch())
		{
							
			$props[] = $ar_res;
		} 
		
		//Старый статус корзины
		foreach($props as $p)
		{
			if($p["CODE"] == "status")
			{
				$status_prev = $p["VALUE"];
			}		
		}
		
		if($unload_status == $status && $status != $status_prev)
		{	
			$unload = COption::GetOptionString('linemedia.auto', "TO_UNLOAD", "");
            if(strlen($unload) == 0)
            {
                COption::SetOptionString('linemedia.auto', "TO_UNLOAD", "1");   
				$unload = COption::GetOptionString('linemedia.auto', "TO_UNLOAD", "");				
            }
            else
            {
                $unload += 1;
                COption::SetOptionString('linemedia.auto', "TO_UNLOAD", $unload);            
            }
		
			foreach($arFields["PROPS"] as $prop)
			{
				if($prop["CODE"] == "p_unload")
				{
					$prop["VALUE"] = $unload;
					$arTemp[] = $prop;
				}
				else
				{
					$arTemp[] = $prop;
				}
			}
			
			unset($arFields["PROPS"]);
			$arFields["PROPS"] = $arTemp;
		}*/	
	}

    /**
     * Автопроценка товаров по локальной базе для выбранных инфоблоков после импорта прайсов
     */

    public function OnAfterPriceListAllImport_UpdateCatalogPrices($files_count, $files)
    {
        CModule::IncludeModule("iblock");
        CModule::IncludeModule("catalog");

        //определяем каталоги
        $iblocks_id = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES'));

        //определение базовой цены
        $ar_base_price = CCatalogGroup::GetBaseGroup();
        $price_type_id = $ar_base_price['ID'];

        $search = new LinemediaAutoSearchSimple();

        foreach ($iblocks_id as $iblock_id) {
            // Получаем элементы каталога.
            $rsDetails = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $iblock_id),
                false,
                false,
                array('ID', 'PROPERTY_ARTICLE', 'PROPERTY_BRAND_TITLE', 'PROPERTY_ARTNUMBER', 'PROPERTY_MANUFACTURER')
            );

            // Делаем проценку.
            while ($detail = $rsDetails -> Fetch()) {

                // Фильтруем по наличию полей 'article' и 'brand_title'.
                if ( (!empty($detail["PROPERTY_ARTICLE_VALUE"]) && !empty($detail["PROPERTY_BRAND_TITLE_VALUE"])) || (!empty($detail["PROPERTY_ARTNUMBER_VALUE"]) && !empty($detail["PROPERTY_MANUFACTURER_VALUE"])) ){

                    $detail['article'] = $detail["PROPERTY_ARTICLE_VALUE"] ? : $detail["PROPERTY_ARTNUMBER_VALUE"];
                    $detail['brand_title'] = $detail["PROPERTY_BRAND_TITLE_VALUE"] ? : $detail["PROPERTY_MANUFACTURER_VALUE"];

                    //Поищем доступные варианты в локальной базе
                    $parts = (array) $search->searchLocalDatabaseForPart(array(
                        'article' => $detail['article'],
                        'brand_title' => $detail['brand_title']
                    ), true);


                    foreach ($parts as $part) {
                        $part_obj = new LinemediaAutoPart($part['id']);

                        //Посчитаем цену товара
                        $price = new LinemediaAutoPrice($part_obj);
                        $price_calc = $price->calculate();
                        //$formatted = CurrencyFormat($price_calc, $price->getCurrency());

                        $detail['PRICES'][(int) $price_calc] = (float) $price_calc;
                    }

                    if (count($detail['PRICES']) > 0) {
                        $detail['min_price'] = min(array_keys($detail['PRICES']));

                        //обновление цены в каталогах
                        $arFields = Array(
                            "PRODUCT_ID" => $detail['ID'],
                            "CATALOG_GROUP_ID" => $price_type_id,
                            "PRICE" => $detail['PRICES'][$detail['min_price']]
                        );

                        $res_detail_price = CPrice::GetList(
                            array(),
                            array(
                                "PRODUCT_ID" => $detail['ID'],
                                "CATALOG_GROUP_ID" => $price_type_id
                            )
                        );

                        if ($detail_price = $res_detail_price->Fetch()) {
                            CPrice::Update($detail_price["ID"], $arFields);
                        } else {
                        	$arFields['CURRENCY'] = CCurrency::GetBaseCurrency();
                        	CPrice::Add($arFields);
                        }
                    }
                }
            }
        }
		/**
		* Добавлен вызов события, в котором выгрузка поисходит не по коду, а по артикулу 
		*/
		
		$events = GetModuleEvents("linemedia.auto", "OnAfterPriceListAllImportCode");
        while ($arEvent = $events->Fetch()) {
            try {
                ExecuteModuleEventEx($arEvent, array($files_count, $files));
            } catch (Exception $e) {
                throw $e;
            }
        }
    }



    /**
     * Отправка письма о смене статуса у товара.
     */
    protected static function sendBasketItemStatusMessage($basket_id, $status)
    {
        if (empty($basket_id) || empty($status)) {
            return;
        }

        $basket = new LinemediaAutoBasket();

        $arData  = $basket->getData($basket_id);
        $arProps = $basket->getProps($basket_id);
        $arOrder = CSaleOrder::GetByID($arData['ORDER_ID']);
        $user_id = $arOrder['USER_ID'];
        $arUser  = CUser::GetByID($user_id)->Fetch();


        // Список статусов.
        $statuses = LinemediaAutoOrder::getStatusesList();

        /*
         * Отправка письма об изменении статуса товара:
         * 1. Если был изменен статус заказа, то отправлять только извещение об этом.
         * 2. Если был изменен только статус товара, например одного из всего заказа, то отправлять извещение о изменении статуса этого товара.
         * 3. Если мы меняем статус у всех товаров в заказе, то пользователю должно отправляться только письмо о смене статуса всего заказа, а не по каждому товару.
         */
        $arEventFields = array(
            'EMAIL'         => $arUser['EMAIL'],
            'ORDER_ID'      => $arOrder['ID'],
            'ORDER_DATE'    => $arOrder['DATE_INSERT'],
            'ITEM_NAME'     => $arData['NAME'],
            'ITEM_STATUS'   => '['.$statuses[$status]['ID'].']'.' '.$statuses[$status]['NAME'],
            'ITEM_ART'      => $arProps['article']['VALUE'],
            'ITEM_BRAND'    => $arProps['brand_title']['VALUE'],
            'ITEM_PRICE'    => CurrencyFormat($arData['PRICE'], $arData['CURRENCY']),
            'ITEM_QUANTITY' => $arData['QUANTITY'],
            'ITEM_AMOUNT'   => CurrencyFormat($arData['PRICE'] * $arData['QUANTITY'], $arData['CURRENCY'])
        );

        $event = 'LM_AUTO_SALE_STATUS_CHANGED';
        $lang_id = $arOrder['LID'];

        /*
         * Cоздаём событие (пока не уточнена необходимость)
         */
        $events = GetModuleEvents('linemedia.auto', 'OnAfterSendEventEmail');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$user_id, &$event, &$lang_id, &$arEventFields));
        }

        CEvent::SendImmediate($event, $lang_id, $arEventFields);
    }






    /************************************************************************
    * Служебные функции
    *************************************************************************/

    /**
     * Слияние каталогов.
     */
    protected static function getIntersectCatalogs($catalogs)
    {
        $items = array();

        foreach ($catalogs as $catalog) {
            $hash = md5($catalog['brand_title']);
            if (!in_array($hash, array_keys($items))) {
                $items[$hash] = $catalog;
            }
            $items[$hash]['genertic_articles'] []= $catalog['extra']['gid'];
        }

        foreach ($items as &$item) {
            $item['extra']['gid'] = implode(',', $item['genertic_articles']);
            unset($item['genertic_articles']);
        }

        return $items;
    }

    public static function OnAfterBasketItemCancel_moneyBack($basket_id, $cancelled, $description)
    {
        if ($cancelled == 'N') return;
        $props = LinemediaAutoBasket::getProps($basket_id);
        if ($props['payed']['VALUE'] =='Y') {
            CModule::IncludeModule('sale');
            $item = CSaleBasket::GetByID($basket_id);
            if (intval($item['ORDER_ID']) > 0) {
                $order = CSaleOrder::GetByID($item['ORDER_ID']);
                if (intval($order['USER_ID']) > 0) {
                    $result = CSaleUserAccount::UpdateAccount(
                        $order['USER_ID'],
                        ($item['PRICE']*$item['QUANTITY']),
                        $order['CURRENCY'],
                        GetMessage('LM_AUTO_CANCEL_ITEM_TRANSACT_COMMENT',array('ART'=>$props['article']['VALUE'],
                                                                            'BRAND'=>$props['brand_title']['VALUE'],
                                                                            'ORDER_ID'=>$item['ORDER_ID'])),
                        $item['ORDER_ID']
                    );

                    //отменяем оплату заказа
                    //CSaleOrder::PayOrder($item['ORDER_ID'], 'N', false);

                    //отмена оплаты для товара
                    $obasket = new LinemediaAutoBasket();
                    $obasket->payItem($basket_id, 'N');
                    unset($obasket);
                    //уменшаем сумму заказа
                    CSaleOrder::Update($item['ORDER_ID'], Array('PRICE' => ($order['PRICE']-($item['PRICE']*$item['QUANTITY']))));
                }
                unset($order);
            }
            unset($item);
        } elseif ($props['payed']['VALUE'] =='N') {
            //Это на тот случай, если мы отменяем не оплаченный товар в заказе.
            CModule::IncludeModule('sale');
            $item = CSaleBasket::GetByID($basket_id);
            if (intval($item['ORDER_ID']) > 0) {
                $order = CSaleOrder::GetByID($item['ORDER_ID']);
                if (intval($order['USER_ID']) > 0) {
                    //Уменьшаем цену заказа на цену отменной позиции
                    CSaleOrder::Update($item['ORDER_ID'], Array('PRICE' => ($order['PRICE']-($item['PRICE']*$item['QUANTITY']))));
                }
                unset($order);
            }
            unset($item);
        }
    }

    /*
     * Возврат денег при одобрении возврата товара
     */
    public function OnAfterBasketItemStatus_returnMoneyBack(&$basket_id, &$status) {

        $returnStatusMoneyBack = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_MONEY_BACK');
        if(strlen($returnStatusMoneyBack) < 1 || $status != $returnStatusMoneyBack) return; // не совпадает или не установлен статус возврата денег

        $props = LinemediaAutoBasket::getProps($basket_id);
        if ($props['payed']['VALUE'] =='Y') {
            CModule::IncludeModule('sale');
            $item = CSaleBasket::GetByID($basket_id);
            if (intval($item['ORDER_ID']) > 0) {
                $order = CSaleOrder::GetByID($item['ORDER_ID']);
                if (intval($order['USER_ID']) > 0) {
                    $result = CSaleUserAccount::UpdateAccount(
                        $order['USER_ID'],
                        ($item['PRICE']*$item['QUANTITY']),
                        $order['CURRENCY'],
                        GetMessage('LM_AUTO_RETURN_ITEM_TRANSACT_COMMENT',array('ART'=>$props['article']['VALUE'],
                            'BRAND'=>$props['brand_title']['VALUE'],
                            'ORDER_ID'=>$item['ORDER_ID'])),
                        $item['ORDER_ID']
                    );

                    //отмена оплаты для товара
                    $obasket = new LinemediaAutoBasket();
                    $obasket->payItem($basket_id, 'N');
                    unset($obasket);
                    //уменшаем сумму заказа
                    //CSaleOrder::Update($item['ORDER_ID'], Array('PRICE' => ($order['PRICE']-($item['PRICE']*$item['QUANTITY']))));
                }
                unset($order);
            }
            unset($item);
        }
    }

    public static function OnAfterBasketItemCancelNo_checkOrderSum($basket_id, $cancelled)
    {

        if ($cancelled == 'Y') return;

        if ($cancelled == 'N') {
            $props = LinemediaAutoBasket::getProps($basket_id);
            CModule::IncludeModule('sale');
            if ($props['payed']['VALUE'] =='N') {
                $item = CSaleBasket::GetByID($basket_id);
                if (intval($item['ORDER_ID']) > 0) {
                    $order = CSaleOrder::GetByID($item['ORDER_ID']);
                    if (intval($order['USER_ID']) > 0) {
                        //Если заказ оплачен, то сумма оплаты - это $order['PRICE'], если же заказ не оплачен, то сумма оплаты - $order['SUM_PAID']

                        //Если $order['SUM_PAID'] > 0 значит заказ не оплачен, и в этом случае увеличиваем его сумму на стоимость переотмененного товара
                        if (intval($order['SUM_PAID']) > 0) {
                            //увеличиваем сумму заказа
                            CSaleOrder::Update($item['ORDER_ID'], Array('PRICE' => ($order['PRICE']+($item['PRICE']*$item['QUANTITY']))));
                        } else {
                            //Если $order['SUM_PAID'] < 0, то заказ либо не оплачен вообще, либо оплачен полностью

                            //увеличиваем сумму заказа
                            CSaleOrder::Update($item['ORDER_ID'], Array('PRICE' => ($order['PRICE']+($item['PRICE']*$item['QUANTITY']))));
                            //отменяем оплату заказа
                            CSaleOrder::PayOrder($item['ORDER_ID'], 'N', false);
                            //делаем заказ частично оплаченным на уже оплаченную сумму заказа
                            CSaleOrder::Update($item['ORDER_ID'], Array('SUM_PAID' => $order['PRICE']));
                        }
                    }
                    unset($order);
                }
                unset($item);
            }
        }
    }

    public static function OnAfterBasketItemCancel_checkOrderCancel($basket_id, $cancelled, $description)
    {
        /*
        *   если все позиции заказа отменили --отменяем весь заказ.
        *   если же хоть одному товару сняли отмену -- снимаем отмену всему заказу. потому что это логично.
        */
        $item = CSaleBasket::GetByID($basket_id);
        $order = CSaleOrder::GetByID($item['ORDER_ID']);

        if ($cancelled !== 'Y') {
            if ($order['CANCELED'] == 'Y') {
                CSaleOrder::CancelOrder($order['ID'], 'N');
            }
        }

        if (intval($item['ORDER_ID']) > 0) {
            if ($order['CANCELED'] == 'Y') return;
            $rs = CSaleBasket::GetList(array(), array('ORDER_ID'=>$item['ORDER_ID']), 0,0, array('ID','CODE'));
            while ($item = $rs->Fetch()) {
                $props = LinemediaAutoBasket::getProps($item['ID']);
                if ($props['canceled']['VALUE'] !== 'Y') {
                    return;
                }
            } //while
            CSaleOrder::CancelOrder($order['ID'], 'Y', $description);
        }
    }

/*
    если все позиции заказа оплачены -- ставим,что заказ оплачен.
*/
    public static function  OnAfterBasketItemPay_checkOrderFullyPayed($basket_id, $payed)
    {
        if ($payed!=='Y' || $GLOBALS['LM_AUTO_PAY_ORDER_FROM_BASKET_EVENT'] === 'Y') return;
        $item = CSaleBasket::GetByID($basket_id);
        if (intval($item['ORDER_ID']) > 0) {
            $order = CSaleOrder::GetByID($item['ORDER_ID']);
            if ($order['PAYED'] == 'Y') return;
            $rs = CSaleBasket::GetList(array(), array('ORDER_ID'=>$item['ORDER_ID']), 0,0, array('ID','CODE'));
            while ($item = $rs->Fetch()) {
                $props = LinemediaAutoBasket::getProps($item['ID']);
                if ($props['payed']['VALUE'] !== 'Y') {
                    return;
                }
            } //while
            $GLOBALS['LM_AUTO_PAY_ORDER_FROM_BASKET_EVENT'] = 'Y';


            /*
             * Чтобы не дублировалось списание с внутреннего счета - установим bWithdraw = false
             * TODO: проверить для розницы!!!
             */

            CSaleOrder::PayOrder($order['ID'], 'Y', false, false, 0, array('NOT_CHANGE_STATUS' => 'Y'));
        }
    }

    /*
     * Добавляем результаты поиска в бд
     */
    public function OnSearchResultParts_SearchStatistics($arParams, $arResult)
    {
        if (LinemediaAutoUserHelper::isSearchRobot()) {
            return;
        }
        $article = LinemediaAutoPartsHelper::clearArticle( (string)$arParams['QUERY'] ) ?: '';
        $brand_title = (string) $arParams['BRAND_TITLE'] ?: '';

        $arSupplierCounts = array();

        $originalCount = 0;
        $analogsCount = 0;

        $originalType = 'analog_type_' . LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;

        /*
         * Считаем число оригинальных запчастей и аналогов по каждому поставщику
         */
        foreach ($arResult['PARTS'] as $type => $parts) {

            foreach($parts as $part) {
                $supplierId = empty($part['supplier_id']) ? 0 : $part['supplier_id'];
                if(!array_key_exists($supplierId, $arSupplierCounts)) {
                    $arSupplierCounts[$supplierId] = array(
                        'original' => 0,
                        'analogs' => 0,
                    );
                }
                if ($type == $originalType) {
                    $arSupplierCounts[$supplierId]['original']++;
                } else {
                    $arSupplierCounts[$supplierId]['analogs']++;
                }
            }
        }

        $searchStatistics = new LinemediaAutoSearchStatistics();

        foreach($arSupplierCounts as $supplierId => $arCount) {

            $arFields = array(
                "article" => $article,
                "brand_title" => $brand_title,
                "supplier_id" => $supplierId,
                "branch_id" => false,
                "variants" => $arCount['original'],
                "analogs" => $arCount['analogs']
            );

            /*
             * Cоздаём событие
             */
            $events = GetModuleEvents('linemedia.auto', 'OnBeforeStatisticAdd');
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array(&$arFields));
            }

            $searchStatistics->add($arFields);
        }
    }




    /*
    * Возможность изменить колонку из которой берётся цена
    */
    public function OnItemPriceConstruct_selectUserGroupPriceColumn(&$_price_obj)
    {
	    /*if(in_array(1, CUser::GetUserGroupArray())) {
			$_this->price_field = 'price_2';
		}*/
    }

    /**
     * function allowing to tune outcome of searching depending on given modificator
     * @param array $params
     * @param array $searchResult
     * @param string $modificatorTitle
     * @return void
     */
    public function OnSearchResultParts_ModifySearchOutcome(array $params, array &$searchResult, $modificatorTitle) {
    	
        if ($modificatorTitle == null) {
            return;
        }
        
    	//check whether modificator is available
    	if (!\LinemediaAutoModule::isFunctionEnabled(\LinemediaAutoSearchModificator::API_NAME)) {
    		 
    		\LinemediaAutoDebug::add(
    				'Modificator',
    				\LinemediaAutoSearchModificator::WARNING_MESSAGE,
    				LM_AUTO_DEBUG_ERROR
    		);
    		return;
    	}
    
    	//create modificator by using partition title (searchs modificator partition)
    	$modificator = new \LinemediaAutoSearchModificator($searchResult['PARTS'], $modificatorTitle);
    	//create each modificators strategy comprising in partition  	
        LinemediaAutoDebug::add('Load modificators config: iblock elements + props', print_r('', true), LM_AUTO_DEBUG_WARNING);

    	$modificator->initializeModifyingStrategies(
    			new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . \LinemediaAutoSearchModificator::DIRECTORY_MODIF)
    	);
        LinemediaAutoDebug::add('Initialization of all modificators (+ suppliers info for each modificator)', print_r('', true), LM_AUTO_DEBUG_WARNING);

    	//try to modify search result
    	$searchResult['PARTS'] = $modificator->execute();
    
    }


    /**
     * allow to unite group spares by comparasing their analog_type name
     * where App - Appellation (title)
     */
    public function OnSearchExecuteEnd_UniteGroupsWithSimilarApp($searchCondition, $article, $catalog, &$searchResult, $type, $resultInfo, $searchModif, $sortOrderParameters) {

        $setGroups = array();
        foreach ($searchResult as $groupName => $listOfItems) {

            $groupId = end(explode('_', $groupName));
            $appellationGroup = \COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ANALOGS_GROUPS_'.$groupId, GetMessage('LM_AUTO_ANALOG_GROUP_'.$groupId));

            if (($key = array_search($appellationGroup, $setGroups)) !== false) {

                $searchResult['analog_type_'.$key] = array_merge($searchResult['analog_type_'.$key], $searchResult['analog_type_'.$groupId]);
                unset($searchResult['analog_type_'.$groupId]);
                continue;
            }

            $setGroups[$groupId] = $appellationGroup;

        }


        $searchResult = \LinemediaAutoPartsHelper::sortCatalogs($searchResult, current($sortOrderParameters), next($sortOrderParameters));

    }
	
	public function OnBeforeBasketStatusesChange_CheckPermissions($groupId, $activeAction, $basketId)
	{         
        $arAllowedActions = LinemediaAutoProductStatus::isActionAllowed($groupId, $activeAction, $basketId); 
        return $arAllowedActions;
	}

    public function OnAfterSetTasksForModule_SetIblockPermissions(&$module_id, &$arGroupTask) {

        if($module_id != 'linemedia.auto') return;

        $arTaskBindings = array();

        global $DB;
        $module_id = $DB->ForSql($module_id);
        $sql_str = "SELECT *
            FROM b_task T
            WHERE T.MODULE_ID='".$module_id."'";
        $r = $DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        $arTasks = array();
        while($arR = $r->Fetch()) {
            $arTasks[] = $arR;
            $binding = $arR['BINDING'];
            $letter = $arR['LETTER'];
            $id = $arR['ID'];
            $arTaskBindings[$binding][$letter] = $id;
			 $arTaskBindings2[$binding][$id] = $letter;
        }

        $arSetTask = array();
        foreach($arGroupTask as $nameType => $t)
        {
            if($nameType == "CTASKS" || $nameType == "STASKS")
            {
                foreach($t as $k => $grArr)
                {
                    foreach($grArr as $gr_id => $oTask)
                    {
                        $arSetTask[$oTask['ID']][] = $gr_id;
                    }
                }
            }
        }
		
		
		$arAllLetters = LinemediaAutoGroup::getLMAutoLetterAndIBLetterArray();
		
		$rsGroups = CGroup::GetList(($by="id"), ($order="asc"), array()); // выбираем группы	
		while($arrGroups = $rsGroups -> Fetch())
		{
			$arGroups[] = $arrGroups["ID"];
		}
		
		/*Удалим биндинги, для которых не нужно устанавливать права на инфоблоки*/		
		unset($arTaskBindings2[LM_AUTO_ACCESS_BINDING_ORDERS]);
		unset($arTaskBindings2[LM_AUTO_ACCESS_BINDING_STATUSES]);
		unset($arTaskBindings2[LM_AUTO_ACCESS_BINDING_PRICES_IMPORT]);
		unset($arTaskBindings2[LM_AUTO_ACCESS_BINDING_PRODUCTS]);		
		unset($arTaskBindings2[LM_AUTO_ACCESS_BINDING_STATISTICS]);
		unset($arTaskBindings2[LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS]);
		
		//Сформируем полный массив доступов $arAccesses в виде array(биндинг => array(буква=>ИД Таска))
		foreach($arTaskBindings2 as $k => $lGr)
		{
			foreach($lGr as $taskId => $letter)
			{
				if(intval($taskId) > 0 && array_key_exists($taskId, $arSetTask))
				{
					$arAccesses[$k][$letter] = $arSetTask[$taskId];
				}
			}
		}

        // Поставщики		
//		$arGroupLetters = array();
//		foreach($arAccesses[LM_AUTO_ACCESS_BINDING_SUPPLIERS] as $l => $arGr)
//		{
//			foreach($arGr as $gr)
//			{
//
//				$arGroupLetters[$gr] = $arAllLetters[$l];
//				$arGroupLettersT[] = $gr;
//			}
//
//		}
//
//		$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT_".LM_AUTO_ACCESS_BINDING_SUPPLIERS, LM_AUTO_MAIN_ACCESS_DENIED);
//		$arGroupLetters["2"] = $arAllLetters[$GROUP_DEFAULT_RIGHT]; //Для группы Все пользователи доступ равен доступу по умолчанию

		//$iblockId = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
		//self::setIblockGroupsRights($iblockId, $arGroupLetters); // необходимо вызывать один раз для всех групп

		
		// Ценообразование
		$arGroupLetters = array();
		foreach($arAccesses[LM_AUTO_ACCESS_BINDING_PRICES] as $l => $arGr)
		{
			foreach($arGr as $gr)
			{
				$arGroupLetters[$gr] = $arAllLetters[$l];
			}	
		}
		
		$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT_".LM_AUTO_ACCESS_BINDING_PRICES, LM_AUTO_MAIN_ACCESS_DENIED);		
		$arGroupLetters["2"] = $arAllLetters[$GROUP_DEFAULT_RIGHT]; //Для группы Все пользователи доступ равен доступу по умолчанию
		
		$iblockId = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_DISCOUNT');
		self::setIblockGroupsRights($iblockId, $arGroupLetters);

        // VIN
		$arGroupLetters = array();
		foreach($arAccesses[LM_AUTO_ACCESS_BINDING_VIN] as $l => $arGr)
		{
			foreach($arGr as $gr)
			{
				$arGroupLetters[$gr] = $arAllLetters[$l];
			}	
		}
		
		$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT_".LM_AUTO_ACCESS_BINDING_VIN, LM_AUTO_MAIN_ACCESS_DENIED);		
		$arGroupLetters["2"] = $arAllLetters[$GROUP_DEFAULT_RIGHT]; //Для группы Все пользователи доступ равен доступу по умолчанию
		
		$iblockId = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_VIN');
        self::setIblockGroupsRights($iblockId, $arGroupLetters);
    }

    private static function setIblockGroupsRights($iblockId, $arGroupLetters) {

        if(CModule::IncludeModule('iblock')) {

            // проверяем расширенный режим доступа, включаем
            if (CIBlock::GetArrayByID($iblockId, "RIGHTS_MODE") != "E") {
                $ib = new CIBlock;
                $ib->Update($iblockId, array("RIGHTS_MODE" => "E"));
            }

            $tasks = false;
            $dbTask = CTask::Getlist(array(), array('MODULE_ID'=>'iblock', 'BINDING' => 'iblock'));

            while($task = $dbTask->Fetch()) {
                $letter = $task['LETTER'];
                $tasks[$letter] = $task['ID'];
            }

            $i=0;
            $obRights = new CIBlockRights($iblockId);

            $curRights = $obRights->GetRights();

            foreach($arGroupLetters as $groupId => $rightLetter) {
                $arSetRights['n'.$i] = array(
                    'GROUP_CODE' => 'G'.$groupId,
                    'TASK_ID' => $tasks[$rightLetter],
                    //'DO_CLEAN' => 'N',
                    //'DO_INHERIT' => 'N',
                );
                $arR[$groupId] = $rightLetter;

                $i++;
            }
            // author
            $arSetRights['n'.$i] = array(
                'GROUP_CODE' => 'CR',
                'TASK_ID' => $tasks['X'],
                //'DO_CLEAN' => 'N',
                //'DO_INHERIT' => 'N',
            );

            $obRights->SetRights($arSetRights);
        }
    }

	/*
	 * Подмена пользователя при оформлении заказа менеджером
	 */

	function OnBeforeOrderAdd_RegisterUser(&$arFields, $useManagerTemplate)
	{
		if ($useManagerTemplate != 'Y') {
			return;
		}

		define('CUSTOMER_TYPE_ID', 1);
		define('COMPANY_TYPE_ID', 2);

		global $USER;
		
		if(!CModule::IncludeModule('linemedia.autobranches')) {
			return;
		};


		/*
		* Создавать нового пользователя при оформлении заказа
		* может только менеджер филиала
		*/

		$managersID = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
		
		//Группа директор филиала
		 $filter = Array
		(
			"STRING_ID"  => "LM_AUTO_CABINET_DIRECTOR_DIRECTORS_GROUP",
		);
		$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter); // выбираем группы
		while($arGroups = $rsGroups -> Fetch())
		{
			$director_group = $arGroups["ID"]; //к какой группе привязан текущий филиал			
		}

		$user_id = $USER->getID();
		$user = CUser::getByID($user_id);
		$user = $user->Fetch();

		if (!empty($user['UF_DEALER_ID'])  && (in_array($managersID, $USER->GetUserGroupArray()) || in_array($director_group, $USER->GetUserGroupArray()))) {
 
						
			// id текущего пользователя (менеджера)
			//$manager_id = $arFields['USER_ID'];

				
			/*
			* Устанавливаем текущего менеджера и филиала для нового пользователя
			*/

			$userID = (int)$_REQUEST['user'] ?: $arFields["USER_ID"];
            
            
            

			if ((int)$userID > 0) {
                
                 $is_branch_setted =  LinemediaAutoUser::setUserBranchByManagerId($userID, $arFields["MANAGER_ID"], true);

			
				$branchUser = new LinemediaAutoBranchesUser($userID);
				if((int)$arFields["MANAGER_ID"] > 0)
				{	
					// устанавливаем менеджера
					$branchUser->setManager($arFields["MANAGER_ID"]);		
				}			
			}
		}
	}

    /**
     * Создание транзакции при смене статуса
     * @param $basket_id
     * @param $status
     * @param LinemediaAutoBasket $basket
     * @return array
     */
    function OnBeforeBasketItemStatus_CreateTransaction($basket_id, $status, $basket) {

        global $APPLICATION;

        // Проверим что статус реально меняется
        $props = $basket->getProps($basket_id);
        $old_status = $props['status']['VALUE'];
        if($status == $old_status) {
            $APPLICATION->ThrowException(GetMessage('LM_AUTO_ERR_NO_CHANGE_STATUS'));
            return false; // отменяем смену статуса
        }

        $transaction = new LinemediaAutoTransaction();

        $isSuccess = $transaction->createTransaction($basket_id, $status, $basket);

        if(!$isSuccess) {
            $APPLICATION->ThrowException($transaction->getInformativeMessage());
        }
        return $isSuccess;
    }

    /**
     * Обработка битриксовых транзакций
     * @deprecated
     * @param $transaction_id
     * @param $transaction_params
     * @return bool
     */
    function OnAfterAddTransact_process($transaction_id, $transaction_params) {

        // если мы сами создали транзакцию - ничего не делаем
        if($transaction_params['NOTES'] == LinemediaAutoTransaction::LM_TRANSACTION_FLAG) {
            LinemediaAutoTransaction::clearBxTransactionNotes($transaction_id);
            return true;
        }

        define( "LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log_transaction.txt");

        AddMessage2Log(print_r(array($transaction_id => $transaction_params), true));

        $transaction = new LinemediaAutoTransaction();
        $transaction_params['ID'] = $transaction_id;
        $transaction->extendTransaction($transaction_params);

        return true;
    }

    function OnAfterPriceListImport_writeHistory($supplier_id, $count, $task_id, $total_price) {
		
		// История импорта должна работать по умолчанию (без оплаты)
		/* if(!LinemediaAutoImportHistory::isEnabled())
			return; */
			
        if(intval($task_id) < 1) return;

        $fields = array(
            'TASK_ID' => $task_id,
            'SUPPLIER_ID' => $supplier_id,
            'PARTS_COUNT' => $count,
            'SUM_PRICE' => $total_price,
            'DATE' => ConvertTimestamp(false, 'FULL'),
        );

        $history = new LinemediaAutoImportHistory($supplier_id);
        $history->add($fields);
    }

    function OnBeginApiQuery_apiHandler($cmd, $data, $driver, &$return_response) {

        if(!LinemediaAutoCrossesApiDriver::isEnabled()) {
            return;
        }

        if($cmd == 'getAnalogs2Multiple') {

            $api_crosses_driver = new LinemediaAutoCrossesApiDriver();
            $return_response = $api_crosses_driver->getAnalogs2Multiple($data);
        }
    }
}

<?php
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

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


/**
* События для главного модуля авто
*/
class LinemediaAutoAnalogsSimpleEventLinemediaAuto
{
    
    /**
     * Добавляем в результаты поиска информацию от Linemedia API.
     * @param array $search_conditions Критерии поиска
     * @param array $articles_to_search Артикулы для посика
     * @param array $catalogs_to_search Каталоги для поиска
     * @param array $search_article_results Результаты поиска
     */
    public function OnSearchExecuteBegin_addSimpleAnalogs(&$search_conditions, &$articles_to_search, &$catalogs_to_search, &$search_article_results)
    {
        if (!CModule::IncludeModule('linemedia.auto')) {
            return;
        }
        
        /*
         * В настройках главного модуля есть вкладка "Поиск"
         * Там указываются галочками источники поиска аналогов
         * В частности использование локальной БД
         */
        $LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES', 'Y');
        if ($LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES != 'Y') {
            return;
        }
        
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES             = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES',          'Y');
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL    = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL', 'Y');
        $LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES          = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES',       'Y');
        $LM_AUTO_MAIN_SEARCH_OEM_CROSSES_SOUGHT         = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_SEARCH_OEM_CROSSES_SOUGHT', 'Y');
        
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES             = ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES == 'Y');
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL    = ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL == 'Y');
        $LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES          = ($LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES == 'Y');
        $LM_AUTO_MAIN_SEARCH_OEM_CROSSES_SOUGHT         = ($LM_AUTO_MAIN_SEARCH_OEM_CROSSES_SOUGHT == 'Y');
        
        
        
        /*
        * сколько максимум новых аналогов должно быть проверено после последнего прохода
        * если аналогов будетбольше, рекурсия прекратится
        */
        $MAX_NEW_ANALOGS_TO_CONTINUE_SEARCH = 500; 
        
        /*
        * сколько максимум всего аналогов должно быть найдено для прекращения рекурсии
        */
        $MAX_TOTAL_ANALOGS_TO_CONTINUE_SEARCH = 1000;
        
        
        
        /*
         * Группы для поиска в TecDoc.
         */
        $search_analog_groups = array(LM_AUTO_MAIN_ARTICLE_TYPE_OE, LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED);
        $search_analog_original_groups = array(LM_AUTO_MAIN_ARTICLE_TYPE_OE, LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED);
        
        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Simple analogs module added');
        
        /*
         * Если нет артикула
         */
        if (!isset($search_conditions['query'])) {
            return;
        }
        
        
        /*
         * В случае, если артикул принадлежит к группе оригинальных замен или замен производителем устаревших артикулов
         * Надо поискать его в текдок
         */
        $additional_tecdoc_request_articles = array();
        
        
        /*
         * Используем ли мы рекурсивный поиск аналогов.
         */
        $recursive_search_enabled = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH', 'N') == 'Y';

		/*
		 *
		 */
		$recursive_search_counts = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS', '3');

		/*
		 * Использовать ли поиск в текдоке оригинальных аналогов для кроссов
		 */
        $tecdoc_request_enabled = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC', 'N') == 'Y';
        
        
        /*
         * Вернём результат
         */
        $source_parts   = array(); // это массив деталей, по которым найдены кроcсы, нужен для определения каталогов
        $analogs        = array(); // это массив деталей - кроссов
        
        $analog_obj = new LinemediaAutoAnalogsSimpleAnalog();
        
        
        $filter = array();
        
        if ($search_conditions['query']) {
            $filter['article'] = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);
        }
        
        if ($search_conditions['brand_title']) {
            $filter['brand_title'] = strtoupper($search_conditions['brand_title']);
        }
        
        if (count($filter) == 0) {
            LinemediaAutoDebug::add('Simple analogs - no search performed (empty filter)');
            return;
        } else {
            LinemediaAutoDebug::add('Simple analogs', print_r($filter, true));
        }
        
        /*
         * Запрос (словоформы учитываются внутри)
         */
        try {
            $result = $analog_obj->find($filter);
        } catch (Exception $e) {
            throw $e;
        }
        
        while ($item = $result->Fetch()) {
            /*
             * Надо вернуть не саму деталь, а другую.
             */
            $suffix = (LinemediaAutoPartsHelper::clearArticle($item['article_original']) == $filter['article']) ? ('analog') : ('original');
            
            $article = $item['article_' . $suffix];
            $brand_title = $item['brand_title_' . $suffix];
            
            // Определим группу аналогов.
            $group = LinemediaAutoAnalogsSimpleAnalog::getAnalogGroup($item[ 'group_' . $suffix ]);
            
            $part = array(
                'id'            => $item['id'],
                'article'       => $article,
                'brand_title'   => $brand_title,
                'analog-source' => 'analogs-simple',
                'analog_type'   => $group, // LM_AUTO_MAIN_ARTICLE_TYPE_COMPARABLE,
                'trace'			=> array('#0 ['.$search_conditions['brand_title'].' '.$search_conditions['query'].']'),
            );
            
            $key = $part['article'] . '|' . $part['brand_title']; // part key
            $analogs [$key]= $part;
            
            /*
             * В случае, если артикул принадлежит к группе оригинальных замен или замен производителем устаревших артикулов.
             * Надо поискать его в текдок.
             */
            
            if (in_array($group, $search_analog_groups) && $tecdoc_request_enabled) {
                $additional_tecdoc_request_articles []= $part;
            }
            
            
            /*
             * Добавим также в массив деталь, по которой найден кросс.
             */
            $source_suffix = ($item['article_original'] == $filter['article']) ? ('original') : ('analog');
            $source_part = array(
                'article'       => $item['article_' . $source_suffix],
                'brand_title'   => $item['brand_title_' . $source_suffix],
                'analog-source' => 'analogs-simple',
            );
            $source_parts[$source_part['brand_title']] = $source_part;
        }
        
		/*
         * Если не включен ни рекурсивный поиск, ни поиск по TecDoc,
         * то в отладке выводим только аналоги из лок. базы кроссов, не включая аналоги TecDoc
         */
		if (!$tecdoc_request_enabled && !$recursive_search_enabled) {
			LinemediaAutoDebug::add('Simple analogs result ('.count($analogs).')', print_r($analogs, true), LM_AUTO_DEBUG_WARNING);
		}

        /*
         * Для каждого артикула поищем аналоги в локальных кроссах, пока они не кончатся.
         */
        $stop_search = (!$recursive_search_enabled);
        
        /*
         * Не надо повторно обрабатывать одни и те же кроссы
         */
        $used_ids = array();
        foreach ($analogs as $a) {
            $used_ids [$a['id']] = $a['id'];
        }
        
        
        /*
        * Необходимо включить в поиск аналоги, пришедшие ранее из текдока
        * они лежат в articles_to_search
        */
        foreach ($articles_to_search as $part) {
            unset($part['id']);
            $key = $part['article'] . '|' . $part['brand_title']; // part key
            $analogs[$key] = $part;
        }
        
        
        /*
         * Объект поиска
         */
        $analog_obj = new LinemediaAutoAnalogsSimpleAnalog();
        
        $loop = 0;
        while (!$stop_search) {
        	$loop++;
        	
            /*
             * Найдены ли новые аналоги
             */
            $new_analogs_to_check = 0;
            foreach ($analogs as $k => $analog) {
            	
            	/*
                 * Cross already checked
                 */
                if ($analogs[$k]['checked'] == true) {
                    continue;
                }
                
                $filter = array(
                    'article' => LinemediaAutoPartsHelper::clearArticle($analog['article']),
                    'brand_title' => $analog['brand_title'],
                    '!id' => $used_ids,
                );
                
                /*
                 * Запрос
                 */
                try {
                    $result = $analog_obj->find($filter);
                } catch (Exception $e) {
                    throw $e;
                }
                
                while ($item = $result->Fetch()) {
                    
                    /*
                     * Кросс уже проверен.
                     */
                    if (isset($used_ids[$item['id']])) {
                    	continue;
                    }
                    $used_ids [$item['id']] = $item['id'];
                    
                    
                    /*
                     * Надо вернуть не саму деталь, а другую.
                     */
                    $suffix = ($item['article_original'] == $filter['article']) ? ('analog') : ('original');
                    
                    $article = $item['article_' . $suffix];
                    $brand_title = $item['brand_title_' . $suffix];
                    
                    // Определим группу аналогов.
                    $group = LinemediaAutoAnalogsSimpleAnalog::getAnalogGroup($item['group_' . $suffix]);
                    
                    $part = array(
                        'id'            => $item['id'],
                        'article'       => $article,
                        'brand_title'   => $brand_title,
                        'analog-source' => 'analogs-simple',
                        'analog_type'   => $group, // LM_AUTO_MAIN_ARTICLE_TYPE_COMPARABLE,
                        'trace'			=> $analog['trace'],
                    );
                    $part['trace'][] = '#1-' . $loop . ' [' . $analog['brand_title'] . ' ' . $analog['article'].']';
                    
                    
                    /*
                    * Если эта запчасть уже есть, переходим к следующей
                    */
                    $key = $part['article'] . '|' . $part['brand_title']; // part key
                    if(isset($analogs [$key]))
                    	continue;
                    
                    
                    /*
                     * В случае, если артикул принадлежит к группе оригинальных замен или замен производителем устаревших артикулов,
                     * надо поискать его в текдок.
                     */
                    if (
                         in_array($group, $search_analog_groups)
                         && $tecdoc_request_enabled
                    ) {
                        $additional_tecdoc_request_articles []= $part;
                    }
                    
                    /*
                     * Для аналогов >=2 уровня замена должна попадать в группу родительской детали
                     * потому что она замена не для искомого артикула, а просто ещё одиин неоригинал
                     */
                    if ($group == LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED) {
	                   $part['analog_type'] =  $analog['analog_type'];
                    }
                    
                    
                    $analogs [$key]= $part;
                    $new_analogs_to_check++;
                }
               
                /*
                 * Этот кросс проверили.
                 */
                $analogs[$k]['checked'] = true;
            }
            
            // дальше нет смысла проверять
            if($new_analogs_to_check > $MAX_NEW_ANALOGS_TO_CONTINUE_SEARCH)
	            $stop_search = true;
            
            if ($new_analogs_to_check == 0 || $loop == $recursive_search_counts) {
                $stop_search = true;
            }
        }
        
        
        /*
         * В случае, если артикул принадлежит к группе оригинальных замен или замен производителем устаревших артикулов
         * Надо поискать его в текдок
         */
        if (
            $tecdoc_request_enabled
            && count($additional_tecdoc_request_articles) > 0
        ) {
            
            
            $api_request = array();
            
            /*
	         * Удаление дубликатов.
	         */
	        $unique = array();
	        foreach ($additional_tecdoc_request_articles as $part) {
		        $unique[$part['article'] . '|' . $part['brand_title']] = $part;
	        }
	        $additional_tecdoc_request_articles = array_values($unique);
            
            
            /*
             * Конструирование запроса.
             */
            $wordforms = new LinemediaAutoWordForm();
            foreach ($additional_tecdoc_request_articles as $api_argument) {
                $titles = (array) $wordforms->getBrandWordforms($api_argument['brand_title']);
                $titles []= $api_argument['brand_title'];
                
                foreach ($titles as $title) {
                    $api_request []= array(
                        'article' => $api_argument['article'],
                        'brand_title' => $title
                    );
                }
            }
            
            /*
             * Запрос.
             */
            try {
                /*
                 * Используем настройки модуля (вкладка поиск), чтобы определить, какие аналоги мы хотим получить в ответе
                 *
                 * Варианты: 
                 *  Искать прямые кроссы в TecDoc
                 *  Искать оригинальные кроссы в TecDoc
                 *  Искать кроссы в БД Linemedia
                 *  Искать кроссы в локальной БД    ----   используется в модуле простых аналогов
                 */
                foreach ($api_request as &$api_argument) {
                    $api_argument['tecdoc_crosses'] = $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES;
                    $api_argument['tecdoc_crosses_original'] = $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL;
                    $api_argument['linemedia_crosses'] = $LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES;
                    
                    if($LM_AUTO_MAIN_SEARCH_OEM_CROSSES_SOUGHT)
                    	$api_argument['tecdoc_crosses_original'] = false;
                    
                }
                
                $api = new LinemediaAutoApiDriver();
                $response = $api->query('getAnalogs2Multiple', $api_request);
                
                LinemediaAutoDebug::add('Search Linemedia API recursive', print_r($response, true));
            } catch (Exception $e) {
                //LinemediaAutoDebug::add('Search Linemedia API:' . $e->GetMessage(), false, LM_AUTO_DEBUG_ERROR);
                // Продолжаем работу скрипта
            }
            
            /*
             * Разбор ответа
             */
            foreach ((array) $response['data'] as $request) {
                foreach ((array) $request['analogs']['parts'] as $part) {
                	$part['extra']['gid'] = $part['generic_article_id'];
                	
                	$key = $part['article'] . '|' . $part['brand_title']; // part key
                	
                    $analogs[$key]= $part;
                }
            }
        }
        
        /*
         * Для всех найденных оригинальных артикулов необходимо добавить локальные кроссы.
         */
        if ($tecdoc_request_enabled) {
            
            $stop_search = (!$recursive_search_enabled);
            
            
            /*
            * Если аналогов много, искать их нет смысла
            */
            if(count($analogs) > $MAX_TOTAL_ANALOGS_TO_CONTINUE_SEARCH)
            	$stop_search = true;
            
	        $loop = 0;
	        while (!$stop_search) {
	            $loop++;
	            /*
	             * Найдены ли новые аналоги
	             */
	            $new_analogs_to_check = 0;
	            foreach ($analogs as $k => $analog) {
	            	
	            	/*
	                 * Cross already checked
	                 */
	                if ($analogs[$k]['checked'] == true) {
	                    continue;
	                }
	                
	                $filter = array(
	                    'article' => LinemediaAutoPartsHelper::clearArticle($analog['article']),
	                    'brand_title' => $analog['brand_title'],
	                    '!id' => $used_ids,
	                );
	                
	                /*
	                 * Запрос
	                 */
	                try {
	                    $result = $analog_obj->find($filter);
	                } catch (Exception $e) {
	                    throw $e;
	                }
	                
	                while ($item = $result->Fetch()) {
	                    
	                    /*
	                     * Кросс уже проверен.
	                     */
	                    if (isset($used_ids[$item['id']])) {
	                    	continue;
                        }
	                    $used_ids [$item['id']] = $item['id'];
	                    
	                    /*
	                     * Надо вернуть не саму деталь, а другую.
	                     */
	                    $suffix = ($item['article_original'] == $filter['article']) ? ('analog') : ('original');
	                    
	                    $article = $item['article_' . $suffix];
	                    $brand_title = $item['brand_title_' . $suffix];
	                    
	                    // Определим группу аналогов.
	                    $group = LinemediaAutoAnalogsSimpleAnalog::getAnalogGroup($item['group_' . $suffix]);
	                    
	                    $part = array(
	                        'id'            => $item['id'],
	                        'article'       => $article,
	                        'brand_title'   => $brand_title,
	                        'analog-source' => 'analogs-simple',
	                        'analog_type'   => $group, // LM_AUTO_MAIN_ARTICLE_TYPE_COMPARABLE,
	                        'trace'			=> $analog['trace'],
	                    );
	                    $part['trace'][] = '2-' . $loop . ' [' . $analog['brand_title'] . ' ' . $analog['article'].']';
	                    
	                    
	                    
	                    $key = $part['article'] . '|' . $part['brand_title']; // part key
	                    if(isset($analogs[$key]))
	                    	continue;
	                    
	                    /*
	                     * В случае, если артикул принадлежит к группе оригинальных замен или замен производителем устаревших артикулов,
	                     * надо поискать его в текдок.
	                     */
	                    if (
	                        in_array($group, $search_analog_groups)
	                        && $tecdoc_request_enabled
	                    ) {
	                        $additional_tecdoc_request_articles []= $part;
	                    }
	                    
	                    $additional_tecdoc_request_articles []= $part;
	                    
	                    /*
	                     * Для аналогов >=2 уровня замена должна попадать в группу родительской детали
	                     * потому что она замена не для искомого артикула, а просто ещё одиин неоригинал
	                     */
	                    if ($group == LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED) {
		                   $part['analog_type'] =  $analog['analog_type'];
	                    }
	                    
	                    $analogs[$key]= $part;
	                    
	                    $new_analogs_to_check++;
	                }
	               
	                /*
	                 * Этот кросс проверили.
	                 */
	                $analogs[$k]['checked'] = true;
	            }
	            
	            if($new_analogs_to_check > $MAX_NEW_ANALOGS_TO_CONTINUE_SEARCH || count($analogs) > $MAX_TOTAL_ANALOGS_TO_CONTINUE_SEARCH)
	            	$stop_search = true;
	            
	            if ($new_analogs_to_check == 0) {
	                $stop_search = true;
	            }
	        }
            
        }
        
        
        /*
         * Удалим дубликаты аналогов для экономии ресурсов при поиске по локальной БД
         */
        $analogs_clear = array();
        foreach ($analogs as $analog) {
            // Если мы проставили id, уберем его из фильтра.
            unset($analog['id']);
            
            $analogs_clear[$analog['article'] . '|' . $analog['brand_title'] . '|' . $analog['analog_type']] = $analog;
        }
        $analogs = array_values($analogs_clear);
        
        
        
        /*
         * А не вернулись ли нам каталоги?
         * Если задан brand_title - каталогов быть не может
         * интересуют нас только железки с совпадающими с запрошенным артикулом, но  разными брендами, за сим выбрасываем всё,
         * что не совпадает по артикулу
         */
        if ($search_conditions['brand_title'] == '') {
            $catalogs = array();
            foreach ($source_parts as $part) {

                if ($part['article'] !== $search_conditions['query']) continue;

                $catalogs []= array(
                    'title'         => '',
                    'brand_title'   => $part['brand_title'],
                    'analog-source' => 'analogs-simple',
                );
            }
            foreach ($analogs as $part) {
                if ($part['article'] !== $search_conditions['query'] || !$part['brand_title']) continue;

                $catalogs []= array(
                    'title'         => '',
                    'brand_title'   => $part['brand_title'],
                    'analog-source' => 'analogs-simple',
                );
            }
            
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Simple analogs returned catalogs', print_r($catalogs, true), LM_AUTO_DEBUG_WARNING);
            
            $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
        }
        
        
        /*
        * Нарисуем змейку из trace
        */
        foreach($analogs AS $k => $analog) {
	        if(isset($analog['trace'])) {
		        $analogs[$k]['trace'] = join(' &rarr; ', $analog['trace']) . ' &rarr; ['.$analog['brand_title'].' '.$analog['article'].']';
	        }
	        
        }
        
        
        
        
        /*
         * Если включен либо рекурсивный поиск, либо поиск по TecDoc,
         * то в отладке выводим и аналоги TecDoc в том числе как результат работы модуля простых аналогов,
         * иначе выше выводим только аналоги, найденные в лок. базе
         */
		if ($tecdoc_request_enabled || $recursive_search_enabled) {
        	LinemediaAutoDebug::add('Simple analogs result ('.count($analogs).')', print_r($analogs, true), LM_AUTO_DEBUG_WARNING);
		}

        
        /*
         * Объединим данные, которые уже были, с новыми.
         */
        $articles_to_search = array_merge_recursive($articles_to_search, $analogs);
    }
    
    
    
}

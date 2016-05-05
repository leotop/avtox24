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
* ������� ��� �������� ������ ����
*/
class LinemediaAutoAnalogsSimpleEventLinemediaAuto
{
    
    /**
     * ��������� � ���������� ������ ���������� �� Linemedia API.
     * @param array $search_conditions �������� ������
     * @param array $articles_to_search �������� ��� ������
     * @param array $catalogs_to_search �������� ��� ������
     * @param array $search_article_results ���������� ������
     */
    public function OnSearchExecuteBegin_addSimpleAnalogs(&$search_conditions, &$articles_to_search, &$catalogs_to_search, &$search_article_results)
    {
        if (!CModule::IncludeModule('linemedia.auto')) {
            return;
        }
        
        /*
         * � ���������� �������� ������ ���� ������� "�����"
         * ��� ����������� ��������� ��������� ������ ��������
         * � ��������� ������������� ��������� ��
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
        * ������� �������� ����� �������� ������ ���� ��������� ����� ���������� �������
        * ���� �������� �����������, �������� �����������
        */
        $MAX_NEW_ANALOGS_TO_CONTINUE_SEARCH = 500; 
        
        /*
        * ������� �������� ����� �������� ������ ���� ������� ��� ����������� ��������
        */
        $MAX_TOTAL_ANALOGS_TO_CONTINUE_SEARCH = 1000;
        
        
        
        /*
         * ������ ��� ������ � TecDoc.
         */
        $search_analog_groups = array(LM_AUTO_MAIN_ARTICLE_TYPE_OE, LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED);
        $search_analog_original_groups = array(LM_AUTO_MAIN_ARTICLE_TYPE_OE, LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED);
        
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add('Simple analogs module added');
        
        /*
         * ���� ��� ��������
         */
        if (!isset($search_conditions['query'])) {
            return;
        }
        
        
        /*
         * � ������, ���� ������� ����������� � ������ ������������ ����� ��� ����� �������������� ���������� ���������
         * ���� �������� ��� � ������
         */
        $additional_tecdoc_request_articles = array();
        
        
        /*
         * ���������� �� �� ����������� ����� ��������.
         */
        $recursive_search_enabled = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_USE_RECURSIVE_SEARCH', 'N') == 'Y';

		/*
		 *
		 */
		$recursive_search_counts = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_RECURSIVE_SEARCH_COUNTS', '3');

		/*
		 * ������������ �� ����� � ������� ������������ �������� ��� �������
		 */
        $tecdoc_request_enabled = COption::GetOptionString('linemedia.autoanalogssimple', 'LM_AUTO_ANALOGSSIMPLE_COMMON_SEARCH_TECDOC', 'N') == 'Y';
        
        
        /*
         * ����� ���������
         */
        $source_parts   = array(); // ��� ������ �������, �� ������� ������� ���c��, ����� ��� ����������� ���������
        $analogs        = array(); // ��� ������ ������� - �������
        
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
         * ������ (���������� ����������� ������)
         */
        try {
            $result = $analog_obj->find($filter);
        } catch (Exception $e) {
            throw $e;
        }
        
        while ($item = $result->Fetch()) {
            /*
             * ���� ������� �� ���� ������, � ������.
             */
            $suffix = (LinemediaAutoPartsHelper::clearArticle($item['article_original']) == $filter['article']) ? ('analog') : ('original');
            
            $article = $item['article_' . $suffix];
            $brand_title = $item['brand_title_' . $suffix];
            
            // ��������� ������ ��������.
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
             * � ������, ���� ������� ����������� � ������ ������������ ����� ��� ����� �������������� ���������� ���������.
             * ���� �������� ��� � ������.
             */
            
            if (in_array($group, $search_analog_groups) && $tecdoc_request_enabled) {
                $additional_tecdoc_request_articles []= $part;
            }
            
            
            /*
             * ������� ����� � ������ ������, �� ������� ������ �����.
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
         * ���� �� ������� �� ����������� �����, �� ����� �� TecDoc,
         * �� � ������� ������� ������ ������� �� ���. ���� �������, �� ������� ������� TecDoc
         */
		if (!$tecdoc_request_enabled && !$recursive_search_enabled) {
			LinemediaAutoDebug::add('Simple analogs result ('.count($analogs).')', print_r($analogs, true), LM_AUTO_DEBUG_WARNING);
		}

        /*
         * ��� ������� �������� ������ ������� � ��������� �������, ���� ��� �� ��������.
         */
        $stop_search = (!$recursive_search_enabled);
        
        /*
         * �� ���� �������� ������������ ���� � �� �� ������
         */
        $used_ids = array();
        foreach ($analogs as $a) {
            $used_ids [$a['id']] = $a['id'];
        }
        
        
        /*
        * ���������� �������� � ����� �������, ��������� ����� �� �������
        * ��� ����� � articles_to_search
        */
        foreach ($articles_to_search as $part) {
            unset($part['id']);
            $key = $part['article'] . '|' . $part['brand_title']; // part key
            $analogs[$key] = $part;
        }
        
        
        /*
         * ������ ������
         */
        $analog_obj = new LinemediaAutoAnalogsSimpleAnalog();
        
        $loop = 0;
        while (!$stop_search) {
        	$loop++;
        	
            /*
             * ������� �� ����� �������
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
                 * ������
                 */
                try {
                    $result = $analog_obj->find($filter);
                } catch (Exception $e) {
                    throw $e;
                }
                
                while ($item = $result->Fetch()) {
                    
                    /*
                     * ����� ��� ��������.
                     */
                    if (isset($used_ids[$item['id']])) {
                    	continue;
                    }
                    $used_ids [$item['id']] = $item['id'];
                    
                    
                    /*
                     * ���� ������� �� ���� ������, � ������.
                     */
                    $suffix = ($item['article_original'] == $filter['article']) ? ('analog') : ('original');
                    
                    $article = $item['article_' . $suffix];
                    $brand_title = $item['brand_title_' . $suffix];
                    
                    // ��������� ������ ��������.
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
                    * ���� ��� �������� ��� ����, ��������� � ���������
                    */
                    $key = $part['article'] . '|' . $part['brand_title']; // part key
                    if(isset($analogs [$key]))
                    	continue;
                    
                    
                    /*
                     * � ������, ���� ������� ����������� � ������ ������������ ����� ��� ����� �������������� ���������� ���������,
                     * ���� �������� ��� � ������.
                     */
                    if (
                         in_array($group, $search_analog_groups)
                         && $tecdoc_request_enabled
                    ) {
                        $additional_tecdoc_request_articles []= $part;
                    }
                    
                    /*
                     * ��� �������� >=2 ������ ������ ������ �������� � ������ ������������ ������
                     * ������ ��� ��� ������ �� ��� �������� ��������, � ������ ��� ����� ����������
                     */
                    if ($group == LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED) {
	                   $part['analog_type'] =  $analog['analog_type'];
                    }
                    
                    
                    $analogs [$key]= $part;
                    $new_analogs_to_check++;
                }
               
                /*
                 * ���� ����� ���������.
                 */
                $analogs[$k]['checked'] = true;
            }
            
            // ������ ��� ������ ���������
            if($new_analogs_to_check > $MAX_NEW_ANALOGS_TO_CONTINUE_SEARCH)
	            $stop_search = true;
            
            if ($new_analogs_to_check == 0 || $loop == $recursive_search_counts) {
                $stop_search = true;
            }
        }
        
        
        /*
         * � ������, ���� ������� ����������� � ������ ������������ ����� ��� ����� �������������� ���������� ���������
         * ���� �������� ��� � ������
         */
        if (
            $tecdoc_request_enabled
            && count($additional_tecdoc_request_articles) > 0
        ) {
            
            
            $api_request = array();
            
            /*
	         * �������� ����������.
	         */
	        $unique = array();
	        foreach ($additional_tecdoc_request_articles as $part) {
		        $unique[$part['article'] . '|' . $part['brand_title']] = $part;
	        }
	        $additional_tecdoc_request_articles = array_values($unique);
            
            
            /*
             * ��������������� �������.
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
             * ������.
             */
            try {
                /*
                 * ���������� ��������� ������ (������� �����), ����� ����������, ����� ������� �� ����� �������� � ������
                 *
                 * ��������: 
                 *  ������ ������ ������ � TecDoc
                 *  ������ ������������ ������ � TecDoc
                 *  ������ ������ � �� Linemedia
                 *  ������ ������ � ��������� ��    ----   ������������ � ������ ������� ��������
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
                // ���������� ������ �������
            }
            
            /*
             * ������ ������
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
         * ��� ���� ��������� ������������ ��������� ���������� �������� ��������� ������.
         */
        if ($tecdoc_request_enabled) {
            
            $stop_search = (!$recursive_search_enabled);
            
            
            /*
            * ���� �������� �����, ������ �� ��� ������
            */
            if(count($analogs) > $MAX_TOTAL_ANALOGS_TO_CONTINUE_SEARCH)
            	$stop_search = true;
            
	        $loop = 0;
	        while (!$stop_search) {
	            $loop++;
	            /*
	             * ������� �� ����� �������
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
	                 * ������
	                 */
	                try {
	                    $result = $analog_obj->find($filter);
	                } catch (Exception $e) {
	                    throw $e;
	                }
	                
	                while ($item = $result->Fetch()) {
	                    
	                    /*
	                     * ����� ��� ��������.
	                     */
	                    if (isset($used_ids[$item['id']])) {
	                    	continue;
                        }
	                    $used_ids [$item['id']] = $item['id'];
	                    
	                    /*
	                     * ���� ������� �� ���� ������, � ������.
	                     */
	                    $suffix = ($item['article_original'] == $filter['article']) ? ('analog') : ('original');
	                    
	                    $article = $item['article_' . $suffix];
	                    $brand_title = $item['brand_title_' . $suffix];
	                    
	                    // ��������� ������ ��������.
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
	                     * � ������, ���� ������� ����������� � ������ ������������ ����� ��� ����� �������������� ���������� ���������,
	                     * ���� �������� ��� � ������.
	                     */
	                    if (
	                        in_array($group, $search_analog_groups)
	                        && $tecdoc_request_enabled
	                    ) {
	                        $additional_tecdoc_request_articles []= $part;
	                    }
	                    
	                    $additional_tecdoc_request_articles []= $part;
	                    
	                    /*
	                     * ��� �������� >=2 ������ ������ ������ �������� � ������ ������������ ������
	                     * ������ ��� ��� ������ �� ��� �������� ��������, � ������ ��� ����� ����������
	                     */
	                    if ($group == LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED) {
		                   $part['analog_type'] =  $analog['analog_type'];
	                    }
	                    
	                    $analogs[$key]= $part;
	                    
	                    $new_analogs_to_check++;
	                }
	               
	                /*
	                 * ���� ����� ���������.
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
         * ������ ��������� �������� ��� �������� �������� ��� ������ �� ��������� ��
         */
        $analogs_clear = array();
        foreach ($analogs as $analog) {
            // ���� �� ���������� id, ������ ��� �� �������.
            unset($analog['id']);
            
            $analogs_clear[$analog['article'] . '|' . $analog['brand_title'] . '|' . $analog['analog_type']] = $analog;
        }
        $analogs = array_values($analogs_clear);
        
        
        
        /*
         * � �� ��������� �� ��� ��������?
         * ���� ����� brand_title - ��������� ���� �� �����
         * ���������� ��� ������ ������� � ������������ � ����������� ���������, ��  ������� ��������, �� ��� ����������� ��,
         * ��� �� ��������� �� ��������
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
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Simple analogs returned catalogs', print_r($catalogs, true), LM_AUTO_DEBUG_WARNING);
            
            $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
        }
        
        
        /*
        * �������� ������ �� trace
        */
        foreach($analogs AS $k => $analog) {
	        if(isset($analog['trace'])) {
		        $analogs[$k]['trace'] = join(' &rarr; ', $analog['trace']) . ' &rarr; ['.$analog['brand_title'].' '.$analog['article'].']';
	        }
	        
        }
        
        
        
        
        /*
         * ���� ������� ���� ����������� �����, ���� ����� �� TecDoc,
         * �� � ������� ������� � ������� TecDoc � ��� ����� ��� ��������� ������ ������ ������� ��������,
         * ����� ���� ������� ������ �������, ��������� � ���. ����
         */
		if ($tecdoc_request_enabled || $recursive_search_enabled) {
        	LinemediaAutoDebug::add('Simple analogs result ('.count($analogs).')', print_r($analogs, true), LM_AUTO_DEBUG_WARNING);
		}

        
        /*
         * ��������� ������, ������� ��� ����, � ������.
         */
        $articles_to_search = array_merge_recursive($articles_to_search, $analogs);
    }
    
    
    
}

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
class LinemediaAutoUrlHelper
{
    
    static $cache;
    /**
     * Получение URL к детали или поиску
     */
    public static function getPartUrl($data = array(), $searchArticleUrl = '/auto/search/#ARTICLE#/', $type = 'LinemediaAutoSearchSimple')
    {
	//$searchArticleUrl
        $url = '';
		        
        /*
         * Событие на формирование URL
         */
        /*$events = GetModuleEvents("linemedia.auto", "OnBeforePartUrlCreate");
		while ($arEvent = $events->Fetch()) {*/
		/*
         * Событие на формирование URL
         */
        if(!isset(self::$cache['events']['OnBeforePartUrlCreate'])) {
            $ar_events = array();
            $events = GetModuleEvents("linemedia.auto", "OnBeforePartUrlCreate");
            while ($arEvent = $events->Fetch()) {
            	$ar_events[] = $arEvent;
            }
            self::$cache['events']['OnBeforePartUrlCreate'] = $ar_events;
        }
        
        $events = self::$cache['events']['OnBeforePartUrlCreate'];
            
        foreach ($events AS $arEvent) {
		    ExecuteModuleEventEx($arEvent, array(&$url, &$data));
	    }
			
        /*
         * Возможные переменные
         */
        $article        = (string) $data['article'];
        $brand_title    = (string) $data['brand_title'];
        $part_id        = (int)    $data['part_id'];
        $supplier_id    = (string) $data['supplier_id'];
        $extra          = (array)  $data['extra'];
        
        // Ilya Pyatin 20.05.14 #8777    // можно было urlencode
        $article		= LinemediaAutoPartsHelper::clearArticle($article);
        
	    
        /*
         * Ссылка на поиск
         */
        if ($part_id) {
            /*
             * Ссылка на конкретную запчасть
             */
            $tpl  = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_DETAIL_PAGE');
            $url .= str_replace(array('#PART_ID#', '#SUPPLIER_ID#'), array($part_id, $supplier_id), $tpl);
			
        } elseif ($article) {
            /*
             * Ссылка на поиск
             */
            //$tpl = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_SEARCH_PAGE');
			
			$tpl = $searchArticleUrl;
            $url .= str_replace('#ARTICLE#', $article, $tpl);
            
            /*
             * Уточнения URL
             */
            $url_params = array();
            if ($brand_title) {
                $url_params['brand_title'] = $brand_title;
            }
            if ($supplier_id) {
                $url_params['supplier_id'] = $supplier_id;
            }
            if (count($extra) > 0) {
                $url_params['extra'] = $extra;
            }
            
            if ($type == LinemediaAutoSearch::SEARCH_PARTIAL) {
                $url_params['partial'] = 'Y';
            }
            
            $url_params = count($url_params) > 0 ? '?' . http_build_query($url_params) : '';
            $url .= $url_params;
			
            
        } else {
            //$tpl = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_SEARCH_PAGE');
			
			$tpl = $searchArticleUrl;
            $url .= str_replace('#ARTICLE#', '', $tpl);
            $url = str_replace('//', '/', $url);
        }
        
        
        /*
         * Событие на формирование URL
         */
        if(!isset(self::$cache['events']['OnAfterPartUrlCreate'])) {
            $ar_events = array();
            $events = GetModuleEvents("linemedia.auto", "OnAfterPartUrlCreate");
            while ($arEvent = $events->Fetch()) {
            	$ar_events[] = $arEvent;
            }
            self::$cache['events']['OnAfterPartUrlCreate'] = $ar_events;
        }
        $events = self::$cache['events']['OnAfterPartUrlCreate'];
        foreach ($events AS $arEvent) {
		    ExecuteModuleEventEx($arEvent, array(&$url, $data));
	    }
	    
	    return $url;
        
    }


	/**
	 * Получение URL для покупки детали
	 */
	public static function getPartBuyUrl($data = array(), $buyArticleUrl = '/auto/search/?part_id=#PART_ID#', $searchArticleUrl = '/auto/search/#ARTICLE#/')
	{
		$url = '';

		/*
		 * Событие на формирование URL для покупки
		 */
		if(!isset(self::$cache['events']['OnBeforePartBuyUrlCreate'])) {
			$ar_events = array();
			$events = GetModuleEvents("linemedia.auto", "OnBeforePartBuyUrlCreate");
			while ($arEvent = $events->Fetch()) {
				$ar_events[] = $arEvent;
			}
			self::$cache['events']['OnBeforePartBuyUrlCreate'] = $ar_events;
		}

		$events = self::$cache['events']['OnBeforePartBuyUrlCreate'];

		foreach ($events AS $arEvent) {
			ExecuteModuleEventEx($arEvent, array(&$url, &$data));
		}

		/*
         * Возможные переменные
         */
		$article        = (string) $data['article'];
		$brand_title    = (string) $data['brand_title'];
		$part_id        = (int)    $data['part_id'];
		$supplier_id    = (string) $data['supplier_id'];
		$extra          = (array)  $data['extra'];

		// Ilya Pyatin 20.05.14 #8777    // можно было urlencode
		$article		= LinemediaAutoPartsHelper::clearArticle($article);

		/*
		 * Ссылка для покупки
		 */
		if ($part_id) {
			$tpl  = $buyArticleUrl;
			$url .= str_replace(array('#PART_ID#', '#SUPPLIER_ID#'), array($part_id, $supplier_id), $tpl);
			$url  = str_replace('//', '/', $url);
		} elseif ($article) {
			$tpl = $searchArticleUrl;
			$url .= str_replace('#ARTICLE#', $article, $tpl);

			/*
			 * Уточнения URL
			 */
			$url_params = array();
			if ($brand_title) {
				$url_params['brand_title'] = $brand_title;
			}
			if ($supplier_id) {
				$url_params['supplier_id'] = $supplier_id;
			}
			if (count($extra) > 0) {
				$url_params['extra'] = $extra;
			}

			$url_params = count($url_params) > 0 ? '?' . http_build_query($url_params) : '';
			$url .= $url_params;
		} else {
			$tpl = $searchArticleUrl;
			$url .= str_replace('#ARTICLE#', '', $tpl);
			$url = str_replace('//', '/', $url);
		}
		/*
		 * Добавляем переменную сессии для защиты от ботов
		 */
		$url .= '&sessid=' . bitrix_sessid();

		/*
		 * Событие на формирование URL для покупки
		 */
		if(!isset(self::$cache['events']['OnAfterPartBuyUrlCreate'])) {
			$ar_events = array();
			$events = GetModuleEvents("linemedia.auto", "OnAfterPartBuyUrlCreate");
			while ($arEvent = $events->Fetch()) {
				$ar_events[] = $arEvent;
			}
			self::$cache['events']['OnAfterPartBuyUrlCreate'] = $ar_events;
		}

		$events = self::$cache['events']['OnAfterPartBuyUrlCreate'];

		foreach ($events AS $arEvent) {
			ExecuteModuleEventEx($arEvent, array(&$url, &$data));
		}

		return $url;
	}
}

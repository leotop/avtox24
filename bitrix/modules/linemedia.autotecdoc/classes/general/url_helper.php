<?php


/**
 * Linemedia Autoportal
 * Autotecdoc module
 * LinemediaAutoTecDocUrlHelper
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);



/**
 * class responsable for working with orders
 */
class LinemediaAutoTecDocUrlHelper
{
    
    /**
     * getting URL to either details or search
     * @param array $data
     * @param string $tpl
     * @return string
     */
    public static function getPartUrl(array $data = array(), $tpl)
    {
        $url = '';
        if(empty($tpl))
            $tpl = COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_TECDOC_PART_SEARCH_PAGE');

        /*
         * Событие на формирование URL
         */
        $events = GetModuleEvents("linemedia.autotecdoc", "OnBeforePartUrlCreate");
		while ($arEvent = $events->Fetch()) {
		    ExecuteModuleEventEx($arEvent, array(&$url, &$data, &$tpl));
	    }

        /*
         * Возможные переменные
         */
        $article        = (string) $data['article'];
        //$brand_id       = (int)    $data['brand_id'];
        //$brand_title    = (string) $data['brand_title'];
        //$part_id        = (int)    $data['part_id'];
        //$supplier_id    = (int)    $data['supplier_id'];
        //$extra          = (array)  $data['extra'];
        
	    
        /*
         * Ссылка на поиск, а не конкретную запчасть
         */
        if ($article) {
        	
        	$article = self::clearArticle($article);
        	
        	
            /*
             * Ссылка на поиск
             */
            $url .= str_replace('#ARTICLE#', $article, $tpl);
            
            /*
             * Уточнения URL
             */
            $url_params = array();
            if ($brand_id) {
                $url_params['brand_id'] = $brand_id;
            }
            if ($brand_title) {
                $url_params['brand_title'] = $brand_title;
            }
            if (count($extra) > 0) {
                $url_params['extra'] = $extra;
            }
            
            $url_params = count($url_params) > 0 ? '?' . http_build_query($url_params) : '';
            $url .= $url_params;
            
        } else {
            $url .= str_replace('#ARTICLE#', '', $tpl);
            $url = str_replace('//', '/', $url);
        }
        
        
        /*
         * Событие на формирование URL
         */
        $events = GetModuleEvents("linemedia.autotecdoc", "OnAfterPartUrlCreate");
		while ($arEvent = $events->Fetch()) {
		    ExecuteModuleEventEx($arEvent, array(&$url, $data));
	    }
	    
	    return $url;
        
    }
    
    
    
    /**
	 * Очистка артикула от лишних символов.
     * 
     * @param string $art
     * @param bool $multiple
     * @return string
	 */
    public static function clearArticle($art)
    {
        $result = '';
        
        if (defined('BX_UTF') && BX_UTF == true) {
            $result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', '"', '\'', PHP_EOL, chr(10), chr(13), chr(160)), '', $art), 'UTF-8');
        } else {
            $result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', '"', '\'', PHP_EOL, chr(10), chr(13), chr(160)), '', $art));
        }
        return $result;
	}
}

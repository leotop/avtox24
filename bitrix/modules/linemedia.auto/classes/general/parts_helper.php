<?php


/**
 * Linemedia Autoportal
 * Main module
 * Parts helper
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/*
 * ����������� ������� � ������ ���� �� ������! �� ���������� � ��� ��������!
 * ���������� IncludeModuleLangFile � GetMessage - ��� �������������� � ���������� �������
 */
IncludeModuleLangFile(__FILE__);



/**
 * �����, ���������� �� ������ � ��������.
 */
class LinemediaAutoPartsHelper
{
    /**
 	* ����������� ���
 	*/
 	static $cache;

    public static $EXTRA_KEYS = array(
        'catalog_code',
        'catalog_group_id',
        'gid',
        'modification_id',
        'wf_b',
    );
 	
    /**
	 * ������� �������� �� ������ ��������.
     * 
     * @param string $art
     * @return string
	 */
    public static function clearArticle($art)
    {
    	if(isset(self::$cache['clearArticle'][$art])) {
    		return self::$cache['clearArticle'][$art];
    	}
    	
        $result = '';
        
        if (defined('BX_UTF') && BX_UTF == true) {
            $result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', ',', '"', '\'', PHP_EOL, "\r", "\n", "\t"), '', $art), 'UTF-8');
            // ������ �������� ����������� ������� ����� chr
            //$result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', '"', '\'', PHP_EOL, chr(10), chr(13), chr(160)), '', $art), 'UTF-8');
        } else {
            $result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', ',', '"', '\'', PHP_EOL, chr(10), chr(13), chr(160)), '', $art));
        }
        
        self::$cache['clearArticle'][$art] = $result;
        return $result;
	}

	/**
	 * ������� ������ �� ������ ��������.
	 * ������ �� ��������������� � �� �����������, �.�. ������ ru_RU.UTF-8 ��������������� � api_driver.php
	 *
	 * @param string $art
	 * @return string
	 */
	public static function clearBrand($brand)
	{
		$brand = strtoupper($brand);
		
		/*
		 * ���� ��� ������ ��� UTF-8 ��������
		 */
		if (defined('BX_UTF') && BX_UTF == true) {

			/*
			 * ���� �������� �������, �� ������ ��� �� ��������
			 */
			if (strpos(htmlentities($brand, ENT_QUOTES, 'UTF-8'), '&') !== false) {

				$brand = iconv('UTF-8', 'ASCII//TRANSLIT', $brand);

			}
		}

		return $brand;
	}
    
    
    /** 
     * @param array $catalogs
     * @param string $code
     * sorting calalogs by using given condition as array keys and order (ASC, DESC)
     * 
     * sorting
     */
    public static function sortCatalogs($catalogs, $sort, $order)
    {
        $sort   = (string) $sort;
        $order  = (string) $order;
        if (empty($sort)) {
            return $catalogs;
        }
        $order = (strtolower($order) == 'asc');
        
        foreach ($catalogs as &$parts) {
            $parts = self::sortArrayByUsingKey($parts, $sort, $order);
        }
        return $catalogs;
    }
    
    
    /**
     * @param array $part1
     * @param array $part2
     * @param string $code
     * sorting array by key. by default use order ACS
     * 
     * sort
     */
    protected static function sortArrayByUsingKey($array, $key, $asc = true)
    {
        $result = array();
        $values = array();
        foreach ((array) $array as $id => $value) {
            $values[$id] = $value[$key];
        }
        
        if ($asc) {
            asort($values);
        } else {
            arsort($values);
        }
        
        foreach ($values as $id => $value) {
            $result[$id] = $array[$id];
        }
        return $result;
     }

    public static function clearExtra($extra, $allowed_keys = array()) {

        if(!is_array($extra) || count($extra) < 1) {
            return $extra;
        }

        $allowed_keys = (array) $allowed_keys;
        $allowed_keys = array_merge($allowed_keys, self::$EXTRA_KEYS);

        foreach($extra as $key => &$value) {

            if(!in_array($key, $allowed_keys)) {
                unset($extra[$key]);
            } else if(is_array($value)) {
                $value = array_unique($value);
            }
        }

        return $extra;
    }
}



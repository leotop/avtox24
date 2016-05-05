<?php
/**
 * Linemedia Autoportal
 * Main module
 * Wordforms
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
/*
 * Словоформы из API
 */
class LinemediaAutoBrandsJoin
{
    protected static $instance = null;
    
    protected $joins = array();
    
    
    /**
     * Конструктор.
     * Получение словоформ из API.
     */
    public function __construct()
    {
        CModule::IncludeModule('iblock');
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_BRANDS_JOIN');
        
        
        $brands_res = CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_ID' => $iblock_id,
            ),
            false,
            false,
            array('ID', 'NAME', 'ACTIVE', 'PROPERTY_brands')
        );
        
        while($join = $brands_res->Fetch())
        {
            foreach($join['PROPERTY_BRANDS_VALUE'] AS $variant)
            {
                $this->joins[strtolower($variant)] = $join['NAME'];
            }
        }
    }
    
    
    /**
     * Экземпляр класса.
     */
    public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    /**
     * Проверка в словоформах.
     * 
     * @param string $key
     */
    public function getBrandTitle($title)
    {
        $key = strtolower((string) $title);
        if (isset($this->joins[$key])) {
            return $this->joins[$key];
        }
        
        return $title;
    }
}


<?php

/**
 * Linemedia Autoportal
 * Wordforms module
 * Parts class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * WordForm
 */
class LinemediaAutoWordForm
{
    
    /**
     * table title
     * @var string CACHE_KEY
     */
    const CACHE_KEY     = 'b_lm_wordforms';
    
    /**
     * cache folder
     * @var string CACHE_DIR
     */
    const CACHE_DIR     = '/lm_auto/wordform';
    
    /**
     * time of lifespan
     * @var int CACHE_TIME
     */
    const CACHE_TIME    = 86400;
    
    /**
     * @var array $groups 
     */
    protected $groups;
    
    /**
     * @var array $titles
     */
    protected $titles;
    
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        $obCache = new CPHPCache();
        if ($obCache->InitCache(self::CACHE_TIME, self::CACHE_KEY, self::CACHE_DIR)) {
            $vars = $obCache->GetVars();
            $this->groups = $vars["groups"];
            $this->titles = $vars["titles"];
        } else {
            if ($obCache->StartDataCache()) {
                global $DB;
                try {
                    $res = $DB->Query('SELECT * FROM `b_lm_wordforms` WHERE 1=1;');
                } catch (Exception $e) {
                    LinemediaAutoDebug::add('Error loading wordforms for ' . $this->brand_title . ' ' . $e->GetMessage());
                }
                
                $groups = array();
                $titles = array();
                while ($form = $res->Fetch()) {
                    $groups[$form['group']][] = $form['brand_title'];
                    $titles[$form['brand_title']] = $form['group'];
                }
                
                $obCache->EndDataCache(array(
                    "groups"    => $groups,
                    "titles"    => $titles
                ));
                $this->groups = $groups;
                $this->titles = $titles;
            }
        }
        
        /*
         * События для других модулей.
         */
        $events = GetModuleEvents("linemedia.auto", "OnWordformObjectCreate");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$this->groups, &$this->titles));
        }
    }
    
    
    /**
     * Получение группы по бренду.
     */
    public function getBrandWordforms($brand_title)
    {
        $brand_title = strtoupper($brand_title);
        
        $group = $this->titles[$brand_title];
        
        if (isset($this->groups[$group])) {
        	$wordforms = (array) $this->groups[$group];
        } else {
        	/*
        	 * В качестве бренда передано название группы.
        	 */
	        $wordforms = (array) $this->groups[$brand_title];
        }
        
        if(!in_array($brand_title, $wordforms))
        	$wordforms[] = $brand_title;
        
        return $wordforms;
    }
    
    
    /**
     * Получение групп словоформ
     */
    public function getGroupWordforms($group)
    {
        $group = strtoupper($group);
        $wordforms = $this->groups[$group];
        return $wordforms;
    }
    
    
    /**
     * Группы брендов.
     */
    public function getBrandGroup($brand_title)
    {
        $brand_title = strtoupper($brand_title);
        return $this->titles[$brand_title];
    }
    
    
    /**
     * Сборс кеша.
     */
    public function clearCache()
    {
        $obCache = new CPHPCache();
        
        $obCache->Clean(self::CACHE_KEY, '/');
        $obCache->Clean(self::CACHE_KEY, self::CACHE_DIR);
        
        BXClearCache(true, self::CACHE_DIR);
    }
    
    
    /**
     * Очистка группы.
     */
    public function clearGroup($group)
    {
        global $DB;
        
        $group = "'" . $DB->ForSQL($group) . "'";
        try {
            $DB->Query('DELETE FROM `b_lm_wordforms` WHERE `group` = ' . $group . ';');
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error clearing wordforms. ' . $e->GetMessage());
        }
        
        $this->clearCache();
    }

    
    /**
     * Проверка на существование словоформы.
     */
    public function isExists($group, $wordform)
    {
        global $DB;
        
        $group      = "'" . $DB->ForSQL($group) . "'";
        $wordform   = "'" . $DB->ForSQL($wordform) . "'";
        
        $DB->Query("SELECT 1 FROM `b_lm_wordforms` WHERE `group` = ".$group." AND `brand_title` = UPPER(".$wordform.");");
        
        return ($DB->SelectedRowsCount() > 0);
    }
    
    
    /**
     * Установка списка брендов для группы.
     */
    public function setGroupWordForms($group, $wordforms, $old_group = false)
    {
        global $DB;
        
        $groupdb = "'" . $DB->ForSQL($group) . "'";
        
        $wordforms = array_map('trim', $wordforms);
        $wordforms = array_map('strtoupper', $wordforms);
        $wordforms = array_filter($wordforms);
        $wordforms = array_map(array($DB, 'ForSQL'), $wordforms);
        
        $unique = array_unique($wordforms);
        
        if (count($unique) < count($wordforms)) {
            throw new Exception(GetMessage('LM_AUTO_WORDFORM_ERROR_NOT_UNIQUE'));
            return;
        }
        
        try {
            $DB->StartTransaction();
            
            $this->clearGroup($old_group ? $old_group : $group);

            foreach ($wordforms as $wordform) {
                $result = $DB->Query('INSERT INTO `b_lm_wordforms` (`group`, `brand_title`) VALUES (' . $groupdb . ', \'' . $wordform . '\')', true);
                if (!$result) {
                    throw new Exception(GetMessage('LM_AUTO_WORDFORM_ERROR_EXIST'));
                }
            }
            $DB->Commit();
            
            $this->clearCache();
            
        } catch (Exception $e) {
            $DB->Rollback();
            throw new Exception($e->GetMessage());
            LinemediaAutoDebug::add('Error updating wordforms. ' . $e->GetMessage());
        }
        
        $this->clearCache();
    }

    public function clean($wordforms) {

    }

    public function makeFilter($brand_titles) {

        $brands = array();

        if(count((array) $brand_titles) < 1) {
            return array();
        }

        foreach($brand_titles as $brand_title) {
            $brands = array_merge($brands, $this->getBrandWordforms($brand_title));
            $brands = array_merge($brands, (array) $this->getBrandGroup($brand_title));
        }

        $brands = array_map('strtoupper', $brands);
        $brands = array_unique($brands);

        return $brands;
    }
}

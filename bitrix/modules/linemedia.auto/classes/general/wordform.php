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
     * В дальнейшем планируется использовать только этот массив
     * @var array
     */
    protected $normalized = array();
    
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
            $this->normalized = $vars["normalized"];
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

                    // создаем новую структуру нормализованных брендов
                    $group_normal = self::normalize($form['group']);
                    $brand_normal = self::normalize($form['brand_title']);

                    $this->normalized['brands'][$brand_normal] = $group_normal;

                    if(!array_key_exists($group_normal, $this->normalized['groups'])) {
                        $this->normalized['groups'][$group_normal] = array(
                            'title' => $form['group'],
                            'forms' => array($brand_normal => $form['brand_title']),
                        );
                    } else {
                        $this->normalized['groups'][$group_normal]['forms'][$brand_normal] = $form['brand_title'];
                    }
                }
                
                $obCache->EndDataCache(array(
                    "groups"        => $groups,
                    "titles"        => $titles,
                    "normalized"    => $this->normalized,
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
        $wordforms = null;

        $brand_normal = self::normalize($brand_title);

        if(array_key_exists($brand_normal, $this->normalized['groups'])) {

            $wordforms = array_values($this->normalized['groups'][$brand_normal]['forms']);
            $wordforms[] = $this->normalized['groups'][$brand_normal]['title'];

        } else if(array_key_exists($brand_normal, $this->normalized['brands'])) {

            $group_normal = $this->normalized['brands'][$brand_normal];

            $wordforms = array_values($this->normalized['groups'][$group_normal]['forms']);
            if(!in_array($this->normalized['groups'][$group_normal]['title'], $wordforms)) {
                $wordforms[] = $this->normalized['groups'][$group_normal]['title'];
            }
        }

        return array_unique($wordforms);
    }
    
    
    /**
     * Получение групп словоформ
     */
    public function getGroupWordforms($group)
    {
        $group_normal = self::normalize($group);
        if(array_key_exists($group_normal, $this->normalized['groups'])) {
            return array_values($this->normalized['groups'][$group_normal]['forms']);
        }

        return null;
    }
    
    
    /**
     * Группы брендов.
     */
    public function getBrandGroup($brand_title)
    {
        $brand_normal = self::normalize($brand_title);

        if(array_key_exists($brand_normal, $this->normalized['groups'])) {
            return $this->normalized['groups'][$brand_normal]['title'];
        } else if(array_key_exists($brand_normal, $this->normalized['brands'])) {
            $group_normal = $this->normalized['brands'][$brand_normal];
            return $this->normalized['groups'][$group_normal]['title'];
        }

        return null;
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

    public static function normalize($brand_title) {

        $brand_title = mb_strtoupper($brand_title);
        // Remove all non-alphanumeric characters
        $brand_title = preg_replace('/[\s\W]+/u', '', $brand_title);
        $brand_title = str_replace(array('?', '?', '?', '?', '?'), array('A', 'E', 'O', 'O', 'U'), $brand_title);

        return $brand_title;
    }
}

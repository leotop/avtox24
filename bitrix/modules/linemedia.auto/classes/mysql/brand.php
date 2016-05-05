<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);
 
/*
 * 
 */
class LinemediaAutoBrand extends LinemediaAutoBrandAll
{
    
    public function __construct($brand_id = false, $brand_title = '')
    {
        parent::__construct($brand_id, $brand_title);
    }
    
    
    /**
     * ���������� ������.
     */
    public function add($fields)
    {
        /*
         * ����������� ������
         */
        CModule::IncludeModule('linemedia.auto');
        
        /*
         * ����������� � ��
         */
        $database = new LinemediaAutoDatabase();
        
        /*
         * ��������� ���������.
         */
        $wordforms = LinemediaAutoWordForms::getInstance();
        
        /*
         * ��������� ����������.
         */
        $title = (string) $fields['title'];
        if ($this->useWordForms()) {
            $title = $wordforms->get($title);
        }
        
        
        /*
         * �������� ������� � �� � ���� ������ ������ ��� - �������.
         */
        $sql_title = $database->ForSql(LinemediaAutoBrand::clear(strtolower($title)));
        $res = $database->Query('SELECT id FROM b_lm_brands WHERE LOWER(title) = \'' . $sql_title . '\'');
        if ($brand = $res->Fetch()) {
            $key = (int) $brand['id'];
        } else {
            $sql_brand_title = $database->ForSql(LinemediaAutoBrand::clear($brand_title));
            $database->Query('INSERT INTO b_lm_brands (title) VALUES (\'' . $sql_title . '\')');
            $key = $database->LastID();
            
            /*
             * ����� ���������� ����������.
             */
            LinemediaAutoDebug::add("Brand $title added with ID " . $key);
        }
        
        $this->brand_id = (int) $key;
        
        $this->loaded = false;
        $this->load();
    }
    
    
    /**
     * �������� ������ �� ���� �� ��������� ID, �������� ������ ��� ID TecDoc
     */
    protected function load()
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;
        
        try {
            $database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }
        
        
        /*
         * ��������� ���������.
         */
        if ($this->brand_title) {
            $wordforms = LinemediaAutoWordForms::getInstance();
            
            /*
             * ��������� ����������.
             */
            if ($this->useWordForms()) {
                $this->brand_title = $wordforms->get($this->brand_title);
            }
        }
        
        /*
         * C������� ������ ��� ������ ������
         */
        $where = array();
        if ($this->brand_id > 0) {
            $where['id'] = $this->brand_id;
        } elseif ($this->brand_title) {
            $where['title'] = $this->brand_title;
        }
        $where = array_merge($where, $this->extra);
        
        
        /*
         * ������ ������
         */
        if (count($where) == 0) {
            LinemediaAutoDebug::add('Error loading brand! No "where" conditions', false, LM_AUTO_DEBUG_CRITICAL);
            return;
        }
        
        /*
         * ���������� ������
         */
        $where_cond = array();
        foreach ($where as $column => $val) {
            $where_cond[] = "`" . $database->ForSQL($column) . "` = '" . $database->ForSQL($val) . "'";
        }
        
        
        /*
         * ��������������� ������ � ��
         */
        try {
            $res = $database->Query('SELECT * FROM b_lm_brands WHERE ' . join(' AND ', $where_cond));
        } catch (Exception $e) {
            throw $e;
        }

        /*
         * ������������� ������?
         */
        if ($brand = $res->Fetch()) {
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Brand object loaded', print_r($brand, 1));
            
            
            /*
             * ������ �������
             */
            $events = GetModuleEvents("linemedia.auto", "OnAfterBrandLoaded");
		    while ($arEvent = $events->Fetch()) {
			    ExecuteModuleEventEx($arEvent, array(
			        &$brand
			    ));
		    }
		    $this->data = $brand;
            
        } else {
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Error loading brand object, 404', print_r($where, 1));
        }
        
    }
}

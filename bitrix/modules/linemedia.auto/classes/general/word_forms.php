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
class LinemediaAutoWordForms
{
    protected static $instance = null;
    
    protected $word_forms = array();
    
    
    /**
     * Конструктор.
     * Получение словоформ из API.
     */
    protected function __construct()
    {
        /*
         * Подключение модуля
         */
        CModule::IncludeModule('linemedia.auto');
        
        /*
         * Коды доступа
         */
        $api_id     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_ID');
        $api_key    = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_KEY');
        $api_url    = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_URL');
        $api_format = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_FORMAT');
        
        /*
         * Объект доступа в API
         */
        $api = new LinemediaAutoApiDriver($api_id, $api_key, $api_url, $api_format);
        
        /*
         * Список словоформ
         */
        try {
            $response = $api->query('getBrandsWordforms');
        } catch (Exception $e) {
            throw $e;
        }
        $this->word_forms = (array) $response['data'];
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
    public function get($key)
    {
        $key = strtolower((string) $key);
        if (isset($this->word_forms[$key])) {
            $key = $this->word_forms[$key];
        }
        return $key;
    }
}


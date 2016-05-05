<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Browser
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

/**
 * Эмулятор браузера
 * Class LinemediaAutoDownloaderBrowser
 */
class LinemediaAutoDownloaderBrowser
{
    /**
     * Соединение curl
     * @var resource
     */
    protected $curl;
    /**
     * Файл куков
     * @var string
     */
    protected $cookiefile;
    /**
     * Урл
     * @var
     */
    protected $base_url;
    
    /**
     * В конструкторе создаётся объект cURL и устанавливаются базовые настройки.
     */
    public function __construct()
    {
        $this->cookiefile = tempnam($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/', "COO");
        
        $this->curl = curl_init();
        $this->resetSettings();
    }

    /**
     * Выполнение GET запроса.
     * @param $url
     * @return mixed
     * @throws Exception
     */
    public function get($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->base_url . $url);
        try {
            return $this->request();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Выполнение POST запроса
     * @param $url
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function post($url, $data = array())
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->base_url . $url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data));
        try {
            return $this->request();
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    /**
     * Сброс настроек.
     */
    public function resetSettings()
    {
        $options = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_CONNECTTIMEOUT  => 3,
            CURLOPT_TIMEOUT         => 7,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 4,
            CURLOPT_USERAGENT       => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11 Linemedia Autoexpert Parser',
            CURLOPT_COOKIEFILE      => $this->cookiefile,
            CURLOPT_COOKIEJAR       => $this->cookiefile,
        );

        curl_setopt_array($this->curl, $options);
    }

    /**
     * Установка referer.
     * @param string $ref
     */
    public function setReferer($ref)
    {
        curl_setopt($this->curl, CURLOPT_REFERER, $ref);
    }

    /**
     * Установка useragent.
     * @param string $agent
     */
    public function setAgent($agent)
    {
        curl_setopt($this->curl, CURLOPT_USERAGENT, $agent);
    }


    /**
     * Установка базового пути к запросам (обычно домена с http://),
     * который подставляется перед каждым вызовом.
     * @param $url
     */
    public function setBaseUrl($url)
    {
        $this->base_url = $url;
    }

    /**
     * Непосредственное выполнение запроса.
     * @return mixed
     * @throws Exception
     */
    protected function request()
    {
        try {
            $response = curl_exec($this->curl);
        } catch (Exception $e) {
            throw $e;
        }
        
        if (!$response) {
            throw new Exception(curl_error($this->curl) . ' (#'.curl_errno($this->curl).')');
        }
        
        $last_query = curl_getinfo($this->curl);
        LinemediaAutoDebug::add('Remote request', $last_query['url'] . ' ['.$last_query['http_code'].']', LM_AUTO_DEBUG_WARNING);
        
        return $response;
    }

    /**
     * Закрытие соединения в деструкторе.
     */
    public function __destruct()
    {
        curl_close($this->curl);
        unlink($this->cookiefile);
    }
}


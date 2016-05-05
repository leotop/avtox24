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

/*
 * ����������� ������� � ������ ���� �� ������! �� ���������� � ��� ��������!
 * ���������� IncludeModuleLangFile � GetMessage - ��� �������������� � ���������� �������
 */
IncludeModuleLangFile(__FILE__);

/**
 * �������� ��������
 * Class LinemediaAutoRemoteSuppliersBrowser
 */
class LinemediaAutoRemoteSuppliersBrowser
{
    /**
     * CURL
     * @var resource
     */
    protected $curl;
    /**
     * URL
     * @var
     */
    protected $base_url;
    /**
     * ������ � ��������� �������
     * @var
     */
    protected $last_query_info;
    /**
     * ��������� �����
     * @var
     */
    protected $last_response;

    /**
     * � ������������ �������� ������ cURL � ��������������� ������� ���������
     */
    public function __construct()
    {
        $this->curl = curl_init();
        $this->resetSettings();
    }

    /**
     * ���������� GET �������
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
     * ���������� POST �������
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
     * ����� ��������
     */
    public function resetSettings()
    {
        $options = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_CONNECTTIMEOUT  => 3,
            CURLOPT_TIMEOUT         => 25,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 4,
            CURLOPT_USERAGENT       => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11 Linemedia Autoexpert Parser',
        );

        curl_setopt_array($this->curl, $options);
    }

    /**
     * ��������� referer
     * @param $ref
     */
    public function setReferer($ref)
    {
        curl_setopt($this->curl, CURLOPT_REFERER, $ref);
    }

    /**
     * ��������� useragent
     * @param $agent
     */
    public function setAgent($agent)
    {
        curl_setopt($this->curl, CURLOPT_USERAGENT, $agent);
    }

    /**
     * ��������� ������ ���������
     * @param $param
     * @param $val
     */
    public function setParam($param, $val)
    {
        curl_setopt($this->curl, $param, $val);
    }

    /**
     * ��������� �������� ���� � �������� (������ ������ � http://)
     * ������� ������������� ����� ������ �������
     * @param $url
     */
    public function setBaseUrl($url)
    {
        $this->base_url = $url;
    }

    /**
     * ���������������� ���������� �������
     * @return mixed
     * @throws Exception
     */
    protected function request()
    {

    	/*
    	* Cookie ����������� �������� ��� ������
    	*/
    	$cookiefile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/lm_auto_browser_' . md5($this->base_url);
    	$options = array(
            CURLOPT_COOKIEFILE      => $cookiefile,
            CURLOPT_COOKIEJAR       => $cookiefile,
        );

        curl_setopt_array($this->curl, $options);

        
        $this->last_response = false;
        
        try {
            $this->last_response = $response = curl_exec($this->curl);
        } catch (Exception $e) {
        	throw new Exception(curl_error($this->curl) . ' (#'.curl_errno($this->curl).')', curl_errno($this->curl));
        }
        

        if ($response === false) {
            throw new Exception('Error ' . curl_error($this->curl) . ' (#'.curl_errno($this->curl).')', curl_errno($this->curl));
        }

        /*
        * �������� ������������ ������
        */
        $this->last_query_info = curl_getinfo($this->curl);
        if($this->last_query_info['http_code'] != 200)
        	throw new Exception('Error http request, response code ' . $this->last_query_info['http_code'], LM_AUTO_DEBUG_ERROR);
        
        return $response;
    }

    /**
     * �������� ������ � ��������� �������
     * @return array
     */
    public function getLastQueryInfo()
    {
	    return (array) $this->last_query_info;
    }

    /**
     * �������� ��������� ������ (����� � ������ ������ �� ���� ������)
     * @return mixed
     */
    public function getLastResponse()
    {
	    return $this->last_response;
    }

    /**
     * �������� ���������� � �����������
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }
}

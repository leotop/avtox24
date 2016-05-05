<?php
/**
 * Linemedia Autoportal
 * Autodecdoc module
 * LinemediaAutoTecDocApiDriver
 * 
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */



IncludeModuleLangFile(__FILE__);

class LinemediaAutoTecDocApiDriver
{
    const SIG_LENGTH = 8; // ����� �������.

    const DEFAULT_ENCODING = 'UTF-8';

	const SERVER_DEV_NAME = 'auto.x.linemedia.ru';

    protected $id     = 0; // ID ������������ � �������
    protected $key    = ''; // ���� ������������

    protected $url    = ''; // ����� �����������
    protected $format = 'json'; // ������ ������ �������

    protected $version = '0.1.0'; // ������ API


    protected $ignore_modifications = false;
    protected $modifications; // ����������� ������� API
    protected $modifications_set = false; // ����� ���� ����������� ������� API


    /*
    * �� ������ ������� � DNS � ������, ��������������� ����������� �����, �������� IP �������
    */
    protected $api_server_ip = '88.198.67.81';


    /**
     * ����������� - ��������� ��������
     */
    public function __construct($id = 0, $key = '', $url = '', $format = 'json', $stub = '')
    {
        $this->id       = (int) $id;
        $this->key      = (string) $key;
        $this->url      = (string) $url;


        /*
         * ���� �������� �� �������� - ������ �����������
         */
        if ($this->id < 1) {
        	$this->id = COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_ID');
        }
        if ($this->key == '') {
        	$this->key = COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_KEY');
        }
        if ($this->url == '') {
        	$this->url = COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_URL');
        }
        if ($format == '') {
        	$format = COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_FORMAT');
        }

        $format = in_array($format, array('json', 'xml', 'serialized')) ? $format : 'json';
        $this->format = (string) $format;

        if ($this->format == 'json' && !function_exists('json_encode')) {
            $this->format = 'serialized';
        }

        $this->modifications = new LinemediaAutoTecDocApiModifications();

    }


    public function __call($function, $args = array())
    {
	    return $this->query($function, $args[0]);
    }


    /**
     * ���������� �������
     */
    public function query($cmd, $data = array())
    {

        /*
		 * ����������� ������ ������� ������ � ������ ������� �������
		 */
		switch ($this->format) {
			case 'json':
				$encoded_data = json_encode($data);
                break;
			case 'serialized':
				$encoded_data = serialize($data);
                break;
			case 'xml':
				$encoded_data = LinemediaAutoArr2XML::encode($data);
                break;
		}

		if (count($data) == 0) {
			$encoded_data = '';
		}
		

		/*
		 * ����������� �������, �������������� ������������ �������
		 */
		$md5 = md5($cmd . $encoded_data . $this->key);
		$sig = substr($md5, 0, self::SIG_LENGTH);

		$encoded_data = urlencode($encoded_data);

		/*
		 * URL �� �������� ���� �������� ������
		 */
		$out = $in = $this->format;
		$query = $this->url . "/?cmd=$cmd&sig=$sig&out=$out&id=" . $this->id . '&v=' . $this->version;


		/*
		 * ����� ���������� ����������
		 */
		//LinemediaAutoDebug::add('Linemedia API query: ' . $query, print_r($data, true));
		
		
		$agent = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'uagent' => $_SERVER['HTTP_USER_AGENT'],
		);

		/*
		 * ���������� �������� �������
		 */
        $uagent = "Linemedia API Client (" . $_SERVER['SERVER_NAME'] . ") [" . $this->id . "]";

        if (function_exists('curl_init')) {
            $ch = curl_init('http://' . $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	1);
            curl_setopt($ch, CURLOPT_HEADER, 			0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	1);
            curl_setopt($ch, CURLOPT_USERAGENT, 		$uagent);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 			30);
            curl_setopt($ch, CURLOPT_FAILONERROR, 		1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 		1);
            curl_setopt($ch, CURLOPT_ENCODING,			'gzip');

            // POST requests
            curl_setopt($ch, CURLOPT_POST, 				true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 		http_build_query(array('data' => $data, 'agent' => $agent)));
            
            
            // start monitoring
            if (CModule::IncludeModule("linemedia.auto")) {
                $timer = LinemediaAutoMonitoring::startTimer(array('scope' => 'api', 'module' => 'linemedia.autotecdoc', 'action' => $cmd));
            }

            // perform request
            $response = curl_exec($ch);
            
            // end monitoring
            if (CModule::IncludeModule("linemedia.auto")) {
                LinemediaAutoMonitoring::stopTimer($timer);
            }
            
            $this->last_request = $query;

            $error = curl_errno($ch);
            if ($error) {

                /*
                * ������ ������� �� ������ ������ ������� DNS
                */
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	10);
                $response = curl_exec($ch);
                $this->last_request = $query;
                $error = curl_errno($ch);


                if ($error) {
    				/*
    				 * ������� ����� ������ api �������������.
    				 * ����������� �� ��������: "LM_AUTO_DEBUG_NOTICE"
    				 */
                    $err_type = $_SERVER['SERVER_NAME'] == self::SERVER_DEV_NAME ? LM_AUTO_DEBUG_ERROR :  LM_AUTO_DEBUG_NOTICE;
                    //$err_type = $error == 28 ? LM_AUTO_DEBUG_NOTICE : LM_AUTO_DEBUG_ERROR;
                    throw new Exception ((GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . curl_error($ch)));
                }
            }
            curl_close($ch);
        } else {

	        throw new Exception ("Need PHP's cURL support");

	        /*
	         * ������� ������� ����� ������ � file_get_contents
	         */

	        /*
	        $opts = array('http' =>
		                      array(
			                      'method'  => 'POST',
			                      'header'  => 'Content-type: application/x-www-form-urlencoded',
			                      'content' => $post_json
		                      )
	        );

	        $context  = stream_context_create($opts);

            try {
                $response = file_get_contents('http://' . $query, false, $context);
            } catch (Exception $e) {
                throw $e;
            }
	        */
        }


		/*
		 * ��������� ��������� ������
		 * ���������� ������ API
		 */
		if ($response == '') {
			/*
			 * ������� ����� ������ api �������������.
			 * ����������� �� ��������: "LM_AUTO_DEBUG_NOTICE", ����: LM_AUTO_DEBUG_ERROR
			 */
			$err_type = $_SERVER['SERVER_NAME'] == self::SERVER_DEV_NAME ? LM_AUTO_DEBUG_ERROR : LM_AUTO_DEBUG_NOTICE;
			throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_EMPTY_RESPONSE'));

			//$response = array('status' => 'error', 'data' => null, 'error' => array('code' => -1, 'error_text' => '������� ������ ����� �� �������'));
			//return $response;
		}

		/*
		 * ����������� ���������� ������ � ������ � �������
		 */
		switch ($this->format) {
			case 'json':
                $response_arr = json_decode($response, 1);
			    break;
			case 'serialized':
				$response_arr = @unserialize($response);
			    break;
			case 'xml':
				$response_arr = LinemediaAutoXML2Arr::decode($response);
				$response_arr = $response_arr['xml'];
			    break;
		}



        /*
        *    ��������� ��� �������������.
        *    ��������� �������� �� ����� � ������� ����� "MOVANO B �������e".
        */
        mb_substitute_character('');
        setlocale(LC_COLLATE,'ru_RU.UTF-8');
        setlocale(LC_CTYPE,'ru_RU.UTF-8');
        mb_internal_encoding('utf-8');

        /*
         * �������������� ���������.
         */
        if (!defined('BX_UTF') || BX_UTF != true) {
            $response_arr = self::iconvArray($response_arr, self::DEFAULT_ENCODING, 'WINDOWS-1251//TRANSLIT');
        }

        setlocale(LC_COLLATE, 0);
        setlocale(LC_CTYPE, 0);
		/*
		 * ��������� ��������� ������
		 * ������ API ������ ������������ ����� (������ API ������ ������ ���������� ������)
		 */
		if (!is_array($response_arr)) {
			/*
			 * ������� ����� ������ api �������������.
			 * ����������� �� ��������: "LM_AUTO_DEBUG_NOTICE", ����: LM_AUTO_DEBUG_ERROR
			 */
			$err_type = $_SERVER['SERVER_NAME'] == self::SERVER_DEV_NAME ? LM_AUTO_DEBUG_ERROR : LM_AUTO_DEBUG_NOTICE;
			throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_INCORRECT_RESPONSE') . ' ' . $response);
		}

        /*
         * ������ ������.
         */
        if (isset($response_arr['status']) && $response_arr['status'] == 'error') {


        	/*
        	 * ���������������� ������.
        	 */
        	$user_errors = array(
        		2,    // client
        		3,    // password
        		112,  // period
				131,  // day limit
        		963
        	);

			/*
			 * ������� ����� ������ api �������������.
			 * ����������� �� ��������: "LM_AUTO_DEBUG_NOTICE", ����: LM_AUTO_DEBUG_USER_ERROR, LM_AUTO_DEBUG_ERROR
			 */

        	if (in_array($response_arr['error']['code'], $user_errors)) {
				$err_type = LM_AUTO_DEBUG_USER_ERROR;
	            throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . $response_arr['error']['text']);
	        } else {
				$err_type = $_SERVER['SERVER_NAME'] == self::SERVER_DEV_NAME ? LM_AUTO_DEBUG_ERROR : LM_AUTO_DEBUG_NOTICE;
	        	throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . $response_arr['error']['text']);
            }
        }


  		/*
		 * ����� ���������� ����������
		 */
        if (CModule::IncludeModule("linemedia.auto")) {
            LinemediaAutoDebug::add('Linemedia API response: ', '<b>' . $cmd . '</b><br>' . print_r($data, true) . print_r($response_arr, true), LM_AUTO_DEBUG_WARNING);
        }


		/*
		 * �������� ����������� ������� ���
		 */
		if (!$this->ignore_modifications) {
			if ($this->modifications_set) {
				$this->modifications->changeSetId($this->modifications_set);
            }
			$this->modifications->applyModifications($cmd, $data, $response_arr['data']);
        }


		/*
		 * ���������� ������� ���������� ��������� �������
		 */
		return $response_arr;
    }



    /**
     * �������� ����������� ����������
     */
    public function ignoreModifications()
    {
	    $this->ignore_modifications = true;
    }


    /**
     * ������� ��� ����������� ����������
     */
    public function changeModificationsSetId($id)
    {
	    $this->modifications_set = $id;
    }


    /**
     * ����������� ���������� �� ������� �������
     */
    protected function iconvArray($array, $from = 'UTF-8', $to = 'cp1251')
    {
        if (empty($array) || !is_array($array)) {
            return array();
        }

        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::iconvArray($value, $from, $to);
            } else {
                $result[$key] = iconv($from, $to, $value);
            }
        }
        return $result;
    }

}



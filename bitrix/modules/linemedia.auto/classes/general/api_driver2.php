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

/**
 * class providing capabilities for working with Linemedia api
 */
class LinemediaAutoApiDriver2
{
    /**
     * @var string DEFAULT_ENCODING
     */
    const DEFAULT_ENCODING = 'UTF-8';

    /**
     * user`s id 
     * @var int $id
     */
    protected $id     = 0; // ID пользователя в системе
    
    /**
     * url
     * @var string $url
     */
    protected $url    = ''; // Адрес подключения
    
    /**
     * format of data exchanging
     * @var string $format
     */
    protected $format = 'json'; // Формат обмена данными

    /**
     * API version
     * @var string $version
     */
    protected $version = '0.2.0'; // Версия API

    /**
     * ignore_modifications
     * @var boolean $ignore_modifications
     */
    protected $ignore_modifications = false;
    
    /**
     * API response 
     * @var LinemediaAutoTecDocApiModifications $modifications
     * @see LinemediaAutoTecDocApiModifications
     */
    protected $modifications; // Модификация ответов API
    
    /**
     * modifications_set
     * @var boolean $modifications_set
     */
    protected $modifications_set = false; // Смена сета модификаций ответов API


   /**
    *  constructor
    * @param number $id
    * @param string $key
    * @param string $url
    * @param string $format
    * @param string $stub
    */
    public function __construct($id = 0, $key = '', $url = '', $format = 'json', $stub = '')
    {
        $this->id       = (int) $id;
        $this->key      = (string) $key;
        $this->url      = (string) $url;


        /*
         * Если значения не переданы - возьмём стандартные
         */
        if ($this->id < 1) {
        	$this->id = COption::GetOptionString('linemedia.auto', 'LM_AUTO_TECDOC_API_ID');
        }
        if ($this->url == '') {
        	$this->url = COption::GetOptionString('linemedia.auto', 'LM_AUTO_TECDOC_API_URL');
        }

        $this->modifications = new LinemediaAutoApiModifications();

    }


    /**
     * magic method
     * @param function $function
     * @param array $args
     * @return mixed 
     */
    public function __call($function, $args = array())
    {
	    return $this->query($function, $args[0]);
    }


    /**
     * Query processing
     * @param string $cmd
     * @param array $data
     * @throws Exception
     * @return array 
     */
    public function query($cmd, $data = array())
    {
		$agent = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'agent' => $_SERVER['HTTP_USER_AGENT'],
		);
		
		
		$post = array(
			'agent' => $agent,
			'args' => $data,
		);
		
		$post_json = json_encode($post);
		
		
		/*
		 * URL по которому надо отослать запрос
		 */
		$out = $in = $this->format;
		$query = $this->url . "/?cmd=$cmd&id=" . $this->id . '&v=' . $this->version;
		
		
		/**
		* Run API xhprof
		*/
		if($_GET['lm_auto_debug'] == 'Y') {
			$query .= '&xhprof=1';
		}
		

		/*
		 * Вывод отладочной информации
		 */
		LinemediaAutoDebug::add('Linemedia API query: ' . $query, print_r($data, true));


		/*
		 * Выполнение простого запроса
		 */
        if (function_exists('curl_init')) {
            $ch = curl_init('http://' . $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	1);
            curl_setopt($ch, CURLOPT_HEADER, 			0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	1);
            curl_setopt($ch, CURLOPT_USERAGENT, 		"Linemedia API Client (" . $_SERVER['SERVER_NAME'] . ") [" . $this->id . "]");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 			30);
            curl_setopt($ch, CURLOPT_FAILONERROR, 		1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 		1);
            curl_setopt($ch, CURLOPT_ENCODING,			'gzip');
            
            curl_setopt($ch, CURLOPT_POST,           	1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,     	$post_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,     	array('Content-Type: application/json'));
            
            try {
            
            	// start monitoring
	            $timer = LinemediaAutoMonitoring::startTimer(array('scope' => 'api', 'module' => 'linemedia.auto', 'action' => $cmd));
	            
	            // perform request
	            $response = curl_exec($ch);
	            
	            // end monitoring
	            LinemediaAutoMonitoring::stopTimer($timer);
            } catch (Exception $e) {
                throw $e;
            }

            $this->last_request = $query;

            $error = curl_errno($ch);
            if ($error) {
                throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . curl_error($ch));
            }
            curl_close($ch);
        } else {

	        throw new Exception ("Need PHP's cURL support");

	        /*
	         * Рабочий вариант через потоки и file_get_contents
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
		 * Обработка возможных ошибок
		 * Недоступен сервер API
		 */
		if ($response == '') {
			throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_EMPTY_RESPONSE'));

			$response = array('status' => 'error', 'data' => null, 'error' => array('code' => -1, 'error_text' => 'Получен пустой ответ от сервера'));
			return $response;
		}


		/*
		 * Преобразуем полученные данные в массив с ответом
		 */
        $response_arr = json_decode($response, 1);
        
        /*
        *    настройки для перекодировки.
        *    идеальная проверка на опеле в моделях найти "MOVANO B грузовоe".
        */
        mb_substitute_character('');
        setlocale('ru_RU.UTF-8');
        mb_internal_encoding('utf-8');

        /*
         * Преобразование кодировки.
         */
        if (!defined('BX_UTF') || BX_UTF != true) {
            $response_arr = self::iconvArray($response_arr, self::DEFAULT_ENCODING, 'WINDOWS-1251//TRANSLIT');
        }

		/*
		 * Обработка возможных ошибок
		 * Сервер API вернул неправильный ответ (Сервер API всегда должен возвращать массив)
		 */
		if (!is_array($response_arr)) {
			throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_INCORRECT_RESPONSE') . ' ' . $response);
		}

        /*
         * Пришла ошибка.
         */
        if (isset($response_arr['status']) && $response_arr['status'] == 'error') {
        	
        	if($response_arr['error_type'] == 'user')
	            throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ' [USER]: ' . $response_arr['error_text']);
	        else
	        	throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ' [SYSTEM]: ' . $response_arr['error_text']);


        }


  		/*
		 * Вывод отладочной информации
		 */
		LinemediaAutoDebug::add('Linemedia API response: ', '<b>' . $cmd . '</b><br>' . print_r($data, true) . print_r($response_arr, true), LM_AUTO_DEBUG_WARNING);


		/*
		 * Применим модификацию ответов АПИ
		 */
		if (!$this->ignore_modifications) {
			if ($this->modifications_set) {
				$this->modifications->changeSetId($this->modifications_set);
            }
			$this->modifications->applyModifications($cmd, $data, $response_arr['data']);
        }

		/*
		 * Выполнение запроса технически завершено успешно
		 */
		return $response_arr;
    }
    
    /**
     * turning off modification of outcome
     * @return void
     */
    public function ignoreModifications()
    {
	    $this->ignore_modifications = true;
    }


    /**
     * vary set of outcome`s modification
     * @param int $id
     * @return void
     */
    public function changeModificationsSetId($id)
    {
	    $this->modifications_set = $id;
    }

    /**
     * convert incoming array
     * @param array $array
     * @param string $from
     * @param string $to
     * @return array
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



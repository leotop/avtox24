<?php

/**
 * Linemedia Autoportal
 * Downloader parser module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

if (!CModule::IncludeModule('linemedia.auto')) {
    return;
}

/**
 * Протокол передачи данных FTP
 * Class LinemediaAutoDownloaderFtpProtocol
 */
class LinemediaAutoDownloaderFtpProtocol extends LinemediaAutoProtocol implements LinemediaAutoDownloaderIProtocol
{
    /**
     * Заголовок
     * @var string
     */
    public static $title = 'FTP';

    /**
     * Логин
     * @var string
     */
    protected $login;
    /**
     * Пароль
     * @var string
     */
    protected $password;
    /**
     * Сервер
     * @var mixed
     */
    protected $server;
    /**
     * Порт
     * @var int
     */
    protected $port;
    /**
     * Пассивный режим
     * @var bool
     */
    protected $passive;
    /**
     * Имя файла на сервере
     * @var string
     */
    protected $remote_filename;
    /**
     * Таймаут
     * @var int
     */
    protected $connect_timeout = 10;

    protected $conn_id = null;

    /**
     * Создает объект, инициализирует параметры соединения
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->login            = trim($data['LOGIN']);
        $this->password         = trim($data['PASSWORD']);
        
        $this->server           = trim($data['SERVER']);
        $this->server           = str_replace(array('ftp://'), '', trim($this->server));
        
        $this->port             = $data['PORT'] ? (int) $data['PORT'] : 21;
        $this->passive          = $data['PASSIVE'] ? $data['PASSIVE'] : true;
        
        $this->remote_filename  = trim($data['FILENAME']);
    }

    /**
     * Скачка файла
     * @param bool $test - проверка подключения
     * @return bool|string
     * @throws Exception
     */
    public function download($test = false)
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 0777, true);
        }

        $temp_filename = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 'lm_auto_downloader_');
        
        /*
         * Соединение
         */
        if (!$this->conn_id = ftp_connect($this->server, $this->port, $this->connect_timeout)) {
            throw new Exception('Couldn\'t connect to ftp ' . $this->server . ' port:' . $this->port);
        }
        if(!$test)
	    	self::log('Connected to: ' . $this->server .':'. $this->port);
        
        
        /*
         * Логин
         */
        if (!$login_result = ftp_login($this->conn_id, $this->login, $this->password)) {
            ftp_close($this->conn_id);
            throw new Exception('Couldn\'t login to ftp ' . $this->server . ' with login: ' . $this->login . ' pass:' . $this->password);
        }
        if(!$test)
	    	self::log('Logined as: ' . $this->login);
        
        
        /*
         * Включение пассивного режима
         */
        if ($this->passive) {
            ftp_pasv($this->conn_id, true);
            if(!$test)
            	self::log('Passive mode on');
        }
        
        if ($test) {

            $path = dirname($this->remote_filename);
            //$name = basename($this->remote_filename);// rus letters error
            $nameSlash = '/' . end(explode('/', $this->remote_filename));
            $nameNoSlash = end(explode('/', $this->remote_filename));
            
            $contents_on_server = $this->getDirectory($path);
            if (in_array($nameSlash, $contents_on_server) || in_array($nameNoSlash, $contents_on_server) || in_array($path . $nameSlash, $contents_on_server)) {
                return true;
            }
            return 'No such file! Files available: ' . join(', ', $contents_on_server);
        }
        
        
        
        /*
         * Загрузка файла
         */
        // список попыток загрузки
        $list_attempt = array($this->remote_filename);
        // check if is unicode
        if(mb_detect_encoding($this->remote_filename) == 'UTF-8') {
            // cp1251 variant
            $list_attempt[] = iconv("utf-8", "windows-1251", $this->remote_filename);
        }

        $success = false;
        foreach($list_attempt as $remote_name) {
        	self::log("Try $remote_name");
            if(ftp_get($this->conn_id, $temp_filename, $remote_name, FTP_BINARY)) {
                $success = true;
                break;
            }
        }

        if(!$success) {
            ftp_close($this->conn_id);
            self::log('Error downloading ' . $remote_name);
            throw new Exception('Couldn\'t download ' . $remote_name . ' FTP get error');
        }

        ftp_close($this->conn_id);
        
        self::log('Success downloading ' . $temp_filename);
        
        return $temp_filename;
    }

    protected function getDirectory($path) {

        if(mb_detect_encoding($path) == 'UTF-8') {

            $list = ftp_nlist($this->conn_id, $path);
            if(is_array($list) && count($list) > 0) return $list;

            // cp1251
            $conv_path = iconv("utf-8", "windows-1251", $path);
            $list = ftp_nlist($this->conn_id, $conv_path);
            if(is_array($list)) {
                foreach($list as $key => $value) {
                    $list[$key] = iconv("windows-1251", "utf-8", $value);
                }
                return $list;
            }

        } else {
            return ftp_nlist($this->conn_id, $path);
        }
        return false;
    }

    /**
     * Получение конфигурации.
     * @return array
     */
    public static function getConfigVars()
    {
        return array(
            'SERVER' => array(
                'title' => GetMessage('SERVER'),
                'type'  => 'string',
                'placeholder' => 'example.com',
                'required' => true,
            ),
            'PORT' => array(
                'title' => GetMessage('PORT'),
                'type' => 'string',
                'default' => 21,
                'size' => 4,
                'required' => true,
            ),
            'PASSIVE' => array(
                'title' => GetMessage('PASSIVE'),
                'type'  => 'checkbox',
                'default' => true,
            ),
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
                'required' => true,
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            ),
            'FILENAME' => array(
                'title' => GetMessage('FILENAME'),
                'type' => 'string',
                'placeholder' => '/prices/last.csv',
                'size' => 40,
                'required' => true,
            ),
        );
    }

    /**
     * Доступны ли функции ftp?
     * @return bool
     */
    public static function available()
    {
        return function_exists('ftp_connect');
    }

    /**
     * Сообщение о требуемых функциях.
     * @return mixed
     */
    public static function getRequirements()
    {
        return GetMessage('FTP_REQUIREMENTS');
    }

    /**
     * Получить оригинальное имя файла
     * @return string
     */
    public function getOriginalFileName() {
        return basename($this->remote_filename);
    }

    /**
     * Подключение текущего протокола.
     * @param $protocols
     */
    public static function inclusion(&$protocols)
    {
        $protocols['ftp'] = array(
            'classname' => __CLASS__,
            'available' => self::available(),
            'title'     => GetMessage('PROTOCOL_FTP'),
            'config'    => self::getConfigVars(),
            'upload'    => '/'.COption::GetOptionString('main', 'upload_dir', 'upload') . '/linemedia.autodownloader/new/',
        );
    }
}


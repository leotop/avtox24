<?php

/**
 * Linemedia Autoportal
 * Downloader module
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
 * �������� �������� ������ HTTP
 * Class LinemediaAutoDownloaderHttpProtocol
 */
class LinemediaAutoDownloaderHttpProtocol extends LinemediaAutoProtocol implements LinemediaAutoDownloaderIProtocol
{
    /**
     * ���������
     * @var string
     */
    public static $title = 'HTTP';
    /**
     * �����
     * @var string
     */
    protected $login;
    /**
     * ������
     * @var string
     */
    protected $password;
    /**
     * ���
     * @var string
     */
    protected $url;

    /**
     * ������� ������, �������������� ��������� ����������
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->login       = trim($data['LOGIN']);
        $this->password    = trim($data['PASSWORD']);
        
        $this->url         = trim($data['URL']);
        $this->port        = $data['PORT'] ? (int) $data['PORT'] : 80;
    }

    /**
     * ���������� �����
     * @param bool $test - �������� �����������
     * @return bool|string
     * @throws Exception
     */
    public function download($test = false)
    {
        $ch = curl_init();       
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
        curl_setopt($ch, CURLOPT_URL, $this->url);    
        
        if ($this->login != '') {
            curl_setopt($ch, CURLOPT_USERPWD, $this->login . ':' . $this->password);    
        }

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 0777, true);
        }
        
        /*
         * ����� ���� � ����
         */
        $temp_filename = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 'lm_auto_downloader_');
        $fp = fopen($temp_filename, 'w');

        
        $options = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_TIMEOUT         => 15,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 4,
            CURLOPT_USERAGENT       => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11 Linemedia Autoexpert Downloader',
        );
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        /*
         * ��������
         */
        if ($test) {
            if ($this->url == '') {
                return 'Empty url';
            }
            
            $options = array(
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_HEADER          => true,
                CURLOPT_NOBODY          => true,
            );
            curl_setopt_array($ch, $options);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            try {
                $result = curl_exec($ch);
            } catch (Exception $e) {
                return "Error connect: " . $e->GetMessage();
            }
            
            $info = curl_getinfo($ch);
            
            /*
             * ��� �� 200.
             */
            if ($info['http_code'] != 200) {
                return 'Error! Http code ' . $info['http_code'];
            }
            
            /*
             * ���� �� �����, ��������� HTML ��������.
             */
            if (strpos($info['content_type'], 'html') !== false) {
                return 'Looks like HTML document, not file';
            }
            
            /*
             * ���� �� �����, ��������� ��������.
             */
            if (strpos($info['content_type'], 'image') !== false) {
                return 'Looks like image, not file';
            }
            
            return true;
        }
        
        
        try {
            $result = curl_exec($ch);
        } catch (Exception $e) {
            curl_close($ch); 
            fclose($fp);
            self::log('Error downloading ' . $this->url . ' ('.$e->GetMessage().')');
            throw new Exception('Http error: ' . $e->GetMessage());
        }
        
        curl_close($ch); 
        fclose($fp);
        
        self::log('Success downloading, saved to: ' . $temp_filename);
        
        return $temp_filename;
    }

    /**
     * ��������� ������������.
     * @return array
     */
    public static function getConfigVars()
    {
        return array(
            'URL' => array(
                'title' => GetMessage('URL'),
                'type' => 'string',
                'placeholder' => 'http://www.site.com/prices/last.csv',
                'size' => 60,
                'required' => true,
            ),
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            ),

        );
    }

    /**
     * �������� �� ������� http (curl)?
     * @return bool
     */
    public static function available()
    {
        return function_exists('curl_init');
    }
    

    /**
     * ��������� � ��������� ��������.
     * @return string
     */
    public static function getRequirements()
    {
        return GetMessage('HTTP_REQUIREMENTS');
    }

    /**
     * �������� ������������ ��� �����
     * @return string
     */
    public function getOriginalFileName() {
        return basename($this->url);
    }

    /**
     * ����������� �������� ���������.
     * @param $protocols
     */
    public static function inclusion(&$protocols)
    {
        $protocols['http'] = array(
            'classname' => __CLASS__,
            'available' => self::available(),
            'title'     => GetMessage('PROTOCOL_HTTP'),
            'config'    => self::getConfigVars(),
            'upload'    => '/'.COption::GetOptionString('main', 'upload_dir', 'upload') . '/linemedia.autodownloader/new/',
        );
    }
}


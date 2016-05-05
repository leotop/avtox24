<?php
/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

class LinemediaAutoSimpleCache {

    public static function create($config) {
        return new LinemediaAutoSimpleCacheFile($config);
    }
}


/**
 * �������� �������� ���
 * Class LinemediaAutoSimpleCache
 */
class LinemediaAutoSimpleCacheFile {

    /**
     * ���� � ����� ���� ��������
     * @var string
     */
    private $basePath = '/bitrix/cache';
    /**
     * ���� ���� ������������ ����� ����
     * @var string
     */
    private $path = '/lm_auto/';

    public function __construct($config) {

        if(array_key_exists('path', $config)) $this->path = $config['path'];
        if(array_key_exists('base_path', $config)) $this->basePath = $config['base_path'];
    }

    /**
     * ���������� ������ �� ���� �� �����, � ������ ������� �����
     * @param $key - ���� ������ ����
     * @param $expired - ����� ����� ���� � ��������
     * @return array|bool|string
     */
    public function getData($key, $expired) {

        $expired = intval($expired);
        if($expired < 1) return false;

        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $this->basePath . $this->path . $key . '.cache';

        if(file_exists($fullPath)) {

            $tsFile = filemtime($fullPath);

            if($tsFile < (time() - $expired)) { // expired
                unlink($fullPath);
                return false;
            }

            try {
            	return include($fullPath);
            } catch(Exception $e) {
                return false;
            }
        }
    }

    /**
     * �������� ������ � ���
     * @param $key
     * @param $data
     * @return bool
     */
    public function setData($key, $data) {

        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $this->basePath . $this->path . $key . '.cache';

        $cacheData = "<?php\nreturn " . var_export($data, true) . ';';
        
        try {

            $fp = fopen($fullPath . '.tmp', 'w');

            if (flock($fp, LOCK_EX)) {  // ��������� ������������ ����������
                ftruncate($fp, 0);      // ������� ����
                fwrite($fp, $cacheData);
                fflush($fp);            // ������� ����� ����� ������� ����������
                flock($fp, LOCK_UN);    // �������� ����
                return rename($fullPath . '.tmp', $fullPath);
            }

        } catch(Exception $e) {
            return false;
        }
    }
}
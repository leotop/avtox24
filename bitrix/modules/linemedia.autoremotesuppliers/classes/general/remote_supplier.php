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

/*
 * Битриксовых функций в классе быть не должно! он вызывается и без битрикса!
 * Исключения IncludeModuleLangFile и GetMessage - они переопределены в консольном скрипте
 */
IncludeModuleLangFile(__FILE__);

/**
 * Интерфейс удалённого поставщика
 * Class LinemediaAutoRemoteSuppliersSupplier
 */
abstract class LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * Браузер для хождения по сайтам
     * @var LinemediaAutoRemoteSuppliersBrowser
     */
    protected $browser;

    /**
     * Настройки поиска: запрос
     * @var
     */
    protected $query;
    /**
     * Настройки поиска: бренд
     * @var
     */
    protected $brand_title;
    /**
     * Настройки поиска extra
     * @var
     */
    protected $extra;
    /**
     * Настройки поиска: аналоги
     * @var bool
     */
    protected $search_analogs = true;

    /**
     * настройки профиля(логин,пароль,итд)
     * @var null
     */
    protected $profile_options = null;

    /**
     * Результаты поиска
     * @var
     */
    protected $response_type;

    /**
     * Каталоги
     * обязательно указывать пустой массив, иначе не срабатывает array_merge_recursive
     * @var array
     */
    protected $catalogs = array();
    /**
     * Детали
     * @var array
     */
    protected $parts = array();

    /**
     * Логин
     * @return mixed
     */
    abstract function login();

    /**
     * Поиск
     * @return mixed
     */
    abstract function search();

    /**
     * Конфил
     * @return mixed
     */
    abstract function getConfigVars();

    /**
     * создание объекта
     */
    public function __construct()
    {
        /*
          * Битриксовых функций в классе быть не должно! он вызывается и без битрикса!
          */
    	//CModule::IncludeModule('linemedia.auto');
        $this->browser = new LinemediaAutoRemoteSuppliersBrowser();
    }

    /**
     * Установить запрос (артикул)
     * @param $query
     */
    public function setQuery($query)
    {
        $this->query = LinemediaAutoPartsHelper::clearArticle((string) $query);
    }

    /**
     * Установить имя бренда
     * @param $brand_title
     */
    public function setBrandTitle($brand_title)
    {
        $this->brand_title = (string) $brand_title;
    }

    /**
     * Установить extra
     * @param $param
     * @param $val
     */
    public function setExtra($param, $val)
    {
        $this->extra[$param] = $val;
    }

    /**
     * parts \ catalogs
     * @return mixed
     */
    public function getResponseType()
    {
        return $this->response_type;
    }


    /**
     * Каталоги
     * @return array
     */
    function getCatalogs()
    {
        return $this->catalogs;
    }

    /**
     * Детали
     * @return array
     */
    function getParts()
    {
        return $this->parts;
    }

    /**
     * Устанавливает опции
     * @param $data
     */
    function setOptions($data)
    {
        $this->profile_options = $data;
    }

    /**
     * Возвращает опции
     * @return null
     */
    function getOptions()
    {
        return $this->profile_options;
    }

    /**
     * Получение объекта класса конкретного поставщика (БЕЗ НАСТРОЕК ДОСТУПА).
     * @param $code
     * @return null
     */
    public static function getSupplierClass($code)
    {
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/profiles/' . strtolower($code) . '.supplier.php';
        if (!file_exists($filename)) {
            return null;
        }
        require_once $filename;
        $classname = $code . 'RemoteSupplier';
        return new $classname;
    }

    /**
     * Загрузка объекта поставщика по коду
     * @param $code
     * @return null
     * @throws Exception
     */
    public static function load($code)
    {
        $result = self::getSupplierClass($code);

        if (!CModule::IncludeModule('iblock')) {
            throw new Exception('Not found iblock module');
        }
        
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS', 0);

        if ($iblock_id > 0) {
            $rs = CIBlockElement::GetList(array(), array('PROPERTY_supplier_id' => $code, 'IBLOCK_ID' => $iblock_id), 0, 0, array('ID', 'IBLOCK_ID', 'PROPERTY_api'));
            if ($rs && $rs->SelectedRowsCount() > 0) {
                $supplier_data = $rs->Fetch();
                if (!is_object($result)) {
                    $result = self::getSupplierClass($supplier_data['PROPERTY_API_VALUE']['LMRSID']);
                }
                if (is_object($result)) {
                    $result->setOptions($supplier_data['PROPERTY_API_VALUE']);
                    //$result->init();
                }
            }
        }

        if (!is_object($result)) {
            throw new Exception($supplier_data['PROPERTY_API_VALUE']['LMRSID'] . ' profile missing');
        }
        return $result;
    }

    /**
     * Загрузка по ID в битриксе.
     * @param $id
     * @return null
     * @throws Exception
     */
    public static function loadByID($id)
    {
        if (!CModule::IncludeModule('iblock')) {
            throw new Exception('Not found iblock module');
        }
        
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS', 0);
        if ($iblock_id > 0) {
            $rs = CIBlockElement::GetList(array(), array('ID' => $id, 'IBLOCK_ID' => $iblock_id), 0, 0, array('ID','IBLOCK_ID','PROPERTY_api'));
            if ($rs && $rs->SelectedRowsCount() > 0) {
                $supplier_data = $rs->Fetch();
                $ret = self::load($supplier_data['PROPERTY_API_VALUE']['LMRSID']);
                $ret->setOptions($supplier_data['PROPERTY_API_VALUE']);
                return $ret;
            }
        }
        return null;
    }

    /**
     * Загрузка объекта поставщика по массиву API
     * @param $arApi - параметр поставщика PROPERTY_API_VALUE
     * @return bool|object - объект класса профиля поставщика
     * @throws Exception
     */
    public static function loadByApi($api_property) {

        $result = false;

        if(is_array($api_property)) {
            $result = self::getSupplierClass($api_property['LMRSID']);
        }
        if (is_object($result)) {
            $result->setOptions($api_property);
        }
        if (!is_object($result)) {
            throw new Exception('loadByApi ' . (is_array($api_property) ? $api_property['LMRSID'] : $api_property) . ' profile missing');
        }
        return $result;
    }

    /**
     * Список доступных поставщиков
     * @var bool
     */
    static $suppliers_cache = false;

    /**
     * Список профилей
     * @param string $filter
     * @return array|bool
     * @throws Exception
     */
    public static function getList($filter = '*')
    {
        if (is_array(self::$suppliers_cache)) {
            return self::$suppliers_cache;
        }
        static $suppliers;
        
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/profiles/';
        if (!file_exists($dir)) {
            throw new Exception('Profiles folder missing');
            return;
        }
        
        $suppliers = array();
        foreach (glob($dir . "$filter.supplier.php") as $filename) {
            require_once $filename;
            $code = basename($filename, '.supplier.php');

            $classname = ucfirst($code) . "RemoteSupplier";
            /* PHP 5.3+
            if (!class_exists($classname) || !isset($classname::$title)) {
                continue;
            }*/
            if (!class_exists($classname)) {
                continue;
            }
            // for PHP 5.2 and below
            $rc = new ReflectionClass($classname);
            $title = $rc->getStaticPropertyValue('title');
            unset($rc);
            if (empty($title)) {
                continue;
            }
            // $suppliers[$code] = $classname::$title; -- PHP 5.3+
            $suppliers[ $code ] = $title;
        }

        self::$suppliers_cache = $suppliers;
        return $suppliers;
    }


    /**
     * Опции профиля
     * @param $code
     * @return mixed
     */
    public function getProfileOption($code)
    {
	    return $this->profile_options[$code];
    }

}
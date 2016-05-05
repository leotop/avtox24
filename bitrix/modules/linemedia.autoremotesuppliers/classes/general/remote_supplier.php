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
 * ����������� ������� � ������ ���� �� ������! �� ���������� � ��� ��������!
 * ���������� IncludeModuleLangFile � GetMessage - ��� �������������� � ���������� �������
 */
IncludeModuleLangFile(__FILE__);

/**
 * ��������� ��������� ����������
 * Class LinemediaAutoRemoteSuppliersSupplier
 */
abstract class LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * ������� ��� �������� �� ������
     * @var LinemediaAutoRemoteSuppliersBrowser
     */
    protected $browser;

    /**
     * ��������� ������: ������
     * @var
     */
    protected $query;
    /**
     * ��������� ������: �����
     * @var
     */
    protected $brand_title;
    /**
     * ��������� ������ extra
     * @var
     */
    protected $extra;
    /**
     * ��������� ������: �������
     * @var bool
     */
    protected $search_analogs = true;

    /**
     * ��������� �������(�����,������,���)
     * @var null
     */
    protected $profile_options = null;

    /**
     * ���������� ������
     * @var
     */
    protected $response_type;

    /**
     * ��������
     * ����������� ��������� ������ ������, ����� �� ����������� array_merge_recursive
     * @var array
     */
    protected $catalogs = array();
    /**
     * ������
     * @var array
     */
    protected $parts = array();

    /**
     * �����
     * @return mixed
     */
    abstract function login();

    /**
     * �����
     * @return mixed
     */
    abstract function search();

    /**
     * ������
     * @return mixed
     */
    abstract function getConfigVars();

    /**
     * �������� �������
     */
    public function __construct()
    {
        /*
          * ����������� ������� � ������ ���� �� ������! �� ���������� � ��� ��������!
          */
    	//CModule::IncludeModule('linemedia.auto');
        $this->browser = new LinemediaAutoRemoteSuppliersBrowser();
    }

    /**
     * ���������� ������ (�������)
     * @param $query
     */
    public function setQuery($query)
    {
        $this->query = LinemediaAutoPartsHelper::clearArticle((string) $query);
    }

    /**
     * ���������� ��� ������
     * @param $brand_title
     */
    public function setBrandTitle($brand_title)
    {
        $this->brand_title = (string) $brand_title;
    }

    /**
     * ���������� extra
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
     * ��������
     * @return array
     */
    function getCatalogs()
    {
        return $this->catalogs;
    }

    /**
     * ������
     * @return array
     */
    function getParts()
    {
        return $this->parts;
    }

    /**
     * ������������� �����
     * @param $data
     */
    function setOptions($data)
    {
        $this->profile_options = $data;
    }

    /**
     * ���������� �����
     * @return null
     */
    function getOptions()
    {
        return $this->profile_options;
    }

    /**
     * ��������� ������� ������ ����������� ���������� (��� �������� �������).
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
     * �������� ������� ���������� �� ����
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
     * �������� �� ID � ��������.
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
     * �������� ������� ���������� �� ������� API
     * @param $arApi - �������� ���������� PROPERTY_API_VALUE
     * @return bool|object - ������ ������ ������� ����������
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
     * ������ ��������� �����������
     * @var bool
     */
    static $suppliers_cache = false;

    /**
     * ������ ��������
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
     * ����� �������
     * @param $code
     * @return mixed
     */
    public function getProfileOption($code)
    {
	    return $this->profile_options[$code];
    }

}
<?php

/**
 * Linemedia Autoportal
 * Main module
 * Return of goods
 *
 * @author  Linemedia
 * @since   05/05/2014
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoReturnGoods {

    
    private static $cache;
    
    /*
     * ��������� ����������� �������
     */
    public static function isEnabled() {

	    if (!array_key_exists('isEnabled', (array) self::$cache)) {
		    self::$cache['isEnabled'] = LinemediaAutoModule::isFunctionEnabled('return_of_goods',  'linemedia.auto');
	    }

		return self::$cache['isEnabled'];
    }

    /*
     * �������� ������� ������� ��� ��������, ������� ��� ��������� �����������
     */
    public static function isStatusEnabled() {

        if(!self::isEnabled()) return false;

        $returnStatus = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_USER_RETURN');
        if(strlen($returnStatus) < 1) return false;
        return true;
    }

    /*
     * ��������� ����������� �������� ��� ��������� ����������
     */
    public static function isSupplierEnabled($supplierId) {

        /*
         * �������� ������� ������� ��� ��������
         */
        if(!self::isStatusEnabled()) return false;

        $arSuppliers = LinemediaAutoSupplier::GetList(array(), array(), false, false, array('ID', 'PROPERTY_RETURNS_BANNED'), 'supplier_id');

        return $arSuppliers[$supplierId]['PROPERTY_RETURNS_BANNED_VALUE'] != 'Y';
    }

    /*
     * ��������� ����������� �������� �������� �������, � ������� ������� ��� ��������
     */
    public static function isClientStatusMatch($status) {

        /*
         * �������� ������� ������� ��� ��������
         */
        if(!self::isStatusEnabled()) return false;

        $arReturnStatuses = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_USER_CAN_RETURN_LIST'));

        if(is_array($arReturnStatuses) && strlen($status) > 0) {
            return in_array($status, $arReturnStatuses);
        }
        return false;
    }

    /*************************************** ��������� ������� ****************************************/

    public static function clearAccount() {

        global $USER;
        CModule::IncludeModule('sale');

//        $res = CSaleUserAccount::UpdateAccount(
//            $USER->GetID(),
//            0,
//            'RUB',
//            'clear'
//        );
        $res = CSaleUserAccount::Withdraw(
            $USER->GetID(),
            10000000,
            'RUB'
        );
        $debug = $res;
        return $res;
    } // LinemediaAutoReturnGoods::clearAccount();
}
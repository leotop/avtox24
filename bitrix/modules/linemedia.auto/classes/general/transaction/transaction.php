<?php


/**
 * Linemedia Autoportal
 * Main module
 * transaction factory class
 * @author Linemedia
 * @since 22/01/2012
 * @link http://auto.linemedia.ru/
 * 
 */
//include lang file
IncludeModuleLangFile(__FILE__);

/**
 * class LinemediaAutoTransaction creates transaction by given status
 */
class LinemediaAutoTransaction
{
    private static $table = 'b_lm_transactions';

    /*
     * Типы транзакций
     */
    const TYPE_CREDIT_LIMIT = 'CREDIT_LIMIT'; // Пополнение кредитного лимита
    const TYPE_GOODS_IN_RESERVE = 'GOODS_IN_RESERVE'; // Товар в резерве
    const TYPE_DEPOSIT_REFUSED_IN_SHIPMENT = 'DEPOSIT_REFUSED_IN_SHIPMENT'; // Отказано в отгрузке
    const TYPE_DEPOSIT_REFUSED_BY_SUPPLIER = 'DEPOSIT_REFUSED_BY_SUPPLIER'; // Отказано поставщиком
    const TYPE_DEPOSIT_RETURN_GOODS = 'DEPOSIT_RETURN_GOODS'; // Возврат товара
    const TYPE_DEPOSIT_FUNDS = 'DEPOSIT_FUNDS'; // Внесение средств

    /* транзакции, которые могут возникать в битриксе */
    const TYPE_ORDER_UNPAY = 'ORDER_UNPAY'; // Отмена оплаченности заказа
    const TYPE_ORDER_PAY = 'ORDER_PAY'; // Оплата заказа
    const TYPE_CC_CHARGE_OFF = 'CC_CHARGE_OFF'; // Внесение денег с пластиковой карты
    const TYPE_OUT_CHARGE_OFF = 'OUT_CHARGE_OFF'; // Внесение денег
    const TYPE_ORDER_CANCEL_PART = 'ORDER_CANCEL_PART'; // Отмена частично оплаченного заказа
    const TYPE_MANUAL = 'MANUAL'; // Ручное изменение счета
    const TYPE_DEL_ACCOUNT = 'DEL_ACCOUNT'; // Удаление счета
    const TYPE_AFFILIATE = 'AFFILIATE'; // Афилиатские выплаты

    const NOTE_DEPOSIT_REFUSED_IN_SHIPMENT = 'NOTE_DEPOSIT_REFUSED_IN_SHIPMENT'; // Отказано в отгрузке
    const NOTE_DEPOSIT_REFUSED_BY_SUPPLIER = 'NOTE_DEPOSIT_REFUSED_BY_SUPPLIER'; // Отказано поставщиком
    const NOTE_DEPOSIT_RETURN_GOODS = 'NOTE_DEPOSIT_RETURN_GOODS'; // Возврат товара
    //const NOTE_CLOSE_RESERVE = 'NOTE_CLOSE_RESERVE'; // резерв закрыт поступлением
    const NOTE_DEFAULT = 'NOTE_DEFAULT';

    /**
     * titles of createRefuseInShipment transaction
     * Отказано в отгрузке
     * @var string
     */
    const ACTION_SHIPMENT = 'createRefuseInShipment';

    /**
     * titles of createRefuseBySupplier transaction
     * Отказано поставщиком
     * @var string
     */
    const ACTION_SUPPLIER = 'createRefuseBySupplier';

    /**
     * titles of createReturnCommodity transaction
     * Возврат товара
     * @var string
     */
    const ACTION_GOODS_RETURN = 'createReturnCommodity';

    /**
     * titles of createDealClosedByCommodity transaction
     * Сделка закрыта по товару
     * @var string
     */
    const ACTION_DEAL_CLOSED = 'createDealClosedByCommodity';

    /**
     * titles of createReserve transaction
     * @var string
     */
    const ACTION_RESERVE = 'createReserve';

    const ACTION_CREDIT_LIMIT = 'completionCreditLimit';

    static $BX_TRANSACTION_TYPES = array(
        TYPE_ORDER_UNPAY,
        TYPE_ORDER_PAY,
        TYPE_CC_CHARGE_OFF,
        TYPE_OUT_CHARGE_OFF,
        TYPE_ORDER_CANCEL_PART,
        TYPE_DEL_ACCOUNT,
        TYPE_AFFILIATE,
    );

	/**
	 * informative message
	 * @var string
	 */
	private $informative_message = '';
	
	/**
	 * @var array
	 */
	private $status_to_action;

    private $pay_from_account = false;

    /**
     * Типы плательщиков, для которых не должны создаватся транзакции
     * @var array
     */
    private $no_transaction_persons = array();

    /**
     * @param array $status_to_action
     */
    public function __construct() {

        $this->status_to_action = (array) unserialize(COption::GetOptionString('linemedia.auto', 'TRANSACTION_STATUSES'));
        $pay_from_account = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PAY_FROM_ACCOUNT_DURING_RESERVE', 'N');
        $this->pay_from_account = ($pay_from_account == 'Y');
        $transaction_switch = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH', 'N');
        $this->no_transaction_persons = (array) unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_NO_TRANSACTION_PERSON', ''));

        LinemediaAutoDebug::add('Transaction PAY_FROM_ACCOUNT = ' . $pay_from_account);
        LinemediaAutoDebug::add('Transaction TRANSACTION_SWITCH = ' . $transaction_switch);

    }

    /**
     * @param $basket_id
     * @param $status
     * @param LinemediaAutoBasket $basket
     * @return bool
     */
    public function createTransaction($basket_id, $status, $basket) {

        global $USER, $DB, $APPLICATION;

        //проверим включена ли настройка создания транзакций
        if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_TRANSACTION_SWITCH', 'N') != 'Y') return true;

        $user_id = $USER->GetID();

        $transaction_type = false;
        $debit = 'Y';

        if(!array_key_exists($status, $this->status_to_action)) {
            return true; // не нужно создавать транзакцию - статуса нет в списке
        } else {
            switch($this->status_to_action[$status]) {
                case self::ACTION_RESERVE : {
                    $transaction_type = self::TYPE_GOODS_IN_RESERVE;
                    $debit = 'N';
                } break;
                case self::ACTION_SHIPMENT : {
                    $transaction_type = self::TYPE_DEPOSIT_REFUSED_IN_SHIPMENT;
                    $debit = 'Y';
                } break;
                case self::ACTION_SUPPLIER : {
                	
                	if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
                		return true;
                    }
                    
                    $transaction_type = self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER;
                    $debit = 'Y';
                } break;
                case self::ACTION_GOODS_RETURN : {
                    $transaction_type = self::TYPE_DEPOSIT_RETURN_GOODS;
                    $debit = 'Y';
                } break;
            }
        }

        $basket_data = $basket->getData($basket_id);
        $currency = $basket_data['CURRENCY'];
        $sum = $basket_data['PRICE'] * $basket_data['QUANTITY'];
        $order_id = $basket_data['ORDER_ID'];

        // check SalePersonType
        if(intval($order_id) > 0) {
            $order_data = CSaleOrder::GetByID($order_id);
            if($order_data['PERSON_TYPE_ID'] && in_array($order_data['PERSON_TYPE_ID'], $this->no_transaction_persons)) {
                return true;
            }
        }

        /*
        * Cоздаём событие
        */
        $break = false;
        $events = GetModuleEvents('linemedia.auto', 'OnBeforeTransactionCreate');
        while ($arEvent = $events->Fetch()) {
            $res = ExecuteModuleEventEx($arEvent, array(&$user_id, &$basket_id, &$status, &$basket, &$debit, &$transaction_type));
            if(!$res) $break = true;
        }

        if($break) {
            if($ex = $APPLICATION->GetException()) {
                $this->informative_message = $ex->GetString();
                LinemediaAutoDebug::add('Transaction: ' . $this->informative_message);
            }
            return false;
        }

        $ex_row = self::getByBasketType($basket_id, $transaction_type);
        if($ex_row) {
            $this->informative_message = GetMessage('ERR_TRANSACTION_EXISTS');//'Такая транзакция уже есть в системе';
            LinemediaAutoDebug::add('Transaction: ' . $this->informative_message);
            return false;
        }

        // оплата товара
        if($transaction_type == self::TYPE_GOODS_IN_RESERVE && $this->pay_from_account) {
            //TODO: что делать если уже оплачен?
            $basket = new LinemediaAutoBasket($user_id);
            $basket->payItem($basket_id);

        } else if($transaction_type == self::TYPE_DEPOSIT_REFUSED_IN_SHIPMENT ||
            $transaction_type == self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER ||
            $transaction_type == self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER) {

            $ex_row = self::getByBasketType($basket_id, self::TYPE_GOODS_IN_RESERVE);

            if(intval($ex_row['REFUSED_BY']) > 0) {
                $this->informative_message = GetMessage('ERR_RESERVE_ALREADY_REFUSED');//'Резерв уже был отменен';
                LinemediaAutoDebug::add('Transaction: ' . $this->informative_message);
                return false;
            }

            if(!$ex_row) {
                $this->informative_message = GetMessage('ERR_RESERVE_NOT_FOUND');//'Нет резерва для возврата';
                LinemediaAutoDebug::add('Transaction: ' . $this->informative_message);
                return false;
            }
        }

        $lm_transaction = array(
            'BASKET_ID' => $basket_id,
            //'PRICE' => $sum,
            //'DONE' => 'N',
            //'PAID' => 'N',
            //'ORDER_ID' => $basket_data['ORDER_ID'],
            //'DATE_TO_DONE' => null,
            //'DATE_TO_PAID' => null,
            //'SUM_PAID' => 0,
            //'CLOSED' => 'N', // закрыта транзакцией (для списаний - могут быть закрыты пополнением.)
            'REFUSED_BY' => null,
            'USER_IP' => $this->getIp(),
            'TYPE' => $transaction_type,
            'MODIFIED_DATE' => null, // дата последнего изменения
            'MODIFIED_BY' => null, //юзер, который внес последнее изменение
            'DELETED' => null,
        );

        $bx_transaction = array(
            'USER_ID' => $user_id, // мог быть установлен в обработчике OnBeforeTransactionCreate
            'TRANSACT_DATE' => ConvertTimestamp(false, 'FULL'),
            'AMOUNT' => $sum,
            'CURRENCY' => $currency,
            'DEBIT' => $debit,
            'ORDER_ID' =>  $order_id,
            'DESCRIPTION' => GetMessage($transaction_type),
            'NOTES' => '',
            'EMPLOYEE_ID' => $USER->GetID(), // всегда текущий
        );

        if(!CModule::IncludeModule('sale')) {
            $this->informative_message = GetMessage('ERR_SALE_MODULE_NOT_INSTALL');//'Модуль интернет магазин не установлен';
            LinemediaAutoDebug::add('Transaction: ' . $this->informative_message);
            return false;
        }

        $bx_id = CSaleUserTransact::Add($bx_transaction);
        if($bx_id) {

            $lm_transaction['ID_BITRIX_TRANSACTION'] = $bx_id;

            if($lm_id = $DB->Add(self::$table, $lm_transaction)) {

                if(
                    $transaction_type == self::TYPE_DEPOSIT_REFUSED_IN_SHIPMENT ||
                    $transaction_type == self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER ||
                    $transaction_type == self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER
                ) {
                    /*
                     * При возврате денег закрываем резерв по заказу.
                     */
                    $this->refuseReserve($basket_id, $lm_id, $transaction_type);
                }
            }
        } else {
            $this->informative_message = GetMessage('ERR_TRANSACTION');//'Ошибка создания транзакции';
            LinemediaAutoDebug::add('Transaction: ' . $this->informative_message);
            return false;
        }

        if($this->pay_from_account) {
            // $user_id может быть переопределен в обработчике
            $account = new LinemediaAutoUserAccount($user_id);
            $account->update(($debit == 'Y' ? 1 : -1) * $sum, $currency);
        }
        return true;
    }

    /**
     * Расширяет битриксовую транзакцию, добавляя наши данные
     * @param $transaction
     */
    public function extendTransaction($transaction) {

        global $DB;
        /*
         * По сути наше дополнение требуется только чтобы уточнить тип транзакции,
         * в частности - кредитный лимит
         */
        $transaction_type = self::TYPE_DEPOSIT_FUNDS;
        if(($_REQUEST['DEBIT'] == 'LIMIT_Y' || $_REQUEST['DEBIT'] == 'LIMIT_N') &&
            floatval($_REQUEST['AMOUNT']) == floatval($transaction['AMOUNT']) &&
            $_REQUEST['CURRENCY'] == $transaction['CURRENCY'] &&
            $transaction['ORDER_ID'] == null) {

            $transaction_type = self::TYPE_CREDIT_LIMIT;

        } else if(in_array($transaction['DESCRIPTION'], self::$BX_TRANSACTION_TYPES)) {
            $transaction_type = $transaction['DESCRIPTION'];
        }

        $lm_transaction = array(
            'ID_BITRIX_TRANSACTION' => $transaction['ID'],
            'BASKET_ID' => null,
            'CLOSED' => 'N', // закрыта транзакцией (для списаний - могут быть закрыты пополнением.)
            'REFUSED_BY' => null,
            'USER_IP' => $this->getIp(),
            'TYPE' => $transaction_type,
            'MODIFIED_DATE' => null, // дата последнего изменения
            'MODIFIED_BY' => null, //юзер, который внес последнее изменение
            'DELETED' => null,
        );

        $DB->Add(self::$table, $lm_transaction);
    }

    private function refuseReserve($basket_id, $transaction_id, $transaction_type) {

        global $DB, $USER;

        $transaction = self::getByBasketType($basket_id, self::TYPE_GOODS_IN_RESERVE);

        if($transaction) {

            $fields = array(
                'REFUSED_BY' => $transaction_id,
                'CLOSED' => 'Y',
                'MODIFIED_DATE' => ConvertTimestamp(false, 'FULL'),
                'MODIFIED_BY' => $USER->GetID(),
            );

            $update = $DB->PrepareUpdate('b_lm_transactions', $fields);

            if(!empty($update)) {

                $res = $DB->Query(
                    "UPDATE b_lm_transactions SET " . $update . " WHERE ID=" . $transaction['ID'],
                    false,
                    "File: " . __FILE__ . "<br>Line: " . __LINE__
                );
                // add log
                if($res) {

                    $params = array(
                        'DATE' => ConvertTimestamp(false, 'FULL'),
                        'USER_ID' => $USER->GetID(),
                        'IP' => $this->getIp(),
                    );

                    $note_type = self::NOTE_DEFAULT;
                    switch($transaction_type) {
                        case self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER: {
                            $note_type = self::NOTE_DEPOSIT_REFUSED_BY_SUPPLIER;
                        } break;
                        case self::TYPE_DEPOSIT_REFUSED_IN_SHIPMENT: {
                            $note_type = self::NOTE_DEPOSIT_REFUSED_IN_SHIPMENT;
                        } break;
                        case self::TYPE_DEPOSIT_RETURN_GOODS: {
                            $note_type = self::NOTE_DEPOSIT_RETURN_GOODS;
                        } break;
                    }

                    $notes = $this->getNote($note_type, $params, $transaction['NOTES']);

                    $fields = array(
                        'NOTES' => $notes,
                    );

                    $update = $DB->PrepareUpdate('b_sale_user_transact', $fields);

                    if(!empty($update)) {

                        $res = $DB->Query(
                            "UPDATE b_sale_user_transact SET " . $update . " WHERE ID=" . $transaction['ID_BITRIX_TRANSACTION'],
                            false,
                            "File: " . __FILE__ . "<br>Line: " . __LINE__
                        );
                    }

                } // if($res)
            } // if(!empty($update))
        }
    }

    public function convertTypes() {

        global $DB;

        $where = "TYPE IS NULL";
        $sort = "TRANSACT_DATE ASC";
        $list = self::getList($sort, $where);
        $rows = array();

        while($row = $list->Fetch()) {

            $type = false;

            if($row['DESCRIPTION'] == GetMessage(self::TYPE_DEPOSIT_RETURN_GOODS)) {
                $type = self::TYPE_DEPOSIT_RETURN_GOODS;
            } else if($row['DESCRIPTION'] == GetMessage(self::TYPE_DEPOSIT_REFUSED_IN_SHIPMENT)) {
                $type = self::TYPE_DEPOSIT_REFUSED_IN_SHIPMENT;
            } else if($row['DESCRIPTION'] == GetMessage(self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER)) {
                $type = self::TYPE_DEPOSIT_REFUSED_BY_SUPPLIER;
            } else if($row['DESCRIPTION'] == GetMessage(self::TYPE_GOODS_IN_RESERVE)) {
                $type = self::TYPE_GOODS_IN_RESERVE;
            }
            if($type) {
                $fields = array(
                    'TYPE' => $type,
                );
                $update = $DB->PrepareUpdate('b_lm_transactions', $fields);
                $res = $DB->Query(
                    "UPDATE b_lm_transactions SET " . $update . " WHERE ID=" . $row['ID'],
                    false,
                    "File: " . __FILE__ . "<br>Line: " . __LINE__
                );
            }
        }
    }

    /**
     * return errors
     * @return string
     */
    public function getInformativeMessage() {
		return $this->informative_message;
	}

    private function getNote($operation_type, $params, $note = '') {

        $str = GetMessage($operation_type);

        foreach($params as $key => $value) {
            $str = str_replace("#" . $key . "#", $value, $str);
        }

        if(!empty($note)) $str = $note . "\r\n" . $str;

        return $str;
    }

    private function getIp() {

        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function getList($sort = array(), $where = array(), $limit  = '') {

        global $DB;
        $sql = "SELECT BT.USER_ID, BT.TIMESTAMP_X, BT.TRANSACT_DATE, BT.AMOUNT, BT.CURRENCY, BT.DEBIT, BT.ORDER_ID, BT.DESCRIPTION, BT.NOTES, BT.EMPLOYEE_ID, LT.*
            FROM b_lm_transactions LT
            LEFT JOIN b_sale_user_transact BT ON BT.ID = LT.ID_BITRIX_TRANSACTION";

        if(!empty($where)) {
            $sql .= " WHERE " . $where;
        } else {
            $sql .= " WHERE 1=1";
        }

        if(!empty($sort)) {
            $sql .= " ORDER BY " . $sort;
        }

        if(!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }

        return $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );
    }

    public static function getByBxTransaction($bx_transaction_id) {

        global $DB;

        if(intval($bx_transaction_id) < 1) return false;

        $sql = "SELECT * FROM b_lm_transactions WHERE ID_BITRIX_TRANSACTION=" . intval($bx_transaction_id);

        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );

        if($row = $res->Fetch()) {
            return $row;
        }
        return false;
    }

    public static function checkExistsLMTransaction($bx_transaction_id) {

        global $DB;

        $sql = "SELECT ID FROM b_lm_transactions WHERE ID_BITRIX_TRANSACTION=" . intval($bx_transaction_id);

        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );

        if(is_object($res) && $row = $res->Fetch()) {
            return $row['ID'];
        }
        return false;
    }

    public static function getLastBxTransaction($user_id) {

        global $DB;

        $sql = "SELECT * FROM b_sale_user_transact WHERE USER_ID=" . intval($user_id) . " AND
            NOT EXISTS (SELECT * FROM b_lm_transactions WHERE ID_BITRIX_TRANSACTION=b_sale_user_transact.ID)
            ORDER BY TRANSACT_DATE DESC";

        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );

        $rows = array();
        while($row = $res->Fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function clearBxTransactionNotes($bx_transaction_id) {

        global $DB;

        $sql = "UPDATE b_sale_user_transact SET NOTES='' WHERE ID=" . intval($bx_transaction_id);
        return $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );
    }

    public static function getById($id) {

        $where = "LT.ID=" . intval($id);
        $sort = "TRANSACT_DATE ASC";
        $list = self::getList($sort, $where);

        if(is_object($list) && $row = $list->Fetch()) {
            return $row;
        }
        return null;
    }

    public static function getUserTransaction($user_id, $filter = false, $sort = 'ASC') {

        $where = array();
        $where[] = "USER_ID=" . $user_id;
        $where[] = "(DELETED IS NULL OR DELETED != 'Y')";
        if(is_array($filter)) {
            foreach($filter as $key => $value) {
                switch($key) {
                    case 'TYPE' : {
                        if(is_array($value)) {
                            $where[] = "TYPE IN ('" . join("', '", $value) . "')";
                        } else {
                            $where[] = "TYPE='" . $value . "'";
                        }
                    } break;
                    case 'ID' : {
                        if(is_array($value)) {
                            $value = array_map('intval', $value);
                            $where[] = "LT.ID IN (" . join(", ", $value) . ")";
                        } else {
                            $where[] = "LT.ID=" . intval($value);
                        }
                    } break;
                    case 'ORDER_ID' : {
                        if(is_array($value)) {
                            $value = array_map('intval', $value);
                            $where[] = "ORDER_ID IN (" . join(", ", $value) . ")";
                        } else {
                            $where[] = "ORDER_ID=" . intval($value);
                        }
                    } break;
                    case '>=TRANSACT_DATE' : {
                        $ts = MakeTimeStamp($value);
                        if($ts) {
                            $where[] = "TRANSACT_DATE >= '" . FormatDate("Y-m-d H:i:s", $ts) . "'";
                        }
                    } break;
                    case '<=TRANSACT_DATE' : {
                        $ts = MakeTimeStamp($value);
                        if($ts) {
                            $where[] = "TRANSACT_DATE <= '" . FormatDate("Y-m-d H:i:s", $ts) . "'";
                        }
                    } break;
                }
            }
            $where = join(" AND ", $where);
        }
        $sort = "TRANSACT_DATE " . $sort;
        $list = self::getList($sort, $where);
        $rows = array();

        while($row = $list->Fetch()) {
            $rows[$row['ID']] = $row;
        }

        return $rows;
    }

    public static function getByBasketType($basket_id, $type) {

        $where = "LT.BASKET_ID=" . $basket_id . " AND
         LT.TYPE = '" . $type . "' AND
         (LT.DELETED IS NULL OR LT.DELETED = 'N')";

        $sort = "TRANSACT_DATE ASC";

        $limit =  "1";

        $list = self::getList($sort, $where, $limit);

        if(is_object($list) && $row = $list->Fetch()) {
            return $row;
        }
        return null;
    }

    /**
     * @param $user_id
     * @return int|array - возвращает транзакцию, либо сумму. В случае суммы - все резервы закрыты поступлением.
     */
    public static function getOldestReserve($user_id) {

        $where = "USER_ID=" . $user_id . " AND
         (LT.TYPE = '" . self::TYPE_GOODS_IN_RESERVE . "' OR LT.TYPE = '" . self::TYPE_DEPOSIT_FUNDS . "') AND
         (LT.DELETED IS NULL OR LT.DELETED = 'N') AND REFUSED_BY IS NULL";

        $sort = "TRANSACT_DATE ASC";


        $list = self::getList($sort, $where);

        $reserve = array();
        $funds = 0;
        while($row = $list->Fetch()) {
            if($row['TYPE'] == self::TYPE_GOODS_IN_RESERVE) {
                $reserve[] = $row;
            } else {
                $funds += ($row['DEBIT'] == 'Y' ? 1 : -1) * $row['AMOUNT'];
            }
        }
        /*
         * До тех пор пока стоимость заказов покрывается суммой внесения - считаем заказы закрытыми
         */
        foreach($reserve as $reserv) {

            if($reserv['AMOUNT'] > $funds) {
                return $reserv;
            } else {
                $funds -= $reserv['AMOUNT'];
            }
        }

        return $funds;
    }

    public static function getCreditLimit($user_id, $format = true) {

        $type = self::TYPE_CREDIT_LIMIT;
        $list = self::getUserTransaction($user_id, array('TYPE' => $type));
        $limit = array();
        foreach($list as $row) {
            $limit[$row['CURRENCY']] += ($row['DEBIT'] == 'Y' ? 1 : -1) * $row['AMOUNT'];
        }
        if($format) {
            foreach($limit as $currency => $value) {
                $limit[$currency] = SaleFormatCurrency($value, $currency);
            }
        }

        return $limit;
    }
}

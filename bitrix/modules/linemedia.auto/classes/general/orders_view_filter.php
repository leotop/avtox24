<?php

/**
 * Linemedia Autoportal
 * Main module
 * Basket management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/*
 * Класс для фильтрации заказов.
 */
class LinemediaAutoOrdersViewFilter
{
    protected $is_filtered  = false;
    protected $where = array();

    protected $user_ids = array();

    public function __construct()
    {

    }

    public function isFiltered()
    {
        return $this->is_filtered;
    }

    public function setIDs($ids) {

        if(is_array($ids) && count($ids) > 0) {
            $this->where['IDS'] = "ORDER_ID IN (". join(', ', $ids) .")";
        }

    }

    public function setNotIDs($ids) {

        if(is_array($ids) && count($ids) > 0) {
            $this->where['IDS_NOT'] = "ID NOT IN (". join(', ', $ids) .")";
        }
    }

    public function setIdFrom($id) {

        if(intval($id) > 0) {
            $this->where['>ORDER_ID'] = "ORDER_ID >= " . $id;
        }
    }

    public function setIdTo($id) {

        if(intval($id) > 0) {
            $this->where['<ORDER_ID'] = "ORDER_ID <= " . $id;
        }
    }

    public function setName($name) {

        if(strlen($name) > 0) {
            $this->where['NAME'] = "NAME LIKE '%" . $name . "%'";
        }
    }

    public function setCustomField($customField) {

    }

    public function setProperty($field, $value) {

        if(strlen($field) > 0 && !empty($value)) {
            $this->where[$field] = $field . " = '" . $value . "'";
        }
    }

    public function setDateFrom($date_from) {

        if($ts = MakeTimestamp($date_from)) {
            $str_date = FormatDate("Y-m-d", $ts);
            $this->where['>ORDER_CREATED'] = "ORDER_CREATED >= '" . $str_date . "'";
        }
    }

    public function setDateTo($date_to) {

        if($ts = MakeTimestamp($date_to)) {
            $str_date = FormatDate("Y-m-d", $ts);
            $this->where['<ORDER_CREATED'] = "ORDER_CREATED <= '" . $str_date . "'";
        }
    }

    public function setDateUpdateFrom($date_from) {

        if($ts = MakeTimestamp($date_from)) {
            $str_date = FormatDate("Y-m-d", $ts);
            $this->where['>DATE_UPDATE'] = "DATE_UPDATE >= '" . $str_date . "'";
        }
    }

    public function setDateUpdateTo($date_to) {

        if($ts = MakeTimestamp($date_to)) {
            $str_date = FormatDate("Y-m-d", $ts);
            $this->where['<DATE_UPDATE'] = "DATE_UPDATE <= '" . $str_date . "'";
        }
    }

    public function setOrderPayed($payed) {

        $this->where['ORDER_PAYED'] = "ORDER_PAYED = '" . $payed . "'";
    }

    public function setOrderCanceled($canceled) {

        $this->where['ORDER_CANCELED'] = "ORDER_CANCELED = '" . $canceled . "'";
    }

    public function setPayed($payed) {

        $this->where['PAYED'] = "PAYED = '" . $payed . "'";
    }

    public function setCanceled($canceled) {

        $this->where['CANCELED'] = "CANCELED = '" . $canceled . "'";
    }

    public function setStatus($statuses) {

        $this->where['STATUS'] = "STATUS IN ('". join("', '", $statuses) ."')";
    }

    public function setNStatus($statuses) {

        if(is_array($statuses) && count($statuses) > 0) {
            $this->where['STATUS_NOT'] = "STATUS NOT IN ('". join("', '", $statuses) ."')";
        }
    }

    public function setPersonType($persons) {

        if(is_array($persons) && count($persons) > 0) {

            foreach($persons as $key => $value) {
                if(empty($value)) unset($persons[$key]);
            }
            if(count($persons) > 0) {
                $this->where['PERSON_TYPE'] = "PERSON_TYPE IN ('". join("', '", $persons) ."')";
            }
        }
    }

    public function setPaySystem($paysytems) {

        if(is_array($paysytems) && count($paysytems) > 0) {

            foreach($paysytems as $key => $value) {
                if(empty($value)) unset($paysytems[$key]);
            }
            if(count($paysytems) > 0) {
                $this->where['PAYSYSTEM'] = "PAYSYSTEM IN ('". join("', '", $paysytems) ."')";
            }
        }
    }

    public function setDelivery($deliveries) {

        if(is_array($deliveries) && count($deliveries) > 0) {

            foreach($deliveries as $key => $value) {
                if(empty($value)) unset($deliveries[$key]);
            }
            if(count($deliveries) > 0) {
                $this->where['DELIVERY'] = "DELIVERY IN ('". join("', '", $deliveries) ."')";
            }
        }
    }

    public function setArticle($article) {

        if(!empty($article)) {
            $this->where['ARTICLE'] = "ARTICLE = '" . $article . "'";
        }
    }

    public function setSupplier($supplier) {

        if(is_array($supplier) && count($supplier) > 0) {
            $this->where['SUPPLIER'] = "SUPPLIER IN ('". join("', '", $supplier) ."')";
        }
    }

    public function setBrandTitle($brand) {

        if(!empty($brand)) {
            $this->where['BRAND'] = "BRAND LIKE '" . $brand . "'";
        }
    }

    public function setOrderId($order_id) {

        if(intval($order_id) > 0) {
            $this->where['ORDER_ID'] = "ORDER_ID = '" . $order_id . "'";
        }
    }

    public function setOrderIDs($order_ids) {

        if(is_array($order_ids) && count($order_ids) > 0) {
            $this->where['ORDER_ID'] = "ORDER_ID IN ('". join("', '", $order_ids) ."')";
        }
    }

    public function setUserId($user_id) {

        if(empty($user_id)) {
            return;
        }
        /**
        вызов функции возможен несколько раз с разными данными.
        например, мы фильтруем по пользователю, но мы менеджер.
        поэтому на фильтр по пользователю надо наложить ещё фильтр по филиалу. логично делать это через пересечение прежних данных
        сего фильтра и новых.
         */
        if (is_array($user_id)) {
            $user_id = array_map('intval', $user_id);
        } else {
            $user_id = array(0=>(int) $user_id);
        }
        $this->user_ids = array_merge($this->user_ids, $user_id);

        $this->where['USER_ID'] = "USER_ID IN ('". join("', '", $this->user_ids) ."')";
    }

    public function setUserLogin($login) {

        if(!empty($login)) {
            $this->where['LOGIN'] = "LOGIN = '" . $login . "'";
        }
    }

    public function setUserEmail($email) {

        if(!empty($email)) {
            $this->where['EMAIL'] = "EMAIL = '" . $email . "'";
        }
    }

    public function setUniversal($buyer) {

        if(!empty($buyer)) {

            if(is_numeric($buyer)) {
                $this->where['UNIVERSAL'] = "USER_ID = '" . intval($buyer) . "'";
            } else {
                $this->where['UNIVERSAL'] = "EMAIL LIKE '" . $buyer . "%' OR LOGIN LIKE '" . $buyer . "%'";
            }

        }
    }

    public function setUniversalArr($arBuyer) {

    }

    public function setOrderProperty($code, $value) {

        if(is_array($value)) {
            $this->where[$code] = $code . " IN ('". join("', '", $value) ."')";
        } else {
            $this->where[$code] = $code . " = '" . $value . "'";
        }
    }

    public function setAdditionalFilter($filters) {

    }

    public function filter() {

        $str_where = '1';

        if(count($this->where) > 0) {

            $str_where = join(' AND ', $this->where);
        }

        return $str_where;
    }


}
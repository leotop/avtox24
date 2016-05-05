<?php

/**
 * Lang file
 * @author  Linemedia
 * @since   25/02/2014
 * @link    http://auto.linemedia.ru/
 */

$MESS['GOODS_IN_RESERVE']  = 'Товар в резерве';
$MESS['DEPOSIT_REFUSED_IN_SHIPMENT']  = 'Отказано в отгрузке';
$MESS['DEPOSIT_REFUSED_BY_SUPPLIER']  = 'Отказано поставщиком';
$MESS['DEPOSIT_RETURN_GOODS']  = 'Возврат товара';
$MESS['DEAL_CLOSED_BY_GOODS']  = 'Сделка закрыта по товару';

$MESS['CLOSED_BY_MONEY'] = 'Товар оплачен, но не отгружен';
$MESS['CLOSED_BY_GOODS'] = 'Товар отгружен, но не оплачен';

/* транзакции, которые могут возникать в битриксе */
$MESS['ORDER_UNPAY']  = 'Отмена оплаченности заказа';
$MESS['ORDER_PAY']  = 'Оплата заказа';
$MESS['CC_CHARGE_OFF']  = 'Внесение денег с пластиковой карты';
$MESS['OUT_CHARGE_OFF']  = 'Внесение денег';
$MESS['ORDER_CANCEL_PART']  = 'Отмена частично оплаченного заказа';
$MESS['MANUAL']  = 'Ручное изменение счета';
$MESS['DEL_ACCOUNT']  = 'Удаление счета';
$MESS['AFFILIATE']  = 'Афилиатские выплаты';

/*
 * Операции, отражаемые в логе изменений
 */
$MESS['NOTE_DEPOSIT_REFUSED_IN_SHIPMENT'] = '#DATE#: Отказано в отгрузке USER_ID=#USER_ID# IP=#IP#';
$MESS['NOTE_DEPOSIT_REFUSED_BY_SUPPLIER'] = '#DATE#: Отказано поставщиком USER_ID=#USER_ID# IP=#IP#';
$MESS['NOTE_DEPOSIT_RETURN_GOODS'] = '#DATE#: Возврат товара USER_ID=#USER_ID# IP=#IP#';

$MESS['NOTE_CLOSE_RESERVE'] = '#DATE#: Резерв закрыт поступлением USER_ID=#USER_ID# IP=#IP#';

$MESS['NOTE_DEFAULT'] = '#DATE#: USER_ID=#USER_ID# IP=#IP#';

$MESS['ERR_BRANCH_NOT_DEFINED'] = 'Невозможно определить филиал владельца заказа';
$MESS['ERR_TRANSACTION_EXISTS'] = 'Такая транзакция уже есть в системе';
$MESS['ERR_RESERVE_ALREADY_REFUSED'] = 'Резерв уже был отменен';
$MESS['ERR_SALE_MODULE_NOT_INSTALL'] = 'Модуль интернет магазин не установлен';
$MESS['ERR_TRANSACTION'] = 'Ошибка создания транзакции';
$MESS['ERR_RESERVE_NOT_FOUND'] = 'Нет резерва для осуществления возврата';




<?php

$arTypes = array(
    'LM_AUTO_USER_MSG_ITEM_ORDER' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: Сообщение менеджеру заказа',
            'DESCRIPTION'   => ""
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: Сообщение менеджеру заказа',
            'DESCRIPTION'   => ""
        ),
    ),

    'LM_AUTO_SALE_STATUS_CHANGED' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
            'NAME'          => 'LINEMEDIA.AUTO: Изменение статуса товара',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#ORDER_STATUS# - статус заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
            'NAME'          => 'LINEMEDIA.AUTO: Изменение статуса товара',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#ORDER_STATUS# - статус заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж"
        ),
    ),
    
    'LM_AUTO_SALE_ALLOW_PAYMENT' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
            'NAME'          => 'LINEMEDIA.AUTO: Возможность оплаты заказа',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ORDER_LIST# - Состав заказа\n#PRICE# - Сумма заказа\n#ORDER_USER# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
            'NAME'          => 'LINEMEDIA.AUTO: Возможность оплаты заказа',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ORDER_LIST# - Состав заказа\n#PRICE# - Сумма заказа\n#ORDER_USER# - Заказчик"
        ),
    ),
    
    'LM_AUTO_ORDER_ITEM_CANCEL' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
            'NAME'          => 'LINEMEDIA.AUTO: Отмена позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ITEM_NAME# - название позиции\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#QUANTITY# - Кол-во позиции\n#NEW_PRICE# - Новая сумма заказа\n#USER_NAME# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
            'NAME'          => 'LINEMEDIA.AUTO: Отмена позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ITEM_NAME# - название позиции\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#QUANTITY# - Кол-во позиции\n#NEW_PRICE# - Новая сумма заказа\n#USER_NAME# - Заказчик"
        ),
    ),
    
    'LM_AUTO_SALE_ORDER_ITEM_PAID' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
            'NAME'          => 'LINEMEDIA.AUTO: Позиция в заказе оплачена',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
            'NAME'          => 'LINEMEDIA.AUTO: Позиция в заказе оплачена',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
    ),
    
    'LM_AUTO_SALE_ORDER_ITEM_DELIVERY' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
            'NAME'          => 'LINEMEDIA.AUTO: Разрешена доставка позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
            'NAME'          => 'LINEMEDIA.AUTO: Разрешена доставка позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
    )
    
);

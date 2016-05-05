<?php

$arTemplates = array(
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Вопрос по позиции заказа',
        'MESSAGE'       => "Вопрос по позиции заказа сайта #SITE_NAME#\n------------------------------------------\nУ пользователя [#USER_ID#] #USER_NAME# возник вопрос\nпо позиции [#ORDER_ITEM_ID#] #ORDER_ITEM_NAME#\nв заказе №#ORDER_ID#\n\nВопрос пользователя:\n#MSG#;"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Изменение статуса заказа N#ORDER_ID#',
        'MESSAGE'       => "Информационное сообщение сайта #SITE_NAME#\n------------------------------------------\n\nСтатус товара \"#ITEM_NAME#\"\nв заказе номер №#ORDER_ID# от #ORDER_DATE# изменен.\n\nНовый статус товара: #ITEM_STATUS#\n\nАртикул: #ITEM_ART#\nБренд: #ITEM_BRAND#\nЦена: #ITEM_PRICE#\nКоличество: #ITEM_QUANTITY#\nСумма: #ITEM_AMOUNT#\n\n#SITE_NAME#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Оплата заказа N#ORDER_ID#',
        'MESSAGE'       => "Вы можете оплатить свой заказ №#ORDER_ID# перейдя по ссылке\nhttp://#SERVER_NAME#/auto/personal/order/make/?ORDER_ID=#ORDER_ID#\n\nCостав заказа:\n#ORDER_LIST#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Отмена позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# в количестве #QUANTITY# отменена в заказе №#ORDER_ID#\nНовая сумма заказа: #NEW_PRICE#руб"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Отмена позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# в количестве #QUANTITY# отменена в заказе №#ORDER_ID#\nНовая сумма заказа: #NEW_PRICE#руб"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Оплата позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# (#ITEM_ART#) в количестве #ITEM_QUANTITY#шт оплачена."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Доставка позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# (#ITEM_ART#) в количестве #ITEM_QUANTITY#шт разрешена к доставке."
    ),
);


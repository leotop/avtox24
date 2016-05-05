<?php

$arTypes = array(
    'LM_AUTO_USER_MSG_ITEM_ORDER' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: ��������� ��������� ������',
            'DESCRIPTION'   => ""
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: ��������� ��������� ������',
            'DESCRIPTION'   => ""
        ),
    ),

    'LM_AUTO_SALE_STATUS_CHANGED' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
            'NAME'          => 'LINEMEDIA.AUTO: ��������� ������� ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#ORDER_STATUS# - ������ ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
            'NAME'          => 'LINEMEDIA.AUTO: ��������� ������� ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#ORDER_STATUS# - ������ ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������"
        ),
    ),
    
    'LM_AUTO_SALE_ALLOW_PAYMENT' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
            'NAME'          => 'LINEMEDIA.AUTO: ����������� ������ ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#ORDER_LIST# - ������ ������\n#PRICE# - ����� ������\n#ORDER_USER# - ��������"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
            'NAME'          => 'LINEMEDIA.AUTO: ����������� ������ ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#ORDER_LIST# - ������ ������\n#PRICE# - ����� ������\n#ORDER_USER# - ��������"
        ),
    ),
    
    'LM_AUTO_ORDER_ITEM_CANCEL' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
            'NAME'          => 'LINEMEDIA.AUTO: ������ ������� � ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ITEM_NAME# - �������� �������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#QUANTITY# - ���-�� �������\n#NEW_PRICE# - ����� ����� ������\n#USER_NAME# - ��������"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
            'NAME'          => 'LINEMEDIA.AUTO: ������ ������� � ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ITEM_NAME# - �������� �������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#QUANTITY# - ���-�� �������\n#NEW_PRICE# - ����� ����� ������\n#USER_NAME# - ��������"
        ),
    ),
    
    'LM_AUTO_SALE_ORDER_ITEM_PAID' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
            'NAME'          => 'LINEMEDIA.AUTO: ������� � ������ ��������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#ITEM_NAME# - �������� �������\n#ITEM_ART# - ������� �������\n#ITEM_QUANTITY# - ���-�� �������\n#ORDER_USER# - ��������"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
            'NAME'          => 'LINEMEDIA.AUTO: ������� � ������ ��������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#ITEM_NAME# - �������� �������\n#ITEM_ART# - ������� �������\n#ITEM_QUANTITY# - ���-�� �������\n#ORDER_USER# - ��������"
        ),
    ),
    
    'LM_AUTO_SALE_ORDER_ITEM_DELIVERY' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
            'NAME'          => 'LINEMEDIA.AUTO: ��������� �������� ������� � ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#ITEM_NAME# - �������� �������\n#ITEM_ART# - ������� �������\n#ITEM_QUANTITY# - ���-�� �������\n#ORDER_USER# - ��������"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
            'NAME'          => 'LINEMEDIA.AUTO: ��������� �������� ������� � ������',
            'DESCRIPTION'   => "#ORDER_ID# - ��� ������\n#ORDER_DATE# - ���� ������\n#EMAIL# - E-Mail ������������\n#SALE_EMAIL# - E-Mail ������ ������\n#ITEM_NAME# - �������� �������\n#ITEM_ART# - ������� �������\n#ITEM_QUANTITY# - ���-�� �������\n#ORDER_USER# - ��������"
        ),
    )
    
);

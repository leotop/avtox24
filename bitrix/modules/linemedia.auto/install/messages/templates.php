<?php

$arTemplates = array(
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: ������ �� ������� ������',
        'MESSAGE'       => "������ �� ������� ������ ����� #SITE_NAME#\n------------------------------------------\n� ������������ [#USER_ID#] #USER_NAME# ������ ������\n�� ������� [#ORDER_ITEM_ID#] #ORDER_ITEM_NAME#\n� ������ �#ORDER_ID#\n\n������ ������������:\n#MSG#;"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: ��������� ������� ������ N#ORDER_ID#',
        'MESSAGE'       => "�������������� ��������� ����� #SITE_NAME#\n------------------------------------------\n\n������ ������ \"#ITEM_NAME#\"\n� ������ ����� �#ORDER_ID# �� #ORDER_DATE# �������.\n\n����� ������ ������: #ITEM_STATUS#\n\n�������: #ITEM_ART#\n�����: #ITEM_BRAND#\n����: #ITEM_PRICE#\n����������: #ITEM_QUANTITY#\n�����: #ITEM_AMOUNT#\n\n#SITE_NAME#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: ������ ������ N#ORDER_ID#',
        'MESSAGE'       => "�� ������ �������� ���� ����� �#ORDER_ID# ������� �� ������\nhttp://#SERVER_NAME#/auto/personal/order/make/?ORDER_ID=#ORDER_ID#\n\nC����� ������:\n#ORDER_LIST#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: ������ ������� ������ N#ORDER_ID#',
        'MESSAGE'       => "������� #ITEM_NAME# � ���������� #QUANTITY# �������� � ������ �#ORDER_ID#\n����� ����� ������: #NEW_PRICE#���"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: ������ ������� ������ N#ORDER_ID#',
        'MESSAGE'       => "������� #ITEM_NAME# � ���������� #QUANTITY# �������� � ������ �#ORDER_ID#\n����� ����� ������: #NEW_PRICE#���"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: ������ ������� ������ N#ORDER_ID#',
        'MESSAGE'       => "������� #ITEM_NAME# (#ITEM_ART#) � ���������� #ITEM_QUANTITY#�� ��������."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: �������� ������� ������ N#ORDER_ID#',
        'MESSAGE'       => "������� #ITEM_NAME# (#ITEM_ART#) � ���������� #ITEM_QUANTITY#�� ��������� � ��������."
    ),
);


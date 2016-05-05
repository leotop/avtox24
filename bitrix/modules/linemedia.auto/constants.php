<?php
       
/*
 * http://webservicepilot.tecdoc.net/pegasus-2-0/doc/InterfaceCatService.PDF
 * page 111
 * 11.1 (Get articles by brandno and generic articleid)
 * "numberType" description
 */
define('LM_AUTO_MAIN_ARTICLE_TYPE_ARTICLE',     0);
define('LM_AUTO_MAIN_ARTICLE_TYPE_OE',          1);
define('LM_AUTO_MAIN_ARTICLE_TYPE_TRADE',       2);
define('LM_AUTO_MAIN_ARTICLE_TYPE_COMPARABLE',  3);
define('LM_AUTO_MAIN_ARTICLE_TYPE_REPLACEMENT', 4);
define('LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED',    5);
define('LM_AUTO_MAIN_ARTICLE_TYPE_EAN',         6);
define('LM_AUTO_MAIN_ARTICLE_TYPE_ANY',         10);


define('LM_AUTO_DEBUG_NOTICE', 1);
define('LM_AUTO_DEBUG_WARNING', 10);
define('LM_AUTO_DEBUG_USER_ERROR', 15);
define('LM_AUTO_DEBUG_ERROR', 20);
define('LM_AUTO_DEBUG_CRITICAL', 30);

/*��������� ������� ������� ��� ����������� �������� �������� */

// ������ �������
define('LM_AUTO_MAIN_ACCESS_DENIED', 'D');
// �������� ������ �������
define('LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH', 'F');
// �������� � �������������� ������ �������
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH', 'P');
// �������� � �������������� �����
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN', 'O');
// �������� � �������������� ������� ����� ��������
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS', 'Q');
// ������������� �������� ��� ��������� �����������
define('LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS', 'U');
// �������������� �������� ��� ��������� �����������
// �������� � �������������� ��������� �����������
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS', 'W');
// �������� ����
define('LM_AUTO_MAIN_ACCESS_READ', 'R');
// ������
define('LM_AUTO_MAIN_ACCESS_READ_WRITE', 'W');
// ������ ������
define('LM_AUTO_MAIN_ACCESS_FULL', 'X');
//VIN access to own customers
define('LM_AUTO_MAIN_ACCESS_VIN_CLIENTS', 'C');
//VIN access to own branch
define('LM_AUTO_MAIN_ACCESS_VIN_BRANCH', 'B');

//finance access to own customers
define('LM_AUTO_MAIN_ACCESS_FINANCE_CLIENTS', 'C');
//finance access to own branch
define('LM_AUTO_MAIN_ACCESS_FINANCE_BRANCH', 'B');


// ������ ������� � ����������, ������� ����������� ��� ��������� �� ������� ��������, � ���������������
// ��� ������������� ������� � ���������� ������ �������.
// ������ ������ ������ �������
define('LM_AUTO_MAIN_BRANCH_IBLOCK_READ', 'L');
// ������ �������������� ������ �������
define('LM_AUTO_MAIN_BRANCH_IBLOCK_READ_WRITE', 'M');

// ��������� �������� ������� �������

define('LM_AUTO_ACCESS_BINDING_ORDERS', 'linemedia_auto_order'); // ������
define('LM_AUTO_ACCESS_BINDING_STATUSES', 'linemedia_auto_goods_statuses'); // ������� �������
define('LM_AUTO_ACCESS_BINDING_SUPPLIERS', 'linemedia_auto_providers'); // ����������
define('LM_AUTO_ACCESS_BINDING_PRICES_IMPORT', 'linemedia_auto_price_import'); // ������ �����-������
define('LM_AUTO_ACCESS_BINDING_WORDFORMS', 'linemedia_auto_word_forms'); // ����������
define('LM_AUTO_ACCESS_BINDING_PRODUCTS', 'linemedia_auto_spare'); // ������ ���������
define('LM_AUTO_ACCESS_BINDING_STATISTICS', 'linemedia_auto_search_stat'); // ���������� ������
define('LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS', 'linemedia_auto_user_fields'); // ���������������� ����
define('LM_AUTO_ACCESS_BINDING_PRICES', 'linemedia_auto_price_ap'); // ���������������
define('LM_AUTO_ACCESS_BINDING_VIN', 'linemedia_auto_vin'); // ���������������
define('LM_AUTO_ACCESS_BINDING_FINANCE', 'linemedia_auto_finance'); // ���������� � ����� �������������

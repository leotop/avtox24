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

/*Константы уровней доступа для расширенных настроек доступов */

// Доступ зпрещен
define('LM_AUTO_MAIN_ACCESS_DENIED', 'D');
// Просмотр своего филиала
define('LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH', 'F');
// Просмотр и редактирование своего филиала
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH', 'P');
// Просмотр и редактирование своих
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN', 'O');
// Просмотр и редактирование заказов своих клиентов
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS', 'Q');
// Использование шаблонов для доступных поставщиков
define('LM_AUTO_MAIN_ACCESS_READ_SUPPLIERS', 'U');
// Редактирование шаблонов для доступных поставщиков
// Просмотр и редактирование доступных поставщиков
define('LM_AUTO_MAIN_ACCESS_READ_WRITE_SUPPLIERS', 'W');
// Просмотр всех
define('LM_AUTO_MAIN_ACCESS_READ', 'R');
// Запись
define('LM_AUTO_MAIN_ACCESS_READ_WRITE', 'W');
// Полный доступ
define('LM_AUTO_MAIN_ACCESS_FULL', 'X');
//VIN access to own customers
define('LM_AUTO_MAIN_ACCESS_VIN_CLIENTS', 'C');
//VIN access to own branch
define('LM_AUTO_MAIN_ACCESS_VIN_BRANCH', 'B');

//finance access to own customers
define('LM_AUTO_MAIN_ACCESS_FINANCE_CLIENTS', 'C');
//finance access to own branch
define('LM_AUTO_MAIN_ACCESS_FINANCE_BRANCH', 'B');


// Уровни доступа к инфоблокам, которые применяются при установке их модулем филиалов, и предназначенные
// для разграничения доступа к инфоблокам своего филиала.
// Доступ чтение своего филиала
define('LM_AUTO_MAIN_BRANCH_IBLOCK_READ', 'L');
// Доступ редактирование своего филиала
define('LM_AUTO_MAIN_BRANCH_IBLOCK_READ_WRITE', 'M');

// Константы биндинга уровней доступа

define('LM_AUTO_ACCESS_BINDING_ORDERS', 'linemedia_auto_order'); // Заказы
define('LM_AUTO_ACCESS_BINDING_STATUSES', 'linemedia_auto_goods_statuses'); // Статусы товаров
define('LM_AUTO_ACCESS_BINDING_SUPPLIERS', 'linemedia_auto_providers'); // Поставщики
define('LM_AUTO_ACCESS_BINDING_PRICES_IMPORT', 'linemedia_auto_price_import'); // Импорт прайс-листов
define('LM_AUTO_ACCESS_BINDING_WORDFORMS', 'linemedia_auto_word_forms'); // Словоформы
define('LM_AUTO_ACCESS_BINDING_PRODUCTS', 'linemedia_auto_spare'); // Список запчастей
define('LM_AUTO_ACCESS_BINDING_STATISTICS', 'linemedia_auto_search_stat'); // Статистика поиска
define('LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS', 'linemedia_auto_user_fields'); // Пользовательские поля
define('LM_AUTO_ACCESS_BINDING_PRICES', 'linemedia_auto_price_ap'); // Ценообразование
define('LM_AUTO_ACCESS_BINDING_VIN', 'linemedia_auto_vin'); // Ценообразование
define('LM_AUTO_ACCESS_BINDING_FINANCE', 'linemedia_auto_finance'); // Транзакции и счета пользователей

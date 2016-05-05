<?php

$MESS['LM_AUTO_API_TITLE'] = 'API Linemedia (TecDoc + собственая база аналогов)';

$MESS['LM_AUTO_CRONTAB'] = 'Агенты работают через системный CRON';
$MESS['LM_AUTO_CRONTAB_HOWTO'] = 'Выполните указанные <a href="http://dev.1c-bitrix.ru/community/webdev/user/8078/blog/implementation-of-all-agents-in-cron/" target="_blank">здесь</a> инструкции!';


$MESS['LM_AUTO_CURL'] = 'Доступен модуль CURL';
$MESS['LM_AUTO_CURL_HOWTO'] = 'Необходимо установить модуль <a href="http://php.net/manual/ru/book.curl.php" target="_blank">CURL</a>';

$MESS['LM_AUTO_AOP'] = 'Доступен модуль PHP-AOP версии 0.3.0 и выше';
$MESS['LM_AUTO_AOP_HOWTO'] = 'Необходимо установить модуль <a href="https://github.com/AOP-PHP/AOP" target="_blank">PHP-AOP</a>';


$MESS['LM_AUTO_PINBA'] = 'Доступен модуль Pinba для PHP';
$MESS['LM_AUTO_PINBA_HOWTO'] = 'Рекомендуется установить модуль <a href="https://github.com/tony2001/pinba_extension" target="_blank">pinba_extension</a>';


$MESS['LM_AUTO_JSON'] = 'Доступен модуль JSON';
$MESS['LM_AUTO_JSON_HOWTO'] = 'Необходимо установить модуль <a href="http://php.net/manual/ru/book.json.php" target="_blank">JSON</a>';


$MESS['LM_AUTO_PHP53'] = 'Версия PHP 5.3.0 и выше (рекомендуется 5.4.2)';
$MESS['LM_AUTO_PHP53_HOWTO'] = 'Обновите PHP до <a href="http://php.net/downloads.php" target="_blank">свежей версии</a>';

$MESS['LM_AUTO_LIBREOFFICE_AVAILABLE'] = 'Установлен консольный LibreOffice';
$MESS['LM_AUTO_NO_LIBREOFFICE'] = 'Установите на сервер программу <b>LibreOffice</b> с помощью команды <u>yum install libreoffice-headless -y</u>';
$MESS['LM_AUTO_PHP_NO_SHELL'] = ' </br>Или для php установлен запрет на выполенение функции <b>shell_exec()</b>';

$MESS['LM_AUTO_JAVA_AVAILABLE'] = 'Установлена Java';
$MESS['LM_AUTO_NO_JAVA'] = 'Установите на сервер программу <b>Java</b> с помощью команды <u>yum install java</u>';

$MESS['LM_AUTO_API_AVAILABLE_TIME'] = 'Доступность сервера Linemedia API (Ваше время соединения - #TIME# сек., средне допустимое - 0.050 сек.)';
$MESS['LM_AUTO_API_AVAILABLE'] = 'Доступность сервера Linemedia API';
$MESS['LM_AUTO_API_RECOMMENDATIONS'] = 'Время соединения с сервером API превышает средне допустимое. Возможны перебои на линии. Попробуйте запустить проверку позже.';

$MESS['LM_AUTO_PRICELISTS_IMPORT_WAITING'] = 'Прайсов ожидают импорта';
$MESS['LM_AUTO_PRICELISTS_IMPORT_WAITING_HOWTO'] = 'Необходимо уменьшить периодичность выполнения CRON, ожидают импорта: ';


$MESS['LM_AUTO_SUPPLIER_MARKUP_DEBUG'] = 'Добавлена наценка <a target="_blank" href="[[SUPPLIER_URL]]#SUPPLIER_ID#">поставщика</a> (#MARKUP#% = #MARKUP_VALUE#) <b>#RESULT#</b>';

$MESS['LM_AUTO_SUPPLIER_CURRENCY_DEBUG'] = 'Цена сконвертирована согласно валюте поставщика (#SUPPLIER_CUR# &rarr; #BASE_CUR#) по курсу <a target="_blank" href="/bitrix/admin/currencies_rates.php?lang='.LANG.'">#AMOUNT#</a>. ';
$MESS['LM_AUTO_SUPPLIER_CURRENCY_NOT_APPLIED_DEBUG'] = 'Конвертация валюты не требуется.';

$MESS['LM_AUTO_LOG_CONVERTER'] = 'Конвертирование скачанных прайслистов';
$MESS['LM_AUTO_CONVERTER_AVAILABLE'] = 'Конвертация XLS и XLSX в CSV';
$MESS['LM_AUTO_CONVERTER_NOT_AVAILABLE'] = 'Требуется установка утилиты <a target="_blank" href="http://projects.gnome.org/gnumeric/doc/sect-files-ssconvert.shtml"><b>ssconvert</b></a> или для php установлен запрет на выполенение функции <b>shell_exec()</b>';



$MESS['LM_AUTO_HDD_SPACE'] = 'Минимум необходимого свободного места на диске #MIN#, доступно <b>#AVAILABLE#</b>';
$MESS['LM_AUTO_HDD_SPACE_HOWTO'] = 'Необходимо срочно очистить жёсткий диск или увеличить его ёмкость, свободного места осталось менее #MIN#!';

$MESS['LM_AUTO_UNZIP_AVAILABLE'] = 'Распаковка ZIP';
$MESS['LM_AUTO_UNZIP_NOT_AVAILABLE'] = 'Требуется установка утилиты <b>unzip</b>, доступной для выполнения из консоли или для php установлен запрет на выполенение функции <b>shell_exec()</b>';

$MESS['LM_AUTO_CANCEL_ITEM_TRANSACT_COMMENT'] = 'Отмена позиции ART (BRAND) в заказе ORDER_ID';


$MESS['LM_AUTO_BASKET_UNLOAD2'] = 'Отгрузка';

/*OnBeforeOrderAdd_RegisterUser*/
$MESS['LM_AUTO_EVENT_ORDER_USER_PROFILE'] = 'Профиль покупателя';
$MESS['LM_AUTO_EVENT_ORDER_FIO'] = 'Ф.И.О.';
$MESS['LM_AUTO_EVENT_ORDER_PHONE'] = "Телефон";
$MESS['LM_AUTO_EVENT_ORDER_INDEX'] = "Индекс";
$MESS['LM_AUTO_EVENT_ORDER_CITY'] = "Город";
$MESS['LM_AUTO_EVENT_ORDER_LOCATION'] = "Местоположение";
$MESS['LM_AUTO_EVENT_ORDER_DELIVERY_PLACE'] = "Адрес доставки";
$MESS['LM_AUTO_EVENT_ORDER_COMPANY_NAME'] = "Название компании";
$MESS['LM_AUTO_EVENT_ORDER_COMPANY_ADDRESS'] = "Юридический адрес";
$MESS['LM_AUTO_EVENT_ORDER_INN'] = "ИНН";
$MESS['LM_AUTO_EVENT_ORDER_KPP'] = "КПП";
$MESS['LM_AUTO_EVENT_ORDER_CONTACT_PERSON'] = "Контактное лицо";
$MESS['LM_AUTO_EVENT_ORDER_FAX'] = "Факс";
$MESS['LM_AUTO_EVENT_ORDER_'] = '';
$MESS['LM_AUTO_EVENT_ORDER_'] = '';
$MESS['LM_AUTO_EVENT_ORDER_'] = '';
$MESS['LM_AUTO_EVENT_ORDER_'] = '';

$MESS['LM_AUTO_ERR_NO_CHANGE_STATUS'] = 'Товар уже находится в этом статусе';

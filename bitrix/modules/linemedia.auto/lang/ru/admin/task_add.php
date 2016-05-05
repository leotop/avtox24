<?php

$MESS['ERROR_ACCESS_TO_APPROPRIATE_SUPPLIER_FORBIDDEN'] = 'Доступ к данному элементу закрыт. ID: ';

$MESS ['ERROR_TITLE'] = "Не заполнено название";
$MESS ['ERROR_PROTOCOL'] = "Не указан тип подключения";
$MESS ['ERROR_INTERVAL'] = "Не заполнен интервал выполнения задачи";
$MESS ['ERROR_SUPPLIER'] = "Не заполнен поставщик";
$MESS ['ERROR_CURRENCY'] = "Неправильная валюта";

$MESS ['ERROR_ADD'] = "Ошибка добавления";
$MESS ['ERROR_EDIT'] = "Ошибка изменения";

$MESS ['PROTOCOL_FTP'] = "FTP";
$MESS ['PROTOCOL_HTTP'] = "HTTP";
$MESS ['PROTOCOL_FILE'] = "Локально";

$MESS ['LM_AUTO_EDIT_TASK'] = "Редактирование шаблона импорта #ID#";
$MESS ['LM_AUTO_NEW_TASK'] = "Добавление шаблона импорта";
$MESS ['LM_AUTO_DELETE_TASK'] = "Удалить шаблон импорта";
$MESS ['LM_AUTO_DELETE_TASK_CONFIRM'] = "Вы подтверждаете удалене этой задачи?";
$MESS ['LM_AUTO_TASKS_LIST'] = "Список шаблонов импорта";

/* TABS */
$MESS ['LM_AUTO_TAB_MAIN'] = "Основные настройки";
$MESS ['LM_AUTO_TAB_CONNECTION'] = "Соединение";
$MESS ['LM_AUTO_TAB_CONVERSION'] = "Конвертация";
$MESS ['LM_AUTO_TAB_SHEDULE'] = "Расписание";
$MESS ['LM_AUTO_TAB_RIGHTS'] = "Доступ";


$MESS ['LM_AUTO_ID'] = "ID";
$MESS ['LM_AUTO_ACTIVE'] = "Активность";
$MESS ['LM_AUTO_TASK_TITLE'] = "Название";
$MESS ['LM_AUTO_PROTOCOL'] = "Протокол подключения";
$MESS ['LM_AUTO_SUPPLIER'] = "Поставщик";
$MESS ['LM_AUTO_INTERVAL'] = "Интервал";
$MESS ['LM_AUTO_TEST_MODE'] = "Режим тестирования";
$MESS ['LM_AUTO_TEST_EMAIL'] = "E-mail для получение результатов тестовой конвертации";

$MESS ['LM_AUTO_INTERVAL_HORLY'] = "Раз в час";
$MESS ['LM_AUTO_INTERVAL_DAILY'] = "Раз в сутки";
$MESS ['LM_AUTO_INTERVAL_WEEKLY'] = "Раз в неделю";
$MESS ['LM_AUTO_INTERVAL_MONTHLY'] = "Раз в месяц";


$MESS ['LM_AUTO_SOURCE_ENCODING_AUTODETECT'] = "(определить автоматически)";

$MESS ['LM_AUTO_NOT_SELECTED'] = "(не выбрано)";
$MESS ['LM_AUTO_ACTIVE_N'] = "Нет";
$MESS ['LM_AUTO_ACTIVE_Y'] = "Да";

$MESS ['LM_AUTO_TEST_PROTOCOL'] = "Проверка подключение";
$MESS ['LM_AUTO_TEST_PROTOCOL_BUTTON'] = "Проверить";


$MESS['LM_AUTO_UNZIP'] = 'Если ZIP архив, то использовать файл';
$MESS['LM_AUTO_UNZIP_DESCR'] = 'доступен символ <b>*</b>';


$MESS ['LM_AUTO_SOURCE_ENCODING'] = "Кодировка файла";
$MESS ['LM_AUTO_SOURCE_TYPE'] = "Тип исходного файла";
$MESS ['LM_AUTO_SOURCE_TYPE_AUTODETECT'] = "(определить на основе расширения)";
$MESS ['LM_AUTO_SOURCE_SKIP_LINES'] = "Пропустить строк в начале";
$MESS ['LM_AUTO_SOURCE_SEPARATOR'] = "Разделитель столбцов (для CSV)";

$MESS ['LM_AUTO_SOURCE_COLUMNS'] = "Порядок (индекс) столбцов в файле";
$MESS ['LM_AUTO_SOURCE_COLUMNS_ZERO'] = "Начиная с нуля";
$MESS ['LM_AUTO_SOURCE_COLUMN_BRAND_TITLE'] = "Бренд";
$MESS ['LM_AUTO_SOURCE_COLUMN_ARTICLE'] = "Артикул";
$MESS ['LM_AUTO_SOURCE_COLUMN_TITLE'] = "Наименование";
$MESS ['LM_AUTO_SOURCE_COLUMN_PRICE'] = "Цена";
$MESS ['LM_AUTO_SOURCE_COLUMN_QUANTITY'] = "Кол-во";
$MESS ['LM_AUTO_SOURCE_COLUMN_WEIGHT'] = "Вес";
$MESS ['LM_AUTO_SOURCE_COLUMN_GROUP_ID'] = "Группа";


$MESS ['LM_AUTO_REPLACEMENTS'] = "Подстановки";
$MESS ['LM_AUTO_SOURCE_COLUMNS_REPLACE'] = "Замены в столбцах";
$MESS ['LM_AUTO_SOURCE_COLUMNS_REPLACE_ALL'] = "Прописать в столбец значение";

$MESS['REPLACE_WHAT'] = 'Что заменить';
$MESS['REPLACE_WITH'] = 'На что';

$MESS ['LM_AUTO_INTERVAL_NOT_SELECTED'] = "не выбрано (запуск вручную)";
$MESS ['LM_AUTO_INTERVAL_TIME'] = "Время";
$MESS ['LM_AUTO_INTERVAL_DAY'] = "День";
$MESS ['LM_AUTO_LAST_DAY'] = "В последний день месяца";

$MESS ['LM_AUTO_ADD_TASK'] = "Добавить задачу";


$MESS['DAY_1'] = "Понедельник";
$MESS['DAY_2'] = "Вторник";
$MESS['DAY_3'] = "Среда";
$MESS['DAY_4'] = "Четверг";
$MESS['DAY_5'] = "Пятница";
$MESS['DAY_6'] = "Суббота";
$MESS['DAY_7'] = "Воскресенье";

$MESS['LM_AUTO_ERROR_CONVERTING'] = 'Внимание! Нельзя импортировать файл в формате XLS или XLSX.<br/>
Для загрузки используйте файл формата CSV.<br/>
<br/>
Вы сможете импортировать файлы, установив на свой сервер утилиту <a target="_blank" href="http://linuxcommand.org/man_pages/ssconvert1.html">ssconvert</a>.';

$MESS['LM_AUTO_SOURCE_IGNORE_EMTY_COLS_XLS'] = '<b>Внимание!</b> Пустые столбцы в начале xls/xlsx файла будут проигнорированы. Столбцом №1 будет первый столбец, где есть данные хотя бы в одной строке.';



$MESS['LM_AUTO_DOWNLOADER_SOURCE_RESAVE'] = 'Пересохранять файл с помощью LibreOffice (если возникают проблемы при работе ssconvert)';
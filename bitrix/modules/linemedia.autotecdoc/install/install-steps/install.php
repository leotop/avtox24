<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/*
 * сохранённые в сессии данные предыдущих шагов установщика
 */
$this->install_settings = (array) $_SESSION['linemedia_autotecdoc_module_install_settings'];

// Установка базы данных.
if (!$this->InstallDB()) {
    ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_INSTALL_DB'));
    exit();
}

// Установка событий.
if (!$this->InstallEvents()) {
    ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_INSTALL_EVENTS'));
    exit();
}

// Установка файлов.
if (!$this->InstallFiles()) {
    ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_INSTALL_FILES'));
    exit();
}

// Установка почтовых шаблонов.
if (!$this->InstallMessageTemplates()) {
    ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_INSTALL_MESSAGE_TEMPLATES'));
    exit();
}

// Добавление свойств интернет-магазина.
if (!$this->InstallSaleProps()) {
    ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_INSTALL_PROPS'));
    exit();
}


/*
 * Установить агенты можно только если модуль уже уставнолен
 */
if (!$this->InstallAgents()) {
    ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_INSTALL_AGENTS'));
    exit();
}
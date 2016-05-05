<?php
/**
* Удаление таблиц в БД запчастей
* Удаление производится через отдельное подключение, поскольку неизвестно, где хранятся таблицы
*/
if ($_POST['REMOVE_ANALOGS'] == 'Y') {

//     $database = new LinemediaAutoDatabase();
    global $DB;
    $DB->Query('DROP TABLE IF EXISTS b_lm_analogs_simple');
    
//     $DB->Disconnect();
}


if (!$this->UnInstallEvents() || !$this->UnInstallFiles()) {
    return;
}

UnRegisterModule( $this->MODULE_ID );

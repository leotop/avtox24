<?php

IncludeModuleLangFile(__FILE__);

/* 
 * Зарегистрируем установку модуля.
 * 
 * Регистрация необходима на последнем этапе, т.к. после нее строится подменю модуля.
 * при этом события на добавление главного меню происходят после подключения этого файла.
 */
RegisterModule('linemedia.autogarage');

?>

<?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_GARAGE_INSTALL_SUCCESS"), 'TYPE' => 'OK')) ?>

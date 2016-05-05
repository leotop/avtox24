<?php

IncludeModuleLangFile(__FILE__);


global $DBType, $DB;

/*
 * Заполняем таблицы
 */


$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.autotecdoc/install/db/".$DBType."/mod.sql");
if (is_array($errors) && count($errors) > 0) {
	foreach ($errors as $error) {
		echo $error;
	}
	ShowError(GetMessage('LM_AUTO_TECDOC_ERROR_CREATING_DATABASE'));
	exit;
}








/* 
 * Зарегистрируем установку модуля.
 * 
 * Регистрация необходима на последнем этапе, т.к. после нее строится подменю модуля.
 * при этом события на добавление главного меню происходят после подключения этого файла.
 */
RegisterModule('linemedia.autotecdoc');

?>

<?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_TECDOC_INSTALL_SUCCESS"), 'TYPE' => 'OK')) ?>

<form action="/bitrix/admin/settings.php?mid=linemedia.autotecdoc" method="post">
    <input type="submit" value="<?= GetMessage('LM_AUTO_TECDOC_INSTALL_GO_TO_MODULE') ?>" />
</form>


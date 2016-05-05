<?
/**
 * Linemedia Autoportal
 * Remote suppliers module
 * install step
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
IncludeModuleLangFile(__FILE__);

header( "refresh:1;url=/bitrix/admin/linemedia.autoremotesuppliers_info.php?lang=" . LANG );


echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_REMOTE_SUPPLIERS_INSTALLATION_SUCCESS"), 'TYPE' => 'OK'));

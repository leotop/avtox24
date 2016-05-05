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


if(!CModule::IncludeModule('linemedia.auto'))
{
    $m = new CAdminMessage(array('TYPE' => 'ERROR', 'HTML' => true, 'MESSAGE' => GetMessage('LINEMEDIA_AUTO_MAIN_MODULE_NOT_INSTALLED')));
    echo $m->Show();
    return;
}
?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autoremotesuppliers" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="finish" />
	
	
	
	
	
	<p>
        <input type="submit" value="<?= GetMessage('LM_AUTO_REMOTE_SUPPLIERS_INSTALL') ?>"/>
    </p>
</form>

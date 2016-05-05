<? IncludeModuleLangFile(__FILE__);
/**
 * Linemedia Autoportal
 * Downloader module
 * Install step
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
if(!CModule::IncludeModule('linemedia.auto'))
{
    $m = new CAdminMessage(array('TYPE' => 'ERROR', 'HTML' => true, 'MESSAGE' => GetMessage('LINEMEDIA_AUTO_MAIN_MODULE_NOT_INSTALLED')));
    echo $m->Show();
    return;
}



/*
* Доступные протоколы
*/
$protocols = LinemediaAutoDownloaderMain::getProtocols();

?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autodownloader" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="finish" />
	
	
	<?=BeginNote()?>
	<?=GetMessage('LM_AUTO_DOWNLOADER_PROTOCOLS_INFO')?>
	<?=EndNote()?>
	<?foreach($protocols AS $code => $protocol){
		if(!$protocol['available'])
		{
			$instance = LinemediaAutoDownloaderMain::getProtocolInstance($code);
			$requirements = $instance::getRequirements();
		}
	?>
		<div class="protocol protocol-<?=$protocol['available']?'ok':'error'?>"><?=$protocol['title']?> - <?=$protocol['available'] ? GetMessage('PROTOCOL_AVAILABLE') : GetMessage('PROTOCOL_UNAVAILABLE') . ' <span>'.$requirements.'</span>'?></div>
	<?}?>
	
	
	<?if(LinemediaAutoDownloaderMain::isConversionSupported()){?>
		<?=BeginNote()?>
		<?=GetMessage('LM_AUTO_DOWNLOADER_NO_CONVERTER')?>
		<?=EndNote()?>
	<?}?>
	
	
	<p>
        <input type="submit" value="<?= GetMessage('LM_AUTO_DOWNLOADER_INSTALL') ?>"/>
    </p>
</form>


<style>
.protocol {margin:4px;font-weight: bold}
.protocol-ok {color: green}
.protocol-error {color: red}
.protocol span {font-weight: normal}
</style>
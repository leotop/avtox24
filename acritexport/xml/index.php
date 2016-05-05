<?
$profId = intval($_REQUEST['ID']);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(0 >= $profId || !CModule::IncludeModule("acrit.export")) die($profId);

$APPLICATION->RestartBuffer();
echo CYml::ReturnXMLData($profId);
$r = $APPLICATION->EndBufferContentMan();
if ($r)
{
	$time = 
	$sFile = 
	$sFilePath = '';
	do
	{
		$time = time();
		$sFile = "/upload/{$time}.xml";
		$sFilePath = $_SERVER["DOCUMENT_ROOT"].$sFile;
	} while (file_exists($sFilePath));
	file_put_contents($sFilePath,$r);
	LocalRedirect($sFile);
}
die();

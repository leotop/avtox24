<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "����� 80 000 0000 ������� �� �����.");
$APPLICATION->SetPageProperty("tags", "��������, ����������, ��������, �������� �� ������");
$APPLICATION->SetPageProperty("keywords_inner", "������������ � ��������������  ��������");
$APPLICATION->SetPageProperty("title", "������������ ��� ��������");
$APPLICATION->SetTitle("AvtoX24.ru");
?>
<?php
include('am_searchpanel.php');
// Include soap request class
include('guayaquillib'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'requestAm.php');

$manufacturerid = $_GET['manufacturerid'];

$request = new GuayaquilRequestAM('en_US');
if (Config::$useLoginAuthorizationMethod) {
    $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
}
$request->appendManufacturerInfo($manufacturerid);
$data = $request->query();

if ($request->error != '')
{
    echo $request->error;
}
else
{
    $data = simplexml_load_string($data);
    $data = $data[0]->ManufacturerInfo->row;

    echo '<div> name: '.$data['name'].'</div>';
    echo '<div> alias: '.$data['alias'].'</div>';
    echo '<div> searchurl: '.$data['searchurl'].'</div>';
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
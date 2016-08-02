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

$request = new GuayaquilRequestAM('en_US');
if (Config::$useLoginAuthorizationMethod) {
    $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
}
$request->appendListManufacturer();
$data = $request->query();
if ($request->error != '')
{
    echo $request->error;
}
else
{
    $data = simplexml_load_string($data);
    $rows = $data[0]->ListManufacturer->row;

    echo '<table>';
    foreach ($rows as $row)
    {
        echo '<tr><td>'.$row['name'].'</td><td>'.$row['alias'].'</td><td>'.$row['searchurl'].'</td></tr>';
    }
    echo '</table>';
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?
file_exists(dirname(__FILE__) . "/class.php") ? include_once(dirname(__FILE__) . "/class.php") : "";
$zzap = new ZzapOrder($_REQUEST);
$zzap->putOrder();
?>
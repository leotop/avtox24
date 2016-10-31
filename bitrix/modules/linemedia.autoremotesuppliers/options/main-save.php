<?
IncludeModuleLangFile(__FILE__);

/*
$cache_time = (float) $_POST['cache_time'];
COption::SetOptionString("linemedia.autoremotesuppliers", "cache_time", $cache_time);
*/

$LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES = (string) $_POST['LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES'];
COption::SetOptionString('linemedia.autoremotesuppliers', 'LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES', $LM_AUTO_REMOTE_SUPPLIERS_USE_CROSSES);

$LM_AUTO_REMOTE_SUPPLIERS_USE_PATH_PHPINI_FILE = (string) $_POST['LM_AUTO_REMOTE_SUPPLIERS_USE_PATH_PHPINI_FILE'];
COption::SetOptionString('linemedia.autoremotesuppliers', 'LM_AUTO_REMOTE_SUPPLIERS_USE_PATH_PHPINI_FILE', $LM_AUTO_REMOTE_SUPPLIERS_USE_PATH_PHPINI_FILE);
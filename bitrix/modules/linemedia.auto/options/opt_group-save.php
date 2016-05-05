<?php 

$LM_AUTO_MAIN_OPT_GROUP = serialize((string)$_POST['LM_AUTO_MAIN_OPT_GROUP']);
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_OPT_GROUP', $LM_AUTO_MAIN_OPT_GROUP);

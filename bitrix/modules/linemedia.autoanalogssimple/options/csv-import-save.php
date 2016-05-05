<?php

$CSV_IMPORT_STRING = (string) $_POST['CSV_IMPORT_STRING'];
COption::SetOptionString($sModuleId, 'CSV_IMPORT_STRING', $CSV_IMPORT_STRING);


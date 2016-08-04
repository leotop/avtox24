<?php
header('Content-type: application/json');
echo safe_json_encode(array('errors' => $arResult['ERRORS']));
exit();
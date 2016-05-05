<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$actions        = (string) $_REQUEST['actioms'];
$brandID        = (int) $_REQUEST['BrandID'];
$modelID        = (int) $_REQUEST['ModelID'];
$modificationID = (int) $_REQUEST['ModificationID'];

$APPLICATION->IncludeComponent(
    'linemedia.auto:tecdoc.auto.select',
    '',
    array(
        'ACTIONS'           => $actions,
        'BRAND_ID'          => $brandID,
        'MODEL_ID'          => $modelID,
        'MODIFICATION_ID'   => $modificationID,
    )
);

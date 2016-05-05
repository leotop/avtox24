<?php

//_d($_POST);

$LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE = serialize((array) $_POST['LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE']);
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE', $LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE);


$LM_AUTO_MAIN_DEFERRED_PAYMENT = (string) $_POST['LM_AUTO_MAIN_DEFERRED_PAYMENT'];
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DEFERRED_PAYMENT', $LM_AUTO_MAIN_DEFERRED_PAYMENT);

$LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING = serialize((array) $_POST['LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING']);
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING', $LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING);

$LM_AUTO_MAIN_GROUP_TRANSFER_BACK = (string) $_POST['LM_AUTO_MAIN_GROUP_TRANSFER_BACK'];
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_GROUP_TRANSFER_BACK', $LM_AUTO_MAIN_GROUP_TRANSFER_BACK);

$LM_AUTO_MAIN_STATUSES_WHEN_CAN_EDIT_ORDERS = serialize( (array)$_POST['LM_AUTO_MAIN_STATUSES_WHEN_CAN_EDIT_ORDERS']);
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUSES_WHEN_CAN_EDIT_ORDERS', $LM_AUTO_MAIN_STATUSES_WHEN_CAN_EDIT_ORDERS);

$postDocTypes = (array)$_REQUEST['LM_AUTO_MAIN_ORDER_DOCUMENT_TYPES'];
$LM_AUTO_MAIN_ORDER_DOCUMENT_TYPES = array();

for($i=0; $i<count($postDocTypes); $i+=2) {
    if(!empty($postDocTypes[$i]) && !empty($postDocTypes[$i+1])) {
        $folder = LinemediaAutoOrderDocuments::safeFolderName($postDocTypes[$i+1]);
        $LM_AUTO_MAIN_ORDER_DOCUMENT_TYPES[] = array(
            'name' => $postDocTypes[$i],
            'folder' => $folder,
        );
        LinemediaAutoOrderDocuments::checkUploadFolder($postDocTypes[$i+1]);
    }
}
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_ORDER_DOCUMENT_TYPES', serialize($LM_AUTO_MAIN_ORDER_DOCUMENT_TYPES));
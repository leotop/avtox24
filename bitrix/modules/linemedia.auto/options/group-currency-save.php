<?php
$LM_AUTO_MAIN_GROUP_CURRENCY = array();

if(array_key_exists('group_id', $_POST) && is_array($_POST['group_id'])) {

    for($i=0; $i<count($_POST['group_id']); $i++) {

        $group_id = $_POST['group_id'][$i];

        if(strlen($_POST['currency'][$i]) == 3) {
            $LM_AUTO_MAIN_GROUP_CURRENCY[$group_id] = $_POST['currency'][$i];
        } else if($_POST['currency'][$i] == '0') {
            unset($LM_AUTO_MAIN_GROUP_CURRENCY[$group_id]);
        }
    }

    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_GROUP_CURRENCY', serialize($LM_AUTO_MAIN_GROUP_CURRENCY));
}
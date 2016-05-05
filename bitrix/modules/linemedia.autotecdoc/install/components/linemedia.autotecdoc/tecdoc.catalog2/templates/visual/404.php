<?php

if(isset($arResult['error']['group_id'])) {

    $group_name = '';

    if(Cmodule::IncludeModule('linemedia.auto')) {
        try {
            $api = new LinemediaAutoApiDriver();
            $args = array('group_id' => $arResult['error']['group_id'], 'type_id' => $arResult['error']['type_id']);
            $group_name = $api->query('getGroupNameById', $args);
        } catch (Exception $e) {
            echo 'LineMedia APi request error: ' . $e->GetMessage();
        }
    }

    echo GetMessage('GROUP_NOT_FOUND', array('#GROUP_NAME#' => $group_name)) . ' <a href="' . $arParams['SEF_FOLDER'] . '">' . GetMessage('GO_TO_TECDOC') . '</a>';
}
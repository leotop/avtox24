<?php

$steps = array();

foreach ((array) $_POST['LM_AUTO_SUPPLIERS_STEP_TITLE'] as $key => $title) {
    if (empty($title)) {
        continue;
    }
    
    // Данные шага.
    $data = array(
        'key'               => (string) $key,
        'title'             => (string) $title,
        'filter-statuses'   => (array)  $_POST['LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES'][$key],
        'default-status'    => (string) $_POST['LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS'][$key],
        'request'           => (string) $_POST['LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST'][$key],
        'mail'              => (string) $_POST['LM_AUTO_SUPPLIERS_STEP_CAN_MAIL'][$key],
        'upload'            => (string) $_POST['LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD'][$key],
    );
    // Полный шаг с отуствием преамбулы и прочего естества
    $steps []= $key;
    
    // Сохранение шага.
    COption::SetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_STEP_'.$key, serialize($data));
}

// Сохранение списка шагов.
COption::SetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_STEPS', serialize($steps));
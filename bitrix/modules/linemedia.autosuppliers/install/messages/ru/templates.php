<?php

$arTemplates = array(
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SUPPLIERS_REQUEST',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'HTML',
        'SUBJECT'       => '#SITE_NAME#: Заявка поставщику',
        'MESSAGE'       => "Вам доступна новая заявка <a href='#SERVER_NAME#/bitrix/admin/linemedia.autosuppliers_out_history.php?find_id=#ID#&set_filter=Y'>#ID#</a> от #TIME#."
    ),
);

<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Default options
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile( __FILE__ );


/* Здесь вписываются значения по умолчанию для всех настроек модуля */

$linemedia_autosuppliers_default_option = array(
    'FIRST_STATUSES'                => serialize(array('N', 'P')),
    'REQUESTED_GOODS_STATUS'        => 'R',
    'CLOSED_STATUSES'               => serialize(array('A', 'C')),
    'LM_AUTO_SUPPLIERS_STEPS'       => serialize(array('1', '2')),
    'LM_AUTO_SUPPLIERS_STEP_1'      => serialize(array(
        'key'               => '1',
        'title'             => GetMessage('LM_AUTO_SUPPIERS_STEP_1'),
        'filter-statuses'   => array('R'),
        'default-status'    => 'A',
        'request'           => 'N',
        'mail'              => 'N',
        'upload'            => 'Y',
    )),
    'LM_AUTO_SUPPLIERS_STEP_2'      => serialize(array(
        'key'               => '2',
        'title'             => GetMessage('LM_AUTO_SUPPIERS_STEP_2'),
        'filter-statuses'   => array('A'),
        'default-status'    => 'S',
        'request'           => 'N',
        'mail'              => 'N',
        'upload'            => 'Y',
    )),
);



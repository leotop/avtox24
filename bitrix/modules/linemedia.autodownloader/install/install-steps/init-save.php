<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * ajax api registration
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);


/*
 * ������������ �������.
 */
$this->InstallEvents();

/*
 * ����������� ������.
 */
$this->InstallFiles();


$this->InstallDB();


/*
 * ��������.
 */
$this->InstallRewrites();



$this->InstallIblocks();


RegisterModule('linemedia.autodownloader');


/*
 * ������������ ������� ����� ������.
 */
$this->InstallAgents();


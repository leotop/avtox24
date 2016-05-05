<?php
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * api-save
 * 
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


$LM_AUTO_TECDOC_API_ID = (int) $_POST['LM_AUTO_TECDOC_API_ID'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_TECDOC_API_ID', $LM_AUTO_TECDOC_API_ID);

$LM_AUTO_TECDOC_API_URL = (string) $_POST['LM_AUTO_TECDOC_API_URL'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_TECDOC_API_URL', $LM_AUTO_TECDOC_API_URL);

$LM_AUTO_TECDOC_API_FORMAT = (string) $_POST['LM_AUTO_TECDOC_API_FORMAT'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_TECDOC_API_FORMAT', $LM_AUTO_TECDOC_API_FORMAT);

<?php
/**
 * Linemedia Autoportal
 * Autodecdoc module
 * common-settings-save
 * 
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


$LM_AUTO_TECDOC_PART_DETAIL_PAGE = (string) $_POST['LM_AUTO_TECDOC_PART_DETAIL_PAGE'];
COption::SetOptionString($sModuleId, 'LM_AUTO_TECDOC_PART_DETAIL_PAGE', $LM_AUTO_TECDOC_PART_DETAIL_PAGE);

$LM_AUTO_TECDOC_PART_SEARCH_PAGE = (string) $_POST['LM_AUTO_TECDOC_PART_SEARCH_PAGE'];
COption::SetOptionString($sModuleId, 'LM_AUTO_TECDOC_PART_SEARCH_PAGE', $LM_AUTO_TECDOC_PART_SEARCH_PAGE);

$LM_AUTO_TECDOC_PART_SEARCH_BRAND_PAGE = (string) $_POST['LM_AUTO_TECDOC_PART_SEARCH_BRAND_PAGE'];
COption::SetOptionString($sModuleId, 'LM_AUTO_TECDOC_PART_SEARCH_BRAND_PAGE', $LM_AUTO_TECDOC_PART_SEARCH_BRAND_PAGE);

<?php
/**
 * Административный скрипт меню удаленных поставщиков
 */
/**
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
IncludeModuleLangFile(__FILE__);

$module_id = 'linemedia.autoremotesuppliers';

$sMGRight = $APPLICATION->GetGroupRight($module_id);

if (IsModuleInstalled($module_id) && IsModuleInstalled('linemedia.auto')) {

    if ($sMGRight != 'D') {
        
        if ($sMGRight == 'U' || $sMGRight == 'W') {
            $aMenu[] = array(
                    'parent_menu'   => 'global_menu_linemedia.auto',
                    'section'       => 'linemedia.auto',
                    'sort'          => 1101,
                    'url'           => 'linemedia.autoremotesuppliers_info.php?lang='.LANGUAGE_ID,
                    'text'          => GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_TITLE'),
                    'title'         => GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_DESCTIPTION'),
                    'icon'          => 'linemedia.autoremotesuppliers_menu_icon_info',
                    'page_icon'     => 'linemedia.autoremotesuppliers_page_icon_info',
                    'module_id'     => 'linemedia.autoremotesuppliers',
                    'items_id'      => 'menu_linemedia.autoremotesuppliers',
                    'dynamic'       => false,
            );
                        
        }
                
        
        
        
           
        
        /*
         * событие для других модулей
         */
        $events = GetModuleEvents("linemedia.autoremotesuppliers", "OnAfterAdminMenuBuild");
    	while ($arEvent = $events->Fetch()) {
    	    try {
    		    ExecuteModuleEventEx($arEvent, array(&$aMenu));
    		} catch (Exception $e) {
    		    throw $e;
    		}
        }
        
        return $aMenu;
    
    }

}

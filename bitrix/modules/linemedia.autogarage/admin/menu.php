<?php

IncludeModuleLangFile(__FILE__);

$sMGRight = $APPLICATION->GetGroupRight('linemedia.autogarage');

if (IsModuleInstalled('linemedia.autogarage')) {
    
    if ($sMGRight != 'D') {
        
        $aMenu[] = array(
            'parent_menu'   => 'global_menu_linemedia.auto',
            'section'       => 'linemedia.autogarage',
            'sort'          => 90,
            'url'           => 'linemedia.autogarage_help.php?lang='.LANGUAGE_ID,
            'more_url'      => array(),
            'text'          => GetMessage('LM_AUTO_GARAGE'),
            'title'         => GetMessage('LM_AUTO_GARAGE_TITLE'),
            'icon'          => 'linemedia.autogarage_menu_icon_manager',
            'page_icon'     => 'linemedia.autogarage_page_icon_manager',
            'module_id'     => 'linemedia.autogarage',
            'items_id'      => 'menu_linemedia.autobracnhes_manager_service',
            'dynamic'       => false,
            'items' => array(
                array(
                    "text" => GetMessage('LM_AUTO_GARAGE_HELP'),
                    "url" => 'linemedia.autogarage_help.php?lang='.LANGUAGE_ID,
                ),
            ),
        );
        
        
        /*
         * Cобытие для других модулей.
         */
        $events = GetModuleEvents("linemedia.autogarage", "OnAfterAdminMenuBuild");
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

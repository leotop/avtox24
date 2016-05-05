<?php

CModule::IncludeModule('linemedia.autosuppliers');

IncludeModuleLangFile(__FILE__);

$sMGRight = $APPLICATION->GetGroupRight('linemedia.autosuppliers');

if ((!defined('NO_LM_AUTO_SUPPLIERS_MODULE_INSTALLED') || NO_LM_AUTO_SUPPLIERS_MODULE_INSTALLED != true) && IsModuleInstalled('linemedia.autosuppliers') && IsModuleInstalled('linemedia.auto')) {

    if ($sMGRight != 'D') {
        
        $steps = LinemediaAutoSuppliersStep::getList();
        
        $menusteps = array();
        foreach ($steps as $step) {
            $menusteps []= array(
                'text'  => $step->get('title'),
                'url'   => 'linemedia.autosuppliers_step.php?lang='.LANGUAGE_ID.'&key='.$step->get('key'),
            );
        }

        if ($sMGRight == 'U' || $sMGRight == 'W') {
			$LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS = unserialize(COption::GetOptionString("linemedia.auto", 'LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS'));
            $curUserGroup = $USER->GetUserGroupArray();
            $intr = array();
            $intr = array_intersect($LM_AUTO_MAIN_MENU_SHOW_SUPPLIERS, $curUserGroup); 

			if(!empty($intr))
			{
				$aMenu[] = array(
						'parent_menu'   => 'global_menu_linemedia.auto',
						'section'       => 'linemedia.autosuppliers',
						'sort'          => 190,
						//'url'           => 'linemedia.autosuppliers_in.php?lang='.LANGUAGE_ID,
						//'more_url'      => array(),
						'text'          => GetMessage('LM_AUTO_SUPPLIERS_TITLE'),
						'title'         => GetMessage('LM_AUTO_SUPPLIERS_DESCTIPTION'),
						'icon'          => 'linemedia.autosuppliers_menu_icon',
						'page_icon'     => 'linemedia.autosuppliers_page_icon',
						'module_id'     => 'linemedia.autosuppliers',
						'items_id'      => 'menu_linemedia.autosuppliers_in',
						'dynamic'       => false,
						'items' => array(
							array(
								'text'          => GetMessage('LM_AUTO_SUPPLIERS_OUT_TITLE'),
								'title'         => GetMessage('LM_AUTO_SUPPLIERS_OUT_DESCTIPTION'),
								'url'           => 'linemedia.autosuppliers_out.php?lang='.LANGUAGE_ID,
								'icon'          => 'linemedia.autosuppliers_menu_icon_out',
								'page_icon'     => 'linemedia.autosuppliers_page_icon_out',
							),
							array(
								'text'          => GetMessage('LM_AUTO_SUPPLIERS_OUT_HISTORY_TITLE'),
								'title'         => GetMessage('LM_AUTO_SUPPLIERS_OUT_HISTORY_DESCRIPTION'),
								'url'           => 'linemedia.autosuppliers_out_history.php',
								'icon'          => 'linemedia.autosuppliers_menu_icon_out_history',
								'page_icon'     => 'linemedia.autosuppliers_page_icon_out_history',
							),
							array(
								'text'          => GetMessage('LM_AUTO_SUPPLIERS_OUT_STEPS_TITLE'),
								'title'         => GetMessage('LM_AUTO_SUPPLIERS_OUT_STEPS_DESCTIPTION'),
								'icon'          => 'linemedia.autosuppliers_menu_icon_step',
								'page_icon'     => 'linemedia.autosuppliers_page_icon_step',
								'module_id'     => 'linemedia.autosuppliers',
								'items_id'      => 'menu_linemedia.autosuppliers_out_steps',
								'items'         => $menusteps
							),
						),
				);
			}
        }
        
        
        
        
        /*
         * событие для других модулей
         */
        $events = GetModuleEvents("linemedia.autosuppliers", "OnAfterAdminMenuBuild");
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

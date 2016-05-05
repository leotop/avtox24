<?php
/**
 * Административный файл для создания меню кроссов
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

$sMGRight = $APPLICATION->GetGroupRight("linemedia.autoanalogssimple");

if (IsModuleInstalled('linemedia.autoanalogssimple') && IsModuleInstalled('linemedia.auto')) {

    if ($sMGRight != 'D') {
        
        if ($sMGRight == 'U' || $sMGRight == 'W') {
            $aMenu[] = array(
                    'parent_menu'   => 'global_menu_linemedia.auto',
                    'section'       => 'linemedia.auto',
                    'sort'          => 200,
                    'url'           => 'linemedia.autoanalogssimple_list.php?lang='.LANGUAGE_ID,
                    'text'          => GetMessage('LM_AUTO_AS_TITLE'),
                    'title'         => GetMessage('LM_AUTO_AS_DESCTIPTION'),
                    'icon'          => 'linemedia.autoanalogssimple_menu_icon_main',
                    'page_icon'     => 'linemedia.autoanalogssimple_page_icon_main',
                    'module_id'     => 'linemedia.autoanalogssimple',
                    'items_id'      => 'menu_linemedia.autoanalogssimple',
                    'dynamic'       => false,
                    'items' => Array(
                        array(
                            "text" => GetMessage('LM_AUTO_AS_IMPORT_TITLE'),
                            "url" => 'linemedia.autoanalogssimple_import.php?lang='.LANGUAGE_ID,
                            'icon'          => 'linemedia.autoanalogssimple_menu_icon_import',
                            'page_icon'     => 'linemedia.autoanalogssimple_page_icon_import',
                            "title" => 'Создать документы'
                        ),
                        array(
                            "text" => GetMessage('LM_AUTO_AS_LIST_TITLE'),
                            "url" => 'linemedia.autoanalogssimple_list.php?lang='.LANGUAGE_ID,
                            'icon'          => 'linemedia.autoanalogssimple_menu_icon_list',
                            'page_icon'     => 'linemedia.autoanalogssimple_page_icon_list',
                            "title" => 'Созданные документы'
                        ),
                    ),
            );
        }   
        
        /*
         * событие для других модулей
         */
        $events = GetModuleEvents("linemedia.autoanalogssimple", "OnAfterAdminMenuBuild");
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

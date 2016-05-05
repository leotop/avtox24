<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/**
 * Linemedia Autoportal
 * Main module
 * Module events for main bitrix module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


class LinemediaAutoEventMain
{
	
	/**
	* Заупуск метрики и других инициализаций
	*/
	public function OnPageStart_Init()
	{
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/classes/general/monitoring.php';
        require_once($filename);
		LinemediaAutoMonitoring::startPage();
		
		
		
		// Check disk space
		
		global $APPLICATION;
		$page = $APPLICATION->GetCurPage();
		// only in admin panel
		if(strpos($page, '/bitrix/admin/') === 0) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/classes/general/file_helper.php');
			
			$available_free_space = LinemediaAutoFileHelper::getAvailableHDDSpace();
			$min_free_space = 1024*1024*1024; // 1G
			
			// show warning
			if($available_free_space < $min_free_space) {
				$min_free_space_print = LinemediaAutoFileHelper::getPrintableFilesize($min_free_space);
				$available_free_space_print = LinemediaAutoFileHelper::getPrintableFilesize($available_free_space);
				
				
				$ar = Array(
		           "MESSAGE" => GetMessage('LM_AUTO_NO_FREE_DISK_SPACE', array('#MIN#' => $min_free_space_print, '#AVAILABLE#' => $available_free_space_print)),
		           "TAG" => "LM_FREE_DISK_SPACE",
		           "MODULE_ID" => "linemedia.auto",
		           "ENABLE_CLOSE" => "N"
		        );
		        $ID = CAdminNotify::Add($ar);
	        } else {
		    	CAdminNotify::DeleteByTag("LM_FREE_DISK_SPACE");
		    }
	    }
	}
	
	
    /**
     * Проверка и изменение главного меню в зависимости от настроек.
     */
    public function OnBuildGlobalMenu_CheckMainMenu(&$mainmenu, &$menu)
    {
        global $APPLICATION, $USER;

        if(!$USER ->IsAdmin())
        {
            // Скрытие пунктов меню магазина.
            if (array_key_exists('global_menu_store', $mainmenu)) {
			    global $USER;
			    $arGroups = $USER->GetUserGroupArray();
			    $arHideStoreGroups = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE'));
                if (count(array_intersect($arGroups, $arHideStoreGroups)) > 0) {
                    // Скрытие всего раздела "Магазин".

                    foreach ($menu as $i => $item) {
                        if ($item['parent_menu'] == 'global_menu_store') {
                            unset($menu[$i]);
                        }
                    }
                    unset($mainmenu['global_menu_store']);
                } else {
                    // Скрытие подпунктов меню раздела "Магазин".
                    $hidemenu = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_MENU_HIDE', array()));
                    $hidemenu = (array) $hidemenu['STORE'];

                    foreach ((array) $menu as $i => $item) {
                        if ($item['parent_menu'] == 'global_menu_store') {
                            foreach ((array) $hidemenu as $index => $value) {
                                $indexes = explode('_', $index);
                                $itemmenu = &$menu[$i]['items'];
                                $last = array_pop($indexes);

                                foreach ($indexes as $idx) {
                                    $itemmenu = &$itemmenu[$idx];
                                }
                            }
                        }
                    }
                }
            } // if (array_key_exists('global_menu_store', $mainmenu))
		    
		    
		    //Скрытие пункта Настройки
		    if (array_key_exists('global_menu_settings', $mainmenu)) {
			    global $USER;
			    $arGroups = $USER->GetUserGroupArray();
			    $arHideStoreGroups = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_MAIN'));
                if (count(array_intersect($arGroups, $arHideStoreGroups)) > 0) {
                    // Скрытие всего раздела "Сервисы".

                    foreach ($menu as $i => $item) {
                        if ($item['parent_menu'] == 'global_menu_settings') {
                            // если есть доступ к модулю валют - перенесем его в авто
                            if($item['section'] == 'currency' && $APPLICATION->GetGroupRight('currency') > 'D') {
                                $menu[$i]['parent_menu'] = 'global_menu_linemedia.auto';
                                $menu[$i]['sort'] = 9050;
                            } else {
                                unset($menu[$i]);
                            }

                        }
                    }
                    unset($mainmenu['global_menu_settings']);
                }
            }
		    
		    //Скрытие пункта Сервисы
		    if (array_key_exists('global_menu_services', $mainmenu)) {
			    global $USER;
			    $arGroups = $USER->GetUserGroupArray();
			    $arHideStoreGroups = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_SERVICES'));
                if (count(array_intersect($arGroups, $arHideStoreGroups)) > 0) {
                    // Скрытие всего раздела "Сервисы".

                    foreach ($menu as $i => $item) {
                        if ($item['parent_menu'] == 'global_menu_services') {
                            unset($menu[$i]);
                        }
                    }
                    unset($mainmenu['global_menu_services']);
                }
            }

	        //Скрытие пункта Аналитика
	        if (array_key_exists('global_menu_statistics', $mainmenu)) {
		        global $USER;
		        $arGroups = $USER->GetUserGroupArray();
		        $arHideStoreGroups = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_ANALYTICS'));
		        if (count(array_intersect($arGroups, $arHideStoreGroups)) > 0) {
			        // Скрытие всего раздела "Сервисы".

			        foreach ($menu as $i => $item) {
				        if ($item['parent_menu'] == 'global_menu_statistics') {
					        unset($menu[$i]);
				        }
			        }
			        unset($mainmenu['global_menu_statistics']);
		        }
	        }

            // Скрыть меню Управление инфоблоками (импорт, экспорт, настройки) в основном меню
            global $USER;
            $arGroups = $USER->GetUserGroupArray();
            $arHideIblockOperationGroups = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_IBLOCK_OPERATION'));
            if (count(array_intersect($arGroups, $arHideIblockOperationGroups)) > 0) {
                foreach ($menu as $i => $item) {
                    if($item['section'] == 'iblock') unset($menu[$i]);
                }
            }
        }

      /*  // Прверка доступа к бизнеспроцессам
        $bizProcRight = $APPLICATION->GetGroupRight("bizproc");
        if($bizProcRight == 'D') {
            foreach($menu as $key => $value) {
                if($value['section'] == 'bizproc') unset($menu[$key]);
            }
        }
		
		// Прверка доступа к общим настройкам
        $mainRight = $APPLICATION->GetGroupRight("main");
        if($mainRight < 'W') {
            foreach($menu as $key => $value) {
                if($value['section'] == 'MAIN' || $value['section'] == 'TOOLS' || $value['section'] == 'security') unset($menu[$key]);
            }
        }
		
		// Прверка доступа к смайлам и рейтингам
        $mainRight = $APPLICATION->GetGroupRight("forum");
        if($mainRight < 'W') {
            foreach($menu as $key => $value) {
                if($value['section'] == 'forum' || $value['section'] == 'smile' || $value['section'] == 'rating') unset($menu[$key]);
            }
        }*/
		
		// Прверка доступа к поставщикам
		$sModuleId = "linemedia.auto";
		$curUserGroup = $USER->GetUserGroupArray();
		$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_SUPPLIERS);
		$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);

        if($maxRole == 'D') {
            foreach($menu as $key => $value) {
                if($value['section'] == 'linemedia.autosuppliers') unset($menu[$key]);
            }
        }

	    /*
	     * Скрытие инфоблока VIN запросов для директоров
	     */
	    $cur_user_group = $USER->GetUserGroupArray(); //массив групп пользователя

	    $directorsGroup = array();

	    $filter = array (
		    "STRING_ID"  => "LM_AUTO_SALESMAN_SALESMAN_GROUP|LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR|LM_AUTO_BRANCHES_DIRECTOR"
	    );

	    $rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), $filter);

	    while($arGroups = $rsGroups -> Fetch()) {
		    $directorsGroup[] = $arGroups["ID"];
	    }

	    if(array_intersect($directorsGroup, $cur_user_group)) {
		    foreach($menu as $i => $item) {
			    if(is_array($item['items'])) {
				    foreach($item['items'] as $ii => $item_item) {
					    if($item_item['items_id'] == 'menu_iblock_/linemedia_auto/' . COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_VIN')) {
							unset($menu[$i]['items'][$ii]);
					    }
				    }
			    }
			}
	    }
    }


    /**
     * Добавление главного меню "LM Автопортал".
     */
    public function OnBuildGlobal_AddMainMenu()
    {

        $is12 = (version_compare(SM_VERSION, 12) >= 0);
        $text = $is12 ? GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_TITLE') : '<nobr>' . GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_TITLE') . '</nobr>';

        /*
         * Глобальный раздел меню "LM Автопортал"
         */
        $menu = array(
            'global_menu_linemedia.auto' => array(
                "menu_id" => "LinemediaAuto",
                'icon' => 'linemedia.auto',
                'page_icon' => 'linemedia.auto',
                'index_icon' => 'linemedia.auto',
                'text' => $text,
                'title' => GetMessage('GLOBAL_MENU_TITLE'),
                'url' => 'linemedia.auto_index.php?lang='.LANGUAGE_ID,
                'sort' => 60,
                'items_id' => 'global_menu_linemedia',
                'help_section' => 'settings',
                'items' => array()
            )
        );

        return $menu;
    }


    /**
     * Проверка обновлений.
     */
    public static function OnAdminInformerInsertItems_addUpdatesCheck()
    {
		/*
		 * Дата: 30.09.13 19:00
		 * Кто: Назарков Илья
		 * Задача: 5470
		 * Пояснения: убираем плашку
		 */
		return;

        if (version_compare(SM_VERSION, 12) < 0) {
            return;
        }

        $updates = LinemediaAutoModule::checkUpdates('linemedia.auto');
        if ($updates === false) {
            return;
        }
        end($updates);
        $last_version = key($updates);


        /*
         * Обновление уже установили
         */
        if (version_compare(LINEMEDIA_AUTO_MAIN_VERSION, $last_version) >= 0) {
            return;
        }

        $updates_str = '';
        $updates = array_slice($updates, 0, 5); // покажем последние 5 записей, а не все стомиллионов
        foreach ($updates as $ver => $txt) {
            $updates_str .= '<span class="adm-informer-strong-text">' . $ver . '</span> '.$txt.'<br>';
        }
        $qAIParams = array(
            "TITLE" => GetMessage('LM_AUTO_MAIN_UPDATES_AVAILABLE'),
            "COLOR" => "red",
            "ALERT" => true,
        );

        $qAIParams["HTML"] = '
        <div class="adm-informer-item-section">
            <span class="adm-informer-item-l">'.GetMessage('LM_AUTO_MAIN_UPDATE_NEED').' <span class="adm-informer-strong-text"><a href="/bitrix/admin/update_system_partner.php?lang='.LANGUAGE_ID.'">'.GetMessage('LM_AUTO_MAIN_UPDATE_NEED_2').'</a></span></span>
        </div>
        <div class="adm-informer-item-section">
            <span class="adm-informer-item-r">'.GetMEssage('LM_AUTO_MAIN_YOUR_VERSION').': <span class="adm-informer-strong-text">'.LINEMEDIA_AUTO_MAIN_VERSION.'</span></span>
        </div>
        <div class="adm-informer-item-section">'.GetMEssage('LM_AUTO_MAIN_NEW_VERSION').': <span class="adm-informer-strong-text">'.$last_version.'</span></div>
        <div class="adm-informer-item-section">'.$updates_str.'</div>';


        /*
         * А нет ли старого информера?
         * как проверить через апи - непонятно
         */
        CAdminInformer::AddItem($qAIParams);
    }


    /**
     * Очистка
     */
    public static function OnModuleUpdate_clearCache()
    {
        BXClearCache(true, '/lm_auto/mod_updates/');
    }


    /**
     * Проверка работы аккаунта в АПИ
     */
    public static function OnAdminInformerInsertItems_addLinemediaAccountCheck()
    {
		/*
		 * Дата: 30.09.13 19:00
		 * Кто: Назарков Илья
		 * Задача: 5470
		 * Пояснения: убираем плашку
		 */
		return;

        /*
         * Отключаем проверку, если модуль не установлен,
         * чтобы избежать ошибок при проверке во время установки модуля
         */
        if (!CModule::IncludeModule('linemedia.auto')) {
            return;
        }
        /*
         * Будем вызывать проверку раз в час
         */
        $obCache = new CPHPCache();
        if ($obCache->InitCache(3600, __METHOD__, "/")) {
            return;
        }
        $obCache->StartDataCache();
        $obCache->EndDataCache();


        $api = new LinemediaAutoApiDriver();

        try {
            $response = $api->getAccountInfo();
        } catch (Exception $e) {
            $ar = Array(
               "MESSAGE" => GetMessage('LM_AUTO_MAIN_LINEMEDIA_API_NOT_AVAILABLE') . ' ' . $e->GetMessage(),
               "TAG" => "LM_API_ERROR",
               "MODULE_ID" => "linemedia.auto",
               "ENABLE_CLOSE" => "Y"
            );
			/*
			 * Дата: 30.09.13 19:00
			 * Кто: Назарков Илья
			 * Задача: 5470
			 * Пояснения: убираем плашку
			 */
			 
            //$ID = CAdminNotify::Add($ar);

            return;
        }


        $account = $response['data'];

        /*
         * Дней осталось у текдока
         */
        $before = strtotime($account['tecdoc']['available_before']);
        if ($before == 0) { // вечный текдок
            CAdminNotify::DeleteByTag("LM_TECDOC");
            return;
        }


        /*
         * Надо ли проверять текдок?
         */
        $LM_AUTO_MAIN_API_INFORM_TECDOC = COption::GetOptionString( 'linemedia.auto', 'LM_AUTO_MAIN_API_INFORM_TECDOC', 'Y' ) == 'Y';
        if ($LM_AUTO_MAIN_API_INFORM_TECDOC) {
            $tecdoc_left = ($before - time()) / 86400;

            if ($tecdoc_left < 1) {
                $ar = Array(
                   "MESSAGE" => GetMessage('LM_AUTO_MAIN_LINEMEDIA_API_TECDOC_FINISHED'),
                   "TAG" => "LM_TECDOC",
                   "MODULE_ID" => "linemedia.auto",
                   "ENABLE_CLOSE" => "Y"
                );
                $ID = CAdminNotify::Add($ar);

                return;
            }

            if( $tecdoc_left < 5) {
                $ar = Array(
                   "MESSAGE" => GetMessage('LM_AUTO_MAIN_LINEMEDIA_API_TECDOC_FINISHES_SOON') . (int) $tecdoc_left,
                   "TAG" => "LM_TECDOC",
                   "MODULE_ID" => "linemedia.auto",
                   "ENABLE_CLOSE" => "Y"
                );
                $ID = CAdminNotify::Add($ar);

                return;
            }

            CAdminNotify::DeleteByTag("LM_TECDOC");
        }
    }


    /**
     * Добавление кнопок на административную панель
     */
    public static function OnBeforeProlog_addAdminPanelButtons()
    {
        global $APPLICATION, $USER;

        if (!is_object($USER) || !$USER->IsAdmin()) {
            return;
        }

        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url['query'], $query);
        $query['lm_auto_debug'] = ($query['lm_auto_debug'] == 'Y') ? 'N' : 'Y';
        $url['query'] = http_build_query($query);
        $debug_url = $url['path'] . '?' . $url['query'];

        $APPLICATION->AddPanelButton(array(
            "HREF"      => $debug_url,
            "SRC"       => '/bitrix/themes/.default/icons/linemedia.auto/misc/debug_'.($query['lm_auto_debug'] == 'N' ? 'disable':'enable').'.png',
            "ALT"       => GetMessage('LM_AUTO_MAIN_DEBUG_BTN'),
            "TYPE"      => 'SMALL',
            "HINT"      => array('TEXT' => GetMessage('LM_AUTO_MAIN_DEBUG_BTN')),
            "MAIN_SORT" => 10000,
            "SORT"      => 100
        ));
    }
    
    /**
     * @return failure if ended up unsuccessfully
     * @param \CAdminList $list, $tableId
     * building up an admin menu depending on user privileges (linemedia pages)
     */
    /*
    public function OnBeforeProductsPageAdd_RenderOnUserRights(\CAdminList &$list, $tableId, $userPrivileges) {
            
        global $APPLICATION, $USER;
              
        if (!$USER->IsAdmin() && (bool) $instance = \Linemedia\Auto\Privilege\AbstractPrivilegeStrategy::getInstance($tableId)) {
            
            try {
                
                if ($instance->execute($list) == FALSE) {
                    throw new \Exception($instance::$errorMessage.' '.$instance::CLASS_NAME);
                }
            }
            catch (Exception $ex) {
                $APPLICATION->ThrowException($ex->getMessage());
                return false;
            }
        }
    }

    */
    
    public function OnAdminListDisplay_CheckPriceFormationAccess(&$list) {

//        $debug = true;
//        $type = 'linemedia_auto';
//        $IBLOCK_ID = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_DISCOUNT");
//        $table_id = "tbl_iblock_list_" . md5($type.".".$IBLOCK_ID);
//
//        if($list->table_id == $table_id) {
////            foreach($list->aRows as &$row) {
////                $row['bReadOnly'] = true;
////            }
//        }
    }
    
    
    
    
    /*
    * Add stat code to </body>
    */
    public function OnEndBufferContent_AddStatCode(&$html)
    {
	    //$html = LinemediaAutoStat::OnEndBufferContent($html);
    }
    
    // add AOP features
    public function OnBeforeProlog_addTransaction() {
        
        if(extension_loaded('aop')) {
        
            $onAfterDBQuery = function(AopJoinPoint $joinpoint) {
                $args = $joinpoint->getArguments();
                $sql = (string) $args[0];
                
                 // транзакции не требуют АОП !!!
//                // transactions
//                if(preg_match('#INSERT INTO b_sale_user_transact#is', $sql)) {
//                    $_DB = $joinpoint->getObject();
//	                $transaction_id = $_DB->LastID();
//
//	                CModule::IncludeModule('sale');
//	                $tr = CSaleUserTransact::GetByID($transaction_id);
//
//	                $userID = $tr['USER_ID'];
//	                $transactionType = $tr['NOTES'];
//
//	                if ((bool) \LinemediaAutoBranchesBranch::getAcceptableBranch($userID) && $transactionType == \LinemediaAutoTransactionTitle::DEPOSIT_FUNDS_DESCRIPTION_TRANS) {
//	                    foreach(GetModuleEvents("LinemediaAutoEventSale", "OnAfterAddTransact", true) as $arEvent)
//	                        if (ExecuteModuleEventEx($arEvent, array($transaction_id, &$tr)) === false)
//	                            return false;
//	                }
//                }
                
                
                //group save
                if(preg_match('#INSERT INTO b_group_subordinate#is', $sql) && !defined('LM_AUTO_EXEC_AOP_SUBORD')) {
                    define('LM_AUTO_EXEC_AOP_SUBORD', true);
                    $_DB = $joinpoint->getObject();
	                
	                preg_match('#VALUES \((.+?),#is', $sql, $matches);
	                $subordinate_id = (int) $matches[1];
	                
	                
	                $arSubGroups = array_map('intval', $_POST['subordinate_groups']);
	                $strSubordinateGroups = $_DB->ForSQL(implode(",", $arSubGroups));
	                
	                // rare query
	                $_DB->Query("ALTER TABLE  `b_group_subordinate` CHANGE  `AR_SUBGROUP_ID`  `AR_SUBGROUP_ID` TEXT NOT NULL", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	                $_DB->Query("UPDATE b_group_subordinate SET AR_SUBGROUP_ID = '".$strSubordinateGroups."' WHERE ID = $subordinate_id", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
                }
        
                
            };
            aop_add_after('CDatabase->Query()', $onAfterDBQuery);
        }
    }

    public function OnAfterUserAuthorize_setCurrencyParam($arUser) {

        global $USER;

        if($USER->GetID() == $arUser['user_fields']['ID']) {

            $USER->SetParam('CURRENCY', false);

            $rsUser = CUser::GetByID($arUser['user_fields']['ID']);
            $arUserData = $rsUser->Fetch();
            if(array_key_exists('UF_CURRENCY', $arUserData) && strlen($arUserData['UF_CURRENCY']) > 0) {

                $rs = CUserFieldEnum::GetList(array(), array(
                    "ID" => $arUserData['UF_CURRENCY'],
                ));
                if($arCurrency = $rs->Fetch()) {
                    if(strlen($arCurrency['VALUE']) == 3) $USER->SetParam('CURRENCY', $arCurrency['VALUE']);
                }
            }

            // check groups settings
            if(strlen($USER->GetParam('CURRENCY')) < 3) {

                $LM_AUTO_MAIN_GROUP_CURRENCY = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GROUP_CURRENCY'));

                if(is_array($LM_AUTO_MAIN_GROUP_CURRENCY) && count($LM_AUTO_MAIN_GROUP_CURRENCY) > 0) {

                    $groups = $USER->GetUserGroupArray();

                    foreach($LM_AUTO_MAIN_GROUP_CURRENCY as $group_id => $currency) {

                        if(strlen($currency) == 3 && in_array($group_id, $groups)) {
                            $USER->SetParam('CURRENCY', $currency);
                        }
                    }
                }
            }

            if(strlen($USER->GetParam('CURRENCY')) < 3 && CModule::IncludeModule('currency')) {
                $USER->SetParam('CURRENCY', CCurrency::GetBaseCurrency());
            }
        }
    }


    public function OnAfterUserUpdate_CheckCurrencyParam(&$arFields) {

        global $USER;

        if(is_object($USER) && $USER->IsAuthorized() && $arFields['ID'] == $USER->GetID()) {

            $USER->SetParam('CURRENCY', false);

            if(array_key_exists('UF_CURRENCY', $arFields) && strlen($arFields['UF_CURRENCY']) > 0) {
                $rs = CUserFieldEnum::GetList(array(), array(
                    "ID" => $arFields['UF_CURRENCY'],
                ));
                if($arCurrency = $rs->Fetch()) {
                    if(strlen($arCurrency['VALUE']) == 3) $USER->SetParam('CURRENCY', $arCurrency['VALUE']);
                }
            }

            // check groups settings
            if(strlen($USER->GetParam('CURRENCY')) < 3) {

                $LM_AUTO_MAIN_GROUP_CURRENCY = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GROUP_CURRENCY'));

                if(is_array($LM_AUTO_MAIN_GROUP_CURRENCY) && count($LM_AUTO_MAIN_GROUP_CURRENCY) > 0) {

                    $groups = $USER->GetUserGroupArray();

                    foreach($LM_AUTO_MAIN_GROUP_CURRENCY as $group_id => $currency) {

                        if(strlen($currency) == 3 && in_array($group_id, $groups)) {
                            $USER->SetParam('CURRENCY', $currency);
                        }
                    }
                }
            }

            if(strlen($USER->GetParam('CURRENCY')) < 3 && CModule::IncludeModule('currency')) {
                $USER->SetParam('CURRENCY', CCurrency::GetBaseCurrency());
            }
        }
    }

}

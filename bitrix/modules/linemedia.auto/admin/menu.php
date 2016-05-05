<?php

IncludeModuleLangFile(__FILE__);

$sModuleId = "linemedia.auto";
$sMGRight = $APPLICATION->GetGroupRight($sModuleId);


if ((!defined('NO_LM_AUTO_MAIN_MODULE_INSTALLED') || NO_LM_AUTO_MAIN_MODULE_INSTALLED != true)&& IsModuleInstalled($sModuleId)) {

    if (!CModule::IncludeModule($sModuleId)) {
        return;
    }
	
	global $USER;
    
	// Получение ID сайта.
    $rsSite = CSite::GetList($by='SORT', $order='ASC', array('ACTIVE' => 'Y', 'DEF' => 'Y'));
    $arSite = $rsSite->Fetch();

    $curUserGroup = $USER->GetUserGroupArray();

    if ($sMGRight != 'D') {
        if ($sMGRight >= 'O') {
		
			$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_ORDERS);

			$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
			
//			CModule::IncludeModule('sale');
//			$arStatusList = False;
//			$arFilter = array("LID" => LANG, "ID" => "N");
//			$arGroupByTmpSt = false;
//            $saleModulePermissions = $APPLICATION->GetGroupRight("sale");
//			//if ($saleModulePermissions < "W") {
//				$arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
//				$arFilter["PERM_UPDATE"] = "Y";
//				$arGroupByTmpSt = array("ID", "NAME", "MAX" => "PERM_UPDATE");
//			//}
//			$dbStatusList = CSaleStatus::GetList(
//				array(),
//				$arFilter,
//				$arGroupByTmpSt,
//				false,
//				array("ID", "NAME")
//			);
//			$arStatusList = $dbStatusList->Fetch();

            /*
             * Если уж проверяем доступ на создание заказа в статусе N то используем готовый метод
             */
            $status_right = LinemediaAutoProductStatus::getStatusesPermissions('N', $GLOBALS["USER"]->GetUserGroupArray());

			
			if($maxRole > 'D') // $maxRole != 'D' && $maxRole != 'V' && $maxRole != 'W'
			{
				$add_order = array();

				if($status_right["PERM_UPDATE"] == "Y" || $USER -> IsAdmin()) // $arStatusList["PERM_UPDATE"] == "Y" || $USER -> IsAdmin()
				{
					$add_order = array(
						'url'           => 'linemedia.auto_sale_order_new.php?lang='.LANGUAGE_ID.'&LID='.$arSite['ID'],
						'more_url'      => array(
												'linemedia.auto_sale_order_new.php',
											),
						'text'          => GetMessage('LM_AUTO_GLOBAL_MENU_CREATE_ORDER_TITLE'),
						'title'         => GetMessage('LM_AUTO_GLOBAL_MENU_CREATE_ORDER_DESCTIPTION'),
						'icon'          => 'linemedia.auto_menu_icon_order_create',
						'page_icon'     => 'linemedia.auto_page_icon_order_create',
						'module_id'     => 'linemedia.auto',
						'items_id'      => 'menu_linemedia.auto_order_create',
					);
				}
				
				$aMenu[] = array(
					'parent_menu'       => 'global_menu_linemedia.auto',
					'section'           => 'linemedia.auto.php',
					'sort'              => 1,
					'url'               => 'linemedia.auto_sale_orders_list.php?lang='.LANGUAGE_ID,
					'more_url'          => array(
												'linemedia.auto_sale_orders_list.php',
												'linemedia.auto_sale_order_detail.php',
												'linemedia.auto_sale_order_print.php',
												'linemedia.auto_sale_order_edit.php'
											),
					'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_ORDERS_TITLE'),
					'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_ORDERS_DESCRIPTION'),
					'icon'              => 'linemedia.auto_menu_icon_order',
					'page_icon'         => 'linemedia.auto_page_icon_order',
					'module_id'         => 'linemedia.auto',
					'items_id'          => 'menu_linemedia.auto_order',
					'dynamic'           => false,
					'items' => array(
						$add_order,
					),
				);
				
//				if($maxRole != "O" && $maxRole != "P" && $maxRole != "Q" && $maxRole != "X")
//				{
//					unset($aMenu[count($aMenu)-1]['items']);
//				}
			}
        }

        if ($sMGRight > 'O') {

			$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_STATISTICS);
			$maxStatRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);

			if($maxStatRole != 'D')
			{
				$searchStatistics = array(
				'url'               => 'linemedia.auto_search_statistics.php?lang='.LANGUAGE_ID,
				'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_SEARCH_STATISTICS_TITLE'),
				'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_SEARCH_STATISTICS_DESCRIPTION'),
				'icon'              => 'linemedia.auto_menu_icon_search_statistics',
				'page_icon'         => 'linemedia.auto_page_icon_search_statistics',
				'module_id'         => 'linemedia.auto',
				'more_url'          => array(
									'linemedia.auto_search_statistics.php',
								),
				);
			}

            $arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_SUPPLIERS);
            $maxSupplierRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
            if($maxSupplierRole != 'D' && $maxStatRole != 'D')
			//if($USER->IsAdmin())
            {
                $supplierRefuseStat = array(
                    'url'               => 'linemedia.auto_suppliers_refusal_statistics.php?lang='.LANGUAGE_ID,
                    'text'              => GetMessage('LM_AUTO_SUPPLIERS_REFUSAL_STAT_TITLE'),
                    'title'             => GetMessage('LM_AUTO_SUPPLIERS_REFUSAL_STAT_DESCRIPTION'),
                    'icon'              => 'linemedia.auto_menu_icon_search_statistics',
                    'page_icon'         => 'linemedia.auto_menu_icon_search_statistics',
                    'module_id'         => 'linemedia.auto',
                    'items_id'          => 'menu_linemedia.auto_supplier_statistics',
                );
                
                
                $supplierPartsStat = array(
                    'url'               => 'linemedia.auto_suppliers_stat.php?lang='.LANGUAGE_ID,
                    'text'              => GetMessage('LM_AUTO_SUPPLIERS_STAT_TITLE'),
                    'title'             => GetMessage('LM_AUTO_SUPPLIERS_STAT_DESCRIPTION'),
                    'icon'              => 'linemedia.auto_menu_icon_search_statistics',
                    'page_icon'         => 'linemedia.auto_menu_icon_search_statistics',
                    'module_id'         => 'linemedia.auto',
                    'items_id'          => 'menu_linemedia.auto_supplier_statistics',
                );
            }
			
			$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_CUSTOM_FIELDS);
			$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
			if($maxRole != 'D')
			{
				$customFields = array(
				'url'               => 'linemedia.auto_custom_fields.php?lang='.LANGUAGE_ID,
				'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_CUSTOM_FIELDS_TITLE'),
				'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_CUSTOM_FIELDS_DESCRIPTION'),
				'icon'              => 'linemedia.auto_menu_icon_custom_fields',
				'page_icon'         => 'linemedia.auto_page_icon_custom_fields',
				'module_id'         => 'linemedia.auto',
				'items_id'          => 'menu_linemedia.auto_custom_fields',
				'more_url'          => array(
										'linemedia.auto_custom_fields.php',
									),
				);
			}
			
			$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_PRODUCTS);
			$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
			if($maxRole != 'D')
			{
				$spare = array(
				'url'               => 'linemedia.auto_products.php?lang='.LANGUAGE_ID,
				'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_PRODUCTS_TITLE'),
				'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_PRODUCTS_DESCRIPTION'),
				'icon'              => 'linemedia.auto_menu_icon_products',
				'page_icon'         => 'linemedia.auto_page_icon_products',
				'module_id'         => 'linemedia.auto',
				'items_id'          => 'menu_linemedia.auto_products',
				'more_url'          => array(
										'linemedia.auto_part_edit.php',
									),
				);
			}

			$accounts = array();
            $trans = array();
            $price_form = array();
            
            
            $arTasksFilterPrices = array("BINDING" => LM_AUTO_ACCESS_BINDING_PRICES);            
            $maxRolePrices = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilterPrices, true);
            
            $arTasksFilterTrans = array("BINDING" => LM_AUTO_ACCESS_BINDING_FINANCE);            
            $maxRoleTrans = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilterTrans);
        
            
            if($maxRoleTrans != 'D')
            {
                $accounts = array(
                                'url'               => 'linemedia.auto_account.php?lang='.LANGUAGE_ID,
                                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_FINANCE_ACC_TITLE'),
                                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_FINANCE_ACC_DESCRIPTION'),
                                'icon'              => '',
                                'page_icon'         => '',
                                'sort'              => 10,
                                'module_id'         => 'linemedia.auto',
                                'items_id'          => 'menu_linemedia.auto_account',
                                'more_url'          => array(
                                                            'linemedia.auto_account_edit.php'
                                                        ),
                            );
                $trans = array(
                                'url'               => 'linemedia.auto_transaction.php?lang='.LANGUAGE_ID,
                                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_FINANCE_TRANS_TITLE'),
                                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_FINANCE_TRANS_DESCRIPTION'),
                                'icon'              => '',
                                'page_icon'         => '',
                                'sort'              => 20,
                                'module_id'         => 'linemedia.auto',
                                'items_id'          => 'menu_linemedia.auto_transaction',
                                'more_url'          => array(
                                                            'linemedia.auto_transaction_edit.php'
                                                        ),
                            );
            }            
            
            
			if($maxRolePrices != 'D' || $maxRoleTrans != 'D') {
			     
			    $aMenu[] = array(
			        'parent_menu'       => 'global_menu_linemedia.auto',
			        'section'           => 'linemedia.auto.php',
			        'sort'              => 20,
			        'url'               => 'linemedia.auto_account.php?lang='.LANGUAGE_ID,
			        'more_url'          => array(),
			        'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_FINANCE_TITLE'),
			        'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_FINANCE_DESCRIPTION'),
			        'icon'              => 'linemedia.auto_menu_icon_vin',
			        'page_icon'         => 'linemedia.auto_menu_icon_vin',
			        'module_id'         => 'linemedia.auto',
			        'items_id'          => 'menu_linemedia.auto_finance',
			        'dynamic'           => false,
			        'items' => array(			             
			           $accounts,
                       $trans,
                       $price_form,
			        )
			    );
			}
			
			
            $arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_VIN);
            $maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
            if($maxRole != 'D')
            {
                $aMenu[] = array(
                    'parent_menu'       => 'global_menu_linemedia.auto',
                    'section'           => 'linemedia.auto.php',
                    'sort'              => 10,
                    'url'               => 'linemedia.auto_vin_iblock_list.php?lang='.LANGUAGE_ID,
                    'more_url'          => array(
                                                'linemedia.auto_vin_iblock_list.php',
                                                'linemedia.auto_vin_iblock_show.php',
                                            ),
                    'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_VIN_IBLOCK_TITLE'),
                    'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_VIN_IBLOCK_DESCRIPTION'),
                    'icon'              => 'linemedia.auto_menu_icon_vin',
                    'page_icon'         => 'linemedia.auto_page_icon_vin',
                    'module_id'         => 'linemedia.auto',
                    'items_id'          => 'menu_linemedia.auto_vin',
                    'dynamic'           => false,
                );
            }
			
			
            $LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK'));
            $curUserGroup = $USER->GetUserGroupArray();
            $intr = array();
            $intr = array_intersect($LM_AUTO_MAIN_MENU_SHOW_PAGE_PRICE_CHECK, $curUserGroup); 
            
            if($USER->IsAdmin() || $intr)
			{
				$price_check =  array(
                            'url'               => 'linemedia.auto_price_check.php?lang='.LANGUAGE_ID,
                            'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_PRICE_CHECK_TITLE'),
                            'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_PRICE_CHECK_DESCRIPTION'),
                            'icon'              => 'linemedia.auto_menu_icon_price_check',
                            'page_icon'         => 'linemedia.auto_page_icon_price_check',
                            'module_id'         => 'linemedia.auto',
                            'items_id'          => 'menu_linemedia.auto_price_check',
                        );
			}
			else $price_check = array();
			
			if($USER->IsAdmin())
			{
				$agents_check =  array(
                            'url'               => 'linemedia.auto_agents_check.php?lang='.LANGUAGE_ID,
                            'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_AGENTS_CHECK_TITLE'),
                            'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_AGENTS_CHECK_DESCRIPTION'),
                            'icon'              => 'linemedia.auto_menu_icon_agents_check',
                            'page_icon'         => 'linemedia.auto_page_icon_agents_check',
                            'module_id'         => 'linemedia.auto',
                            'items_id'          => 'menu_linemedia.auto_agents_check',
                        );
			}
			else $agents_check = array();
			
			if($USER->IsAdmin())
			{
				$pricelist_check =  array(
                            'url'               => 'linemedia.auto_csv_check.php?lang='.LANGUAGE_ID,
                            'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_CSV_CHECK_TITLE'),
                            'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_CSV_CHECK_DESCRIPTION'),
                            'icon'              => 'linemedia.auto_menu_icon_csv_check',
                            'page_icon'         => 'linemedia.auto_page_icon_csv_check',
                            'module_id'         => 'linemedia.auto',
                            'items_id'          => 'menu_linemedia.auto_csv_check',
                        );
			}			                      
            
            if($USER->IsAdmin())
			{
				$system_check = array(
                            'url'               => 'linemedia.auto_check.php?lang='.LANGUAGE_ID,
                            'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_CHECK_TITLE'),
                            'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_CHECK_DESCRIPTION'),
                            'icon'              => 'linemedia.auto_menu_icon_check',
                            'page_icon'         => 'linemedia.auto_page_icon_check',
                            'module_id'         => 'linemedia.auto',
                            'items_id'          => 'menu_linemedia.auto_check',
                        );
			}
			else $system_check = array();

			if(is_array($searchStatistics) || is_array($spare) || $USER->IsAdmin())
			{
				$check_menu = array(
					'parent_menu'       => 'global_menu_linemedia.auto',
					'section'           => 'linemedia.auto_service',
					'sort'              => 9000,
					'url'               => 'linemedia.auto_check.php?lang='.LANGUAGE_ID,
					'more_url'          => array(),
					'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_SERVICE_TITLE'),
					'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_SERVICE_DESCRIPTION'),
					'icon'              => 'linemedia.auto_menu_icon_service',
					'page_icon'         => 'linemedia.auto_page_icon_service',
					'module_id'         => 'linemedia.auto',
					'items_id'          => 'menu_linemedia.auto_service',
					'dynamic'           => true,
					'items' => array(
							$price_check,
							$agents_check,
							$pricelist_check,
							$spare,
							$searchStatistics,
							$supplierRefuseStat,
							$supplierPartsStat,
	//                        array(
	//                            'url'               => 'linemedia.auto_suppliers_refusal_statistics.php?lang='.LANGUAGE_ID,
	//                            'text'              => GetMessage('LM_AUTO_SUPPLIERS_REFUSAL_STAT_TITLE'),
	//                            'title'             => GetMessage('LM_AUTO_SUPPLIERS_REFUSAL_STAT_DESCRIPTION'),
	//                            'icon'              => 'linemedia.auto_menu_icon_search_statistics',
	//                            'page_icon'         => 'linemedia.auto_menu_icon_search_statistics',
	//                            'module_id'         => 'linemedia.auto',
	//                            'items_id'          => 'menu_linemedia.auto_supplier_statistics',
	//                        ),
							$customFields,
							$system_check,
					),
				);
			}

            /*
            * проверка удалённых поставщиков
            */
            if (IsModuleInstalled('linemedia.autoremotesuppliers') && $USER->IsAdmin()) {
				$check_menu['items'][] = array(
					'url'               => 'linemedia.autoremotesuppliers_check.php?lang='.LANGUAGE_ID,
                    'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_REMOTESUPPLIERSCHECK_TITLE'),
                    'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_REMOTESUPPLIERSCHECK_DESCRIPTION'),
                    'icon'              => 'linemedia.autoautoremotesuppliers_menu_icon_check',
                    'page_icon'         => 'linemedia.autoautoremotesuppliers_page_icon_check',
                    'module_id'         => 'linemedia.autoautoremotesuppliers',
                    'items_id'          => 'menu_linemedia.autoautoremotesuppliers_check',
				);
			}
			
			
			
			/**
			* Настройка статусов
			*/
			if(CUser::IsAdmin()) {
				$check_menu['items'][] = array(
					'url'               => 'linemedia.auto_settings_statuses.php?lang='.LANGUAGE_ID,
	                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_SETTINGS_STATUSES_TITLE'),
	                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_SETTINGS_STATUSES_TITLE'),
	                'icon'              => 'linemedia.auto_settings_statuses_menu_icon_check',
	                'page_icon'         => 'linemedia.auto_settings_statuses_page_icon_check',
	                'module_id'         => 'linemedia.auto',
	                'items_id'          => 'menu_linemedia.auto_settings_statuses',
				);
			}
			
			

            if(is_array($check_menu)) $aMenu[] = $check_menu;

			$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_WORDFORMS);
			$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);

			if($maxRole != 'D')
			{
			
				$aMenu[] = array(
					'parent_menu'       => 'global_menu_linemedia.auto',
					'section'           => 'linemedia.auto.php',
					'sort'              => 8000,
					'url'               => 'linemedia.auto_wordforms.php?lang='.LANGUAGE_ID,
					'more_url'          => array(
												'linemedia.auto_wordforms_add.php?lang='.LANGUAGE_ID
											),
					'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_WORDFORMS_TITLE'),
					'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_WORDFORMS_DESCRIPTION'),
					'icon'              => 'linemedia.auto_menu_icon_wordforms',
					'page_icon'         => 'linemedia.auto_page_icon_wordforms',
					'module_id'         => 'linemedia.auto',
					'items_id'          => 'menu_linemedia.auto_wordforms',
					'dynamic'           => false,
				);
			}

			if($USER->IsAdmin())
			{
				$aMenu[] = array(
					'parent_menu'       => 'global_menu_linemedia.auto',
					'section'           => 'linemedia.auto.php',
					'sort'              => 9000,
					'url'               => 'linemedia.linemedia_account.php?lang='.LANGUAGE_ID,
					'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_LINEMEDIA_ACCOUNT_TITLE'),
					'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_LINEMEDIA_ACCOUNT_DESCRIPTION'),
					'icon'              => 'linemedia.auto_menu_icon_linemedia_account',
					'page_icon'         => 'linemedia.auto_page_icon_linemedia_account',
					'module_id'         => 'linemedia.auto',
					'items_id'          => 'menu_linemedia.linemedia_account',
				);
			}

            $aMenu[] = array(
                'parent_menu'       => 'global_menu_linemedia.auto',
                'section'           => 'linemedia.auto.php',
                'sort'              => 20000,
                'url'               => 'linemedia.auto_help.php?lang='.LANGUAGE_ID,
                'more_url'          => array(),
                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_HELP_TITLE'),
                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_HELP_DESCRIPTION'),
                'icon'              => 'linemedia.auto_menu_icon_help',
                'page_icon'         => 'linemedia.auto_page_icon_help',
                'module_id'         => 'linemedia.auto',
                'items_id'          => 'menu_linemedia.auto_help',
                'dynamic'           => false,
            );

			//Страница со списком покупателей
			$LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS'));
            $curUserGroup = $USER->GetUserGroupArray();
            $intr = array();
            $intr = array_intersect($LM_AUTO_MAIN_MENU_SHOW_PAGE_BUERS, $curUserGroup); 

            if($intr || $USER->IsAdmin()) {

                $aMenu[] = array(
                    'parent_menu'       => 'global_menu_linemedia.auto',
                    'section'           => 'linemedia.user.php',
                    'sort'              => 100,
                    'url'               => 'linemedia.auto_buyers_list.php?lang='.LANGUAGE_ID,
                    'more_url'          => array(
                        'linemedia.auto_buyers_list.php',
                        //'linemedia.auto_managers_edit.php',
                    ),
                    'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_BUYERS_TITLE'),
                    'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_MANAGERS_DESCRIPTION'),
                    'icon'              => 'user_menu_icon',
                    'page_icon'         => 'user_menu_icon',
                    'module_id'         => 'linemedia.auto',
                    'items_id'          => 'menu_linemedia.auto_buyers',
                    'dynamic'           => false,
                );
            }
		
			//Страница со списком менеджеров			
            $managersID = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');
			
			$accessToManagers = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_SHOW_PAGE_MANAGERS'));
            $curUserGroup = $USER->GetUserGroupArray();
            $intr = array();
            $intr = array_intersect($accessToManagers, $curUserGroup);
			
            if(intval($managersID) > 0 && ($intr || $USER->IsAdmin())) {

                $aMenu[] = array(
                    'parent_menu'       => 'global_menu_linemedia.auto',
                    'section'           => 'linemedia.user.php',
                    'sort'              => 100,
                    'url'               => 'linemedia.auto_managers.php?lang='.LANGUAGE_ID,
                    'more_url'          => array(
                        'linemedia.auto_managers.php',
                        'linemedia.auto_managers_edit.php',
                    ),
                    'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_MANAGERS_TITLE'),
                    'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_MANAGERS_DESCRIPTION'),
                    'icon'              => 'user_menu_icon',
                    'page_icon'         => 'user_menu_icon',
                    'module_id'         => 'linemedia.auto',
                    'items_id'          => 'menu_linemedia.auto_users',
                    'dynamic'           => false,
                );
            }

            // Настройка главного модуля
            $isShowHowToEarnMore = true;
            $arHideHowToEarnMore = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_MENU_HIDE_EARN_MORE'));
            if (count(array_intersect($curUserGroup, $arHideHowToEarnMore)) > 0) $isShowHowToEarnMore = false;

			$class = 'auto_text_modules_not_active';
			$classPrice = 'price';
			$url = 'linemedia.auto_buy.php';

			if ($isShowHowToEarnMore &&
                (!IsModuleInstalled('linemedia.api') && !CModule::IncludeModule('linemedia.api') ||
				!IsModuleInstalled('linemedia.autoanalogssimple') && !CModule::IncludeModule('linemedia.autoanalogssimple') ||
				!IsModuleInstalled('linemedia.autobranches') && !CModule::IncludeModule('linemedia.autobranches') ||
				!IsModuleInstalled('linemedia.autogarage') && !CModule::IncludeModule('linemedia.autogarage') ||
				!IsModuleInstalled('linemedia.autoglass') && !CModule::IncludeModule('linemedia.autoglass') ||
				!IsModuleInstalled('linemedia.automodifier') && !CModule::IncludeModule('linemedia.automodifier') ||
				!IsModuleInstalled('linemedia.autooil') && !CModule::IncludeModule('linemedia.autooil') ||
				!IsModuleInstalled('linemedia.autoprice') && !CModule::IncludeModule('linemedia.autoprice') ||
				!IsModuleInstalled('linemedia.autoremotesuppliers') && !CModule::IncludeModule('linemedia.autoremotesuppliers') ||
				!IsModuleInstalled('linemedia.autosphinx') && !CModule::IncludeModule('linemedia.autosphinx') ||
				!IsModuleInstalled('linemedia.autosuppliers') && !CModule::IncludeModule('linemedia.autosuppliers') ||
				!IsModuleInstalled('linemedia.autoto') && !CModule::IncludeModule('linemedia.autoto') ||
				!IsModuleInstalled('linemedia.autotyres') && !CModule::IncludeModule('linemedia.autotyres'))
            ) {

				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.buy_global_menu',
					'sort'          => 30000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=all',
					'text'          => "<span class='auto_see_also'>" . GetMessage('LM_AUTO_HOW_GET_MONEY') . '</span>',
					'title'         => "<span class='auto_see_also'>" . 'Как зарабатывать больше?' . '</span>',
					'icon'          => '',
					'page_icon'     => '',
					'module_id'     => 'linemedia.api',
					'items_id'      => 'menu_linemedia.api',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.api module menu, if module doesn't install
			 * + fix encoding problems
	         */

			$currentEndoding = mb_internal_encoding();
			try {
				$api = new LinemediaAutoApiDriver();
				$accountInfo = $api->query('getAccountInfo', array());
			} catch (Exception $e) {}
			mb_internal_encoding($currentEndoding);

			$prices = array();
			foreach ($accountInfo['data']['payments']['services']['modules'] as $module => $info) {
				$prices[$module] = $info['price'];
				unset($module);
				unset($info);
			}



			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.api') && !CModule::IncludeModule('linemedia.api')) {
				$price = $prices['api'] ? ' ' . "<span class='$classPrice'>" . $prices['api'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.api',
					'sort'          => 1100000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=api',
					'text'          => "<span class='$class'>" . GetMessage('LM_API_TITLE') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_API_DESCTIPTION') . '</span>',
					'icon'          => 'linemedia.api_menu_icon_info_not_active',
					'page_icon'     => 'linemedia.api_page_icon_info_not_active',
					'module_id'     => 'linemedia.api',
					'items_id'      => 'menu_linemedia.api',
					'dynamic'       => false,
				);
			}

			/*
	         * Add autoanalogssimple module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autoanalogssimple') && !CModule::IncludeModule('linemedia.autoanalogssimple')) {
				$price = $prices['autoanalogssimple'] ? ' ' . "<span class='$classPrice'>" . $prices['autoanalogssimple'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.auto',
					'sort'          => 200000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autoanalogssimple',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_AS_TITLE'). $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_AS_DESCTIPTION') . '</span>',
					'icon'          => 'linemedia.autoanalogssimple_menu_icon_main_not_active',
					'page_icon'     => 'linemedia.autoanalogssimple_page_icon_main_not_active',
					'module_id'     => 'linemedia.autoanalogssimple',
					'items_id'      => 'menu_linemedia.autoanalogssimple',
					'dynamic'       => false,
				);
			}



			/*
	         * Add linemedia.autobranches module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autobranches') && !CModule::IncludeModule('linemedia.autobranches')) {
				$price = $prices['autobranches'] ? ' ' . "<span class='$classPrice'>" .  $prices['autobranches'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autobranches',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autobranches',
					'more_url'      => array(),
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_BRANCHES') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_BRANCHES_TITLE') . '</span>',
					'icon'          => 'linemedia.autobranches_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autobranches_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autobranches',
					'items_id'      => 'menu_linemedia.autobracnhes_manager_service',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autogarage module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autogarage') && !CModule::IncludeModule('linemedia.autogarage')) {
				$price = $prices['autogarage'] ? ' ' . "<span class='$classPrice'>" .  $prices['autogarage'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autogarage',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autogarage',
					'more_url'      => array(),
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_GARAGE') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_GARAGE_TITLE') . '</span>',
					'icon'          => 'linemedia.autogarage_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autogarage_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autogarage',
					'items_id'      => 'menu_linemedia.autobracnhes_manager_service',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autoglass module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autoglass') && !CModule::IncludeModule('linemedia.autoglass')) {
				$price = $prices['autoglass'] ? ' ' . "<span class='$classPrice'>" .  $prices['autoglass'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autoglass',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autoglass',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_GLASS_MANAGER') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_GLASS_MANAGER_TITLE') . '</span>',
					'icon'          => 'linemedia.autoglass_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autoglass_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autoglass',
					'items_id'      => 'menu_linemedia.autoglass_manager_service',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.automodifier module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.automodifier') && !CModule::IncludeModule('linemedia.automodifier')) {
				$price = $prices['automodifier'] ? ' ' . "<span class='$classPrice'>" .  $prices['automodifier'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.auto',
					'sort'          => 40000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=automodifier',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_MODIFIER_TITLE') . $price .  '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_MODIFIER_DESCTIPTION') . '</span>',
					'icon'          => 'linemedia.automodifier_menu_icon_main_not_active',
					'page_icon'     => 'linemedia.automodifier_page_icon_main_not_active',
					'module_id'     => 'linemedia.automodifier',
					'items_id'      => 'menu_linemedia.automodifier',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autooil module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autooil') && !CModule::IncludeModule('linemedia.autooil')) {
				$price = $prices['autooil'] ? ' ' . "<span class='$classPrice'>" .  $prices['autooil'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autooil',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autooil',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_OIL_MANAGER') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_OIL_MANAGER_TITLE') . '</span>',
					'icon'          => 'linemedia.autooil_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autooil_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autooil',
					'items_id'      => 'menu_linemedia.autooil_manager_service',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autoprice module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autoprice') && !CModule::IncludeModule('linemedia.autoprice')) {
				$price = $prices['autoprice'] ? ' ' . "<span class='$classPrice'>" .  $prices['autoprice'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autoprice',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autoprice',
					'more_url'      => array(),
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_PRICE') . $price .  '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_PRICE_TITLE') . '</span>',
					'icon'          => 'linemedia.autoprice_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autoprice_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autoprice',
					'items_id'      => 'menu_linemedia.autobracnhes_manager_service',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autoremotesuppliers module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autoremotesuppliers') && !CModule::IncludeModule('linemedia.autoremotesuppliers')) {
				$price = $prices['autoremotesuppliers'] ? ' ' . "<span class='$classPrice'>" .  $prices['autoremotesuppliers'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.auto',
					'sort'          => 1101000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autoremotesuppliers',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_TITLE') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_DESCTIPTION') . '</span>',
					'icon'          => 'linemedia.autoremotesuppliers_menu_icon_info_not_active',
					'page_icon'     => 'linemedia.autoremotesuppliers_page_icon_info_not_active',
					'module_id'     => 'linemedia.autoremotesuppliers',
					'items_id'      => 'menu_linemedia.autoremotesuppliers',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autosphinx module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autosphinx') && !CModule::IncludeModule('linemedia.autosphinx')) {
				$price = $prices['autosphinx'] ? ' ' . "<span class='$classPrice'>" .  $prices['autosphinx'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.auto',
					'sort'          => 1100000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autosphinx',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_SPHINX_TITLE') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_SPHINX_DESCTIPTION') . '</span>',
					'icon'          => 'linemedia.autosphinx_menu_icon_info_not_active',
					'page_icon'     => 'linemedia.autosphinx_page_icon_info_not_active',
					'module_id'     => 'linemedia.autosphinx',
					'items_id'      => 'menu_linemedia.autosphinx',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autosuppliers module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autosuppliers') && !CModule::IncludeModule('linemedia.autosuppliers')) {
				$price = $prices['autosuppliers'] ? ' ' . "<span class='$classPrice'>" .  $prices['autosuppliers'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autosuppliers',
					'sort'          => 190000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autosuppliers',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_SUPPLIERS_TITLE') . $price .  '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_SUPPLIERS_DESCTIPTION') . '</span>',
					'icon'          => 'linemedia.autosuppliers_menu_icon_not_active',
					'page_icon'     => 'linemedia.autosuppliers_page_icon_not_active',
					'module_id'     => 'linemedia.autosuppliers',
					'items_id'      => 'menu_linemedia.autosuppliers_in',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autoto module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autoto') && !CModule::IncludeModule('linemedia.autoto')) {
				$price = $prices['autoto'] ? ' ' . "<span class='$classPrice'>" .  $prices['autoto'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autoto',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autoto',
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_TO_MANAGER') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_TO_MANAGER_TITLE') . '</span>',
					'icon'          => 'linemedia.autoto_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autoto_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autoto',
					'items_id'      => 'menu_linemedia.autoto_manager_service',
					'dynamic'       => false,
				);
			}

			/*
	         * Add linemedia.autotyres module menu, if module doesn't install
	         */

			if ($isShowHowToEarnMore && !IsModuleInstalled('linemedia.autotyres') && !CModule::IncludeModule('linemedia.autotyres')) {
				$price = $prices['autotyres'] ? ' ' . "<span class='$classPrice'>" .  $prices['autotyres'] . GetMessage('LM_AUTO_RUB') . '</span>' : '';
				$aMenu[] = array(
					'parent_menu'   => 'global_menu_linemedia.auto',
					'section'       => 'linemedia.autotyres',
					'sort'          => 90000,
					'url'           => $url .'?lang='.LANGUAGE_ID.'&module=autotyres',
					'more_url'      => array(),
					'text'          => "<span class='$class'>" . GetMessage('LM_AUTO_TYRES') . $price . '</span>',
					'title'         => "<span class='$class'>" . GetMessage('LM_AUTO_TYRES_TITLE') . '</span>',
					'icon'          => 'linemedia.autotyres_menu_icon_manager_not_active',
					'page_icon'     => 'linemedia.autotyres_page_icon_manager_not_active',
					'module_id'     => 'linemedia.autotyres',
					'items_id'      => 'menu_linemedia.autotyres_manager_service',
					'dynamic'       => false,
				);
			}
        }
        if($sMGRight >='I') {
			$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT);
			$maxRole = LinemediaAutoGroup::getMaxPermissionId($sModuleId, $curUserGroup, $arTasksFilter);
			if($maxRole != 'D')
			{
		
            $aMenu[] = array(
                    'parent_menu'   => 'global_menu_linemedia.auto',
                    'section'       => 'linemedia.auto',
                    'sort'          => 200,
                    'url'           => 'linemedia.auto_task_list.php?lang='.LANGUAGE_ID,
                    'text'          => GetMessage('LM_AUTO_TITLE'),
                    'title'         => GetMessage('LM_AUTO_DESCTIPTION'),
                    'icon'          => 'linemedia.auto_menu_icon_main',
                    'page_icon'     => 'linemedia.auto_page_icon_main',
                    'module_id'     => 'linemedia.auto',
                    'items_id'      => 'menu_linemedia.auto',
                    'dynamic'       => false,
                    'items'         => array(
                        array(
                            "text"      => GetMessage('LM_AUTO_IMPORT_HISTORY_TITLE'),
                            "url"       => 'linemedia.auto_task_history.php?lang='.LANGUAGE_ID,
                            'icon'      => 'linemedia.auto_menu_icon_list',
                            'page_icon' => 'linemedia.auto_menu_icon_list',
                        ),
                    /*
                        array(
                            "text"      => GetMessage('LM_AUTO_ADD_TITLE'),
                            "url"       => 'linemedia.auto_task_add.php?lang='.LANGUAGE_ID,
                            'icon'      => 'linemedia.auto_menu_icon_add',
                            'page_icon' => 'linemedia.auto_page_icon_add',
                        ),
                        array(
                            "text"      => GetMessage('LM_AUTO_LIST_TITLE'),
                            "url"       => 'linemedia.auto_task_list.php?lang='.LANGUAGE_ID,
                            'icon'      => 'linemedia.auto_menu_icon_list',
                            'page_icon' => 'linemedia.auto_page_icon_list',
                        ),
                     */
                    ),
				);
			}
        }


        /*
         * Событие для других модулей.
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterAdminMenuBuild");
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

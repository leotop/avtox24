<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.autosuppliers");



if ($saleModulePermissions == 'D') {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


$POST_RIGHT = 'W';

$ERROR = false;

if (!CModule::IncludeModule("linemedia.auto")) {
	ShowError('LM_AUTO_MODULE_NOT_INSTALLED');
	return;
}

if (!CModule::IncludeModule("linemedia.autosuppliers")) {
	ShowError('LM_AUTOSUPPLIERS_MODULE_NOT_INSTALLED');
	return;
}

if (!CModule::IncludeModule("sale")) {
	ShowError('SALE_MODULE_NOT_INSTALLED');
	return;
}


global $USER;

if (!$USER) {
	$USER = new CUser();
}


/*
 * Настройки страницы
 */
$arPageSettings = array(
    'ORDERS_LIST_PAGE' => 'linemedia.auto_sale_orders_list.php',
    'OUT_PAGE' => 'linemedia.autosuppliers_out.php',
    'OUT_HISTORY_PAGE' => 'linemedia.autosuppliers_out_history.php',
);
/*
 * Cоздаём событие
 */
$events = GetModuleEvents('linemedia.autosuppliers', 'OnBeforeOutPageBuild');
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$arPageSettings));
}

/*
 * Статусы.
 */
$statuses = array();
$res = CSaleStatus::GetList();
while ($status = $res->Fetch()) {
	$statuses[$status['ID']] = $status;
}
/*
 * Доступные статусы для перевода и просмотра
 */
if($USER->IsAdmin()) {
    /*
    * Открываем для админа доступ ко всем статусам
    */
    foreach($statuses as &$status) {
        foreach($status as $sKey => $value) {
            if(strpos($sKey, 'PERM_') !== false && $value != 'Y') {
                $status[$sKey] = 'Y';
            }
        }
    }
    $arUserStatuses = $statuses;
} else {
    $arUserStatuses = array();
    $dbStatusListAvail = LinemediaAutoProductStatus::getAvailableStatuses("PERM_VIEW", array("PERM_STATUS", "PERM_STATUS_FROM"));
    while($arStatusListAvail = $dbStatusListAvail->GetNext()) {
        $arUserStatuses[$arStatusListAvail['ID']] = $arStatusListAvail;
    }
}
/*
 * Статусы в которые можно переводить
 */
$arUserTransferStatuses = array();
foreach ($arUserStatuses as $id => $status) {
    if($status['PERM_STATUS'] == 'Y' || $USER->IsAdmin()) $arUserTransferStatuses[$id] = $status;
}
/*
 * Статусы из которых можно переводить
 */
$arUserTransferFromStatuses = array();
foreach ($arUserStatuses as $id => $status) {
    if($status['PERM_STATUS_FROM'] == 'Y' || $USER->IsAdmin()) $arUserTransferFromStatuses[$id] = $status;
}
/*
 * Для каких статусов возможно отображение чекбоксов (разрешено переводить из этого статуса и есть статусы в которые можно перевести)
 */
$arStatusesCheckBox = array();
if(count($arUserTransferStatuses) > 0 && count($arUserTransferFromStatuses) > 0) {
    $arStatusesCheckBox = array_keys($arUserTransferFromStatuses);
}

$managGroup = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_MANAGERS');

/*
 * Список поставщиков для фильтра
 */
$suppliers = LinemediaAutoSupplier::GetList(array("SORT" => "ASC"), array(), false, false, array('ID', 'NAME', 'CODE', 'ACTIVE'), 'supplier_id');

/*
 * Прверка доступных поставщиков
 */
if(!is_array($suppliers) || count($suppliers) < 1) {
    $APPLICATION->AuthForm(GetMessage("LM_AUTO_SUPPLIERS_DENIED"));
}

/*
 * Проверка доступа главного модуля
 */
$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_ORDERS);
$curUserGroup = $USER->GetUserGroupArray();

$maxRole = LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $curUserGroup, $arTasksFilter);

if ($maxRole == 'D') {
    $APPLICATION->AuthForm("ORDERS_ACCESS_DENIED");
}



$first_statuses = unserialize(COption::GetOptionString('linemedia.autosuppliers', 'FIRST_STATUSES'));

$goods_status = (string) COption::GetOptionString('linemedia.autosuppliers', 'REQUESTED_GOODS_STATUS');

//if (IsModuleInstalled('linemedia.autobranches')) {
//
//    if (in_array($managGroup, $USER->GetUserGroupArray()) || $USER->IsAdmin()) {
//
//        $bUseBranchesModule = true;
//        $cur_val = COption::GetOptionString('linemedia.autobranches', 'LM_AUTO_BRANCHES_MANAGER_ORDER_ACCESS', 'all');
//        if ($cur_val!='all') {
//            $branches_iblock_id = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_IBLOCK_BRANCHES');
//            $arrBranchFilter =  array('IBLOCK_ID'=>$branches_iblock_id,
//                array(
//                    'LOGIC'=>'OR',
//                    array('PROPERTY_director'=>$GLOBALS['USER']->GetID()),
//                    array('PROPERTY_managers'=>$GLOBALS['USER']->GetID())
//                )
//            );
//            $rs = CIBlockElement::GetList(array(), $arrBranchFilter,0,0, array('ID'));
//            if ($rs && $rs->SelectedRowsCount() > 0) {
//
//                $branch = $rs->Fetch();
//                $br = new LinemediaAutoBranchesBranch($branch['ID']);
//                /*
//                 убираем скрытых для филиала поставщиков
//                */
//                $hidden = $br->getProperty('hide_suppliers');
//                if (is_array($hidden)) {
//                    foreach ($hidden['VALUE'] as $val) {
//                        unset($suppliers[ $val ]);
//                    }
//                }
//
//                if (in_array($cur_val, array('own', 'ownbranch')) && $br->getDirectorID()!==$GLOBALS['USER']->GetID()) {
//                    $rs = CUser::GetList(($by="id"), ($order="asc"), array('UF_MANAGER_ID'=>$GLOBALS['USER']->GetID()));
//                    $uids = array();
//                    while ($u = $rs->Fetch()) {
//                        $uids[] = $u['ID'];
//                    }//while
//                } else if (in_array($cur_val, array('own', 'ownbranch', 'branch')) && $br->getDirectorID()==$GLOBALS['USER']->GetID()) {
//                    $uids = $br->getBranchesUserIDsList();
//                }
//            }//if branch fetched
//        }//if not all
//    }
//
//} else {
//	$bUseBranchesModule = false;
//}//no branch module

/*
 * Заменяем старое ограничение проверкой доступа
 */
// F || P
$userAccessIds = false;
$orderAccessIds = false;
if($maxRole == LM_AUTO_MAIN_ACCESS_READ_OWN_BRANCH || $maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_BRANCH) {

    $arDealer = LinemediaAutoGroup::getUserDealerId();
    $branchId = $arDealer['UF_DEALER_ID'][0];

    if(intval($branchId) > 0) {

        $arOrdersIds = LinemediaAutoGroup::getBranchOrderIds($branchId);
        if(is_array($arOrdersIds) && count($arOrdersIds) > 0) {
            $orderAccessIds = $arOrdersIds;
        } else {
            $orderAccessIds = array();
        }
    }
} else if($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN) { // O
    $userAccessIds = array($USER->GetId());
} else if($maxRole == LM_AUTO_MAIN_ACCESS_READ_WRITE_OWN_CLIENTS) { // Q

    $arUsers = LinemediaAutoGroup::getUserClients();

    if(is_array($arUsers) && count($arUsers) > 0) {
        $userAccessIds = $arUsers;
    } else {
        $userAccessIds = array();
    }
}

/*
 * Дата: 24.09.13 13:37
 * Кто: Назарков Илья
 * Задача: 5353
 * Пояснения: Поучаем список пользователей у которых ектущий пользователь является менеджером
 */
//if (in_array($managGroup, $USER->GetUserGroupArray()) || $USER->IsAdmin()) {
//
//    $rsUsers = CUser::GetList(($by="id"), ($order="asc"), array('UF_MANAGER_ID'=>$GLOBALS['USER']->GetID()));
//    $usersIManager = array();
//    while ($u = $rsUsers->Fetch()) {
//        $usersIManager[] = $u['ID'];
//        unset($u);
//    }
//}

/*
 * Оформление заявок - смена статусов у товаров заказа.
 */
if (isset($_POST['basket'])) {

	CModule::IncludeModule('sale');

    $isError = false;

	$status = (string) $_POST['lm-auto-suppliers-status'];
	$note   = (string) $_POST['lm-auto-suppliers-note'];

    /*
     * Проверка доступа
     */
    // нельзя переводить в указанный статус
    if(!array_key_exists($status, $arUserStatuses) || $arUserStatuses[$status]['PERM_STATUS'] != 'Y') {
        $ERROR = str_replace('#STATUS#', $statuses[$status]['NAME'], GetMessage('LM_AUTO_SUPPLIERS_ERROR_TO_STATUS'));
        $isError = true;
    }

	$lmbasket = new LinemediaAutoBasket();

	$basket_ids = array_map('intval', array_filter((array) $_POST['basket']));

    // проверка перевода из статуса
    foreach($basket_ids as $basket_id) {
        $basketProps = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket_id, 'CODE' => 'status'));
        if($prop = $basketProps->Fetch()) {
            $oldStatus = $prop['VALUE'];
            if($arUserStatuses[$oldStatus]['PERM_STATUS_FROM'] != 'Y') {
                $ERROR = str_replace('#STATUS#', $statuses[$oldStatus]['NAME'], GetMessage('LM_AUTO_SUPPLIERS_ERROR_FROM_STATUS'));
                $isError = true;
            }
        }
    }

    if(!$isError) {

        /*
         * Смена статусов.
         */
        foreach ($basket_ids as $basket_id) {
            $lmbasket->statusItem($basket_id, $status);
        }


        /*
         * Список корзин
         */
        $baskets = array();
        $dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $basket_ids), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "DELAY", "PRICE", "WEIGHT"));
        while ($basket = $dbBasketItems->Fetch()) {
            $baskets[$basket['ID']] = $basket;
        }

        /*
         * Свойства.
         */
        $props = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket_ids));
        while ($prop = $props->Fetch()) {
            $baskets[$prop['BASKET_ID']]['PROPS'][$prop['CODE']] = $prop;
        }

        /*
         * Поставщики.
         */
        $arSuppliers = array();
        foreach ($baskets as $basket) {
            $supplier_id = $basket['PROPS']['supplier_id']['VALUE'];
            $arSuppliers[$supplier_id][$basket['ID']] = $basket;
        }

        /*
         * Добавляем заявки.
         */
        $requests    = array();
        $request_ids = array();
        foreach ($arSuppliers as $supplier_id => $baskets) {
            $request = new LinemediaAutoSuppliersRequest();
            $request_ids []= $request->add($supplier_id, $baskets, $status, '0', 'N', $note);
            $requests []= $request;
        }

        if ($_POST['set-mail'] == 'Y') {
            foreach ($requests as $request) {
                $exporter = new LinemediaAutoSuppliersRequestExporter();
                $exporter->setRequest($request);

                // Создадим файл.
                $file = $exporter->saveXLS();

                // Название файла.
                $filename = $exporter->getFileTitle();

                $supplier = new LinemediaAutoSupplier($request->get('supplier_id'));

                $email = $supplier->get('email');


                if (empty($email)) {
                    continue;
                }

                // Кому отправляем заявку.
                $user = CUser::GetByID($USER->GetID())->Fetch();

                // Отправка заявки.
                CEvent::Send('LM_AUTO_SUPPLIERS_REQUEST', $user['LID'], array(
                    'EMAIL'         => $email,
                    'ID'            => $request->getID(),
                    'TIME'          => date('H:i d.m.Y'),
                    'ATTACH'        => $file,
                    'ATTACH_NAME'   => $filename
                ));
            }
        }

        // Переход в историю заявок.
        $redirectPath = '/bitrix/admin/' . $arPageSettings['OUT_HISTORY_PAGE'];
        // ??????? ? ??????? ??????.
        $events = GetModuleEvents("linemedia.auto", "OnAdminShowOrdersListFilterReady");
        while ($arEvent = $events->Fetch()) {
            try {
                ExecuteModuleEventEx($arEvent, array(&$redirectPath));
            } catch (Exception $e) {
                throw $e;
            }
        }

        LocalRedirect($redirectPath.'?lang=ru&set_filter=Y&filter_ids='.implode(',', $request_ids));
        exit();
    } // if(!$isError) {
}

// Можно ли отправить почту при первичном открытии страницы (не было ajax)?
// по умолчанию выделены все поставщики, поэтому пробегаемся по всем поставщикам.

$disabled_mail = false;

$supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
foreach ($suppliers as $supplier) {
	if (empty($email)) {
		$disabled_mail = true;
	}
}

/*
 * AJAX действия
 */
if (isset($_GET['ajax'])) {
	$action = strval($_GET['ajax']);

	if ($action == 'mails') {
		// Поставщики.
		$supplier_ids = array_map('strval', array_filter((array) $_POST['supplier_id']));

		$disabled_mail = false;
		$supplier_urls = array();
		$supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
		foreach ($suppliers as $supplier) {
			$title          = trim($supplier['NAME']);
			$email          = trim($supplier['PROPS']['email']['VALUE']);
			$supplier_id    = strval($supplier['PROPS']['supplier_id']['VALUE']);

			if (!in_array($supplier_id, $supplier_ids)) {
				continue;
			}
			if (empty($email)) {
				$disabled_mail = true;
			}
			$url  = '<div class="lm-auto-suppliers-item-mail"><a class="lm-auto-supplier-mail"';
			$url .= 'target="_blank" href="/bitrix/admin/iblock_element_edit.php?ID='.$supplier['ID'].'&type=linemedia_auto&lang=ru&IBLOCK_ID='.$supplier_iblock_id.'">';
			$url .= $title.'</a> ('.((!empty($email)) ? ('<span class="lm-auto-supplier-has-mail">'.$email.'</span>') : ('<span class="lm-auto-supplier-not-mail">'.GetMessage('LM_AUTO_SUPPLIERS_SUPPLIER_NO_EMAIL').'</span>')).')</div>';
			$supplier_urls []= $url;
		}
		$html = implode('', $supplier_urls);

		$response = array('mail' => ($disabled_mail) ? ('Y') : ('N'), 'html' => $html);

		header('Content-type: text/json');
		echo json_encode($response);
	}


	if ($action == 'orders') {
		CModule::IncludeModule('sale');

		/*
		 * Фильтры
		 */

		// Поставщики.
		$supplier_ids   = array_map('strval', array_filter((array) $_POST['supplier_id']));

		// Заказы из фильтра
		$order_ids_filter = (int) $_REQUEST['order_ids'] ? array_map('intval', explode(',', $_REQUEST['order_ids'])) : false;

		if($order_ids_filter) {
			$basket_ids_user = array();

			$o_basket_ids_user = CSaleBasket::GetList(array(), array('ORDER_ID' => $order_ids_filter), false, false, array('ID'));

			while ($basket_user = $o_basket_ids_user->Fetch()) {

				$basket_ids_filter[]= (int) $basket_user['ID'];

				unset($basket_user);
			}

			unset($o_basket_ids_user);

			if(empty($basket_ids_filter)) {
				$basket_ids_filter = array(0);
			}
		}

		if(empty($basket_ids_filter)) {
			$basket_ids_filter = false;
		}

		//Пользователь
		$user_id_filter = (int) $_REQUEST['user_id'];

		// Оплата.
		$payed_only     = ($_POST['payed_only'] == 'Y');

		// Статусы.
		$basket_status  = $first_statuses;
		
		//$basket_status = array_map('strval', array_filter((array) $_POST['basket_status']));


		/*
		 * Получим список нужных корзин через свойство поставщика
		 */
		$basket_ids = array();
		$props = CSaleBasket::GetPropsList(array(), array('CODE' => 'supplier_id', 'VALUE' => $supplier_ids));
		while ($prop = $props->Fetch()) {
			$basket_ids []= $prop['BASKET_ID'];
		}

		if (empty($basket_ids)) {
			$basket_ids = false;
		}
		
		/*
		 * Получим нужные корзины заданного пользователя в фильтре
		 */
		if ($user_id_filter) {

			$order_ids_user = array();

			$o_order_ids_user = CSaleOrder::GetList(array(), array('USER_ID' => $user_id_filter), false, false, array('ID'));

			while ($order_user = $o_order_ids_user->Fetch()) {

				$order_ids_user []= (int) $order_user['ID'];

				unset($order_user);
			}

			unset($o_order_ids_user);

			if(!empty($order_ids_user)) {
				$basket_ids_user = array();

				$o_basket_ids_user = CSaleBasket::GetList(array(), array('ORDER_ID' => $order_ids_user), false, false, array('ID'));

				while ($basket_user = $o_basket_ids_user->Fetch()) {

					$basket_ids_user []= (int) $basket_user['ID'];

					unset($basket_user);
				}

				unset($o_basket_ids_user);
			}

			if (empty($basket_ids_user)) {
				$basket_ids_user = array(0);
			}

		}

		if (empty($basket_ids_user)) {
			$basket_ids_user = false;
		}


		/*
		 * Найдем пересечение всех выбранных корзин
		 */

		$basket_ids_result = false;
		$baskets_not_empty_array = false;

		if($basket_ids_user) {
			$baskets_not_empty_array = $basket_ids_user;
		}

		if($basket_ids) {
			$baskets_not_empty_array = $basket_ids;
		}

		if($basket_ids_filter) {
			$baskets_not_empty_array = $basket_ids_filter;
		}

		if ($baskets_not_empty_array) {
			$basket_ids_user = $basket_ids_user ?: $baskets_not_empty_array;
			$basket_ids_filter = $basket_ids_filter ?: $baskets_not_empty_array;
			if (empty($supplier_ids)) {
				$basket_ids = $baskets_not_empty_array;
			}

			$basket_ids_result = array_intersect($basket_ids, $basket_ids_user, $basket_ids_filter);
		}

		if(empty($basket_ids_result)) {
			$basket_ids_result = false;
		}

		
		/*
		 * Получим нужные корзины
		 * и список заказов
		 */
		$users = array();
		$orders = array();
		$result = array();
		$dbBasketItems = CSaleBasket::GetList(array(), array('ID' => $basket_ids_result, '!ORDER_ID' => false), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "WEIGHT", 'ORDER_ID'));
		while ($basket = $dbBasketItems->Fetch()) {
			/*
			 * Все свойства
			 */
			$props_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID']));
			while ($prop = $props_res->Fetch()) {
				$basket['PROPS'][$prop['CODE']] = $prop;
			}

			/*
			 * Отфильтруем корзины, которые ещё не оплачены
			 */
			
			if ($payed_only && $basket['PROPS']['payed']['VALUE'] != 'Y') {
				continue;
			}

			/*
			 * Отфильтруем корзины по статусу
			 */
			if (count($basket_status) > 0 && !in_array($basket['PROPS']['status']['VALUE'], $basket_status)) {
				continue;
			}

            /*
             * Проверим доступ к статусу на просмотр
             */
            if(!array_key_exists($basket['PROPS']['status']['VALUE'], $arUserStatuses)) {
                continue;
            }

			/*
			 * Получим заказ этой корзины
			 */
			$order_id = $basket['ORDER_ID'];
			if (!isset($orders[$order_id])) {
				$orders[ $order_id ] = CSaleOrder::GetByID($order_id);

				$user_id = $orders[ $order_id ]['USER_ID'];

				/*
				*   если используются филиалы, то отсекаем все ненужные заказы.
				*/
//				$cur_val = COption::GetOptionString('linemedia.autobranches', 'LM_AUTO_BRANCHES_MANAGER_ORDER_ACCESS', 'all');
//
//				if (in_array($managGroup, $USER->GetUserGroupArray()) || $USER->IsAdmin()) {
//
//				    if (!$GLOBALS['USER']->IsAdmin() &&  $bUseBranchesModule && $cur_val != 'all' && !in_array($user_id, $uids) && !in_array($user_id, $usersIManager)) {
//				        unset($orders[ $order_id ]);
//				        continue;
//				    }
//				}
                /*
                 * Проверим доступы. если $uids массив - то он содержит всех доступных пользователей
                 */
                if(is_array($userAccessIds) && !in_array($user_id, $userAccessIds)) {
                    unset($orders[ $order_id ]);
			        continue;
                }
                if(is_array($orderAccessIds) && !in_array($order_id, $orderAccessIds)) {
                    unset($orders[ $order_id ]);
                    continue;
                }

				if (!isset($users[$user_id])) {
					$user = CUser::GetByID($user_id);
					$users[$user_id] = $user->Fetch();

					$result[$user_id] = $users[$user_id];
				}
			} else {
				$user_id = $orders[$order_id]['USER_ID'];
			}
			$result[$user_id]['ORDERS'][$order_id]['ORDER'] = $orders[$order_id];
			$result[$user_id]['ORDERS'][$order_id]['BASKETS'][] = $basket;
		}//while basket

		/*
		 * Распечатка таблицы
		 */
		$t = 1;
		if (!empty($result)) {
			echo '<div class="lm-auto-suppliers-orders-desc">' . GetMEssage('LM_AUTO_SUPPLIERS_DESC') . '</div>';
		}
		echo '<table class="lm-auto-suppliers-orders">';
		foreach ($result as $user_id => $user) {
			echo '<tr class="user"><th>' . GetMessage('LM_AUTO_SUPPLIERS_USER') . ' <a href="/bitrix/admin/user_edit.php?ID='.  $user['ID'] .'&lang='.LANG.'">' . $user['EMAIL'] . '</a></th></tr>';
			foreach ($user['ORDERS'] as $order) {
				echo '<tr class="lm-auto-suppliers-order-link">';
				echo '<td>
                        <a target="_blank" href="/bitrix/admin/'.$arPageSettings['ORDERS_LIST_PAGE'].'?lang='.LANG.'&set_filter=Y&filter_id_from=' . $order['ORDER']['ID'] . '&filter_id_to=' . $order['ORDER']['ID'] . '">' . GetMessage('LM_AUTO_SUPPLIERS_ORDER') . ' N ' . $order['ORDER']['ID'] .  '</a>' . ' <small>(' . $order['ORDER']['DATE_INSERT']. ')</small>' . '</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<td>';
				echo '<table class="lm-auto-suppliers-order">';
				echo '<tr class="lm-auto-suppliers-order-header">';
				echo '<td>#</td>';
				echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_BRAND') . '</td>';
				echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_ARTICLE') . '</td>';
				echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_QUANTITY') . '</td>';
				echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_PRICE') . '</td>';
				echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_STATUS') . '</td>';
				echo '<td>' . GetMEssage('LM_AUTO_SUPPLIERS_SUPPLIER') . '</td>';
				echo '</tr>';
				foreach ($order['BASKETS'] as $basket_id => $basket) {
					echo '<tr>';
					echo '<td>' . $t . '</td>';
					echo '<td>' . $basket['PROPS']['brand_title']['VALUE'] . '</td>';
                    if(in_array($basket['PROPS']['status']['VALUE'], $arStatusesCheckBox)) {
                        echo '<td>
                                    <label><input type="checkbox" class="basket" name="basket[]" value="' . $basket['ID'] . '" data-supplier-id="' . $basket['PROPS']['supplier_id']['VALUE'] . '" data-article="' . $basket['PROPS']['article']['VALUE'] . '" data-quantity="' . $basket['QUANTITY'] . '" data-brand-title="' . $basket['PROPS']['brand_title']['VALUE'] . '" checked />';
                    } else {
                        echo '<td></td>';
                    }
					echo $basket['PROPS']['article']['VALUE'] . '</label></td>';
					echo '<td>' . $basket['QUANTITY'] . '</td>';
					echo '<td>' . $basket['PRICE'] . '</td>';
					echo '<td>' . $statuses[$basket['PROPS']['status']['VALUE']]['NAME'] . '</td>';
					echo '<td>' . $basket['PROPS']['supplier_title']['VALUE'] . '</td>';
					echo '</tr>';
					$t++;
				}
				echo '</table>';
				echo '</td>';
				echo '</tr>';
			}
		}
		echo '</table>';


	}
	

	/*
	 * Получить товары, для заявок
	 */
	if ($action == 'request') {
 
	    
		CModule::IncludeModule('sale');

		global $DB;

		
		/*
		 * Фильтр по поставщикам
		 */

		// Поставщики.
		$supplier_ids   = array_map('strval', array_filter((array) $_POST['supplier_id']));

		// Оплата.
		$payed_only     = ($_POST['payed_only'] == 'Y');

		// Статусы.
		$basket_status  = array_map(array($DB, 'ForSql'), array_map('strval', array_filter((array) $first_statuses)));


		if (count($supplier_ids) > 0) {
			$supplier_filter = " AND SUPPLIER_PROP.VALUE IN ('" . (implode("', '", $supplier_ids)) . "')";
		}

		if ($payed_only) {
			$payed_filter = " AND PAYED_PROP.VALUE = 'Y'";
		}

        /*
         * Доступы к статусам
         */
        $arStatusFilter = array();
		if (count($basket_status) > 0) {
            $arStatusFilter = array_intersect($basket_status, array_keys($arUserStatuses));
		} else {
            $arStatusFilter = array_keys($arUserStatuses);
        }
        if(count($arStatusFilter) > 0) {
            $status_filter = " AND STATUS_PROP.VALUE IN ('" . join("','", $arStatusFilter) . "')";
        } else {
            $status_filter = " AND STATUS_PROP.VALUE='-1'"; // нет доступных статусов
        }



		/*
		 * Получим список корзин у которых отсутствует свойство supplier_request_status
		 * или у которых оно установлено в "new"
		 */
		$sql = "SELECT b_sale_basket.ID
                FROM
                    `b_sale_basket`
                LEFT JOIN `b_sale_basket_props` SUPPLIER_PROP ON
                    SUPPLIER_PROP.BASKET_ID = b_sale_basket.ID
                    AND
                    SUPPLIER_PROP.CODE = 'supplier_id'
                LEFT JOIN `b_sale_basket_props` STATUS_PROP ON
                    STATUS_PROP.BASKET_ID = b_sale_basket.ID
                    AND
                    STATUS_PROP.CODE = 'status'
                LEFT JOIN `b_sale_basket_props` PAYED_PROP ON
                    PAYED_PROP.BASKET_ID = b_sale_basket.ID
                    AND
                    PAYED_PROP.CODE = 'payed'";
		/*
		*   если используются филиалы, то отсекаем все заказы, которые сделаны не теми пользователями, что приписаны к филиалу.
		*/
		/*
		 * Дата: 24.09.13 13:58
		 * Кто: Назарков Илья
		 * Задача: 5353
		 * Пояснения: Некорректная проверка, не определна переменная $user_id в этом if
		 */


		/*
		if (!$GLOBALS['USER']->IsAdmin() && $bUseBranchesModule && !empty($uids) && !in_array($user_id, $uids)) {
			$sql .= " INNER JOIN `b_sale_order` ON `b_sale_basket`.ORDER_ID=`b_sale_order`.ID";
			$branch_filter = ' AND `b_sale_order`.USER_ID IN ('.implode(',', $uids).')';
		} else {
			$branch_filter = '';
		}*/
		$sql .= "
                WHERE
                    1" . $supplier_filter . $status_filter . $payed_filter . $branch_filter;


		$res = $DB->Query($sql);

		/*
		 * Массив ID корзин
		 */
		$basket_ids = array();
		while ($basket = $res->Fetch()) {
			$basket_ids []= $basket['ID'];
		}

		// Заказы из фильтра
		$order_ids_filter = (int) $_REQUEST['order_ids'] ? array_map('intval', explode(',', $_REQUEST['order_ids'])) : false;

		if($order_ids_filter) {
			$basket_ids_user = array();

			$o_basket_ids_user = CSaleBasket::GetList(array(), array('ORDER_ID' => $order_ids_filter), false, false, array('ID'));

			while ($basket_user = $o_basket_ids_user->Fetch()) {

				$basket_ids_filter[]= (int) $basket_user['ID'];

				unset($basket_user);
			}

			unset($o_basket_ids_user);

			if(empty($basket_ids_filter)) {
				$basket_ids_filter = array(0);
			}
		}

		if(empty($basket_ids_filter)) {
			$basket_ids_filter = false;
		}

		//Пользователь
		$user_id_filter = (int) $_REQUEST['user_id'];


		/*
		 * Получим нужные корзины заданного пользователя в фильтре
		 */
		if ($user_id_filter) {

			$order_ids_user = array();

			$o_order_ids_user = CSaleOrder::GetList(array(), array('USER_ID' => $user_id_filter), false, false, array('ID'));

			while ($order_user = $o_order_ids_user->Fetch()) {

				$order_ids_user []= (int) $order_user['ID'];

				unset($order_user);
			}

			unset($o_order_ids_user);

			if(!empty($order_ids_user)) {
				$basket_ids_user = array();

				$o_basket_ids_user = CSaleBasket::GetList(array(), array('ORDER_ID' => $order_ids_user), false, false, array('ID'));

				while ($basket_user = $o_basket_ids_user->Fetch()) {

					$basket_ids_user []= (int) $basket_user['ID'];

					unset($basket_user);
				}

				unset($o_basket_ids_user);
			}

			if (empty($basket_ids_user)) {
				$basket_ids_user = array(0);
			}

		}

		if (empty($basket_ids_user)) {
			$basket_ids_user = false;
		}


		/*
		 * Найдем пересечение всех выбранных корзин
		 */

		$basket_ids_result = false;
		$baskets_not_empty_array = false;

		if($basket_ids_user) {
			$baskets_not_empty_array = $basket_ids_user;
		}

		if($basket_ids) {
			$baskets_not_empty_array = $basket_ids;
		}

		if($basket_ids_filter) {
			$baskets_not_empty_array = $basket_ids_filter;
		}

		if ($baskets_not_empty_array) {
			$basket_ids_user = $basket_ids_user ?: $baskets_not_empty_array;
			$basket_ids_filter = $basket_ids_filter ?: $baskets_not_empty_array;
			if (empty($supplier_ids)) {
				$basket_ids = $baskets_not_empty_array;
			}

			$basket_ids_result = array_intersect($basket_ids, $basket_ids_user, $basket_ids_filter);
		}

		if(empty($basket_ids_result)) {
			$basket_ids_result = false;
		}

		if (count($basket_ids_result) == 0) {
			return;
		}

		/*
		 * Получим нужные корзины и список поставщиков
		 */
		$arSuppliers = array();
		$dbBasketItems = CSaleBasket::GetList(array(), array('ID' => $basket_ids_result, '!ORDER_ID' => false), false, false, array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "WEIGHT", 'ORDER_ID'));
		while ($basket = $dbBasketItems->Fetch()) {

			/*
			 * Дата: 24.09.13 13:55
			 * Кто: Назарков Илья
			 * Задача: 5353
			 * Пояснения: Фильтр при наличии модуля филиалы
			 */

			/*
             * Получим заказ этой корзины
             */
			$order_id = $basket['ORDER_ID'];
			$order = CSaleOrder::GetByID($order_id);
			$user_id = $order['USER_ID'];

			/*
			*   если используются филиалы, то отсекаем все ненужные заказы.
			*/
//			$cur_val = COption::GetOptionString('linemedia.autobranches', 'LM_AUTO_BRANCHES_MANAGER_ORDER_ACCESS', 'all');
//
//			if (in_array($managGroup, $USER->GetUserGroupArray()) || $USER->IsAdmin()) {
//
//			    if (!$GLOBALS['USER']->IsAdmin() && $bUseBranchesModule && $cur_val != 'all' && !in_array($user_id, $uids) && !in_array($user_id, $usersIManager)) {
//			        continue;
//			    }
//			}
            /*
             * Проверим доступы. если $uids массив - то он содержит всех доступных пользователей
             */
            if(is_array($userAccessIds) && !in_array($user_id, $userAccessIds)) {
                unset($orders[ $order_id ]);
                continue;
            }
            if(is_array($orderAccessIds) && !in_array($order_id, $orderAccessIds)) {
                unset($orders[ $order_id ]);
                continue;
            }

			$props_res = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket['ID'], 'CODE' => array('article', 'brand_title', 'supplier_id', 'status', 'payed')));
			while ($prop = $props_res->Fetch()) {
				$basket['PROPS'][$prop['CODE']] = $prop;
			}


			/*
			 * Отфильтруем корзины, которые ещё не оплачены
			 */

			if ($payed_only && $basket['PROPS']['payed']['VALUE'] != 'Y') {
				continue;
			}

			/*
			 * Получим поставщика и закешируем его
			 */
			$supplier_id = $basket['PROPS']['supplier_id']['VALUE'];
			if (!isset($arSuppliers[$supplier_id])) {
				$supplier = new LinemediaAutoSupplier($supplier_id);

				$arSuppliers[$supplier_id] = array(
					'title' => $supplier->get('NAME'),
					'baskets' => array()
				);
			}


			$part_key = $basket['PROPS']['article']['VALUE'] . '=|=' . $basket['PROPS']['brand_title']['VALUE'];

			$arSuppliers[$supplier_id]['baskets'][$part_key]['quantity']   += $basket['QUANTITY'];
			$arSuppliers[$supplier_id]['baskets'][$part_key]['ids'][]       = $basket['ID'];
			$arSuppliers[$supplier_id]['baskets'][$part_key]['article']     = $basket['PROPS']['article']['VALUE'];
			$arSuppliers[$supplier_id]['baskets'][$part_key]['brand_title'] = $basket['PROPS']['brand_title']['VALUE'];
            $arSuppliers[$supplier_id]['baskets'][$part_key]['status'] = $basket['PROPS']['status']['VALUE'];
		}

		
		/*
		 * Распечатаем HTML
		 */
		?>

		<? foreach ($arSuppliers as $supplier_id => $supplier) { ?>
			<div class="lm-auto-suppluers-request-for-supplier">
				<?= GetMessage('LM_AUTO_SUPPLIERS_SUPPLIER_REQUEST_TITLE')?> &laquo;<b><?= $supplier['title'] ?></b>&raquo;
			</div>
			<table class="lm-auto-supplier-request">
				<tr>
					<th></th>
					<th><?= GetMessage('LM_AUTO_SUPPLIERS_BRAND') ?></th>
					<th><?= GetMessage('LM_AUTO_SUPPLIERS_ARTICLE') ?></th>
					<th><?= GetMessage('LM_AUTO_SUPPLIERS_QANTITY') ?></th>
				</tr>
				<? foreach ($supplier['baskets'] as $basket) { ?>
					<? $id = $supplier_id . '-' . $basket['brand_title'] . '-' . $basket['article'] ?>
					<? $rand = 'q'.rand(1, 99999999) ?>
					<tr id="<?= $id ?>">
                        <? if(in_array($basket['status'], $arStatusesCheckBox)) { ?>
						    <td class="cbox"><input id="<?=$id?>-chk" data-quantity-id="<?= $rand ?>" data-basket-ids="<?=join(',', $basket['ids'])?>" type="checkbox" class="supplier" value="" checked /></td>
                        <? } else { ?>
                            <td></td>
                        <? } ?>
						<td class="brand"><label for="<?= $id ?>-chk"><?= $basket['brand_title'] ?></label></td>
						<td class="article"><label for="<?= $id ?>-chk"><?= $basket['article'] ?></label></td>
						<td class="quantity"><label for="<?= $id ?>-chk"><span id="<?= $rand ?>" class="quantity" data-max="<?= $basket['quantity']?>"><?= $basket['quantity'] ?></span>/<?= $basket['quantity'] ?></label></td>
					</tr>
				<? } ?>
			</table>
		<?}
	}

	exit();
}



// Выбрано по умолчанию
$selected_statuses = array('P');

// Отключено
$disabled_statuses = array(COption::GetOptionString('linemedia.autosuppliers', "REQUESTED_GOODS_STATUS", ""));

// Установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIERS_OUT_TITLE'));


// jquery
$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.2/jquery.min.js');


// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// создадим объект фильтра
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage('LM_AUTO_SUPPLIERS_FILTER_SUPPLIER'),
		GetMessage('LM_AUTO_SUPPLIERS_PAYED_ONLY'),
		GetMessage('LM_AUTO_SUPPLIERS_FILTER_ORDERS'),
		GetMessage('LM_AUTO_SUPPLIERS_FILTER_USER'),
	)
);

?>


	<h1><?= $APPLICATION->GetTitle() ?></h1>

<?= BeginNote();?>
<?= GetMessage('LM_AUTO_SUPPLIER_STEP_STATUSES') ?>: <br/>
	<ul>
		<? foreach ($first_statuses as $status) { ?>
			<li><b><?= $statuses[$status]['NAME'] ?></b> <? if (!empty($statuses[$status]['DESCRIPTION'])) { ?> (<?= $statuses[$status]['DESCRIPTION'] ?>)<? } ?>
            <? if(!array_key_exists($status, $arUserStatuses)) { ?><span style="color:red"><?=GetMessage('LM_AUTO_SUPPLIERS_NOT_VIEW_STATUS')?></span><? } ?>
            </li>
		<? } ?>
	</ul>
    <? if($ERROR) ShowError($ERROR); ?>
<?= EndNote() ?>

	<form action="" method="post" id="lm-auto-suppliers-frm">
		<? $oFilter->Begin(); ?>
		<tr>
			<td><?= GetMessage('LM_AUTO_SUPPLIERS_FILTER_SUPPLIER') ?>:</td>
			<td>
				<select name="supplier_id[]" id="supplier_id" multiple="multiple" size="8">
					<? foreach ($suppliers as $supplier) { ?>
						<? $id = $supplier['PROPS']['supplier_id']['VALUE'] ?>
						<option value="<?= $id ?>" selected="selected">
							<?= $supplier['NAME'] ?>
						</option>
					<? } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage('LM_AUTO_SUPPLIERS_PAYED_ONLY') ?>:</td>
			<td>
				<input type="checkbox" name="payed_only" id="payed_only" value="Y" checked="checked" />
			</td>
		</tr>
		<tr>
			<td><?= GetMessage('LM_AUTO_SUPPLIERS_FILTER_ORDER_IDS') ?>:</td>
			<td>
				<input type="text" name="order_ids" id="order_ids" value="<?=(string) $_REQUEST['order_ids']?>"/>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage('LM_AUTO_SUPPLIERS_FILTER_USER_ID') ?>:</td>
			<td>
				<input type="text" name="user_id" id="user_id" value=""/>
			</td>
		</tr>
		<? $oFilter->Buttons(array('table_id' => $sTableID, 'url' => '')) ?>
		<? $oFilter->End() ?>
	</form>

	<table class="filter-form">
		<tbody>
		<tr class="top">
			<td class="left"><div class="empty"></div></td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tbody>
					<tr>
						<td><div class="section-separator first"></div></td>
						<td></td>
						<td></td>
						<td><div class="separator"></div></td>
						<td></td>
						<td width="100%"></td>
						<td></td>
					</tr>
					</tbody>
				</table>
			</td>
			<td class="right"><div class="empty"></div></td>
		</tr>
		<tr>
			<td class="left"><div class="empty"></div></td>
			<td class="content"></td>
			<td class="right"><div class="empty"></div></td>
		</tr>
		<tr class="bottom">
			<td class="left"><div class="empty"></div></td>
			<td><div class="empty"></div></td>
			<td class="right"><div class="empty"></div></td>
		</tr>
		</tbody>
	</table>


<? /* Форма создания заявки (выбор для смены статусов) */ ?>

	<form action="" method="post" id="lm-suppliers-make-request">
		<div class="adm-detail-block" id="form_element_OUT_layout" style="min-width: 735px;">
			<div class="adm-detail-content-wrap">
				<div class="adm-detail-content">
					<div class="adm-detail-content-item-block lm-auto-suppliers-content-block">
						<div id="lm-auto-suppliers-request"></div>
						<div id="lm-auto-suppliers-orders"></div>
					</div>
				</div>

				<div class="adm-detail-content-btns-wrap adm-detail-content-btns-fixed adm-detail-content-btns-pin" id="form_element_OUT_buttons_div" style="left: 0px;">
                    <?
                    if(count($arUserTransferStatuses) > 0 && count($arUserTransferFromStatuses) > 0) { ?>
                        <div class="adm-detail-content-btns">
                            <!--div onclick="form_element_OUT.ToggleFix('bottom')" class="adm-detail-pin-btn" title="Прикрепить панель"></div-->
                            <input type="submit" value="<?= GetMessage('LM_AUTO_SUPPLIERS_SUPPLIER_REQUEST_SUBMIT') ?>" />
                            <?= GetMessage('LM_AUTO_SUPPLIERS_SUPPLIER_REQUEST_SUBMIT_NOTE') ?>&nbsp;
                            <select id="lm-auto-suppliers-status" name="lm-auto-suppliers-status">
                                <? foreach ($arUserTransferStatuses as $id => $status) { ?>
                                    <option value="<?= $id ?>" <?= ($id == $goods_status) ? ('selected') : ('') ?>>
                                        <?= $status['NAME'] ?> <? if (!empty($statuses[$id]['DESCRIPTION'])) { ?>(<?= $statuses[$id]['DESCRIPTION'] ?>)<? } ?>
                                    </option>
                                <? } ?>
                            </select>
                            <br/>

                            <div class="lm-auto-suppliers-request-note">
                                <?= GetMessage('LM_AUTO_SUPPLIERS_NOTE') ?>:<br/>
                                <textarea class="adm-textarea" name="lm-auto-suppliers-note" cols="50" rows="5"></textarea>
                            </div>
                            <br/>

                            <label for="lm-auto-suppliers-set-mail" title="<?= $disabled_mail ? GetMessage('LM_AUTO_SUPPLIERS_MAIL_NOTE_DISABLE'): GetMessage('LM_AUTO_SUPPLIERS_MAIL_NOTE') ?>">
                                <input type="checkbox" id="lm-auto-suppliers-set-mail" value="Y" name="set-mail" <?= ($disabled_mail) ? ('disabled') : ('') ?> />
                                <?= GetMessage('LM_AUTO_SUPPLIERS_MAIL') ?>
                            </label>
                            <div id="lm-auto-suppliers-mails"></div>
                        </div>
                    <? } else { ?>
                        <? if(count($arUserTransferStatuses) == 0) { ?><br /><span style="color:red;"><?= GetMessage('LM_AUTO_SUPPLIERS_TRANSFER_STATUS_DENIED') ?></span><? } ?>
                        <? if(count($arUserTransferFromStatuses) == 0) { ?><br /><span style="color:red;"><?= GetMessage('LM_AUTO_SUPPLIERS_TRANSFER_STATUS_FROM_DENIED') ?></span><? } ?>
                    <? } ?>
				</div>

			</div>
		</div>

	</form>



	<script type="text/javascript">
		$(document).ready(function() {

			$('#lm-auto-suppliers-frm .adm-filter-bottom .adm-btn').remove();

			update_windows();

			// Смена поставщика.
			$('#supplier_id').live('change', function() {
				update_windows();
			});

			// Смена статуса оплаты.
			$('#payed_only').live('change', function() {
				update_windows();
			});

			// Смена статуса оплаты.
			$('#order_ids').live('keyup paste', function() {
				update_windows();
			});

			// Смена статуса оплаты.
			$('#user_id').live('keyup paste', function() {
				update_windows();
			});

			// Сабмит формы.
			$('#lm-auto-suppliers-frm').live('submit', function() {
				update_windows();
				return false;
			});
		});


		function update_windows()
		{
			$('#lm-auto-suppliers-orders').html('');
			$('#lm-auto-suppliers-request').html('');

			update_orders();
			update_request();
			update_mails();
		}

		/*
		 * Обновление данных о заказах при смене фильтра
		 */
		function update_orders()
		{
			$.ajax({
				type: 'POST',
				url: "/bitrix/admin/<?=$arPageSettings['OUT_PAGE']?>?lang=<?= LANG ?>&ajax=orders",
				data: $('#lm-auto-suppliers-frm').serialize()
			}).done(function(html) {

					/*
					 Делаем кнопку "Создать заявки" активной
					 */
					var inputSubmit = $('.adm-detail-content-btns input');
					inputSubmit.removeAttr('disabled');

					/*
					 Если заявка пустая, то делаем кнопку "Создать заявки" неактивной
					 */
					if( html == '<table class="lm-auto-suppliers-orders"></table>') {
						inputSubmit.attr('disabled','disabled');
					}

					$('#lm-auto-suppliers-orders').html(html);
				});
		}


		/*
		 * Обновление данных о поставщиках
		 */
		function update_request()
		{
			$.ajax({
				type: 'POST',
				url: "/bitrix/admin/<?=$arPageSettings['OUT_PAGE']?>?lang=<?=LANG?>&ajax=request",
				data: $('#lm-auto-suppliers-frm').serialize()
			}).done(function(html) {
					$('#lm-auto-suppliers-request').html(html);
				});
		}


		/*
		 * Обновление данных о почтах поставщиков
		 */
		function update_mails()
		{
			$.ajax({
				type: 'POST',
				url: "/bitrix/admin/<?=$arPageSettings['OUT_PAGE']?>?lang=<?= LANG ?>&ajax=mails",
				data: $('#lm-auto-suppliers-frm').serialize()
			}).done(function(response) {

					/*
					 Если не указаны все email, то делаем невозможным выбрать отправку на почту поставщикам
					 + устанавливаем title
					 */
					$('#lm-auto-suppliers-set-mail').attr('disabled', (response.mail == 'Y'));

					var title = '';
					if (response.mail == 'Y') {
						title = "<?=GetMessage('LM_AUTO_SUPPLIERS_MAIL_NOTE_DISABLE')?>";
					} else {
						title = "<?=GetMessage('LM_AUTO_SUPPLIERS_MAIL_NOTE')?>";
					}

					$('#lm-auto-suppliers-set-mail').closest('label').attr('title', title);
					$('#lm-auto-suppliers-mails').html(response.html);
				});
		}


		/*
		 * Отметки галочками
		 */
		$("input.supplier").live("click", function(event){
			var baskets = $(this).data('basket-ids').toString().split(',');
			var checked = $(this).attr('checked') == 'checked';

			// Поменяем галочки корзинам
			for (var i in baskets) {
				var basket_id = baskets[i];
				$("input.basket[value=" + basket_id + "]").attr('checked', checked);
			}

			// Поменяем общее кол-во
			var quantity_id = $(this).data('quantity-id');
			var $quantity_field = $('#' + quantity_id);

			if (checked) {
				$quantity_field.html($quantity_field.data('max'));
			} else {
				$quantity_field.html('0');
			}

		});


		$("input.basket").live("click", function(event){
			// найдём строку в другой таблице
			var supplier_id = $(this).data('supplier-id');
			var article     = $(this).data('article');
			var brand_title = $(this).data('brand-title');

			var input_id = supplier_id + '-' + brand_title + '-' + article + '-chk';
			var $input = $('#' + input_id);

			// объект кол-ва в другой таблице
			var $quantity = $('#' + $input.data('quantity-id'));

			// сколько мы сейчас отметили
			var this_quantity = parseFloat($(this).data('quantity'));


			// уменьшить или прибавить?
			var plus = $(this).attr('checked') == 'checked';
			if (plus) {
				$quantity.html(parseFloat($quantity.html()) + this_quantity)
			} else {
				$quantity.html(parseFloat($quantity.html()) - this_quantity)
			}

			var max_quantity = parseFloat($quantity.data('max'));
			var checked_quantity = parseFloat($quantity.html());

			if (max_quantity == checked_quantity) {
				$input.prop('indeterminate', false);
				$input.attr('checked', true);
			} else {
				$input.prop('indeterminate', true);
			}

			if (checked_quantity == 0) {
				$input.prop('indeterminate', false);
				$input.attr('checked', false);
			}
		});
	</script>


<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

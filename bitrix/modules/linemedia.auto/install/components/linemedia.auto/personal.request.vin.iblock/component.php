<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*
 * ????????? ?????????? ?????? ?? VIN, ???????? ?? ??????????
 */

if (!CModule::IncludeModule('linemedia.auto')) {
	ShowError(GetMessage('LM_AUTOPORTAL_MODULE_NOT_INSTALL'));
	return;
}

if (!CModule::IncludeModule('iblock')) {
	ShowError(GetMessage('LM_IBLOCK_MODULE_NOT_INSTALL'));
	return;
}

if (!$USER->IsAuthorized()){
	$APPLICATION->AuthForm(GetMessage('LM_AUTO_VIN_ACCESS_DENIED'), false, false, 'N', false);
	return;
}

// ???????? ???? ???????? ????????????
// ?????? ?? ????????? ?????? 12944

//$arTasksFilter = array("BINDING" => LM_AUTO_ACCESS_BINDING_VIN);
//$curUserGroup = $USER->GetUserGroupArray();  //?????? ????? ????????????
//$maxRole = LinemediaAutoGroup::getMaxPermissionId("linemedia.auto", $curUserGroup, $arTasksFilter); //???????????? ???? ????????????
//if($maxRole == "D")
//{
 //   $APPLICATION->AuthForm(GetMessage('LM_AUTO_VIN_ACCESS_DENIED'), false, false, 'N', false);
 //   return;
//}

$res = CIBlock::GetList(Array(), Array("CODE"=>'lm_auto_vin', 'CHECK_PERMISSIONS' => 'N'));
$iblock = $res->Fetch();
$iblock_id = $iblock['ID'];

$arResult['IBLOCK'] = $iblock;
if($iblock['ID'] < 1) {
	ShowError(GetMessage('LM_AUTOPORTAL_IBLOCK_NOT_FOUND'));
	return;
}
$arParams["SET_PAGE_TITLE"] = ($arParams["SET_PAGE_TITLE"] == "N" ? "N" : "Y" );
$arParams["REDIRECT_NEW_TICKET_PAGE"] = ($arParams["REDIRECT_NEW_TICKET_PAGE"] == "N" ? "N" : "Y");


$arParams['MODIFICATIONS_SET'] = $arParams['MODIFICATIONS_SET'] ? : 'default';

$arDefaultUrlTemplates404 = array(
	"list" => "index.php",
	"edit" => "#ID#.php",
);

$arDefaultVariableAliases = array(
	"ID" => "ID"
);

$arDefaultVariableAliases404 = array(
);

$arComponentVariables = array('ID');
if ($arParams['SEF_MODE'] == 'Y') {
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$action = CComponentEngine::ParseComponentPath(
		$arParams['SEF_FOLDER'],
		$arUrlTemplates,
		$arVariables
	);

	if ($action == "edit") {
		$action = "edit";
	} elseif($_SERVER["REQUEST_METHOD"] == "POST" && (strlen($_REQUEST["save"]) > 0 || strlen($_REQUEST["apply"]) > 0)) {
		$action = "edit";
	} else {
		if (!$USER->IsAuthorized()) {
			$action = "edit";
		} else {
			$action = "list";
		}
	}


	CComponentEngine::InitComponentVariables($action, $arComponentVariables, $arVariableAliases, $arVariables);

	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
} else {
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$action = "";

	if (isset($arVariables["ID"]) && intval($arVariables["ID"]) >= 0) {
		$action = "edit";
	} else {
		if (!$USER->IsAuthorized()) {
			$action = "edit";
		} else {
			$action = "list";
		}
	}

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => array(
			"edit" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["ID"]."=#ID#",
			"list" => htmlspecialchars($APPLICATION->GetCurPage()),
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}


$api = new LinemediaAutoApiDriver();

if($arParams['AVAILABLE_TECDOC'] == 'N') {
	$arResult['AVAILABLE_TECDOC'] = false;
} else {
	try {
		$account_info = $api->getAccountInfo();
		$available_tecdoc = $account_info['data']['tecdoc']['available'];
	} catch (Exception $e) {
		$available_tecdoc = false;
	}
	$arResult['AVAILABLE_TECDOC'] = $available_tecdoc;
}

switch($action){
	case 'edit':

		if ($response['data']['tecdoc']['available'] != 1) {
			$fields_hidden = Array('brand_id', 'model_id', 'modification_id'); //????, ??????? ?????????? ? hidden
			$fields_main = Array('vin', 'year', 'month', 'extra', 'horsepower', 'displacement','brand', 'model','modification' ); //???????? ??????
		} else {
			$fields_hidden = Array('brand', 'brand_id', 'model', 'model_id', 'modification', 'modification_id'); //????, ??????? ?????????? ? hidden
			$fields_main = Array('vin', 'year', 'month', 'extra', 'horsepower', 'displacement'); //???????? ??????
		}

		$fields_disabled = Array('request', 'manager', 'answer', 'answer_date', 'answer_manager', 'site_id'); //????, ??????? ?????? ?? ???????

		$arResult['ID'] = (intval($arResult["VARIABLES"]["ID"]) > 0)?intval($arResult["VARIABLES"]["ID"]):0;
		$arResult["TICKET_LIST_URL"] = trim($arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list"]);
		$arResult["TICKET_LIST_URL"] = (strlen($arResult["TICKET_LIST_URL"]) > 0 ? htmlspecialchars($arResult["TICKET_LIST_URL"]) : "list.php");

		$arResult['IS_GARAGE'] = CModule::IncludeModule('linemedia.autogarage');
		$arResult['ERRORS'] = array();
		$arResult['HTML'] = array();

		//???????? ????????????
		$arResult['MANAGER'] = false;
		if(CModule::IncludeModule('linemedia.autobranches')){
			$branch_user = new LinemediaAutoBranchesUser( $USER->GetID() );
			$manager_id = $branch_user->getManagerID();
			if($manager_id > 0){
				$arResult['MANAGER'] = CUser::getByID($manager_id)->Fetch();
			}
			unset($manager_id, $branch_user);
		}


		if($arResult['ID'] > 0){
			//?????????? ????????? ??????
			$template = 'show';
			$item_res = CIBlockElement::GetByID($arResult['ID'])->GetNextElement();
			if($item_res === false){
				LocalRedirect($arResult["TICKET_LIST_URL"]);
				exit();
			}
			$arResult['DETAIL']['FIELDS'] = $item_res->GetFields();
			//???? ??? ????? ??????, ?????????? ?? ??????
			if($arResult['DETAIL']['FIELDS']['CREATED_BY'] != $USER->GetID()){
				LocalRedirect($arResult["TICKET_LIST_URL"]);
				exit();
			}
			$arResult['DETAIL']['PROPS'] = $item_res->GetProperties();
			if(is_array($arResult['DETAIL']['PROPS']) && count($arResult['DETAIL']['PROPS']) > 0){
				$arResult['DETAIL']['REQUEST'] = @unserialize($arResult['DETAIL']['PROPS']['request']['~VALUE']['TEXT']);
				$arResult['DETAIL']['MANAGER'] = (!empty($arResult['DETAIL']['PROPS']['manager']['VALUE']))?CUser::GetByID($arResult['DETAIL']['PROPS']['manager']['VALUE'])->Fetch():'';
				$arResult['DETAIL']['ANSWER'] = $arResult['DETAIL']['PROPS']['answer']['~VALUE'];
				$arResult['DETAIL']['ANSWER_DATE'] = $arResult['DETAIL']['PROPS']['answer_date']['VALUE'];
				$arResult['DETAIL']['ANSWER_MANAGER'] = (!empty($arResult['DETAIL']['PROPS']['answer_manager']['VALUE']))?CUser::GetByID($arResult['DETAIL']['PROPS']['answer_manager']['VALUE'])->Fetch():'';

				foreach($arResult['DETAIL']['PROPS'] AS $prop_key => $prop_value){
					if(in_array($prop_key, $fields_disabled) || in_array($prop_key, Array('brand_id', 'model_id', 'modification_id'))){
						unset($arResult['DETAIL']['PROPS'][$prop_key]);
					}
				}
				unset($prop_value, $prop_key);
			}
		}else{
			//?????????? ????? ??? ???????
			$template = 'add';

			/*
			 * ??????? ??? ?????? ???????: ????????? ??????????????? HTML ??? ??????.
			 */
			$events = GetModuleEvents("linemedia.auto", "OnVinShowIBlockHTML");
			while ($arEvent = $events->Fetch()) {
				$arResult['HTML'][] = ExecuteModuleEventEx($arEvent, array(CUser::getID(), true));
			}

			$properties = Array();
			$property_res = CIBlockProperty::GetList(Array('sort' => 'ASC'), Array('ACTIVE' => 'Y', 'IBLOCK_CODE' => 'lm_auto_vin'));
			while($property = $property_res->Fetch()){
				//????????? ???????? ???????
				if($property['CODE'] == 'request'){
					$arResult['REQUEST'] = $property;
					continue;
				}

				//?????????? ?? ?????? ????????
				if(in_array($property['CODE'], $fields_disabled)){
					continue;
				}

				//??????? ???????? ??? ??????? ???? "??????"
				if($property['PROPERTY_TYPE'] === 'L'){
					$prop_enum = CIBlockProperty::GetPropertyEnum($property['ID'], Array('SORT' => 'ASC'));
					while($enum = $prop_enum->Fetch()){
						$property['ENUM'][] = $enum;
					}
					unset($prop_enum, $enum);
				}

				//???????????? ????????
				if(!empty($_REQUEST['save']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
					if(isset($_REQUEST[$property['CODE']]) && !empty($_REQUEST[$property['CODE']])){
						$property['VALUE'] = trim($_REQUEST[$property['CODE']]);
					}elseif($property['IS_REQUIRED'] === 'Y'){
						$arResult['ERRORS'][] = GetMessage('LM_AUTO_VIN_ERROR_REQUIRED', Array('#FIELD#' => $property['NAME']));
					}
				}

				//???????? ????
				if(in_array($property['CODE'], $fields_main)){
					$arResult['FIELDS']['MAIN'][$property['CODE']] = $property;
				}
				//??????? ????
				if(in_array($property['CODE'], $fields_hidden)){
					$arResult['FIELDS']['HIDDEN'][$property['CODE']] = $property;
				}
				//?????????????? ????
				if(!in_array($property['CODE'], $fields_main) && !in_array($property['CODE'], $fields_hidden)){
					$arResult['FIELDS']['EXTRA'][$property['CODE']] = $property;
				}

				$properties[$property['CODE']] = $property;
			}
			unset($property_res);

			if (!empty($_REQUEST['save']) && $_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid()) {
				//???????? ???????????
				if(!$USER->IsAuthorized()){
					$arResult['ERRORS'][] = GetMessage('LM_AUTO_VIN_AUTH_ERROR');
				}
				//?????????? ???? ? ?????
				$arResult['AUTO_ADD'] = (isset($_REQUEST['AutoAdd']) && $_REQUEST['AutoAdd'] === 'Y')?'Y':'N';

				//?????????? ???????
				$arResult['REQUEST']['VALUE'] = Array();
				$request_isset = false;
				if(is_array($_REQUEST['request']['title']) && count($_REQUEST['request']['title']) > 0){
					foreach($_REQUEST['request']['title'] AS $field_key => $field_value){
						if(
							empty($_REQUEST['request']['title'][$field_key]) &&
							empty($_REQUEST['request']['art'][$field_key]) &&
							empty($_REQUEST['request']['quantity'][$field_key]) &&
							empty($_REQUEST['request']['comment'][$field_key])
						){
							continue;
						}
						$arResult['REQUEST']['VALUE'][] = Array(
							'title' => trim($_REQUEST['request']['title'][$field_key]),
							'art' => trim($_REQUEST['request']['art'][$field_key]),
							'quantity' => trim($_REQUEST['request']['quantity'][$field_key]),
							'comment' => trim($_REQUEST['request']['comment'][$field_key])
						);
						if(!empty($_REQUEST['request']['title'][$field_key]) || !empty($_REQUEST['request']['art'][$field_key])){
							$request_isset = true;
						}
					}
					unset($field_key, $field_value);
				}
				if($request_isset === false){
					$arResult['ERRORS'][] = GetMessage('LM_AUTO_VIN_ERROR_PARTS');
				}

				//??? ??????, ????????? ??????
				if (empty($arResult['ERRORS'])) {

					/*
					 * ??????? ??? ??????: ?????????? ???? ? ?????
					 */
					if ($arResult['IS_GARAGE'] && $arResult['AUTO_ADD'] === 'Y'){
						$events = GetModuleEvents("linemedia.auto", "OnVinAutoAdd");
						$event_fields = Array(
							'vin' => $properties['vin']['VALUE'],
							'brand' => $properties['brand']['VALUE'],
							'brand_id' => $properties['brand_id']['VALUE'],
							'model' => $properties['model']['VALUE'],
							'model_id' => $properties['model_id']['VALUE'],
							'modification' => $properties['modification']['VALUE'],
							'modification_id' => $properties['modification_id']['VALUE'],
							'extra' => $properties['extra']['VALUE']
						);

						while ($arEvent = $events->Fetch()) {
							ExecuteModuleEventEx($arEvent, Array($event_fields));
						}
						unset($event_fields);
					}

					$element_fields = Array(
						'IBLOCK_ID' => $iblock_id,
						'NAME' => GetMessage('LM_AUTO_VIN_REQUEST_NAME', Array('#VIN#' => $properties['vin']['VALUE'])),
						'ACTIVE' => 'Y'
					);

					foreach($properties AS $prop_code => $property){
						if(!empty($property['VALUE'])){
							if($property['USER_TYPE'] === 'HTML'){
								$element_fields['PROPERTY_VALUES'][$prop_code] = Array('VALUE' => Array('TYPE' => 'TEXT', 'TEXT' => $property['VALUE']));
							} else {
								$element_fields['PROPERTY_VALUES'][$prop_code] = $property['VALUE'];
							}
						}
					}
					unset($prop_code, $property);
					//??????
					$element_fields['PROPERTY_VALUES']['request'] =  Array('VALUE' => Array('TYPE' => 'TEXT', 'TEXT' => @serialize($arResult['REQUEST']['VALUE'])));
					//????????
					if($arResult['MANAGER']){
						$element_fields['PROPERTY_VALUES']['manager'] = $arResult['MANAGER']['ID'];
					}

					$element_fields['PROPERTY_VALUES']['site_id'] = SITE_ID; //??????? ????

					/*
					 * ??????? ??? ?????? ???????: ?? ?????????? ????????
					 */
					$events = GetModuleEvents('linemedia.auto', 'OnVinIBlockBeforeRequest');
					while ($arEvent = $events->Fetch()) {
						try {
							ExecuteModuleEventEx($arEvent, Array($element_fields));
						} catch (Exception $e) {
							throw $e;
						}
					}

					$oIblockElement = new CIBlockElement;
					if($element_id = $oIblockElement->Add($element_fields)){
						//??????? ????????? ???????
						$arResult['MESSAGE'] = GetMessage('LM_AUTO_VIN_ADD_SUCCESS');

						$user = $USER->GetByID($USER->GetID())->Fetch();

						//??????????? ????????????
						$arEventFields = Array(
							'ID'         => $element_id,
							'VIN'        => $properties['vin']['VALUE'],
							'EMAIL'      => $user['EMAIL'],
							'NAME'       => $user['NAME'],
							'LAST_NAME'  => $user['LAST_NAME'],
						);
						$eventId = CEvent::Send('LM_AUTO_VIN_IBLOCK_SEND_MAIL', SITE_ID, $arEventFields);
						unset($arEventFields);

						//??????????? ?????????????
						$arEventFields = Array(
							'ID'             => $element_id,
							'USER_ID'        => $user['ID'],
							'USER_NAME'      => $user['NAME'],
							'USER_LAST_NAME' => $user['LAST_NAME'],
							'USER_EMAIL'     => $user['EMAIL'],
							'DATE_CREATE'    => ConvertTimeStamp(false, 'FULL'),
							'VIN'            => $properties['vin']['VALUE'],
							'ADMIN_EDIT_URL' => '/bitrix/admin/linemedia.auto_vin_iblock_show.php?ID=' . $iblock_id,
						);
						$eventId = CEvent::Send('LM_AUTO_VIN_IBLOCK_SEND_MAIL_ADMIN', SITE_ID, $arEventFields);
						unset($arEventFields);

						//??????????? ?????????
						if($arResult['MANAGER'] !== false){
							$arEventFields = Array(
								'ID'             => $element_id,
								'USER_ID'        => $user['ID'],
								'USER_NAME'      => $user['NAME'],
								'USER_LAST_NAME' => $user['LAST_NAME'],
								'USER_EMAIL'     => $user['EMAIL'],
								'DATE_CREATE'    => ConvertTimeStamp(false, 'FULL'),
								'VIN'            => $properties['vin']['VALUE'],
								'MANAGER_EMAIL'  => $arResult['MANAGER']['EMAIL'],
								'ADMIN_EDIT_URL' => '/bitrix/admin/linemedia.auto_vin_iblock_show.php?ID=' . $iblock_id,
							);
							$eventId = CEvent::Send('LM_AUTO_VIN_IBLOCK_SEND_MAIL_MANAGER', SITE_ID, $arEventFields);
							unset($arEventFields);
						}

					}else{
                        $arResult['ERRORS'][] = GetMessage('LM_AUTO_VIN_ERROR_SEND') . ' ' . $oIblockElement->LAST_ERROR;
                    }

					/*
					 * ??????? ??? ?????? ???????: ????? ?????????? ????????
					 */
					$events = GetModuleEvents('linemedia.auto', 'OnVinIBlockAfterRequest');
					while ($arEvent = $events->Fetch()) {
						try {
							ExecuteModuleEventEx($arEvent, Array($element_id, $element_fields));
						} catch (Exception $e) {
							throw $e;
						}
					}

					unset($element_fields);
				}
			}
		}

		//????????????? ?????????
		if ($arParams["SET_PAGE_TITLE"] == "Y") {
			if (intval($arResult['ID']) > 0) {
				$APPLICATION->SetTitle(GetMessage("LM_AUTO_VIN_SHOW_TICKET_TITLE", array("#ID#" => $arResult['ID'])));
			} else {
				$APPLICATION->SetTitle(GetMessage("LM_AUTO_VIN_NEW_TICKET_TITLE"));
			}
		}
		break;
	case 'list':

		//????????? ???????
		$arFilter = array(
			'ACTIVE'            => 'Y',
			"IBLOCK_CODE"         => 'lm_auto_vin',
			"CREATED_BY"        => $USER->GetID(),
			"PROPERTY_site_id"  => SITE_ID,
		);

		//????????? ??????????
		$arResult["TICKET_EDIT_TEMPLATE"] = $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["edit"];
		$arResult["TICKET_EDIT_TEMPLATE"] = (strlen($arResult["TICKET_EDIT_TEMPLATE"]) > 0 ? htmlspecialchars($arResult["TICKET_EDIT_TEMPLATE"]) : "edit.php?ID=#ID#");
		$arParams["TICKET_SORT_ORDER"] = (strtoupper($arParams["TICKET_SORT_ORDER"]) == "ASC" ? "ASC" : "DESC" );
		$arParams["TICKETS_PER_PAGE"] = (intval($arParams["TICKETS_PER_PAGE"]) <= 0 ? 10 : intval($arParams["TICKETS_PER_PAGE"]));
		// Get Tickets
		CPageOption::SetOptionString("main", "nav_page_in_session", "N");
		$rsItems = CIBlockElement::GetList(Array('ID' => $arParams["TICKET_SORT_ORDER"]), $arFilter, false, false, Array('ID', 'IBLOCK_ID', 'CREATED_BY', 'DATE_CREATE', 'PROPERTY_VIN', 'PROPERTY_ANSWER', 'PROPERTY_ANSWER_DATE', 'PROPERTY_MANAGER'));
		$rsItems->NavStart($arParams["TICKETS_PER_PAGE"]);
		// Result array

		$arResult["TICKETS"] = Array();
		$arResult["TICKETS_COUNT"] = $rsItems->SelectedRowsCount();
		$arResult["NAV_STRING"] = $rsItems->GetPageNavString(GetMessage("LM_AUTO_VIN_PAGES"));
		$arResult["CURRENT_PAGE"] = htmlspecialchars($APPLICATION->GetCurPage());
		$arResult["NEW_TICKET_PAGE"] = htmlspecialchars(CComponentEngine::MakePathFromTemplate($arResult["TICKET_EDIT_TEMPLATE"], Array("ID" => "0")));

		if(intval($rsItems->SelectedRowsCount()) === 0 && $arParams["REDIRECT_NEW_TICKET_PAGE"] == "Y"){
			LocalRedirect($arResult["NEW_TICKET_PAGE"]);
			exit();
		}

		while ($arTicket = $rsItems->GetNext()) {
			$arTicket['TICKET_SHOW_URL'] = CComponentEngine::MakePathFromTemplate($arResult["TICKET_EDIT_TEMPLATE"], Array("ID" => $arTicket["ID"]));
			$arResult["TICKETS"][] = $arTicket;
		}

		if ($arParams["SET_PAGE_TITLE"] == "Y") {
			$APPLICATION->SetTitle(GetMessage("LM_AUTO_VIN_DEFAULT_TITLE"));
		}

		$template = 'list';
		break;
}

$this->IncludeComponentTemplate($template);



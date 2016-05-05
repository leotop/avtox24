<?php

/**
 * Linemedia API
 * API module
 * Price class
 *
 * @author  Krivorot Oleg (krivorot.oleg@gmal.com)  
 * @since   23/04/2015
 *
 * @link    http://www.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/**
* Класс для работы с прайсами поставщиков
*/
class CAPILinemediaAutoPrice extends CAPIFrame
{
    /**
    * Конструктор класса
    * 
    */
	public function __construct()
	{
		parent::__construct();
	}

    /**
    * Получение списка обновлений
    * 
    * @param array $filters
    * 
    */
    public function LinemediaAutoPrice_PriceImportTask($filters) 
	{
	/*
	* Проверка прав доступа к функции
	*/
	
	$this->checkPermission(__METHOD__);
	global $USER;

	$userPermission = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS));

	if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
		return GetMessage('ACCESS_DENIED');
	}
	
	if (!CModule::IncludeModule("linemedia.auto")) {
		ShowError('LM_AUTO MODULE NOT INSTALLED');
		return;
	}
	
	// Построение фильтра
	$filter_history = array();
	foreach($filters as $filter) {
		if ($filter['CODE'] == "ID") {
			$filter_history["ID"] = trim($filter['VALUE']);
		}
		if ($filter['CODE'] == "TASK_ID") {
			$filter_history["TASK_ID"] = trim($filter['VALUE']);
		}
		if ($filter['CODE'] == "SUPPLIER_ID") {
			$filter_history["SUPPLIER_ID"] = trim($filter['VALUE']);
		}
		if ($filter['CODE'] == ">=DATE") {
			$filter_history[">=DATE"] = trim($filter['VALUE']);
		}
		if ($filter['CODE'] == "<=DATE") {
			$filter_history["<=DATE"] = trim($filter['VALUE']);
		}
	}

    $order = array();

	// Выберем список.
	$res = LinemediaAutoImportHistory::getList($order, $filter_history);
	
	$elements = array();
	while ($element = $res->Fetch()) {
		$this->formatResponse($element, 'Struct_PriceImportTask');
		$elements[] = $element;
	}
	return $elements;
	}



    /**
    * Получение списка товаров из прайса
    * 
    * @param array $filters
    */
    public function LinemediaAutoPrice_PriceProductsList($filters) 
	{
	/*
	* Проверка прав доступа к функции
	*/
	
	$this->checkPermission(__METHOD__);
	global $USER;
	$userPermission = \LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $USER->GetUserGroupArray(), array('BINDING' => LM_AUTO_ACCESS_BINDING_PRODUCTS));
	if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
		return GetMessage('ACCESS_DENIED');
	}
	
	if (!CModule::IncludeModule("linemedia.auto")) {
		ShowError('LM_AUTO MODULE NOT INSTALLED');
		return;
	}
	
	// Построение фильтра
	$filter_history = array();
	$where_str = '';
	foreach($filters as $filter) {
		$supplierWhere = "";
		if ($filter['CODE'] == "ID") {
			$supplierWhere = 'id = "' . trim($filter['VALUE']) . '"';
		}		
		if ($filter['CODE'] == "SUPPLIER_ID") {
			$supplierWhere = 'supplier_id = "' . trim($filter['VALUE']) . '"';
		}
		if ($filter['CODE'] == "ARTICLE") {
			$supplierWhere = 'article = "' . trim($filter['VALUE']) . '"';
		}
		if ($filter['CODE'] == "ORIGINAL_ARTICLE") {
			$supplierWhere = 'original_article = "' . trim($filter['VALUE']) . '"';
		}		
		if ($filter['CODE'] == ">=MODIFIED") {
			$ts = MakeTimeStamp(trim($filter['VALUE']));
			if($ts) {
				$supplierWhere = "modified >= '" . FormatDate("Y-m-d H:i:s", $ts) . "'";
			}
		}
		if (strlen($supplierWhere) > 0) {
			if (strlen($where_str) > 0) {
				$where_str .= ' AND ('.$supplierWhere.')';
			} else {
				$where_str .= ' ('.$supplierWhere.')';
			}
		}
	}
	
	// Запрос к базе
	$database = new LinemediaAutoDatabase();
	if (strlen($where_str) > 0 ) {
		$dbData = $database->Query("SELECT * FROM `b_lm_products` WHERE $where_str ORDER BY `id` DESC");
	} else {
		$dbData = $database->Query("SELECT * FROM `b_lm_products` ORDER BY `id` DESC LIMIT 1, 10");
	}
	
	// Построение результата
	$elements = array();
	while ($element = $dbData->Fetch()) {
		$this->formatResponse($element, 'Struct_PriceProductsList');//'Struct_IblockElementProperty');
		$elements[] = $element;
	}
	return $elements;
	}
}

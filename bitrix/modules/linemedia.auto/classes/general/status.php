<?php
/**
 * Linemedia Autoportal
 * Sale status module
 * 
 *
 * @author  Linemedia
 * @since   10/10/2014
 *
 * @link    http://auto.linemedia.ru/
 */



IncludeModuleLangFile(__FILE__);

class LinemediaAutoStatus
{
	
	
	/**
	* Get sale statuses
	*
	* @return array
	*
	*/
	public static function getList($filter = array('LID' => LANGUAGE_ID))
	{
		if(!CModule::IncludeModule('sale')) {
			throw new Exception('No sale module');
		}
		$res = CSaleStatus::GetList(array('SORT' => 'ASC'), $filter);
		$statuses = array();
		while($status = $res->fetch()) {
			$status['PUBLIC_TITLE'] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_PUBLIC_RISTRICT_' . $status['ID']);
			if(trim($status['PUBLIC_TITLE']) == '') {
				$status['PUBLIC_TITLE'] = $status['NAME'];
			}
			
			$status['COLOR_ADMIN'] = trim(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_COLOR_' . $status['ID'], '#ffffff'));
			$status['COLOR_PUBLIC'] = trim(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_' . $status['ID'], '#ffffff'));
			
			
			$status['RELATIONS'] = (array) json_decode(COption::GetOptionString('linemedia.auto', 'LM_AUTO_STATUS_RELATIONS_' . $status['ID'], null));
			
			$statuses[$status['ID']] = $status;
		}
		return $statuses;
	}
	
	
	/**
	* Update status
	*
	* @param string $status_id Status ID
	* @param array $status Data
	*
	*/
	public static function Update($status_id, $status)
	{
		if(isset($status['PUBLIC_TITLE'])) {
			self::UpdatePublicTitle($status_id, $status['PUBLIC_TITLE']);
			unset($status['PUBLIC_TITLE']);
		}
		if(isset($status['COLOR_ADMIN'])) {
			self::UpdateColorAdmin($status_id, $status['COLOR_ADMIN']);
			unset($status['COLOR_ADMIN']);
		}
		if(isset($status['COLOR_PUBLIC'])) {
			self::UpdateColorPublic($status_id, $status['COLOR_PUBLIC']);
			unset($status['COLOR_PUBLIC']);
		}
		if(isset($status['RELATIONS'])) {
			self::UpdateRelations($status_id, $status['RELATIONS']);
			unset($status['RELATIONS']);
		}
		
		
		if(!CModule::IncludeModule('sale')) {
			throw new Exception('No sale module');
		}
		
		
		
		// langs
		$db_status = array();
		$db_lang = CLangAdmin::GetList($b, $o, array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch()) {
			$db_status['LANG'][] = array(
				'LID' => $arLang['LID'],
				'NAME' => $status['NAME'],
				'DESCRIPTION' => $status['DESCRIPTION'],
			);
		}
		
		$ok = CSaleStatus::Update($status_id, $db_status);
		if(!$ok) {
			global $APPLICATION;
			if($ex = $APPLICATION->GetException()) {
				ShowError($ex->GetString());
				exit;
			}
		}
		
	}
	
	
	
	
	
	
	/**
	* Set sale status public title
	*
	* @param string $status_id Status ID
	* @param string $public_title Public title
	*
	*/
	public static function updatePublicTitle($status_id, $public_title)
	{
		COption::SetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_PUBLIC_RISTRICT_' . $status_id, $public_title);
	}
	
	
	/**
	* Set sale status admin color
	*
	* @param string $status_id Status ID
	* @param string $color Color
	*
	*/
	public static function updateColorAdmin($status_id, $color)
	{
		COption::SetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_COLOR_' . $status_id, $color);
	}
	
	/**
	* Set sale status public color
	*
	* @param string $status_id Status ID
	* @param string $color Color
	*
	*/
	public static function updateColorPublic($status_id, $color)
	{
		COption::SetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_' . $status_id, $color);
	}
	
	
	/**
	* Set sale status relations
	*
	* @param string $status_id Status ID
	* @param array $relations Relations of type wholesale and retail
	*
	*/
	public static function updateRelations($status_id, $relations = array())
	{
		if(!isset($relations['wholesale'])) {
			throw new Exception($status_id . ' status relation doesnt contain wholesale relation');
		}
		if(!isset($relations['retail'])) {
			throw new Exception($status_id . ' status relation doesnt contain retail relation');
		}
		COption::SetOptionString('linemedia.auto', 'LM_AUTO_STATUS_RELATIONS_' . $status_id, json_encode($relations));
	}
	
	
	/**
	* Get sale status by id
	*
	* @param string $status_id Status ID
	*
	*/
	public static function GetByID($status_id)
	{
		$status = CSaleStatus::GetByID($status_id);
		$status['PUBLIC_TITLE'] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_PUBLIC_RISTRICT_' . $status_id);
		if(trim($status['PUBLIC_TITLE']) == '') {
			$status['PUBLIC_TITLE'] = $status['NAME'];
		}
		
		$status['COLOR_ADMIN'] = trim(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_COLOR_' . $status['ID'], '#ffffff'));
		$status['COLOR_PUBLIC'] = trim(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_' . $status['ID'], '#ffffff'));
		$status['RELATIONS'] = (array) json_decode(COption::GetOptionString('linemedia.auto', 'LM_AUTO_STATUS_RELATIONS_' . $status['ID'], null));
			
		return $status;
	}
	
	
	/**
	* Get sale public title by status id
	*
	* @param string $status_id Status ID
	*
	*/
	public static function getPublicTitleByStatusID($status_id)
	{
		$public_title = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_PUBLIC_RISTRICT_' . $status_id);
		if(trim($public_title) == '') {
			$status = CSaleStatus::GetByID($status_id);
			$public_title = $status['NAME'];
		}
		
		return $public_title;
	}
	
	
	/**
	* Get sale relations status by status id
	*
	* @param string $status_id Status ID
	* @param string $relation_type Type (wholesale or retail)
	*/
	public static function getRelationStatusID($status_id, $relation_type)
	{
		$relations = (array) json_decode(COption::GetOptionString('linemedia.auto', 'LM_AUTO_STATUS_RELATIONS_' . $status_id, null));
		return $relations[$relation_type];
	}
}
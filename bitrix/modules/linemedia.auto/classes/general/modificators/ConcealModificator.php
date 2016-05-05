<?php

/**
 * Linemedia Autoportal
 * Main module
 * up suppliers modificator
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */

/*
 * скрыть поставщиков
 */
class ConcealModificator implements CapableToModifyingSearchInterface
{
	
	/**
	 * suppliers denied
	 * @var string UNAVAILABLE_SUPPLIERS
	 */
	const UNAVAILABLE_SUPPLIERS = 'you havent enough privileges to access to suppliers in ConcealModificator';
	
	/**
	 * modif cond
	 * @var array $modifyingCondtitions
	 */
	private $modifyingConditions;
	
	/**
	 * logig cond
	 * @var array $logicCondtitions
	 */
	private $logicConditions;
	
	/**
	 * debug
	 * @var array $debugInfo
	 */
	private $debugInfo = array();
	
	/**
	 * @var boolen $deniedSuppliers
	 */
	private $deniedSuppliers = false;
	
	
	static $cache = array();
	
	/**
	 * @param array $configData
	 * @return void
	 */
	public function __construct(array $configData) {
		$this->logicConditions = $this->loadLogicConditions($configData);
		$this->modifyingConditions = $this->loadModifyingConditions($configData);
		$this->debugInfo[$this->modifyingConditions['modificatorID']] = array_merge($this->logicConditions, $this->modifyingConditions);
		
	}
	
	/**
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::applyModificatorToSearch()
	 */
	public function applyModificatorToSearch($searchOutcome, $traversal = null) {
		// TODO: Auto-generated method stub\

		if (!$searchOutcome instanceof \Iterator && !is_array($searchOutcome) ||
			!$traversal instanceof \Iterator && !is_array($traversal)
	    ) {
			throw new \InvalidArgumentException(sprintf(
					'%s: invalid argument was conveyed to %s. Expected obj implimenting \Iterator interface or array. %s given',
					__METHOD__,
					__FUNCTION__,
					$type = gettype($searchOutcome) == 'object' ? get_class($searchOutcome) : gettype($searchOutcome)
						
			));
		}
		
		$affectedGroups = $this->modifyingConditions['affectedGroups'];

		$isAffectedGroupExist = is_array($affectedGroups) ? true : false;
		$modificatorChunks = explode('#', $this->modifyingConditions['modificatorID']);
		
		foreach ($searchOutcome as $group => &$spares) {
			
			/*
			 * apply modificator strictly only to groups set in config (affected group by modif)
			 * on the contrary to all groups
			 */
			if ($isAffectedGroupExist && !in_array($group, $affectedGroups)) {
				continue;
			}

			$unaffected = array();
			$affected = array();
			$affectedAware = array();
			$count = 0;		
			$slicedIndexes = array();
			$markedIndexes = array();
			
			/*
			 * split array into 2 arrays first - affected by modificator, second unaffected
			* moreover marked each elements passed through modificator (type affected)
			*/
			foreach ($spares as $key => $spare) {
	
				/*
				 * if set up omitModificators and spare has one of them
				* put spare into unaffected array and go over to the next one
				*/
				$modificators = (array) array_values($spare['affectedModificators']);				
				if (count(array_intersect($modificators, $this->modifyingConditions['omitModificators'])) != 0)
				{
		        	$unaffected[$key] = $spare;
					continue;
				}
				
				/*
				 * if set up awareModificators and $spare has one
				* put spare into affected array and go over to the next one
				*/
				if (in_array($this->modifyingConditions['awareModificator'], $modificators)) {
					$spare['affectedModificators'][$this->modifyingConditions['modificatorID']] = end($modificatorChunks);
					$affectedAware[$key] = $spare;			
					continue;	
				}
				
				$modificators[$this->modifyingConditions['modificatorID']] = end($modificatorChunks);
				$spare['affectedModificators'] = $modificators;
				$affected[$key] = $spare;

			}

			/*
			 * array may be one of following types : either $affected or $affectedAware
			* if set up count of elements to be affected by modificator then take it into considiration
			*/
			$concealedSpares = count($affectedAware) == 0 ? $affected : $affectedAware;	

			foreach ($concealedSpares as $key => &$spare) {
				if ($this->modifyingConditions['affectedElementByModif'] != null && $count >= (int) $this->modifyingConditions['affectedElementByModif']) {
					$markedIndexes[] = $key;
					continue;
				}
				
				if (in_array($spare['supplier_id'], (array) $this->modifyingConditions['concealedSuppliers'])) {
					$slicedIndexes[] = $key;
					$count++;
					continue;
				}
				
				$markedIndexes[] = $key;
				
			}

			/*
			* Фильтр "скрыть поставщика", если в конфигураторе поиска во вкладке "Условия применения"
			* не пустое поле "Наличие поставщика". Если поле пустое, поставщик будет скрыт, если не пустое,
			* будет скрыт при условии, что хотя бы один поставщик из поля "Наличие поставщика" есть в массиве
			* $arResult компонента "linemedia.auto:search.results"
			*/
			$filter_existing_supplier = array();
			$flag = 'N';

			foreach($this->logicConditions as $v)
			{
				if($v['type'] == 'filter_existing_supplier')
					$filter_existing_supplier = $v['value'];
			}
			if(empty($filter_existing_supplier))
				$flag = 'Y';

			foreach($concealedSpares as $v)
			{
				if(in_array($v['supplier_id'], $filter_existing_supplier))
					$flag = 'Y';
			}

			/*///////////////////////////////////////////////*/

			/*
			 * replace spare if it has $markedIndexes or unset if have $slicedIndexes
			*/
			foreach ($searchOutcome[$group] as $key => $spare) {
				if (in_array($key, $slicedIndexes) && $flag == 'Y') {

					unset($searchOutcome[$group][$key]);
					continue;
				}
				
				if (in_array($key, $markedIndexes)) {				
    				$searchOutcome[$group][$key] = $concealedSpares[$key];
				}
			}
			
		}

		return $searchOutcome;
		
	}
	
	
	/**
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::isAlterationFeasible()
	 */
	public function isAlterationFeasible($searchOutcome) {
		
		//whether type $searchOutcome is type of \Iterator or array
		if (!$searchOutcome instanceof \Iterator && !is_array($searchOutcome)) {
			throw new \InvalidArgumentException(sprintf(
					'%s: invalid argument was conveyed to %s. Expected obj implimenting \Iterator interface or array. %s given',
					__METHOD__,
					__FUNCTION__,
					$type = gettype($searchOutcome) == 'object' ? get_class($searchOutcome) : gettype($searchOutcome)
		
			));
		}
		
	    
		if (!(bool) count($this->logicConditions)) {
			return true;
		}
		
		if (array_key_exists('unavailableSuppliers', $this->debugInfo)) {
			return false;
		}
		
		foreach ($this->logicConditions as $cond) {
		
			switch ($cond['type']) {
					
				case 'filter_user_group': {
				    
				    global $USER;
				    foreach ($cond['value'] as &$groupType) {
				        if (!(bool) strcmp($groupType, 'guest') && !$USER->IsAdmin()) {
				            $groupType = 2;
				        }
				    }
				    
					if (!(bool) array_intersect($USER->GetUserGroupArray(), $cond['value'])) {
						return false;
					}
					return true;
				}
					
				//part group condition
				case 'filter_part_group': {
					foreach ($cond['value'] as $group) {
						if (!in_array($group, array_keys($searchOutcome))) {
							return false;
						}
					}
					return true;
				}
					
				//numeric conditions
				case 'filter_overall_count_max':
				case 'filter_overall_count_min': {
					$count = 0;
					if (strcmp($cond['type'], 'filter_overall_count_min') == 0) {
						foreach (self::getAnalogGroups() as $group) {
		
							$group = 'analog_type_' . $group;
							if ($searchOutcome[$group] != NULL) {
								$count += count($searchOutcome[$group]);
							}
						}
						if ($count < $cond['value']) {
							return false;
						}
							
					}
					elseif (strcmp($cond['type'], 'filter_overall_count_max') == 0) {
						foreach (self::getAnalogGroups() as $group) {
		
							$group = 'analog_type_' . $group;
							if ($searchOutcome[$group] != NULL) {
								$count += count($searchOutcome[$group]);
							}
						}
						if ($count > $cond['value']) {
							return false;
						}
							
					}
		
					return true;
				}
					
				case 'filter_existing_supplier' : {
					foreach ($searchOutcome as $group => $spares) {
						foreach ($spares as $spare) {
							if (in_array($spare['supplier_id'], $cond['value'])) {
								$key = array_search($spare['supplier_id'], $cond['value']);
								unset($cond['value'][$key]);
							}
		
							if (empty($cond['value'])) {
								return true;
							}
						}
					}
		
					return false;
				}
			}
		
		}
		
	}
	
	
	/**
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::getDebugInfo()
	 */
	public function getDebugInfo() {
		return $this->debugInfo;
	}
	

	/**
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::getDebugInfo()
	 */
	public function setDebugInfo($debug) {
		$this->debugInfo = $debug;
	}
	

	/**
	 * load logic conditions into $logicCondtitions attribute
	 * @param array $configData
	 * @return array
	 */
	private function loadLogicConditions(array $configData) {
		
		$logicConditions = array();
		foreach ($configData as $title => $features) {
			
			if ((bool) strstr($title, 'filter') && (bool) $features['VALUE']) {
				if (strcmp('filter_part_group', $title) == 0) {
					$logicConditions[] = array('type' => $features['CODE'], 'value' => $features['VALUE_XML_ID']);
					continue;
				}
		
				if (strcmp('filter_existing_supplier', $title) == 0) {
		
					$suppliersId = array_intersect($features['VALUE'], \LinemediaAutoSupplier::getAllowedSuppliers());
					$suppliers = array();
					 
					$unwroughtSuppliers = CIBlockElement::GetList(
							array(),
							array('IBLOCK_ID' => COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), 'ID' => $suppliersId ,'ACTIVE' => 'Y')
					);
					 
					while ($ob = $unwroughtSuppliers->GetNextElement()) {
						$ob = $ob->GetProperties();
						array_push($suppliers, $ob['supplier_id']['VALUE']);
					}
		
					$logicConditions[] = array('type' => $features['CODE'], 'value' => $suppliers);
					continue;
				}
		
				$logicConditions[] = array('type' => $features['CODE'], 'value' => $features['VALUE']);
			}
		}
		
		return $logicConditions;
		
	}
	
	
	/**
	 * load modifying data into $modifyingCondtitions attribute
	 * @param unknown $configData
	 */
	private function loadModifyingConditions(array $configData) {
		
		return array(
				'affectedElementByModif' => $configData['affected_element_by_action']['VALUE'],
				'awareModificator' => $configData['aware_modificator']['VALUE'],
				'omitModificators' => $configData['omit_modificators']['VALUE'],
				'modificatorID' => $configData['modificatorID'],
				'affectedGroups' => $configData['affected_groups']['VALUE_XML_ID'],
				'concealedSuppliers' => self::calculateVisualFormSuppliers($configData['hide_suppliers']['VALUE'], $this)
		);
		
	}
	
	/**
	 * return visual representation of each suppliers
	 * @param int $configData
	 * @param CapableToModifyingSearchInterface $obj
	 * @return array
	 */
	private static function calculateVisualFormSuppliers($conveyedSuppliers, CapableToModifyingSearchInterface $obj) {
	
		
		$cache_key = md5(__METHOD__ . json_encode($conveyedSuppliers));
		if(self::$cache[$cache_key]) {
			return self::$cache[$cache_key];
		}
		
		
		$suppliersId = array_intersect($conveyedSuppliers, \LinemediaAutoSupplier::getAllowedSuppliers());
		$suppliers = array();
		
		if (count($suppliersId) == 0) {
			$obj->setDebugInfo(array('unavailableSuppliers' => self::UNAVAILABLE_SUPPLIERS));
			return;
		}
		
		
		
		foreach($suppliersId AS $sup_id) {
			$res = \LinemediaAutoSupplier::GetList(array(), array('ID' => $sup_id), false, false, array('ID', 'PROPERTY_supplier_id'));
			array_push($suppliers, $res[$sup_id]['PROPERTY_SUPPLIER_ID_VALUE']);
		}
		
		/*
		$unwroughtSuppliers = CIBlockElement::GetList(
				array(),
				array('IBLOCK_ID' => COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), 'ID' => $suppliersId ,'ACTIVE' => 'Y')
		);
		 
		while ($ob = $unwroughtSuppliers->GetNextElement()) {
			$ob = $ob->GetProperties();
			array_push($suppliers, $ob['supplier_id']['VALUE']);
		}
		*/
		
		self::$cache[$cache_key] = $suppliers;
		
		return $suppliers;
	}
	
	/**
	 * analog group type
	 * @return array
	 */
	private static function getAnalogGroups() {
		return array_keys(\LinemediaAutoPartAll::getAnalogGroups());
	}
	
}








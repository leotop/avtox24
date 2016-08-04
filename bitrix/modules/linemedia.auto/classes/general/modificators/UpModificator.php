<?php

/**
 * Linemedia Autoportal
 * Main module
 * up suppliers modificator
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


class UpModificator implements CapableToModifyingSearchInterface
{

	
	/**
	 * suppliers denied
	 * @var string UNAVAILABLE_SUPPLIERS
	 */
	const UNAVAILABLE_SUPPLIERS = 'you havent enough privileges to access to suppliers';
	
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
			$unaffectedWithModif = array();
			$affected = array();
			$affectedAware = array();
			$discardedIndexes = array();
			
			/*
			 * split array into 2 arrays first - affected by modificator, second unaffected
			* moreover marked each elements passed through modificator (type affected)
			*/
			foreach ($spares as $key => &$spare) {
	
				/*
				 * if set up omitModificators and spare has one of them
				* put spare into unaffected array and go over to the next one
				*/
				$modificators = (array) array_values($spare['affectedModificators']);		
				if (count(array_intersect($modificators, $this->modifyingConditions['omitModificators'])) != 0)
				{		
					$unaffectedWithModif[$key] = $spare;
					continue;
				}
			    
				/*
				 * if set up awareModificators and $spare has one
				* put spare into ffected array and go over to the next one
				*/
				if (in_array($this->modifyingConditions['awareModificator'], $modificators)) {
					$spare['affectedModificators'][$this->modifyingConditions['modificatorID']] = end($modificatorChunks);
					$affectedAware[$key] = $spare;
					continue;
				}
				
				if ($spare['supplier_id'] != current($this->modifyingConditions['upSuppliers'])) {
					$unaffected[$key] = $spare;
					continue;
				}
				
				
				$spare['affectedModificators'][$this->modifyingConditions['modificatorID']] = end($modificatorChunks);
				$affected[$key] = $spare;
			}
			
			$upSpares = count($affectedAware) == 0 ? $affected : $affectedAware;
			
			//if limit of rendering set up
			if (($limit = (int) $this->modifyingConditions['affectedElementByModif']) != 0) {
					
				$discarded = array_slice($upSpares, $limit);
				foreach ($discarded as $key => &$spare) {
					$discardedIndexes[] = $key;
					unset($spare['affectedModificators'][$this->modifyingConditions['modificatorID']]);
				}
				
				$upSpares = array_slice($upSpares, 0, $limit);
			}
			
			//assemble all arrays into one
			$searchOutcome[$group] = array_merge($unaffectedWithModif, $upSpares);
			$searchOutcome[$group] = array_merge($searchOutcome[$group], $unaffected);
			
			if (count($discarded) > 0) {
				$searchOutcome[$group] = array_merge($searchOutcome[$group], $discarded);
			}
		}
		
		return $searchOutcome;
		
	}
	
	
	/**
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::isAlterationFeasible()
	 */
	public function isAlterationFeasible($searchOutcome)
	{	
		// Whether type $searchOutcome is type of \Iterator or array.
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
					
				// Part group condition.
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
					} elseif (strcmp($cond['type'], 'filter_overall_count_max') == 0) {
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
	public function getDebugInfo()
	{
		return $this->debugInfo;
	}
	

	/**
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::getDebugInfo()
	 */
	public function setDebugInfo($debug)
	{
		$this->debugInfo = $debug;
	}
	

	/**
	 * load logic conditions into $logicCondtitions attribute
	 * @param array $configData
	 * @return array
	 */
	private function loadLogicConditions(array $configData)
	{	
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
	private function loadModifyingConditions(array $configData)
	{		
		return array(
			'affectedElementByModif' => $configData['affected_element_by_action']['VALUE'],
			'awareModificator' => $configData['aware_modificator']['VALUE'],
			'omitModificators' => $configData['omit_modificators']['VALUE'],
			'modificatorID' => $configData['modificatorID'],
			'typeOfSortAfterUp' => $configData['up_suppliers_condition']['VALUE_XML_ID'],
			'upSuppliers' => self::calculateVisualFormSuppliers($configData['up_suppliers']['VALUE'], $this),
			'affectedGroups' => $configData['affected_groups']['VALUE_XML_ID']
		);	
	}
	
	
	/**
	 * return visual representation of each suppliers
	 * @param int $configData
	 * @param CapableToModifyingSearchInterface $obj
	 * @return array
	 */
	private static function calculateVisualFormSuppliers($conveyedSuppliers, CapableToModifyingSearchInterface $obj)
	{
		$cache_key = md5(__METHOD__ . json_encode($conveyedSuppliers));
		if(self::$cache[$cache_key]) {
			return self::$cache[$cache_key];
		}
		
		$suppliers = array();
		// #21849 - для массива in_array некорректен
		if(is_array($conveyedSuppliers)) {
			if(count(array_diff($conveyedSuppliers, \LinemediaAutoSupplier::getAllowedSuppliers())) > 0) {
				$obj->setDebugInfo(array(
					'unavailableSuppliers' => self::UNAVAILABLE_SUPPLIERS
				));
				return;
			}
		} else {
			if (!in_array($conveyedSuppliers, \LinemediaAutoSupplier::getAllowedSuppliers())) {
				$obj->setDebugInfo(array(
					'unavailableSuppliers' => self::UNAVAILABLE_SUPPLIERS
				));
				return;
			}
		}

		
		
		/*if(!is_array($conveyedSuppliers)) {
			$conveyedSuppliers = array($conveyedSuppliers);
		}
		foreach($conveyedSuppliers AS $sup_id) {
			$res = \LinemediaAutoSupplier::GetList(array(), array('ID' => $sup_id), false, false, array('ID', 'PROPERTY_supplier_id'));
			array_push($suppliers, $res[$sup_id]['PROPERTY_SUPPLIER_ID_VALUE']);
		}
		*/
		
		$unwroughtSuppliers = CIBlockElement::GetList(
			array(),
			array('IBLOCK_ID' => COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS'), 'ID' => $conveyedSuppliers ,'ACTIVE' => 'Y')
		);
		
		while ($ob = $unwroughtSuppliers->GetNextElement()) {
			$ob = $ob->GetProperties();
			array_push($suppliers, $ob['supplier_id']['VALUE']);
		}
		
		
		self::$cache[$cache_key] = $suppliers;
		
		return $suppliers;
	}
	
	
	/**
	 * analog group type
	 * @return array
	 */
	private static function getAnalogGroups()
	{
		return array_keys(\LinemediaAutoPartAll::getAnalogGroups());
	}
}

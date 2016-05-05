<?php

/**
 * Linemedia Autoportal
 * Main module
 * sort modificator
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


class SortModificator implements CapableToModifyingSearchInterface
{
	
	
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
			$firstIndex = 0;
			$lastIndex = 0;
			$isFirstSet = false;
						
			/*
			 * split array into 2 arrays first - affected by modificator, second unaffected
			* moreover marked each elements passed through modificator (type affected)
			*/
			foreach ($spares as $index => $spare) {

				$modificators = (array) array_values($spare['affectedModificators']);
				if (count(array_intersect($modificators, $this->modifyingConditions['omitModificators'])) != 0) {
					$unaffected[] = $spare;
					continue;
				}
				
				
				if (in_array($this->modifyingConditions['awareModificator'], $modificators)) {	
					$spare['affectedModificators'][$this->modifyingConditions['modificatorID']] = end($modificatorChunks);
					$affectedAware[] = $spare;
					
					if (!$isFirstSet) {
						$firstIndex = $index;
						$isFirstSet = true;
					}
					
					++$lastIndex;
					continue;
				}
				
				
				$spare['affectedModificators'][$this->modifyingConditions['modificatorID']] = end($modificatorChunks);
				$affected[] = $spare;
				
			}
			
			
			$lastIndex = $firstIndex + $lastIndex;
			$modifyCond = $this->modifyingConditions;
			$sortedSpares = count($affectedAware) == 0 ? $affected : $affectedAware;
			usort($sortedSpares, function ($spare1, $spare2) use ($modifyCond) {
			   
				$by = $modifyCond['by'];
				$sortOrder = $modifyCond['sort_order'];
				
				if ($modifyCond['partition'] == LinemediaAutoSearchModificator::ADMIN_SEARCH && $by == 'price_src') {

                    $by = 'price';
					if ($sortOrder == SORT_ASC) {
						return $spare1[$by] > $spare2[$by];
					}
					else {
						return $spare1[$by] < $spare2[$by];
					}
				}
				else {
					
					if ($sortOrder == SORT_ASC) {
						return $spare1[$by] > $spare2[$by];
					}
					else {
						return $spare1[$by] < $spare2[$by];
					}
					
				}
			
			});
			
			if ((bool) count($affectedAware)) {

				$tmp = array();
				foreach ($sortedSpares as $key => $spare) {
					$tmp[$firstIndex++] = $spare;
				}
				
				$sortedSpares = $tmp;
				$keys = array_keys($sortedSpares);
				array_walk($searchOutcome[$group], function (&$spare, $key) use ($sortedSpares, $keys) {
					if (in_array($key, $keys)) {
						$spare = $sortedSpares[$key];
					}
				});
				
			} else {

				$limit = (int) $this->modifyingConditions['affectedElementByModif'];
					
				//if limit of rendering set up
				if ($limit != 0) {
				
					$discarded = array_slice($sortedSpares, $limit);
					$sortedSpares = array_slice($sortedSpares, 0, $limit);
				
					foreach ($discarded as &$spare) {
						unset($spare['affectedModificators'][$this->modifyingConditions['modificatorID']]);
					}
				
					$unaffected = array_merge($unaffected, $sortedSpares);
					$unaffected = array_merge($unaffected, $discarded);
				}
					
				
				$searchOutcome[$group] = (bool) $limit ?  $unaffected : array_merge($unaffected, $sortedSpares);
				
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
	 * dummy function
	 *  (non-PHPdoc)
	 * @see \Application\Service\Search\CapableModifySearchInterface\CapableModifySearchInterface::getDebugInfo()
	 */
	public function setDebugInfo($debug) {
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
	 * @param array $configData
	 */
	private function loadModifyingConditions(array $configData) {
		
		return array(
				'affectedElementByModif' => $configData['affected_element_by_action']['VALUE'],
				'awareModificator' => $configData['aware_modificator']['VALUE'],
				'omitModificators' => $configData['omit_modificators']['VALUE'],
				'modificatorID' => $configData['modificatorID'],
				'affectedGroups' => $configData['affected_groups']['VALUE_XML_ID'],
				'by' => $configData['sort_field']['VALUE_XML_ID'],
				'sort_order' => $configData['sort_order']['VALUE_XML_ID'] == 'desc' ? SORT_DESC : SORT_ASC,
				'partition' => $configData['partition']
		);
		
	}
	

	/**
	 * analog group type
	 * @return array
	 */
	private static function getAnalogGroups() {
		return array_keys(\LinemediaAutoPartAll::getAnalogGroups());
	}
	
}








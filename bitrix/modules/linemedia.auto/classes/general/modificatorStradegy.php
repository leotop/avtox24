<?php

/**
 *
 * Linemedia Autoportal
 * Main module
 * Module events for module itself
 *
 * @author  Linemedia
 * @since   08/02/2014
 *
 * @link    http://auto.linemedia.ru/
 *
 */

IncludeModuleLangFile(__FILE__);

/**
 *
 * @author Sonny
 *
 */
class RecursiveIdenticalKeys
{
    public static function recursive_identical_keys_compose($array, $index, &$composed_array) {

    	if (!is_array($array)) {
    		return;
    	}

    	foreach ($array as $key => $sub_array) {
    		if (isset($sub_array[$index])) {
    			$composed_array[] = $sub_array[$index];
    		}

    		self::recursive_identical_keys_compose($array[$key], $index, $composed_array);
    	}
    }
}

/**
 *
 * @author Sonny
 *
 */
abstract class AbstractModificator
{

    protected  static $__digitalPossibleFields = array('price', 'quantity', 'delivery_time');
    protected  static $__setOfKeys = array();
    protected  static $__acceptableBrands = array();

    /**
     *
     * @param $array
     * @param $key
     */
    public static function initiateBrands($array, $key) {
        self::$__acceptableBrands = array_unique(RecursiveIdenticalKeys::recursive_identical_keys_compose($array, $key, self::$__acceptableBrands), SORT_ASC);
    }

    public static function initiateKeys($array) {
        self::$__setOfKeys = array_keys($array);
    }

    /**
     *
     * @param $conditions
     * @param $array
     * @param $analogGroupType
     * @return void
     */
    public function checkingConditions($conditions, $array, $analogGroupType) {

        switch ($conditions['type']) {

        	case 'filter_user_group': {

        		global $USER;
        		$currentUserGroups = $USER->GetUserGroupArray();

        		/**
        		 * replace all non-numeric elements of incoming arrays with digital ones
        		 */
        		for (reset($currentUserGroups), reset($conditions['value']), $i = -1; current($currentUserGroups) != null || current($conditions['value']) != null; next($currentUserGroups), next($conditions['value'])) {

        			if (!preg_match('/[\d]+/', current($currentUserGroups))) {

        				if ($key = array_search(current($currentUserGroups), $conditions)) {
        					$conditions['value'][$key] = --$i;
        				}

        				$currentUserGroups[key($currentUserGroups)] = $i;
        			}

        			if (!preg_match('/[\d]+/', current($condition['value']))) {

        				if ($key = array_search(current($conditions['value']), $currentUserGroups)) {
        					$currentUserGroups[$key] = --$i;
        				}

        				$conditions['value'][key($conditions['value'])] = $i;
        			}
        		}

        		if (array_intersect($currentUserGroups, $conditions['value']) == null) {
        			break 3;
        		}

        		break;
        	}

        	/**
        	 * set of textual conditions
        	 */
        	//case 'filter_brand_group':
        	case 'filter_part_group': {

        		if ($conditions['type'] == 'filter_part_group') {

        			foreach ($conditions['value'] as $group) {
        				if (!in_array($analogGroupType[$group], self::$__setOfKeys)) {
        					break 3;
        				}
        			}
        		}
        		else {

        			/**
        			 * brands and other same textual things
        			 */
        			foreach ($array as $group_type => $item) {
        				foreach ($conditions['value'] as $entered_supplier)
        				if (!in_array($entered_supplier, self::$__acceptableBrands)) {
        					break 4;
        				}
        			}
        		}

        		break;
        	}

        	/**
        	 * set of numeric conditions
        	 */
        	case 'filter_overall_count_max':
        	case 'filter_overall_count_min': {


        		if ($conditions['type'] == 'filter_overall_count_min') {

        			foreach (self::$__setOfKeys as $key => $item) {
        				if (count($array[$item]) < $conditions['value']) {
        					break 3;                                    //need to be verified
        				}
        			}
        		}
        		else {

        			if ($conditions['type'] == 'filter_overall_count_max') {

        				foreach (self::$__setOfKeys as $key => $item) {
        					if (count($array[$item]) > $conditions['value']) {
        						break 4;                                    //need to be verified
        					}
        				}
        			}
        		}

        		break;
        	}
        }
    }

    public function getConditions() {
    	return null;
    }

    abstract public function initiate($modificator);
    abstract public function execute(&$array);
}

/**
 *
 * @author Sonny
 *
 */
class CustomizedSortSearch extends AbstractModificator
{

    private $__conditions = array();
    private $__auxiliarySettings = array();
    private static $__sortOrder = array('asc' => SORT_ASC, 'desc' => SORT_DESC);

    public function getConditions() {
    	return $this->__conditions;
    }

    public function initiate($modificator) {

        foreach ($modificator as $appell => $item) {

        	if (strstr($appell, 'filter') && $modificator[$appell]['VALUE']) {
        		array_push($this->__conditions, array('type' => $modificator[$appell]['CODE'], 'value' => $modificator[$appell]['VALUE']));
        	}
        }

        $this->__auxiliarySettings = array(
                'by' => array($modificator['sort_field']['VALUE_XML_ID']),
                'sort_order' => array($modificator['sort_field']['VALUE_XML_ID'] => self::$__sortOrder[$modificator['sort_order']['VALUE_XML_ID']])
       );

       return $this;
    }


    public function execute(&$arrangedArray) {

        /**
         * initiate criterion (fields which will be used in sorting),
         * order of sorting (ascending or descending)
         * sort sequence (be used in array_of_arg for array_multisort)
         */

        $sort_sequence = array();
        $array_of_arg = array();

        foreach (self::$__setOfKeys as $group)
        	$array_of_arg[$group] = array();

        foreach ($this->__auxiliarySettings['by'] as $criteron) {

        	foreach ($arrangedArray as $group => $item) {
        		foreach ($item as $key => $detail) {
        			if (in_array($criteron, self::$__digitalPossibleFields)) {

        				$digit_pattern = '/[^0-9\.]/';
        				$detail[$criteron] = floatval(preg_replace($digit_pattern, '', $detail[$criteron]));

        			}
        			$sort_sequence[$group][$criteron][$key] = $detail[$criteron];
        		}
        	}
        }


        //_d($taken_array_of_parts);

        foreach (self::$__setOfKeys as $group) {
        	foreach ($this->__auxiliarySettings['by'] as $criterion) {

        		array_push($array_of_arg[$group], $sort_sequence[$group][$criterion]);
        		array_push($array_of_arg[$group], $this->__auxiliarySettings['sort_order'][$criterion]);
        	}
        }

        foreach ($arrangedArray as $group => &$array) {

        	$array_of_arg[$group][] = &$array;
        	call_user_func_array('array_multisort', $array_of_arg[$group]);
        	$arrangedArray[$group] = $array;
        }
    }
}

/**
 *
 * @author Sonny
 *
 */
class CustomizedTruncateSearch extends AbstractModificator
{
    private $__conditions = array();
    private $__auxiliarySettings = array();

    public function getConditions() {
    	return $this->__conditions;
    }

    public function initiate($modificator) {

        foreach ($modificator as $appell => $item) {

        	if (strstr($appell, 'filter') && $modificator[$appell]['VALUE']) {
        		array_push($this->__conditions, array('type' => $modificator[$appell]['CODE'], 'value' => $modificator[$appell]['VALUE']));
        	}
        }

        $this->__auxiliarySettings = array('count_of_row_visible_chunk' => $modificator['limit']['VALUE']);

        return $this;
    }

    public function execute(&$truncatedArray) {

        foreach ($truncatedArray as $group => $sub_array) {
        	$truncatedArray[$group] = array_slice($sub_array, 0, $this->__auxiliarySettings['count_of_row_visible_chunk']);
        }
    }
}

/**
 *
 * @author Sonny
 *
 */
class CustomizedConcealSearch extends AbstractModificator
{
	private $__conditions = array();
	private $__auxiliarySettings = array();

	public function getConditions() {
		return $this->__conditions;
	}

	public function initiate($modificator) {

		$id_suppliers_iblock = COption::GetOptionInt('linemedia.auto', 'SUPPLIERS_IBLOCK_ID', 263);
		$id_suppliers = $modificator['hide_suppliers']['VALUE'];
		$retrieved_suppliers = array();

		$raw_retrieved_suppliers = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $id_suppliers_iblock, 'ID' => $id_suppliers ,'ACTIVE' => 'Y'), false, false, array());

		while ($ob = $raw_retrieved_suppliers->GetNextElement()) {
			$ob = $ob->GetProperties();
			array_push($retrieved_suppliers, $ob['supplier_id']['VALUE']);
		}

		foreach ($modificator as $appell => $item) {

			if (strstr($appell, 'filter') && $modificator[$appell]['VALUE']) {
				array_push($this->__conditions, array('type' => $modificator[$appell]['CODE'], 'value' => $modificator[$appell]['VALUE']));
			}
		}

		$this->__auxiliarySettings = array('concealed_appell' => $retrieved_suppliers);

		return $this;
	}

	public function execute(&$array) {

		/**
		 * casting all fields to lowercase format
		 */
		foreach ($this->__auxiliarySettings['concealed_appell'] as $field) {
			if (is_string($field)) {
				strtolower($field);
			}
		}

		/**
		 * get field type for appropriate field value
		 */
		/*
		 reset($taken_array_of_parts);
		$key = key($taken_array_of_parts);
		$field_type = array_search($field, current($taken_array_of_parts[$key]));
		*/
		$field_type = 'supplier_id'; // TODO temporary field need to be thinking how to obtain automatic field

		/**
		 * remove all row
		 */
		foreach ($array as $group => $subarray) {
			foreach ($subarray as $key => $detail) {

				if (is_string($detail[$field_type])) {
					strtolower($detail[$field_type]);
				}

				if (in_array($detail[$field_type], $this->__auxiliarySettings['concealed_appell'])) {
					unset($array[$group][$key]);
				}
			}
		}
	}
}

/**
 *
 * @author Sonny
 *
 */
class CustomizedAscendingSearch extends AbstractModificator
{
	private $__conditions = array();
	private $__auxiliarySettings = array();

	public function getConditions() {
		return $this->__conditions;
	}

	public function initiate($modificator) {

		$id_suppliers_iblock = COption::GetOptionInt('linemedia.auto', 'SUPPLIERS_IBLOCK_ID', 263);
		$id_suppliers = $modificator['hide_suppliers']['VALUE'];
		$retrieved_suppliers = array();

		$raw_retrieved_suppliers = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $id_suppliers_iblock, 'ID' => $id_suppliers ,'ACTIVE' => 'Y'), false, false, array());

		while ($ob = $raw_retrieved_suppliers->GetNextElement()) {
			$ob = $ob->GetProperties();
			array_push($retrieved_suppliers, $ob['supplier_id']['VALUE']);
		}

		foreach ($modificator as $appell => $item) {

			if (strstr($appell, 'filter') && $modificator[$appell]['VALUE']) {
				array_push($this->__conditions, array('type' => $modificator[$appell]['CODE'], 'value' => $modificator[$appell]['VALUE']));
			}
		}

		$this->__auxiliarySettings = array('ascended_appell' => $retrieved_suppliers);

		return $this;
	}

	public function execute(&$array) {

		$sortOrder = $this->__auxiliarySettings['ascended_appell'];

		/**
		 * casting all fileds to lowercase format
		 */
		foreach ($this->__auxiliarySettings['concealed_appell'] as $field) {
			if (is_string($field)) {
				strtolower($field);
			}
		}

		/**
		 * get field type for appropriate field value
		 */
		/*
		 reset($taken_array_of_parts);
		$key = key($taken_array_of_parts);
		$field_type = array_search(current($sort_order), current($taken_array_of_parts[$key]));
		*/
		$field_type = 'supplier_id'; // TODO temporary field need to be thinking how to obtain autoomatic field

		/**
		 * auxiliary anonymous function for usort
		 */
		$sortingByPredefinedOrder = function ($param1, $param2) use ($sortOrder, $field_type) {

			if ($param1 == $param2)
				return 0;

			$pos_first = array_search($param1[$field_type],$sortOrder);
			$pos_second = array_search($param2[$field_type], $sortOrder);

			//if both are in the $order, then sort according to their order in $order...
			if ($pos_first !== false && $pos_second !== false)
				return ($pos_first < $pos_second) ? -1 : 1;

			//if only one is in $order, then sort to put the one in $order first...
			if ($pos_first !== false)
				return -1;

			if($pos_second !== false)
				return 1;

			//if neither in $order, then a simple alphabetic sort...
			return ($param1 < $param2) ? - 1 : 1;
		};

		foreach ($array as $group => &$sub_array) {
			usort($sub_array, $sortingByPredefinedOrder);
		}
	}
}











<?php
/**
 * Linemedia Autoportal
 * Main module
 * Import prices history
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoImportHistory {

    private $supplier_id;
	private static $cache;

    public function __construct($supplier_id) {
        $this->supplier_id = $supplier_id;
    }
	
	/*
     * ��������� ����������� �������
     */
    public static function isEnabled() {

	    if (!array_key_exists('isEnabled', self::$cache)) {
		    self::$cache['isEnabled'] = LinemediaAutoModule::isFunctionEnabled('import_history',  'linemedia.auto');
	    }

		return self::$cache['isEnabled'];
    }

	/*
     * ��������� ���������� �� ������� � ������� �������
     */
	 
    public function add($fields) {

        global $DB;

        $prev = $this->getPrevious();
        if($prev) {
            if(intval($fields['PARTS_COUNT']) > 0) {
				$fields['PARTS_DIFF'] = round(100 * $prev['PARTS_COUNT'] / $fields['PARTS_COUNT']);
				if($fields['PARTS_DIFF'] == 0 && $prev['PARTS_COUNT'] > 0) {
					$fields['PARTS_DIFF'] = round(100 * $fields['PARTS_COUNT'] / $prev['PARTS_COUNT']);
				}
			}
            if(intval($fields['SUM_PRICE']) > 0) {
				$fields['SUM_DIFF'] = round(100 * $prev['SUM_PRICE'] / $fields['SUM_PRICE']);        
				if($fields['SUM_DIFF'] == 0 && $prev['SUM_PRICE'] > 0) {
					$fields['SUM_DIFF'] = round(100 * $fields['SUM_PRICE'] / $prev['SUM_PRICE']);   
				}
			}

			$suppliers = LinemediaAutoSupplier::GetList(array(), array(), false, false, array('ID'), 'supplier_id');

			$amount_deviation = intval($suppliers[$this->supplier_id]['PROPS']['amount_deviation']['VALUE']);
			$total_sum_deviation = intval($suppliers[$this->supplier_id]['PROPS']['total_sum_deviation']['VALUE']);
			
			$parts_diff = abs($fields['PARTS_DIFF'] - 100);
			$sum_diff = abs($fields['SUM_DIFF'] - 100);
		
			$fields['CORRECT_IMPORT'] = $prev['CORRECT_IMPORT'];
			if($amount_deviation != 0 || $total_sum_deviation != 0) {
				if(($parts_diff > $amount_deviation) || ($sum_diff > $total_sum_deviation)) {
														
					if(LinemediaAutoImportHistory::isEnabled())			
						self::supplierActivation($this->supplier_id, 'N');
					
					$arEventFields = array(
						"SUPPLIER_ID"			=> $this->supplier_id,	
						"AMOUNT_DEVIATION"		=> $parts_diff,
						"TOTAL_SUM_DEVIATION"	=> $sum_diff
					);
					// 1 - ��������, ��� �����-���� �����������
					if(!$fields['CORRECT_IMPORT']) {
						$fields['CORRECT_IMPORT'] = true;
						CEvent::SendImmediate("IMPORT_PRICE_LIST_DEVIATION", SITE_ID, $arEventFields);
					}
				} 
			}	
		}
		else $fields['CORRECT_IMPORT'] = 0;
		
        return $DB->Add('b_lm_import_history', $fields);
    }
	
	/*
     * ������ ���������� �������� ��������� ����������, ����� �� ���������� � �������� ������
	 * N - ������� ����������, Y - ��������
     */
	 
	public static function supplierActivation($supplier_id, $active) {
		
		global $DB;
		
		$arSelect = array("ID", "NAME");
		$arFilter = Array("IBLOCK_CODE"=>"lm_auto_suppliers", "PROPERTY_supplier_id"=>$supplier_id);
		$suppliers = LinemediaAutoSupplier::GetList(array(), $arFilter, false, false, $arSelect, 'supplier_id');
		$id = $suppliers[$supplier_id]['ID'];
		
		$id = intval($id);
		$active = $DB->ForSql($active);	
		
		$sql = "UPDATE `b_iblock_element` SET `ACTIVE` = '$active' WHERE `b_iblock_element`.`ID` = '$id'";
        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );		
	}
	
	/*
     * ��������� ������ � ���������� �������
     */
    private function getPrevious() {

        global $DB;
		$supplier_id = $DB->ForSql($this->supplier_id);	
        // ���������� ������
        $sql = "SELECT * FROM `b_lm_import_history` WHERE `SUPPLIER_ID`='$supplier_id' ORDER BY DATE DESC LIMIT 1";
        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );
        if($row = $res->Fetch()) {
            return $row;
        }
        return false;
    }
	
	/*
     * ���������� ���������� ��� ����������, ��������� id ������ � ��
     */
	public static function getSupplierId($id) {
		global $DB;
        $id = intval($id);	
        $sql = "SELECT `SUPPLIER_ID` FROM `b_lm_import_history` WHERE `id`='$id'";

        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );

        while($result = $res->Fetch())
            $ID = $result['SUPPLIER_ID'];

        return $ID;
	}
		
	/*
     * ���������� null, ���� ���������� �� ����������, � ��������� ������ ID ����������.
     */
	public static function isSupplierExist($supplier_id) {
		$arSelect = array("ID", "NAME");
		$arFilter = Array("IBLOCK_CODE"=>"lm_auto_suppliers", "PROPERTY_supplier_id"=>$supplier_id);
		$suppliers = LinemediaAutoSupplier::GetList(array(), $arFilter, false, false, $arSelect, 'supplier_id');
					
		return $suppliers[$supplier_id]['ID'];
	}
	
	/*
     * ���������� ��������� ID ������� ������ ������ ���� ����������� 
     */
	public static function getLastImportIds() {
		global $DB;
       
        $sql = "select max(id) from `b_lm_import_history` group by `supplier_id`";

        $res = $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );

        while($result = $res->Fetch())
            $IDs[] = $result['max(id)'];

        return $IDs;
	}
   	
	/*
     * ������������� ����� ���� ����������, ���� ��� � �������������, ���� ������� ���������� ��������� ��������� ����������
	 * $correct = 0 - ���������� ����� ����, 1 - ������������; $active = 'N' - ������� ���������� ����������
     */
    public static function setCorrectnessPriceList($supplier_id, $correct, $supplier_active = false, $active = 'N', $history = false) {
        global $DB;
        $supplier_id = $DB->ForSql($supplier_id);		
        $active = $DB->ForSql($active);		
		
		if($supplier_active) {
			if(LinemediaAutoImportHistory::isEnabled())
				self::supplierActivation($supplier_id, $active);
		}
		
		if($history) {
			$sql = "select max(id) from `b_lm_import_history` WHERE `supplier_id` = '$supplier_id'";
			$res = $DB->Query(
				$sql,
				false,
				"File: " . __FILE__ . "<br>Line: " . __LINE__
			);
			while($result = $res->Fetch())
				$ID = $result['max(id)'];
			
			$sql = "UPDATE `b_lm_import_history` SET `correct_import` = '$correct' WHERE `ID`= '$ID'";
			$DB->Query(
				$sql,
				false,
				"File: " . __FILE__ . "<br>Line: " . __LINE__
			);			
		}
    }		
	
    public static function getList($sort = array(), $filter = array()) {

        global $DB;
        $where_parts = array();

        foreach($filter as $key => $value) {

            if (strlen($value) <= 0) {
                continue;
            }
            switch($key) {
                case 'ID' : {
                    $where_parts[] = "ID='" . $DB->ForSql($value) . "'";
                } break;
                case 'TASK_ID' : {
                    $where_parts[] = "TASK_ID='" . $DB->ForSql($value) . "'";
                } break;
                case 'SUPPLIER_ID' : {
                    $where_parts[] = "SUPPLIER_ID='" . $DB->ForSql($value) . "'";
                } break;
                case '>=DATE' : {
                    $ts = MakeTimeStamp($value);
                    if($ts) {
                        $where_parts[] = "DATE >= '" . FormatDate("Y-m-d H:i:s", $ts) . "'";
                    }
                } break;
                case '<=DATE' : {
                    $ts = MakeTimeStamp($value);
                    if($ts) {
                        $where_parts[] = "DATE <= '" . FormatDate("Y-m-d H:i:s", $ts) . "'";
                    }
                } break;
            }
        } // foreach($filter as $key => $value)

        $where = " WHERE 1=1";
        if(count($where_parts) > 0) {
            $where = " WHERE " . join(" AND ", $where_parts);
        }

        $order_parts = array();

        foreach($sort as $key => $value) {

            $ord = (ToUpper($value) <> "ASC" ? "DESC" : "ASC");

            $order_parts[] = $key . " " . $ord;
        }

        $order = " ORDER BY ID DESC";
        if(count($order_parts) > 0) {
            $order = " ORDER BY " . join(', ', $order_parts);
        }

        $sql = "SELECT * FROM b_lm_import_history" . $where . $order;

        return $DB->Query(
            $sql,
            false,
            "File: " . __FILE__ . "<br>Line: " . __LINE__
        );
    }
}
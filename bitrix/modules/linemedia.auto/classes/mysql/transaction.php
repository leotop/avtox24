<?php

/**
 * Linemedia Autoportal
 * Main module
 * Transaction db class
 *
 * @author Linemedia
 * @since 22/01/2012
 * @link http://auto.linemedia.ru/
 */


class LinemediaAutoTransactionBDTable
{

	/**
	 * @var array $mandatoryFields
	 */
	private static $mandatoryFields = array('basketId', 'userId', 'price', 'done', 'paid', 'orderId');

	/**
	 * db title
	 * @var string $tableApp
	 */
	private static $tableApp = 'b_lm_transactions';

	/**
	 * accordnace between appellation of LinemediaAutoTransactions::operation and queries
	 * @var array $setQueries
	 */
	private static $setQueries = array(
		'findByConditions' => 'SELECT T.* FROM `b_lm_transactions` AS T',
	    'findByID' => 'SELECT T.* FROM `b_lm_transactions` T WHERE T.BASKET_ID = ',
	);


	/**
	 * error type
	 * @var string $LAST_ERROR
	 */
	private $LAST_ERROR = '';


	/**
	 * check fields before writing
	 * @return boolean
	 */
	private static function checkingFields() {

		global $DB;
		$aMsg = array();
        $corruptedField = false;

        array_walk(self::$mandatoryFields, function ($field) use (&$corruptedField) {
        	if ($field == null) {
        		$corruptedField = $field;
        	}
        });

		if ($corruptedField) {
			$aMsg[] = array('id' => $corruptedField, 'text' => GetMessage("class_rub_err_title"));
		}

		if (!empty($aMsg)) {

			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			//$this->LAST_ERROR = $e->GetString();
			return false;
		}

		return true;
	}


   /**
    * retrieving elements out of database 
    * @param array $filter
    * @param array $aSort
    * @return \CDBResult
    */
	public function findByConditions($filter = array(), $aSort = array()) {

		global $DB;

		$arFilter = array();
		$arOrder = array();

		foreach ($filter as $key => $val) {

			if (strlen($val) <= 0) {
				continue;
            }

		    $arFilter[] = "T.".$key." = '".$DB->ForSql($val)."'";

		}

		foreach ($aSort as $key => $val) {

			$ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
			$arOrder[] = "T.".$key." ".$ord;
		}

		if (count($arOrder) == 0) {
			$arOrder[] = "T.id DESC";
        }

		$sOrder = "\nORDER BY ".implode(", ",$arOrder);

		if (count($arFilter) == 0) {
			$sFilter = "";
		} else {
			$sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
        }

		return $DB->Query(
				self::$setQueries[__FUNCTION__] . $sFilter . $sOrder, 
				false, 
				"File: " . __FILE__ . "<br>Line: " . __LINE__
		);
	}

    /**
     * retrieving element by id
     * @param int $id
     * @return \CDBResult
     */
	public function findByID($id) {
		global $DB;
		return $DB->Query(
				self::$setQueries[__FUNCTION__] . intval($id), 
				false, 
				"File: " . __FILE__ . "<br>Line: " . __LINE__
		);
	}


    /**
     * removing element by id
     * @param int $id
     */
	public function removeId($id)
	{
		global $DB;

		$DB->StartTransaction();

		$res = $DB->Query('DELETE FROM'.(self::$tableApp).'WHERE ID_BITRIX_TRANSACTION ='.intval($id), false, "File: ".__FILE__."<br>Line: ".__LINE__);

        if ($res)
			$DB->Commit();
		else
			$DB->Rollback();

		return $res;
	}


	/**
	 * putting transacton into database 
	 * @param \Iterator $iter
	 * @return boolean
	 */
	public function create(array $dbParams) {
		
		CModule::IncludeModule('sale');
        global $DB;
        $success;
        $transDescr = \LinemediaAutoTransactionTitle::transateFromEngToRus();
        
        $trans = $dbParams['transaction'];
        $baskets = $dbParams['baskets'];
        $trans['DESCRIPTION'] = $transDescr[$trans['DESCRIPTION']];       
        $transId = CSaleUserTransact::Add($trans);
        foreach ($baskets as $basket) {
        	$basket['ID_BITRIX_TRANSACTION'] = $transId;
        	$success = $DB->Add(self::$tableApp, $basket);
        }
        
		return $success;
	}

   /**
    * updating transaction
    * @param int $cartId
    * @param array $array
    * @return boolean
    */
	public function update($cartId, $array) {

		global $DB;
/*
		if (!self::checkingFields()) {
			return false;
        }
*/
		$strUpdate = $DB->PrepareUpdate(self::$tableApp, $array);
		
		if ($strUpdate != "") {
			$DB->Query(
				'UPDATE `' . (self::$tableApp) . '` SET ' . $strUpdate . ' WHERE `BASKET_ID` = ' . intval($cartId), 
				false, 
				"File: " . __FILE__ . "<br>Line: " . __LINE__
        	);
		}
		
		return true;
	}
}

<?php

IncludeModuleLangFile(__FILE__);

class LinemediaAutoUserAccount
{
    private $user_id;

	
    public function __construct($user_id)
	{
        $this->user_id = $user_id;
    }

	
    public function update($sum, $currency)
	{
        global $DB;

        // зачем это исключение?
		if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') != 'Y') {
			//return true;
		}
		
        if (CModule::IncludeModule('sale')) {

            $res = CSaleUserAccount::GetList(
                array(),
                array("USER_ID" => $this->user_id, "CURRENCY" => $currency)
            );

            if ($account = $res->Fetch()) {
                $result = CSaleUserAccount::Update($account["ID"], array("CURRENT_BUDGET" => $account["CURRENT_BUDGET"] + $sum));
            } else {
                $result = $this->create($sum, $currency);
            }

            return $result;
        }
        return false;
    }

	
    private function create($sum = 0, $currency = null)
	{
        global $DB;

        if (!$currency) $currency = CCurrency::GetBaseCurrency();
        $account = array(
            "USER_ID" => $this->user_id,
            "CURRENT_BUDGET" => $sum,
            "CURRENCY" => $currency,
            "LOCKED" => "Y",
            "DATE_LOCKED" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)))
        );

        if ($id = CSaleUserAccount::Add($account)) {
            return $id;
        }
        return false;
    }

	
    public function getList($format = true)
	{
        $res = CSaleUserAccount::GetList(array(), array('USER_ID' => $this->user_id));
        $list = array();
        while ($fields = $res->Fetch()) {
            if (intval($fields['CURRENT_BUDGET']) != 0) {
                if($format) {
                    $list[$fields['CURRENCY']] = SaleFormatCurrency($fields["CURRENT_BUDGET"], $fields["CURRENCY"]);
                } else {
                    $list[$fields['CURRENCY']] = floatval($fields["CURRENT_BUDGET"]);
                }

            }
        }
        return $list;
    }

	
    public function getCurrentLimit($currency = null)
	{
        if(!$currency) $currency = CCurrency::GetBaseCurrency();

        $list = $this->getList(false);

        if (array_key_exists($currency, $list)) {
            return $list[$currency];
        } else {
            return 0;
        }
    }

    public function isBasketsOrderAvailable($baskets)
    {
    	global $APPLICATION, $USER;
    	$my_branch = LinemediaAutoBranchesDealer::getUserDealer($USER->GetID());
    	$my_branch_id = $my_branch->getBranchID();
    	
    	
    	$has_internal_supplier_basket = false;
    	$sum = 0;
    	foreach($baskets AS $basket) {
	    	
	    	//TODO: optimize
	    	$supplier = new LinemediaAutoSupplier($basket['PROPS']['supplier_id']['VALUE']);
	    	$branch_owner = $supplier->get('branch_owner');
	    	if($my_branch_id != $branch_owner) {
	    		$sum += $basket['PRICE'];
	    		$has_internal_supplier_basket = true;
	    	}
    	}
    	
    	if($has_internal_supplier_basket) {
	        $account = new LinemediaAutoUserAccount($USER->GetID());
	        if ($account->getCurrentLimit($basket['CURRENCY']) < $sum) {
	            $APPLICATION->ThrowException(GetMessage('ERR_EXCEEDED_PAYMENT_LIMIT'));
	            return false;
	        }
	        
	        
	        $director = new LinemediaAutoBranchesDirector($USER->GetID());
	        $delay = $director->getCurrrentDelay();
	        if($delay < 1) {
		        $APPLICATION->ThrowException(GetMessage('ERR_EXCEEDED_DELAY_LIMIT'));
	            return false;
	        }
        }
        
	    return true;
    }
}
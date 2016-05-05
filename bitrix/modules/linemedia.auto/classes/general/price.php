<?php

/**
 * Linemedia Autoportal
 * Main module
 * Price calculation class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);
 
 CModule::IncludeModule("sale");
 
/*
 * ����� ������� ���� ��������
 */
class LinemediaAutoPrice
{

    public $part_id;
    public $part;
    public $user_id;

    /**
     * ������ �������������� ���������� ������� ���� - � ���� ����� ��������� �������� �� ������ �������
     * @var
     */
    protected $external = array();

    public $date;
    
    public $currency = 'RUB';

    /*
    * �������, � ������� �������� ����
    */
    public $price_field = 'price';

    /**
     * ������� ����
     * @var float
     */
    private $base_price;
    
    
    /**
     * �������� �������?
     * @var bool
     */
    private $is_chain = false;
    private $chain = array();

    /*
    * ��������� ��������� ������� ������� ����
    */
    public $debug_calculations = false;
    public $debug_calculations_results = false;




    static $cache;
    
    /**
     * �������� � ������������ ������ ������������.
     */
    public function __construct(LinemediaAutoPartAll $part)
    {
    	if(!isset(self::$cache['events']['BaseCurrency'])) {
    		
    		if(!CModule::IncludeModule('currency'))
    			throw new Exception('No currency module');

    		self::$cache['events']['BaseCurrency'] = $this->currency = CCurrency::GetBaseCurrency();
    	} else {
	    	$this->currency = self::$cache['events']['BaseCurrency'];
    	}
    	
    	
        global $USER;
        $USER = ($USER) ? $USER : new CUser();
        $this->USER = $USER;

        $this->user_id = $USER->getID();
        $this->date = time();
        $this->part = $part;

        /*
         * ������ �������
         */
        if(!isset(self::$cache['events']['OnItemPriceConstruct'])) {
			$events = GetModuleEvents("linemedia.auto", "OnItemPriceConstruct");
			while ($arEvent = $events->Fetch()) {
				self::$cache['events']['OnItemPriceConstruct'][] = $arEvent;
			}
		}
		$events = self::$cache['events']['OnItemPriceConstruct'];
		
		foreach ($events AS $arEvent) {
			ExecuteModuleEventEx($arEvent, array(
			    &$this,
			));
		}        
        
        
        // ��� ������� ����� ��������� � ������ part ������ �� ��
        if (!$this->part->exists()) {
            LinemediaAutoDebug::add('Error price calculation, part id=' . $this->part->get('ID') . ' not found', false, LM_AUTO_DEBUG_WARNING);
        }
        
        // ��� ����?
        if ($this->part->get($this->price_field) <= 0) {
        	/*
        	* � ������ ������ ��� ���������� ���� ����, ��������� ������������ ����������
        	*/
        	if($this->price_field != 'price') {
	        	$this->price_field = 'price';
	        	if ($this->part->get($this->price_field) <= 0) {
	        		LinemediaAutoDebug::add('Error price calculation, part id=' . $this->part->get('ID') . ' price is zero!', false, LM_AUTO_DEBUG_WARNING);
	        	}
        	}
        }

        $this->base_price = $this->part->get($this->price_field);
        $this->base_price = preg_replace("/[^0-9,.]/", "", $this->base_price);
        $this->base_price = (float) $this->base_price;
    }
    
    
    /**
    * �������� �������
    */
    public function setChain($data = array())
    {
	    $this->is_chain = true;
	    $this->chain = $data;
    }
    
    public function isChain()
    {
	    return $this->is_chain;
    }
    
    public function getChain()
    {
	    return $this->chain;
    }
    
    
    public function __destruct()
    {
        if ($this->part) {
            unset($this->part);
        }
        if ($this->USER) {
            unset($this->USER);
        }
    }

    /**
     * ��������� ID ������������.
     */
    public function setUserID($user_id)
    {
        $this->user_id = (int) $user_id;
    }

    /**
     * ��������� ����
     */
    public function setDate($date)
    {
        $this->date = strtotime(strval($date));
    }

    /**
     * ��������� ������� ����������
     */
    public function setExternal($key, $value)
    {
        $this->external[$key] = $value;
    }
    
    /**
     * ��������������� ������� ����.
     */
    public function calculate($price_format = '%i')
    {
    
        $part_id = $this->part_id;
        $price = $this->base_price;

        /*
        * ������� ����������� ����
        */
        if($this->debug_calculations)	
        	$this->debug_calculations_results[] = GetMessage('LM_AUTO_MAIN_PRICE_PRICELIST') . ' <b>' . $price . '</b>';
        

        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnItemPriceCalculate");
        
		while ($arEvent = $events->Fetch()) {
			ExecuteModuleEventEx($arEvent, array(
			    &$this->part,
			    &$price,
			    &$this->currency,
			    &$this->user_id,
			    &$this->date,
			    &$this->debug_calculations_results,
                &$this->external,
                &$this
			));
		}
		

		/*
        * ������� ����������� ����
        */
        if($this->debug_calculations)	
        	$this->debug_calculations_results[] = GetMessage('LM_AUTO_MAIN_PRICE_FINAL') . ' <b>' . number_format($price, 2, '.', ' ') . ' ' . $this->getCurrency() . '</b>';
		
        
        return $price;
    }
    
    
    /**
     * ��������� ������.
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /*
    * ��������� ��������� ������� ������� ����
    */
    public function enableDebugCollection()
    {
	    $this->debug_calculations = true;
    }
    
    /*
    * ��������� ����������� ��������� ������� ������� ����
    */
    public function getDebug()
    {
	    return $this->debug_calculations_results;
    }

    /**
     * ��������� ��� �������� �������.
     * ������� ������� - ���������� ����
     * ������� ���� ����� ������������ ������
     * @return array
     */
    public function getRetailChain() {

        /**
         * �������� �������
         */
        $chain = array();

        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnItemPriceGetRetailChain");

        while ($arEvent = $events->Fetch()) {

            ExecuteModuleEventEx($arEvent, array(
                &$this->part,
                &$chain,
                &$this->user_id,
                &$this->debug_calculations_results,
            ));
        }

        $chain[] = array(
            'price' => $this->base_price,
            'branch_id' => 0,
            'director_id' => 0,
        );

        /*if($this->debug_calculations) {
            foreach($chain as $item) {
                $this->debug_calculations_results[] = $item['branch_id'] . ' => ' . $item['price'];
            }
        }*/


        return $chain;
    }

    /**
     * ������� �������� ���� � ��������� ������ � ������ ������������
     * @param $price - �������� ��� ��������� ������������� ����
     * @return mixed
     */
    public static function userPrice($price, $date = "", $base_currency = false) {

        global $USER;

        if(CModule::IncludeModule('currency')) {

            $user_currency = $USER->GetParam('CURRENCY');
            $default_currency = $base_currency ? $base_currency : CCurrency::GetBaseCurrency();

            if(!empty($user_currency) && $user_currency != $default_currency) {

                if(is_numeric($price)) {
                    $price = CCurrencyRates::ConvertCurrency($price, $default_currency, $user_currency, $date);
                } else {
                    $price_num = preg_replace("/[^0-9,.]/", "", $price);
                    $new_price = CCurrencyRates::ConvertCurrency($price_num, $default_currency, $user_currency, $date);

                    $price = CurrencyFormat($new_price, $user_currency);
                }
            }
        }

        return $price;
    }

    public static function userAdminFormatCurrency($price, $base_currency = false, $date = "", $keep_original = true) {

        if(CModule::IncludeModule('sale') && CModule::IncludeModule('currency')) {

            $price_conv = self::userPrice(SaleFormatCurrency($price, $base_currency), $date, $base_currency);
            $base_currency = $base_currency ? $base_currency : CCurrency::GetBaseCurrency();
            $price_orig = SaleFormatCurrency($price, $base_currency);

            if($price_conv != $price_orig && $keep_original) {
                return $price_conv . '<br /><nobr>(' . $price_orig . ')</nobr>';
            }
            return $price_conv;
        }
        return false;
    }
}

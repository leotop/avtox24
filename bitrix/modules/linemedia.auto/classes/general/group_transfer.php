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


/*
 * Класс, отвечающий за перевод пользователей по группам.
 */
class LinemediaAutoGroupTransfer
{
    protected $user_id = null;
    
    
    public function __construct($user_id)
    {
        $this->user_id = (int) $user_id;
        
        if ($this->user_id <= 0) {
            throw new Exception('Wrong user ID');
        }
    }
    
    
    /**
     * Получение ID пользователя.
     */
    public function getUserID()
    {
        return $this->user_id;
    }
    
    
    /**
     * Расчет общей суммы всех оплаченных пользователем заказов.
     */
    public function getUserSumm($period = false, $brands = array())
    {
    	global $DB;
        if (!CModule::IncludeModule('sale')) {
            return;
        }
        
        $summ = 0.0;
        
        
        
        $lmfilter = new LinemediaAutoBasketFilter();
        $lmfilter->setUserId($this->user_id);
		$lmfilter->setCustomField(array('CODE' => 'PAYED', 'VALUE' => 'Y'));
		
		$brands = array_map('trim', $brands);
		$brands = array_filter($brands);
		if(count($brands)) {
			$lmfilter->setBrandTitle($brands);
		}
        
        
        if($period) {
	        switch($period) {
		        case 'month':
		        	$start = ConvertTimeStamp(strtotime(date('Y-m')." -1 month"), 'FULL');
		        	$end   = ConvertTimeStamp(strtotime(date('Y-m')), 'FULL');
		        	$lmfilter->setDateFrom($start);
		        	$lmfilter->setDateTo($end);
		        break;
		        case 'quarter':
		        	
					$current_month = date('m');
					$current_year = date('Y');
					
					if($current_month>=1 && $current_month<=3) {
						$start_date = strtotime('1-October-'.($current_year-1));  // timestamp or 1-October Last Year 12:00:00 AM
						$end_date = strtotime('1-Janauary-'.$current_year);  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
					} else if($current_month>=4 && $current_month<=6) {
						$start_date = strtotime('1-January-'.$current_year);  // timestamp or 1-Janauray 12:00:00 AM
						$end_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
					} else  if($current_month>=7 && $current_month<=9) {
						$start_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
						$end_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
					} else  if($current_month>=10 && $current_month<=12) {
						$start_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
						$end_date = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
					}
		        	
		        	$start = ConvertTimeStamp($start_date, 'FULL');
		        	$end   = ConvertTimeStamp($end_date, 'FULL');
		        	$lmfilter->setDateFrom($start);
		        	$lmfilter->setDateTo($end);
		        break;
		        case 'year':
		        	$start = ConvertTimeStamp(strtotime('01-01-' . (date('Y')-1)), 'FULL');
		        	$end   = ConvertTimeStamp(strtotime('01-01-' . (date('Y')  )), 'FULL');
		        	
		        	$lmfilter->setDateFrom($start);
		        	$lmfilter->setDateTo($end);
		        	
		        break;
		        default:// days
		        	$start = ConvertTimeStamp(strtotime("-$period day"), 'FULL');
		        	$lmfilter->setDateFrom($start);
	        }
        }
        
        $aBasketItemsSFilter = $lmfilter->filter();
        $arBasketFilter = array();
		if ($lmfilter->isFiltered()) {
			if (!empty($aBasketItemsSFilter)) {
				if (isset($arBasketFilter['ID'])) {
					if (is_array($arBasketFilter['ID'])) {
						$arBasketFilter['ID'] = array_values(array_intersect($arBasketFilter['ID'], $aBasketItemsSFilter));
						if (count($arBasketFilter['ID']) == 0) {
							$arBasketFilter['ID'] = false;
						}
					}
				} else {
					$arBasketFilter['ID'] = $aBasketItemsSFilter;
				}
			} else {
				$arBasketFilter['ID'] = false;
			}
		}
        
        $dbOrders = CSaleBasket::getList(array(), $arBasketFilter, false, false, array('PRICE'));
        while ($arOrder = $dbOrders->Fetch()) {
            $summ += (float) $arOrder['PRICE'];
        }
        return $summ;
    }
    
    
    /**
     * Нахождение подходящих переводов.
     *
     * @param int $days_shift Сдвиг по дням, например (-2) можно постчитать, какие группы будут послезавтра, если не делать больше заказов
     */
    public function getSuitableGroupsList($days_shift = 0)
    {
    	$days2sum = array();
    	
    	
    	$user_groups = CUser::GetUserGroup($this->user_id);
    	
    	/**
    	* Пробежим по всем переводам и найдём подходящие по сумме / сроку расчёта
    	*/
    	$suitable = array();
        $all_transfers = self::getList();
        foreach($all_transfers AS $transfer) {
	        $days = $transfer['PROPS']['days']['VALUE'] + $days_shift;
	        $days = ($days > 0) ? $days : 0;
	        $min_sum = $transfer['PROPS']['summ']['VALUE'];
	        $brands = array_filter((array) $transfer['PROPS']['brands']['VALUE']);
	        
	        // не использовать скидку для пользователей, состоящих в группе N
	        $ignore_groups = array_filter((array) $transfer['PROPS']['ignore_groups']['VALUE']);
	        if(array_intersect($ignore_groups, $user_groups)) {
		        continue;
	        }
	        
	        $key = md5(json_encode($brands) . $days);
	        
	        if(!isset($days2sum[$key])) {
		        $days2sum[$key] = $this->getUserSumm($days, $brands);
	        }
	        
	        if($min_sum <= $days2sum[$key]) {
		        $suitable[] = $transfer;
	        }
	        
	        
        }
        return $suitable;
	}
    
    
    
    
    
    
    
    
    /**
     * Получение нужных групп пользователей.
     */
    public function getUserGroups()
    {
        // Группы, удовлетворяющие накопленной сумме пользователя.
        $arGroupTransfers = $this->getSuitableGroupsList();
        
        $arUserGroupsIn     = array(); // Группы для добавления
        $arUserGroupsOut    = array(); // Группы для удаления
        
        // Текущие группы пользователя.
        $arUserGroups = (array) CUser::GetUserGroup($this->getUserID());
        
        foreach ($arGroupTransfers as $arGroupTransfer) {
            $arUserGroupsIn  = array_filter((array) $arGroupTransfer['PROPS']['groups_in']['VALUE']);
            $arUserGroupsOut = array_filter((array) $arGroupTransfer['PROPS']['groups_out']['VALUE']);
            
            // Уберем лишние группы.
            $arUserGroups = array_diff($arUserGroups, $arUserGroupsOut);
            
            // Добавим нужные группы.
            $arUserGroups = array_merge($arUserGroups, $arUserGroupsIn);
        }
        $arUserGroups = array_unique($arUserGroups);
        
        return $arUserGroups;
    }
    
    
    /**
     * Список переводов по группам.
     * 
     * @param array $arSort
     * @param array $arFilter
     */
    public static function getList($arSort = array(), $arFilter = array())
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_GROUP_TRANSFER');
        
        $arSort     = (array) $arSort;
        $arFilter   = (array) $arFilter;
        
        $arFilter['IBLOCK_ID'] = $iblock_id;
        
        $arTransfers = array();
        $dbres = CIBlockElement::GetList($arSort, $arFilter, false, false, array());
        while ($dbTransfer = $dbres->GetNextElement()) {
            $arTransfer          = $dbTransfer->GetFields();
            $arTransfer['PROPS'] = $dbTransfer->GetProperties();
            
            $arTransfers[$arTransfer['ID']] = $arTransfer;
        }
        return $arTransfers;
    }
    
}

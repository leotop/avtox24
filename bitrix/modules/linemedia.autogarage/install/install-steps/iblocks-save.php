<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/*
 * Установка инфоблоков
 */
CModule::IncludeModule('iblock');

/*
 * Тип иноблоков
 */
$db_iblock_type = CIBlockType::GetList(array(), array('ID' => 'linemedia_auto'));
if (!($ar_iblock_type = $db_iblock_type->Fetch())) {
    ShowError(GetMessage('LM_AUTO_GARAGE_ERROR_CREATING_IBLOCK_NO_TYPE'));
    exit;
}


/*
 * какие сайты есть в системе?
 */
$sites = array();
$rsSites = CSite::GetList($by="sort", $order="desc", array());
while ($arSite = $rsSites->Fetch()) {
    $sites[] = $arSite['ID'];
}


/*
 * Добавление инфоблоков в новый тип
 */
$iblocks = array(
    
    /*
     * Гараж
     */
    array(
        'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_GARAGE'),
        'CODE' => 'garage',
        'PROPERTIES' => array(
            /*
             * Код VIN
             */
            array(
                'CODE' => 'vin',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_VIN'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Марка
             */
            array(
                'CODE' => 'brand',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_BRAND'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Марка (ID)
             */
            array(
                'CODE' => 'brand_id',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_BRAND_ID'),
                "PROPERTY_TYPE" => "S",
            ),
        	/*
        	 * Год
        	*/
        	array(
        		'CODE' => 'year',
        		'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_YEAR'),
        		"PROPERTY_TYPE" => "S",
        	),
            /*
             * Модель
             */
            array(
                'CODE' => 'model',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_MODEL'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Модель (ID)
             */
            array(
                'CODE' => 'model_id',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_MODEL_ID'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Модификация
             */
            array(
                'CODE' => 'modification',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_MODIFICATION'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Модификация (ID)
             */
            array(
                'CODE' => 'modification_id',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_MODIFICATION_ID'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Дополнительная информация (тюнинг и т.п.)
             */
            array(
                'CODE' => 'extra',
                'NAME' => GetMessage('LM_AUTO_GARAGE_IBLOCK_EXTRA'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" => "HTML" 
            ),
        ),
    ),
);



foreach ($iblocks as $SORT => $iblock) {
    /*
     * Если инфоблок уже есть - не создаём его
     */
    $res = CIBlock::GetList(array(), array('TYPE' => 'linemedia_auto', 'ACTIVE' => 'Y', 'CODE' => 'lm_auto_' . $iblock['CODE']), true);
    if ($found_iblock = $res->Fetch()) {
	    COption::SetOptionInt("linemedia.autogarage", "LM_AUTO_IBLOCK_" . $iblock['CODE'], $found_iblock['ID']);
    } else {
        /*
         * Инфоблока нет - создадим его
         */
        $CODE = strtoupper($iblock['CODE']);
        
        $ib = new CIBlock();
        
        $iblock['ACTIVE'] = 'Y';
        $iblock['CODE'] = 'lm_auto_' . $iblock['CODE'];
        $iblock['IBLOCK_TYPE_ID'] = 'linemedia_auto';
        $iblock['SITE_ID'] = $sites;
        $iblock['SORT'] = $SORT;
        $iblock['INDEX_ELEMENT'] = 'N';
        
        $IBLOCK_ID = $ib->Add($iblock);
        if ($IBLOCK_ID > 0) {
            COption::SetOptionInt("linemedia.autogarage", "LM_AUTO_IBLOCK_" . $CODE, $IBLOCK_ID);
        } else {
            ShowError('Error adding iblock ' . $iblock['CODE']);
        }
        
        /*
         * Установка прав на чтение для пользователей.
         */
        CIBlock::SetPermission($IBLOCK_ID, array(1 => 'X', '2' => 'R'));
                
        
        /*
         * Добавление свойств инфоблока
         */
        foreach ($iblock['PROPERTIES'] as $i => $PROP) {
            $PROP['ACTIVE'] = 'Y';
            $PROP['IBLOCK_ID'] = $IBLOCK_ID;
            $PROP['SORT'] = $i;
            
            $ibp = new CIBlockProperty();
            if (!$PropID = $ibp->Add($PROP)) {
                ShowError('Error adding iblock property ' . $PROP['NAME']);
            }
        }
        
        /*
         * Добавление элементов в инфоблок
         */
        foreach ($iblock['ELEMENTS'] as $ELEMENT) {
            $ELEMENT['ACTIVE'] = 'Y';
            $ELEMENT['IBLOCK_ID'] = $IBLOCK_ID;
            
            $el = new CIBlockElement();
            if (!$ELEMENT_ID = $el->Add($ELEMENT)) {
                ShowError('Error adding iblock element ' . $ELEMENT['NAME']);
            }
        }
        
    }

}

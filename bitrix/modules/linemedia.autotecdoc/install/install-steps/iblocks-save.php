<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/*
 * Установка инфоблоков
 */

CModule::IncludeModule('iblock');

/*
 * Тип иноблоков
 */
$db_iblock_type = CIBlockType::GetList(array(), array('ID' => 'linemedia_autotecdoc'));
if ($ar_iblock_type = $db_iblock_type->Fetch()) {
    // TODO: nothing
} else {
    $arFields = array(
	    'ID'        =>  'linemedia_autotecdoc',
	    'SECTIONS'  =>  'Y',
	    'IN_RSS'    =>  'N',
	    'SORT'      =>  100,
	    'LANG'      =>  array(
	        'en'    =>  array(
		        'NAME'          =>  'Linemedia Autoexpert TecDoc',
		        'SECTION_NAME'  =>  'Sections',
		        'ELEMENT_NAME'  =>  'Products'
	        ),
	        'ru'    =>  array(
		        'NAME'          =>  'Linemedia Автоэксперт TecDoc',
		        'SECTION_NAME'  =>  'Секции',
		        'ELEMENT_NAME'  =>  'Товары'
	        ),
	        'fr'    =>  array(
		        'NAME'          =>  'Linemedia Autoexpert TecDoc',
		        'SECTION_NAME'  =>  'Sections',
		        'ELEMENT_NAME'  =>  'Articles'
	        ),
	        'de'    =>  array(
		        'NAME'          =>  'Linemedia Autoexpert TecDoc',
		        'SECTION_NAME'  =>  'Sektions',
		        'ELEMENT_NAME'  =>  'Waren'
	        ),
        )
    );

    $obBlocktype = new CIBlockType();
    global $DB;
    $DB->StartTransaction();
    $res = $obBlocktype->Add($arFields);
    if (!$res) {
       $DB->Rollback();
       echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
    }
    else
       $DB->Commit();
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
     * Tecdoc права доступа
     */
    array(
        'NAME' => GetMessage('LM_AUTO_TECDOC_IBLOCK_TECDOC_ACCESS_LIST'),
        'CODE' => 'tecdoc_access_list',
        'PROPERTIES' => array(
            /*
             * Раздел API
             */
            array(
                'CODE' => 'api_section',
                'NAME' => GetMessage('LM_AUTO_TECDOC_IBLOCK_TECDOC_ACCESS_LIST_PROP_API_SECTION'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Компонент
             */
            array(
                'CODE' => 'component',
                'NAME' => GetMessage('LM_AUTO_TECDOC_IBLOCK_TECDOC_ACCESS_LIST_PROP_COMPONENT'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * ID элемента API
             */
            array(
                'CODE' => 'api_id',
                'NAME' => GetMessage('LM_AUTO_TECDOC_IBLOCK_TECDOC_ACCESS_LIST_PROP_API_ID'),
                "PROPERTY_TYPE" => "N",
                "IS_REQUIRED" => "N",
            ),
        )
    )
);



foreach ($iblocks as $SORT => $iblock) {
    /*
     * Если инфоблок уже есть - не создаём его
     */
    $res = CIBlock::GetList(array(), array('TYPE' => 'linemedia_autotecdoc', 'ACTIVE' => 'Y', 'CODE' => 'lm_auto_' . $iblock['CODE']), true);
    if ($found_iblock = $res->Fetch()) {
	    COption::SetOptionInt("linemedia.autotecdoc", "LM_AUTO_TECDOC_IBLOCK_" . $iblock['CODE'], $found_iblock['ID']);
    } else {
        /*
         * Инфоблока нет - создадим его
         */
        $CODE = strtoupper($iblock['CODE']);
        
        $ib = new CIBlock();
        
        $iblock['ACTIVE'] = 'Y';
        $iblock['CODE'] = 'lm_auto_' . $iblock['CODE'];
        $iblock['IBLOCK_TYPE_ID'] = 'linemedia_autotecdoc';
        $iblock['SITE_ID'] = $sites;
        $iblock['SORT'] = $SORT;
        $iblock['INDEX_ELEMENT'] = 'N';
        
        $IBLOCK_ID = $ib->Add($iblock);
        if ($IBLOCK_ID > 0) {
            COption::SetOptionInt("linemedia.autotecdoc", "LM_AUTO_IBLOCK_" . $CODE, $IBLOCK_ID);
        } else {
            ShowError('Error adding iblock ' . $iblock['CODE']);
        }
        
        /*
         * Добавление свойств инфоблока
         */
        foreach($iblock['PROPERTIES'] as $i => $PROP) {
            $PROP['ACTIVE'] = 'Y';
            $PROP['IBLOCK_ID'] = $IBLOCK_ID;
            $PROP['SORT'] = $i;
            
            $ibp = new CIBlockProperty;
            if(!$PropID = $ibp->Add($PROP)) {
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



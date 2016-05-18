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
if ($ar_iblock_type = $db_iblock_type->Fetch()) {
   
} else {
    $arFields = array(
        'ID'        =>  'linemedia_auto',
        'SECTIONS'  =>  'Y',
        'IN_RSS'    =>  'N',
        'SORT'      =>  100,
        'LANG'      =>  array(
            'en'    =>  array(
                'NAME'          =>  'Linemedia Autoexpert',
                'SECTION_NAME'  =>  'Sections',
                'ELEMENT_NAME'  =>  'Products'
            ),
            'ru'    =>  array(
                'NAME'          =>  GetMessage('LM_AUTO_MAIN_IBLOCK_AUTOEXPERT'),
                'SECTION_NAME'  =>  GetMessage('LM_AUTO_MAIN_IBLOCK_SECTIONS'),
                'ELEMENT_NAME'  =>  GetMessage('LM_AUTO_MAIN_IBLOCK_GOODS'),
            ),
            'fr'    =>  array(
                'NAME'          =>  'Linemedia Autoexpert',
                'SECTION_NAME'  =>  'Sections',
                'ELEMENT_NAME'  =>  'Articles'
            ),
            'de'    =>  array(
                'NAME'          =>  'Linemedia Autoexpert',
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
    } else {
       $DB->Commit();
    }
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
     * Поставщики
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS'),
        'CODE' => 'suppliers',
        'ELEMENT_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_NAME'),
        'ELEMENTS_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENTS_NAME'),
        'ELEMENT_ADD' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_ADD'),
        'ELEMENT_EDIT' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_EDIT'),
        'ELEMENT_DELETE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_DELETE'),
        'PROPERTIES' => array(
            /*
             * ID поставщика
             */
            array(
                'CODE' => 'supplier_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "Y",
            ),
            /*
             * Наценка
             */
            array(
                'CODE' => 'markup',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_MARKUP'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Срок доставки
             */
            array(
                'CODE' => 'delivery_time',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_DELIVERY_TIME'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Название для пользователей
             */
            array(
                'CODE' => 'visual_title',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_VISUAL_TITLE'),
                "PROPERTY_TYPE" => "S",
                "REQUIRED"      => 'Y',
            ),
            /*
             * email
             */
            array(
                'CODE' => 'email',
                'NAME' => 'e-mail',
                "PROPERTY_TYPE" => "S",
                "REQUIRED"      => 'N',
            ),
            /*
             * Валюта прайслиста
             */
            array(
                'CODE' => 'currency',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_CURRENCY'),
                "PROPERTY_TYPE" => "N",
                "USER_TYPE" 	=> "currency",
                "REQUIRED"      => 'Y',
            ),
            /*
             * CSS
             */
            array(
                'CODE' => 'css',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_CSS'),
                "PROPERTY_TYPE" => "S",
                "REQUIRED"      => 'N',
            ),
            
            /*
             * checkbox own store
             */
            array(
                'CODE' => 'own_store',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_STORE'),
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE'     => 'Checkbox',
            ),
            /*
             * checkbox returns_banned
             */
            array(
                'CODE' => 'returns_banned',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_RETURN_GOODS'),
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE'     => 'Checkbox',
            )
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
            array(
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_SUPPLIER_1'),
                'PROPERTY_VALUES' => array(
                    'supplier_id' => '1',
                    'markup' => '10',
                    'visual_title' => 'brg',
                ),
            ),
            array(
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_SUPPLIER_2'),
                'PROPERTY_VALUES' => array(
                    'supplier_id' => '2',
                    'markup' => '15',
                    'visual_title' => 'world',
                ),
            ),
        ),
        'FORMS' => array(
        	'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_SUPPLIER_ID#,PROPERTY_#PROP_VISUAL_TITLE#,PROPERTY_#PROP_DELIVERY_TIME#,PROPERTY_#PROP_API#',
        	'EDIT' => array(
        		array(
		          'CODE' => 'edit1',
		          'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIER'),
		          'FIELDS' => array(
		               array(
		                    'NAME' => 'NAME',
		                    'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_NAME'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_MARKUP#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_MARKUP'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_VISUAL_TITLE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_VISUAL_TITLE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_SUPPLIER_ID#',
		                   'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_ID'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_EMAIL#',
		                   'TITLE' => 'e-mail',
		               ),
		               array(
		                   'NAME' => 'IBLOCK_ELEMENT_PROP_VALUE',
		                   'TITLE' => '--' . GetMessage('LM_AUTO_MAIN_IBLOCK_ADDITIONAL_SETTINGS'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_DELIVERY_TIME#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_DELIVERY_TIME'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_CURRENCY#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_CURRENCY'),
		               ),
		           ),
		       ),
		    ),
        ),
    ),
    
    //search modificator
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIFICATOR'),
        'CODE' => 'modificator',
        'PROPERTIES' => array(
            /*
             * action
    */
            array(
                'CODE' => 'action',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION'),
                "PROPERTY_TYPE" => "L",
                "IS_REQUIRED" => "Y",
                "VALUES" => array(
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_SORT'),
                        "DEF" => "N",
                        "SORT" => "1",
                        'XML_ID' => 'SortModificator',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_LIMIT'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'TrimModificator',
                    ),
                    array(
                        "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_HIDE'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'ConcelaModificator',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_UP'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'UpModificator',
                    ),
                    array(
                        "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_FIELD'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'VaryFieldModificator',
                    ),
                ),
    ),
    /*
     * limit
     */
    array(
    'CODE' => 'limit',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_LIMIT'),
    "PROPERTY_TYPE" => "N",
    ),
    /*
     * sort_field
     */
    array(
    'CODE' => 'sort_field',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_SORT'),
    "PROPERTY_TYPE" => "L",
    "VALUES" => array(
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_SORT_PRICE'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'price_src',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_SORT_DELIVERY'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'delivery',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_SORT_QUANTITY'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'quantity',
    ),
    )
    ),
    /*
     * sort_order
     */
    array(
    'CODE' => 'sort_order',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ORDER'),
    "PROPERTY_TYPE" => "L",
    "VALUES" => array(
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_SORT_DESC'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'desc',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_SORT_ASC'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'asc',
    ),
    )
    ),
    /*
     * hide suppliers
     */
    array(
    'CODE' => 'hide_suppliers',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_HIDE_SUPP'),
    "PROPERTY_TYPE" => "E",
    //'USER_TYPE' => "EAutocomplete",
    'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS"),
    'MULTIPLE'      => 'Y',
    'MULTIPLE_CNT' => 1,
    ),
    /*
     *filter_overall_count_min
     */
    array(
    'CODE' => 'filter_overall_count_min',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_COUNT_MIN'),
    "PROPERTY_TYPE" => "N",
    ),
    /*
     * filter_overall_count_max
     */
    array(
    'CODE' => 'filter_overall_count_max',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_COUNT_MAX'),
    "PROPERTY_TYPE" => "N",
    ),
    
    /*
     * filter_part_group
     */
    array(
    'CODE' => 'filter_part_group',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_PART_GROUP'),
    "PROPERTY_TYPE" => "L",
    'MULTIPLE'      => 'Y',
    'MULTIPLE_CNT' => 1,
    "VALUES" => array(
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_PART_OR'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'analog_type_N',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_UNOR'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'analog_type_0',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_OEM'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_1',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_PART_BOUGHT_N'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'analog_type_2',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_COMP_N'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'analog_type_3',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_REPLACE'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_4',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_OBSOLETE'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_5',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_EAN'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_6',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_OTHER'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_10',
    ),
    )
    ),
    
    /*
     * filter_user_group
     */
    array(
    'CODE' => 'filter_user_group',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_USER_GROUP'),
    "PROPERTY_TYPE" => "S",
    'USER_TYPE' => "user_group",
    'MULTIPLE'      => 'Y',
    'MULTIPLE_CNT' => 1,
    ),
    
    /*
     *field_code
     */
    array(
    'CODE' => 'field_code',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_FIELD_CODE'),
    "PROPERTY_TYPE" => "L",
    "VALUES" => array(
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_CODE_ARTICLE'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'article',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_CODE_TITLE'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'title',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_CODE_BRAND'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'brand_title',
    ),
    )
    ),
    
    /*
     * field_action
     */
    array(
    'CODE' => 'field_action',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_FIELD_ACTION'),
    "PROPERTY_TYPE" => "L",
    "VALUES" => array(
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_FIELD_REPLACE'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'replace',
    ),
    )
    ),
    
    /*
     * field_action_data
     */
    array(
    'CODE' => 'field_action_data',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_DATA'),
    "PROPERTY_TYPE" => "S",
    ),
    
    /*
     * linked_suppliers
     */
    array(
    'CODE' => 'linked_suppliers',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_LINKED_SUPP'),
    "PROPERTY_TYPE" => "E",
    //'USER_TYPE' => "EAutocomplete",
    'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS"),
    'MULTIPLE'      => 'Y',
    // 'MULTIPLE_CNT' => 1,
    ),
    
    /*
     * filter_existing_supplier
     */
    array(
    'CODE' => 'filter_existing_supplier',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_EX_SUPP'),
    "PROPERTY_TYPE" => "E",
    //'USER_TYPE' => "EAutocomplete",
    'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS"),
    'MULTIPLE'      => 'Y',
    //'MULTIPLE_CNT' => 1,
    ),
    
    /*
     * up_suppliers
     */
    array(
    'CODE' => 'up_suppliers',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_UP_SUPP'),
    "PROPERTY_TYPE" => "E",
    //'USER_TYPE' => "EAutocomplete",
    'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS"),
    'MULTIPLE'      => 'Y',
    'MULTIPLE_CNT' => 1,
    ),
    
    /* affected count of elements by action
     */
    array(
       'CODE' => 'affected_element_by_action',
       'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_AFF_ELEMENTS_BY_ACTION'),
        "PROPERTY_TYPE" => "N",
    ),
    
    /*
     * omit_modificators
     */
     array(
        'CODE' => 'omit_modificators',
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_OMIT_MODIFICATORS'),
        "PROPERTY_TYPE" => "E",
        'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_MODIFICATOR"),
        'MULTIPLE'      => 'Y',
     	'MULTIPLE_CNT' => 1,
     ),
     
      /*
       * aware_modificators
       */
      array(
        'CODE' => 'aware_modificator',
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_AWARE_MODIFICATOR'),
        "PROPERTY_TYPE" => "E",
        'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_MODIFICATOR"),
        'MULTIPLE'      => 'N',
      ),
    
     
        		
    /*
     * affected_groups
     */
    array(
    'CODE' => 'affected_groups',
    'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_AFF_SUPP'),
    "PROPERTY_TYPE" => "L",
    'MULTIPLE'      => 'Y',
    "VALUES" => array(
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_PART_OR'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'analog_type_N',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_UNOR'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'analog_type_0',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_OEM'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_1',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_PART_BOUGHT_N'),
    "DEF" => "N",
    "SORT" => "1",
    'XML_ID' => 'analog_type_2',
    ),
    array(
    "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_COMP_N'),
    "DEF" => "N",
    "SORT" => "2",
    'XML_ID' => 'analog_type_3',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_REPLACE'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_4',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_OBSOLETE'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_5',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_EAN'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_6',
    ),
    array(
    "VALUE" =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_PART_OTHER'),
    "DEF" => "N",
    "SORT" => "3",
    'XML_ID' => 'analog_type_10',
    ),
    )
    ),
        ),
    
    
        'FORMS' => array(
        'LIST' => 'ACTIVE,SORT,NAME,PROPERTY_#PROP_ACTION#,PROPERTY_#PROP_AFF#,PROPERTY_#PROP_MIN#,
             PROPERTY_#PROP_MAX#,PROPERTY_#PROP_PART#,PROPERTY_#PROP_USER#,PROPERTY_#PROP_EX#,PROPERTY_#PROP_ORDER#,PROPERTY_#PROP_SORT#,
             PROPERTY_#PROP_LIMIT#,PROPERTY_#PROP_UP#,PROPERTY_#PROP_HIDE#,PROPERTY_#PROP_DATA#,PROPERTY_#PROP_FIELD#,PROPERTY_#PROP_LINKED#,
             PROPERTY_#PROP_AFIELD#',
                 'EDIT' => array(
                  
                 //action
        array(
        'CODE' => 'edit1',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_ACTION'),
        'FIELDS' => array(
        array(
        'NAME' => 'ACTIVE',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_BX_ACTIVE'),
        ),
        array(
        'NAME' => 'SORT',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_BX_SORT'),
        ),
        array(
        'NAME' => 'NAME',
        'TITLE' => '*'.GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_BX_NAME'),
        ),
        array(
        	'NAME' => 'ACTIVE_FROM',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_BX_FROM_ACTIVE'),
        ),
        array(
        	'NAME' => 'ACTIVE_TO',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_BX_TO_ACTIVE'),
        ),
        ),
        ),
    
        //subaction
        array(
        'CODE' => 'edit2',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_SUB_ACTION'),
        'FIELDS' => array(
        array(
        'NAME' =>  'PROPERTY_#PROP_ACTION#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION'),
        ),
        array(
           'NAME' => 'PROPERTY_#PROP_AFFECTED_ELEMENT_BY_ACTION#',
           'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_AFF_ELEMENTS_BY_ACTION'),
        ),
        ),
        ),
    
    
       //condition
        array(
        'CODE' => 'cedit3',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_COND'),
        'FIELDS' => array(
        array(
        	'NAME' => 'PROPERTY_#PROP_FILTER_USER_GROUP#',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_USER_GROUP')
        ),
        array(
           'NAME' => 'PROPERTY_#PROP_FILTER_PART_GROUP#',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_PART_GROUP')
        ),
        array(
        	'NAME' => 'PROPERTY_#PROP_FILTER_EXISTING_SUPPLIER#',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_EX_SUPP')
        ),
        array(
        	'NAME' => 'PROPERTY_#PROP_AFFECTED_GROUPS#',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_AFF_SUPP'),
        ),
        array(
        	'NAME' => 'PROPERTY_#PROP_OMIT_MODIFICATORS#',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_OMIT_MODIFICATORS'),
        ),
        array(
        	'NAME' => 'PROPERTY_#PROP_AWARE_MODIFICATOR#',
        	'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_AWARE_MODIFICATOR'),
        ),
        array(
        'NAME' => 'PROPERTY_#PROP_FILTER_OVERALL_COUNT_MIN#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_COUNT_MIN'),
        ),
        array(
        'NAME' => 'PROPERTY_#PROP_FILTER_OVERALL_COUNT_MAX#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_COUNT_MAX')
        ),
        ),
        ),
    
        //sort
        array(
        'CODE' => 'cedit4',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_SORT'),
        'FIELDS' => array(
        array(
        'NAME' => 'PROPERTY_#PROP_SORT_ORDER#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ORDER')
        ),
        array(
        'NAME' => 'PROPERTY_#PROP_SORT_FIELD#',
        'TITLE' =>  GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_SORT')
        )
        ),
        ),
    
    
        array(
        'CODE' => 'cedit5',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_LIMIT'),
        'FIELDS' => array(
        array(
        'NAME' => 'PROPERTY_#PROP_LIMIT#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_LIMIT')
        ),
        ),
        ),
    
        array(
        'CODE' => 'cedit6',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_UP_SUPP'),
        'FIELDS' => array(
        array(
        'NAME' => 'PROPERTY_#PROP_UP_SUPPLIERS#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_UP_SUPP')
        ),
        ),
        ),
    
        array(
        'CODE' => 'cedit7',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_HIDE_SUPP'),
        'FIELDS' => array(
        array(
        'NAME' => 'PROPERTY_#PROP_HIDE_SUPPLIERS#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_HIDE_SUPP')
        ),
        ),
        ),
    
    
        array(
        'CODE' => 'cedit8',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_TITLE_VERY_FIELD'),
        'FIELDS' => array(
        array(
        'NAME' => 'PROPERTY_#PROP_FIELD_ACTION_DATA#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_ACTION_DATA')
        ),
        array(
        'NAME' => 'PROPERTY_#PROP_FIELD_CODE#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_FIELD_CODE')
        ),
        array(
        'NAME' => 'PROPERTY_#PROP_LINKED_SUPPLIERS#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_LINKED_SUPP')
        ),
        array(
        'NAME' => 'PROPERTY_#PROP_FIELD_ACTION#',
        'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_MODIF_FIELD_ACTION')
        ),
        ),
        ),
        ),
        ),
    ),
    

    
    /*
     * SEO текст в поиске
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO'),
        'CODE' => 'search_seo',
        'PROPERTIES' => array(
            /*
             * Артикул
             */
            array(
                'CODE' => 'article',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_ARTICLE'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Название бренда
             */
            array(
                'CODE' => 'brand_title',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_BRAND_TITLE'),
                "PROPERTY_TYPE" => "N",
            ),
        ),
        /*
        * Примеры
        */
        'ELEMENTS' => array(
            array(
                'NAME' => 'gdb1550',
                "DETAIL_TEXT" => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_GOOD_DESCR'),
                "DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/images/linemedia.auto/gdb1550.jpg"),
                'PROPERTY_VALUES' => array(
                    'article' => 'gdb1550',
                    'brand_title' => 'TRW',
                ),
            ),
        ),
        'FORMS' => array(
        	'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_ARTICLE#,PROPERTY_#PROP_BRAND_TITLE#,DETAIL_PICTURE',
        	'EDIT' => array(
        		array(
		          'CODE' => 'edit1',
		          'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO'),
		          'FIELDS' => array(
		               array(
		                    'NAME' => 'NAME',
		                    'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_NAME'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_ARTICLE#',
		                   'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_ARTICLE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_BRAND_TITLE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_BRAND_TITLE'),
		               ),
		               array(
		                   'NAME' => 'DETAIL_TEXT',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEO_DETAIL_TEXT'),
		               ),
		               array(
		                   'NAME' => 'DETAIL_PICTURE',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEO_DETAIL_PICTURE'),
		               ),
		           ),
		       ),
		    ),
        ),
    ),
    
    
    
    /*
     * Tecdoc права доступа
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST'),
        'CODE' => 'tecdoc_access_list',
        'PROPERTIES' => array(
            /*
             * Раздел API
             */
            array(
                'CODE' => 'api_section',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST_PROP_API_SECTION'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Компонент
             */
            array(
                'CODE' => 'component',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST_PROP_COMPONENT'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * ID элемента API
             */
            array(
                'CODE' => 'api_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST_PROP_API_ID'),
                "PROPERTY_TYPE" => "N",
                "IS_REQUIRED" => "N",
            ),
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
        )
    ),
    
    
    /*
     * Скидки
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT'),
        'CODE' => 'discount',
        'ELEMENT_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_NAME'),
        'ELEMENTS_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENTS_NAME'),
        'ELEMENT_ADD' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_ADD'),
        'ELEMENT_EDIT' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_EDIT'),
        'ELEMENT_DELETE' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_DELETE'),
        'PROPERTIES' => array(
            /*
            * Артикул
            */
            array(
                'CODE' => 'article',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_ARTICLE'),
                "PROPERTY_TYPE" => "S",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Название бренда
             */
            array(
                'CODE' => 'brand_title',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_BRAND_TITLE'),
                "PROPERTY_TYPE" => "S",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Група пользователя
             */
            array(
                'CODE' => 'user_group',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_USER_GROUP'),
                "PROPERTY_TYPE" => "S",
                'USER_TYPE' => "user_group",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Пользователь
             */
            array(
                'CODE' => 'user_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_USER_ID'),
                "PROPERTY_TYPE" => "S",
                'USER_TYPE' => "UserID",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Поставщик
             */
            array(
                'CODE' => 'supplier_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_SUPPLIER_ID'),
                "PROPERTY_TYPE" => "E",
                //'USER_TYPE' => "EAutocomplete",
                'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS"),
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Мин цена
             */
            array(
                'CODE' => 'price_min',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_PRICE_MIN'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Макс цена
             */
            array(
                'CODE' => 'price_max',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_PRICE_MAX'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Изменение (%)
             */
            array(
                'CODE' => 'discount',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT'),
                "PROPERTY_TYPE" => "N",
                //'PROPERTY_HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_HINT'),
            ),
            /*
             * Тип скидки (Способ расчёта цены)
             */
            array(
                'CODE' => 'discount_type',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE'),
                "PROPERTY_TYPE" => "L",
                'REQUIRED' => 'Y',
                "VALUES" => array(
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_MARKUP_DISCOUNT'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'SUPPLIER_MARKUP_DISCOUNT',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_FINAL_PRICE_DISCOUNT'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'FINAL_PRICE_DISCOUNT',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_BASE_PRICE_MARKUP'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'BASE_PRICE_MARKUP',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_CALCULATED_PRICE_MARKUP'),
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'CALCULATED_PRICE_MARKUP',
                    ),
                ),
            ),
            
            
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
            array(
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_SALE'),
                'PROPERTY_VALUES' => array(
                    'article' => 'gdb1550',
                    'user_group' => Array("VALUE" => 1),
                    'discount' => 30,
                ),
            ),
        ),
        'FORMS' => array(
        	'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_ARTICLE#,PROPERTY_#PROP_BRAND_TITLE#,PROPERTY_#PROP_SUPPLIER_ID#,PROPERTY_#PROP_DISCOUNT#,PROPERTY_#PROP_DISCOUNT_TYPE#',
        	'EDIT' => array(
        		array(
		          'CODE' => 'edit1',
		          'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE'),
		          'FIELDS' => array(
		               array(
		                    'NAME' => 'ACTIVE',
		                    'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTIVE'),
		               ),
		               array(
		                    'NAME' => 'ACTIVE_FROM',
		                    'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTIVE_FROM'),
		               ),
		               array(
		                    'NAME' => 'ACTIVE_TO',
		                    'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTIVE_TO'),
		               ),
		               array(
		                    'NAME' => 'NAME',
		                    'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_NAME'),
		               ),
		               array(
		                    'NAME' => 'CONDITIONS',
		                    'TITLE' => '--' . GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_CONDITIONS'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_ARTICLE#',
		                   'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ARTICLE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_BRAND_TITLE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_BRAND_TITLE'),
		               ),
		               
		               array(
		                   'NAME' => 'PROPERTY_#PROP_USER_GROUP#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_USER_GROUP'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_USER_ID#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_USER_ID'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_SUPPLIER_ID#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_SUPPLIER_ID'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_PRICE_MIN#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_PRICE_MIN'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_PRICE_MAX#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_PRICE_MAX'),
		               ),
		               
		               array(
		                   'NAME' => 'ACTION',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTION'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_DISCOUNT_TYPE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_DISCOUNT_TYPE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_DISCOUNT#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_DISCOUNT'),
		               ),
		               
		           ),
		       ),
		    ),
        ),
    ),
    
    
    /*
     * Автопереводы в группы
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER'),
        'CODE' => 'group_transfer',
        'ELEMENT_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_NAME'),
        'ELEMENTS_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENTS_NAME'),
        'ELEMENT_ADD' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_ADD'),
        'ELEMENT_EDIT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_EDIT'),
        'ELEMENT_DELETE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_DELETE'),
        'PROPERTIES' => array(
            /*
             * Сумма для перехода
             */
            array(
                'CODE' => 'summ',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_SUMM'),
                'SORT' => '100',
                'PROPERTY_TYPE' => 'N',
                'MULTIPLE' => 'N',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'Y',
                'USER_TYPE' => NULL,
            ),
            /*
             * Группы, в которые входит пользователь.
             */
            array(
                'CODE' => 'groups_in',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_IN'),
                'HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_IN_HINT'),
                'SORT' => '200',
                'PROPERTY_TYPE' => 'S',
                'MULTIPLE' => 'Y',
                'MULTIPLE_CNT' => '1',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'N',
                'USER_TYPE' => 'user_group',
            ),
            /*
             * Группы, из которых выходит пользователь.
             */
            array(
                'CODE' => 'groups_out',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_OUT'),
                'HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_OUT_HINT'),
                'SORT' => '300',
                'PROPERTY_TYPE' => 'S',
                'MULTIPLE' => 'Y',
                'MULTIPLE_CNT' => '1',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'N',
                'USER_TYPE' => 'user_group',
            ),
            
            
            
            /**
            * Не применять для состоящих в
            */
            array (
			  'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_IGNORE_GROUPS'),
			  'ACTIVE' => 'Y',
			  'SORT' => '20',
			  'CODE' => 'ignore_groups',
			  'DEFAULT_VALUE' => '',
			  'PROPERTY_TYPE' => 'S',
			  'ROW_COUNT' => '1',
			  'COL_COUNT' => '30',
			  'LIST_TYPE' => 'L',
			  'MULTIPLE' => 'Y',
			  'XML_ID' => NULL,
			  'FILE_TYPE' => '',
			  'MULTIPLE_CNT' => '1',
			  'TMP_ID' => NULL,
			  'WITH_DESCRIPTION' => 'N',
			  'SEARCHABLE' => 'N',
			  'FILTRABLE' => 'N',
			  'IS_REQUIRED' => 'N',
			  'VERSION' => '1',
			  'USER_TYPE' => 'user_group',
			  'USER_TYPE_SETTINGS' => NULL,
			  'HINT' => '',
			  'IBLOCK_TYPE_ID' => 'linemedia_auto',
			  'IBLOCK_CODE' => 'lm_auto_group_transfer',
			),
			
			/**
            * Учитываемый период (дн)
            */
			array (
			  'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_DAYS'),
			  'ACTIVE' => 'Y',
			  'SORT' => '5',
			  'CODE' => 'days',
			  'DEFAULT_VALUE' => '0',
			  'PROPERTY_TYPE' => 'N',
			  'ROW_COUNT' => '1',
			  'COL_COUNT' => '30',
			  'LIST_TYPE' => 'L',
			  'MULTIPLE' => 'N',
			  'XML_ID' => NULL,
			  'FILE_TYPE' => '',
			  'MULTIPLE_CNT' => '1',
			  'WITH_DESCRIPTION' => 'N',
			  'SEARCHABLE' => 'Y',
			  'FILTRABLE' => 'Y',
			  'IS_REQUIRED' => 'N',
			  'VERSION' => '1',
			  'USER_TYPE' => NULL,
			  'USER_TYPE_SETTINGS' => NULL,
			  'HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_DAYS_HINT'),
			  'IBLOCK_TYPE_ID' => 'linemedia_auto',
			  'IBLOCK_CODE' => 'lm_auto_group_transfer',
			),
			
			/**
			* Бренды
			*/
			array (
			  'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_BRANDS'),
			  'ACTIVE' => 'Y',
			  'SORT' => '12',
			  'CODE' => 'brands',
			  'DEFAULT_VALUE' => '',
			  'PROPERTY_TYPE' => 'S',
			  'ROW_COUNT' => '1',
			  'COL_COUNT' => '30',
			  'LIST_TYPE' => 'L',
			  'MULTIPLE' => 'Y',
			  'XML_ID' => NULL,
			  'FILE_TYPE' => '',
			  'MULTIPLE_CNT' => '1',
			  'TMP_ID' => NULL,
			  'WITH_DESCRIPTION' => 'N',
			  'SEARCHABLE' => 'N',
			  'FILTRABLE' => 'N',
			  'IS_REQUIRED' => 'N',
			  'VERSION' => '1',
			  'USER_TYPE' => NULL,
			  'USER_TYPE_SETTINGS' => NULL,
			  'HINT' => '',
			  'IBLOCK_TYPE_ID' => 'linemedia_auto',
			  'IBLOCK_CODE' => 'lm_auto_group_transfer',
			),
			
			/**
			* Учитываемый период
			*/
			array (
			  'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_PERIOD'),
			  'ACTIVE' => 'Y',
			  'SORT' => '8',
			  'CODE' => 'period',
			  'DEFAULT_VALUE' => '',
			  'PROPERTY_TYPE' => 'L',
			  'ROW_COUNT' => '1',
			  'COL_COUNT' => '30',
			  'LIST_TYPE' => 'L',
			  'MULTIPLE' => 'N',
			  'XML_ID' => NULL,
			  'FILE_TYPE' => '',
			  'MULTIPLE_CNT' => '5',
			  'TMP_ID' => NULL,
			  'WITH_DESCRIPTION' => 'N',
			  'SEARCHABLE' => 'N',
			  'FILTRABLE' => 'N',
			  'IS_REQUIRED' => 'N',
			  'VERSION' => '1',
			  'USER_TYPE' => NULL,
			  'USER_TYPE_SETTINGS' => NULL,
			  'HINT' => '',
			  'IBLOCK_TYPE_ID' => 'linemedia_auto',
			  'IBLOCK_CODE' => 'lm_auto_group_transfer',
			  'ENUM' => array(
			  	'month' => array(
			  		'VALUE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_PERIOD_MONTH'),
					'DEF' => 'N',
					'SORT' => '1',
					'XML_ID' => 'month',
					'EXTERNAL_ID' => 'month',
					'PROPERTY_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_PERIOD'),
					'PROPERTY_CODE' => 'period',
					'PROPERTY_SORT' => '8',
					'IBLOCK_TYPE_ID' => 'linemedia_auto',
					'IBLOCK_CODE' => 'lm_auto_group_transfer',

			  	),
			  	'quarter' => array(
			  		'VALUE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_QUARTER'),
					'DEF' => 'N',
					'SORT' => '2',
					'XML_ID' => 'quarter',
					'EXTERNAL_ID' => 'quarter',
					'PROPERTY_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_PERIOD'),
					'PROPERTY_CODE' => 'period',
					'IBLOCK_TYPE_ID' => 'linemedia_auto',
					'IBLOCK_CODE' => 'lm_auto_group_transfer',

			  	),
			  	'year' => array(
			  		'VALUE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_YEAR'),
					'DEF' => 'N',
					'SORT' => '3',
					'XML_ID' => 'year',
					'EXTERNAL_ID' => 'year',
					'PROPERTY_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_PERIOD'),
					'PROPERTY_CODE' => 'period',
					'IBLOCK_TYPE_ID' => 'linemedia_auto',
					'IBLOCK_CODE' => 'lm_auto_group_transfer',

			  	),
			  ),
			),
			
			
			
        ),
        
        
        
        
        
        
        
        'FORMS' => array(
            'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_SUMM#,PROPERTY_#PROP_GROUPS_IN#,PROPERTY_#PROP_GROUPS_OUT#',
            'EDIT' => array(
                array(
                  'CODE' => 'edit1',
                  'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER'),
                  'FIELDS' => array(
                       array(
                            'NAME' => 'NAME',
                            'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_NAME'),
                       ),
                       array(
                            'NAME' => 'ACTIVE',
                            'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_FORM_GROUP_ACTIVE'),
                       ),
                       array(
                            'NAME' => 'ACTIVE_FROM',
                            'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_FORM_GROUP_ACTIVE_FROM'),
                       ),
                       array(
                            'NAME' => 'ACTIVE_TO',
                            'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_FORM_GROUP_ACTIVE_TO'),
                       ),
                       array(
                           'NAME' => 'PROPERTY_#PROP_SUMM#',
                           'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_SUMM'),
                       ),
                       array(
                           'NAME' => 'PROPERTY_#PROP_GROUPS_IN#',
                           'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_IN'),
                       ),
                       array(
                           'NAME' => 'PROPERTY_#PROP_GROUPS_OUT#',
                           'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_OUT'),
                       ),
                   ),
               ),
            ),
        ),
    ),

    
    /*
     * Запросы по VIN
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN'),
        'CODE' => 'vin',
        'VERSION' => '2',
        'WORKFLOW' => 'N',
        'RIGHTS_MODE' => 'E',
        'PROPERTIES' => array(
            /*
             * Код VIN
             */
            array(
                'CODE' => 'vin',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_VIN'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "Y",
            ),
            
            /*
             * Год выпуска
             */
            array(
                'CODE' => 'year',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_YEAR'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Месяц выпуска
             */
            array(
                'CODE' => 'month',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MONTH'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Марка
             */
            array(
                'CODE' => 'brand',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BRAND'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Марка (ID)
             */
            array(
                'CODE' => 'brand_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BRAND_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модель
             */
            array(
                'CODE' => 'model',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODEL'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модель (ID)
             */
            array(
                'CODE' => 'model_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODEL_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модификация
             */
            array(
                'CODE' => 'modification',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODIFICATION'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модификация (ID)
             */
            array(
                'CODE' => 'modification_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODIFICATION_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Мощность, л.с.
             */
            array(
                'CODE' => 'horsepower',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_HORSEPOWER'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Объем двигателя, см3
             */
            array(
                'CODE' => 'displacement',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DISPLACEMENT'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Дополнительная информация
             */
            array(
                'CODE' => 'extra',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_EXTRA'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "HTML",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Цилиндров
             */
            array(
                'CODE' => 'cylinders',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CYLINDERS'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Клапанов
             */
            array(
                'CODE' => 'valve',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_VALVE'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Тип кузова
             */
            array(
                'CODE' => 'body_type',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Седан
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_SEDAN'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'SEDAN',
                    ),
                    //Хэтчбэк
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_HATCHBACK'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'HATCHBACK',
                    ),
                    //Универсал
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_UNIVERSAL'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'UNIVERSAL',
                    ),
                    //Джип
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_JEEP'),
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'JEEP',
                    ),
                    //Купе
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_COUPE'),
                        "DEF" => "N",
                        "SORT" => "5",
                        'XML_ID' => 'COUPE',
                    ),
                    //Кабриолет
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_CABRIOLET'),
                        "DEF" => "N",
                        "SORT" => "6",
                        'XML_ID' => 'CABRIOLET',
                    ),
                    //Минивэн
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_MINIVAN'),
                        "DEF" => "N",
                        "SORT" => "7",
                        'XML_ID' => 'MINIVAN',
                    ),
                    //Микроавтобус
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_MINIBUS'),
                        "DEF" => "N",
                        "SORT" => "8",
                        'XML_ID' => 'MINIBUS',
                    ),
                ),
            ),
            
            /*
             * Число дверей
             */
            array(
                'CODE' => 'doors',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DOORS'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    array(
                        "VALUE" => "2",
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'DOOR_1',
                    ),
                    array(
                        "VALUE" => "3",
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'DOOR_2',
                    ),
                    array(
                        "VALUE" => "4",
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'DOOR_3',
                    ),
                    array(
                        "VALUE" => "5",
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'DOOR_4',
                    ),
                    array(
                        "VALUE" => "6",
                        "DEF" => "N",
                        "SORT" => "5",
                        'XML_ID' => 'DOOR_5',
                    ),
                    array(
                        "VALUE" => "7",
                        "DEF" => "N",
                        "SORT" => "6",
                        'XML_ID' => 'DOOR_6',
                    ),
                ),
            ),
            
            /*
             * Тип/буквы двигателя
             */
            array(
                'CODE' => 'engine_type',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ENGINE_TYPE'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Привод
             */
            array(
                'CODE' => 'drive',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Передний
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE_FRONT'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'FRONT',
                    ),
                    //Задний
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE_BACK'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'BACK',
                    ),
                    //Полный
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE_FULL'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'FULL',
                    ),
                ),
            ),
            
            /*
             * Тип кпп
             */
            array(
                'CODE' => 'transmission',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Механическая
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_MANUAL'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'MANUAL',
                    ),
                    //Автоматическая
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_AUTOMATIC'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'AUTOMATIC',
                    ),
                    //Вариатор
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_VARIATOR'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'VARIATOR',
                    ),
                ),
            ),
            
            /*
             * Номер кпп
             */
            array(
                'CODE' => 'transmission_number',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_NUMBER'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Руль
             */
            array(
                'CODE' => 'steering_wheel',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_STEERING_WHEEL'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'C',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Слева
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_STEERING_WHEEL_LEFT'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'LEFT',
                    ),
                    //Справа
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_STEERING_WHEEL_RIGHT'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'RIGHT',
                    ),
                ),
            ),
            
            /*
             * Опции комплектации
             */
            array(
                'CODE' => 'configuration',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'C',
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //ABS
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_ABS'),
                        "DEF" => "N",
                        "SORT" => "1",
                        'XML_ID' => 'LEFT',
                    ),
                    //ESP
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_ESP'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'RIGHT',
                    ),
                    //УР
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_UR'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'RIGHT',
                    ),
                    //Кондиционер
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_AC'),
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'RIGHT',
                    ),
                    //Катализатор
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_ACCELERANT'),
                        "DEF" => "N",
                        "SORT" => "5",
                        'XML_ID' => 'RIGHT',
                    ),
                ),
            ),
            
            /*
             * Запрос
             */
            array(
                'CODE' => 'request',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_REQUEST'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "HTML",
                "IS_REQUIRED" => "Y",
            ),
            
            /*
             * Ответ
             */
            array(
                'CODE' => 'answer',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ANSWER'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "HTML",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Ответил 
             */
            array(
                'CODE' => 'answer_manager',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ANSWER_MANAGER'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "UserID",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Дата ответа
             */
            array(
                'CODE' => 'answer_date',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ANSWER_DATE'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "DateTime",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Менеджер
             */
            array(
                'CODE' => 'manager',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MANAGER'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "UserID",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Сайт
             */
            array(
                'CODE' => 'site_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_SITE_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),

            /*
             * Сайт
             */
            array(
                'CODE' => 'user_name',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_USER_NAME'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "Y",
            ),

            /*
             * Сайт
             */
            array(
                'CODE' => 'user_phone',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_USER_PHONE'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "Y",
            ),

            /*
             * Сайт
             */
            array(
                'CODE' => 'user_email',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_USER_EMAIL'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
        )
    ),

    
);


foreach ($iblocks as $SORT => $iblock) {
    /*
     * Если инфоблок уже есть - не создаём его
     */
    $res = CIBlock::GetList(array(), array('TYPE' => 'linemedia_auto', 'ACTIVE' => 'Y', 'CODE' => 'lm_auto_' . $iblock['CODE']), true);
    if ($found_iblock = $res->Fetch()) {
        COption::SetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_" . $iblock['CODE'], $found_iblock['ID']);
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
            COption::SetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_" . $CODE, $IBLOCK_ID);
        } else {
            throw new Exception('Error adding iblock ' . $iblock['CODE']);
        }
        
        
        /*
         * Сохраним ID свойств, чтобы прописать их в визуальные настройки
         */
        $PROP_IDS = array();
        
        /*
         * Добавление свойств инфоблока
         */
        foreach ($iblock['PROPERTIES'] as $i => $PROP) {
            $PROP['ACTIVE'] = 'Y';
            $PROP['IBLOCK_ID'] = $IBLOCK_ID;
            $PROP['SORT'] = $i;
            
            $ibp = new CIBlockProperty();
            if (!$PropID = $ibp->Add($PROP)) {
                throw new Exception('Error adding iblock property ' . print_r($PROP, 1));
            }
            $PROP_IDS['#PROP_' . strtoupper($PROP['CODE']) . '#'] = $PropID;
            
            
            if($PROP['ENUM']) {
	           foreach($PROP['ENUM'] AS $ENUM) {
	           		$ENUM['PROPERTY_ID'] = $PropID;
		            $ib = new CIBlockPropertyEnum;
					$ib->Add($ENUM);
	           } 
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
                throw new Exception('Error adding iblock element ' . $ELEMENT['NAME']);
            }
        }
        
        
        /*
		 * Настройка форм и списков инфоблоков
		 */
		$iblock_type = 'linemedia_auto';
		$columns = trim(strval($iblock['FORMS']['LIST']));
		$edit_tabs = array_filter((array) $iblock['FORMS']['EDIT']);
        
        /*
         * Список
         */
        if ($columns != '') {
	        $columns = str_replace(array_keys($PROP_IDS), array_values($PROP_IDS), $columns);
	        
	        $option_hash = "tbl_iblock_list_".md5($iblock_type.".".$IBLOCK_ID);
			$arOptions = array(
			     array(
			          'c' => 'list',
			          'n' => $option_hash,
			          'd' => 'Y',
			          'v' => array(
			               'columns' => $columns,
			               'by' => 'timestamp_x',
			               'order' => 'desc',
			               'page_size' => '20',
			          ),
			     )
			);
			CUserOptions::SetOptionsFromArray($arOptions);
        }
        
        /*
         * Форма редактирования
         */
        if (count($edit_tabs) > 0) {
	        /*
	         * Подставим ID свойств
	         */
	        foreach ($edit_tabs AS $i => $tab) {
		        foreach ($tab['FIELDS'] AS $y => $field) {
			        $edit_tabs[$i]['FIELDS'][$y]['NAME'] = str_replace(array_keys($PROP_IDS), array_values($PROP_IDS), $field['NAME']);
		        }
	        }
	        
	        /*
	         * Составим этот адский хеш
	         */
            $tabs_string = '';
            foreach($edit_tabs as $tab) {
    
               $tabs_string .= $tab['CODE'] . '--#--' . $tab['TITLE'] . '--,--';
               foreach($tab['FIELDS'] as $field) {
    
                    $tabs_string .= $field['NAME'] . '--#--' . $field['TITLE'];
                    if (end($tab['FIELDS']) == $field) {
                        $tabs_string .= '--;--';
                        continue;
                    }
            
                    $tabs_string .= '--,--';
               }
            }
			
			
			if (strcmp($CODE, strtoupper('modificator')) == 0) {
			    $tabs_string = implode(',', explode(';', $tabs_string, 2));		    
			} 
			$arOptions = array(
			    array(
			        'c' => 'form',
			        'n' => 'form_element_' . $IBLOCK_ID,
			        'd' => 'Y',
			        'v' => array(
			            'tabs' => $tabs_string,
			        ),
			    )
			);
			CUserOptions::SetOptionsFromArray($arOptions);
        }
    }

}


/*
 * Установка прав в инфоблоках.
 */

$obIblockRights = new CIBlockRights(COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_VIN'));

$rights = array(
    'n0' => array(
        'GROUP_CODE'    => 'G2',
        'DO_CLEAN'      => 'Y',
        'TASK_ID'       => $obIblockRights->LetterToTask('R'),
    )
);

$obIblockRights->SetRights($rights);
unset($obIblockRights, $rights);
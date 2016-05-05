<?php

$oSort = new CAdminSorting($sTableID, "title", "asc"); // объект сортировки

// Поставщики
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
$suppliers = array();
$arsuppliers = LinemediaAutoSupplier::GetList();
foreach ($arsuppliers as $supplier) {
    $suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}

$arHeaders = array(
    array(
        "id"        => "id",
        "content"   => "ID",
        "default"   => false,
		//"sort"  	=> "id",
    ),
    array(
        "id"        => "title",
        "content"   => GetMessage("LM_AUTO_MAIN_TITLE"),
        "default"   => true,
		//"sort"		=> "title",
    ),
    array(
        "id"        => "article",
        "content"   => GetMessage("LM_AUTO_MAIN_ARTICLE"),
        "default"   => true,
		//"sort"		=> "article",
    ),
    array(
        "id"        => "original_article",
        "content"   => GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE"),
        "default"   => false,
		//"sort"		=> "original_article",
    ),
    array(
        "id"        => "brand_title",
        "content"   => GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
        "default"   => true,
		//"sort"		=> "brand_title",
    ),
    array(
        "id"        => "prices",
        "content"   => GetMessage("LM_AUTO_MAIN_PRICE"),
        "default"   => true,
		//"sort"		=> "prices",
    ),
	array(
		"id"        => "price",
		"content"   => GetMessage("LM_AUTO_MAIN_INITIAL_PRICE"),
		"default"   => false,
				//"sort"		=> "prices",
	),
    array(
        "id"        => "supplier_id",
        "content"   => GetMessage("LM_AUTO_MAIN_SUPPLIER_ID"),
        "default"   => true,
		//"sort"		=> "supplier_id",
    ),
    array(
        "id"        => "quantity",
        "content"   => GetMessage("LM_AUTO_MAIN_QUANTITY"),
        "default"   => true,
		//"sort"		=> "quantity",
    ),
    array(
        "id"        => "weight",
        "content"   => GetMessage("LM_AUTO_MAIN_WEIGHT"),
        "default"   => false,
		//"sort"		=> "weight",
    ),
    array(
        "id"        => "modified",
        "content"   => GetMessage("LM_AUTO_MAIN_MODIFIED"),
        "default"   => false,
		//"sort"		=> "modified",
    ),
	array(
		"id"        => "delivery_time",
		"content"   => GetMessage("LM_AUTO_MAIN_DELIVERY_TIME"),
		"default"   => true,
		//"sort"		=> "delivery_time",
	),
);


$arAnalogGroups = LinemediaAutoPart::getAnalogGroups();

foreach ($arResult['PARTS'] as $type => $arCatalogs) {
    $type = str_replace('analog_type_', '', $type);

    $sTableID = 'lm-search-parts-'.$type; // ID таблицы

    $lAdmin = new CAdminList($sTableID, $oSort); // Основной объект списка.
    $lAdmin->AddHeaders($arHeaders);
    
    foreach ($arCatalogs as $arRes) {
        // Создаем строку. Результат - экземпляр класса CAdminListRow.
        $row =& $lAdmin->AddRow($arRes['id'], $arRes);
        
        $supplier = $suppliers[$arRes['supplier_id']];
        $row->AddViewField('supplier_id', "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>".$arRes['supplier_id']."</a>] " . $supplier['NAME']);

		$row->AddViewField('delivery_time', round($arRes['delivery_time']/24) . GetMessage("LM_AUTO_MAIN_DELIVERY_TIME_DAYS"));


		$arRes['hash'] = md5($arRes['supplier_id'].$arRes['brand_title'].$arRes['article'].$arRes['price']);

		$content = '<select name="PRICE['.$arRes['hash'].']" id="price-'.$arRes['hash'].'" style="" title="'. CurrencyFormat($arRes['price'], CCurrency::GetBaseCurrency()) .'">';
		foreach ($arRes['prices'] as $group_id => $price) {
			$content .= '<option value="'.round($price, 2).'" rel="'.$groups[$group_id]['NAME'].'">';
			$content .= $groups[$group_id]['NAME'] . ' ';
			$content .= CurrencyFormat($price, CCurrency::GetBaseCurrency());
			$content .= '</option>';
		}
		$content .= '</select>';

        $row->AddViewField('prices', $content);
        $row->AddViewField('price', CurrencyFormat($arRes['price'], CCurrency::GetBaseCurrency()));
        
        
        
        // Сформируем контекстное меню.
        $arActions = array();
        
        // Выбор элемента.
        $arActions []= array(
            "ICON"      => "select",
            "DEFAULT"   => true,
            "TEXT"      => GetMessage("NEWO_SELECT"),
            "ACTION"    => "SelEl('".$arRes['id']."', ".json_encode($arRes).");"
        );

        // Применим контекстное меню к строке.
        $row->AddActions($arActions);
    }
    
    /*
     * Отображение списка.
     */
    echo '<h2>', $arAnalogGroups[$type], '</h2>';

    $lAdmin->DisplayList();
    
    echo '<br/>';
}





<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autosuppliers/include.php"); // инициализация модуля
IncludeModuleLangFile(__FILE__);
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.autosuppliers");

if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autosuppliers")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}


$POST_RIGHT = 'W';



/*
* Подтверждение прихода
*/
if(isset($_GET['action']))
{
    if($_GET['action'] == 'confirm')
    {
        $ids = (array) $_POST['ID'];
        
        foreach($ids AS $id => $entry)
        {
            $quantity = (float) $entry['quantity'];
            $basket_ids = array_filter(explode(',', strval($entry['basket_ids'])));
            
            if($quantity <= 0) continue;
            
            $id          = explode('|', $id);
            $supplier_id = (int)    $id[0];
            $brand_title = (string) $id[1];
            $article     = (string) $id[2];

            // Количество списывается после каждой корзины
            $left = $quantity;
            
            
            LinemediaAutoDebug::add("supplier_id $supplier_id | brand_title $brand_title | article $article | quantity $quantity", false, LM_AUTO_DEBUG_WARNING);
            
            
            /*
            * Пробежимся по корзинам
            */
            $baskets_res = CSaleBasket::GetList(array(), array('ID' => $basket_ids));
            while ($basket = $baskets_res->Fetch())
            {
                /*
                * Получим список свойств
                */
                $props = array();
                $db_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID']), false, false, array('CODE', 'NAME', 'VALUE'));
                while ($ar_res = $db_res->Fetch())
                {
                    $props[$ar_res['CODE']] = $ar_res;
                }
                
                /*
                * Зачем нам корзина, если она уже оприходована?
                * Вероятно её поделили ранее и это старый... корзины кусок
                */
                if($props['supplier_request_status']['VALUE'] != 'request_sent')
                {
                    //throw new Exception('Basket #'.$basket['ID'] . ' already approved');
                    continue;
                }
                
                /*
                * товар пришёл не весь! надо разделить корзину!
                */
                if($left < $basket['QUANTITY'])
                {
                    LinemediaAutoDebug::add("Split baskets necessary");
                    
                    /*
                    * новая корзина
                    */
                    $NEW = $basket;
                    $props['supplier_request_status']['VALUE'] = 'request_sent';
                    $NEW['PROPS'] = array_values($props);
                    $NEW['QUANTITY'] -= $left;
                    
                    unset($NEW['DATE_INSERT'], $NEW['DATE_UPDATE'], $NEW['ID']);// unset bulk
                    $NEW['NOTES'] = 'Split with basket ' . $basket['ID']; // add comment
                    $NEW['PRICE'] = $NEW['PRICE'] / $basket['QUANTITY'] * $NEW['QUANTITY'];// recalc price
                    $nID = CSaleBasket::Add($NEW);
                    
                    LinemediaAutoDebug::add("New basket $nID", false, LM_AUTO_DEBUG_WARNING);
                    
                    if($nID < 1)
                    {
                         throw new Exception('New basket not created'); 
                         exit;
                    }
                    
                    
                    // новую корзину также надо добавить в заявку поставщику
                    LinemediaAutoSuppliersRequestBasket::Add(array(
                        'basket_id' => $nID,
                        'request_id' => LinemediaAutoSuppliersRequestBasket::GetRequestIdByBasketId($basket['ID']),
                    ));
                    
                    /*
                    * старая корзина
                    */
                    $props['supplier_request_status']['VALUE'] = 'request_approved';
                    $OLD_PRICE = $OLD['PRICE'] / $basket['quantity'] * $left;// recalc price
                    CSaleBasket::Update($basket['ID'], array('QUANTITY' => $left, 'PRICE' => $OLD_PRICE/*, 'PROPS' => array_values($props)*/));
                    //чтобы отрабатывали события, привязанные к смене статусов и свойств...
                    LinemediaAutoBasket::setProperty($basket['ID'], array_values($props));
                } else {
                    /*
                    * Товар пришёл весь
                    * Просто обновим значения в БД
                    */
                    $props['supplier_request_status']['VALUE'] = 'request_approved';
                    LinemediaAutoBasket::setProperty($basket['ID'], array_values($props));
//                     CSaleBasket::Update($basket['ID'], array('PROPS' => array_values($props)));
                    
                }
                
                
                /*
                * Спишем остаток и если деталей больше нет - перейдём к обработке следующей детали
                */ 
                $left -= $basket['QUANTITY'];
                if($left <= 0) break;
            }
            
            /*
            * Остались лишние товары!
            */
            if($left > 0)
            {
                throw new Exception('Supplier [' . $supplier_id . '], brand [' . $brand_title . '], article [' . $article . '] left ' . $left . ' details');
            }
        }
        
        die('OK');
        
    }
    exit;
}















/***********************************************************/
$sTableID = "b_lm_suppliers_requests_baskets"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "brand_title", "asc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	
	return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// опишем элементы фильтра
$FilterArr = Array(
	"find_supplier_id",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if (CheckFilter()) {
	// создадим массив фильтрации для выборки LinemediaAutoSuppliersRequestBasket::GetList() на основе значений фильтра
	$arFilter = array(
		"supplier_id"	  => $find_supplier_id,
		"status"    	  => 'request_sent',
	);
}



// выберем список
$cData = new LinemediaAutoSuppliersRequestBasket;
$rsData = $cData->GetList(array($by => $order), $arFilter);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_SUPPLIERS_BRANDS_NAV")));



$lAdmin->AddHeaders(array(
  array(  "id"    =>"quantity",
    "content"  => GetMessage("LM_AUTO_SUPPLIERS_QUANTITY"),
    "sort"     =>"quantity",
    "default"  =>true,
  ),
  array(  "id"    =>"brand_title",
    "content"  =>GetMessage("LM_AUTO_SUPPLIERS_BRAND_TITLE"),
    "sort"     =>"brand_title",
    "default"  =>true,
  ),
  array(  "id"    =>"article",
    "content"  =>GetMessage("LM_AUTO_SUPPLIERS_ARTICLE"),
    "sort"     =>"article",
    "default"  =>true,
  ),
));



/*
* Получим все корзины
*/
$basket_ids = array();
while ($arRes = $rsData->NavNext(true, "f_")) {
       $basket_ids[] = $arRes['basket_id'];
}

if(count($basket_ids) > 0)
{

    $baskets = array();
    $res = CSaleBasket::GetList(array(), array('ID' => $basket_ids));
    while($basket = $res->Fetch())
    {
        $baskets[$basket['ID']] = $basket;
    }

    $res = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket_ids, 'CODE' => array('brand_title', 'article', 'supplier_request_status')), false, false, array('CODE', 'NAME', 'VALUE', 'SORT', 'BASKET_ID'));
    while($prop = $res->Fetch())
    {
        $basket_id = $prop['BASKET_ID'];
        unset($prop['BASKET_ID']);
        $baskets[$basket_id]['PROPS'][$prop['CODE']] = $prop;
    }


    /*
    * Подготовим сгруппированный список
    */
    $result = array();
    foreach ($baskets AS $basket) {
        // уже полученные корзины исключим
        //if($basket['PROPS']['supplier_request_status']['VALUE'] != 'request_sent') continue;

        $brand_title = $basket['PROPS']['brand_title']['VALUE'];
        $article = $basket['PROPS']['article']['VALUE'];
        $quantity = $basket['QUANTITY'];
        
        $result[$brand_title][$article]['quantity'] += $quantity;
        $result[$brand_title][$article]['basket_ids'][] = $basket['ID'];
    }


    $find_supplier_id = (int) $find_supplier_id;
    if($find_supplier_id < 1) 
    {
        $result = array();
    }
} else {
    $result = array();
}

foreach($result AS $brand_title => $articles)
{
    foreach($articles AS $article => $article_data)
    {
        $quantity = $article_data['quantity'];
        $basket_ids = (array) $article_data['basket_ids'];
        
        $arRes = array(
            'brand_title' => $brand_title,
            'article'     => $article,
            'quantity'    => $quantity,
        );
        
        // создаем строку. результат - экземпляр класса CAdminListRow
        $ID = $find_supplier_id . '|' . $brand_title . '|' . $article;
        $row =& $lAdmin->AddRow($ID, $arRes);
        $row->AddViewField("quantity", '<input data-basket-ids="' . join(',',$basket_ids) . '" class="quantity" type="text" size="4" name="quantity[]" value="0" id="' . $ID . '" /> <b>/ ' . $quantity . '</b>');
        //$row->AddEditField("LID", CLang::SelectBox("LID", $f_LID)); 

        // сформируем контекстное меню
        $arActions = Array();
        // удаление элемента
        if (false AND $POST_RIGHT>="W")
        {
            $arActions[] = array(
                "ICON"   => "checked",
                "TEXT"   => GetMessage("LM_AUTO_SUPPLIERS_CONFIRM"),
                "ACTION" => $lAdmin->ActionDoGroup($f_id, "confirm")
            );
            $arActions[] = array(
                "ICON"   => "delete",
                "TEXT"   => GetMessage("LM_AUTO_SUPPLIERS_CANCEL"),
                "ACTION" => "if(confirm('".GetMessage("CONFIRM_CANCEL")."')) document.getElementById('lm-auto-suppliers-in-frm').submit()",
            );
        }
        $row->AddActions($arActions);  
    }
    
}








// резюме таблицы
/*$lAdmin->AddFooter(
  array(
    array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
    array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
  )
);*/

// групповые действия
/*$lAdmin->AddGroupActionTable(Array(
    "confirm"=>GetMessage("LM_AUTO_SUPPLIERS_CONFIRM"),
));*/





  
// сформируем меню из одного пункта - добавление рассылки
$aContext = array(
  array(
    "TEXT"=>GetMessage("LM_AUTO_SUPPLIERS_ADD_REQUEST"),
    "LINK"=>"/bitrix/admin/linemedia.autosuppliers_out.php?lang=" . LANG,
    "supplier_id"=>GetMessage("LM_AUTO_SUPPLIERS_ADD_REQUEST"),
    "ICON"=>"btn_new",
  ),
);

// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext, false, true);




CUtil::InitJSCore(array('window'));


// альтернативный вывод
$lAdmin->CheckListMode();


$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.2/jquery.min.js');



// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIERS_IN_TITLE'));
// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


// создадим объект фильтра
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
    GetMessage("LM_AUTO_SUPPLIER"),
  )
);


$suppliers = LinemediaAutoSupplier::GetList();

?>
<form name="find_form" id="lm_find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<input type="hidden" name="set_filter" value="Y" />
<input type="hidden" name="lang" value="<?=LANG?>" />
<? $oFilter->Begin(); ?>
<tr>
  <td><?= GetMessage('LM_AUTO_SUPPLIER') ?>:</td>
  <td>
    <select name="find_supplier_id" id="lm_find_supplier_id_filter">
        <option value=""><?=GetMessage('LM_AUTO_SUPPLIER_NOT_SELECTED')?></option>
        <?foreach($suppliers AS $supplier){?>
            <?$id = $supplier['PROPS']['supplier_id']['VALUE']?>
            <option value="<?=$id?>" <?=($find_supplier_id==$id) ? 'selected':''?>><?=$supplier['NAME']?></option>
        <?}?>
    </select>
  </td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>


<?if($find_supplier_id < 1){
CAdminMessage::ShowNote(GetMessage('LM_AUTO_SUPPLIER_NOT_SELECTED_WARNING'));
}?>

<div id="lm-auto-suppliers-list">
    <?$lAdmin->DisplayList();?>
</div>

<input type="button" id="lm-auto-suppliers-submit" value='<?=GetMessage('LM_AUTO_SUPPLIERS_CONFIRM')?>' />

<div id="lm-auto-suppliers-response"></div>



<script>
$(document).ready(function(){
    $('#lm_find_supplier_id_filter').change(function(){
        $('#lm_find_form').submit();
    });
    
    $('#lm-auto-suppliers-submit').click(function(){
        
        var data = '';
        $('input.quantity').each(function(index, obj){
            var id = $(obj).attr('id');
            var quantity = parseFloat($(obj).val());
            var basket_ids = $(obj).data('basket-ids');
            
            data += '&ID[' + id + '][quantity]=' + quantity;
            data += '&ID[' + id + '][basket_ids]=' + basket_ids;
        })
        
        $.ajax({
            type: 'POST',
            url: "/bitrix/admin/linemedia.autosuppliers_in.php?lang=<?=LANG?>&action=confirm",
            data: data
        }).done(function(html) {
            if(html == 'OK') document.location = document.location;
            $('#lm-auto-suppliers-response').html(html);
        }).error(function(html) {
            $('#lm-auto-suppliers-response').html(html);
        });
    })
})
</script>




<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

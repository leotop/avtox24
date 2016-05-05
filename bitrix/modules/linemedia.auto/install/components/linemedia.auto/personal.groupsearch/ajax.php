<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}

if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
    if (!CModule::IncludeModule("linemedia.autobranches")) {
        ShowError(GetMessage("LM_AUTO_MODULE_BRANCHES_NOT_INSTALL"));
        return;
    }
}

CModule::IncludeModule("sale");

global $USER;

if(!$USER->IsAuthorized()) {
    $allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
}else{
    $allCurrency = $USER->GetParam('CURRENCY');
}

if(empty($allCurrency)) {
    $allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
}

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

global $USER;

function multiExplode($delimiters, $string) {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}

$uploadStrategy = $_REQUEST['load'];
$spares = array();
$path = null;

$arParams = array();

$real_path = $_SERVER['PHP_SELF'];

if ($uploadStrategy == 'price') {

    $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'upload/linemedia.auto/pricelists/price_groupsearch/';
    if (!file_exists($path)) {
		mkdir($path, 0777, true);
	}
    $csvIter = null;
    $directoryIt = new DirectoryIterator($path);
    
    while ($directoryIt->valid()) {
        if (current(explode('.', $directoryIt->getFilename())) == session_id()) {
    
            $ext = pathinfo($directoryIt->getRealPath(), PATHINFO_EXTENSION);
            $csv_ext = '';
            if ($ext == 'xlsx' || $ext == 'xls') {
                $csv_ext =  '.csv';
                 shell_exec(
                    'ssconvert ' .
                    $directoryIt->getRealPath() .
                    ' ' .
                    $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/price_groupsearch/' .
                    $directoryIt->getFilename() . $csv_ext
                 );
            }

            $csvIter = new SplFileObject($directoryIt->getRealPath() . $csv_ext);
            $csvIter->setFlags(SplFileObject::READ_CSV);
            break;
        }
    
        $directoryIt->next();
    }
    
    if ($csvIter != null) {

        while ($csvIter->valid()) {

            list($tmp['article'], $tmp['brand'], $tmp['count']) = $csvIter->fgetcsv(',');          
            if ($tmp['article'] == null && $tmp['brand'] == null && $tmp['count'] == null) {
            	continue;
            }
                        
            $tmp['count'] = str_replace(';', '', $tmp['count']);

            $tmp['article'] = LinemediaAutoPartsHelper::clearArticle($tmp['article']);//trim(ToLower($tmp['article']));
            $tmp['brand'] = trim(ToLower($tmp['brand']));
            $tmp['count'] = intval($tmp['count']) > 0 ? intval($tmp['count']) : 1;

            $spares[$tmp['article'] . '|' . $tmp['brand']] = $tmp;
        }
        
    }
    
    
    $csvIter = null;
    unlink($directoryIt->getRealPath() . $csv_ext);
    
} elseif ($uploadStrategy == 'article') {
    
    //$data = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $_REQUEST['data']); 
    $items = multiExplode(array(';', "\n", "\t", "\r"), $_REQUEST['data']);
    
   // _d(array($items, $_REQUEST['data']));
    
    foreach ($items as $spare) {
        
        if ($spare == null) {
            continue;
        }
        list($tmp['article'], $tmp['brand'], $tmp['count']) = explode(',', $spare);

        $tmp['article'] = LinemediaAutoPartsHelper::clearArticle($tmp['article']);//trim(ToLower($tmp['article']));
        $tmp['brand'] = trim(ToLower($tmp['brand']));
        $tmp['count'] = intval($tmp['count']) > 0 ? intval($tmp['count']) : 1;

        $spares[$tmp['article'] . '|' . $tmp['brand']] = $tmp;
    }
}

$queryArticle = '';

$result_parts = array();
$suppliers = array();

$suppliers_filter = (array) $_REQUEST['suppliers'];

if (count($spares) >= 1) {

    $queryArticle = join(',', array_keys($spares));

    $search = new \LinemediaAutoSearchGroup();
    // для внутренних поставщиков такой фильтр не работает
    if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') != 'Y') {
        $search->setSuppliers($suppliers_filter);
    }
    $retrievedSpares = $search->searchLocalDatabaseForPart(array('article' => $queryArticle), true);


    foreach($spares as $key => $spare) {

        $result_parts[$key] = array(
            'parts' => array(),
            'query' => $spare
        );

        foreach ($retrievedSpares as $rsp) {

            if( ToLower($rsp['article']) == $spare['article'] &&
                (empty($spare['brand']) || ToLower($rsp['brand_title']) == $spare['brand']) ) {

                $part_obj = new LinemediaAutoPart($rsp['id'], $rsp);

                /*
                 * Поставщик
                */
                if(!isset($suppliers[$rsp['supplier_id']])) {
                    $supplier = $suppliers[$rsp['supplier_id']] = new LinemediaAutoSupplier($rsp['supplier_id']);
                } else {
                    $supplier = $suppliers[$rsp['supplier_id']];
                }

                if(count($suppliers_filter) > 0 && !in_array($rsp['supplier_id'], $suppliers_filter)) {
                    continue;
                }

                /*
                 * Посчитаем цену товара
                */

                $price = new LinemediaAutoPrice($part_obj);
                if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
                    $price_calc = $price->calculate();
                } else {
                    $price_calc = $rsp['price'];
                }
                $rsp['price_src'] = $price_calc;

                $currency = $price->getCurrency();
                $user_currency = $USER->GetParam('CURRENCY');
                if(strlen($user_currency) == 3 && $currency != $user_currency) {
                    $currency = $user_currency;
                    $price_calc = LinemediaAutoPrice::userPrice($price_calc);
                }

                $rsp['price'] = CurrencyFormat($price_calc, $currency);

                $rsp['quantity'] = intval($rsp['quantity']);
                if($rsp['quantity'] < 1) continue;

                $rsp['customer_quantity'] = $spare['count'];
                if($spare['count'] > $rsp['quantity']) {
                    $rsp['customer_quantity'] = $rsp['quantity'];
                }

                $title = $supplier->get('visual_title');
                if(empty($title)) $title = $supplier->get('NAME');

                $rsp['supplier_title'] = $title;

                /*
                 * Срок доставки
                */
                if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
                    if (!$rsp['delivery_time']) {
                        $rsp['delivery_time'] = (int) $supplier->get('delivery_time');
                    } else {
                        $rsp['delivery_time'] += (int) $supplier->get('delivery_time');
                    }
                }
                $rsp['delivery'] = $rsp['delivery_time'];

                /*
                 * Пересчитаем в дни
                */
                $delivery_time = $rsp['delivery_time'];
                if ($delivery_time >= 24) {
                    $days = round($delivery_time / 24);
                    $delivery_time = '&asymp; ' . $days . ' ' . GetMessage('LM_AUTO_MAIN_DAYS');
                } else {
                    $delivery_time .= ' ' . GetMessage('LM_AUTO_MAIN_HOURS');
                }
                $rsp['delivery_time'] = $delivery_time;

                // нужно делать когда все поля уже сформированы
                $part_obj = new LinemediaAutoPart($rsp['id'], $rsp);
                $rsp['hash'] = $part_obj->groupSearchIdentity();

                $result_parts[$key]['parts'][] = $rsp;
            }
        }
    }
}
?>

<div class="cat_to_section">
<? if (!empty($result_parts)) { ?>

	<div class="lm-catalogs-list-wrapper">
		<div class="hr-custom-light offset-b-20"></div>
        <h3><?= GetMessage('LM_GROUP_SEARCH_SOUGHT_SPARES') ?></h3>
		<div class="filter-block">
        	<div class="filter-block-left form-horizontal offset-b-10 offset-r-20">

                <div class="input-select-block offset-r-5">
                    <span class="text-uppercase filter-block-left-title text-12"><?= GetMessage('LM_GROUP_SEARCH_DELIVERY') ?></span>
                    <div class="delivery">
                    </div>
                </div>
				
                <div class="input-select-block offset-r-5">   
                    <span class="text-uppercase filter-block-left-title text-12"><?= GetMessage('LM_GROUP_SEARCH_PRICE') ?></span>
                    <div class="price">
                    </div>
                </div>
                
                <div class="input-select-block offset-r-5">
                    <span class="text-uppercase filter-block-left-title text-12"><?= GetMessage('LM_GROUP_SEARCH_SUPPLIER') ?></span>
                    <div class="supplier">
                    </div>
                </div>
				
                <div class="input-select-block offset-r-5">
                    <span class="text-uppercase filter-block-left-title text-12"><?= GetMessage('LM_GROUP_SEARCH_BRAND') ?></span>
                    <div class="brands">	
                    </div>
                </div>
				
                <div style="float: right; margin-top: 15px;">
                    <input class="btn" type="button" id="sort" value="<?= GetMessage('LM_GROUP_SEARCH_SORT_BUTTON') ?>">
                </div>
                
				<!--<select class="width-200 filter-count-to"></select>-->
			</div>
		</div>
        <div style="clear:both;"></div>
		<form method="post" action="/auto/search/" class="lm_cat_to_form">
			<input type="hidden" name="act" value="ADD2BASKET" />
    		<input type="hidden" name="MULTIPLY_BASKET" value="Y" />
            <table class="table offset-b-20 found">
                <thead>
                <tr>
                    <th class="check-bl">&nbsp;</th>
                    <th class="article-bl"><?= GetMessage('LM_GROUP_SEARCH_ARTICLE') ?></th>
                    <th class="brands-bl"><?= GetMessage('LM_GROUP_SEARCH_BRAND') ?></th>
                    <th class="title-bl"><?= GetMessage('LM_GROUP_SEARCH_TITLE') ?></th>
                    <th class="supplier-bl"><?= GetMessage('LM_GROUP_SEARCH_SUPPLIER') ?></th>
                    <th class="price-bl"><?= GetMessage('LM_GROUP_SEARCH_PRICE') ?></th>
                    <th class="h_quantity-bl"><?= GetMessage('LM_GROUP_SEARCH_AMOUNT') ?></th>
                    <th class="delivery-bl"><?= GetMessage('LM_GROUP_SEARCH_DELIVERY') ?></th>
                </tr>
        	    </thead>
                <tbody>
			    <? foreach ($result_parts as $spares_key => $spares) {
                    $table_id = str_replace('|', '_', $spares_key);
                    $table_id = str_replace(' ', '_', $table_id);

                    ?>
                    <tr>
                        <td colspan="8">
                            <p class="part-header" id="<?=$table_id?>-part-headerr">
                                <?=$spares['query']['article']?>
                                <? if(!empty($spares['query']['brand'])) { ?>, <?= GetMessage('LM_GROUP_SEARCH_BRAND') ?>: <?=$spares['query']['brand']?><? } ?>
                                , <?= GetMessage('LM_GROUP_SEARCH_AMOUNT') ?>: <?=$spares['query']['count']?> <?=GetMessage('PCS')?>
                                <span class="q-difference" id="<?=$table_id?>-real-count" style="display: none;">(<?=GetMessage('SELECTED_QUANTITY')?> <span id="<?=$table_id?>-real-count-val"><?=$spares['query']['count']?></span> <?=GetMessage('PCS')?>)</span>
                               <? if(count($spares['parts']) < 1) { ?><span style="color:red"><?=GetMessage('LM_GROUP_SEARCH_UNAVAILABLE')?></span><? } ?>
                                <a class="analogs_toggle" id="<?=$table_id?>-analogs" data-table="<?=$table_id?>" href="#" onclick="return toggle_analogs('<?=$table_id?>');"><?= GetMessage('LM_GROUP_SEARCH_ANALOGS') ?>&nbsp;</a>
                                <input type="hidden" id="<?=$table_id?>-request-count" name="request-count" value="<?=$spares['query']['count']?>" />
                                <input type="hidden" id="<?=$table_id?>-request-article" name="request-article" value="<?=$spares['query']['article']?>" />
                                <input type="hidden" id="<?=$table_id?>-request-brand" name="request-brand" value="<?=$spares['query']['brand']?>" />
                            </p>
			                <table class="table catalog-to-result-table found" id="<?=$table_id?>">
                                <?
                                if(count($spares['parts']) > 0) {
                                    $cnt = 0;
                                    foreach ($spares['parts'] as $key => $detail) { ?>
                                    <tr id="<?=$detail['hash']?>" class="section-part">
                                        <td class="check-bl">
                                            <input type="checkbox" class="id_inp" name="part_id[<?=$detail['hash'] ?>]" value="<?=$detail['id']?>" />
                                            <input type="hidden" class="q_inp" name="q[<?=$detail['hash']?>]" value="<?=$detail['article']?>" />
                                            <input type="hidden" class="supplier_id_inp" name="supplier_id[<?=$detail['hash']?>]" value="<?=$detail['supplier_id']?>" />
                                            <input type="hidden" class="brand_title_inp" name="brand_title[<?=$detail['hash']?>]" value="<?=$detail['brand_title']?>" />
                                            <input type="hidden" class="delivery_inp" name="delivery[<?=$detail['hash']?>]" value="<?=$detail['delivery']?>" />
                                            <input type="hidden" class="ch_id_inp" name="ch_id[<?=$detail['hash'] ?>]" value="<?=$detail['chain_id']?>" />
                                        </td>
                                        <td class="article-bl dblckick-sence" nowrap="nowrap">
                                            <span><?= $detail['article'] ?></span>
                                        </td>
                                        <td class="brands-bl dblckick-sence">
                                            <span data-val="<?=$detail['brand_title']?>"><?= $detail['brand_title'] ?></span>
                                        </td>
                                        <td class="title-bl dblckick-sence">
                                            <span><?= $detail['title'] ?></span>
                                        </td>
                                        <td class="supplier-bl dblckick-sence">
                                            <span data-val="<?=$detail['supplier_id']?>"><?= $detail['supplier_title'] ?></span>
                                        </td>
                                        <td class="price-bl dblckick-sence">
                                            <span data-val="<?=$detail['price_src']?>"><?= $detail['price'] ?></span>
                                        </td>
                                        <td class="quantity-bl">
                                            <input type="hidden" name="max_quantity" value="<?=$detail['quantity']?>" />
                                            <span style="z-index: 100;"><input type="number" class="quantity_inp" min="0" max="<?=$detail['quantity']?>" step="1" size="4" name="quantity[<?=$detail['hash'] ?>]" value="<?=$detail['customer_quantity']?>" title="max. <?=$detail['quantity']?>" /></span>
                                        </td>
                                        <td class="delivery-bl dblckick-sence">
                                            <span data-val="<?=$detail['delivery']?>"><?= $detail['delivery_time'] ?></span>
                                        </td>
                                    </tr>
                                    <?
                                    } // foreach
                                } else {
                                ?>
                                <table class="table catalog-to-result-table found" id="<?=$table_id?>">
                                </table>
                                <? } ?>
                            </table>
                            <div id="<?=$table_id?>-loader" style="text-align: center; display:none;"><img src="<?=dirname($real_path);?>/images/loader.gif" /></div>
                        </td>
                    </tr>
                <? } // foreach ?>
                </tbody>
            </table>
            <div id="price-button-block" class="text-right offset-t-20" style="display:none;">
                <p class="offset-b-10"><span class="bold size14"><?= GetMessage('LM_CATALOGS_TOTAL_TEXT') ?> </span>
                    <span id="price_val" class="text18 red"></span> <?= $allCurrency ?></p>
                <input type="button" class="buy-a-kit btn btn-success btn-lg text-uppercase" value="<?= GetMessage('LM_CATALOGS_BUY_BUTTON_TEXT') ?>" />
            </div>
         </form>
    </div>
<? } else { ?>
	<p style="color: red"><?= GetMessage('FOUND_NO_ITEMS'); ?></p>
<? } // if (!empty($tableSpares)) ?>


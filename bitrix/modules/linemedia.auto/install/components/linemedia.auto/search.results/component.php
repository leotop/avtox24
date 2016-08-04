<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}



// иначе не работает сабмит формы на кастомных шаблонах, например bitrix24
CJSCore::Init(array("jquery"));



/*
 * Обработка входных параметров.
 */
$arParams['QUERY'] = trim(strval($arParams['QUERY']));

$arParams['NO_SHOW_WORDFORMS'] = trim(strval($arParams['NO_SHOW_WORDFORMS'])) ?: 'N';

$arParams['BRAND_TITLE'] = trim(strval($arParams['BRAND_TITLE']));

$arParams['EXTRA'] = (array) $arParams['EXTRA'];

$arParams['PART_ID'] = intval($arParams['PART_ID']);

$arParams['HIDE_FIELDS'] = (array) $arParams['HIDE_FIELDS'];

$arParams['SHOW_CUSTOM_FIELDS'] = (array) $arParams['SHOW_CUSTOM_FIELDS'];

$arParams['USE_GROUP_SEARCH'] = ($arParams['USE_GROUP_SEARCH'] != 'N');

$arParams['SHOW_SUPPLIER'] = (array) $arParams['SHOW_SUPPLIER'];

$arParams['REMAPPING'] = ($arParams['REMAPPING'] == 'Y');

$arParams['SET_TITLE']  = ($arParams['SET_TITLE'] == 'Y');

$arParams['TITLE'] = trim(strval($arParams['TITLE']));

$arParams['AUTH_URL'] = (isset($arParams['AUTH_URL'])) ? trim(strval($arParams['AUTH_URL'])) : "/auth/";

$arParams['BASKET_URL'] = (isset($arParams['BASKET_URL'])) ? trim(strval($arParams['BASKET_URL'])) : "/auto/cart/";

$arParams['VIN_URL'] = (isset($arParams['VIN_URL'])) ? trim(strval($arParams['VIN_URL'])) : "/auto/vin/";

$arParams['INFO_URL'] = (isset($arParams['INFO_URL'])) ? trim(strval($arParams['INFO_URL'])) : "/auto/part-detail/#BRAND#/#ARTICLE#/";

$arParams['GROUP'] = (count(explode(',', (string) $arParams['QUERY'])) > 1);

$arParams['PARTIAL'] = ($_REQUEST['partial'] == 'Y');

$arParams['SHOW_BLOCKS'] = (!empty($arParams['SHOW_BLOCKS'])) ? (strval($arParams['SHOW_BLOCKS'])) : ('both');

$arParams['MERGE_GROUPS'] = ($arParams['MERGE_GROUPS'] == 'Y');

$arParams['ANTI_BOTS']  = ($arParams['ANTI_BOTS'] == 'Y' && !$USER->IsAuthorized());

$arParams['ACTION_VAR'] = (!empty($arParams['ACTION_VAR'])) ? (strval($arParams['ACTION_VAR'])) : ('act');

$arParams['BUY_ARTICLE_URL'] = isset($arParams['BUY_ARTICLE_URL']) ? $arParams['BUY_ARTICLE_URL'] : '/auto/search/?part_id=#PART_ID#';

/*
 * Дополнительные параметры добавления в корзину
 */
$arParams['BUY_ARTICLE_ADDITIONAL'] = (array) $arParams['BUY_ARTICLE_ADDITIONAL'];

//$arParams['SORT'] = (!empty($arParams['SORT'])) ? (strval($arParams['SORT'])) : ('price_src');

//$arParams['ORDER'] = (!empty($arParams['ORDER'])) ? (strval($arParams['ORDER'])) : ('asc');


$arParams['LIMIT'] = (int) $arParams['LIMIT'];

$arParams['PATH_NOTEPAD'] = (isset($arParams['PATH_NOTEPAD'])) ? trim(strval($arParams['PATH_NOTEPAD'])) : "/auto/notepad/";

$arParams['SHOW_ANALOGS'] = $arParams['SHOW_ANALOGS'] ? : "Y";

$arParams['SHOW_ANALOGS_STATISTICS'] = $arParams['SHOW_ANALOGS_STATISTICS'] ?: 'N';

$arParams['SEARCH_MODIFICATION_SET'] = isset($arParams['SEARCH_MODIFICATION_SET']) ? $arParams['SEARCH_MODIFICATION_SET'] : null;

$arParams['SEARCH_ARTICLE_URL'] = isset($arParams['SEARCH_ARTICLE_URL']) ? $arParams['SEARCH_ARTICLE_URL'] : '/auto/search/#ARTICLE#/';

$arParams['ORIGINAL_CATALOGS_FOLDER'] = isset($arParams['ORIGINAL_CATALOGS_FOLDER']) ? $arParams['ORIGINAL_CATALOGS_FOLDER'] : '/auto/original/';

$arParams['SEARCH_LIMIT'] = isset($_REQUEST['search_limit']) ? (string) $_REQUEST['search_limit'] : '';

$arParams['SEO_BLOCK'] = isset($arParams['SEO_BLOCK']) ? $arParams['SEO_BLOCK'] : 'N';


/*
 * Если будет применен модификатор, то нам не нужна сортировка и ограничение в самом компоненте
 * 14606
 */
if($arParams['SEARCH_MODIFICATION_SET'] != null && $arParams['SEARCH_MODIFICATION_SET'] != 'empty') {
    $arParams['SORT'] = '';
    $arParams['ORDER'] = '';
    $arParams['LIMIT'] = 0;
}

/*
 * Не ajax-ли запрос пришёл?
 */
$AJAX = isset($_REQUEST['ajax']);
if ($AJAX) {
    $GLOBALS['APPLICATION']->RestartBuffer();
    header('Content-type: application/json');
}

/*
 * Необходимо для popup блокнота
 */
CUtil::InitJSCore(array('window'));

/*
 * Что пойдёт в шаблон.
 */
$arResult = array();

$arResult['SHOW_SUPPLIER'] = (count(array_intersect(CUser::GetUserGroup(CUser::getID()), $arParams['SHOW_SUPPLIER'])) > 0);


/*
 * Форма авторизации для незарегистрированных пользователей
 */
if (!$USER->IsAuthorized() && $arParams['ANTI_BOTS'] && isset($_REQUEST['SHOW_AUTH_FORM'])) {
    $APPLICATION->AuthForm(GetMessage('LM_AUTO_SEARCH_NEED_AUTH'));
}

/*
 * Покупка товара.
 */
if (isset($_REQUEST[$arParams['ACTION_VAR']]) && $_REQUEST[$arParams['ACTION_VAR']] == 'ADD2BASKET') {

    //_d($_REQUEST);

    /*
     * Проверка сессии, установлена в LinemediaAutoUrlHelper::getPartBuyUrl
     */
    if (check_bitrix_sessid('sessid')) {

        /*
         * Унифицированный массив добавления в корзину
         * #7534, Ivan
         */
        $arAddToBasket = array();
		  
        if (isset($_REQUEST['MULTIPLY_BASKET']) && $_REQUEST['MULTIPLY_BASKET'] == 'Y' && is_array($_REQUEST['part_id'])) {

            foreach((array)$_REQUEST['part_id'] as $key => $id) {
                // если $id = 0, то товар пришел от удаленного поставщика.
                // в этом случае в $extra будет весь массив $_REQUEST['extra'] по ключу $key
                // сделано для задачи №21625
                if($id == 0) {
                    $extra = array(
                        'hash' => $_REQUEST['extra']['hash'][$key],
                        'bg_bid' => $_REQUEST['extra']['bg_bid'][$key],
                        'bg_wid' => $_REQUEST['extra']['bg_wid'][$key],
                        'bg_wht' => $_REQUEST['extra']['bg_wht'][$key],
                        'article_original' => $_REQUEST['extra']['article_original'][$key],
                    );
                } else {
                    $extra = (array) $_REQUEST['extra'][$key];
                }

                $extra['need_vin'] = ($_REQUEST['need_vin'][$key] == 'Y');

                $arAddToBasket[$key] = array(
                    'supplier_id' => (string) $_REQUEST['supplier_id'][$key], // ID поставщика. По нему можно также узнать, что запчасть лежит не в локальной БД, а в удалённом API.
                    'quantity' => (int) $_REQUEST['quantity'][$key], // Количество к заказу
                    'ch_id' => (string) $_REQUEST['ch_id'][$key],
                    'additional' => array(
                        'article'       => (string) $_REQUEST['q'][$key],
                        'brand_title'   => (string) $_REQUEST['brand_title'][$key],
                        'extra'         => $extra,
                        'max_available_quantity' => (int) $_REQUEST['max_available_quantity'][$key]
                    ),
                );
                if(isset($_REQUEST['step'][$key]) && intval($_REQUEST['step'][$key]) > 0) {
                    $arAddToBasket[$key]['additional']['step'] = intval($_REQUEST['step'][$key]);
                }
                if(is_numeric($id)) $arAddToBasket[$key]['part_id'] = (int) $_REQUEST['part_id'][$key]; // ID запчасти в локальной БД.
            }

        } else {

            $extra = (array) $_REQUEST['extra'];
            $extra['need_vin'] = ($_REQUEST['need_vin'] == 'Y');

            $arAddToBasket[0] = array(
                'part_id' => (int) $_REQUEST['part_id'], // ID запчасти в локальной БД.
                'supplier_id' => (string) $_REQUEST['supplier_id'], // ID поставщика. По нему можно также узнать, что запчасть лежит не в локальной БД, а в удалённом API.
                'quantity' => (int) $_REQUEST['quantity'], // Количество к заказу
                'ch_id' => (string) $_REQUEST['ch_id'],
                'additional' => array(
                    'article'       => (string) $_REQUEST['q'],
                    'brand_title'   => (string) $_REQUEST['extra']['brand_title_original'] ? : $_REQUEST['brand_title'],
                    'extra'         => $extra,
                    'max_available_quantity'          => (int) $_REQUEST['max_available_quantity']
                ),
            );
            if(isset($_REQUEST['step']) && intval($_REQUEST['step']) > 0) {
                $arAddToBasket[0]['additional']['step'] = intval($_REQUEST['step']);
            }
        }


        /*
         * Добавление в корзину
         */
        $basket = new LinemediaAutoBasket();

        foreach ($arAddToBasket as $arAdditem) {

            /*
             * ID запчасти в локальной БД.
             */
            $part_id = $arAdditem['part_id'];
            $spare = new LinemediaAutoPart($part_id);

            /*
             * ID поставщика.
             * По нему можно также узнать, что запчасть лежит не в локальной БД, а в удалённом API.
             */
            $supplier_id = ($arAdditem['supplier_id'] != '') ? $arAdditem['supplier_id'] : $spare->get('supplier_id');

            /*
             * Количество к заказу.
             */
            $quantity = ($arAdditem['quantity'] > 0) ? $arAdditem['quantity'] : 1;

            /*
             * Дополнительные параметры.
             */
            $additional = $arAdditem['additional'];

            /*
            * Максимально возможное к-во на основании к-ва в базе
            */
            $additional['max_available_quantity'] = abs($spare->get('quantity'));



            if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') == 'Y') {
                // торговая цепочка и полная информация о детали из сессии
                $chain_id = (string) $arAdditem['ch_id'];
                if(!isset($_SESSION['search_chains'][$chain_id])) {
                    ShowError('No chain ID');
                    exit();
                } else {
                    $chain = (array) $_SESSION['search_chains'][$chain_id];
                    $additional = $chain['part'] + $additional; // порядок важен
                    $supplier_id = $chain['part']['supplier_id'];
                }
            }


            /*
             * Создаём новую запись в корзине.
             */
            $basket_id = $basket->addItem($part_id, $supplier_id, $quantity, null, $additional);
        }

        /*
         * Завершим выполнение скрипта.
         */
        if (!$AJAX) {
            LocalRedirect($arParams['BASKET_URL']);
            exit();
        } else {
            die(
            safe_json_encode(
                array(
                    'status' => 'ok',
                    'basket_id' => $basket_id,
                )
            )
            );
        }
    } // if (check_bitrix_sessid('sessid'))

} // if (isset($_REQUEST[$arParams['ACTION_VAR']]) && $_REQUEST[$arParams['ACTION_VAR']] == 'ADD2BASKET')


/*
 * Сохраним в сессию для статистики этот запрос.
 */
if ($arParams['QUERY']) {
	
	if (!defined('LM_AUTO_NO_KEEP_LAST_QUERY') || LM_AUTO_NO_KEEP_LAST_QUERY == false) {
	    $key = $arParams['QUERY'] . $arParams['BRAND_TITLE'];
	    $_SESSION['LM_AUTO_MAIN']['QUERIES'][$key] = array(
	        'added' => time(),
	        'title' => $arParams['QUERY'],
	        'url' => LinemediaAutoUrlHelper::getPartUrl(array(
	            'article' => $arParams['QUERY'],
	            'part_id' => $arParams['PART_ID'],
	            'brand_title' => $arParams['BRAND_TITLE'],
	            'extra' =>  $arParams['EXTRA'],
	        )),
	    );
	
	    if ($arParams['BRAND_TITLE'] != '') {
	         $_SESSION['LM_AUTO_MAIN']['QUERIES'][$key]['title'] .= ' (' . $arParams['BRAND_TITLE'] . ')';
	    }
	}
}


/*
 * Если нет запроса.
 */
if ($arParams['QUERY'] == '' && $arParams['PART_ID'] < 1) {
    $url = str_replace('#ARTICLE#', '', $arParams['SEARCH_ARTICLE_URL']);
    $url = str_replace('//', '/', $url);
    $arResult['FORM_ACTION'] = $url;
    $this->IncludeComponentTemplate('default');
    return;
}


/*
 * Создаём объект поиска.
 */
try {
    $search = new LinemediaAutoSearch();
} catch (Exception $e) {
    $arResult['ERRORS'] []= $e->GetMessage();
}

/*
* Что пойдёт в шаблон?
*/
$arResult['QUERY'] = htmlspecialchars($arParams['QUERY']);


/*
 * Устанавливаем поисковый запрос.
 */
$search->setSearchQuery($arParams['QUERY']);

/*
 * Устанавливаем бренд.
 */
if ($arParams['BRAND_TITLE'] != '') {
    $search->setSearchCondition('brand_title', $arParams['BRAND_TITLE']);
}

/*
 * Дополнительные параметры поисковых модулей.
 */
if (count($arParams['EXTRA']) > 0) {
    $search->setSearchCondition('extra', $arParams['EXTRA']);
}

/*
 * Если мы хотим показать одну запчасть, то пропишем её ID в поиск.
 */
if ($arParams['PART_ID'] > 0) {
    $search->setSearchCondition('id', $arParams['PART_ID']);
}


/*
 * Определение типа поиска.
 */
$arParams['TYPE'] = LinemediaAutoSearch::SEARCH_SIMPLE;
if ($arParams['GROUP'] && $arParams['USE_GROUP_SEARCH']) {
    $arParams['TYPE'] = LinemediaAutoSearch::SEARCH_GROUP;
}
if ($arParams['PARTIAL']) {
    $arParams['TYPE'] = LinemediaAutoSearch::SEARCH_PARTIAL;
}


$search->setType($arParams['TYPE']);



/*
 * Подключение формы поиска.
 */

$url = str_replace('#ARTICLE#', '', $arParams['SEARCH_ARTICLE_URL']);
$url = str_replace('//', '/', $url);

$arResult['FORM_ACTION'] = $url;


if (in_array($arParams['SHOW_BLOCKS'], array('form', 'both'))) {
   $this->IncludeComponentTemplate('default');
}


/*
 * Подключение вывода результатов поиска.
 */
if (!in_array($arParams['SHOW_BLOCKS'], array('results', 'both'))) {
    return;
}



// Convey recieved modificator to LinemediaAutoEventSelf::CustomSearchResult_Modificator
$search->setModificator($arParams['SEARCH_MODIFICATION_SET']);

// Convey given parameters of sorting and ordering to LinemediaAutoEventSelf::OnSearchExecuteEnd_UniteGroupsWithSimilarApp
$search->setSortOrderForSimilarGroup(array($arParams['SORT'], $arParams['ORDER']));

//set condition (article or title) in which sources search should be accomplished
//Linemedia API, Local BD, Remote Suppliers, Sphinx
$search->setSearchLimit($arParams['SEARCH_LIMIT']);


if (isset($_REQUEST['search_limit']) && $_REQUEST['search_limit'] == \LinemediaAutoSearch::ARTICLE_LIMIT) {
    $by = LinemediaAutoSearch::DEBUG_LIMIT_ARTICLE_MESS;
} elseif (isset($_REQUEST['search_limit']) && $_REQUEST['search_limit'] == LinemediaAutoSearch::TITLE_LIMIT) {
    $by = LinemediaAutoSearch::DEBUG_LIMIT_TITLE_MESS;
} else {
    $by = LinemediaAutoSearch::DEBUG_LIMIT_DEFAULT;
}


/*
 * Выполняем запрос.
 */
try {
    $search->execute();
} catch (Exception $e) {
    $arResult['ERRORS'] []= $e->GetMessage();
}



/*
 * Ошибки от модулей.
 */
$modules_exceptions = $search->getThrownExceptions();
foreach ($modules_exceptions as $exception) {
    $arResult['ERRORS'] []= $exception->GetMessage();
}


/*
* Подробная информация о деталях
*/
$parts_detail_info = $search->getResultInfo();

/*
 * TecDoc и Linemedia аналоги
 */
$resultInfo = $search->getResultInfo();
$arResult['tecdocAndLinemediaAnalogs'] = $resultInfo['tecdocAndLinemediaAnalogs'];



/*
* Словоформы для поиска информации о детали в текдоке
*/
$wordforms_obj = new LinemediaAutoWordForm();

$suppliers = array();

/*
 * Что пришло в ответ?
 */
switch ($search->getResultsType()) {
	
    case 'catalogs':
        $arResult['CATALOGS'] = $search->getResultsCatalogs();
        foreach ($arResult['CATALOGS'] as $id => $catalog) {
            $arResult['CATALOGS'][$id]['url'] = LinemediaAutoUrlHelper::getPartUrl(
                array(
                    'article' => urlencode(urlencode($arParams['QUERY'])), // (!empty($catalog['article'])) ? ($catalog['article']) : ($arParams['QUERY']),
                    'brand_title' => strtoupper($catalog['brand_title']),
                    'extra' => $catalog['extra'],
                ),
                $arParams['SEARCH_ARTICLE_URL'],
				
                $arParams['TYPE']
            );
        }
		
        ksort($arResult['CATALOGS']);
		
        if ($AJAX) {
            die(safe_json_encode(array(
                'type' => 'catalogs',
                'data' => $arResult['CATALOGS'],
            )));
        }

        $this->IncludeComponentTemplate('catalogs');
        break;

    case '404':
    case 'parts':
        
    	
        $arResult['PARTS'] = $search->getResultsParts();
        
        // информация о схеме установки детали
        $arResult['NODE_INFO'] = $search->getArticleNodeInfo();
        if($arResult['NODE_INFO']) {
	        $arResult['NODE_INFO']['ORIGINAL_CATALOG_URL'] = $arParams['ORIGINAL_CATALOGS_FOLDER'] . htmlspecialchars($arResult['NODE_INFO']['article']['Brand']).'/'.$arResult['NODE_INFO']['ID_mod'].'/'.$arResult['NODE_INFO']['ID_typ'].'/'.$arResult['NODE_INFO']['ID_grp'].'/'.$arResult['NODE_INFO']['ID_sec'].'/';
        }
        
        
        
        /*
         * Информация о деталях.
         */
        $info = $search->getResultInfo();

        
        /*
         * Приведение к единому виду брендов и артикулов.
         */
        foreach ($arResult['PARTS'] as $group_id => $parts) {
            foreach ($parts as $i => $spare) {
                $arResult['PARTS'][$group_id][$i]['brand_title'] = strtoupper($spare['brand_title']);
                $arResult['PARTS'][$group_id][$i]['article']     = LinemediaAutoPartsHelper::clearArticle($spare['article']);
            }
        }

		/*
		 * Show analogs?
		 */
		if ( $arParams['SHOW_ANALOGS'] == 'N' ) {
			foreach ($arResult['PARTS'] as $group_id => $parts) {
				if($group_id != 'analog_type_N') {
					unset($arResult['PARTS'][$group_id]);
				}
			}
		}

        /*
         * Сортировка групп деталей.
         */
        asort($arResult['PARTS']);
        if (isset($arResult['PARTS']['analog_type_N'])) {
            $N['analog_type_N'] = $arResult['PARTS']['analog_type_N'];
            unset($arResult['PARTS']['analog_type_N']);
            $arResult['PARTS'] = array_merge_recursive($N, $arResult['PARTS']);
        }
                
        /*
         * Пробежимся по запчастям и ...
         */
        foreach ($arResult['PARTS'] as $group_id => $parts) {
        
            foreach ($parts as $i => $spare) {
   	
                /*
                 * Сформируем путь для покупки
                */
                $spare['part_id']        = (int) $spare['id'];
                $spare['supplier_id']    = (string) $spare['supplier_id'];
        
                $buy_url  = LinemediaAutoUrlHelper::getPartBuyUrl($spare, $arParams['BUY_ARTICLE_URL'], $arParams['SEARCH_ARTICLE_URL']);
                /*
                 * Проверим доступ к поставщику
                */
                if(!LinemediaAutoSupplier::isSupplierAccesRight($spare['supplier_id'], 'supplier_id')) {
                    unset($arResult['PARTS'][$group_id][$i]);
                    continue;
                }

                if($spare['supplier_id'] == 2) {
                    $debug = true;
                }
        
                $buy_url .= '&'.$arParams['ACTION_VAR'].'=ADD2BASKET';
        
                $arResult['PARTS'][$group_id][$i]['buy_url'] = $buy_url;
                
                /*
                 * Объект запчасти
                */
                $part_obj = new LinemediaAutoPart($spare['id'], $spare);
                
                
                /*
                 * Посчитаем цену товара
                */
                $price = new LinemediaAutoPrice($part_obj);
                if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
                    $price_calc = $price->calculate();
                } else {
                    $price_calc = $spare['price'];
                }

                $arResult['PARTS'][$group_id][$i]['price_src'] = $price_calc;

                $currency = $price->getCurrency();
                $user_currency = $USER->GetParam('CURRENCY');
                if(strlen($user_currency) == 3 && $currency != $user_currency) {
                    $currency = $user_currency;
                    $price_calc = LinemediaAutoPrice::userPrice($price_calc);
                }
                $arResult['PARTS'][$group_id][$i]['price'] =  CurrencyFormat($price_calc, $currency);

                /*
                 * Для отладки добавим цену товара в линк
                * Цена из линка при покупке НЕ учитывается
                */
                $arResult['PARTS'][$group_id][$i]['buy_url'] .= '&p=' . $arResult['PARTS'][$group_id][$i]['price_src'];
                $arResult['PARTS'][$group_id][$i]['buy_url'] .= '&ch_id=' . $arResult['PARTS'][$group_id][$i]['chain_id'];

                /*
                 * Обработаем дополнительные параметры
                 */
                if(is_array($arParams['BUY_ARTICLE_ADDITIONAL']) && count($arParams['BUY_ARTICLE_ADDITIONAL']) > 0) {
                    foreach($arParams['BUY_ARTICLE_ADDITIONAL'] as $k => $v) {
                        $arResult['PARTS'][$group_id][$i]['buy_url'] .= '&' . $k . '=' . urlencode($v);
                    }
                }
        
                /*
                 * Бренд
                */
                $use_wordform = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SHOW_WORDFORM_PARTS' ,'N');
                if ($use_wordform == 'Y') {
                    $wordforms = new LinemediaAutoWordForm();
                    $wordform = $wordforms->getBrandGroup($spare['brand_title']);
                    if (!empty($wordform)) {
                        $arResult['PARTS'][$group_id][$i]['brand']['title'] = $wordform;
                    } else {
                        $arResult['PARTS'][$group_id][$i]['brand']['title'] = $spare['brand_title'];
                    }
                } else {
                    $arResult['PARTS'][$group_id][$i]['brand']['title'] = $spare['brand_title'];
                }
                
                /*
                 * Поставщик
                */
                if(!isset($suppliers[$spare['supplier_id']])) {
                	$supplier = $suppliers[$spare['supplier_id']] = new LinemediaAutoSupplier($spare['supplier_id']);
                } else {
	                $supplier = $suppliers[$spare['supplier_id']];
                }
        
                $arResult['PARTS'][$group_id][$i]['supplier'] = $supplier->getArray();
        
                $arResult['PARTS'][$group_id][$i]['supplier_title'] = $arResult['PARTS'][$group_id][$i]['supplier']['PROPS']['visual_title']['VALUE'];
        
                /*
                 * Вес
                */
                $arResult['PARTS'][$group_id][$i]['weight'] = (float) $arResult['PARTS'][$group_id][$i]['weight'];
        
                /*
                 * Срок доставки
                */
                if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_EXPERIMENTAL_ORDER_SPLIT', 'N') !== 'Y') {
	                if (!$arResult['PARTS'][$group_id][$i]['delivery_time']) {
	                    $arResult['PARTS'][$group_id][$i]['delivery_time'] = (int) $supplier->get('delivery_time');
	                } else {
	                    $arResult['PARTS'][$group_id][$i]['delivery_time'] += (int) $supplier->get('delivery_time');
	                }
                }
                $arResult['PARTS'][$group_id][$i]['delivery'] = $arResult['PARTS'][$group_id][$i]['delivery_time'];
                
                
                /*
                 * Пересчитаем в дни
                 */
                $delivery_time = $arResult['PARTS'][$group_id][$i]['delivery_time'];
                if ($delivery_time >= 24) {
                    $days = round($delivery_time / 24);
                    $delivery_time = '&asymp; ' . $days . ' ' . GetMessage('LM_AUTO_MAIN_DAYS');
                } else {
                    $delivery_time .= ' ' . GetMessage('LM_AUTO_MAIN_HOURS');
                }
                $arResult['PARTS'][$group_id][$i]['delivery_time'] = $delivery_time;
                
                
                /*
                 * Количество.
                 */ 
                $arResult['PARTS'][$group_id][$i]['quantity'] = preg_replace('/[^\.0-9]/', '', $arResult['PARTS'][$group_id][$i]['quantity']);
                
                
                /*
                 * Есть ли Инфо о запчасти в текдоке
                * Проверим по каждой словоформе
                */
                $brand_titles = $wordforms_obj->getBrandWordforms($spare['brand_title']);
                foreach($brand_titles AS $wf_brand_title) {
                    $article_id = (int) $parts_detail_info[$wf_brand_title][$spare['article']]['tecdoc']['article_id'];
                    $is_oem = false;
                    if(LinemediaAutoCrossesApiDriver::isEnabled()) {
                        $is_oem = (bool) $parts_detail_info[$wf_brand_title][$spare['article']]['tecdoc']['oem'];
                    }
                    if($article_id > 0 && !$is_oem) {
                        $arResult['PARTS'][$group_id][$i]['info'] = true;
                        $arResult['PARTS'][$group_id][$i]['article_id'] = $article_id;
                        break;
                    }
                }
        
        
                /*
                 * URL страницы с информацией.
                */
                $part_info_url = str_replace(
                    array('#BRAND#', '#ARTICLE#'),
                    array($spare['brand_title'], $spare['article']),
                    $arParams['INFO_URL']
                );
                $arResult['PARTS'][$group_id][$i]['part_info_url'] = $part_info_url;
        
                /*
                 * URL поиска запчасти
                */
				$part_search_url = LinemediaAutoUrlHelper::getPartUrl(
			        array(
			                'article'     => $spare['article'],
			                'brand_title' => $spare['brand_title'],
			                'extra'       => $spare['extra'],
			            ),
					$arParams['SEARCH_ARTICLE_URL']
				);
	            
                $arResult['PARTS'][$group_id][$i]['part_search_url'] = $part_search_url;
        
        
                /*
                 * Проверка для антиботов.
                */
                if ($arParams['ANTI_BOTS']) {
                    $arResult['PARTS'][$group_id][$i]['article']            = str_pad(substr($spare['article'], 0, 2), strlen($spare['article']), '*');
                    $arResult['PARTS'][$group_id][$i]['original_article']   = str_pad(substr($spare['original_article'], 0, 2), strlen($spare['original_article']), '*');
                    $arResult['PARTS'][$group_id][$i]['part_info_url']      = 'javascript:void(0)';
                    $arResult['PARTS'][$group_id][$i]['part_search_url']    = 'javascript:void(0)';

                    // заменено проверкой сессии при добавлении в корзину
                    //$arResult['PARTS'][$group_id][$i]['buy_url']            = $APPLICATION->GetCurPageParam('SHOW_AUTH_FORM=1');
        
                    unset($arResult['PARTS'][$group_id][$i]['info']);
                    unset($arResult['PARTS'][$group_id][$i]['article_id']);
                }
            }            
        }        


        /*
         * Проверим пустые группы
         */
        foreach ($arResult['PARTS'] as $group_id => $parts) {
            if(count($parts) < 1) unset($arResult['PARTS'][$group_id]);
        }

        /*
         * Объединять группы в одну.
         */
        if ($arParams['MERGE_GROUPS']) {
            $parts = array_reduce($arResult['PARTS'], function($a, $b) { return array_merge($a, $b); }, array());
            $arResult['PARTS'] = array('analog_type_N' => $parts);
        }


        /*
         * Сортировка запчастей
         */
        $arResult['PARTS'] = LinemediaAutoPartsHelper::sortCatalogs($arResult['PARTS'], $arParams['SORT'], $arParams['ORDER']);


        /*
         * Ограничения по количеству (после сортировки).
         */
        if ($arParams['LIMIT'] > 0) {
            foreach ($arResult['PARTS'] as $group_id => $parts) {
                $arResult['PARTS'][$group_id] = array_slice($parts, 0, $arParams['LIMIT']);
            }
        }

        LinemediaAutoDebug::add('Component actions with parts and catalogs', print_r(1, true), LM_AUTO_DEBUG_WARNING);

        /*
         * Сортировка по цене
        foreach ($arResult['PARTS'] as $group_id => $parts) {
            usort($arResult['PARTS'][$group_id], 'linemediaPriceSort');
        }
        */

        if ($AJAX) {
            foreach ($arResult['PARTS'] as $group => $parts) {
                foreach ($parts as $i => $spare) {
                    unset($arResult['PARTS'][$group][$i]['supplier']);
                    $arResult['PARTS'][$group][$i]['supplier']['PROPS']['visual_title']['VALUE'] = $spare['supplier']['PROPS']['visual_title']['VALUE'];
                }
            }
            die(safe_json_encode(array(
                'type' => 'parts',
                'data' => $arResult['PARTS'],
            )));
        }
        
        
        
        /*
         * Создаём событие для подсчета стаистики поиска (странице в админке "Статистика поиска")
         */
        
        $events = GetModuleEvents("linemedia.auto", "OnSearchResultParts");
        while ($arEvent = $events->Fetch()) {
            
            ExecuteModuleEventEx($arEvent, array(&$arParams, &$arResult, $search->getModificator()));
        }


        /**
         *
         * TODO training ground
         *
         */

        $this->IncludeComponentTemplate('parts');
        break;

    /*
     * В дополнение к 404 могут прийти запчасти из ajax
     */
    /*case '404':

        if ($AJAX) {
            die(json_encode(array(
                'type' => '404',
            )));
        }

        $this->IncludeComponentTemplate('404');
        break;
    */
    default:
        if ($AJAX) {
            die(safe_json_encode(array(
                'type' => 'errors',
            )));
        }
        $this->IncludeComponentTemplate('errors');
}

/*
 *  Хлебные крошки.
 */

$tecdoc_brand_id = (int) $_REQUEST['tecdoc_brand_id'];
$tecdoc_folder = str_replace('_', '/', htmlspecialchars(strip_tags($_REQUEST['tecdoc_folder'])));
$tecdoc_brand = htmlspecialchars(strip_tags($_REQUEST['tecdoc_brand']));
$tecdoc_car_id = (int) $_REQUEST['tecdoc_car_id'];
$tecdoc_group_id = (int) $_REQUEST['tecdoc_group_id'];
$tecdoc_model_id = (int) $_REQUEST['tecdoc_model_id'];

$oLmApiDriver = new LinemediaAutoApiDriver();

// Бренд.
if (!empty($tecdoc_brand_id)) {
	$APPLICATION->AddChainItem(GetMessage('LM_AUTO_SEARCH_ALL_TECDOC'), $tecdoc_folder);

	$args = array('types' => array(1, 2, 3), 'brand_id' => $tecdoc_brand_id, 'include_info' => true);

	$aModelRes = $oLmApiDriver->query('getVehicleModels2', $args);

	$brand_name = (string) $aModelRes['data']['brand']['title'];

	$APPLICATION->AddChainItem($brand_name, $tecdoc_folder . $tecdoc_brand . '/');

	if (!empty($tecdoc_model_id)) {
		$model_nameRes = $oLmApiDriver->query('getVehicleModelNameById',
			$data = array('brand_id' => $tecdoc_brand_id,
						  'model_id' => $tecdoc_model_id),
			$in = 'serialized');
		$model_name = $model_nameRes['data'];
		$APPLICATION->AddChainItem($model_name, $tecdoc_folder . $tecdoc_brand . '/' . $additional_url . $tecdoc_model_id . '/');

		// Модификация.
		if (!empty($tecdoc_car_id)) {
			$type_nameRes = $oLmApiDriver->query('getModelVariantNameById',
				$data = array('brand_id' => $tecdoc_brand_id,
							  'model_id' => $tecdoc_model_id,
							  'car_id'   => $tecdoc_car_id),
				$in = 'serialized');
			$type_name = $type_nameRes['data'];
			$APPLICATION->AddChainItem($type_name, $tecdoc_folder . $tecdoc_brand . '/' . $additional_url . $tecdoc_model_id . '/' . $tecdoc_car_id . '/');

			// Группа запчастей.
			if (!empty($tecdoc_group_id)) {
				$group_nameRes = $oLmApiDriver->query('getGroupNameById',
					$data = array('type_id'  => $tecdoc_car_id,
								  'group_id' => $tecdoc_group_id),
					$in = 'serialized');
				$group_name = (GetMessage("LM_AUTO_GROUP_" . $tecdoc_group_id))? GetMessage("LM_AUTO_GROUP_" . $tecdoc_group_id) : $group_nameRes['data'];

				$APPLICATION->AddChainItem($group_name,
					$tecdoc_folder . $tecdoc_brand . '/' . $additional_url . $tecdoc_model_id . '/' . $tecdoc_car_id . '/' . $tecdoc_group_id . '/');
			}
		}
	}
	$APPLICATION->AddChainItem(GetMessage('LM_AUTO_SEARCH_SEARCH') . ' ' . $arResult['QUERY'], $_SERVER['REQUEST_URI']);
}



/*
 * Устанавливаем заголовок страницы.
 */
if ($arParams['SET_TITLE']) {
    /*
     * Для детального просмотра запчасти выведем полную информацию.
     */
    if ($arParams['PART_ID'] > 0) {
        $spare = $arResult['PARTS']['N'][0];
        $TITLE = str_replace('#QUERY#', $spare['title'] . ' ' . $spare['brand_title'] . ' ' .$spare['article'], $arParams['TITLE']);
    } else {
        $TITLE = str_replace('#QUERY#', $arParams['QUERY'], $arParams['TITLE']);
        if ($arParams['BRAND_TITLE'] != '') {
            $TITLE .= ' (' . $arParams['BRAND_TITLE'] . ')';
        }
    }
    $APPLICATION->SetTitle($TITLE);
}



/*
 * Добавим сборщик статистики Linemedia
 */
/*if ($arParams['DISABLE_STATS'] != 'Y') {
    if ($arParams['QUERY'] !== '' && $arParams['BRAND_TITLE'] != '') {
        $APPLICATION->AddHeadScript('http://api.auto-expert.info/api.js?article=' . urlencode($arParams['QUERY']) . '&brand_title=' . urldecode($arParams['BRAND_TITLE']));
    } else {
        $APPLICATION->AddHeadScript('http://api.auto-expert.info/api.js');
    }
}*/



if (!function_exists('linemediaPriceSort')) {
    function linemediaPriceSort($a, $b)
    {
        if ($a['price_src'] == $b['price_src']) {
            return 0;
        }
        return (int) $a['price_src'] > (int) $b['price_src'] ? 1 : -1;
    }
}

// Возвращение результата.
return $arResult; 
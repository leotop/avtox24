<?php
/*
 * компонент выводит автокаталог текдока из нашего API
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*
* Проверка наличия необходимых модулей
*/
if (!CModule::IncludeModule("linemedia.autotecdoc")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("iblock")) {
    ShowError('MODULE IBLOCK NOT INSTALL');
    return;
}


global $USER;


/*
 * Настройки по умолчанию
 */
if (empty($arParams['SEF_FOLDER'])) {
    $arParams['SEF_FOLDER'] = "/auto/tecdoc/";
}
if (empty($arParams['SEARCH_ARTICLE_URL'])) {
    $arParams['SEARCH_ARTICLE_URL'] = '/auto/search/#ARTICLE#/';
}
if (empty($arParams['DETAIL_URL'])) {
    $arParams['DETAIL_URL'] = COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_MAIN_DEMO_FOLDER', '/auto/').'part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/';
}
if (empty($arParams['SHOW_ORIGINAL_ITEMS'])) {
    $arParams['SHOW_ORIGINAL_ITEMS'] = 'Y';
}
if (empty($arParams['MODIFICATIONS_SET'])) {
    $arParams['MODIFICATIONS_SET'] = 'default';
}
if(empty($arParams['MODIFICATIONS_SET'])) $arParams['MODIFICATIONS_SET'] = 'default';

$arParams['ANTI_BOTS'] = ($arParams['ANTI_BOTS'] == 'Y') ? 'Y' : 'N';

$arParams['INCLUDE_PARTS_IMAGES'] = ($arParams['INCLUDE_PARTS_IMAGES'] == 'N') ? 'N' : 'Y';

$arResult['FILTER'] = ($arParams['SWIFT_FILTER'] == 'N') ? 'N' : 'Y';

$arResult['DETAILS'] = array();


$arParams['CONTEMPORARY_YEAR'] = !empty($arParams['CONTEMPORARY_YEAR']) ? intval($arParams['CONTEMPORARY_YEAR']) : 2000;


/*
 * Защита от парсинга каталога для авторизованного пользователя
 */
if (LinemediaAutoTecdocUserHelper::isRobot('LM_AUTO_ORIG_CAT_HIT', 60, 30)) {
    $arResult['ERROR'] = GetMessage('LM_AUTO_ORIGINAL_SCAN_ERROR');
    $this->IncludeComponentTemplate('error');
    return;
}



/*
 * Обработка входных запросов.
 */
// include('ajax.php');



/*
 * Типы брендов текдока (1 2 3)
 */
$tecdoc_brand_types = array_map('intval', (array) $arParams['TECDOC_BRAND_TYPES']);


/*
 * Подключим на страницу jquery и возможность открытия попапа битрикса
 */
CJSCore::Init(array('jquery', 'window', 'ajax'));

/*
 * Добавим сборщик статистики Linemedia
 */
/*if ($arParams['DISABLE_STATS'] != 'Y') {
    $APPLICATION->AddHeadScript('http://api.auto-expert.info/api.js');
}*/

// Массив шаблонов путей
/*
 * Праметр который указвает на то что урл в тектоке меняется ("#BRAND#/#MODEL_GROUP#/...")
 * Если нет, тогда стандартный ("#BRAND#/#MODEL#/...")
 */
$models_are_groupped = (isset($arParams['GROUP_MODELS']) && $arParams['GROUP_MODELS'] === 'Y');



/*
 * По ссылкам из гаража группировка не нужна
 */
if (isset($_GET['nogroup']) || (isset($_GET['from']) && $_GET['from'] == 'garage')) {
    $models_are_groupped = false;
}


if ($models_are_groupped) {
    $arUrlTemplates = array(
        "part" => "part/#PART#/",
        "list" => "index.php",
        "brand" => "#BRAND#/",
        "model_group" => "#BRAND#/?model_group=#MODEL_GROUP#",
        "model" => "#BRAND#/#MODEL#/",
        "article_link_id" => "detail-info/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
        "car_id" => "#BRAND#/#MODEL#/#CAR_ID#/",
        "group_id" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/",
        "dud" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/#DUD#/", /*такая страница не предусмотрена, но есть куча вумников, которые подтирают куски url.*/
        //"article_link_id" => "#BRAND#/#MODEL_GROUP#/#MODEL#/#CAR_ID#/#GROUP_ID#/#ARTICLE_ID#/#ARTICLE_LINK_ID#/"
    );
    $arResult['GROUP_MODELS'] = 'Y';
} else {
    $arUrlTemplates = array(
        "part" => "part/#PART#/",
        "list" => "index.php",
        "brand" => "#BRAND#/",
        "model" => "#BRAND#/#MODEL#/",
        "article_link_id" => "detail-info/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
        "car_id" => "#BRAND#/#MODEL#/#CAR_ID#/",
        "group_id" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/",
        "dud" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/#DUD#/", /*такая страница не предусмотрена, но есть seo-шники, которые подтирают куски url...*/
        //"article_link_id" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/#ARTICLE_ID#/#ARTICLE_LINK_ID#/"
    );
    $arResult['GROUP_MODELS'] = 'N';
}

$arVariables = array();


/*
 * Обработка адресов.
 */
$url  = $APPLICATION->GetCurPage(true);

$arResult['URL'] = $url;

$page = CComponentEngine::ParseComponentPath($arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables, $url);

/*
 * Если $page === false, то у нас ни один из $arUrlTemplates не подошёл.
 * Будем считать, что это возможно в случае, когда у нас нет завершающего слеша
 * Поэтому редиректим на страницу со слешем на конце (но только в случае включённого чпу).
 */
if ($page == false && $arParams['SEF_MODE'] == 'Y') {
    $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
	if($uri){
		$path = parse_url($uri, PHP_URL_PATH);
		$path = str_replace('index.php', '', $path);
		if (strrpos($path, '/') != strlen($path)-1) {
			$q = parse_url($uri, PHP_URL_QUERY);
			if (strlen($q)) {
				LocalRedirect($path.'/?'.$q, 1, '301 Moved Permanently');
			} else {
				LocalRedirect($path.'/', 1, '301 Moved Permanently');
			}
			return;
		}
	}
} else {
    /*
     * После удачного вызова ParseComponentPath() в $arVariables лежат полученные из шаблона пути переменные.
     */
    //extract($arVariables);
}


/*
 * Подключаемся к API
 */
$api = new LinemediaAutoTecdocApiDriver();
$api->changeModificationsSetId($arParams['MODIFICATIONS_SET']);

/*
 * Показывать ли в URL названия брендов вместо их ID?
 */
$is_brand_car_name = (isset($arParams['SHOW_CAR_BRANDS_IN_URI']) && $arParams['SHOW_CAR_BRANDS_IN_URI'] == 'Y');

/*
 * Включён ли режим редактирования каталога?
 */
$arResult['EDIT_MODE'] = ($USER->IsAdmin() && $_SESSION['SESS_INCLUDE_AREAS']);



/*
 * Определим страницу для отображения
 */
$arVariables = array_map('trim', $arVariables);

if (isset($_GET['model_group'])) {
    $arVariables['MODEL_GROUP'] = (string) $_GET['model_group'];
}
if (isset($arVariables['MODEL_GROUP'])) {
    $arVariables['MODEL_GROUP'] = htmlspecialchars_decode($arVariables['MODEL_GROUP'], ENT_QUOTES); // KIA CEE'D
}


/*
 * Функция для сортировки брендов внутри буквенной группы
 */
if (!function_exists('tecdocItemsSort')) {
    function tecdocItemsSort($a, $b)
    {

        /*
        *    если указано только у одного элемента, то второму ставим 500 (потому что стандартное битриксное значение для "сортировка по умолчанию")
        */
        if ( isset($a['sort']) ^ isset($b['sort'])) {
            $a['sort'] =  isset($a['sort']) ? $a['sort'] : 500;
            $b['sort'] =  isset($b['sort']) ? $b['sort'] : 500;
        }
        /*
         * Если отсутствуют поля сортировки или оба поля 500
         */
        if ((!isset($a['sort']) && !isset($b['sort'])) || ($a['sort'] == 500 && $b['sort'] == 500)) {
            /*
             * Бренды
             */
            if (isset($a['manuName'])) {
                if ($a['manuName'] == $b['manuName']) {
                    return 0;
                }
                return ($a['manuName'] < $b['manuName']) ? -1 : 1;
            }

            /*
             * Модели
             */
            if (isset($a['modelname'])) {
                if ($a['modelname'] == $b['modelname']) {
                    return 0;
                }
                return ($a['modelname'] < $b['modelname']) ? -1 : 1;
            }


            /*
             * Модификации
             */
            if (isset($a['carName'])) {
                if ($a['carName'] == $b['carName']) {
                    return 0;
                }
                return ($a['carName'] < $b['carName']) ? -1 : 1;
            }


            /*
             * Группы
             */
            if (isset($a['assemblyGroupName'])) {
                // TODO: не факт, что работает на 1251
                $a['assemblyGroupName'] = mb_convert_case($a['assemblyGroupName'], MB_CASE_TITLE);
                $b['assemblyGroupName'] = mb_convert_case($b['assemblyGroupName'], MB_CASE_TITLE);

                if ($a['assemblyGroupName'] == $b['assemblyGroupName']) {
                    return 0;
                }
                return ($a['assemblyGroupName'] < $b['assemblyGroupName']) ? -1 : 1;
            }


            /*
             * Детали сортируются по цене, затем по бренду
             */
            if (isset($a['articleNo'])) {

                $a_min = (int) $a['min_price'];
                $b_min = (int) $b['min_price'];

                $a_min = ($a_min) ? $a_min : 999999999;
                $b_min = ($b_min) ? $b_min : 999999999;

                if ($a_min == $b_min) {
                    $a['brandName'] = mb_convert_case($a['brandName'], MB_CASE_TITLE);
                    $b['brandName'] = mb_convert_case($b['brandName'], MB_CASE_TITLE);

                    if ($a['brandName'] == $b['brandName']) {
                        return 0;
                    }
                    return ($a['brandName'] < $b['brandName']) ? -1 : 1;
                }
                return ($a_min < $b_min) ? -1 : 1;
            }
        }

        if ($a['sort'] == $b['sort']) {
            return 0;
        }
        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }
}


$arResult['HIDE_MANUAL_TECDOC'] = true;
//$hide_manual_tecdoc = true;


//$sCacheID = 'tecdoc_auto_catalog_' . $arResult['EDIT_MODE'] . serialize($arVariables) . serialize($USER->GetGroups()) . $_REQUEST['sort'].'|'.$_REQUEST['dir'];
//if ($this->StartResultCache(false, $sCacheID)) {
	
	
	
	
	
	
	
	if ($arVariables['PART'] > 0) {
		// Формарование обратной ссылки (в каталог).
		if (!empty($_SESSION['tecdoc_catalog'])) {
		    $databack = $_SESSION['tecdoc_catalog'][$arParams['ARTICLE_ID']];
		    
		    $backurl  = $arParams['SEF_FOLDER'];
		    $backurl .= $databack['brand']['id'];
		    if (!empty($databack['model_group']['title'])) {
		        $backurl .= '/'.$databack['model_group']['title'];
		    }
		    $backurl .= '/'.$databack['model']['id'];
		    $backurl .= '/'.$databack['modification']['id'];
		    $backurl .= '/'.$databack['group']['id'];
		    $backurl .= '/';
		    
		    $arParams['BACKURL'] = $backurl;
		}
		try {
			$part_id = intval($arVariables['PART']);
			$args = array(
				'part_ids' => array($part_id),
				'model_id' => $databack['model']['id'],
				'modification_id' => $databack['modification']['id'],
				'group_id' => $databack['group']['id'],
			);
			$response = $api->query('getPartsDetails3', $args);
			$arResult['DATA'] = $response['data'][$part_id];
		} catch (Exception $e) {
			$arResult['ERROR'] = $e->GetMessage(); 
			$this->IncludeComponentTemplate('error');
			return;
		}
		
		$arResult['IMAGE'] = isset($arResult['DATA']['images'][0]) ? $arResult['DATA']['images'][0]['url'] : null;
		$arResult['APPLICABILITY'] = $arResult['DATA']['appliance'];
		
		$template = 'part';
	}
	
	
	
	
	
	
	
	
	
    /*
     * Первая страница с брендами
     */
    if ($arVariables['BRAND'] == '' AND $arVariables['PART'] <= 0) {
        $template = 'brands';

        $args = array('types' => $tecdoc_brand_types);
        try {
            $aBrandRes = $api->query('getBrands3', $args);
        } catch (Exception $e) {
            $arResult['ERROR'] = $e->GetMessage();
            $this->IncludeComponentTemplate('error');
            return;
        }

        // Применим сортировку.
        uasort($aBrandRes['data'], 'tecdocItemsSort');

        $brands_sorted = array();
        foreach ($aBrandRes['data']['brands'] AS $brand) {
            // Не показываем пустые буквы, если не в режиме правки.
            if ($brand['hidden'] == 'Y' && !$arResult['EDIT_MODE']) {
                continue;
            }
            $letter = substr($brand['manuName'], 0, 1);
            $brand['sort'] = ($brand['sort']) ? $brand['sort'] : 500;
            $brands_sorted[$letter][] = $brand;
        }

        ksort($brands_sorted);

        foreach ($brands_sorted as &$brands) {
            uasort($brands, 'tecdocItemsSort');
        }

        // Отправим результат в шаблон.
        $arResult['BRANDS'] = $brands_sorted;

        $arResult['type'] = 'brand';

        $arResult['parent_id'] =  '';//join(':', $tecdoc_brand_types);//$arParams['MODIFICATIONS_SET'];

    /*
     * Присутствует выбор бренда
     */
    } elseif ($arVariables['BRAND'] != '' && !isset( $arVariables['MODEL'])) {

        $template = 'models';


        /*
         * Выборка группы моделей.
         */
        $args = array('types' => $tecdoc_brand_types, 'brand_id' => $arVariables['BRAND'], 'include_info' => true);

        try {
            $aModelRes = $api->query('getModels3', $args);
        } catch (Exception $e) {
            $arResult['ERROR'] = $e->GetMessage();
            $this->IncludeComponentTemplate('error');
            return;
        }
		
        foreach ($aModelRes['data']['models'] as $i => $model) {
            $aModelRes['data']['models'][$i]['sort'] = $model['sort'] ? $model['sort'] : 500;
        }
        
        /*
         * Применим сортировку
         */
        uasort($aModelRes['data']['models'], 'tecdocItemsSort');

        if ($arResult['GROUP_MODELS'] == 'Y' && !isset($arVariables['MODEL_GROUP'])) {
            $arResult['MODEL_GROUPS'] = array();
            foreach ($aModelRes['data']['models'] as $key => $value) {

                /*
                 * Не учитываем неактивные модели
                 */
                if ($value['hidden'] == 'Y' && !$arResult['EDIT_MODE']) {
                    continue;
                }
                $model = explode(' ', $value['modelname']);
                $code = $model[0];
                $arResult['MODEL_GROUPS'][$code] = $code;
            }

            /*
             * 404
             */
            if (count((array) $arResult['MODEL_GROUPS']) < 1 && !$arResult['EDIT_MODE']) {
                //канонический bitrix-способ генерации 404 ошибки
                @define('ERROR_404', 'Y');
                CHTTP::SetStatus('404 Not Found');
                return;
            }

        } else {

            if (isset($arVariables['MODEL_GROUP'])) {
                foreach ($aModelRes['data']['models'] as $model) {

                    /*
                    * Не учитываем неактивные модели
                    */
                    if($model['hidden'] == 'Y' AND !$arResult['EDIT_MODE'])
                        continue;


                    $modelname = explode(' ', $model['modelname']);
                    $code = trim($modelname[0]);

                    if ($code == $arVariables['MODEL_GROUP']) {
                        $arResult['MODELS'][] = $model;
                    }
                }
            } else {
                $arResult['MODELS'] = (array) $aModelRes['data']['models'];
            }

            /*
             * 404
             */
            if (count((array) $arResult['MODELS']) < 1 && !$arResult['EDIT_MODE']) {
                @define('ERROR_404', 'Y');
                CHTTP::SetStatus('404 Not Found');
                return;
            }

        }


        $arResult['brand_title'] = (string) $aModelRes['data']['brand']['title'];
        $arResult['brand_id'] = (string) $arVariables['BRAND'];


        $arResult['type'] = 'model';
        $arResult['parent_id'] = $arVariables['BRAND'];//$arParams['MODIFICATIONS_SET'];//;



    /*
     * Присутствует выбор модели
     */
    } elseif($arVariables['MODEL'] != '' && !isset($arVariables['CAR_ID'])) {
        $template = 'modifications';
        
        
        $args = array('brand_id' => $arVariables['BRAND'], 'model_id'=>$arVariables['MODEL']);

        try {
            $aModRes = $api->query('getModifications3', $args);
        } catch (Exception $e) {
            $arResult['ERROR'] = $e->GetMessage();
            $this->IncludeComponentTemplate('error');
            return;
        }


        /*
         * 404
         */
        if (count($aModRes['data']['modifications']) < 1 && !$arResult['EDIT_MODE']) {
            @define("ERROR_404", "Y");
            CHTTP::SetStatus("404 Not Found");
            return;
        }


        /*
         * Применим сортировку
         */
        uasort($aModRes['data']['modifications'], 'tecdocItemsSort');


        $arResult['MODIFICATIONS'] = $aModRes['data']['modifications'];
        $arResult['brand_title'] = (string) $aModRes['data']['brand']['title'];
        $arResult['model_title'] = (string) $aModRes['data']['model']['title'];

        $arResult['brand_id'] = (string) $arVariables['BRAND'];
        $arResult['model_id'] = (string) $arVariables['MODEL'];
        $arResult['main_image'] = (string) $aModRes['data']['main_image']['url'];
        $arResult['images'] = (array) $aModRes['data']['images'];
        $arResult['type'] = 'modification';
        $arResult['parent_id'] = $arResult['brand_id'].':'.$arResult['model_id'];//$arParams['MODIFICATIONS_SET'];//

        
        // add stat code
        if(CModule::IncludeModule('linemedia.auto') && class_exists('LinemediaAutoStat'))
	        LinemediaAutoStat::addTecdocModificationsView($arResult);
        

    /**
     * TODO choose of mod that should be skiped
     * Присутствует выбор модификации
     */
    } elseif($arVariables['CAR_ID'] != '' && !isset( $arVariables['GROUP_ID'])) {


    	$args = array('brand_id' => $arVariables['BRAND'], 'model_id' => $arVariables['MODEL'], 'type_id' => $arVariables['CAR_ID'], 'group_id' => 0);
    	$manual_tecdoc_groups = $arParams['MANUAL_TECDOC_GROUPS'] != null ? explode(',', $arParams['MANUAL_TECDOC_GROUPS']) : null;
    	$nonexisten_group = array();

        if ($manual_tecdoc_groups != null) {

        	$response_parts = array();

        	foreach ($manual_tecdoc_groups as $item) {

        		$args = array(
        				'brand_id' => $arVariables['BRAND'], 'model_id' => $arVariables['MODEL'], 'modification_id' => $arVariables['CAR_ID'], 'group_id' => (int) trim($item),
        				'include_oem' => ($arParams['SHOW_ORIGINAL_ITEMS'] === 'Y'), 'include_info' => ($arParams['INCLUDE_PARTS_IMAGES'] == 'Y')
        		);

        		try {
        			$response_parts = $api->query('getGroupParts3', $args);
        		} catch (Exception $e) {
        			$arResult['ERROR'] = $e->GetMessage();
        			$this->IncludeComponentTemplate('error');
        			return;
        		}

        		if ( count($response_parts['data']['parts']) == 0) {
                    array_push($nonexisten_group, $item);
        		}

        		$arResult['DETAILS'] = array_merge((array) $arResult['DETAILS'], (array) $response_parts['data']['parts']);

        	}

        	$arResult['brand_title'] = (string) $response_parts['data']['brand']['title'];
        	$arResult['model_title'] = (string) $response_parts['data']['model']['title'];
        	$arResult['modification_title'] = (string) $response_parts['data']['modification']['title'];

        	/**
        	 * forming detail url for each parts
        	 */
        	
        	if(CModule::IncludeModule('linemedia.auto')) {
	        	$search = new LinemediaAutoSearchSimple();
	        	$where = array('#ARTICLE_ID#','#BRAND_ID#');
	
	        	foreach ($arResult['DETAILS'] as $key => $detail) {
	
	
	        		$findurl = LinemediaAutoTecdocUrlHelper::getPartUrl(array(
	        				'article' => $detail['articleNo'],
	        				'brand_title' => $detail['brandName'],
	        				'extra' => array(
	        						'gid' => $detail['genericArticleId'],
	        				),

	        		));
	
	        		$detail_url = ($detail['articleId'] > 0 && $detail['articleLinkId'] > 0) ? str_replace(array('#ARTICLE_ID#','#ARTICLE_LINK_ID#'), array($detail['articleId'], $detail['articleLinkId']), $arParams['DETAIL_URL']) : null;
	        		
	        		
	        		/*
	        		* New part
	        		*/
	        		/*if($detail['articleId']) {
	        			$detail_url = $arParams['SEF_FOLDER'] . 'part/' . $detail['articleId'] . '/';
	        		} else {
		        		$detail_url = null;
	        		}*/
	        		
	        		
	
	        		/*
	        		 * Для созданных вручную деталей тоже должна быть страница
	        		*/
	        		if($detail['lm_mod_id'] == 'Y') {
	        			$detail_url = str_replace(array('#ARTICLE_ID#','#ARTICLE_LINK_ID#'), array($detail['source_id'], LinemediaAutoApiModifications::LOCAL_ID_KEY), $arParams['DETAIL_URL']);
	        		}
	
	
	        		$arResult['DETAILS'][$key]['detail_url'] = $detail_url;
	        		$arResult['DETAILS'][$key]['search_url'] = $findurl; // str_replace($where, $what, $arParams['SEARCH_URL']);
	
	        		/*
	        		 * Для хлебных крошек в поиске
	        		*/
	        		
	        		$arResult['DETAILS'][$key]['search_url'] .= '?from=tecdoc';
	

	        		// Группа модель.
	        		/*if (!empty($arVariables['BRAND'])) {
	        			$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_brand_id=' . $arVariables['BRAND'];
	        			//$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_brand=' . $arVariables['BRAND'];
	        			$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_folder=' . str_replace('/', '_', $arParams['SEF_FOLDER']);
	
	        			if (!empty($arVariables['MODEL'])) {
	        				$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_model_id=' . $arVariables['MODEL'];
	
	        				// Модификация.
	        				if (!empty($arVariables['CAR_ID'])) {
	        					$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_car_id=' . $arVariables['CAR_ID'];
	
	        					// Группа запчастей.
	        					if (!empty($arVariables['GROUP_ID'])) {
	        						$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_group_id=' . $arVariables['GROUP_ID'];
	        					}
	        				}
	        			}
	        		}*/
	
	        		/*
	        		 * Поищем доступные варианты в локальной базе
	        		*/
	        		$parts = (array) $search->searchLocalDatabaseForPart(array(
	        				'article' => $detail['articleNo'],
	        				'brand_title' => $detail['brandName']
	        		), true);
	
	
	        		/*
	        		 * Скрываем товары, которых нет в локальной базе
	        		*/
	        		if ($arParams['HIDE_UNAVAILABLE'] == 'Y' && count($parts) == 0) {
	        			unset($arResult['DETAILS'][$key]);
	        			continue;
	        		}
	
	        		$arResult['DETAILS'][$key]['PARTS'] = $parts;
	
	        		foreach ($parts as $part) {
	        			$part_obj = new LinemediaAutoPart($part['id']);
	
	        			/*
	        			 * Посчитаем цену товара
	        			*/
	        			$price = new LinemediaAutoPrice($part_obj);
	        			$price_calc = $price->calculate();
	        			$formatted = CurrencyFormat($price_calc, $price->getCurrency());
	
	        			$arResult['DETAILS'][$key]['PRICES'][$price_calc] = $formatted;
	        		}
	
	        		foreach ($arResult['DETAILS'] as $key => $part) {
	        			if (count($part['PRICES']) > 0) {
	        				$arResult['DETAILS'][$key]['min_price'] = min(array_keys($part['PRICES']));
	        				$arResult['DETAILS'][$key]['max_price'] = max(array_keys($part['PRICES']));
	        			}
	        		}
	
	
	        		/*
	        		 * Костыль для передачи информации о выбранном автомобиле на другие страницы
	        		*/
	        		$_SESSION['tecdoc_catalog'][$detail['articleId']] = array(
	        				'brand' 		=> array('id' => $arVariables['BRAND'], 		'title' => $arResult['brand_title']),
	        				'model' 		=> array('id' => $arVariables['MODEL'], 		'title' => $arResult['model_title']),
	        				'modification' 	=> array('id' => $arVariables['CAR_ID'], 		'title' => $arResult['modification_title']),
	        				'group' 		=> array('id' => $arVariables['GROUP_ID'], 		'title' => $arResult['group_title']),
	        		);
	        	}
	        } // if linemedia.auto

        	//end of forming
        	
        	
        	// add stat code
	        if(CModule::IncludeModule('linemedia.auto') && class_exists('LinemediaAutoStat'))
	        	LinemediaAutoStat::addTecdocPartsView($arResult);
        	
        	
        	
        	$template = 'parts';
        } else {

        	$args = array('brand_id' => $arVariables['BRAND'], 'model_id' => $arVariables['MODEL'], 'modification_id' => $arVariables['CAR_ID'], 'group_id' => 0);

        	try {
        		$aGroupsRes = $api->query('getGroups3', $args);
        	} catch (Exception $e) {
        		$arResult['ERROR'] = $e->GetMessage();
        		$this->IncludeComponentTemplate('error');
        		return;
        	}

        	/*
        	 * 404
        	*/
        	if (count($aGroupsRes['data']['groups']) < 1 && !$arResult['EDIT_MODE']) {
        		CHTTP::SetStatus('404 Not Found');
        		@define('ERROR_404', 'Y');
        		return;
        	}

        	/*
        	 * Применим сортировку
        	*/
        	uasort($aGroupsRes['data']['groups'], 'tecdocItemsSort');

        	$arResult['GROUPS'] = $aGroupsRes['data']['groups'];
        	$arResult['SHORTCUTS'] = $aGroupsRes['data']['shortcuts'];

        	$arResult['brand_title'] = (string) $aGroupsRes['data']['brand']['title'];
        	$arResult['model_title'] = (string) $aGroupsRes['data']['model']['title'];
        	$arResult['modification_title'] = (string) $aGroupsRes['data']['modification']['title'];
        	
        	// add stat code
        	if(CModule::IncludeModule('linemedia.auto') && class_exists('LinemediaAutoStat'))
        		LinemediaAutoStat::addTecdocGroupsView($arResult);
        	
        	/*
	         * Костыль для передачи информации о выбранном автомобиле на другие страницы
	         */
	        $_SESSION['tecdoc_catalog'][$detail['articleId']] = array(
	            'brand' 		=> array('id' => $arVariables['BRAND'], 		'title' => $arResult['brand_title']),
	            'model' 		=> array('id' => $arVariables['MODEL'], 		'title' => $arResult['model_title']),
	            'modification' 	=> array('id' => $arVariables['CAR_ID'], 		'title' => $arResult['modification_title']),
	            'group' 		=> array('id' => $arVariables['GROUP_ID'], 		'title' => $arResult['group_title']),
	        );
        	
        	$template = 'groups';

        }
        /************TEST********************************************************************************************************/

        /*$groups_active = array();
         $args = array('brand_id' => $arVariables['BRAND'], 'model_id' => $arVariables['MODEL'], 'type_id' => $arVariables['CAR_ID'], 'group_ids' => array(), 'include_oem' => ($arParams['SHOW_ORIGINAL_ITEMS'] === 'Y'), 'include_info' => false);
        foreach($aGroupsRes['data']['groups'] AS $G) {
        $args['group_ids'][] = $G['assemblyGroupNodeId'];
        }


        try {
        $aDetRes = $api->query('getDetails2MultipleGroups', $args);
        } catch (Exception $e) {
        $arResult['ERROR'] = $e->GetMessage();
        $this->IncludeComponentTemplate('error');
        return;
        }

        _d($aDetRes);
        */



        /************END TEST********************************************************************************************************/


        $arResult['brand_id'] = (string) $arVariables['BRAND'];
        $arResult['model_id'] = (string) $arVariables['MODEL'];
        $arResult['modification_id'] = (string) $arVariables['CAR_ID'];

        $arResult['type'] = 'group';
        $arResult['parent_id'] = $arVariables['CAR_ID']; //$arParams['MODIFICATIONS_SET'];//

    /*
     * Присутствует выбор группы
     */
    } elseif ($arVariables['GROUP_ID'] != '') {
        $template = 'parts';

        $args = array('brand_id' => $arVariables['BRAND'], 'model_id' => $arVariables['MODEL'], 'modification_id' => $arVariables['CAR_ID'], 'type_id' => $arVariables['CAR_ID'], 'group_id' => $arVariables['GROUP_ID'], 'include_oem' => ($arParams['SHOW_ORIGINAL_ITEMS'] === 'Y'), 'include_info' => ($arParams['INCLUDE_PARTS_IMAGES'] == 'Y'));

        try {
            $aDetRes = $api->query('getGroupParts3', $args);
        } catch (Exception $e) {
            $arResult['ERROR'] = $e->GetMessage();
            $this->IncludeComponentTemplate('error');
            return;
        }


        /*
         * 404
         */
        if (count($aDetRes['data']['parts']) < 1 && !$arResult['EDIT_MODE']) {
            CHTTP::SetStatus('404 Not Found');
            @define('ERROR_404', 'Y');
            $arResult['error']['group_id'] = $arVariables['GROUP_ID'];
            $arResult['error']['type_id'] = $arVariables['CAR_ID'];
            $this->IncludeComponentTemplate('404');
            return;
        }

        $arResult['DETAILS'] = $aDetRes['data']['parts'];
// var_dump($arResult['DETAILS']);

        $arResult['brand_title'] = (string) $aDetRes['data']['brand']['title'];
        $arResult['model_title'] = (string) $aDetRes['data']['model']['title'];
        $arResult['modification_title'] = (string) $aDetRes['data']['modification']['title'];
        $arResult['group_title'] = (string) $aDetRes['data']['group']['title'];

        if(CModule::IncludeModule('linemedia.auto')) {
	        $search = new LinemediaAutoSearchSimple();
	
	        $where = array('#ARTICLE_ID#','#BRAND_ID#');
	        foreach ($arResult['DETAILS'] as $key => $detail) {
	
	
	            $findurl = LinemediaAutoTecdocUrlHelper::getPartUrl(array(
	                'article' => $detail['articleNo'],
	                'brand_title' => $detail['brandName'],
	                'extra' => array(
	                    'gid' => $detail['genericArticleId'],
	                ),
	            ), $arParams['SEARCH_ARTICLE_URL']);
	
	            $detail_url = ($detail['articleId'] > 0 && $detail['articleLinkId'] > 0) ? str_replace(array('#ARTICLE_ID#','#ARTICLE_LINK_ID#'), array($detail['articleId'], $detail['articleLinkId']), $arParams['DETAIL_URL']) : null;
	
	
	            /*
	            * Для созданных вручную деталей тоже должна быть страница
	            */
	            if($detail['lm_mod_id'] == 'Y') {
		            $detail_url = str_replace(array('#ARTICLE_ID#','#ARTICLE_LINK_ID#'), array($detail['source_id'], LinemediaAutoApiModifications::LOCAL_ID_KEY), $arParams['DETAIL_URL']);
	            }
	
	
	            $arResult['DETAILS'][$key]['detail_url'] = $detail_url;
	            $arResult['DETAILS'][$key]['search_url'] = $findurl; // str_replace($where, $what, $arParams['SEARCH_URL']);
	
				/*
				 * Для хлебных крошек в поиске
				 */
	
	
				// Группа модель.
				/*if (!empty($arVariables['BRAND'])) {
					$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_brand_id=' . $arVariables['BRAND'];
					$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_brand=' . $arVariables['BRAND'];
					$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_folder=' . str_replace('/', '_', $arParams['SEF_FOLDER']);
	
					if (!empty($arVariables['MODEL'])) {
						$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_model_id=' . $arVariables['MODEL'];
	
						// Модификация.
						if (!empty($arVariables['CAR_ID'])) {
							$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_car_id=' . $arVariables['CAR_ID'];
	
							// Группа запчастей.
							if (!empty($arVariables['GROUP_ID'])) {
								$arResult['DETAILS'][$key]['search_url'] .= '&tecdoc_group_id=' . $arVariables['GROUP_ID'];
							}
						}
					}
				}*/
	
	            /*
	             * Поищем доступные варианты в локальной базе
	             */
	            $parts = (array) $search->searchLocalDatabaseForPart(array(
	                'article' => $detail['articleNo'],
	                'brand_title' => $detail['brandName']
	            ), true);
	
	
	            /*
	             * Скрываем товары, которых нет в локальной базе
	             */
	            if ($arParams['HIDE_UNAVAILABLE'] == 'Y' && count($parts) == 0) {
	                unset($arResult['DETAILS'][$key]);
	                continue;
	            }
	
	            $arResult['DETAILS'][$key]['PARTS'] = $parts;
	
	            foreach ($parts as $part) {
	                $part_obj = new LinemediaAutoPart($part['id']);

                    /*
                     * Проверим доступность поставщика
                     */
                    $supplier_id = $part_obj->get('supplier_id');
                    if(LinemediaAutoSupplier::isSupplierAccesRight($supplier_id, 'supplier_id')) {
                        /*
                         * Посчитаем цену товара
                         */
                        $price = new LinemediaAutoPrice($part_obj);
                        $price_calc = $price->calculate();
                        $formatted = CurrencyFormat($price_calc, $price->getCurrency());

                        $arResult['DETAILS'][$key]['PRICES'][$price_calc] = $formatted;
                    }
	            }
	
	            foreach ($arResult['DETAILS'] as $key => $part) {
	                if (count($part['PRICES']) > 0) {
	                    $arResult['DETAILS'][$key]['min_price'] = min(array_keys($part['PRICES']));
	                    $arResult['DETAILS'][$key]['max_price'] = max(array_keys($part['PRICES']));
	                }
	            }
	
	
	            /*
	             * Костыль для передачи информации о выбранном автомобиле на другие страницы
	             */
	            $_SESSION['tecdoc_catalog'][$detail['articleId']] = array(
	                'brand' 		=> array('id' => $arVariables['BRAND'], 		'title' => $arResult['brand_title']),
	                'model' 		=> array('id' => $arVariables['MODEL'], 		'title' => $arResult['model_title']),
	                'modification' 	=> array('id' => $arVariables['CAR_ID'], 		'title' => $arResult['modification_title']),
	                'group' 		=> array('id' => $arVariables['GROUP_ID'], 		'title' => $arResult['group_title']),
	            );
	
	
	            $literalArticle = array();
	
	            foreach ($arResult['DETAILS'] as $part) {
	                if (!preg_match('/^[\d]+$/', $part['articleId'])) {
	                	$literalArticle[] = $part['articleId'];
	                }
	            }
	
	            array_unique($literalArticle, SORT_ASC);
	
	            $arParams['literalArticle'] = $literalArticle;
	
	        }
	    } // if linemedia.auto

        /*
         * Защита от сканирования
         */
        if (!$USER->IsAuthorized() && $arParams['ANTI_BOTS'] == 'Y') {
            foreach ($arResult['DETAILS'] as $i => $part) {

                $arResult['DETAILS'][$i]['articleNo'] = str_pad(substr($part['articleNo'], 0, 2), strlen($part['articleNo']), '*');
                $arResult['DETAILS'][$i]['detail_url'] = '?SHOW_AUTH_FORM=1';
                $arResult['DETAILS'][$i]['search_url']= '?SHOW_AUTH_FORM=1';
                $arResult['HIDE_ARTICLES'] = true;

                if ($arResult['DETAILS'][$i]['articleId']) {
                    unset($arResult['DETAILS'][$i]['articleId']);
                }
                
            }
        }

        /*
         * Применим сортировку
         */
        usort($arResult['DETAILS'], 'tecdocItemsSort');


        $arResult['brand_id'] = (string) $arVariables['BRAND'];
        $arResult['model_id'] = (string) $arVariables['MODEL'];
        $arResult['modification_id'] = (string) $arVariables['CAR_ID'];
        $arResult['group_id'] = (string) $arVariables['GROUP_ID'];

        $arResult['type'] = 'part';
        $arResult['parent_id'] = $arResult['modification_id'].':'.$arResult['group_id'];//$arParams['MODIFICATIONS_SET'];//

    }

    if (!isset($template)) {
        $template = 'error';
    }

    /*
     * Форма авторизации для незарегистрированных пользователей
     */
    if (!$USER->IsAuthorized() && isset($_REQUEST['SHOW_AUTH_FORM'])) {
        $APPLICATION->AuthForm(GetMessage('LM_AUTO_TECDOC_NEED_AUTH'));
    } else {
        /*
         * Подключение шаблона
         */

   		$this->IncludeComponentTemplate($template);
    }

//}


/*
 *  Хлебные крошки.
 */
if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
    $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_TITLE_CATALOG'));
    $APPLICATION->AddChainItem(GetMessage('LM_AUTOPORTAL_ALL_MARKS'), $arParams['SEF_FOLDER']);


    /*
     * Присутствует выбор бренда
     */
    if ($arVariables['PART'] > 0) {
        $APPLICATION->AddChainItem($arResult['DATA']['Name'].' '.$arResult['DATA']['Article'], null);
        $APPLICATION->SetTitle($arResult['DATA']['Name'].' '.$arResult['DATA']['Article']);
    }
    
    
    /*
     * Присутствует выбор бренда
     */
    if ($arVariables['BRAND'] != '') {
        $APPLICATION->AddChainItem($arResult['brand_title'], $arParams['SEF_FOLDER'].$arVariables['BRAND'].'/');
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$arResult['brand_title']);
    }


    /*
     * Присутствует выбор модели
     */
    if ($arVariables['MODEL_GROUP'] != '') {
        $APPLICATION->AddChainItem($arVariables['MODEL_GROUP'], $arParams['SEF_FOLDER'].$arVariables['BRAND'].'/?model_group='.$arVariables['MODEL_GROUP']);
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$arResult['brand_title'].' '.$arVariables['MODEL_GROUP']);
    }


    /*
     * Присутствует выбор модели
     */
    if ($arVariables['MODEL'] != '') {
        $APPLICATION->AddChainItem($arResult['model_title'], $arParams['SEF_FOLDER'].$arVariables['BRAND'].'/'.$arVariables['MODEL'].'/');
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$arResult['brand_title'].' '.$arResult['model_title']);
    }


    /*
     * Присутствует выбор модификации
     */
    if ($arVariables['CAR_ID'] != '') {
        $APPLICATION->AddChainItem($arResult['modification_title'], $arParams['SEF_FOLDER'].$arVariables['BRAND'].'/'.$arVariables['MODEL'].'/'.$arVariables['CAR_ID'].'/');
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$arResult['brand_title'].' '.$arResult['model_title'].' '.$arResult['modification_title']);
    }


    /*
     * Присутствует выбор группы
     */
    if ($arVariables['GROUP_ID'] != '') {
		$arResult['group_title'] = (GetMessage("LM_AUTO_GROUP_" . $arVariables['GROUP_ID']))? GetMessage("LM_AUTO_GROUP_" . $arVariables['GROUP_ID']) : $arResult['group_title'];

		$APPLICATION->AddChainItem($arResult['group_title'], $arParams['SEF_FOLDER'].$arVariables['BRAND'].'/'.$arVariables['MODEL'].'/'.$arVariables['CAR_ID'].'/'.$arVariables['GROUP_ID'].'/');
        $APPLICATION->SetTitle($arResult['group_title'].' '.$arResult['brand_title'].' '.$arResult['model_title'].' '.$arResult['modification_title']);
    }

}





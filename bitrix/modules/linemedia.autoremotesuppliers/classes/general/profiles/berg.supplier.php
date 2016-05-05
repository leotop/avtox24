<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Autodoc Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * doc: http://portal.linemedia.ru/company/personal/user/7/tasks/task/view/2556/
 * Интерфейс удалённого поставщика
 * Class BergRemoteSupplier
 */
class BergRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'Berg';
    /**
     * @var null
     */
    private $brands = null;
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'https://api.berg.ru'; //
    /**
     * Искать ли аналоги?
     * Настраивается в настройках модуля
     * @var bool
     */
    protected $search_analogs = false;


    /**
     * Создадим объект
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Инициализация.
     */
    public function init()
    {
        $this->browser->setBaseUrl($this->url);
        $this->browser->setReferer($this->url);

        $this->search_analogs = '' != $this->profile_options['USE_ANALOGS'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'berg_USE_ANALOGS');
    }


    /**
     * Авторизация
     */
    public function login()
    {
        /*
         * Логин объединяется с поиском для ускорения загрузки страницы (один запрос вместо двух)
         */
    }

    /**
     * Получаем внутренний список брендов поставщика
     * @throws Exception
     */
    private function getBrands()
    {
        $lmCache = LinemediaAutoSimpleCache::create(array('path' => '/lm_auto/remote_suppliers/'));
        $life_time = 60*60*24;
        $cache_id = 'berg-makers';
        if ($cachedResult = $lmCache->getData($cache_id, $life_time)) {
            $this->brands = $cachedResult;
        } else {

            $this->browser->setParam(CURLOPT_PORT , 443);
            $this->browser->setParam(CURLOPT_SSL_VERIFYHOST, 0);
            $this->browser->setParam(CURLOPT_SSL_VERIFYPEER, 0);

            if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                $this->browser->setParam(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }


            $key = $this->profile_options['KEY'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'berg_KEY');
            $url = '/references/brands.xml?key='.$key;
            try{
                $page = $this->browser->get($url);
            } catch (Exception $ex) {
                $query_info = $this->browser->getLastQueryInfo();
                if($query_info['http_code'] == 401)
                    throw new Exception('Incorrect key', LM_AUTO_DEBUG_USER_ERROR);
                if ($query_info['http_code'] == 300) { /*HTTP 300 -- каталоги, а не ошибка!*/
                    $this->response_type = 'catalogs';
                    $page = $this->browser->getLastResponse();
                } else {
                    throw $ex;
                }
            }//catch

            try {
                $xml = simplexml_load_string($page);
            } catch (Exception $e) {
                throw new Exception ('Error parsing Berg brands XML. ' . $e->GetMessage());
            }

            $brands = array();
            foreach ($xml->brands as $brand) {
                $brands[ strtoupper((string) $brand->attributes()->name)] = (string) $brand->attributes()->id;
            }

            $this->brands = $brands;

            $lmCache->setData($cache_id, $brands);
        }
    }

    /**
     * Поиск
     * @throws Exception
     */
    public function search()
    {


        $query  = $this->query;
        $key    = $this->profile_options['KEY'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'berg_KEY');

        /*
         * У нас есть уточнение, т.е. это точно запрос не на каталоги
         */
        $params = array(
            'key'       => $key,
            'analogs'   => intval($this->search_analogs)
        );

        $params['items'][0]['resource_article'] = $query;

        if ($this->brand_title != '') {

            $berg_brand_id = $this->extra['bg_bid'];
            $brand_title = $this->brand_title;

            /*
             * Если нет номера бренда, то пробуем получить его из названия.
             */
            if ((!isset($this->extra['bg_bid']) || $this->extra['bg_bid'] <= 0) && !empty($this->brand_title)) {
                $this->getBrands();
                $berg_brand_id = $this->brands[strtoupper($this->brand_title)];
            }


            if ($berg_brand_id) {
                $params['items'][0]['brand_id'] = $berg_brand_id;
            } else if ($brand_title) {
                $params['items'][0]['brand_name'] = $brand_title;
            }

        } else {
            // Уточнения нет, просто ищем
            $params['items'][0]['resource_article'] = $query;
        }




        $url = '/ordering/get_stock.xml?'.http_build_query($params, '', '&');

        /*
         * Настраиваемся на работу по SSL
         */
        $this->browser->setBaseUrl($this->url);
        $this->browser->setParam(CURLOPT_PORT, 443);
        $this->browser->setParam(CURLOPT_SSL_VERIFYHOST, 0);
        $this->browser->setParam(CURLOPT_SSL_VERIFYPEER, 0);
        
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $this->browser->setParam(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        }

        try {
            $page = $this->browser->get($url);
        } catch (Exception $e) {
            $query_info = $this->browser->getLastQueryInfo();


            if($query_info['http_code'] == 401)
                throw new Exception('Incorrect key', LM_AUTO_DEBUG_USER_ERROR);
            if ($query_info['http_code'] == 300) { /*HTTP 300 -- каталоги, а не ошибка!*/
                $this->response_type = 'catalogs';
                $page = $this->browser->getLastResponse();
            } else {
                throw $e;
            }
        }

        try {
            $xml = simplexml_load_string($page);
        } catch (Exception $e) {
            throw new Exception ('parse XML. ' . $e->GetMessage() . ' - ' . $page);
        }




        /*
         * Если артикул в нескольких каталогах, то нам вернут предупреждение,
         * по нему и будем ориентироваться, что нам вернули каталоги.
         */
        if ($xml->warnings && (string)$xml->warnings->warning->attributes()->code == 'WARN_ARTICLE_IS_AMBIGUOUS') {
            $this->response_type = 'catalogs';
        } else {
            $this->response_type = 'parts';
        }

        $this->parts = array();
        $this->catalogs = array();

        /*
         * Если вернулись каталоги, то они вернулись все: и пустые, и непустые.
         * Поэтому сначала складируем каталоги в all_cats, параллельно собирая
         * массив параметров params для второго запроса по поводу наличия заявленных железок.
         * В ответе на второй запрос в source_idx будет числовой индекс того каталога, который нашёлся.
         * Если не нашлось на сочетание бренд-артикул, то индекса этой пары не будет в ответе.
         */
        if ($this->response_type == 'catalogs') {
            $all_cats = array();
            $params = array(
                'key'       => $key,
                'analogs'   => intval($this->search_analogs)
            );
            foreach ($xml->resources->resource as $cat) {
                $cat = get_object_vars($cat);
                $cat['brand'] = get_object_vars($cat['brand']);

                $all_cats[(string)$cat['brand']['@attributes']['id']]= array(
                    'article' => (string)$cat['@attributes']['article'],
                    'brand_title' => (string)$cat['brand']['@attributes']['name'],
                    'title'=>(string)$cat['@attributes']['name'],
                    'extra' => array(
                        'bg_bid' => (string)$cat['brand']['@attributes']['id'],
                    )
                );
            }

			foreach ($xml->resources->resource as $cat) {
				$cat = get_object_vars($cat);
				$cat['brand'] = get_object_vars($cat['brand']);

				$params['items'][0]= array(
					'resource_article' => (string)$cat['@attributes']['article'],
					'brand_id' => (string)$cat['brand']['@attributes']['id']
				);

				$url = '/ordering/get_stock.xml?'.http_build_query($params, '', '&');

				try {
					$page = $this->browser->get($url);
				} catch (Exception $e) {
					$query_info = $this->browser->getLastQueryInfo();
					if($query_info['http_code'] == 401)
						throw new Exception('Incorrect key', LM_AUTO_DEBUG_USER_ERROR);
					throw $e;
				}

				try {
					$xml = simplexml_load_string($page);
				} catch (Exception $e) {
					throw new Exception ('parse XML2 ' . $e->GetMessage() . ' - ' . $page);
				}

				$data = (array)$xml->resources;
				if ( count($xml->resources->resource) == 1) {
					$data['resource'] = array(0=>$data['resource']);
				}

				if (!empty($data)) {
					$cat = $all_cats[ (string)$cat['brand']['@attributes']['id'] ];
					$this->catalogs[ $cat['brand_title'] ] = $cat;
					/*
					 * Если у нас словоформы, то на запрос по бренду и артикулу
					 * мы уже не попадем, а значит нужно здесь получить аналоги по каждому
					 * каталогу, т.к. по всем сразу нельзя
					 */
					$this->addCatalogParts($xml);
				}
            }
        }

        if($this->response_type == 'parts') {

            $data = (array) $xml->resources;
            $n_parts = 0;
            /*
             * Два варианта того, как придут данные. в одном случае в $xml->resources->resource окажется
             * просто элемент с предложениями, а во втором - массив элементов с предложениями.
             * Пример первого случая - артикул 826841.
             */
            if ( count($xml->resources->resource) == 1) {
                $data['resource'] = array(0=>$data['resource']);
            }

            foreach ($data['resource'] as $part) {
                foreach ($part->offers->offer as $offer) {

                    
                    // Уникальная extra для детали.
                    $extra = $this->extra;
                    $offer = get_object_vars($offer);
                    
                    $price                 = floatval(str_replace(array(' ', ','), array('', '.') , $offer["@attributes"]['price']));
                    $brand_title           = strval($part->brand->attributes()->name);
                    $article               = LinemediaAutoPartsHelper::clearArticle(strval($part->attributes()->article));
                    $title                 = strval($part->attributes()->name);
                    $weight                = 0;
                    $quantity              = intval($offer["@attributes"]['quantity']);
                    $delivery_time         = intval($offer["@attributes"]['assured_period']) * 24; // нам вернули дни. делаем из них часы
                    $multiplication_factor = max(1, intval($offer["@attributes"]['multiplication_factor']));
                    $date_update           = null;
                    $extra['bg_bid']     = strval($part->brand->attributes()->id);
                    $extra['hash'] = implode(':', array(
                        intval($part->attributes()->id), $price, $offer['warehouse']['id']
                    ));

                    /*
                     * Схороним внутренние номера детали и склада Берга, чтобы потом точно найти нужную железку.
                     */
                    $extra['bg_oid'] = $offer['@attributes']['id'];
                    $extra['bg_wid'] = strval($offer['warehouse']['id']);

                    /**
                        Тип склада: 1 -- склад текущего отделения, 2 -- склад центрального отделения БЕРГ, 3 -- дополнительный склад, 4 -- рейс
                        Не факт, что оно надо, но если
                    */
                    $extra['bg_wht'] = strval($offer['warehouse']['type']);

                    /*
                     * Нашли, но бренд не тот -- пропускаем.
                     */
                    if (!empty($this->brand_title) && strtoupper($brand_title) != strtoupper($this->brand_title) && intval($this->search_analogs) < 1) {
                        continue;
                    }

                    /*
                     * Стандартная процедура: если артикул равен искомому, то это оригинал, иначе это аналог
                     */
                    if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                        $this->catalogs[ $brand_title ] = array(
                            'article'      => $article,
                            'brand_title'  => $brand_title,
                            'title'        => $title,
                            'source'       => self::$title,
                        );
                        $key = 'analog_type_N';
                    } else {
                        if (intval($this->search_analogs) < 1) continue;
                        $key = 'analog_type_4';
                    }

                    ++$n_parts;

                    $this->parts[ $key ][] = array(
                        'id'                => 'berg',
                        'article'           => $article,
                        'brand_title'       => $brand_title,
                        'title'             => $title.($multiplication_factor > 1 ? " \n(".GetMessage('ORDER_MULT').":".$multiplication_factor.')':''),
                        'price'             => $price,
                        'weight'            => $weight,
                        'quantity'          => $quantity,
                        'delivery_time'     => $delivery_time,
                        'date_update'       => $date_update,
                        'data-source'       => self::$title,
                        'extra'             => $extra,
                        'multiplication_factor'  => $multiplication_factor
                    );
                }
            }

            if (count($this->parts) == 0) {
            	$this->brand_title = null;
            }
        }

        /*
         * Если нет бренда, то могут вернуться или каталоги или детали.
         */
        $this->catalogs = array_values($this->catalogs);
        if (empty($this->brand_title)) {
            $this->response_type = count($this->catalogs) > 1 ? 'catalogs' : 'parts';
        }

		if (count($this->catalogs) < 1 && $n_parts == 0) {
            $this->response_type = '404';
        }
        return;

    }

    /**
     * addCatalogParts
     * @param $xml
     */
    public function addCatalogParts($xml)
	{
		$data = (array) $xml->resources;
		$n_parts = 0;
		/*
		 * Два варианта того, как придут данные. в одном случае в $xml->resources->resource окажется
		 * просто элемент с предложениями, а во втором - массив элементов с предложениями.
		 * Пример первого случая - артикул 826841.
		 */
		if ( count($xml->resources->resource) == 1) {
			$data['resource'] = array(0=>$data['resource']);
		}

		foreach ($data['resource'] as $part) {
			foreach ($part->offers->offer as $offer) {
				// Уникальная extra для детали.
				$extra = $this->extra;
				$offer = get_object_vars($offer);

				$price                 = floatval(str_replace(array(' ', ','), array('', '.') , $offer["@attributes"]['price']));
				$brand_title           = strval($part->brand->attributes()->name);
				$article               = LinemediaAutoPartsHelper::clearArticle(strval($part->attributes()->article));
				$title                 = strval($part->attributes()->name);
				$weight                = 0;
				$quantity              = intval($offer["@attributes"]['quantity']);
				$delivery_time         = intval($offer["@attributes"]['assured_period']) * 24; // нам вернули дни. делаем из них часы
				$multiplication_factor = max(1, intval($offer["@attributes"]['multiplication_factor']));
				$date_update           = null;
				$extra['bg_bid']     = strval($part->brand->attributes()->id);
				$extra['hash'] = implode(':', array(
					intval($part->attributes()->id), $price, $offer['warehouse']['id']
				));

				/*
				 * Схороним внутренние номера детали и склада Берга, чтобы потом точно найти нужную железку.
				 */
				$extra['bg_oid'] = $offer['@attributes']['id'];
				$extra['bg_wid'] = strval($offer['warehouse']['id']);

				/**
				Тип склада: 1 -- склад текущего отделения, 2 -- склад центрального отделения БЕРГ, 3 -- дополнительный склад, 4 -- рейс
				Не факт, что оно надо, но если
				 */
				$extra['bg_wht'] = strval($offer['warehouse']['type']);

				/*
				 * Нашли, но бренд не тот -- пропускаем.
				 */
				if (!empty($this->brand_title) && strtoupper($brand_title) != strtoupper($this->brand_title) && intval($this->search_analogs) < 1) {
					continue;
				}

				/*
				 * Стандартная процедура: если артикул равен искомому, то это оригинал, иначе это аналог
				 */
				if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
					$key = 'analog_type_N';
				} else {
					if (intval($this->search_analogs) < 1) continue;
					$key = 'analog_type_4';
				}

				++$n_parts;

				$this->parts[ $key ][] = array(
					'id'                => 'berg',
					'article'           => $article,
					'brand_title'       => $brand_title,
					'title'             => $title.($multiplication_factor > 1 ? " \n(Кратность заказа:".$multiplication_factor.')':''),
					'price'             => $price,
					'weight'            => $weight,
					'quantity'          => $quantity,
					'delivery_time'     => $delivery_time,
					'date_update'       => $date_update,
					'data-source'       => self::$title,
					'extra'             => $extra,
					'multiplication_factor'  => $multiplication_factor
				);
			}
		}
		return;
	}

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том,
     * что эта запчасть данного поставщика и пришла из поиска.
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
    {
        $hash = $data['extra']['hash'];

        $this->query = $data['article'];
        $this->brand_title = $data['brand_title'];
        $this->extra = $data['extra'];
        $this->init();

        // Выполнить в любом случае для логина и получения id user.
        $this->search();

        /*
         * Найдём именно эту деталь
         */
        foreach ($this->parts as $group => $parts) {
            foreach ($parts as $part) {
                if ($part['extra']['hash'] == $hash && $part['extra']['bg_wid'] == $this->extra['bg_wid']) {
                    return $part;
                }
            }
        }
        throw new Exception(self::$title.': '.'Remote part not found');
    }

    /**
     * Кроме ключа ничего не надо для доступа. Ну и плюс настраиваем, спрашивать ли аналоги у Берга
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'KEY' => array(
                'title' => 'key',
                'type'  => 'string',
            ),
            'USE_ANALOGS'=>array(
                'title' => GetMessage('BERG_USE_ANALOGS'),
                'type'  => 'checkbox',
                'default' => false,
            )
        );
    }

}

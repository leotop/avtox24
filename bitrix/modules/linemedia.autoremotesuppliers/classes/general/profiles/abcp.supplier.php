<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote AUTOTrade Supplier
 *
 * @author  Linemedia
 * @since   17/02/2014
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * interface of remote supplier
 * Class AutoTradeRemoteSupplier
 */
class AbcpRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * appellation and URL of supplier
     * @var string
     */
    public static $title = 'Abcp';
    /**
     * @var string
     */
    public $url = 'http://abcp.ru';
    /**
     * @var string
     */
    private $api = '';

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

    }


    /**
     * Авторизация.
     */
    public function login()
    {

    }

    /**
     * search
     * @throws Exception
     */
    public function search() {

        $this->api = $this->profile_options['URL'];

        $this->browser->setBaseUrl($this->api);
        $this->browser->setReferer($this->api);

        $login    = rawurlencode($this->profile_options['LOGIN']);
        $password = rawurlencode(md5($this->profile_options['PASSWORD']));

        $article = rawurlencode($this->query);

        if ($this->brand_title != '') {

            $this->response_type = 'parts';
            $brand_title = rawurlencode($this->brand_title);
            $url = "/search/articles?userlogin=$login&userpsw=$password&number=$article&brand=$brand_title";

        } else {
            $this->response_type = 'catalogs';
            // уточнения нет, просто ищем
            $url = "/search/brands/?userlogin=$login&userpsw=$password&number=$article";
        }

        //	print_r($this->api.$url);
        $info = $this->browser->get($url);

        $info = json_decode($info, 1);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception(json_last_error());
        }

        $parts = $info;

        if (!is_array($parts)) {
            $this->response_type = '404';
            return;
        }

        /*
         * Только 1 каталог, сразу получим детали с ценой
         */
        if(count($parts) == 1) {
            $part = array_shift($parts);
            $brand_title = rawurlencode($part['brand']);

            $this->response_type = 'parts';

            $url = "/search/articles?userlogin=$login&userpsw=$password&number=$article&brand=$brand_title";

            $info = $this->browser->get($url);

            $info = json_decode($info, 1);

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception(json_last_error());
            }

            $parts = $info;

            if (!is_array($parts)) {
                $this->response_type = '404';
                return;
            }
        }

        foreach($parts as $part) {

            //	$analogType == 'N'
            /* if ($this->brand_title != '' && $analogType == 'N' && (strtoupper($this->brand_title) != strtoupper($part['brand']))) {
                continue;
            } */

            if($this->brand_title == '') {
                if(LinemediaAutoPartsHelper::clearArticle($part['number']) == $this->query) {
                    $analogType = 'N';
                } else {
                    $analogType = '0';
                }
            }
            else {
                if((LinemediaAutoPartsHelper::clearArticle($part['number']) == $this->query) && (strtoupper($this->brand_title) == strtoupper($part['brand'])))
                    $analogType = 'N';
                else
                    $analogType = '0';
            }


            /*
             * Get stock info
             */
            $quantity = 0;
            $deliveryTime = $part['deliveryPeriod'];

            switch($part['availability']) {
                case -10:
                    $quantity = 'под заказ';
                    break;
                case -1:
                case -2:
                case -3:
                    $quantity = 1;
                default:
                    $quantity = $part['availability'];
                    break;
            }


			/* if($this->response_type == 'parts') {
				if (!$quantity) {
					continue;
				} 

				if (!$part['price']) {
					continue;
				} 
			} */

			$this->parts["analog_type_$analogType"][] = array(
				'id'                => 'abcp',
				'article'           => LinemediaAutoPartsHelper::clearArticle($part['number']),
				'brand_title'       => strtoupper($part['brand']),
				'title'             => $part['description'],
				'price'             => $part['price'],
				'quantity'          => $quantity,
				'delivery_time'     => intval($deliveryTime), // в часах
				'date_update'       => '',
				'data-source'       => self::$title,
				'extra'				=> array(
					'id'			   => $part['distributorId'],
					'article_original' => $part['number'],
					'hash'			   => md5($part['distributorId'] . $part['number'] . $part['brand'])
				)
			);
			
			if ($analogType == 'N') {
				$this->catalogs[strtoupper($part['brand'])] = array(
					'article' => LinemediaAutoPartsHelper::clearArticle($part['number']),
					'brand_title' => strtoupper($part['brand']),
					'title' => $part['description'],
				);
			}
			
		}
		
		/*
		 * Set response code
		 */
		if (count($this->catalogs) == 1 || $this->brand_title != '') {
			$this->response_type = 'parts';
		} elseif (count($this->parts) == 0 && $this->brand_title != '' || count($this->catalogs) == 0) {
			$this->response_type = '404';
		} else {
			$this->response_type = 'catalogs';
		}
			
    }

    /**
     *  add detail to cart
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
	{
		$hash = $data['extra']['hash'];

		$this->query = $data['extra']['article_original'];
		$this->brand_title = $data['brand_title'];

		$this->init();

		// Выполнить в любом случае для логина и получения id user
		$this->search();
		/*
		 * Найдём именно эту деталь
		 */
		foreach ($this->parts as $group => $parts) {
			foreach ($parts as $part) {
				if ($part['extra']['hash'] == $hash) {
					return $part;
				}
			}
		}

		throw new Exception(self::$title.': '.'Remote part not found');
    }


    /**
     * Получение конфигурационных данных.
     * @return array
     */
    public function getConfigVars() {

        return array(
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            ),
            'URL' => array(
                'title' => GetMessage('URL'),
                'type'  => 'string',
            ),
        );
    }

}


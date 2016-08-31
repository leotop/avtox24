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
class AutoTradeRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * appellation and URL of supplier
     * @var string
     */
    public static $title = 'AutoTrade';
    /**
     * @var string
     */
    public $url = 'http://autotrade.su';
    /**
     * @var string
     */
    private $api = 'https://api2.autotrade.su';
    /**
     * @var string
     */
    private static $authorizedString = '1>6)/MI~{J';

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
		$this->browser->setBaseUrl($this->api);
		$this->browser->setReferer($this->api);
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

		$authKey = md5((string) $this->profile_options['LOGIN'].md5((string) $this->profile_options['PASSWORD']).self::$authorizedString);

		$string = '/?json&data={"auth_key":"'.$authKey.'","method":"GetItemsByQuery","params":{"q":"'.$this->query.'","strict":1,"cross":1,"replace":1,"with_delivery":1,"with_stocks_and_prices":1}}';


		//$page = $this->browser->get("/?json&data=".json_encode($data));
		$info = $this->browser->get($string);

		$info = json_decode($info, 1);

		if (json_last_error() != JSON_ERROR_NONE) {
			throw new Exception(json_last_error());
		}

		$parts = $info['items'];

		if (!is_array($parts)) {
			$this->response_type = '404';
			return;
		}


		foreach($parts as $part) {

			/*
			 * Get part type
			 */
			switch($part['type']) {
				case 'cross':
					$analogType = 0;
					break;
				case 'replace':
					$analogType = 4;
					break;
				default:
					$analogType = 'N';
					break;
			}

			if ($this->brand_title != '' && $analogType == 'N' && strtoupper($this->brand_title) != strtoupper($part['brand_name'])) {
				continue;
			}

			/*
			 * Get stock info
			 */
			$quantity = 0;
			$deliveryTime = 0;
			if(is_array($part['stocks'])) {
				foreach($part['stocks'] as $stock) {
					if ($stock['quantity_packed'] != '-' || $stock['quantity_unpacked'] != '-' ) {
						$quantity = 1;

						if ($deliveryTime == 24)
							continue;

						$deliveryTime = strpos($stock['name'], GetMessage('KHABAROVSK')) ? 24 : 72;
					}
				}
			}


			if (!$quantity) {
				continue;
			}

			if (!$part['price']) {
				continue;
			}

			$this->parts["analog_type_$analogType"][] = array(
				'id'                => self::$title,
				'article'           => LinemediaAutoPartsHelper::clearArticle($part['article']),
				'brand_title'       => strtoupper($part['brand_name']),
				'title'             => $part['name'],
				'price'             => $part['price'],
				'quantity'          => $quantity,
				'delivery_time'     => intval($deliveryTime) * 24, // в часах
				'date_update'       => '',
				'data-source'       => self::$title,
				'currency'			=> $part['currency'],
				'extra'				=> array(
					'id'			   => $part['id'],
					'article_original' => $part['article'],
					'hash'			   => md5($part['id'] . $part['article'] . $part['brand_name'])
				)
			);

			if ($analogType == 'N') {
				$this->catalogs[strtoupper($part['brand_name'])] = array(
					'article' => LinemediaAutoPartsHelper::clearArticle($part['article']),
					'brand_title' => strtoupper($part['brand_name']),
					'title' => $part['name'],
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
        );
    }

}


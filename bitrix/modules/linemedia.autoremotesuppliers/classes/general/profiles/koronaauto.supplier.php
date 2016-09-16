<?php

/**
 * Linemedia Auto
 * Suppliers parser module
 * Linemedia Supplier
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * Class KoronaAutoRemoteSupplier
 */
class KoronaAutoRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * @var string
     */
    public static $title = 'KoronaAuto';
    /**
     * @var string
     */
    public $url = 'http://korona-auto.com';
    /**
     * @var string
     */
    public $url_search = '/api/search/';
    /**
     * @var string
     */
    public $url_detail_info = '/api/product/info/';
    /**
     * @var string
     */
    public $data_type = 'json';

    /**
     * Создадим объект
     */
    public function __construct()
	{
		parent::__construct();
	}

    /**
     * Инициализация.
     * @throws Exception
     */
    public function init()
	{
		$this->browser->setBaseUrl($this->url);
		$this->browser->setReferer($this->url);

		try {
			$parts = $this->browser->get($this->url_search . '?apiUid=' . $this->profile_options['apiUid'] . '&dataType=' . $this->data_type . '&q=M123132');
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


	/*
	 * Authorization
	 */
	public function login()
	{
		//no auth here
	}

    /**
     * General search
     * @throws Exception
     */
    public function search()
	{
		$query = urlencode($this->query);
        $warehouse = $this->profile_options['warehouse'] ?: GetMessage('Moscow');

		try {
			$search_parts = $this->browser->get($this->url_search . '?apiUid=' . $this->profile_options['apiUid'] . '&dataType=' . $this->data_type . '&q=' . $query);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}

		$search_parts = json_decode($search_parts, true);

		if ($search_parts['error']) {
			throw new Exception($search_parts['error']);
		}

		$search_parts = $search_parts['product'];

		if ($this->brand_title != '') {

			$parts = array();

			foreach ($search_parts as $search_part) {
				/*
				 * Compare search brand and part brand
				 */

				if (strcasecmp($search_part['producer'], $this->brand_title) == 0 ) {
					$search_part_info = $this->getPartInfo($search_part['id']);

					if (LinemediaAutoPartsHelper::clearArticle($search_part_info['factory_number']) == $this->query) {
						$key = 'analog_type_N';
					} else {
						$key = 'analog_type_4';
					}

					/*
					 * Get Moscow price and quantity
					 */
					$quantity = 0;
					$price = 0;

					foreach($search_part_info['stock'] as $stock) {
						if ($stock['warehouse']['name'] == $warehouse) {
							$quantity = $stock['warehouse']['quantity'];
						}
					}

					foreach($search_part_info['prices'] as $stock) {
						if ($stock['warehouse']['name'] == $warehouse &&
							$stock['warehouse']['currency'] == 'RUB') {
							$price = $stock['warehouse']['value'];
						}
					}

					if ($price == 0) {
						continue;
					}

					$parts[$key][] = array(
						'id'                => 'KoronaAuto',
						'article'           => LinemediaAutoPartsHelper::clearArticle($search_part_info['factory_number']),
						'brand_title'       => strtoupper($this->brand_title),
						'title'             => $search_part['name'],
						'price'             => $price,
						'quantity'          => $quantity,
						'delivery_time'     => '',
						'date_update'       => '',
						'data-source'       => self::$title,
						'weight'            => $search_part_info['weight']*1000,
						'extra'              => array(
							'part_id' => $search_part['id'],
							'article_original' => $search_part['factory_number'],
						)
					);
				}
			}

			$this->response_type = 'parts';
			$this->parts = $parts;

		} else {

			$catalogs = array();

			if(is_array($search_parts)) {
				foreach ($search_parts as $search_part) {
					$catalogs[strtoupper($search_part['producer'])] = array(
						'article'       => $this->query,
						'brand_title'   => $search_part['producer'],
						'title'         => $search_part['name']
					);
				}
			}


			$this->response_type = 'catalogs';
			$this->catalogs = $catalogs;

			if (count($catalogs) == 1) {
				$catalog = array_pop($catalogs);
				$this->brand_title = $catalog['brand_title'];
				$this->search();
			}
		}
	}

    /**
     * Get a part info
     * @param $part_id
     * @return array
     * @throws Exception
     */
    public function getPartInfo($part_id)
	{
		try {
			$part = $this->browser->get($this->url_detail_info . '?apiUid=' . $this->profile_options['apiUid'] . '&dataType=' . $this->data_type . '&id=' . $part_id);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}

		if ($part) {
			$part = json_decode($part, true);
		}

		if ($part['error']) {
			throw new Exception($part['error']);
		}

		return $part['product'] ? : array();
	}

    /**
     * Get a part data
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function getPartData($data)
	{
		$this->browser->setBaseUrl($this->url);
		$this->browser->setReferer($this->url);

		$part_id = $data['extra']['part_id'];


		$this->query = $data['article'];
		$this->brand_title = $data['brand_title'];

		$this->search();

		/*
		 * Find needed part
		 */
		foreach ($this->parts as $group => $parts) {
			foreach ($parts as $part) {
				if ($part['extra']['part_id'] == $part_id) {
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
	public function getConfigVars()
	{
		return array(
			'apiUid'    => array(
				'title' => GetMessage('apiUid'),
				'type'  => 'string',
			),
            'warehouse'   => array(
                'title'   => GetMessage('warehouse'),
                'default' => GetMessage('Moscow'),
                'type'    => 'string',
            )
		);
	}
}
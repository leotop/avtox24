<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote Ixora Supplier
 *
 * @author  Linemedia
 * @since   5/12/2013
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * Интерфейс удалённого поставщика
 * http://ws.auto-iksora.ru:83/searchdetails/searchdetails.asmx?op=FindDetails
 * Class IxoraRemoteSupplier
 */
class IxoraRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * description of supplier
     * @var string
     */
    public static $title = 'Ixora';
    /**
     * @var string
     */
    public $url = 'ws.ixora-auto.ru';

    /**
     * Создадим объект
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Инициализация.
     */
    public function init() {
        $this->browser->setBaseUrl($this->url);
    }


    /**
     * Авторизация
     */
    public function login() {
        /**
         * Логин объединяется с поиском для ускорения загрузки страницы (один запрос вместо двух)
         *
         */
    }

    /**
     * Поиск
     */
    public function search() {

        $url = "/soap/ApiService.asmx/Find?Number={$this->query}&Maker={$this->brand_title}&StockOnly=True&SubstFilter=All&AuthCode={$this->profile_options['AUTH']}";

        $catalogs_quantity = 0;

        try {
            $response = $this->browser->get($url);
        } catch (Exception $e) {
            $query_info = $this->browser->getLastQueryInfo();
            if($query_info['http_code'] == 403)
                throw new Exception('Incorrect password');
            throw new Exception('Last query info: <pre>' . print_r($query_info, true), LM_AUTO_DEBUG_USER_ERROR);
        }

        try {

            $set_of_detail = new SimpleXMLIterator($response);
        } catch (Exception $e) {
            throw new Exception('Ixora ended up with error: '.$e->getMessage());
        }

        $set_of_detail = (array) $set_of_detail;
        $set_of_detail = $set_of_detail['DetailInfo'];

        if ($this->brand_title == '') {

            foreach ($set_of_detail as $key => $part) {

                $expression = strcasecmp((string) $part->number, strval($this->query));
                if (!empty($expression)) {
                    continue;
                }

                $this->catalogs[(string) $part->maker] = array(
                    'article'     => (string) $part->number,
                    'brand_title' => (string) $part->maker,
                    'title'       => (string) $part->name,
                    'source'      => self::$title,
                    'extra'       => array(
                        'detail_number' => (string) $part->number,
                        'brand_title'   => (string) $part->maker,
                        'title'         => (string) $part->name,
                    )
                );
            }

            $catalogs_quantity = count($this->catalogs);
        }

        if ($catalogs_quantity == 1 || $this->brand_title != '') {

            foreach ($set_of_detail as $key => $part) {

                if ($this->brand_title && strcasecmp((string) $part->maker, $this->brand_title) == 0 && strcasecmp((string) $part->number, $this->query) == 0 ||
                    !$this->brand_title && strcasecmp((string) $part->number, $this->query) == 0) {
                    $group = 'N';
                } else {
                    $group = '0';
                }

                $regionname =  preg_replace('/[\s-]/', '', (string) $part->region);
                $orderrefernce = str_replace('-', '', (string) $part->orderreference);
                $quantity = preg_replace('/[^0-9]/', '', (string) $part->quantity);
                $article = LinemediaAutoPartsHelper::clearArticle((string) $part->number);

                // заполняем поля
                $this->parts['analog_type_'.$group][] = array(
                    'id' => self::$title,
                    'article'               => $article,
                    'brand_title'           => (string) $part->maker,
                    'title'                 => (string) $part->name,
                    'price'                 => (string) $part->price,
                    'weight'                => '-',
                    'quantity'              =>  $quantity,
                    'delivery_time'         => (string) $part->days * 24,
                    'modified'              => '-',
                    'data-source'           => self::$title,
                    'extra'                 => array(
                        'manufacture' => (string) $part->maker,
                        'regionname'  => $regionname,
                        'orderrefernce' => $orderrefernce,
                        'hash'    => md5((string) $part->maker . (string) $part->price . (string) $part->name . $article . $quantity . (string) $part->days)
                    )
                );
            }
        }

        if ($catalogs_quantity == 1 || $this->brand_title != '') {
            $this->response_type = 'parts';
        } elseif (count($this->parts) == 0 &&  $catalogs_quantity == 1) {
            $this->response_type = '404';
        } else {
            $this->response_type = 'catalogs';
        }

    }

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска
     * @param $data
     * @return array
     */
    public function getPartData($data) {

        $this->query = $data['article'];
        $this->brand_title = $data['brand_title'];

        $this->init();
        $this->search();

        foreach ($this->parts as $group) {
            foreach($group as $part) {
                if ($data['extra']['hash'] == $part['extra']['hash']) {
                    return $part;
                }
            }
        }

        return false;
    }

    /**
     * Кроме ключа ничего не надо для доступа. Ну и плюс настраиваем, спрашивать ли аналоги у Берга
     * @return array
     */
    public function getConfigVars() {

        return array(
            'AUTH' => array(
                'title' => GetMessage('AUTH'),
                'type'  => 'string',
            ),
        );
    }

}


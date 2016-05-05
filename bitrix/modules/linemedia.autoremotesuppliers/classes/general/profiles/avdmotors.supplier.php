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
 * Интерфейс удалённого поставщика
 * http://avdmotors.ru/webservise/
 * Class AvdmotorsRemoteSupplier
 */
class AvdmotorsRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    const URL = 'http://www.avdmotors.ru/ws/?';
    /**
     * @var string
     */
    public static $title = 'AvdMotors';
    /**
     * public - для вывода в настройках
     * @var string
     */
    public $url = 'http://www.avdmotors.ru/'; //


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
        // Используется ID клиента и его IP-адрес в системе.
    }

    /**
     * Поиск.
     * @throws Exception
     */
    public function search()
    {
        $query = urlencode($this->query);

        // Номер клиента. проставляется при создании объекта поставщика из соотв.профиля в инфоблоке.
        $client = $this->profile_options['LOGIN'];//COption::GetOptionString('linemedia.autoremotesuppliers', 'avdmotors_LOGIN');

        // Формирование URL-запроса к AvdMotors.
        $url = self::URL.'&action=show&client='.$client.'&number='.$query;

        // Получение данных.
        $page = $this->browser->get($url);
        
        
        try {
            $xml = simplexml_load_string($page);
        } catch (Exception $e) {
            throw new Exception ('Error parsing AVDMOTORS XML. ' . $e->GetMessage() . ' - ' . $page);
        }

        /*
        * Возникла ошибка. если текст ошибки есть, то это что-то важное. если текст ошибки пустой -- просто ничего не нашлось.
        *   в документации сие не освещено вообще, к сожалению.
        */
        $error_text = trim(strval($xml->err));

        if(!empty($error_text)) {
	        throw new Exception ('AVD error: '.$error_text, LM_AUTO_DEBUG_USER_ERROR);
        } else if (empty($error_text) && isset($xml->err)) {
            $this->response_type = '404';
            return;
        }

        // Запчасти
        $this->response_type = 'parts';

        // Тип аналога
        $analog_type = '4';

        /*
         * Запчасти.
         */
        if ($this->getResponseType() == 'parts') {
            $parts = array();
            $n_parts = 0;
            $reserve_catalogs = array();
            $this->brand_title = trim($this->brand_title);
            foreach ($xml->Item as $part) {

                $part = get_object_vars($part);

                // Уникальная extra для детали.
                $extra = $this->extra;
                $extra['hash'] = md5(json_encode(array(
                    $part['PriceID'], $part['Supplier'], $part['SupplierID'], $part['SupplierRegion'], $part['DeliveryPeriod']
                )));

                $price          = floatval(str_replace(array(' ', ','), array('', '.') , $part['Price']));
                $brand_title    = trim(strval($part['CatalogName']));
                $article        = LinemediaAutoPartsHelper::clearArticle(strval($part['DetailNum']));
                $title          = strval($part['DetailNameRus']);//iconv('CP1251', 'UTF-8', strval($part['DetailNameRus']));
                $weight         = floatval($part['DetailWeight']);
                $quantity       = intval($part['Quantity']);
                $delivery_time  = intval($part['DeliveryPeriod']);
                $date_update    = strval($part['LastUpdate']);
                if (!empty($this->brand_title) && strcasecmp($brand_title, $this->brand_title) != 0)
                    continue;
                /*
                * РЕЗЕРВНЫЕ КАТАЛОГИ
                */
                if (LinemediaAutoPartsHelper::clearArticle($article) == $this->query) {
                    $reserve_catalogs[ $brand_title ] = array(
                        'article' => $article,
                        'brand_title' => $brand_title,
                        'title' => $title,
                    );
                    $key = 'analog_type_N';
                } else {
                    $key = 'analog_type_4';
                }
                ++$n_parts;
                $parts[ $key ][] = array(
                    'id'                => 'avdmotors',
                    'article'           => $article,
                    'brand_title'       => $brand_title,
                    'title'             => $title,
                    'price'             => $price,
                    'weight'            => $weight,
                    'quantity'          => $quantity,
                    'delivery_time'     => $delivery_time * 24, // В часах
                    'date_update'       => $date_update,
                    'data-source'       => self::$title,
                    'extra'             => $extra,
                );
            }//foreach

            $this->parts = $parts;
            /*
            * РЕЗЕРВНЫЕ КАТАЛОГИ
            */
            $this->catalogs = array_values($reserve_catalogs);
            if (empty($this->brand_title)) {
                $this->response_type = count($reserve_catalogs) > 1 ? 'catalogs' : 'parts';
            }
             if (count($reserve_catalogs) <= 1 && $n_parts == 0) {
                 $this->response_type = '404';
             }
        }
    }

    /**
     * Получить максимум информации о детали (а особенно цену) основываясь на том, что эта запчасть данного поставщика и пришла из поиска
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

        // выполнить в любом случае для логина и получения id user
        $this->search();

        /*
        * Найдём именно эту деталь
        */
        foreach($this->parts as $group => $parts)
        {
            foreach($parts AS $part)
            {
                if($part['extra']['hash'] == $hash)
                    return $part;
            }
        }

        throw new Exception(self::$title.': Remote part not found');
    }

    /**
     * Конфигурация.
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            /*'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
            ),*/
        );
    }
}

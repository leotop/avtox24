<?php

/**
 * Linemedia Autoportal
 * Suppliers parser module
 * Remote MXgroup Supplier
 *
 * @author  Linemedia
 * @since   17/02/2014
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * interface of remote supplier
 */
class NextautoRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * appellation and URL of supplier
     *
     * @var string
     */
    public static $title = 'Next-auto';
    /**
     * @var string
     */
    public $url = 'http://next-auto.pro/';

    /**
     * initiate object
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     *  authorization
     *  login are united with searching for accelerating of page downloading
     */
    public function login()
    {
    }

    /**
     * init
     */
    public function init()
    {
        $this->browser->setBaseUrl($this->url);
    }

    /**
     * search
     *
     * @throws Exception
     */
    public function search()
    {
        $baseUrl = 'xmlprice.php?login=' . $this->profile_options['LOGIN'] . '&password=' . $this->profile_options['PASSWORD'];

        $getCatalogsUrl = $baseUrl . '&code=' . $this->query . '&sm=1&json';

        try {
            $answer = $this->browser->get($getCatalogsUrl);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        $response = $this->json_decode($answer);

        if ($response['error']) {
            throw new Exception($response['error']['descr']);
        }

        if (empty($response['details'])) {
            throw new Exception('Empty result');
        }

        $query = LinemediaAutoPartsHelper::clearArticle($this->query);

        if (is_array($response['details'])) {

            foreach ($response['details'] as $k => $part) {
                    $this->catalogs[$part['producer']] = array(
                        'article'     => $part['article'],
                        'brand_title' => $part['producer'],
                        'title'       => '-',
                        'source'      => self::$title,
                        'extra'       => array(
                            'articlefull' => $part['articlefull'],
                            'ident'       => $part['ident']
                        )
                    );
            }
        }

        if(count($this->catalogs) == 1) {
            $catalogs = array_values($this->catalogs);

            $this->brand_title = $catalogs[0]['brand_title'];
        }
        
        if ($this->brand_title) {

            $ident = '';

            foreach ($this->catalogs as $catalog) {
                if (strcasecmp($this->brand_title, $catalog['brand_title']) == 0) {
                    $ident = $catalog['extra']['ident'];
                    break;
                }
            }

            if (!$ident) {
                throw new \Exception(GetMessage('ERROR_IDENT'));
            }

            $getPartsUrl = $baseUrl . '&ident=' . $ident . '&json';

            try {
                $answer = $this->browser->get($getPartsUrl);
            } catch (\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }

            $response = $this->json_decode($answer);

            foreach ($response['pricelist'] as $part) {

                $article = LinemediaAutoPartsHelper::clearArticle($part['code']);

                $brandsCheck = strcasecmp($this->brand_title, $part['producer']) == 0 ? true : false;

                if ($article == $query && $brandsCheck) {
                    $part_group = 'N';
                } else {
                    $part_group = '0';
                }

                $delivery = explode(' ', $part['deliverydays']);
                $delivery = explode('-', $delivery[0]);

                if(count($delivery) == 2) {
                    $delivery = (int) ($delivery[0] + $delivery[1]) / 2;
                } else {
                    $delivery = (int) $delivery[0];
                }
                $this->parts['analog_type_' . $part_group][] = array(
                    'id'            => self::$title,
                    'article'       => $article,
                    'brand_title'   => $part['producer'],
                    'title'         => $part['caption'],
                    'price'         => $part['price'],
                    'quantity'      => (int)$part['rest'] ? : ($part['rest'] === '+' ? 1 : 0),
                    'delivery_time' => $delivery * 24,
                    'modified'      => $part['dataprice'],
                    'data-source'   => self::$title,
                    'extra'         => array(
                        'uid'               => $part['uid'],
                        'id'                => $part['id'],
                        'stock'             => $part['stock'],
                        'currency'          => $part['currency'],
                        'stat_otkaz'        => $part['stat_otkaz'],
                        'soutputorderstime' => $part['soutputorderstime'],
                        'amount'            => $part['amount'],
                        'hash'              => md5($article . $part['producer'] . $part['caption'] . $part['price'] . (int)$part['deliverydays'] . $part['stock'])
                    )
                );

            }


        }

        if (count($this->catalogs) == 1 || $this->brand_title != '') {
            $this->response_type = 'parts';
        } elseif (count($this->parts) == 0 && $this->brand_title != '') {
            $this->response_type = '404';
        } else {
            $this->response_type = 'catalogs';
        }
    }

    /**
     *  add detail to cart
     *
     * @param $data
     * @return array
     */
    public function getPartData($data)
    {
        $this->query = $data['article'];
        $this->brand_title = $data['brand_title'];

        $this->init();
        $this->search();

        foreach ($this->parts as $parts) {
            foreach ($parts as $part) {
                if ($part['extra']['hash'] == $data['extra']['hash']) {
                    return $part;
                }
            }
        }

        return array();
    }

    /**
     * ????????? ???????????????? ??????.
     *
     * @return array
     */
    public function getConfigVars()
    {
        return array(
            'LOGIN'    => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type'  => 'password',
            )
        );
    }

    public function json_decode($answer)
    {
        $response = json_decode($answer, true);

        if ($error = json_last_error()) {

            switch ($error) {

                case JSON_ERROR_DEPTH:
                {
                    $error_message = 'JSON parsing was ended up with error: Maximum stack depth exceeded';
                    break;
                }

                case JSON_ERROR_STATE_MISMATCH:
                {
                    $error_message = 'JSON parsing was ended up with error: Underflow or the modes mismatch';
                    break;
                }

                case JSON_ERROR_CTRL_CHAR:
                {
                    $error_message = 'JSON parsing was ended up with error: Unexpected control character found';
                    break;
                }

                case JSON_ERROR_SYNTAX:
                {
                    $error_message = 'JSON parsing was ended up with error: Syntax error, malformed JSON';
                    break;
                }

                case JSON_ERROR_UTF8:
                {
                    $error_message = 'JSON parsing was ended up with error: Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                }

                default:
                    {
                    $error_message = 'JSON parsing was ended up with error: Unknown error';
                    break;
                    }

            }

            throw new \Exception($error_message);

        }

        return $response;
    }

}


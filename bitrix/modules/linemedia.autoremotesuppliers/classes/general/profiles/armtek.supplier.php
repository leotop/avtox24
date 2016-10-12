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
class ArmtekRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * appellation and URL of supplier
     * @var string
     */
    public static $title = 'Armtek';
    /**
     * @var string
     */
    public $url = 'http://ws.armtek.ru/api/ws_search/search';

    /**
     * ???????? ??????
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * ?????????????.
     */
    public function init()
    {

    }


    /**
     * ???????????.
     */
    public function login()
    {

    }

    /**
     * search
     * @throws Exception
     */
    public function search() {

        $params = array(
                'VKORG'        => $this->profile_options['VKORG'],
                'KUNNR_RG'     => $this->profile_options['KUNNR_RG'],
                'PIN'          => $this->query,
                'BRAND'        => $this->brand_title,
                'QUERY_TYPE'   => '',
                'KUNNR_ZA'     => '',
                'INCOTERMS'    => '',
                'VBELN'        => '',
                'format'       => 'json'
        );

        $cURLOptions = array(
            CURLOPT_USERPWD         => $this->profile_options['LOGIN'] . ':' . $this->profile_options['PASSWORD'],
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
            CURLOPT_URL             => $this->url,
            CURLOPT_CUSTOMREQUEST   => "POST",
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_HTTPHEADER      => array("User-Agent: ArmtekRestClient ver1.0.0"),
            CURLOPT_HEADER          => false,
            CURLINFO_HEADER_OUT     => false,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 50,
            CURLOPT_POST            => count($params),
            CURLOPT_POSTFIELDS      => http_build_query($params)
        );

        $cURL = curl_init();
        curl_setopt_array($cURL, $cURLOptions);

        $response = curl_exec($cURL);

        //????????? ?????? curl
        if($errno = curl_errno($cURL)) {
            $error_message = curl_strerror($errno);
            throw new Exception($error_message);

        }

        curl_close($cURL);

        //????????? ?????? json_decode
        $response = json_decode($response, 1);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception(json_last_error());
        }

        //????????? ?????? ??????
        if($response['STATUS'] != 200) {
            throw new Exception($response['MESSAGES']);
        } elseif ($response['RESP']['MSG']) {
            throw new Exception($response['RESP']['MSG']);
        }

        $parts = $response['RESP'];

        $search_article = LinemediaAutoPartsHelper::clearArticle($this->query);
        $search_brand_title = strtoupper($this->brand_title);

        foreach($parts as $part) {

            $part_article = LinemediaAutoPartsHelper::clearArticle($part['PIN']);
            $part_brand_title = strtoupper($part['BRAND']);

            if($this->brand_title == '') {
                if($part_article == $search_article) {
                    $analogType = 'N';
                } else {
                    $analogType = '0';
                }
            } else {
                if((LinemediaAutoPartsHelper::clearArticle($part['PIN']) == $search_article) && ($search_brand_title == $part_brand_title))
                    $analogType = 'N';
                else
                    $analogType = '0';
            }

            $date_info = strptime($part['DLVDT'], '%Y%m%d%H%M%S');

            $delivery_days = $date_info['tm_yday'] - (date('z') + 1);

            $this->parts["analog_type_$analogType"][] = array(
                'id'                => 'armtek',
                'article'           => $part_article,
                'brand_title'       => $part_brand_title,
                'title'             => $part['NAME'],
                'price'             => $part['PRICE'],
                'quantity'          => (int) $part['RVALUE'],
                'delivery_time'     => $delivery_days*24, // ? ?????
                'date_update'       => '',
                'data-source'       => self::$title,
                'extra'				=> array(
                    'hash'			   => md5($part_article . $part_brand_title . $part['PRICE'] . (int) $part['RVALUE'] . $delivery_days),
                    'origin_article'   => $this->query,
                    'origin_brand'     => $this->brand_title
                )
            );

            if ($analogType == 'N') {
                $this->catalogs[$part_brand_title] = array(
                    'article' => $part_article,
                    'brand_title' => $part_brand_title,
                    'title' => $part['NAME']
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

        $this->query = $data['extra']['origin_article'];
        $this->brand_title = $data['extra']['origin_brand'];

        $this->init();

        $this->search();
        /*
         * ????? ?????? ??? ??????
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
     * ????????? ???????????????? ??????.
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
            'VKORG' => array(
                'title' => GetMessage('VKORG'),
                'type'  => 'string',
            ),
            'KUNNR_RG' => array(
                'title' => GetMessage('KUNNR_RG'),
                'type'  => 'string',
            ),
        );
    }

}


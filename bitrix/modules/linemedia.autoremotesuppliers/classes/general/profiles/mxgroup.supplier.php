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
class MXgroupRemoteSupplier extends LinemediaAutoRemoteSuppliersSupplier
{
    /**
     * appellation and URL of supplier
     * @var string
     */
    public static $title = 'MXgroup';
    /**
     * @var string
     */
    public $url = 'http://zakaz.mxgroup.ru/mxapi/?m=';
    /**
     * @var string
     */
    private static $search_strategy = 'search';
    /**
     * @var string
     */
    private static $received_format = 'json';

    /**
     * forming appropriate string for query
     * @param $recievedFormat
     * @param $searchStradegy
     */
    private function createURL($search_stradegy, $recieved_format) {
    	return $search_stradegy.'&login='.trim($this->profile_options['LOGIN']).'&password='.trim($this->profile_options['PASSWORD']).'&zapros='.trim($this->query).'&out='.$recieved_format;
    }


    /**
     * initiate object
     */
    public function __construct() {
        parent::__construct();
    }


    /**
     *  authorization
     *  login are united with searching for accelerating of page downloading
     */
    public function login() {
    }

    /**
     * init
     */
    public function init() {
    	$this->browser->setBaseUrl($this->url);
    }

    /**
     * search
     * @throws Exception
     */
    public function search() {

    	try {
    		$page = $this->browser->get($this->createURL(self::$search_strategy, self::$received_format));
    	} catch (\Exception $ex) {
    		throw new \Exception($ex->getMessage());
    	}

    	$response = json_decode($page, true);
		if ($error = json_last_error()) {

			switch ($error) {

				case JSON_ERROR_DEPTH: {
					$error_message = 'JSON parsing was ended up with error: Maximum stack depth exceeded';
					break;
				}

				case JSON_ERROR_STATE_MISMATCH: {
					$error_message = 'JSON parsing was ended up with error: Underflow or the modes mismatch';
					break;
				}

				case JSON_ERROR_CTRL_CHAR: {
					$error_message = 'JSON parsing was ended up with error: Unexpected control character found';
					break;
				}

				case JSON_ERROR_SYNTAX: {
					$error_message = 'JSON parsing was ended up with error: Syntax error, malformed JSON';
					break;
				}

				case JSON_ERROR_UTF8: {
					$error_message = 'JSON parsing was ended up with error: Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				}

				default: {
					$error_message = 'JSON parsing was ended up with error: Unknown error';
					break;
				}

			}

			throw new \Exception($error_message);

		}

    	$response = json_decode($page, true);
		if ($error = json_last_error())
			throw new \Exception($error);


    	if ($response['error']) {
    		$errors = array(
    				'Bad login or password' => GetMessage('ERROR_BAD_LOGIN_PASSWORD'), 'Bad request' => GetMessage('ERROR_BAD_REQUEST'),
    				'Forbidden' => GetMessage('ERROR_ACCESS_FORBIDDEN'), 'Not found' => GetMessage('ERROR_SPARE_NOT_FOUND'),
    				'Unknown error' => GetMessage('ERROR_UNKNOWN_ERROR'),
    		);
    		$error_text = (isset($errors[$response['error']])) ? $errors[$response['error']] : $response['error'];

    		throw new Exception($error_text);
    	}

	    if(is_array($response['result'])) {
		    
		    foreach ($response['result'] as $k => $part) {

			    $article = LinemediaAutoPartsHelper::clearArticle($part['articul']);

			    $coincide_brand = strtolower($this->brand_title) == strtolower($part['brand']) ? true : false;

			    if ($this->brand_title == '') {

				    $this->catalogs[$part['brand']] = array(
					    'article'     => $part['articul'],
					    'brand_title' => $part['brand'],
					    'title'       => $part['name'],
					    'source'      => self::$title,
					    'extra'       => array()
				    );
			    }


			    if (count($this->catalogs) == 1 || $this->brand_title != '' && $coincide_brand) {

				    if ($article == $this->query) {
					    // из консоли у нас нет модул€ авто
					    $part_group = 'N'; //LinemediaAutoPart::ANALOG_GROUP_ORIGINAL;
				    } else {
					    // из консоли у нас нет модул€ авто
					    $part_group = '0'; //LinemediaAutoPart::ANALOG_GROUP_UNORIGINAL;
				    }


				    $this->parts['analog_type_'.$part_group][] = array(
					    'id'                    => self::$title,
					    'article'               => $article,
					    'brand_title'           => $part['brand'],
					    'title'                 => $part['name'],
					    'price'                 => $part['discountprice'],
					    'weight'                => '-',
					    'quantity'              => (int) $part['count'],
					    'delivery_time'         => $part['deliverytime'],
					    'modified'           => '-',
					    'data-source'           => self::$title,
					    'extra'                 => array(
						    'article' => $article, 'brand' => $part['brand'] ,'id' => $part['id'], 'hash' => md5($part['storename'].$part['deliverytime'])
					    )
				    );
			    }

		    }

	    }
	    



    	/**
    	 * if brand not found than perhaps will be returned either catalogs or spares
    	*/
    	if (count($this->catalogs) == 1 || $this->brand_title != '') {
    		$this->response_type = 'parts';
    	} elseif (count($this->parts) == 0) {
    		$this->response_type = '404';
    	} else {
    		$this->response_type = 'catalogs';
    	}
    }

    /**
     *  add detail to cart
     * @param $data
     * @return array
     */
    public function getPartData($data) {


    	$this->query = $data['article'];
    	$this->brand_title = $data['brand_title'];
    	$this->extra = $data['extra'];

    	$this->init();
    	$this->search();


    	$md5hash = strtolower($data['brand_title']).$data['article'].$data['extra']['id'].$data['extra']['hash'];
    	$md5_required_detail = md5($md5hash);

    	foreach ($this->parts as $group => $parts) {
    		foreach ($parts as $part) {
    			$md5_current_detail = md5(strtolower($part['brand_title']).$part['article'].$data['extra']['id'].$data['extra']['hash']);

    			if ($md5_current_detail == $md5_required_detail) {
    				return $part;
    			}
    		}
    	}
    	return array();
    }

    /**
     * ѕолучение конфигурационных данных.
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


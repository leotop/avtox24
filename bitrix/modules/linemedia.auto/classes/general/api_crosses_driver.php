<?php
/**
 * Linemedia Autoportal
 * Main module
 * Connection to Linemedia API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoCrossesApiDriver {

    const DEFAULT_ENCODING = 'UTF-8';

    static $BASE_URL = 'http://78.46.101.198/json.php';
    static $LOGIN = null;
    static $PASSWORD = null;
    static $ENABLED = null;
    static $ERROR;

    /*
     * Максимально возможное количество аргументов для множественного запроса
     */
    static $MAX_ANALOGS_REQUEST = 10;

    static $SEARCH_OPTIONS = array(
        'subanalogs_non_oem' => false,
        'subanalogs_oem' => true,
        'subanalogs_appliance' => false,
        'min_weight' => 20,
    );

    public static function getError() {
        return self::$ERROR;
    }

    public static function isEnabled() {

        if(!is_null(self::$ENABLED)) {
            return self::$ENABLED;
        }
        if(!self::getLogin()) {
            self::$ERROR = array(
                'code' => 1,
                'error_text' => 'LM_AUTO_CROSSES_LOGIN_ERROR',
            );
            self::$ENABLED = false;
            return false;
        }
        if(!self::getPassword()) {
            self::$ERROR = array(
                'code' => 2,
                'error_text' => 'LM_AUTO_CROSSES_PASSWORD_ERROR',
            );
            self::$ENABLED = false;
            return false;
        }
        if(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ACCESS_CROSSES_ENABLED', 'N') != 'Y') {
            self::$ERROR = array(
                'code' =>3,
                'error_text' => 'LM_AUTO_CROSSES_NOT_ENABLED',
            );
            self::$ENABLED = false;
            return false;
        }

        $ver_info = self::query('Crosses_getVersion', array(), false);
        if($ver_info['status'] == 'ok') {
            self::$ENABLED = true;
            return true;
        } else {
            self::$ERROR = $ver_info['error'];
        }
        self::$ENABLED = false;
        return false;
    }

    private static function getLogin() {

        if(self::$LOGIN) {
            return self::$LOGIN;
        }
        self::$LOGIN = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CROSSES_LOGIN', false);
        return self::$LOGIN;
    }

    private static function getPassword() {

        if(self::$PASSWORD) {
            return self::$PASSWORD;
        }
        self::$PASSWORD = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_CROSSES_PASSWORD', false);
        return self::$PASSWORD;
    }

    public function getApplianceByArtMfaId($args) {

        if(count($args) > 0) {
            $no_cache = false;
            if($args['no_cache'] && $args['no_cache'] != 'N') {
                $no_cache = true;
            }
            return self::query('Crosses_applianceByArtMfaId', array('art_id' => (int) $args['art_id'], 'mfa_id' => (int) $args['mfa_id'], 'no_cache' => $no_cache));
        }
        return null;
    }

    public function getPartInfoByArticles($args) {

        $api_args = array();
        $options = array();
        if(array_key_exists('options', $args) && is_array($args['options'])) {
            $options = $args['options'];
            unset($args['options']);
        }

        // validation
        foreach($args as $arg) {

            $article = (string) $arg['article'];
            $brands = array();
            if(is_array($arg['brands'])) {
                $brands = $arg['brands'];
            }
            if(strlen($arg['brand_title']) > 0) {
                $brands[] = $arg['brand_title'];
            }
            $ga_id = (int) $arg['ga_id'];

            if(strlen($article) > 0 && count($brands) > 0 && $ga_id > 0) {
                $api_args[] = array(
                    'article' => $article,
                    'brands' => $brands,
                    'ga_id' => $ga_id,
                );
            }
        }

        if(count($api_args) > 0) {
            $no_cache = false;
            if($args['no_cache'] && $args['no_cache'] != 'N') {
                $no_cache = true;
            }
            return self::query('Crosses_partInfoByArticles', array('args' => $api_args, 'options' => $options, 'no_cache' => $no_cache));
        }
        return null;
    }

    public function getPartInfoByArtIds($args) {

        $api_args = array();
        $options = array();
        if(array_key_exists('options', $args) && is_array($args['options'])) {
            $options = $args['options'];
            unset($args['options']);
        }

        // validation
        foreach($args as $arg) {

            $art_id = (int) $arg;
            if($art_id > 0) {
                $api_args[] = $art_id;
            }
        }

        if(count($api_args) > 0) {
            $no_cache = false;
            if($args['no_cache'] && $args['no_cache'] != 'N') {
                $no_cache = true;
            }
            return self::query('Crosses_partInfoByArtIds', array('ids' => $api_args, 'options' => $options, 'no_cache' => $no_cache));
        }
        return null;
    }

    public function getArticleDetailsMultiple($args) {

        $api_args = array(
            'article_id' => $args['article_id'],
        );
        if($args['no_cache'] && $args['no_cache'] != 'N') {
            $api_args['no_cache'] = true;
        }
        return self::query('Crosses_partInfo', $api_args);
    }

    public function getAnalogs2Multiple($args) {

        $response = array();

        $options = self::$SEARCH_OPTIONS;

        /*
         * Возможность перекрывать настройки
         */
        if(array_key_exists('options', $args) && is_array($args['options'])) {
            $options = array_merge($options, $args['options']);
            unset($args['options']);
        }

        /*
         * Если идет множественный запрос - не запрашиваем субаналоги
         */
        if(count($args) > 1) {
            $options['subanalogs_non_oem'] = false;
            $options['subanalogs_oem'] = false;
            $options['subanalogs_appliance'] = false;
            $options['multiple'] = count($args);
        }

        /**
         * Защита от черезмерного количества запросов
         */
        if(count($args) > self::$MAX_ANALOGS_REQUEST) {
            $args = array_slice($args, 0, self::$MAX_ANALOGS_REQUEST);
        }

        foreach($args as $req_data) {

            $api_args = array();

            if(strlen($req_data['article']) > 0) {

                $api_args['article'] = $req_data['article'];
                if(array_key_exists('brand_title', $req_data)) {
                    $api_args['brand_title'] = $req_data['brand_title'];
                } else {
                    $api_args['brand_title'] = null;
                }
                if(array_key_exists('generic_article_id', $req_data)) {
                    $api_args['gid'] = (int) $req_data['generic_article_id'];
                } else {
                    $api_args['gid'] = null;
                }
                $api_args['options'] = $options;
                if(array_key_exists('modification_id', $req_data)) {
                    $api_args['options']['modification_id'] = (int) $req_data['modification_id'];
                }

                $res = self::query('Crosses_search', $api_args);

                if(count($response) < 1) {
                    $response['status'] = $res['status'];
                    if($res['status'] == 'error') {
                        $response['error'] = $res['error'];
                    } else {
                        $response['data'][] = $res['data'];
                    }
                } else {
                    if($res['status'] == 'error') {
                        $response['status'] = 'error';
                        $response['error'] = $res['error'];
                    } else {
                        $response['data'][] = $res['data'];
                    }
                }
            }
        }

        return $response;
    }

    public static function query($cmd, $data = array(), $check_enabled = true) {

        if($check_enabled && !self::isEnabled()) {
            return array(
                'status' => 'error',
                'error' => self::$ERROR,
            );
        }

        $url = self::$BASE_URL . '?f=' . $cmd . '&out=json';

        if(defined('JSON_UNESCAPED_UNICODE')) {
            $post_data_enc = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $post_data_enc = json_encode($data);
        }

        $login =

        $curl = curl_init();
        $options = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_CONNECTTIMEOUT  => 3,
            CURLOPT_TIMEOUT         => 25,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 4,
            CURLOPT_USERAGENT       => 'LinemediaAutoCrossesApiDriver',
            CURLINFO_HEADER_OUT		=> true,
            CURLOPT_URL				=> $url,
            CURLOPT_POST			=> true,
            CURLOPT_POSTFIELDS		=> $post_data_enc,
            CURLOPT_HTTPHEADER     	=> array('Content-Type: application/json'),
            CURLOPT_USERPWD			=> self::getLogin() . ":" . self::getPassword(),
            CURLOPT_ENCODING		=> 'gzip',
        );

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $last_query_info = curl_getinfo($curl);

        $error = false;
        if(curl_errno($curl)) {
            $error = array(
                'code' => curl_errno($curl),
                'error_text' => curl_error($curl),
            );
        }
        if($last_query_info['http_code'] != 200) {
            $error = array(
                'code' => $last_query_info['http_code'],
                'error_text' => 'RESPONSE HTTP CODE ERROR',
            );
        }

        if($error) {

            $response = array(
                'status' => 'error',
                'error' => $error,
            );
            return $response;

        } else {
            /*
             *    настройки для перекодировки.
             *    идеальная проверка на опеле в моделях найти "MOVANO B грузовоe".
             */
            mb_substitute_character('');
            setlocale(LC_COLLATE,'ru_RU.UTF-8');
            setlocale(LC_CTYPE,'ru_RU.UTF-8');
            mb_internal_encoding('utf-8');

            /*
             * Преобразование кодировки.
             */
            if (!defined('BX_UTF') || BX_UTF != true) {
                $response = self::iconvArray($response, self::DEFAULT_ENCODING, 'WINDOWS-1251//TRANSLIT');
            }

            setlocale(LC_COLLATE, 0);
            setlocale(LC_CTYPE, 0);


            return json_decode($response, 1);
        }
    }

    /**
     * Конвертация пришедшего от сервера базы кросов массива
     */
    protected function iconvArray($array, $from = 'UTF-8', $to = 'cp1251')
    {
        if (empty($array) || !is_array($array)) {
            return array();
        }

        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::iconvArray($value, $from, $to);
            } else {
                $result[$key] = iconv($from, $to, $value);
            }
        }
        return $result;
    }
}
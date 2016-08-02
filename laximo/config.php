<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

class Config {
    public static $ui_localization = 'ru'; // ru or en
    public static $catalog_data = 'ru_RU'; // en_GB or ru_RU

    public static $useLoginAuthorizationMethod = false;

    // login/key from laximo.ru
    public static $userLogin = '';
    public static $userKey = '';

    public static $redirectUrl = 'http://avtox24.ru/auto/search/?q=$oem$';
}

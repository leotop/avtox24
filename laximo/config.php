<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

include ($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/config.php");


class Config {
    public static $ui_localization = 'ru'; // ru or en
    public static $catalog_data = 'ru_RU'; // en_GB or ru_RU

    public static $useLoginAuthorizationMethod = true;

    // login/key from laximo.ru
    public static $userLogin = LAXIMO_LOGIN;
    public static $userKey = LAXIMO_PASS;    

    public static $redirectUrl = 'http://avtox24.ru/auto/search/?q=$oem$';
}

?>
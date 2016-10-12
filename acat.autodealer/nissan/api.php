<?php defined('LOGIN') or die('No direct script access.');
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
/**
 * Класс с API и внутренними функциями для оригинального каталога Nissan
 * В каталог входят следующие марки: Nissan.Infiniti
 */
class NIS extends A2D {

    /// Тип каталога передается на сервер
    protected static $_type = "NISSAN";
    public static $_typeID = 9;
//    protected static $catalogRoot = "/nissan";
    /// Производим базовые настройки
    public function __construct(){
        parent::__construct();
        $mark = $this->rcv('mark');
        if(empty($mark)) $mark = (stripos(strtolower($this->rcv('market')),'inf') > 1)?'infiniti':'nissan';
        static::setMark($mark);
        static::$mark = $mark;
        A2D::$catalogRoot = SERVICE_DIRECTORY."/nissan";
        /// Корневой каталог откуда стартовать скрипты поиска для текущего каталога (используется в конструкторе формы поиска)
        static::$searchIFace = "nissan";
        if(empty($mark) && !empty($this->rcv('market')) )  $mark = (stripos($this->rcv('market'),'inf') > 1)?'infiniti':'nissan';
        if(strtolower($mark)  == 'nissan') {
            static::$searchTabs = [[
                'id' => 1,
                'alias' => 'vin',
                'name' => 'VIN',
                'tName' => 'Поиск по VIN',
            ], [
                'id' => 2,
                'action' => 'frame',
                'alias' => 'frame',
                'tName' => 'Поиск по номеру кузова',
                'multi' => [[
                    'alias' => 'frame',
                    'name' => 'фрейм', /// В контексте "Укажите ..."
                ], [
                    'alias' => 'serial',
                    'name' => 'номер', /// В контексте "Укажите ..."
                ]],
            ], [
                'id' => 3,
                'action' => 'number',
                'alias' => 'number',
                'tName' => 'Поиск по номеру детали',
                'multi' => [
                    [
                        'alias' => 'number',
                        'name' => 'номер', /// В контексте "Укажите ..."
                    ], [
                        'alias' => 'market',
                        'name' => 'страна', /// В контексте "Укажите ..."
                        'list' => (array)$this->getNisMarkets($mark)
                    ]
                ],
            ]];
        }else{
            static::$searchTabs = [[
                'id' => 1,
                'alias' => 'vin',
                'name' => 'VIN',
                'tName' => 'Поиск по VIN',
            ], [
                'id' => 3,
                'action' => 'number',
                'tName' => 'Поиск по номеру детали',
                'multi' => [
                    [
                        'alias' => 'number',
                        'name' => 'номер', /// В контексте "Укажите ..."
                    ], [
                        'alias' => 'market',
                        'name' => 'страна', /// В контексте "Укажите ..."
                        'list' => (array)$this->getNisMarkets($mark)
                    ]
                ],
            ]];
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///   СТАНДАРТНЫЕ ФУНКЦИИ ДЛЯ ПОЛУЧЕНИЯ ДАННЫХ   ///////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
    /// Получение рынков ($mark = 'NISSAN' OR 'INFINITI')
    public function getNisMarkets($mark){
        $body  = "t=".static::$_type.$this->_auth."&f=".__FUNCTION__."&mark=$mark"; //$this->e($body);
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Модели выпускаемые для данного рынка ($market = 'CA' или 'CAINF')
    public function getNisModels($mark,$market){
        $body = "t=".static::$_type.$this->_auth."&f=".__FUNCTION__."&mark=$mark&market=$market";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Модификации для выбранной модели ($model - модель(U14))
    public function getNisModiff($market,$model){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&market=$market&model=$model";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Группы запчастей модификации ($modif(int) - модификация(1))
	public function getNisModInfo($market,$model,$modif){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&market=$market&model=$model&modif=$modif"; 
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Группа деталей для текущей комплектации
   	public function getNisGroup($market,$model,$modif,$group){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&market=$market&model=$model&modif=$modif&group=$group";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Иллюстрация и список номенклатуры для выбранной детали ($subfig,$sec = подфигура и секция, для первой страницы не обязательны)
    public function getNisPic($market,$model,$modif,$group,$figure,$subfig=NULL,$sec=NULL){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&market=$market&model=$model&modif=$modif&group=$group&figure=$figure&subfig=$subfig&sec=$sec";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Переадресация после перехода с поиска, если результат не в 1 вкладке
    public function getNisPicRedirect($market,$mdldir,$subfig,$part){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&market=$market&dir=$mdldir&subfig=$subfig&part=$part"; //$this->e($body);
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Информация по детали - под каким номеров в каком годе выпускалась
    public function getNisPnc($market,$model,$modif,$group,$figure,$subfig,$sec,$pnc){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&market=$market";
        $body .="&model=$model&modif=$modif&group=$group&figure=$figure&subfig=$subfig&sec=$sec&pnc=$pnc";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
	
    /// Поиск модели по VIN,фрейму и коду, если $srcflag:
	//  'n' = $vin 17 символов и марка 'nissan' или 'infiniti'
	//  'y' = $vin 3-4 символова и марка только 'nissan'(рынок только 'JP') $serial - это дата(не больше 6 цифр)
    public function searchNisVIN($vin,$mark,$serial = '',$srcflag = 'n'){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&vin=$vin&serial=$serial&mark=$mark&srcflag=$srcflag"; //$this->e($body);
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    /// Поиск модели по номеру детали($number = номер,$mark = рынок(ca) )
    /// Далее модель и модификация используются только в nissan->JP, т.к. у японцев много данных
    public function searchNISNumber($number,$mark,$model='',$modif=''){
        $body = "t=".static::$_type."&f=".__FUNCTION__.$this->_auth."&number=$number&market=$mark&model=$model&modif=$modif"; //var_dump($body);exit;
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Ну эт классика - минифункция перевода(используется к некоторых файлах)
     * @param null $k
     * @return null
     */
    static function translate($k = NULL){

        $zapas = [//перевожу на русский ключ массива
            "engine" => "Двигатель",
            "body" => "Кузов",
            "trim lvl" => "Класс",
            "trans" => "Трансмиссия",
            "drive" => "Привод",
            "grade" => "Класс",
            "other" => "Другое"
        ];
        if($k && array_key_exists($k,$zapas)) return $zapas[strtolower($k)];
        elseif($k && !array_key_exists($k,$zapas)) return $k;
        else false;
    }

    /**
     * Убирает ненужное из крошек и выставляет правельные ключи для примера
     * т.к. названия файлов отличаются от ключей
     * @param array $breadcrumbs
     * @param string $breadname
     */
    static function constructBreadcrumbs( $breadcrumbs=[],$breadname='' ){
        $a = '0'; array_shift(static::$arrActions);
        if (empty($breadcrumbs)) {
            A2D::$aBreads = A2D::toObj(A2D::$aBreads);
        } else {
            foreach ($breadcrumbs as $k=>$bread) {
                $currbread = self::stdToArray($bread);
                A2D::$aBreads = self::stdToArray(A2D::$aBreads);
                unset($currbread['breads'][0]);
                $breadname = (count((array)$breadcrumbs) == 1) ? $breadname : $k;

                if ($breadname == 'modinfo') {
                    $breadname = 'groups';
                } elseif ($breadname == 'group') {
                    $breadname = 'subgroups';
                }

                A2D::$aBreads[$breadname]['name'] =  $currbread['name'] ;
                A2D::$aBreads[$breadname]['breads'] =  $currbread['breads'] ;
                A2D::$aBreads = A2D::toObj(A2D::$aBreads);
            }
        }
    }

    /**
     * Классика преобразования объекта в массив
     * @param $obj
     * @return array
     */
    private static function stdToArray($obj){
        $rc = (array)$obj;
        foreach($rc as $key => &$field){
            if(is_object($field)) $field = self::stdToArray($field);
        }
        return $rc;
    }

    /**
     * Функция для получения имени файла из переменной __FILE__
     * используется в constructBreadcrumbs второй параметр
     * @param $filepath
     * @return mixed
     */
    public static function filename($filepath){
        $fullfilename = explode('/',$filepath);
        $filename = str_replace('.php','',$fullfilename[count($fullfilename)-1]);
        return $filename;
    }

    /**
     * Возвращает последовательный ключ для крошек, например $currbread['breads'][0] = 'mark' => 'nissan'
     * нужно для построения адреса в крошках
     * @param $key
     * @return string
     */
    static function getParams($key){ $key = (int) $key;
        $arr = [
            0 => 'mark',
            1 => 'market',
            2 => 'model',
            3 => 'modif',
            4 => 'group',
            5 => 'figure',
            6 => 'subfig',
            7 => 'sec',
            8 => 'pnc'
        ];
        if(isset($key)) return $arr[$key];
        else return 'empty';
    }

    /**
     * Редирект, если после перехода с поиска по номеру результат на 2 и далее закладке
     * @param null $url
     * @param int $code
     * @param string $site
     * @return bool
     */
    public function redirect($url = NULL, $code = 302, $site ='' ){
        if ($url === NULL) return false;
        if (!empty($site)){ $site = str_replace('http://','',$site); header("Location: http://".$site.$url,TRUE,$code);}
        else header("Location: http://".$_SERVER['HTTP_HOST'].$url,TRUE,$code);
    }

    /**
     * Каступная функция замены спец символом
     * Чтобы JS грамотно обработал все, в class & id не может быть некаких спецсимволов, кроме "_"
     * а иногда встречается в Partcode "+", других пока не видел
     * если встречаются другие - увеличить количество знаков __ и чуть переработать функцию
     *
     * @param $string
     * @return mixed
     */
    public function repl($string){
        $string = str_replace('+','_',$string);
        return $string;
    }

    /**
     * Функция похожа на repl($string) , только используется на странице поиска в названиях моделей и т.д.
     * @param $str
     * @return mixed
     */
    function replaceSS($str) {
        $str = str_replace('*','_',$str);
        $str = str_replace('-','_',$str);
        $str = str_replace(' ','_',$str);
        $str = str_replace('/','_',$str);
        return $str;
    }

    public function jEcho($msg,$exit=TRUE){
        if( !is_string($msg) ) $msg = json_encode($msg);
        echo $msg;
        if( $exit ) exit;
    }
}

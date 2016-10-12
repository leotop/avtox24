<?php defined('LOGIN') or die('No direct script access.');

/**
 * Класс с API и внутренними функциями для оригинальных каталогов
 */
class MCCT extends A2D
{
    /**
     * Тип каталога передается на сервер (может иметь разные значения)<br>
     * Примеры: KIA, HYUNDAI
     * @var string 
     */
    static public $_type=null;

    static public $_mark=null;

    // <editor-fold defaultstate="collapsed" desc="Constants & Enums"> =================================================================================

    const MCCT_MARK_KIA            ='KIA';
    const MCCT_MARK_HYUNDAI    ='HYUNDAI';

    static public $ENUM_MCCT_MARK=[
        self::MCCT_MARK_KIA            =>'kia',
        self::MCCT_MARK_HYUNDAI    =>'hyundai',
    ];

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Constructor"> =================================================================================

    /**
     * @return static
     */
    public static function instance()
    {
        /* @var $self static */
        $self=parent::instance();
        $mark=strtoupper(trim($self->rcv('mark')));

        if(!array_key_exists($mark,static::$ENUM_MCCT_MARK))
            $mark=static::MCCT_MARK_KIA;

        $markval=static::$ENUM_MCCT_MARK[$mark];
        static::setMark($markval);
        static::$_mark=$markval;
        static::$_type=$mark;
        static::$searchTabs[0]['hidden']=['mark'=>static::$_mark];

        //static::$bLogo=true;
        //static::$breadsLogo='/media/images/mcct/'.$markval.'.png';

        return $self;
    }

    public function __construct()
    {
        parent::__construct();

    static::$catalogRoot=false;//"/mcct";

        /// Данным значениям (static::$arrActions) в helpers/breads.php сопоставляются последовательно параметрам из A2D::$aBreads
        //static::$arrActions=['index','catalogs','models','model','major','minor'];
        static::$arrActions=['mark','type','region','family','catalog','model','vin','major','minor'];

        /// Корневой каталог откуда стартовать скрипты поиска для текущего каталога (используется в конструкторе формы поиска)
        static::$searchIFace='mcct';

        /// Массив для построение формы с поиском (опиание в главном README.MD)
        static::$searchTabs = [[
            'id'    => 1,
            'alias' => 'vin',
            'name'  => 'VIN', /// В контексте "Укажите ..."
            'tName' => 'Поиск по VIN',
        ],[
            'id'        => 2,
            'alias'    => 'detail',
            'name'    => 'Номер', /// В контексте "Укажите ..."
            'tName'    => 'Поиск по номеру детали',
        ]];
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Util"> =================================================================================

    public function addSearch($server)
    {
        $stdAll=new stdClass();
        $stdAll->code='all';
        $stdAll->ru='Все регионы';
        $select[]=$stdAll;
        $region=$this->rcv('region');
        $mark=static::$_type;

        if($region)
            $_GET['region']=strtolower($region);

        foreach((array)$server->regions as $i=>$v)
        {
            $std=new stdClass();
            $std->code=strtolower($i);
            $std->ru=$v;
            $select[]=$std;//json_decode("{code:'".$i."',ru:'".$v."'}");
        }
        //$this->e($select);

        static::$searchWhere = [
            'first' => TRUE, /// Первое "искать везде"
            'tabs'  => ['detail'],
            'name'  => 'mark',
            'value' => static::$_mark,
            'desc'  => 'каталог '.$mark,
            'lists' => [
                'regions' => [
                    'name' => 'Все регионы',
                    'alias' => 'region',
                    'options' => $select
                ]
            ],
            'gSearch' => '/adc/search/detail.php',
        ];
    }

    /**
     * @param string $key
     * @param string $name
     * @param string[] $vals
     */
    public function addMcctBread($key,$name,$vals=[],$base=false)
    {
        if(!static::$aBreads)
            static::$aBreads= new stdClass();

        if($base)
            $array=['name'=>$name,'breads'=>$vals];
        else
            $array=['name'=>$name,'breads'=>$vals,'root'=>'/mcct'];

        static::$aBreads->{$key}=static::toObj($array);
    }

    public function addMcctBreadRoot()
    {
        $this->addMcctBread('types','Каталог',[],true);

        switch($this->rcv('type'))
        {
            case 's': $this->addMcctBread('marks','Грузовые (иномарки)',['s'=>['typeID'=>10]],true); break;
            case 'c': $this->addMcctBread('marks','Автобусы',['s'=>['typeID'=>3]],true); break;
            default: $this->addMcctBread('marks','Легковые (иномарки)',['s'=>['typeID'=>9]],true); break;
        }
    }

    public function addMcctBreadIndex($server)
    {
        $regions=(array)static::property($server,'regions',[]);
        $region=static::property($server,'region');
        $name=static::$_type;
        $type=$this->rcv('type');
        $mark=$this->rcv('mark');

        if($region&&isset($regions[$region]))
            $this->addMcctBread('index','Оригинальный каталог '.$name.' ('.$regions[$region].')',[$mark,$type,strtolower($region)]);
        else
            $this->addMcctBread('index','Оригинальный каталог '.$name,[$mark,$type]);
    }

    public function addMcctBreadFamily()
    {
        $type=$this->rcv('type');
        $mark=$this->rcv('mark');
        $region=$this->rcv('region');
        $family=$this->rcv('family');
        $name=static::txtMcctFamilyName($family);

        $this->addMcctBread('catalogs',$name,[$mark,$type,$region,$family]);
    }

    public function urlMcct($script,$param=[],$dir='/mcct/')
    {
        $url=$dir.$script.'.php';

        if($param)
        {
            // Переводим ассоциативный массив в GET параметры, ключи в нижнем регистре, значения без пробелов в ENCODE формате
            $arr=array_map(function($i,$v){ return strtolower($i).'='.urlencode(str_replace(' ','_',$v)); },array_keys($param),$param);
            $url.='?'.implode('&',$arr);
        }

        return $url;
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Text"> =================================================================================

    static public function txtMcctFamilyName($string)
    {
        return strtoupper(str_replace('_',' ',$string));
    }

    static public function txtMcctFamilyUrl($string)
    {
        return strtolower(str_replace(' ','_',$string));
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Server"> =================================================================================

    /**
     * @param string $type
     * @param string $region
     * @return stdClass
     */
    public function getMcctIndex($type,$region)
    {
        return $this->getMcct('Index',"&type=$type&region=$region");
    }

    /**
     * @param string $type
     * @param string $region
     * @param string $family
     * @return stdClass
     */
    public function getMcctCatalogs($type,$region,$family)
    {
        return $this->getMcct('Catalogs',"&type=$type&region=$region&family=$family");
    }

    /**
     * @param string $type
     * @param string $region
     * @param string $family
     * @param string $catalog
     * @return stdClass
     */
    public function getMcctModels($type,$region,$family,$catalog)
    {
        return $this->getMcct('Models',"&type=$type&region=$region&family=$family&catalog=$catalog");
    }

    /**
     * @param string $type
     * @param string $region
     * @param string $family
     * @param string $catalog
     * @param string $model
     * @param string $vin
     * @return stdClass
     */
    public function getMcctModel($type,$region,$family,$catalog,$model,$vin)
    {
        return $this->getMcct('Model',"&type=$type&region=$region&family=$family&catalog=$catalog&model=$model&vin=$vin");
    }

    /**
     * @param string $type
     * @param string $region
     * @param string $family
     * @param string $catalog
     * @param string $model
     * @param string $vin
     * @param string $major
     * @return stdClass
     */
    public function getMcctMajor($type,$region,$family,$catalog,$model,$vin,$major)
    {
        return $this->getMcct('Major',"&type=$type&region=$region&family=$family&catalog=$catalog&model=$model&vin=$vin&major=$major");
    }

    /**
     * @param string $type
     * @param string $region
     * @param string $family
     * @param string $catalog
     * @param string $model
     * @param string $vin
     * @param string $major
     * @param string $minor
     * @return stdClass
     */
    public function getMcctMinor($type,$region,$family,$catalog,$model,$vin,$major,$minor)
    {
        return $this->getMcct('Minor',"&type=$type&region=$region&family=$family&catalog=$catalog&model=$model&vin=$vin&major=$major&minor=$minor");
    }

    /**
     * @param string $where
     * @param string $primary
     * @param string $in
     * @param string $region
     * @return stdClass
     */
    public function getMcctSearch($where='',$primary='',$in='',$region='')
    {
        return $this->getMcct('Search',"&where=$where&primary=$primary&in=$in&region=$region");
    }

    /**
     * @param string $func
     * @param string $body
     * @return stdClass
     */
    public function getMcct($func,$body)
    {
        $function    ='get'.static::$_type.$func;
        $url            ='t='.static::$_type.'&f='.$function.$this->_auth.$body;
        $answer        =$this->getAnswer($url);

        if(!$answer)
            $this->error('Empty response from server',404);

        $result        =json_decode($answer);

        if(!$result)
            $this->error('Empty response from server',404);
        elseif(($error=static::property($result,'errors')))
            $this->error($error,404);

        return $result;
    }

    // </editor-fold>

}

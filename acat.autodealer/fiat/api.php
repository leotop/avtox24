<?php defined('LOGIN') OR die('No direct script access.');


class Fiat extends A2D {

    public function __construct(){
        parent::__construct();
        static::setMark($this->rcv('brand'));
        A2D::$catalogRoot = SERVICE_DIRECTORY."/fiat";
    }

    public function getFIATModels( $brand ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&brand=$brand";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function getFIATProduction( $brand, $model ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&brand=$brand&model=$model";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function getFIATGroup( $brand, $model, $production ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&brand=$brand&model=$model&production=$production";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function getFIATSubGroup( $brand, $model, $production, $group ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&brand=$brand&model=$model&production=$production&group=$group";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function getFIATBoard( $brand, $model, $production, $group, $subGroup ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&brand=$brand&model=$model&production=$production&group=$group&subGroup=$subGroup";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function getFIATPartDrawData( $production, $group, $subGroup, $tableCod ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&production=$production&group=$group&subGroup=$subGroup&tableCod=$tableCod";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function getFIATDraw( $brand, $model, $production, $group, $subGroup, $tableCod, $variant, $zoom ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&brand=$brand&model=$model&production=$production&group=$group&subGroup=$subGroup&tableCod=$tableCod&variant=$variant&zoom=$zoom".
            "&uIP=".$this->uIP."&uAgent=".$this->uAgent.
            "";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function searchFIATVIN( $VIN ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&VIN=$VIN";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public function searchFIATNumber( $number ){
        $body = "t=FIAT&f=".__FUNCTION__.$this->_auth."&number=$number";
        $answer = $this->getAnswer($body);
        $r = json_decode($answer);
        return $r;
    }

    public static function constructBreadcrumbs($breadcrumbs) {
        foreach ($breadcrumbs AS $key => $bread) {
            switch ($key) {
                case 0: {
                    $name = mb_strtolower($bread->name);
                    A2D::$aBreads['models'] = [
                        'name' => strtoupper($bread->name),
                        'breads' => [
                            0 => $name,
                        ]
                    ];
                } break;
                case 1: {
                    $model = $bread->path;
                    A2D::$aBreads['productions'] = [
                        'name' => $bread->name,
                        'breads' => [
                            0 => $name,
                            1 => $model
                        ]
                    ];
                } break;
                case 2: {
                    $production = $bread->path;
                    A2D::$aBreads['groups'] = [
                        'name' => $bread->name,
                        'breads' => [
                            0 => $name,
                            1 => $model,
                            2 => $production
                        ]
                    ];
                } break;
                case 3: {
                    $group = $bread->path;
                    A2D::$aBreads['subGroups'] = [
                        'name' => $bread->name,
                        'breads' => [
                            0 => $name,
                            1 => $model,
                            2 => $production,
                            3 => $group
                        ]
                    ];
                } break;
                case 4: {
                    A2D::$aBreads['draw'] = [
                        'name' => $bread->name,
                        'breads' => [
                            0 => $name,
                            1 => $model,
                            2 => $production,
                            3 => $group,
                            4 => $bread->path
                        ]
                    ];
                } break;
            }

        }
        A2D::$aBreads = A2D::toObj(A2D::$aBreads);
    }
}
<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Requests class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);

/**
 * Класс для работы с "Шагами" заказа
 * Class LinemediaAutoSuppliersStep
 */
class LinemediaAutoSuppliersStep
{
    const STEPS_KEY = 'LM_AUTO_SUPPLIERS_STEPS';
    
    protected $id   = null;
    protected $data = array();


    /**
     * Конструктор, загружает Шаг по названию
     * @param $id - название Шага
     */
    public function __construct($id)
    {
        $this->id = (string) $id;
        $this->load();
    }

    /**
     * Получение названия Шага
     * @return null|string - возвращает название Шага
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Загрузка Шага
     */
    public function load()
    {
        $this->data = unserialize(COption::GetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_STEP_'.$this->id));
    }

    /**
     * Сохранение данных в Шаге
     */
    public function save()
    {
        COption::SetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_STEP_'.$this->id, unserialize($this->data));
    }

    /**
     * Получение данных по названию поля
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[strval($key)];
    }
    
    
    public function set($key, $value)
    {
        $this->data[strval($key)] = $value;
    }
    
    
    /**
     * Получение следующего шага по порядку.
     */
    public function next()
    {
        $steps = self::getList();
        
        foreach ($steps as $step) {
            if ($step->getID() == $this->getID()) {
                break;
            }
        }
        return current($steps);
    }
    
    
    /**
     * Получение первого шага по порядку.
     */
    public static function getNextStepByKey($key)
    {
        $key = (string) $key;
        
        if (!array_key_exists($key, self::getList())) {
            return self::getFirstStep();
        }
        
        $step = new self($key);
        
        return $step->next();
    }
    
    
    /**
     * Получение первого шага по порядку.
     */
    public static function getFirstStep()
    {
        $step = reset(self::getList());
        
        return $step;
    }
    
    
    /**
     * Получение списка шагов.
     */
    public static function getList()
    {
        $keys = unserialize(COption::GetOptionString('linemedia.autosuppliers', self::STEPS_KEY));
        
        $steps = array();
        foreach ($keys as $key) {
            $steps[$key] = new self($key);
        }
        return $steps;
    }
}

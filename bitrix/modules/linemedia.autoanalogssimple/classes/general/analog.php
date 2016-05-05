<?php
/**
 * Linemedia Автоэксперт
 * Модуль простых аналогов
 * Общий класс для работы с БД
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/**
* Общий класс для работы с БД
*/
abstract class LinemediaAutoAnalogsSimpleAnalogAll
{
	/**
	* Группа аналогов по умолчанию
	* №3 - это сравнительные номера
	*/
    const ANALOG_GROUP_DEFAULT = '3';
    
    /**
    * Объект БД
    */
    protected $database;
    
    /**
    * Объект словоформ
    */
    protected $wordforms;
    
    
    /**
    * Подключение к БД и создание объекта словоформ
    */
    public function __construct()
    {
        /*
         * Connect to DB
         */
        try {
            global $DB;
            $this->database = $DB;//new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }
        
        $this->wordforms = new LinemediaAutoWordForm();
    }
    
    
    /**
     * Получение названия группы аналогов по её коду
     * @param string $group Код группы
     */
    public static function getAnalogGroup($group)
    {
        $group = (string) $group;
        
        $groups = LinemediaAutoPart::getAnalogGroups();
        if (array_key_exists($group, $groups)) {
            return $group;
        }
        return self::ANALOG_GROUP_DEFAULT;
    }
    
     /**
     * Поиск записей по условиям.
     * @param array $conditions Список критериев для поиска
     */
    abstract function find($conditions);
    
    /**
     * Добавление кросса.
     *
     * @param array $entry Массив с информацие о кроссе
     */
    abstract function add($entry);
    
    /**
     * Удаление аналогов по условию.
     *
     * @param array $conditions Список критериев для поиска и удаления
     */
    abstract function clear($conditions);

}

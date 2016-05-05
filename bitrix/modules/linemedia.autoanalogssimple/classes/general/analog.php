<?php
/**
 * Linemedia �����������
 * ������ ������� ��������
 * ����� ����� ��� ������ � ��
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/**
* ����� ����� ��� ������ � ��
*/
abstract class LinemediaAutoAnalogsSimpleAnalogAll
{
	/**
	* ������ �������� �� ���������
	* �3 - ��� ������������� ������
	*/
    const ANALOG_GROUP_DEFAULT = '3';
    
    /**
    * ������ ��
    */
    protected $database;
    
    /**
    * ������ ���������
    */
    protected $wordforms;
    
    
    /**
    * ����������� � �� � �������� ������� ���������
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
     * ��������� �������� ������ �������� �� � ����
     * @param string $group ��� ������
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
     * ����� ������� �� ��������.
     * @param array $conditions ������ ��������� ��� ������
     */
    abstract function find($conditions);
    
    /**
     * ���������� ������.
     *
     * @param array $entry ������ � ���������� � ������
     */
    abstract function add($entry);
    
    /**
     * �������� �������� �� �������.
     *
     * @param array $conditions ������ ��������� ��� ������ � ��������
     */
    abstract function clear($conditions);

}

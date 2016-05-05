<?php
use Bitrix\Main\Entity;

/**
 * Class LinemediaProductsTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> title string(255) optional
 * <li> article string(100) mandatory
 * <li> original_article string(100) mandatory
 * <li> brand_title string(100) mandatory
 * <li> price double optional
 * <li> quantity double optional
 * <li> group_id string(50) optional
 * <li> weight double optional
 * <li> Multiplicity int mandatory
 * <li> supplier_id string(100) mandatory
 * <li> modified datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> second_hand string(255) optional
 * <li> wholesale_price_barrier string(255) optional
 * <li> illiquid string(255) optional
 * <li> correct_import double optional
 * <li> multiplication_factor double optional
 * </ul>
 **/

class LinemediaProductsTable extends Entity\DataManager {

    static $map;

    // поля, передаваемые в фильтре напрямую
    public static $BASE_FIELDS = array(
        'id',
        //'title', - передавать только через additional_fields
        'article',
        //'original_article', - передавать только через additional_fields
        'brand_title',
        //'price', - передавать только через additional_fields
        'quantity',
        //'group_id', - передавать только через additional_fields
        //'weight', - передавать только через additional_fields
        'supplier_id',
    );

    public static function getTableName()
    {
        return 'b_lm_products';
    }

    public static function getMap()
    {
        global $DB;

        if(is_array(self::$map)) {
            return self::$map;
        } else {
            $map = array();
            $sql = "SHOW COLUMNS FROM b_lm_products";
            $res = $DB->Query($sql);
            while($row = $res->Fetch()) {

                $field = array();
                $type = $row['Type'];
                if(($pos = strpos('(', $type)) !== false) {
                    $type = substr($type, 0, $pos);
                }
                switch($type) {
                    case 'bigint' :
                    case 'int' : {
                        $field['data_type'] = 'integer';
                    } break;
                    case 'decimal' :
                    case 'float': {
                        $field['data_type'] = 'float';
                    }
                    case 'timestamp' :
                    case 'datetime' : {
                        $field['data_type'] = 'datetime';
                    } break;
                    default: {
                        $field['data_type'] = 'string';
                    }
                }
                if($row['Key'] == 'PRI') {
                    $field['primary'] = true;
                } else if($row['Null'] == 'NO') {
                    $field['required'] = true;
                }
                $map[$row['Field']] = $field;
            }
            self::$map = $map;
            return $map;
        }
    }
}
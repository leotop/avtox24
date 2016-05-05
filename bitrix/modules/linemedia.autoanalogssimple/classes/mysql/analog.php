<?php

/**
 * Linemedia Автоэксперт
 * Модуль простых аналогов
 * Класс для работы с БД MySQL
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/

 */
 
IncludeModuleLangFile(__FILE__); 
 
/**
 * Класс для работы с БД MySQL
 */
class LinemediaAutoAnalogsSimpleAnalog extends LinemediaAutoAnalogsSimpleAnalogAll
{
	/**
	* Имя таблицы в БД, хранящей список кроссов
	*/
    const TABLE = 'b_lm_analogs_simple';
    
    /**
    * Конструктор класса
    */
    public function __construct()
    {
        parent::__construct();
    }

    
    /**
     * Поиск записей по условиям.
     * @param array $conditions Список критериев для поиска
     */
    public function find($conditions)
    {
        $conditions = (array) $conditions;

        $where = array();
        
        if ($conditions['!id']) {
            // $ids = join(',', array_map('intval', (array) $conditions['!id']));
            $ids = join(',', $conditions['!id']);
            //$where []= "`id` NOT IN ($ids)";
            $where []= "(SELECT `id` IN ($ids) = 0)";
        }
        
        if ($conditions['brand_title']) {
            $brand_titles  = (array) $this->wordforms->getBrandWordforms($conditions['brand_title']);
            $brand_titles[]= $conditions['brand_title'];
        } else {
            $brand_titles = array('');
        }
        
        
        if (isset($conditions['article']) && !isset($conditions['brand_title'])) {
            $article = "'" . $this->database->ForSql(strval($conditions['article'])) . "'";
            $where []= "(`article_original` = $article OR `article_analog` = $article)";
        } elseif (isset($conditions['article']) && isset($conditions['brand_title'])) {
            // Загрузка словоформ.
            $brand_titles  = (array) $this->wordforms->getBrandWordforms($conditions['brand_title']);
            $brand_titles[]= $conditions['brand_title'];
            $brand_titles_in = '(\'' . join("','", array_map(array($this->database, 'ForSQL'), $brand_titles)) . '\')';
            
            $article = "'" . $this->database->ForSql(strval($conditions['article'])) . "'";
            $where []= "((`article_original` = $article AND `brand_title_original` IN $brand_titles_in) OR (`article_analog` = $article AND `brand_title_analog` IN $brand_titles_in))";
        } elseif (isset($conditions['brand_title'])) {
            // Загрузка словоформ.
            $brand_titles  = (array) $this->wordforms->getBrandWordforms($conditions['brand_title']);
            $brand_titles[]= $conditions['brand_title'];
            $brand_titles_in = '(\'' . join("','", array_map(array($this->database, 'ForSQL'), $brand_titles)) . '\')';
            
            $brand_title  = "'" . $this->database->ForSql(strval($conditions['brand_title'])) . "'";
            $where []= "(`brand_title_original` IN $brand_titles_in OR `brand_title_analog` IN $brand_titles_in)";
        }
        
        // Импорт.
        if ($conditions['import_id']) {
            $import_id = "'%" . $this->database->ForSql(trim(strval($conditions['import_id']))) . "%'";
            $where []= "`import_id` LIKE $import_id";
        }
        
        // Группа.
        if ($conditions['group']) {
			$group = "'" . $this->database->ForSql(strval($conditions['group'])) . "'";
            $where []= "(`group_original` = $group OR `group_original` IS NULL OR `group_analog` = $group OR `group_analog` IS NULL)";
        }
        
        // Дата добалвение импорта (с).
        if ($conditions['added']['from']) {
            $added_from = date('Y.m.d G:i:s', strtotime(strval($conditions['added']['from'])));
            $added_from = "'" . $this->database->ForSql($added_from) . "'";
            $where []= "`added` >= $added_from";
        }
        
        // Дата добалвение импорта (по).
        if ($conditions['added']['to']) {
            $added_to = date('Y.m.d G:i:s', strtotime(strval($conditions['added']['to'])));
            $added_to = "'" . $this->database->ForSql($added_to) . "'";
            $where []= "`added` <= $added_to";
        }
        
        
        
        $where = (count($where)) ? ' WHERE ' . join(' AND ', $where) : '';
        
        
        /*
         * Ограничение по количеству.
         */
        if (isset($conditions['start']) || isset($conditions['limit'])) {
            $start = (int) $conditions['start'] ? $conditions['start'] : 0;
            $limit = (int) $conditions['limit'] ? $conditions['limit'] : 50;
            $limit = " LIMIT $start, $limit";
        }
        
        try {
            $sql = "SELECT * FROM `".self::TABLE."` $where $limit;";
            return $this->database->Query($sql);
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error searching simple analogs ' . $e->GetMessage());
        }
    }
    
    
    /**
     * Получить количество запчастей по критериям
     *
     * @param array $conditions Список критериев для поиска
     */
	public function counts($conditions)
	{
		$conditions = (array) $conditions;

		$where = array();

		if ($conditions['!id']) {
			// $ids = join(',', array_map('intval', (array) $conditions['!id']));
			$ids = join(',', $conditions['!id']);
			$where []= "`id` NOT IN ($ids)";
		}

		if ($conditions['brand_title']) {
			$brand_titles  = (array) $this->wordforms->getBrandWordforms($conditions['brand_title']);
			$brand_titles[]= $conditions['brand_title'];
		} else {
			$brand_titles = array('');
		}


		if (isset($conditions['article']) && !isset($conditions['brand_title'])) {
			$article = "'" . $this->database->ForSql(strval($conditions['article'])) . "'";
			$where []= "(`article_original` = $article OR `article_analog` = $article)";
		} elseif (isset($conditions['article']) && isset($conditions['brand_title'])) {
			// Загрузка словоформ.
			$brand_titles  = (array) $this->wordforms->getBrandWordforms($conditions['brand_title']);
			$brand_titles[]= $conditions['brand_title'];
			$brand_titles_in = '(\'' . join("','", array_map(array($this->database, 'ForSQL'), $brand_titles)) . '\')';

			$article = "'" . $this->database->ForSql(strval($conditions['article'])) . "'";
			$where []= "((`article_original` = $article AND `brand_title_original` IN $brand_titles_in) OR (`article_analog` = $article AND `brand_title_analog` IN $brand_titles_in))";
		} elseif (isset($conditions['brand_title'])) {
			// Загрузка словоформ.
			$brand_titles  = (array) $this->wordforms->getBrandWordforms($conditions['brand_title']);
			$brand_titles[]= $conditions['brand_title'];
			$brand_titles_in = '(\'' . join("','", array_map(array($this->database, 'ForSQL'), $brand_titles)) . '\')';

			$brand_title  = "'" . $this->database->ForSql(strval($conditions['brand_title'])) . "'";
			$where []= "(`brand_title_original` IN $brand_titles_in OR `brand_title_analog` IN $brand_titles_in)";
		}

		// Импорт.
		if ($conditions['import_id']) {
			$import_id = "'%" . $this->database->ForSql(trim(strval($conditions['import_id']))) . "%'";
			$where []= "`import_id` LIKE $import_id";
		}

		// Группа.
		if ($conditions['group']) {
			$group = "'" . $this->database->ForSql(strval($conditions['group'])) . "'";
			$where []= "(`group_original` = $group OR `group_original` IS NULL OR `group_analog` = $group OR `group_analog` IS NULL)";
		}

		// Дата добалвение импорта (с).
		if ($conditions['added']['from']) {
			$added_from = date('Y.m.d G:i:s', strtotime(strval($conditions['added']['from'])));
			$added_from = "'" . $this->database->ForSql($added_from) . "'";
			$where []= "`added` >= $added_from";
		}

		// Дата добалвение импорта (по).
		if ($conditions['added']['to']) {
			$added_to = date('Y.m.d G:i:s', strtotime(strval($conditions['added']['to'])));
			$added_to = "'" . $this->database->ForSql($added_to) . "'";
			$where []= "`added` <= $added_to";
		}


		$where = (count($where)) ? ' WHERE ' . join(' AND ', $where) : '';

		try {
           /*
			* Если нет условий, выборка COUNT(*) в InnoDB очень долгая
			* Для её ускорения есть клон первичного индекса, форсированное использование которого
			* ускоряет её во много раз 
			*/
			$use_index = ($where == '') ? 'USE INDEX ( id )' : '';
			$sql = "SELECT COUNT(*) AS cnt FROM `".self::TABLE."` $where $use_index;";
            $res = $this->database->Query($sql)->Fetch();
			
			return $res['cnt'];
		} catch (Exception $e) {
			LinemediaAutoDebug::add('Error searching simple analogs ' . $e->GetMessage());
		}
	}
    
    
    /**
     * Добавление кросса.
     *
     * @param array $entry Массив с информацие о кроссе
     */
    public function add($entry)
    {
        global $APPLICATION;
        $modulePermissions = $APPLICATION->GetGroupRight("linemedia.autoanalogssimple");
        if($modulePermissions < 'W') {
            throw new Exception(GetMessage("ACCESS_DENIED"));
        }

        $import_id              = "'" . $this->database->ForSql(strval($entry['import_id']))            . "'";
        $group_original         = "'" . $this->database->ForSql(strval($entry['group_original']))                . "'";
        $group_analog           = "'" . $this->database->ForSql(strval($entry['group_analog']))                . "'";
        $article_original       = "'" . $this->database->ForSql(LinemediaAutoPartsHelper::clearArticle(strval($entry['article_original'])))     . "'";
        $brand_title_original   = "'" . $this->database->ForSql(strval($entry['brand_title_original'])) . "'";
        $article_analog         = "'" . $this->database->ForSql(LinemediaAutoPartsHelper::clearArticle(strval($entry['article_analog'])))       . "'";
        $brand_title_analog     = "'" . $this->database->ForSql(strval($entry['brand_title_analog']))   . "'";
        
        try {
            $this->database->Query("
                INSERT INTO `".self::TABLE."` (`import_id`, `group_original`, `group_analog`, `article_original`, `brand_title_original`, `article_analog`, `brand_title_analog`)
                VALUES ($import_id, $group_original, $group_analog, $article_original, $brand_title_original, $article_analog, $brand_title_analog)
                ON DUPLICATE KEY UPDATE `group_original`=$group_original, `group_analog`=$group_analog,`import_id`=$import_id
            ", true);
            return  $this->database->lastID();
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error adding simple analog ' . $e->GetMessage());
        }
    }
    
    
    /**
     * Обновление записи об аналогах.
     *
     * @param int $id ID записи
     * @param array $entry Массив с новой информацие о кроссе
     */
    public function update($id, $entry)
    {
        global $APPLICATION;
        $modulePermissions = $APPLICATION->GetGroupRight("linemedia.autoanalogssimple");
        if($modulePermissions < 'W') {
            throw new Exception(GetMessage("ACCESS_DENIED"));
        }

    	$id                    = (int) $id;
        $import_id             = "'" . $this->database->ForSql(strval($entry['import_id']))            . "'";
        $group                 = "'" . $this->database->ForSql(strval($entry['group_original']))                . "'";
        $article_original      = "'" . $this->database->ForSql(LinemediaAutoPartsHelper::clearArticle(strval($entry['article_original'])))     . "'";
        $brand_title_original  = "'" . $this->database->ForSql(strval($entry['brand_title_original'])) . "'";
        $article_analog        = "'" . $this->database->ForSql(LinemediaAutoPartsHelper::clearArticle(strval($entry['article_analog'])))       . "'";
        $brand_title_analog    = "'" . $this->database->ForSql(strval($entry['brand_title_analog']))   . "'";
        $group_analog          = "'" . $this->database->ForSql(strval($entry['group_analog']))   . "'";
        try {
            $this->database->Query("
                UPDATE `".self::TABLE."` 
                SET `import_id` = $import_id,
                    `group_original` = $group,
                    `article_original` = $article_original,
                    `brand_title_original` = $brand_title_original,
                    `article_analog` = $article_analog,
                    `brand_title_analog` = $brand_title_analog,
                    `group_analog` = $group_analog
                WHERE `id` = $id
                LIMIT 1;
            ", true);
			if ($this->database->db_Error) {
				return false;
			}
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error updating simple analog ' . $e->GetMessage());
            return false;
        }
        return true;
    }
    
    
    /**
     * Получение аналога по ID.
     *
     * @param int $id ID записи
     */
    public function GetByID($id)
    {
    	$id = (int) $id;
        try {
            $res = $this->database->Query("SELECT * FROM `".self::TABLE."` WHERE `id` = $id LIMIT 1;");
            return $res;
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error updating simple analog ' . $e->GetMessage());
        }
    }
    
    
    /**
     * Удаление аналогов по условию.
     *
     * @param array $conditions Список критериев для поиска и удаления
     */
    public function clear($conditions)
    {
        $where = array();
        
        if ($conditions['import_id']) {
            $import_id = "'" . $this->database->ForSql(strval($conditions['import_id'])) . "'";
            $where[] = "`import_id` = $import_id";
        }
        if ($conditions['group_original']) {
			$group = "'" . $this->database->ForSql(strval($conditions['group_original'])) . "'";
            $where[] = "`group_original` = $group";
        }
        if ($conditions['id']) {
            $id = intval($conditions['id']);
            $where[] = "`id` = $id";
        }
        
        $where = (count($where)) ? ' WHERE ' . join(' AND ', $where) : '';
        
        try {
            $this->database->Query("DELETE FROM `".self::TABLE."` $where");
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error clearing simple analogs ' . $e->GetMessage());
        }
    }
}

<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__); 
 
/*
 * Search through database
 */
class LinemediaAutoSearchPartialAll implements LinemediaAutoISearch
{
	/** 
	 * Объект базы данных 
	 * 
	 * @param object $database
	 */
	
	private $database;
	private $cur_page;
	private $limit;
	private $pages_number;
	private $sort;
	private $sort_field;
	
	/**
	 * В кострукторе создадим объект базы данных 
	 */
	 
	public function __construct($cur_page = 1, $limit = 20, $pages_number = 10)
	{
		$this->cur_page = $cur_page;
		$this->limit = $limit;
		$this->pages_number = $pages_number;
		
		try {
            $this->database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }
	}
	
	/**
	 * Получим запчасть по ID 
	 */
	 
	public function getById($id) {
		$id = (int) $id;
		$query = "SELECT `id`, `title`, `supplier_id` FROM `b_lm_products` WHERE `id` = $id";
		
		$res_db = $this->database->query($query);
		
		if($res = $res_db->Fetch()) {
			return $res; 
		} 
	}
	
    /**
     * Поиск запчасти по локальной базе данных
	 *
	 * @param array $part
	 * @param bool $multiple
     */
    public function searchLocalDatabaseForPart($part, $multiple = false)
    {
        /*
         * Основные критерии поиска
         */
        $article         = LinemediaAutoPartsHelper::clearArticle($part['article']);
        $id              = (int) $part['id'];
		$name            = addslashes((string) $part['title']);
        $brand_title     = (string) $part['brand'];
        $supplier_id     = (array) $part['supplier_id'];
		$all             = $part['field'] == "all" ? 1 : 0;
		$sort			 = isset($part['sort']) ? $part['sort'] : "asc";
		$sort_field		 = isset($part['sort_field']) ? $part['sort_field'] : "title";
		$pagination      = (isset($part['pagination']) && $part['pagination'] == "Y") ? "Y" : "N";
		$wordFrms        = (isset($part['wordforms'])) ? true : false;
		$this->sorting = $sort;
		$this->sort_field = $sort_field;
		
		/*
		 * Если был поисковый запрос
		 */
		
		if(isset($part["s"])) {
			$this->search = "Y";
			$this->name = $name;
			$this->brand_title = $brand_title;
			$this->article = $article;
		}
		
        /*
         * Дополнительные критерии поиска, требующие дополнительного поиска по бренду
         */
        $extra = (array) $part['extra'];
		
        /*
         * составляем запрос
         */
        $where = array();
        
        // Показывать ли товары только в наличии.
        if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y') {
            $where []= '`quantity` > 0';
        }
        
        if ($id > 0) {
            $where[] = '`id` = ' . $this->database->ForSql($id);
        }
		
		if ($brand_title != '') {
			/*
			 * Добавим словоформы.
			 */
			$wordforms = new LinemediaAutoWordForm();
			$brand_titles = $wordforms->getBrandWordforms($brand_title);
			if (count($brand_titles) > 0 && $wordFrms == true) {
				$brand_titles[]= $brand_title;
				$brand_titles = array_unique($brand_titles);
				$brand_titles = array_map('strval', $brand_titles);
				$brand_titles = array_map(array($this->database, 'ForSql'), $brand_titles);
				$brand_titles = "'" . join("', '", $brand_titles) . "'";
				$where[] = "UPPER(`brand_title`) IN ($brand_titles)";
			} else {
				$brand_title = strtoupper((string) $brand_title);
				$where[] = "UPPER(`brand_title`) = '" . $this->database->ForSql($brand_title) . "'";
			}
		}
		
		if ($supplier_id) {
			foreach($supplier_id as $k => $v) {
				$supplier_id[$k] = "'" . $v . "'";
			}
			$where[] = '`supplier_id` IN (' . implode(", ", $supplier_id) . ')';
		}
		
		if($all) {
			if ($article) {
				$where[] = "`article` LIKE '%" . $this->database->ForSql($article) . "%'";
			}
			
			if($name) {
				if(strpos($name, " ") === false) {
					$where[] = "`title` LIKE '%" . $this->database->ForSql($name) . "%'";
				} else {
					$where_name = explode(" ", $name);
					foreach($where_name as $k => $v) {
						if($v != '') {
							$where_name[$k] = "`title` LIKE '%" . $this->database->ForSql($v) . "%'";
						} else {
							unset($where_name[$k]);
						}
					}
					if(count($where_name) > 1 && $where_name[1] != '' ) {
						$name = join(" AND ", $where_name);
					} else {
						$name = $where_name[0];
					}
					$where[] = $name;
				}
			}
		
			/*
			 * Дополнительные критерии поиска.
			 */
			if (count($additional_fields) > 0) {
				foreach ($additional_fields as $col => $val) {
					$operator = '=';
					if (in_array($col[0], array('=', '>', '<'))) {
						$operator = $col[0];
					}
					$col = '`' . $this->database->ForSql($col) . '`';
					$val = "'" . $this->database->ForSql($val) . "'";
					$where []= "$col $operator $val";
				}
			}
			
			/*
			 * Должен быть задан хоть один фильтр, количества и активных поставщиков.
			 */
			if (count($where) < 1) {
				return false;
			}
		
			/*
			 * Запрос.
			 */
			 
			$this->getPages($where, $this->limit);
			$this->createPagination();
			 
			$sql = 'SELECT `id`, `title`, `article`, `supplier_id`, `quantity`, `price`, `brand_title` FROM `b_lm_products` WHERE ' . join(' AND ', $where);
		} else {
			$sql_str = join(' AND', $where);
			
			switch($part['field']) {
				case "title": 
				if(strpos($name, " ") === false) {
					$sql = "SELECT `title` FROM `b_lm_products` WHERE `title` LIKE '%" . $name . "%' AND " . $sql_str;
				} else {
					$where_name = explode(" ", $name);
					foreach($where_name as $k => $v) {
						if($v != '') {
							$where_name[$k] = "`title` LIKE '%" . $v . "%'";
						} else {
							unset($where_name[$k]);
						}
					}
					if(count($where_name) > 1 && $where_name[1] != '' ) {
						$name = join(" AND ", $where_name);
					} else {
						$name = $where_name[0];
					}
					$sql = "SELECT `title` FROM `b_lm_products` WHERE $name AND " . $sql_str;
				}
				break;
				case "article": 
				$sql = "SELECT `article` FROM `b_lm_products` WHERE `article` LIKE '%" . $article . "%' AND " . $sql_str;
				break;
			}
		}
		
		//Для сортировки по алфавиту
		if($sort == "desc") {
			$sql .= " ORDER BY `$sort_field` DESC";
		} else {
			$sql .= " ORDER BY `$sort_field`";
		}
		
		if($pagination == "Y") { 
			if(!isset($this->limit) || $this->limit <= 0) {
				$this->limit = 20;
			}
			if(!isset($this->cur_page)) {
				$this->cur_page = 1;
			}
			
			$rows_start = ($this->cur_page - 1) * $this->limit;
			$sql .= " LIMIT $rows_start, $this->limit";
		} 
		
		$parts = array();
		
		$st = microtime(true);
		
        try {
            $res = $this->database->Query($sql);
        } catch (Exception $e) {
            throw $e;
        }
		
        /*
         * Мы ищем одну запчасть или много?
         */
        if ($multiple) {
            $parts = array();
			$i = 0;
            while($part_arr = $res->Fetch()) {
                /*
                 * Источник поступления информации о запчасти - локальная БД в случае, если запрос не конкретно по одному полю
                 */
				if($all) {
					$part_arr['data-source'] = 'local-database';
					$parts []= $part_arr;
				} else {
					//Сразу запишем данные с тегами для вывода, чтобы избежать лишних переборов
					if($part_arr[$part['field']] == $compare_parts[$i - 1]) {
						continue;
					}
					$compare_parts[$i] = $part_arr[$part['field']];
					$parts []= '<div class="color_p"><p class="answ_item">' . $part_arr[$part['field']] . '</p></div>';
					$i++;
				}
            }
		$end = microtime(true);
		$time = $end - $st;
			
            return $parts;
        } else {
            if ($part_arr = $res->Fetch()) {
                /*
                 * Источник поступления ин-ции о запчасти - локальная БД в случае, если запрос не конкретно по одному полю
                 */
				if($all) {
					$part_arr['data-source'] = 'local-database';
				}
                return $part_arr;
            } else {
                return false;
            }
        }
    }
	
	/*
	 * Получим производителей, доступных пользователю, которые есть в базе
	 *
	 * @param array $supplier_id
	 */
	
	public function getAllManufacturers($supplier_id = array(), $unused_suppliers = array()) {
		$query = 'SELECT `brand_title` FROM `b_lm_products`';
		
		$where = "";
		
		if(count($supplier_id) > 0) {
			foreach($supplier_id as $k => $v) {
				$supplier_id[$k] = "'".$v."'";
			}
			$where = " WHERE `supplier_id` IN (" . implode(",", $supplier_id) . ")";
		}
		
		$query .= $where;
		$query .= " ORDER BY `brand_title`";
		
		$brands_db = $this->database->query($query);
		while($brands_arr = $brands_db->getNext()) {
			$brands[] = $brands_arr['brand_title'];
		}
		
		$brands = array_unique($brands);
		
		return $brands;
	}
	
	/*
	 * Метод для подсчета страниц постранички
	 */
	 
	public function getPages($where = array(), $limit = 20) {
		if(count($where) > 0) {
			foreach($supplier_id as $k => $v) {
				$supplier_id[$k] = "'" . $v . "'";
			}
			$query = "SELECT COUNT(*) FROM `b_lm_products` WHERE " . join(" AND ", $where);
		} else {
			$query = "SELECT COUNT(*) FROM `b_lm_products`";
		}
		
		$rows_db = $this->database->query($query);
		if($rows = $rows_db->Fetch()) {
			foreach($rows as $row) {
				$row_count = $row;
				break;
			}
			$pages = ceil($row_count/$limit);
		}
		
		$this->limit = $limit;
		$this->pages = $pages;
	}
	
	/*
	 * Метод для создания постраничной навигации
	 */
	
	public function createPagination() {
		$end_page = $this->pages;
		$start_page = 1;
		$show_left_arrows = true;
		$show_right_arrows = true;
		$page_arr = array();
		if($this->pages < $this->pages_number) {
			$counter = $this->pages;
		} else {
			$counter = $this->pages_number;
			if($this->pages_number % 2 == 0) {
				$k = -$this->pages_number/2;
			} else {
				$k = -floor($this->pages_number/2);
			}
		}
		
		//Если нет ни одного элемента - страниц тоже нет - навигацию не показваем
		
		if($this->pages == 0 || !isset($this->pages)) {
			return;
		}
		
		//Если текущая страница больше, тогда приравняем к максимальной
		
		if($this->cur_page > $this->pages) {
			$this->cur_page = $this->pages;
		}
		for($i = 0; $i < $counter; $i++) {
			$page_arr[$i] = $this->cur_page + $k;
			$k++;
		}
		$arr_length = count($page_arr);
		foreach($page_arr as $k => $v) {
			if($v <= 0) {
				unset($page_arr[$k]);
				$page_arr[end(array_keys($page_arr)) + 1] = $page_arr[end(array_keys($page_arr))] + 1;
			}
			if($v > $end_page) {
				unset($page_arr[$k]);
				array_unshift($page_arr, $page_arr[0] - 1);
			}
		}
		
		reset($page_arr);
		if(current($page_arr) == $start_page && $this->cur_page == $start_page) {
			$show_left_arrows = false;
		}
		
		if(end($page_arr) == $end_page && $this->cur_page == $end_page) {
			$show_right_arrows = false;
		}
		
		/**
		 * Если постраничка делается после поискового запроса
		 */
		 
		$url = ''; 
		 
		if(isset($this->search)) {
			$url = "&search=Y&article=".$this->article."&name=".$this->name."&brand=".$this->brand_title;
		} 
		
		if(isset($this->sorting)) {
			$url .= "&sort=" . $this->sorting . "&sort_field=" . $this->sort_field;
		}
		foreach($page_arr as $k => $v) {	
			if($v == $this->cur_page) {
				$page_arr[$k] = '<li class="disabled"><a class="disabled" href="">' . $v . '</a></li>';
			} else {	
				$page_arr[$k] = '<li><a href="' . $_SERVER['SCRIPT_URL'] . '?page=' . $v . $url . '">' . $v . '</a></li>';
			}
		}
		
		$html = implode("", $page_arr);
		
		if($show_left_arrows) {
			$left_arrow = '<div class="pagination"><ul><li><a href="'. $_SERVER['SCRIPT_URL'] . '?page=' . $start_page . $url . '">&laquo;</a></li>';
			$left_arrow .= '<li><a href="' . $_SERVER['SCRIPT_URL'] . '?page=' . ($this->cur_page - 1) . $url . '">&lsaquo;</a></li>';
		} else {
			$left_arrow = '<ul><li class="disabled"><a class="disabled" href="">&laquo;</a></li>';
			$left_arrow .= '<li class="disabled"><a class="disabled" href="">&lsaquo;</a></li>';
		}
		
		if($show_right_arrows) {
			$right_arrow = '<li><a href="'. $_SERVER['SCRIPT_URL'] . '?page=' . ($this->cur_page + 1) . $url . '">&rsaquo;</a></li>';
			$right_arrow .= '<li><a href="' . $_SERVER['SCRIPT_URL'] . '?page=' . $end_page . $url . '">&raquo;</a></li></ul></div>';
		} else {
			$right_arrow = '<li class="disabled"><a class="disabled" href="">&rsaquo;</a></li>';
			$right_arrow .= '<li class="disabled"><a  class="disabled" href="">&raquo;</a></li></ul></div>';
		}
		
		$pagination = $left_arrow . $html . $right_arrow;
		
		$this->pagination = $pagination;
	}
	
	public function getPagination() {
		return $this->pagination;
	}
}

<?php
/**
 * Linemedia Autoportal
 * Main module
 * Convert prices agent
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


/*
CModule::IncludeModule('linemedia.auto');
LinemediaAutoConverterAgent::run();
*/


IncludeModuleLangFile(__FILE__);

class LinemediaAutoConverterAgent
{
    const STANDARD_PRICELIST_URL = 'standard_pricelist.csv';

    static $run_string = 'LinemediaAutoConverterAgent::run();';

    // выводить ли лог?
    // используется для проверки агента из админки
    static $output_debug = true;


    /**
     * Основная функция дебага
     */
    public static function run($test = false)
    {
    	
    	
    	
    	
    	if (!CModule::IncludeModule('linemedia.auto')) {
    		return self::$run_string;
    	}
        
    	
        /*
    	* Можем ли мы запустить ещё один крон?
    	*/
        if(!LinemediaAutoModule::canRunAnotherCron())
        	return self::$run_string;

        

        @set_time_limit(0);

        // disable all buffering
        while(ob_get_level())
        	ob_end_flush();
        flush();


        /*
        * Отключим отладку
        */
        LinemediaAutoDebug::$enabled = false;


        
    	/*
		 * А есть ли крон?
		 */
		if (!LinemediaAutoImportAgent::checkCron()) {
			$ar = array(
			   "MESSAGE"         => GetMessage('LM_AUTO_NEED_CRON'),
			   "TAG"             => "LM_NEED_CRON",
			   "MODULE_ID"       => "linemedia.auto",
			   "ENABLE_CLOSE"    => "N"
			);
			$ID = CAdminNotify::Add($ar);
					
			/*
			 * Запрещено выполнять импорт не из под крона.
			 */
			if(!$test) return self::$run_string;

		} else {
			CAdminNotify::DeleteByTag("LM_NEED_CRON");
		}
		
        $agent = new self();

        $tasks = $agent->getPendingTasks();

         
        
        
        
		/*
		 * Нет задач.
		 */
		if (count($tasks) == 0) {

			if(self::$output_debug)
				self::log('no pending files');

			return self::$run_string;
        }

		foreach ($tasks as $task_id => $files) {
			$agent->executeTask($task_id, $files);
		}

		/*
         * Cобытие окончания отработки агента.
         * Были ли импортированы файлы и сколько.
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterPricelistsConvert");
		while ($arEvent = $events->Fetch()) {
		    try {
			    ExecuteModuleEventEx($arEvent, array(count($files), $files));
			} catch (Exception $e) {
			    throw $e;
			}
	    }


	    if(self::$output_debug)
			self::log('jobs completed');

        return self::$run_string;
	}


    /**
     * Получение ожидающих задач.
     */
	public function getPendingTasks()
	{
		/*
		 * Файлы для конвертации
		 */
        if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/pending/')) {
            self::log('Error! Create /upload/linemedia.auto/pricelists/pending/ folder!');
            return false;
        }

		$folder = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/pending/';
		$files = scandir($folder);

		$tasks = array();
		foreach ($files as $filename) {
		    if (in_array($filename, array('.', '..'))) {
		        continue;
		    }


		    /*
             * Загружен ли файл до конца?
             */
            if ($handle = fopen($folder . $filename, 'r')) {
                // http://php.net/manual/en/function.flock.php
                if (!flock($handle, LOCK_EX)) {
                    continue;
                } else {
                    flock($handle, LOCK_UN);
                }
                fclose($handle);
            }



			$file_data = explode('_', $filename);
			$task_id = $file_data[0];
			$tasks[$task_id] []= $filename;
		}
		return $tasks;
    }


    /**
     * Обработка одной задачи с указанием файлов.
     */
    private function executeTask($task_id, $files = array())
    {
    	
    	
		$task_res = LinemediaAutoTask::GetByID($task_id);
		$task = $task_res->Fetch();
        if(!$task) {
	        self::log('Task #' . $task_id . ' not found! ERROR!');
	        return false;
        }


		try {
			$task['conversion'] = unserialize($task['conversion']);
		} catch (Exception $e) {
			$task['conversion'] = array();
		}
        unset($task_res);

		/*
		 * Пробежимся по всем файлам
		 */
		foreach ($files as $file) {
			self::log('Start task ' . $task['title'] . ', filename is ' . $file);

			$filename = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/pending/' . $file;

			if (filesize($filename) == 0) {
				self::log('File skipped! Zero filesize ' . $filename);
				rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting_error/' . $file);
				continue;
			}

			if (!is_readable($filename)) {
				self::log('File skipped! Not readable ' . $filename);
				try {
					rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting_error/' . $file);
				} catch (Exception $e) {
					// ohh (((
				}
				continue;
			}


			/*
			 * Переместим файл - источник конвертации
			 */
			$new_folder = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/sources/' . date('Y_m_d')  . '/';
			if (!file_exists($new_folder)) {
	            mkdir($new_folder, 0777, true);
	        }

	        self::log('Copy original file to sources folder');
			copy($filename, $new_folder . basename($filename));



			/*
			 * Переместим файл в папку для конвертации. 
			 */
            if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting/')) {
                self::log('Error! Create /upload/linemedia.auto/pricelists/converting/ folder!');
                return false;
            }
			self::log("Move $file to converting folder");
			$info = pathinfo($filename);
			$new_filename = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting/' .$task['id'] . '_' . md5($file) . '.' . $info['extension'];
			rename($filename, $new_filename);
			$filename = $new_filename;

			
			
			/*
	         * Cобытие начала конвертации прайса
	         */
	        $events = GetModuleEvents("linemedia.auto", "OnBeforePricelistConvert");
			while ($arEvent = $events->Fetch()) {
			    try {
				    ExecuteModuleEventEx($arEvent, array($filename, $task));
				} catch (Exception $e) {
				    throw $e;
				}
		    }


			/*
			* Если необходимоо, распакуем архив
			*/
			$filename = $this->unzipFile($filename, $task);





			/*
			 * Сначала тип файла
			 */

			/*
			 * Если не работает конвертация файла!
			 */
			if (!LinemediaAutoTasker::isConversionSupported()) {
				/*
				 * Если тип не CSV.
				 */
				if (!in_array($task['conversion']['type'], array('csv'))) {
					/*
					 * Да и файл не из CSV, TXT
					 */
					$fileinfo = pathinfo($filename);
					if (!in_array($fileinfo['extension'], array('txt', 'csv'))) {
						self::log('File skipped! Conversion not supported (install ssconvert) ' . $filename);
						rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting_error/' . $file);
						continue;
					}
				}
			}
            
            /*
             * Нужно ли пересохранить файл в libreoffice?
             */
            if($task['conversion']['source_resave']) {

                /*
                 * На случай крона. Если там будет стоять /, то не будет отрабатывать libreoffice
                 * и будет ошибка java. Задача 14142
                 */
                if(php_sapi_name() == 'cli') {
                    putenv("HOME=" . shell_exec('echo ~'));
                }

                if(strpos($filename, '.xlsx') !== false) {
                    $folder = dirname($filename);
                    $cmd = "libreoffice --headless --convert-to xlsx $filename --outdir $folder/tmp && mv -f $folder/tmp/* $folder";
                    print('CMD: ' . $cmd . "\n\n");
                    shell_exec($cmd);
                } elseif(strpos($filename, '.xls') !== false) {
                    $folder = dirname($filename);
                    $cmd = "libreoffice --headless --convert-to xls $filename --outdir $folder/tmp && mv -f $folder/tmp/* $folder";
                    print('CMD: ' . $cmd . "\n\n");
                    shell_exec($cmd);
                }
            }

			/*
			 * Перевод в CSV
			 */
			switch ($task['conversion']['type']) {
				case 'xls':
					$filename = $this->convert2CSV($filename, 'xls');
                    break;
				case 'xlsx':
					$filename = $this->convert2CSV($filename, 'xlsx');
                    break;
				case 'csv':
					//OK
                    break;

				// Автоопределение.
				case '':
				default:
					$fileinfo = pathinfo($filename);
					switch ($fileinfo['extension']) {
						case 'xls':
							$filename = $this->convert2CSV($filename, 'xls');
                            break;
						case 'xlsx':
							$filename = $this->convert2CSV($filename, 'xlsx');
                            break;
						case 'csv':
							//OK
							$task['conversion']['type'] = 'csv'; // Установим для парсинга CSV.
                            break;
                        case 'txt':
                            // TXT по умолчанию считаем как CSV.
                            $task['conversion']['type'] = 'csv'; // Установим для парсинга CSV.
                            rename($filename, $filename.'.csv');
                            $filename .= '.csv';
                            break;
						default:
							/*
							 * Не удалось определить тип файла
							 */
							self::log('File skipped! Couldn\'t detect filetype of ' . $file);
							rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/converting_error/' . $file);
							continue 3;
                            break;
					}
                    break;
			}

			// error converting xls to xsv
			if($filename === false || !file_exists($filename)) {
				self::log('Missing file to open,possibly ssconvert returned error');
				continue;
			}


			/*
			 * Теперь кодировка
			 */
			switch ($task['conversion']['encoding']) {
				case 'Windows-1251':
					$from = 'cp1251';

                    self::log('File encoding is manually set to ' . $from . ', changing to utf-8 with iconv [0]');

			        $cmd = 'iconv -f ' . $from . ' -t UTF-8 ' . escapeshellarg($filename) . ' -o ' . escapeshellarg($filename.'.tmp');
			        $cmd_result = shell_exec($cmd);
			        unlink($filename);
			        rename($filename . '.tmp', $filename);
                    break;

				case 'utf-8':
				    break;

				// автоопределение
				case '':
				default:
					$cmd = 'file -bi ' . escapeshellarg($filename);
			        $cmd_result = shell_exec($cmd);

			        $response = explode(';', $cmd_result);
			        $charset  = explode('=', $response[1]);
			        $encoding = trim($charset[1]);
			        if ($encoding != 'utf-8') {
			            self::log('File encoding is ' . $encoding . ', changing to utf-8 with iconv [1]');

			            $from = 'cp1251';
				        $cmd = 'iconv -f ' . $from . ' -t UTF-8 ' . escapeshellarg($filename) . ' -o ' . escapeshellarg($filename.'.utf');
				        $cmd_result = shell_exec($cmd);
				        unlink($filename);
				        rename($filename.'.utf', $filename);
			        }
                    break;
			}

			/*
			 * Начнём конвертацию
			 */
			self::log('File conversion completed, open file '.$filename.' to move columns');


			// Открываем файл на чтение
	        try {
	            $handle = fopen($filename, "r");
	        } catch (Exception $e) {
	            self::log("Error opening pricelist $filename " . $e->GetMessage());
	            rename($filename, $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/converting_error/'.$file);
	            continue;
	        }

	        // Открываем файл на запись
	        try {
	            $handle_w = fopen($filename . '.result', "w");
	        } catch (Exception $e) {
	            fclose($handle);
	            self::log("Error opening new file to write " . $e->GetMessage());
	            continue;
	        }

	        /*
	         * Установка локали.
	         */
	        @setlocale(LC_ALL, "ru_RU");
	        //@setlocale(LC_COLLATE, "ru_RU.UTF-8");
	        //@setlocale(LC_CTYPE, "ru_RU.UTF-8");

	        /*
	         * Настройки столбцов.
	         * Столбцы начинаются с нуля, поэтому отнимаем 1 от введённых пользователем значений.
	         */
	        $columns = $task['conversion']['columns'];
	        foreach ($columns as $code => $val) {
	            $columns[$code] = intval($val) - 1;
            }

	        /*
	         * Разделитель
	         */
	        if ($task['conversion']['type'] == 'csv') {
		        $separator = $task['conversion']['separator'] ? $task['conversion']['separator'] : ';';

                if($separator == "\\t") {
                    $separator = "\t";
                }
	        } else {
	        	$separator = ','; // Результат деятельности ssconvert
	        }
	        
	        
	        
	        // ASCII замены
	        $new_replacements = array();
	        foreach($task['conversion']['column_replacements'] AS $col => $replacements) {
		        foreach($replacements AS $from => $to) {
			        $from = preg_replace('#ASCII(\d+)#ise', 'chr($1)', $from);
			        $to = preg_replace('#ASCII(\d+)#ise', 'chr($1)', $to);
			        
			        $new_replacements[$col][$from] = $to;
		        }
	        }
	        $task['conversion']['column_replacements'] = $new_replacements;
	        
	        
	        
	        /*
	         * Пропустим пару строк?
	         */
	        for ($i = 0; $i < $task['conversion']['skip_lines']; $i++) {
		        $data = fgetcsv($handle, 1000, $separator);
	        }

	        self::log('String-by-string conversion started');
	        
	        
            /*
             * Построчно конвертируем данные
             */
            $i = 0;
            while (($data = fgets($handle)) !== false) {

                // remove escapes
                $data = str_getcsv(stripcslashes($data), $separator);

                $i++;

                // Столбцы для замены.
                $replaces = array();

                // Определение полей в CSV.
                foreach ($columns as $index => $key) {
                    if ($key >= 0) {
                        // удалить обратные слеши в конце строки
                        $replaces[$index] = @trim($data[$key]);
                    } else {
                        $replaces[$index] = '';
                    }
                }

                /*
                 * Автозамены.
                 */
                foreach ($columns as $index => $key) {

                    // Замены в столбцах.
                    if (isset($task['conversion']['column_replacements'][$index]) && count($task['conversion']['column_replacements'][$index]) > 0) {
                        $replaces[$index] = str_replace(array_keys($task['conversion']['column_replacements'][$index]), array_values($task['conversion']['column_replacements'][$index]), $replaces[$index]);

                    }

                    // Перезапись в столбцах.
                    if (isset($task['conversion']['column_replacements_all'][$index])) {
                        $replaces[$index] = $task['conversion']['column_replacements_all'][$index];
                    }
                }


                /*
                 * Новая CSV строка
                 */
                $fields = array_values($replaces);

                //array_splice($fields, 5, 0, '');

                fputcsv($handle_w, $fields, ';');


                if($i % 50000 == 0) {
                    self::log($i . ' entries converted');
                }
            }

	        fclose($handle);
	        fclose($handle_w);

	        self::log('String-by-string conversion completed');

            /*
             * Если это тестовый режим, то отправим письмо и переместим прайс в папку для тестов.
             */
            if ($task['mode'] == LinemediaAutoTask::MODE_TEST) {
                $import_file = $task['supplier_id'] . '_' . basename($filename);
                $import_name = '/upload/linemedia.auto/pricelists/testing/' . $import_file;
                self::log('Conversion completed, move new csv to testing. '.$i.' strings, moved to '.$import_name);
                rename($filename . '.result', $_SERVER['DOCUMENT_ROOT'].$import_name);

                if (!empty($task['email'])) {
                    $arEventFields = array(
                        'EMAIL'         => strval($task['email']),
                        'TESTED_FILE'   => '/bitrix/admin/fileman_admin.php?set_filter=Y&path=/upload/linemedia.auto/pricelists/testing/&find_name='.$import_file,
                        'STANDARD_FILE' => '/bitrix/admin/fileman_admin.php?set_filter=Y&path=/upload/linemedia.auto/pricelists/&find_name='.self::STANDARD_PRICELIST_URL
                    );

                    $rsSites = CSite::GetList($b="sort", $o="asc", array());
                    while ($arSite = $rsSites->Fetch()) {
                        CEvent::SendImmediate('LM_AUTO_TASK_PRICE_TEST', $arSite['ID'], $arEventFields);
                    }
                    unset($rsSites, $arSite, $arEventFields);
                }
            } else {
    			/*
    			 * Отправим файл на импорт
    			 * Первой составляющей имени файла должен быть ID поставщика
    			 */
    			$import_name = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/'.$task['supplier_id'].'_'.basename($filename);
    			self::log('Conversion completed, move new csv to import. '.$i.' strings, moved to '.$import_name);
    			rename($filename . '.result', $import_name);
			}

			/*
			 * Удалим файл - источник конвертации
			 */
			unlink($filename);

			self::log('End task ' . $task['title'] . ' completed.');

			return true;
		}
    }





    /**
     * Распаковка архива
     */
    private function unzipFile($filename, $task)
    {
    	/*
    	* ZIP ли?
    	*/
    	$info = pathinfo($filename);
    	if($info['extension'] != 'zip') {
	    	return $filename;
    	}

    	self::log('ZIP detected');

    	if(!LinemediaAutoTasker::isUnzipSupported()) {
	    	self::log('ERROR! Missing unZip ability');
	    	return;
    	}

		$settings = $task['conversion'];
		
    	// распакуем
    	$tmp_folder = 'tmp_' . rand(0, 99999999);
		$path = dirname($filename) . '/' . $tmp_folder;

        if(strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $cmd = 'unzip ' . escapeshellarg($filename) . ' -d ' . $path;
        } else {
            $cmd = 'DISPLAY=:0 unzip ' . escapeshellarg($filename) . ' -d ' . $path;
        }

		$cmd_result = shell_exec($cmd);

	    /*
	     * Переименуем файлы, чтобы выглядели корректно русские названия
	     */
		foreach (glob($path . '/*.*') as $file) {

			$newfile = iconv("cp1252", "cp850", $file);
			$newfile = iconv("cp866", "utf-8", $newfile);

			$file = str_replace(' ', '\ ', $file);
			$newfile = str_replace(' ', '\ ', $newfile);

			$cmd = 'mv ' . $file . ' ' . $newfile;
			shell_exec($cmd);
		}


		self::log('Unzipped to ' . $path);


		$pattern = $path . '/*' . $settings['zip_content_filename'] . '*';
		self::log('Search for: ' . $pattern);

	    //поищем по маске
		foreach(glob($pattern) AS $unzipped_file) {

			$info = pathinfo($unzipped_file);
			if(!in_array($info['extension'], array('csv', 'xls', 'xlsx', 'txt')))
				continue;

			self::log('File found: ' . $unzipped_file);

			//переименуем, избавившись от русских названий
			self::log('Rename ' . $unzipped_file . ' to ' . dirname($unzipped_file). '/' . $task['id'] . '_' . md5($unzipped_file) . '.' . $info['extension']);

			rename($unzipped_file, dirname($unzipped_file) . '/' . $task['id'] . '_' . md5($unzipped_file) . '.' . $info['extension']);

			//новое название файла после переименования
			$unzipped_file = dirname($unzipped_file) . '/' . $task['id'] . '_' . md5($unzipped_file) . '.' . $info['extension'];

			// скопируем файл повыше
			$new_filename = dirname(dirname($unzipped_file)) . '/' . basename($unzipped_file);
			rename($unzipped_file, $new_filename);

			self::log('Converted to ' . $new_filename);

			self::log('Moved to ' . $new_filename);

			break;
		}

		//удалим папку
		$this->_rrmdir($path);

		// удалим zip
		unlink($filename);

		return $new_filename;
    }





    /**
     * Конвертация файла.
     */
    private function convert2CSV($filename, $from = 'xls')
    {
    	switch ($from) {
	    	case 'xls':
	    	case 'xlsx':

                if(strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                    $cmd = 'ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename . '.csv');
                } else {
                    $cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename . '.csv');

                    self::log('Conversion command "' . $cmd);
                }

                $cmd_result = shell_exec($cmd);

		        if (trim($cmd_result) != '') {
		          self::log('Conversion command "' . $cmd . '" returned:<br>' . $cmd_result."<br>");

		          if(stripos($cmd_result, 'aborting') !== false || stripos($cmd_result, 'killed') !== false) {
			          self::log('Error converting excel document!');
			          return false;
		          }
                }
		        unlink($filename);
		        return $filename . '.csv';
                break;
	    	default:
	    		return $filename;
    	}
    }


    /**
     * Лог.
     */
    public static function log($str)
    {
    	if(self::$output_debug)
		    echo date('G:i:s') . ' - ' . $str . "\n";
    }



	private function _rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") rmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}


}


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
CModule::IncludeModule('linemedia.autodownloader');
LinemediaAutoDownloaderConverterAgent::run();
*/
 
 
IncludeModuleLangFile(__FILE__);

class LinemediaAutoDownloaderConverterAgent// extends LinemediaAutoDownloaderDownloadAgentAll
{
    static $run_string = 'LinemediaAutoDownloaderConverterAgent::run();';
    
    /**
     * ???????? ??????? ??????
     */
    public static function run() 
    {
    	if (!CModule::IncludeModule('linemedia.auto')) {
    		return;
        }
        
    	/*
		 * ? ???? ?? ?????
		 */
		if (!LinemediaAutoImportAgent::checkCron()) {
			$ar = Array(
			   "MESSAGE" => GetMessage('LM_AUTO_DOWNLOADER_NEED_CRON'),
			   "TAG" => "LM_NEED_CRON",
			   "MODULE_ID" => "linemedia.autodownloader",
			   "ENABLE_CLOSE" => "N"
			);
			$ID = CAdminNotify::Add($ar);
			
			/*
			* ????????? ????????? ?????? ?? ?? ??? ?????
			*/
			return self::$run_string;
			
		} else {
			CAdminNotify::DeleteByTag("LM_NEED_CRON");
		}
        
		$agent = new LinemediaAutoDownloaderConverterAgent();	
		$tasks = $agent->getPendingTasks();
		
		/*
		 * ??? ?????
		 */
		if (count($tasks) == 0) {
			return self::$run_string;
        }
		
		foreach ($tasks as $task_id => $files) {
			$agent->executeTask($task_id, $files);
		}
		
		/*
         * C?????? ????????? ????????? ??????
         * ???? ?? ????????????? ????? ? ???????
         */
        $events = GetModuleEvents("linemedia.autodownloader", "OnAfterPricelistsConvert");
		while ($arEvent = $events->Fetch()) {
		    try {
			    ExecuteModuleEventEx($arEvent, array(count($files), $files));
			} catch (Exception $e) {
			    throw $e;
			}
	    }
        
        return self::$run_string;
	}
	
	
    /**
     * ????????? ????????? ?????.
     */
	public function getPendingTasks()
	{
		/*
		 * ????? ??? ???????????
		 */
		$files = scandir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/');
		$files = array_slice($files, 2);
		
		$tasks = array();
		foreach ($files as $filename) {
			$file_data = explode('_', $filename);
			$task_id = $file_data[0];
			$tasks[$task_id][] = $filename;
		}
		return $tasks;
    }
    
    
    /**
     * ????????? ????? ?????? ? ????????? ??????.
     */
    private function executeTask($task_id, $files = array())
    {
		$task_res = LinemediaAutoDownloaderTask::GetByID($task_id);
		$task = $task_res->Fetch();
		try {
			$task['conversion'] = unserialize($task['conversion']);
		} catch (Exception $e) {
			$task['conversion'] = array();
		}
		
		/*
		 * ?????????? ?? ???? ??????
		 */
		foreach ($files as $file) {
			self::log('Start task ' . $task['title'] . ', filename is ' . $file);
			
			$filename = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/' . $file;
			
			if (filesize($filename) == 0) {
				self::log('File skipped! Zero filesize ' . $filename);
				rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting_error/' . $file);
				continue;
			}
			
			if (!is_readable($filename)) {
				self::log('File skipped! Not readable ' . $filename);
				try {
					rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting_error/' . $file);
				} catch (Exception $e) {
					// ohh (((
				}
				continue;
			}
			
			
			/*
			 * ?????????? ???? ? ????? ??? ???????????
			 */
			self::log("Move $file to converting folder");
			rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting/' . $file);
			$filename = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting/' . $file;
			
			
			/*
			 * ??????? ??? ?????
			 */
			
			/*
			 * ???? ?? ???????? ??????????? ?????!
			 */
			if (!LinemediaAutoDownloaderMain::isConversionSupported()) {
				/*
				 * ???? ??? ?? csv
				 */
				if ($task['conversion']['type'] != 'csv') {
					/*
					 * ?? ? ???? ?? CSV
					 */
					$fileinfo = pathinfo($filename);
					if ($fileinfo['extension'] != 'csv') {
						self::log('File skipped! Conversion not supported (install ssconvert) ' . $filename);
						rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting_error/' . $file);
						continue;
					}
				}
			}
			
			
			/*
			 * ??????? ? CSV
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
				
				// ???????????????
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
                            break;
						default:
							/*
							 * ?? ??????? ?????????? ??? ?????
							 */
							self::log('File skipped! Couldn\'t detect filetype of ' . $file);
							rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting_error/' . $file);
							continue 3;
                            break;
					}
                    break;
			}
			//return; //=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//=//
			/*
			 * ?????? ?????????
			 */
			switch($task['conversion']['encoding']) {
				case 'Windows-1251':
					$from = 'cp1251';
			        $cmd = 'iconv -f ' . $from . ' -t utf8 "' . escapeshellarg($filename) . '" -o "' . escapeshellarg($filename) . '.tmp"';
			        $cmd_result = shell_exec($cmd);
			        unlink($filename);
			        rename($filename . '.tmp', $filename);
                    break;
                    
				case 'utf-8':
				    break;
				
				// ???????????????
				case '':
				default:
					$cmd = 'file -bi ' . escapeshellarg($filename);
			        $cmd_result = shell_exec($cmd);
			        
			        $response = explode(';', $cmd_result);
			        $charset  = explode('=', $response[1]);
			        $encoding = trim($charset[1]);
			        if ($encoding != 'utf-8') {
			            self::log('File encoding is ' . $encoding . ', changing to utf-8 with iconv');
			            
			            $from = 'cp1251';
				        $cmd = 'iconv -f ' . $from . ' -t utf8 "' . escapeshellarg($filename) . '" -o "' . escapeshellarg($filename) . '.utf"';
				        $cmd_result = shell_exec($cmd);
				        unlink($filename);
				        rename($filename . '.utf', $filename);
			        }
                    break;
			}
	        
			/*
			 * ?????? ???????????
			 */
			
			// ????????? ???? ?? ??????
	        try {
	            $handle = fopen($filename, "r");
	        } catch (Exception $e) {
	            self::log("Error opening pricelist $filename " . $e->GetMessage());
	            rename($filename, $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converting_error/' . $file);
	            continue;
	        }
	        
	        // ????????? ???? ?? ??????
	        try {
	            $handle_w = fopen($filename . '.result', "w");
	        } catch (Exception $e) {
	            fclose($handle);
	            self::log("Error opening new file to write " . $e->GetMessage());
	            continue;
	        }
	        
	        /*
	         * ????????? ??????.
	         */
	        @setlocale(LC_ALL, "ru_RU");
	        
	        /*
	         * ????????? ????????
	         * ??????? ?????????? ? ????, ??????? ???????? 1 ?? ???????? ????????????? ????????
	         */
	        $columns = $task['conversion']['columns'];
	        foreach ($columns as $code => $val) {
	        	$columns[$code] = $val - 1;
            }
	        
	        /*
	         * ???????????
	         */
	        if ($task['conversion']['type'] == 'csv') {
		        $separator = $task['conversion']['separator'] ? $task['conversion']['separator'] : ';';
	        } else {
	        	$separator = ','; // ????????? ???????????? ssconvert
	        }

	        /*
	         * ????????? ???? ??????
	         */
	        for ($i = 0; $i < $task['conversion']['skip_lines']; $i++) {
		        $data = fgetcsv($handle, 1000, $separator);
	        }
	        
	        
	        
	        /*
	         * ????????? ???????????? ??????
	         */
	        $i = 0;
	        while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
	            $i++;
	            
                
                /*
                 * ??????? ??? ??????.
                 */
                $replaces = array();
                
                
                /*
                 * ??????????? ????? ? CSV
                 */
                foreach ($columns as $index => $key) {
                    $replaces[$index] = @trim($data[$key]);
                }
                
                
	            /*
	             * ??????????
	             */
                foreach ($columns as $index => $key) {
                    // ?????? ? ????????
                    if (isset($task['conversion']['column_replacements'][$index]) && count($task['conversion']['column_replacements'][$index]) > 0) {
                        $replaces[$index] = str_replace(array_keys($task['conversion']['column_replacements'][$index]), array_values($task['conversion']['column_replacements'][$index]), $replaces[$index]);
                    }
                    
                    // ?????????? ? ????????
                    if (isset($task['conversion']['column_replacements_all'][$index])) {
                        $replaces[$index] = $task['conversion']['column_replacements_all'][$index];
                    }
                }
                
                
                /*
                 * ????? CSV ??????
                 */
                $fields = array_values($replaces);
	            
	            array_splice($fields, 5, 0, '');
                
                self::log('Result FIELDS: ' . print_r($fields, true));
                
	            fputcsv($handle_w, $fields, ';');
	            
	        }
	        
	        fclose($handle);
	        fclose($handle_w);
			
			
			/*
			 * ???????? ???? ?? ??????
			 * ?????? ???????????? ????? ????? ?????? ???? ID ??????????
			 */
			$import_name = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/new/' . $task['supplier_id'] . '_' . basename($filename);
			self::log('Conversion completed, move new csv to import. '.$i.' strings, moved to '.$import_name);
			rename($filename . '.result', $import_name);
			
			/*
			 * ?????? ???? - ???????? ???????????
			 */
			unlink($filename);
			
			self::log('End task ' . $task['title'] . ' completed.');
			
			return true;
		}
		
		
    }
    
    
    /**
     * ??????????? ?????.
     */
    private function convert2CSV($filename, $from = 'xls')
    {
    	switch($from) {
	    	case 'xls':
	    	case 'xlsx':
	    		$cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename) . '.csv';
		        $cmd_result = shell_exec($cmd);
		        
		        if (trim($cmd_result) != '') {
		          self::log('Conversion command ' . $cmd . ' returned ' . $cmd_result);
                }
		        unlink($filename);
		        return $filename . '.csv';
                break;
	    	default:
	    		return $filename;
    	}
    }
    
    
    /**
     * ???.
     */
    public static function log($str)
    {
    	$str = date('G:i:s d-m-Y') . ' - ' . $str . "\n";
	    $fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/converter.log', 'a');
		fwrite($fp, $str);
		fclose($fp);
		echo $str;
    }
    
}

<?php
/**
 * Linemedia Autoportal
 * Main module
 * Import prices agent
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
/*
CModule::IncludeModule('linemedia.autodownloader');
LinemediaAutoDownloaderDownloadAgent::run();
*/
 
 
IncludeModuleLangFile(__FILE__);

/**
 * Class LinemediaAutoDownloaderDownloadAgent
 */
class LinemediaAutoDownloaderDownloadAgent // extends LinemediaAutoDownloaderDownloadAgentAll
{
    /**
     * Строка запуска агента
     * @var string
     */
    static $run_string = 'LinemediaAutoDownloaderDownloadAgent::run();';
    
    
    // выводить ли лог?
    // используется для проверки агента из админки
    static $output_debug = true;
    
    
    /**
     * Запуск агента
     * @param bool $test - если true - будет работать без крона
     * @return string
     * @throws Exception
     */
    public static function run($test = false)
    {
    	if (!CModule::IncludeModule('linemedia.auto')) {
    		return;
        }

    	
    	/*
		 * А есть ли крон?
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
			 * Запрещено выполнять импорт не из под крона
			 */
			if(!$test) return self::$run_string;
			
		} else {
			CAdminNotify::DeleteByTag("LM_NEED_CRON");
		}
        
		$now = time();
		
		/*
		 * Выберем все автоматические задачи и проверим, нужно ли их выполнять?
		 */
		$sheduled_tasks = array();
		$shedule_obj = new LinemediaAutoTaskShedule();
		$shedule_res = $shedule_obj->GetList(array(), array('active' => 'Y'));
		while ($shedule = $shedule_res->Fetch()) {
			/*
			 * Запускаем задачу насильно
			 */
			if ($shedule['force_run_now'] == 1) {
				$sheduled_tasks[] = $shedule;
				
				
				$shedule_obj->Update($shedule['id'], array('force_run_now' => 0));
				
				continue;
			}
			

			/*
			 * Задачи с интервалом 0 запускаются только вручную с force_run_now
			 */
			if ($shedule['interval'] < 1) {
				continue;
            }
			
			$last_exec = strtotime($shedule['last_exec']);
			
			$start_time = explode(':', $shedule['start_time']);
			$start_time_seconds = ($start_time[0] * 60 * 60) + ($start_time[1] * 60) + $start_time[2];
			$today_seconds = $now - strtotime('today');
			
			switch ($shedule['interval']) {
				case 3600:
					if ($now < $last_exec + 3600) {
						continue 2;
                    }
				    break;
				
				case 86400:
					/*
					 * Подходит ли день (понедельник - №1, а не №0)
					 */
					$days = array_map('intval', explode(',', $shedule['days']));
					$weekday = date('w')+1;
					if (!in_array($weekday, $days)) {
						continue 2;
                    }
                    
					/*
					 * Подошло ли время
					 */
					if ($today_seconds < $start_time_seconds) {
						continue 2;
                    }
                    
					/*
					 * А может сегодня задача уже запускалась?
					 */
					if ($last_exec > strtotime('today')) {
						continue 2;
                    }
				    break;
				
				case 2592000: // месяц 
					/*
					 * последний день месяца?
					 */
					if ($shedule['start_day'] == 'last') {
						if (date('d') < 28) {
							continue 2;
                        }
					} else {
						/*
						 * Тот ли сегодня день
						 */
						if ($shedule['start_day'] != date('d')) {
							continue 2;
                        }
					}
					
					/*
					 * Подошло ли время
					 */
					if ($today_seconds < $start_time_seconds) {
						continue 2;
                    }
                    
					/*
					 * А может сегодня задача уже запускалась?
					 */
					if ($last_exec > strtotime('today')) {
						continue 2;
                    }
                    break;
			}
			
			$sheduled_tasks[] = $shedule;
		}
    
        /*
         * Делать нечего
         */
        if (count($sheduled_tasks) == 0) {
        	//self::log('No tasks');
	        return self::$run_string;
        }
        
        /*
         * Найдём задачи
         */
        $task_obj = new LinemediaAutoTask();
        $tasks_to_run = array();
        foreach ($sheduled_tasks as $shedule) {
	        $task = $task_obj->GetByID($shedule['task_id']);
	        $task = $task->Fetch();

	        if($task['protocol'] == 'file') continue;

            $task['connection'] = unserialize(strVal($task['connection']));
            $task['conversion'] = unserialize(strVal($task['conversion']));

            if(!is_array($task['connection'])) $task['connection'] = array();
            if(!is_array($task['conversion'])) $task['conversion'] = array();
	        
	        $task['shedule'] = $shedule;
	        
	        $tasks_to_run[] = $task;
        }
        
        
        /*
         * Запускаем качальщик
         */
        foreach ($tasks_to_run as $task) {

            if(!is_array($task['connection'])) {
                self::log('Bad task ' . print_r($task, true));
                throw new Exception('Bad connection data!');
            }

        	$protocol = $task['connection']['protocol'];
        	
        	self::log('Start task ' . $task['title'] . ' with protocol ' . $protocol);

            try {
	            $downloader = new LinemediaAutoDownloaderMain($protocol, $task['connection'][$protocol]);
	            $downloader->setTaskData($task);
            } catch (Exception $e) {
                self::log('Error start task ' . $task['title'] . ': ' . $e->GetMessage());
            }
	        
	        try {
		        $filename = $downloader->download();
	        } catch (Exception $e) {
		        self::log('Error 2 task ' . $task['title'] . ': ' . $e->GetMessage());
	        }
	        $shedule_obj = new LinemediaAutoTaskShedule();
	        $shedule_obj->Update($task['shedule']['id'], array('last_exec' => ConvertTimestamp(false, 'FULL')));
	        
	        self::log('Completed task ' . $task['title'] . ' with protocol ' . $protocol . ', filename is ' . $filename . '<a target="_blank" href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Fupload%2Flinemedia.auto%2Fpricelists%2Fpending&">[view]</a>');
        }
        
        
        /*
         * Cобытие окончания отработки агента
         * Были ли импортированы файлы и сколько
         */
        $events = GetModuleEvents("linemedia.autodownloader", "OnAfterPricelistsImport");
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
     * Лог.
     * @param $str
     */
    public static function log($str)
    {
    	if(self::$output_debug)
		    echo date('G:i:s') . ' - ' . $str . "\n";
    }
    
}

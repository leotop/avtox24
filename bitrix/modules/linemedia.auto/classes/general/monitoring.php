<?php
/**
 * Linemedia Autoportal
 * Main module
 * Pinba monitoring
 *
 * @author  Linemedia
 * @since   14/07/2014
 *
 * @link    http://auto.linemedia.ru/
 */


/*
 * Pinba for bitrix
 */
IncludeModuleLangFile(__FILE__);

/**
* Класс отвечает за отправку данных о производительности на сервер мониторинга
* 
* $timer = LinemediaAutoMonitoring::startTimer(array('scope' => 'db', 'module' => 'linemedia.auto', 'action' => 'main-search'));
* performAction();
* LinemediaAutoMonitoring::stopTimer($timer);
* 
*/
class LinemediaAutoMonitoring
{
	
	static $starttime = false;
	
	/**
	* Начало страницы
	*/
	public static function startPage()
	{
		if(!self::isEnabled()) return;
		
		ini_set('pinba.server', 'monitoring.linemedia.ru');
		
		// ЧПУ
		if($_SERVER['SCRIPT_NAME'] == '/bitrix/urlrewrite.php') {
			if(isset($_SERVER['REAL_FILE_PATH'])) {
				pinba_script_name_set($_SERVER['REAL_FILE_PATH']);
			} else {
				pinba_script_name_set($_SERVER['REQUEST_URI']);
			}
		}
	}
	
	
	/**
	* Запуск таймера
	* @param array $tags Теги таймера
	*/
	public static function startTimer(array $tags = array())
	{
		if(!self::isEnabled()) return;
		
		return pinba_timer_start($tags);
		
	}
	
	
	/**
	* Остановка таймера
	*/
	public static function stopTimer($timer)
	{
		if(!self::isEnabled()) return;
		
		return pinba_timer_stop($timer);
		
	}
	
	
	public static function isEnabled()
	{
		if(extension_loaded('pinba')) {
			return true;
		} else {
			return false;
			
			// php implementation
			/*if(!self::$starttime) {
				self::$starttime = microtime(true);
			}
			
			//$filename = $_SERVER['DOCUMENT_ROOT'] . getLocalPath("modules/linemedia.auto/classes/general/pinba_php/lib/prtbfr.php");
			$filename = $_SERVER['DOCUMENT_ROOT'] . getLocalPath("modules/linemedia.auto/classes/general/pinba_php/lib/Protobuf-PHP/library/DrSlump/Protobuf.php");
			require_once($filename);
			$filename = $_SERVER['DOCUMENT_ROOT'] . getLocalPath("modules/linemedia.auto/classes/general/pinba_php/lib/pinba.php");
			require_once($filename);
			$filename = $_SERVER['DOCUMENT_ROOT'] . getLocalPath("modules/linemedia.auto/classes/general/pinba_php/lib/pinbafunctions.php");
			require_once($filename);
			return true;
			*/
		}
	}
}

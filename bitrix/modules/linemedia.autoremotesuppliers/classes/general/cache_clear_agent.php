<?php
/**
 * Linemedia Auto
 * Remote suppliers module
 * Cache clear agent
 *
 * @author  Linemedia
 * @since   25/07/2014
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoRemoteSuppliersCacheClearAgent
{
	/**
	 * @var string Строка запуска агента
	 */
	static $run_string = 'LinemediaAutoRemoteSuppliersCacheClearAgent::run();';

	/**
	 * @var string Путь к кешу удаленных поставщиков
	 */
	static $path = '/bitrix/cache/lm_auto/remote_suppliers/';
	
	
	
	/**
	 * Основной метод запуска агента
	 * @param bool $test
	 * @return string
	 */
	public static function run($test = false)
	{
		@set_time_limit(0);

		/*
		 * Очищаем буферизацию.
		 */
		while (ob_get_level()) {
			ob_end_flush();
		}
		flush();

		if (CModule::IncludeModule('linemedia.auto')) {
			/*
			 * А не запущен ли уже крон?
			 */
			if (!LinemediaAutoModule::canRunAnotherCron()) {
				return self::$run_string;
			}
		}

		/*
		 * Проверяем класс битрикса для работы с кешем.
		 */
		if (!class_exists("CFileCacheCleaner")) {
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/cache_files_cleaner.php');
		}

		$curentTime = time();

		if (php_sapi_name() == 'cli' || defined("BX_CRONTAB") && BX_CRONTAB === true) {
			/*
			 * Если на кроне, то работаем максимум 120 секунд.
			 */
			$endTime = time() + 120;
		} else {
			/*
			 * Если на хитах, то не более секунды.
			 */
			$endTime = time() + 1;
		}

		 /*
		  * Работаем со всем кешем.
		  */
		$obCacheCleaner = new CFileCacheCleaner("all");

		if (!$obCacheCleaner->InitPath(self::$path)) {
			/*
			 * Произошла ошибка.
			 */
			return self::$run_string;
		}

		$obCacheCleaner->Start();

		while ($file = $obCacheCleaner->GetNextFile()) {
			if (is_string($file)) {
				$date_expire = $obCacheCleaner->GetFileExpiration($file);

				if ($date_expire) {
					if ($date_expire < $curentTime) {
						unlink($file);
					}
				}

				if (time() >= $endTime) {
					break;
				}
			}

			/*
			 *  Не более 200 файлов за секунду (взято у битрикса).
			 */
			usleep(5000);
		}
		return self::$run_string;
	}
}

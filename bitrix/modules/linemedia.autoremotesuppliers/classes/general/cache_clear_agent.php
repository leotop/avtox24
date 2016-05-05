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
	 * @var string ������ ������� ������
	 */
	static $run_string = 'LinemediaAutoRemoteSuppliersCacheClearAgent::run();';

	/**
	 * @var string ���� � ���� ��������� �����������
	 */
	static $path = '/bitrix/cache/lm_auto/remote_suppliers/';
	
	
	
	/**
	 * �������� ����� ������� ������
	 * @param bool $test
	 * @return string
	 */
	public static function run($test = false)
	{
		@set_time_limit(0);

		/*
		 * ������� �����������.
		 */
		while (ob_get_level()) {
			ob_end_flush();
		}
		flush();

		if (CModule::IncludeModule('linemedia.auto')) {
			/*
			 * � �� ������� �� ��� ����?
			 */
			if (!LinemediaAutoModule::canRunAnotherCron()) {
				return self::$run_string;
			}
		}

		/*
		 * ��������� ����� �������� ��� ������ � �����.
		 */
		if (!class_exists("CFileCacheCleaner")) {
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/cache_files_cleaner.php');
		}

		$curentTime = time();

		if (php_sapi_name() == 'cli' || defined("BX_CRONTAB") && BX_CRONTAB === true) {
			/*
			 * ���� �� �����, �� �������� �������� 120 ������.
			 */
			$endTime = time() + 120;
		} else {
			/*
			 * ���� �� �����, �� �� ����� �������.
			 */
			$endTime = time() + 1;
		}

		 /*
		  * �������� �� ���� �����.
		  */
		$obCacheCleaner = new CFileCacheCleaner("all");

		if (!$obCacheCleaner->InitPath(self::$path)) {
			/*
			 * ��������� ������.
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
			 *  �� ����� 200 ������ �� ������� (����� � ��������).
			 */
			usleep(5000);
		}
		return self::$run_string;
	}
}

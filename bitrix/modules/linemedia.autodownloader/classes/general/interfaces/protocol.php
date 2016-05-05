<?php
/**
 * Linemedia Autoportal
 * Downloader module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
/**
 * Интерфейс протокола для скачивания.
 * Interface LinemediaAutoDownloaderIProtocol
 */
interface LinemediaAutoDownloaderIProtocol
{
    /**
     * Подключение протокола
     * @param $protocols
     * @return mixed
     */
    public static function inclusion(&$protocols);
}

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
 * ��������� ��������� ��� ����������.
 * Interface LinemediaAutoDownloaderIProtocol
 */
interface LinemediaAutoDownloaderIProtocol
{
    /**
     * ����������� ���������
     * @param $protocols
     * @return mixed
     */
    public static function inclusion(&$protocols);
}

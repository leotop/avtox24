<?php
/**
 * Linemedia Autoportal
 * Downloader module
 * Uninstall step
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
/*
var_dump($this->UnInstallDB());
var_dump($this->UnInstallEvents());
var_dump($this->UnInstallFiles());
var_dump($this->RemoveIblocks());
exit;*/


if($_POST['REMOVE_DB'] == 'Y')
{
	if(!$this->UnInstallDB())
	{
		throw new Exception('Error remove module');
		return false;
	}
}

if($_POST['REMOVE_FILES'] == 'Y')
{
	DeleteDirFilesEx('/upload/linemedia.autodownloader/');
}

if (!$this->UnInstallEvents() || !$this->UnInstallFiles() || !$this->RemoveIblocks()) {
    throw new Exception('Error remove module');
    return;
}
UnRegisterModule( $this->MODULE_ID );

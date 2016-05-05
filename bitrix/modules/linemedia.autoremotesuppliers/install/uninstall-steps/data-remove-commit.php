<?php
/**
 * Linemedia Autoportal
 * Remote suppliers module
 * uninstall step
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
/*
var_dump($this->UnInstallDB());
var_dump($this->UnInstallEvents());
var_dump($this->UnInstallFiles());
var_dump($this->RemoveIblocks());
exit;*/

if (!$this->UnInstallDB() || !$this->UnInstallEvents() || !$this->UnInstallFiles() || !$this->RemoveIblocks()) {
    return;
}
UnRegisterModule( $this->MODULE_ID );

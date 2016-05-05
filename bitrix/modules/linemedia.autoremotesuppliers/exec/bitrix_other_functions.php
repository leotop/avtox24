<?
/*********************************************************************
Файлы и каталоги
*********************************************************************/

function CheckDirPath($path, $bPermission = true)
{
	define('BX_DIR_PERMISSIONS', 0700);
	$path = str_replace(array("\\", "//"), "/", $path);

	//remove file name
	if (substr($path, -1) != "/") {
		$p = strrpos($path, "/");
		$path = substr($path, 0, $p);
	}

	$path = rtrim($path, "/");

	if (!file_exists($path))
		return mkdir($path, BX_DIR_PERMISSIONS, true);
	else
		return is_dir($path);
}
<?php
/******************************** Bitrix language functions **************************************/

if(!function_exists('LangSubst')) {

    function LangSubst($lang)
    {
        static $arSubst = array('ua'=>'ru', 'kz'=>'ru', 'ru'=>'ru');
        if(isset($arSubst[$lang]))
            return $arSubst[$lang];
        return 'en';
    }
}

if(!function_exists('getLocalPath')) {

    function getLocalPath($path, $baseFolder = "/bitrix")
    {
        $root = rtrim($_SERVER["DOCUMENT_ROOT"], "\\/");

        static $hasLocalDir = null;
        if($hasLocalDir === null)
        {
            $hasLocalDir = is_dir($root."/local");
        }

        if($hasLocalDir && file_exists($root."/local/".$path))
        {
            return "/local/".$path;
        }
        elseif(file_exists($root.$baseFolder."/".$path))
        {
            return $baseFolder."/".$path;
        }
        return false;
    }
}

if(!function_exists('IncludeModuleLangFile')) {

    function IncludeModuleLangFile($filepath, $lang=false, $bReturnArray=false)
    {
        $filepath = rtrim(preg_replace("'[\\\\/]+'", "/", $filepath), "/ ");
        $module_path = "/modules/";
        if(strpos($filepath, $module_path) !== false)
        {
            $pos = strlen($filepath) - strpos(strrev($filepath), strrev($module_path));
            $rel_path = substr($filepath, $pos);
            $p = strpos($rel_path, "/");
            if(!$p)
                return false;

            $module_name = substr($rel_path, 0, $p);
            $rel_path = substr($rel_path, $p+1);
            $BX_DOC_ROOT = rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ ");
            $module_path = $BX_DOC_ROOT.getLocalPath($module_path.$module_name);
        }
        elseif(strpos($filepath, "/.last_version/") !== false)
        {
            $pos = strlen($filepath) - strpos(strrev($filepath), strrev("/.last_version/"));
            $rel_path = substr($filepath, $pos);
            $module_path = substr($filepath, 0, $pos-1);
        }
        else
        {
            return false;
        }

        if($lang === false)
            $lang = LANGUAGE_ID;

        $lang_subst = LangSubst($lang);

        $arMess = array();
        if($lang_subst <> $lang && file_exists(($fname = $module_path."/lang/".$lang_subst."/".$rel_path)))
        {
            $arMess = __IncludeLang($fname, $bReturnArray, true);
        }
        if(file_exists(($fname = $module_path."/lang/".$lang."/".$rel_path)))
        {
            $msg = __IncludeLang($fname, $bReturnArray, true);
            if(is_array($msg))
                $arMess = array_merge($arMess, $msg);
        }

        if($bReturnArray)
            return $arMess;
        return true;
    }
}

if(!function_exists('GetMessage')) {

    function GetMessage($name, $aReplace=false)
    {
        global $MESS;
        if(isset($MESS[$name]))
        {
            $s = $MESS[$name];
            if($aReplace!==false && is_array($aReplace))
                foreach($aReplace as $search=>$replace)
                    $s = str_replace($search, $replace, $s);
            return $s;
        }
        return $name;
    }
} // if(!function_exists('GetMessage'))

if(!function_exists('__IncludeLang')) {
	function __IncludeLang($path, $bReturnArray=false, $bFileChecked=false)
	{
		global $ALL_LANG_FILES;
		$ALL_LANG_FILES[] = $path;

		if($bReturnArray)
			$MESS = array();
		else
			global $MESS;

		if($bFileChecked || file_exists($path))
			include($path);

		//read messages from user lang file
		static $bFirstCall = true;
		if($bFirstCall)
		{
			$bFirstCall = false;
			$fname = getLocalPath("php_interface/user_lang/".LANGUAGE_ID."/lang.php");
			if($fname !== false)
			{
				$arMess = __IncludeLang($_SERVER["DOCUMENT_ROOT"].$fname, true, true);
				foreach($arMess as $key=>$val)
					$GLOBALS["MESS"][str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"].$key))] = $val;
			}
		}

		//redefine messages from user lang file
		$path = str_replace("\\", "/", realpath($path));
		if(isset($GLOBALS["MESS"][$path]) && is_array($GLOBALS["MESS"][$path]))
			foreach($GLOBALS["MESS"][$path] as $key=>$val)
				$MESS[$key] = $val;

		if($bReturnArray)
			return $MESS;
		else
			return true;
	}
}


/******************************** end of Bitrix language functions **************************************/
<?php
/**
 * Linemedia Autoportal
 * Autodecdoc module
 * LinemediaAutoTecDocDebug
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 

IncludeModuleLangFile(__FILE__); 

/**
 *class allowing to put debug information into admin panel
 */
class LinemediaAutoTecDocDebug
{
    
    /**
     * main debug`s function
     * @param string $message
     * @param string $spoiler
     * @param string $priority
     */
    public function add($message, $spoiler = false, $priority = LM_AUTO_DEBUG_NOTICE) 
    {
    
        /*
        * В битриксе есть класс CDebugInfo;
        * возможно стоит его использовать
        */
    
        /*
        * Время отладки
        */
        $GLOBALS['last_debug_time'] = $GLOBALS['last_debug_time'] ? $GLOBALS['last_debug_time'] : microtime(true);
        
        
        if(!CUser::IsAdmin() || $_REQUEST['lm_auto_debug']!=='Y') return;
        
        $min_debug = ($_REQUEST['debug']) ? $_REQUEST['debug'] : LM_AUTO_DEBUG_WARNING;
        if($priority < $min_debug) return;
        
        
        $str = '<div class="bx-component-debug">' . $message;
        
        /*
        * Прошло времени
        */
        $now = microtime(true);
        $diff = ($now - $GLOBALS['last_debug_time']);
        $diff = sprintf('%.4f', $diff);
        $diff = ($diff > 0.1) ? "<b>$diff</b>" : $diff;
        
        $str .= "<br><nobr>$diff s</nobr>";
        
        if($spoiler) {
            $id = 'lm_dbg_' . mt_rand(0, 99999999);
            $str .= " <a href='javascript:;' onclick=\"document.getElementById('$id').style.display = (document.getElementById('$id').style.display == 'none') ? 'block' : 'none'\">+</a><pre id='$id' style=\"display:none\">$spoiler</pre>";
            
            
        }
        
        
        $str .= "</div>";
        
        if(!defined('AJAX')) echo $str;
        
        $GLOBALS['last_debug_time'] = $now;
    }
    
    /**
     * checking whether variable SESS_SHOW_TIME_EXEC in session is set up
     * @return boolean
     */
    public static function enabled()
    {
        return $_SESSION["SESS_SHOW_TIME_EXEC"] == 'Y';
    }
}

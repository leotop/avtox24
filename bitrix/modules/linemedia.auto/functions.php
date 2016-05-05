<?php

/*
 * json_encode
 */
if (!function_exists('json_encode')) {
    function json_encode($data) {
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                return '"' . addslashes($data) . '"';
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed []= json_encode($value);
                    $output_associative []= json_encode($key) . ':' . json_encode($value);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(',', $output_indexed) . ']';
                } else {
                    return '{' . implode(',', $output_associative) . '}';
                }
            default:
                return '';
        }
    }
}


/*
 * json_decode
 */
if (!function_exists('json_decode')) {
    function json_decode($json) {
        $comment = false;
        $out = '$x=';
        for ($i = 0; $i < strlen($json); $i++) {
            if (!$comment) {
                if (($json[$i] == '{') || ($json[$i] == '[')) {
                    $out .= ' array(';
                } elseif (($json[$i] == '}') || ($json[$i] == ']')) {
                    $out .= ')';
                } elseif ($json[$i] == ':') {
                    $out .= '=>';
                } else {
                    $out .= $json[$i];
                }
            } else {
                $out .= $json[$i];
            }
            if ($json[$i] == '"' && $json[($i-1)]!="\\")    $comment = !$comment;
        }
        eval($out . ';');
        return $x;
    }
}


/*
 * Сколнение числительных.
 */
if (!function_exists('numstr')) {
	function numstr($number, $titles, $include = false)
	{
		$cases = array(2, 0, 1, 1, 1, 2);
		$string = $titles[($number % 100 > 4 && $number %100 < 20) ? 2 : $cases[min($number%10, 5)]];
		if ($include) {
			$string = $number.' '.$string;
		}
		return $string;
	}
}

if(!function_exists('format_by_count')) {
    // ex. format_by_count($daysLeft, 'день', 'дня', 'дней')
    function format_by_count($count, $form1, $form2, $form3)
    {
        $count = abs($count) % 100;
        $lcount = $count % 10;
        if ($count >= 11 && $count <= 19) return($form3);
        if ($lcount >= 2 && $lcount <= 4) return($form2);
        if ($lcount == 1) return($form1);
        return $form3;
    }
}


/*
 * debug
 */
if (!function_exists('_d')) {
    function _d($a = null, $die = true) {
        while(ob_get_level())
            ob_end_clean();
        
        echo '<pre>';
        print_r($a);
        echo '</pre>';
        if($die) exit;
    }
}

// add custom stat
// global $APPLICATION;
// $APPLICATION->AddHeadScript('http://api.auto-expert.info/api.js');

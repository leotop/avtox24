<?php
/**
 * Преобразует строки в utf8
 * @param $data
 * @return array|string
 */
if (!function_exists('convertToUtf8')) {
    function convertToUtf8($data)
    {

        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = convertToUtf8($value);
            }
        } else if (is_string($data)) {
            $data = mb_convert_encoding($data, "utf-8", "windows-1251");
        }
        return $data;
    }
}

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

/**
 * Безопасная для windows-1251 обертка для json_encode
 * @param $data
 * @return string
 */
if (!function_exists('safe_json_encode')) {
    function safe_json_encode($data, $options = 0, $depth = 512)
    {

        if (!defined('BX_UTF') || BX_UTF != true) {

            $data = convertToUtf8($data);
        }

        if ($options != 0) {
            $json_str = json_encode($data, $options, $depth);
        } else if (defined('JSON_UNESCAPED_UNICODE')) {
            $json_str = json_encode($data, JSON_UNESCAPED_UNICODE, $depth);
        } else {
            $json_str = json_encode($data, $options, $depth);
        }

        if (!defined('BX_UTF') || BX_UTF != true) {

            return mb_convert_encoding($json_str, "windows-1251", "utf-8");
        }

        return $json_str;
    }
}

/**
 * Безопасная для windows-1251 обертка для htmlspecialchars
 * https://bugs.php.net/bug.php?id=61354
 */
if(!function_exists('safe_htmlspecialchars')) {

    function safe_htmlspecialchars($string, $flags = null, $encoding = null, $double_encode = true) {

        if(is_null($flags)) {
            $flags = ENT_COMPAT | ENT_HTML401;
        }

        if(is_null($encoding)) {
            if (defined('BX_UTF') && BX_UTF == true) {
                $encoding = 'UTF-8';
            } else {
                $encoding = 'cp1251';
            }
        }

        return htmlspecialchars($string, $flags, $encoding, $double_encode);
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

if (!function_exists('extract_zip_file')) {

    function extract_zip_file($archive_path, $in_archive_path, $dest_file) {

        if(file_exists($archive_path)) {

            $handle_from = fopen('zip://' . $archive_path . '#' . $in_archive_path, 'rb');

            $dest_dir = pathinfo($dest_file, PATHINFO_DIRNAME);
            if(!file_exists($dest_dir)) {
                mkdir($dest_dir, BX_DIR_PERMISSIONS, true);
            }

            $handle_to = fopen($dest_file, 'w');

            if($handle_from && $handle_to) {
                while (!feof($handle_from)) {
                    fwrite($handle_to, fread($handle_from, 8192));
                }
            }
            fclose($handle_from);
            fclose($handle_to);

            return file_exists($dest_file);
        }
        return false;
    }
}

if (!function_exists('fineText')) {

    function fineText($text, $break = false, $maxLen = false) {

        $outStr = false;
        $text = strip_tags($text);
        $text = htmlspecialchars_decode($text);

        $text = preg_replace('/([,.;])(?=\D)/', '$1 ', $text);
        if($break) $text = str_replace("\r", '<br />', $text);
        $text = preg_replace('|\s+|', ' ', $text);
        $text = trim($text);

        if($maxLen && strlen($text) > $maxLen) {
            $text = substr($text, 0, $maxLen-3) . "...";
        }

        return $text;
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

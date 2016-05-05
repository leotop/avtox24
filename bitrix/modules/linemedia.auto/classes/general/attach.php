<?php

/**
 * Linemedia Autoportal
 * Main module
 * LinemediaAutoAttach class
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


/**
 * LinemediaAutoAttach class allow possibility to attach files to email
 */
class LinemediaAutoAttach
{
    
	/**
	 * attach files to email
	 * @param string $event
	 * @param string $lid
	 * @param array $arFields
	 * @param array $filePath
	 * @param string $fileName
	 */
    public static function SendAttach($event, $lid, $arFields, $filePath, $fileName = null)
    {
              
        
        global $DB;
        
        $event = $DB->ForSQL($event);
        $lid = $DB->ForSQL($lid);
    
        $rsMessTpl = $DB->Query("SELECT * FROM `b_event_message` WHERE `EVENT_NAME` LIKE '$event' AND `LID` LIKE '$lid'");
            
        while ($arMessTpl = $rsMessTpl->Fetch()) {
            // get charset

            
            $strSql = "SELECT CHARSET FROM `b_lang` WHERE LID = '$lid' ORDER BY DEF DESC, SORT;";
            $dbCharset = $DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
            $arCharset = $dbCharset->Fetch();
            $charset = $arCharset["CHARSET"];
            
            // additional params
            if (isset($arFields['SITE_ID'])) {
                $siteFeatures = CSite::GetByID($arFields['SITE_ID'])->Fetch();
                $arFields["DEFAULT_EMAIL_FROM"] = $siteFeatures['EMAIL'];
            }
            elseif (!isset($arFields["DEFAULT_EMAIL_FROM"])) {
                $arFields["DEFAULT_EMAIL_FROM"] = COption::GetOptionString("main", "email_from", "admin@".$GLOBALS["SERVER_NAME"]);
            }
            
          if (!isset($arFields["SITE_NAME"])) {
                $site = CSite::GetByID($lid)->Fetch();
                $arFields["SITE_NAME"] = $site['SITE_NAME'];
            }
            if (!isset($arFields["SERVER_NAME"])) {
                $arFields["SERVER_NAME"] = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
            }
            
            // replace
            $from = CAllEvent::ReplaceTemplate($arMessTpl["EMAIL_FROM"], $arFields);
            $to = CAllEvent::ReplaceTemplate($arMessTpl["EMAIL_TO"], $arFields);
            $message = CAllEvent::ReplaceTemplate($arMessTpl["MESSAGE"], $arFields);
            $subj = CAllEvent::ReplaceTemplate($arMessTpl["SUBJECT"], $arFields);
            $bcc = CAllEvent::ReplaceTemplate($arMessTpl["BCC"], $arFields);
    
            preg_match('/(?<=<p>).*(?=<\/p>)/', $message, $info);
            $message = $info[0]; 
            
            $from = trim($from, "\r\n");
            $to = trim($to, "\r\n");
            $subj = trim($subj, "\r\n");
            $bcc = trim($bcc, "\r\n");
            
            if (COption::GetOptionString("main", "convert_mail_header", "Y")=="Y") {
                $from = CAllEvent::EncodeMimeString($from, $charset);
                $to = CAllEvent::EncodeMimeString($to, $charset);
                $subj = CAllEvent::EncodeMimeString($subj, $charset);
            }
            
            $all_bcc = COption::GetOptionString("main", "all_bcc", "");
            if ($all_bcc != "") {
                $bcc .= (strlen($bcc)>0 ? "," : "") . $all_bcc;
                $duplicate = "Y";
            } else {
                $duplicate = "N";
            }
            
            $strCFields = "";
            if (!empty($arSearch)) {
                $cSearch = count($arSearch);
                foreach ($arSearch as $id => $key) {
                    $strCFields .= substr($key, 1, strlen($key)-2)."=".$arReplace[$id];
                    if ($id < $cSearch - 1) {
                        $strCFields .= "&";
                    }
                }
            }
            
            if (COption::GetOptionString("main", "CONVERT_UNIX_NEWLINE_2_WINDOWS", "N") == "Y") {
                $message = str_replace("\n", "\r\n", $message);
            }
            
            
            // read file(s)
            $arFiles = array();
            if (!is_array($filePath)) {
                $filePath = array($filePath);
            }
            foreach ($filePath as $fPath) {
                $arFiles[] = array(
                    'F_PATH' => $_SERVER['DOCUMENT_ROOT'].$fPath,
                    'F_LINK' => $f = fopen($_SERVER['DOCUMENT_ROOT'].$fPath, "rb")
                );
            }
            
            $un = strtoupper(uniqid(time()));
            $eol = CAllEvent::GetMailEOL();
            $head = $body = "";
    
            // Заголовок
            $head .= "Mime-Version: 1.0". $eol;
            $head .= "From: $from". $eol;
            if (COption::GetOptionString("main", "fill_to_mail", "N")=="Y") {
                $header = "To: $to".$eol;
            }
            $head .= "Reply-To: $from".$eol;
            $head .= "X-Priority: 3 (Normal)".$eol;
            $head .= "X-MID: $messID.".$arMessTpl["ID"]."(".date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL"))).")".$eol;
            $head .= "X-EVENT_NAME: ISALE_KEY_F_SEND".$eol;
            if (strpos($bcc, "@") !== false) {
                $head .= "BCC: $bcc".$eol;
            }
            $head .= "Content-Type: multipart/mixed; ";
            $head .= "boundary=\"----".$un."\"".$eol.$eol;
    
            // Тело пиьсма.
            $body = "------".$un.$eol;
            if ($arMessTpl['BODY_TYPE'] == "text") {
                $body .= "Content-Type:text/plain; charset=".$charset.$eol;
            } else {
                $body .= "Content-Type:text/html; charset=".$charset.$eol;
            }
            $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
            $body .= $message.$eol.$eol;
            
            $fileName = (!empty($fileName)) ? (strval($fileName)) : ($arF['F_PATH']);
            
            foreach ($arFiles as $arF) {
                    
                $body .= "------".$un.$eol;
                $body .= "Content-Type: application/octet-stream; name=\"".basename($arF["F_PATH"])."\"".$eol;
                $body .= "Content-Disposition:attachment; filename=\"".basename($arF["F_PATH"])."\"".$eol;
                $body .= "Content-Transfer-Encoding: base64".$eol.$eol;
                $body .= chunk_split(base64_encode(fread($arF["F_LINK"], filesize($arF["F_PATH"])))).$eol.$eol;
                
            }
            $body .= "------".$un."--";
            
            // Отправка.
            if (!defined('ONLY_EMAIL') || $to == ONLY_EMAIL) {
                bxmail($to, $subj, $body, $head, COption::GetOptionString('main', 'mail_additional_parameters', ''));
            }
        }
    }
        
}

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
 
IncludeModuleLangFile(__FILE__); 
 
if (!CModule::IncludeModule('linemedia.auto')) {
    return;
}

/**
 * Протокол передачи данных HTTP
 * Class LinemediaAutoDownloaderEmailProtocol
 */
class LinemediaAutoDownloaderEmailProtocol extends LinemediaAutoProtocol implements LinemediaAutoDownloaderIProtocol
{
    /**
     * Заголовок
     * @var string
     */
    public static $title = 'E-mail';
    /**
     * Логин
     * @var string
     */
    protected $login;
    /**
     * Пароль
     * @var string
     */
    protected $password;
    /**
     * Сервер
     * @var string
     */
    protected $imap_server;
    /**
     * Порт
     * @var int
     */
    protected $imap_port;
    /**
     * Флаг SSL
     * @var bool
     */
    protected $imap_ssl;
    /**
     * Оригинальное имя файла
     * @var
     */
    protected $original_filename;

    /**
     * Урл
     * @var
     */
    protected $url;

    /**
     * Создает объект, инициализирует параметры соединения
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->login       = trim($data['LOGIN']);
        $this->password    = trim($data['PASSWORD']);
        
        $this->imap_server = trim($data['IMAP_SERVER']);
        $this->imap_port   = $data['IMAP_PORT'] ? intval($data['IMAP_PORT']) : 993;
        $this->imap_ssl    = (bool) $data['IMAP_SERVER'];
        
        $this->from_email    = (string) $data['FROM'];
        $this->attachment_name    = (string) $data['ATTACHMENT_NAME'];
    }

    /**
     * Скачивание файла
     * @param bool $test - проверка подключения
     * @return bool|string
     */
    public function download($test = false)
    {
        
        $connection_string = "{" . $this->imap_server . ":" . $this->imap_port . "/imap" . ($this->imap_ssl ? '/ssl' : '') . "}INBOX";
        
       	$imap_connection = imap_open($connection_string, $this->login, $this->password, 0, 3);
        if(!$imap_connection) {
	        return 'Email error: ' . imap_last_error() . '';
        }
        
        
        $emails = imap_search($imap_connection, 'UNSEEN');
        
        
        
        if ($test) {
        	return true;
        }
        
		if($emails) {
		
			self::log('Found ' . count($emails) . ' emails');
			
			foreach($emails as $email_number) {
				$overview = imap_fetch_overview($imap_connection, $email_number);
                $from_email = imap_rfc822_parse_adrlist($overview[0]->from, '');
                if(!is_array($from_email)) {
                    self::log('Error parse "' . $overview[0]->from . "'");
                    continue;
                }
                $from = $from_email[0]->mailbox . '@' .  $from_email[0]->host;

                /*$from = $overview[0]->from;
                if(strpos($from, '<') !== false) {
                    preg_match('#<(.+?)>#is', $overview[0]->from, $from);
                    $from = $from[1];
                }*/

				self::log('Check email from ' . $from);
				
				if($this->from_email != $from) {
					self::log('Not that sender');
					continue;
				}
				
				// наш клиент! качаем!
				
				$structure = imap_fetchstructure($imap_connection, $email_number);

                $attachments = array();

                /*
                if(!$structure->parts) {

                    $attachments[0] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );

                    if($structure->ifdparameters) {
                        foreach($structure->dparameters as $object) {
                            if(strtolower($object->attribute) == 'filename') {
                                $attachments[0]['is_attachment'] = true;
                                $attachments[0]['filename'] = $object->value;
                            }
                        }
                    }

                    if($structure->ifparameters) {
                        foreach($structure->parameters as $object) {
                            if(strtolower($object->attribute) == 'name') {
                                $attachments[0]['is_attachment'] = true;
                                $attachments[0]['name'] = $object->value;
                            }
                        }
                    }

                    if($attachments[0]['is_attachment']) {
                        $attachments[0]['attachment'] = imap_body($imap_connection, $email_number);

                        if($structure->encoding == 3) { // 3 = BASE64
                            $attachments[0]['attachment'] = base64_decode($attachments[0]['attachment']);
                        }
                        elseif($structure->encoding == 4) { // 4 = QUOTED-PRINTABLE
                            $attachments[0]['attachment'] = quoted_printable_decode($attachments[0]['attachment']);
                        }
                    } else {
                        unset($attachments[0]);
                    }
                }
                */
				
				if(isset($structure->parts) && count($structure->parts)) {
				
					for($i = 0; $i < count($structure->parts); $i++) {
				
						$attachments[$i] = array(
							'is_attachment' => false,
							'filename' => '',
							'name' => '',
							'attachment' => ''
						);
						
						if($structure->parts[$i]->ifdparameters) {
							foreach($structure->parts[$i]->dparameters as $object) {
								if(strtolower($object->attribute) == 'filename') {
									$attachments[$i]['is_attachment'] = true;
									$attachments[$i]['filename'] = $object->value;
								}
							}
						}
						
						if($structure->parts[$i]->ifparameters) {
							foreach($structure->parts[$i]->parameters as $object) {
								if(strtolower($object->attribute) == 'name') {
									$attachments[$i]['is_attachment'] = true;
									$attachments[$i]['name'] = $object->value;
								}
							}
						}
						
						if($attachments[$i]['is_attachment']) {
							$attachments[$i]['attachment'] = imap_fetchbody($imap_connection, $email_number, $i+1);
							
							if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
								$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
							}
							elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
								$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
							}
						} else {
							unset($attachments[$i]);
						}
						//бывает, что в dparameters от mail.ru приходит пустой Content-Disposition: filename
						if(empty($attachments[$i]['filename'])){
							$attachments[$i]['filename'] = $attachments[$i]['name'];
						}
						
					}
				}
				
				$attachments = array_values($attachments);
				if(count($attachments) == 0) {
					self::log('No attachments');
					continue;
				}
				
				foreach($attachments AS $attachment) {
					// =?UTF-8?B?0JvQldCe0JzQoSDQntCe0J5fMDMwOTIwMTQ=?=
					$attachment['filename'] = iconv_mime_decode($attachment['filename'], ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
					
					if($this->attachment_name) {
						$regex = str_replace('*', '(.+?)', $this->attachment_name);
						$regex = '#'.$regex.'#is';
						
                        /*
                         * For ru names
                         */
                        $regexRu = str_replace('*', '(.+?)', iconv('CP1251', 'UTF-8', $this->attachment_name));
                        $regexRu = '#'.$regexRu.'#is';

						if(!preg_match($regex, $attachment['filename']) && !preg_match($regexRu, $attachment['filename'])) {
							self::log('Not sought attachment name ' . $this->attachment_name);
							continue;
						}
						
					}

                    //установим оригинальное имя
                    $this->original_filename = $attachment['filename'];

                    //если пусто ориг. имя, то считаем что там csv (иначе в нек. случаях вообще не работает, а так работает хотя бы для csv)
                    if(!$this->original_filename) {
                        $this->original_filename = 'price.csv';
                    }

                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/')) {
                        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 0777, true);
                    }
					
					// md5 filename to prevent bad utf etc
					$pathinfo = pathinfo($attachment['filename']);
					$temp_filename = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.autodownloader/downloaded/', 'lm_auto_downloader_email_') . md5($attachment['filename']) . '.' . $pathinfo['extension'];
					
					$fp = fopen($temp_filename, 'w');
					fputs($fp, $attachment['attachment']);
					fclose($fp);
					
					self::log('Attachment downloaded: ' . $temp_filename);
					// мы возвращаем только один аттач
					return $temp_filename;
					
				}
				
				self::log('No necessary attachment');
				
				
			}// next email
		} else {
			self::log('No new emails');
		}
        
    }

    /**
     * Введён емейл, надо поискать серверы порты подключения
     * @param $data
     * @return string
     */
    public function ajaxEmailEntered($data)
    {
	    $email = trim(strval($data['EMAIL']));
	    
	    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	    	return "$('.protocol-email-smtp').show(); $('.protocol-email-pop3').show();";
	    
	    $email_parts = explode('@', $email);
	    $domain = $email_parts[1];
	    
	    $dns = dns_get_record($domain, DNS_MX);
	    $mx_domain = $dns[0]['target'];
	    if(!$mx_domain)
	    	return;
	    
	    // GMail
	    // https://support.google.com/mail/troubleshooter/1668960?rd=1#ts=1665119,1665162
	    if(preg_match('#google#is', $mx_domain)) {
		    $pop_server = 'imap.gmail.com';
		    $pop_ssl = true;
		    $pop_port = 993;
		    
		    return "
		    	$('#email_LOGIN').val('".$email."');
		    	$('#email_IMAP_SERVER').val('".$pop_server."');
		    	$('#email_IMAP_PORT').val('".$pop_port."');
		    	$('#email_IMAP_SSL').prop('checked', '".$pop_ssl."');
		    ";
	    }
	    
	    // Yandex
	    // http://help.yandex.ru/mail/mail-clients.xml
	    if(preg_match('#yandex#is', $mx_domain)) {
		    $pop_server = 'imap.yandex.ru';
		    $pop_ssl = true;
		    $pop_port = 993;
		    return "
		    	$('#email_LOGIN').val('".$email_parts[0]."');
		    	$('#email_IMAP_SERVER').val('".$pop_server."');
		    	$('#email_IMAP_PORT').val('".$pop_port."');
		    	$('#email_IMAP_SSL').prop('checked', '".$pop_ssl."');
		    ";
	    }
	    
	    // Mail.ru
	    // http://help.mail.ru/mail-help/mailer/popsmtp
	    if(preg_match('#mxs.mail.ru#is', $mx_domain)) {
		    $pop_server = 'imap.mail.ru';
		    $pop_ssl = true;
		    $pop_port = 993;
		    return "
		    	$('#email_LOGIN').val('".$email."');
		    	$('#email_IMAP_SERVER').val('".$pop_server."');
		    	$('#email_IMAP_PORT').val('".$pop_port."');
		    	$('#email_IMAP_SSL').prop('checked', '".$pop_ssl."');
		    ";
	    }

	    // Rambler
	    // http://help.rambler.ru/mail/mail-pochtovye-klienty/1276/
	    if(preg_match('#rambler#is', $mx_domain)) {
		    $pop_server = 'imap.rambler.ru';
		    $pop_ssl = true;
		    $pop_port = 993;
		    return "
		    	$('#email_LOGIN').val('".$email."');
		    	$('#email_IMAP_SERVER').val('".$pop_server."');
		    	$('#email_IMAP_PORT').val('".$pop_port."');
		    	$('#email_IMAP_SSL').prop('checked', '".$pop_ssl."');
		    ";
	    }

	    // Yahoo
	    // 
	    if(preg_match('#yahoo#is', $mx_domain)) {
		    $pop_server = 'imap.mail.yahoo.com';
		    $pop_ssl = true;
		    $pop_port = 993;
		    return "
		    	$('#email_LOGIN').val('".$email."');
		    	$('#email_IMAP_SERVER').val('".$pop_server."');
		    	$('#email_IMAP_PORT').val('".$pop_port."');
		    	$('#email_IMAP_SSL').prop('checked', '".$pop_ssl."');
		    ";
	    }

	    //default
	    return "
	    	$('#email_LOGIN').val('".$email_parts[0]."');
	    	$('#email_IMAP_SERVER').val('".$mx_domain."');
	    	$('#email_IMAP_PORT').val('993');
	    	$('#email_IMAP_SSL').prop('checked', 'true');
	    ";

	    
	    
    }

    /**
     * Получение конфигурации.
     * @return array
     */
    public static function getConfigVars()
    {
        return array(
            'EMAIL' => array(
                'title' => GetMessage('EMAIL'),
                'type' => 'string',
                'placeholder' => 'name@site.com',
                'size' => 60,
                'required' => true,
                'onchange' => 'emailEntered',
            ),
            'LOGIN' => array(
                'title' => GetMessage('LOGIN'),
                'type'  => 'string',
                'required' => true,
            ),
            'PASSWORD' => array(
                'title' => GetMessage('PASSWORD'),
                'type' => 'password',
                'required' => true,
            ),
            'IMAP_SERVER' => array(
                'title' => GetMessage('IMAP_SERVER'),
                'type' => 'string',
                'required' => true,
            ),
            'IMAP_PORT' => array(
                'title' => GetMessage('IMAP_PORT'),
                'type' => 'string',
            ),
            'IMAP_SSL' => array(
                'title' => GetMessage('IMAP_SSL'),
                'type' => 'checkbox',
            ),
            'FROM' => array(
                'title' => GetMessage('EMAIL_FROM'),
                'description' => GetMessage('EMAIL_FROM_DESCR'),
                'type' => 'string',
                'required' => true,
            ),
            'ATTACHMENT_NAME' => array(
                'title' => GetMessage('ATTACHMENT_NAME'),
                'description' => GetMessage('ATTACHMENT_NAME_DESCR'),
                'type' => 'string',
            ),

        );
    }

    /**
     * Доступны ли функции http (curl)?
     * @return bool
     */
    public static function available()
    {
    	//function_exists('dns_get_record') && 
        return function_exists('imap_open');
    }

    /**
     * Сообщение о требуемых функциях.
     * @return mixed
     */
    public static function getRequirements()
    {
        return GetMessage('EMAIL_REQUIREMENTS');
    }

    /**
     * Получить ригинальное имя файла
     * @return string
     */
    public function getOriginalFileName() {
        return basename($this->original_filename);
    }

    /**
     * Подключение текущего протокола.
     * @param $protocols
     */
    public static function inclusion(&$protocols)
    {
        $protocols['email'] = array(
            'classname' => __CLASS__,
            'available' => self::available(),
            'title'     => GetMessage('PROTOCOL_EMAIL'),
            'config'    => self::getConfigVars(),
            'upload'    => '/'.COption::GetOptionString('main', 'upload_dir', 'upload') . '/linemedia.autodownloader/new/',
        );
    }
    
}


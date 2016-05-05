<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Threads class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

/**
 * Requests
 * Class LinemediaAutoSuppliersThread
 */
class LinemediaAutoSuppliersThread
{
	protected $jobs = array();
	
	protected $handlers = array();
	
	protected $results = array();
	
	
	/**
	 * Добавление задачи (ID, файл, аргументы)
     * 
     * @param string $code
     * @param string $filename
     * @param array $args
	 */
    public function addJob($code, $filename, $args = array(), $timeout = 30)
    {
	    $this->jobs[$code] = array(
	    	'filename' 	=> $_SERVER['DOCUMENT_ROOT'] . $filename,
	    	'args'		=> $args,
		    'timeout'   => $timeout
	    );
    }
    
    
    /**
     * Запуск задач
     */
    public function execute()
    {
        
    	/*
    	 * Запустим задачи
    	 */
	    foreach ($this->jobs as $code => $job) {
	    	$filename = $job['filename'];
	    	
	    	
	    	$oldLocale = setlocale(LC_CTYPE, "UTF8", "en_US.UTF-8");
	    	$args = array_map('escapeshellarg', $job['args']);
	    	if ($oldLocale) {
	    		setlocale(LC_CTYPE, "UTF8", $oldLocale);
            }
	    	
	    	$args = join(' ', $args);


            $command = 'timeout '.$job['timeout'].' php ' . $filename . ' ' . $args . ' 2>&1';
		
		    LinemediaAutoDebug::add('popen: ' . $command);
		    $this->handlers[$code] = popen($command, 'r');



		    if(!is_resource($this->handlers[$code])) {
			    $this->results[$code] = 'Can\'t run background search process';
		    }
		    stream_set_blocking($this->handlers[$code], 0);
	    }
	    
	    /*
	     * Считаем ответ
	     */
	    foreach ($this->jobs as $code => $job) {
	    	while (is_resource($this->handlers[$code]) && !feof($this->handlers[$code])) {
				$this->results[$code] .= fread($this->handlers[$code], 10000);
			}
	    }
	    
	    /*
	     * Закроем процессы
	     */
	    foreach ($this->jobs as $code => $job) {
	    	if(is_resource($this->handlers[$code]))
		    	pclose($this->handlers[$code]);
	    }
	    
    }

    /**
     * Получение результата
     * @return array
     */
    public function getResults()
    {
	    return $this->results;
    }
    
}
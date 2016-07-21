<?php

	if(!is_null(null)) {
		echo 'null';
	} else {
		echo 'not null';
	}
	
	$ch = curl_init('http://api.auto-expert.info');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	1);
	curl_setopt($ch, CURLOPT_HEADER, 			1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 			2);
	curl_setopt($ch, CURLOPT_FAILONERROR, 		1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 		1);
	curl_setopt($ch, CURLOPT_ENCODING,			'gzip');
	$start = microtime(true);
	curl_exec($ch);
	$result = microtime(true) - $start;
	var_dump($result);
	curl_close($ch);
	var_dump("==============================================================");
	
	
	$uagent = "Linemedia API Client (avtox24.ru) [777]";
	$data = array
        (
            'article' => 02126,
            'tecdoc_crosses' => 1,
            'tecdoc_crosses_original' => 1,
            'linemedia_crosses' => 1,
            'oem_sought_only' => 1
        );
	$agent = array(
		'ip' => $_SERVER['REMOTE_ADDR'],
		'uagent' => $_SERVER['HTTP_USER_AGENT'],
	);
	$curlh = curl_init('http://api.auto-expert.info/?cmd=getAnalogs2Multiple&sig=80bb7231&out=json&id=777&v=0.1.0');
	curl_setopt($curlh, CURLOPT_RETURNTRANSFER, 	1);
	curl_setopt($curlh, CURLOPT_HEADER, 			0);
	curl_setopt($curlh, CURLOPT_FOLLOWLOCATION, 	1);
	curl_setopt($curlh, CURLOPT_USERAGENT, 		$uagent);
	curl_setopt($curlh, CURLOPT_CONNECTTIMEOUT, 	3);
	curl_setopt($curlh, CURLOPT_TIMEOUT, 			30);
	curl_setopt($curlh, CURLOPT_FAILONERROR, 		1);
	curl_setopt($curlh, CURLOPT_AUTOREFERER, 		1);
	curl_setopt($curlh, CURLOPT_ENCODING,			'gzip');
	curl_setopt($curlh, CURLOPT_POST, 				true);
	curl_setopt($curlh, CURLOPT_POSTFIELDS, 		http_build_query(array('data' => $data, 'agent' => $agent)));
	$start2 = microtime(true);
	curl_exec($curlh);
	$result2 = microtime(true) - $start2;
	var_dump($result2);
	curl_close($curlh);
	var_dump(PHP_EOL . "==========================end end end====================================");
	
	
?>
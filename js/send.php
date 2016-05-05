<?php
if ($_POST) {
	$send = $_POST['submit'];
	$name = $_POST['name'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$messagee = $_POST['messagee'];
	$skype = $_POST['skype'];
	
 $json = array();
	$to = "hr@avtox24.ru";
	$subject = "Заявка с сайта avtox24.ru Вакансии ";        
	$headers = 'Content-Type: text/plain; charset=utf-8' . "\r\n" . 'From: <info@avtox24.ru>' . 'avtox24'. "\r\n";
	$headers .= "Bcc:instait.freelance@yandex.ru\n";
	
	$msg .= "Заявка с сайта avtox24.ru Вакансии\n\nИмя: $name ";
	$msg .= "\nПочта отправителя: $email ";	
	$msg .= "\nТелефон: $phone ";	
	$msg .= "\nSkype: $skype ";	
	$msg .= "\nСообщение: $messagee ";	
	mail ($to, $subject, $msg, $headers);
	$json['error'] = 0; // ошибок не было
	echo json_encode($json);
} else { // если массив POST не был передан
	echo 'GET LOST!'; // высылаем
}

?>
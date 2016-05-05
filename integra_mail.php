<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


echo 'I am : ' . `whoami`; echo '<br>';

$from = 'integra_test@' . $_SERVER['SERVER_NAME'];
$to      = 'wildtest.integra@gmail.com';
$subject = 'subject';
$message = 'some kind of text';
$headers = 'From: ' . $from . "\r\n" .
    'Reply-To: ' . $from . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$result = mail($to, $subject, $message, $headers);

if($result){
    echo 'it works';
} else {
    echo 'none';
}
?>

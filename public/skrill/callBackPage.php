<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

//save response to log file
$logmessage = date('Y-m-d H:i:s')." skrill response: ".json_encode($_POST)."\n";
file_put_contents(dirname(dirname(__DIR__)).'/logs/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

$amount = $_POST['amount'];
$currency = $_POST['currency'];
$transactionId = $_POST['transaction_id'];
$status = $_POST['status'];
$message = $_POST['status_msg'];
$exttransid = $_POST['mb_transaction_id'];

$paymentData = get_payment_details($transactionId);

if($status == 2){
	$status = "OK";
}else if($status == 0){
	$status = "PENDING";
}else if($status == -1){
	$status = "CANCELLED";
}else{
	$status = "KO";
}

update_payments_status($transactionId, $paymentData['player_id'], $status, $message, $exttransid);

?>
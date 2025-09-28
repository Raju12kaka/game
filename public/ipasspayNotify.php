<?php 
include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';

//save response to log file
$logmessage = date('Y-m-d H:i:s')." ipasspay response: ".json_encode($_POST)."\n";
file_put_contents(dirname(dirname(__DIR__)).'/logs/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

$orderstatus = $_POST['order_status'];
$orderid = $_POST['oid'];
$transactionid = $_POST["pid"];
$orderamount = $_POST["order_amount"]*100;
$ordercurrency = $_POST["order_currency"];
$hashinfo = $_POST["hash_info"];


$paymentData = get_payment_details($orderid);
if($paymentData && $paymentData['status'] != 'OK'){

	if($orderstatus == 2){
		$status = "OK";
		$charges = 1;
	}else if($orderstatus == 1){
		$status = "PENDING";
		$charges = 2;
	}else if($orderstatus == 6){
		$status = "KO";
		$charges = 0;
	}else if($orderstatus == 3){
		$status = "Authorized";
	}else{
		$status = "KO";
		$charges = 0;
	}
	$player = get_player_all_details($paymentData['player_id']);
	$totalamount = $orderamount + $player->balance;
	
	update_payments_status($transactionId, $paymentData['player_id'], $status, $message, $transactionid, $charges, $orderamount, $totalamount);
	if($status == 'OK'){
		$_PARAMS['player_id'] = $player->id;
		$_PARAMS['amount'] = $orderamount;
		update_player_balance($_PARAMS);
	}
}

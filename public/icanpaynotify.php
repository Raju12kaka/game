<?php 
$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
array_pop($documentroot);
array_push($documentroot, 'logs');
$root_path = implode('/', $documentroot);

if($_POST)
{
	$getdata = $_POST;
	/**  Log message start **/
  	$logmessage = date('Y-m-d H:i:s')." icanpay 3ds response callback data POST :".json_encode($getdata)."\n";
  	file_put_contents($root_path.'/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  	/**  Log message end **/
	
}
else if($_GET) {
	/**  Log message start **/
  	$logmessage = date('Y-m-d H:i:s')." icanpay 3ds response callback data GET :".$getdata."\n";
  	file_put_contents($root_path.'/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  	/**  Log message end **/
}
else {
	$getdata = file_get_contents('php://input');
	/**  Log message start **/
  	$logmessage = date('Y-m-d H:i:s')." icanpay 3ds response callback data file_get_contents :".$getdata."\n";
  	file_put_contents($root_path.'/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  	/**  Log message end **/	
}

include_once dirname(dirname(__FILE__)).'/models/common.php';
include_once dirname(dirname(__FILE__)).'/models/define.php';
if($_POST)
{
	$responseData = $_POST;
	$orderNo			= substr($responseData['orderid'], 2);//return tradeNo
	$payment_details 	= get_payment_details($orderNo);
	
	file_put_contents($root_path.'/payments_icanpay.log', "payment details :: ".json_encode($payment_details).PHP_EOL , FILE_APPEND | LOCK_EX);
    $_PARAMS = get_params($payment_details['payment_request_id']);
    file_put_contents($root_path.'/payments_icanpay.log', "payment details request id :: ".$payment_details['payment_request_id'].PHP_EOL , FILE_APPEND | LOCK_EX);
    $get_player_details = get_player_all_details($payment_details['player_id']);
    
    if($payment_details['status'] == "INITIATED")
    { 
	    if($responseData['status'] == 1 && !$responseData['errorcode'])
	    { 
	    	$status 			= "OK";
			$_RESULT['status'] 	= 'Success';
			$_RESULT['html'] 	= "Authorised";
	    	$_RESULT['errorcode']= 0;
			$orderNo = $payment_details['id'];
			$charges = 1;
			//$amount = $payment_details_byforeignid['amount'];
			//$totalamount = $payment_details_byforeignid['amount'];
			$amount = ($responseData['amount']*100);
			$totalamount = ($get_player_details->balance)+($payment_details['player_currency_amount']);
			$payment_descriptor = $responseData['descriptor'];
			$transaction_Id = $responseData['transactionid'];
			
			update_payments_status($orderNo, $payment_details['player_id'], $status, $_RESULT['html'], $transaction_Id, $charges, $amount, $totalamount, 'icanpay3ds');
			
			$player_amountupdate = array();
			$player_amountupdate['player_id'] = $payment_details['player_id'];
			$player_amount = $payment_details['player_currency_amount'];
			$player_amountupdate['amount'] = $player_amount;
				
			update_player_balance($player_amountupdate);
			update_payment_foreignid($orderNo, $transaction_Id);
			update_payment_discriptor($orderNo, $payment_descriptor);
	    }
		else
		{
			$status 			= "KO";
			$payment_id = $payment_details['id'];
			$errorcode = 207;
			$foreign_errorcode = $responseData['errorcode'];
			$transaction_id = $responseData['transactionid'];
			$message = $responseData['errormessage'];
			$amount = ($payment_details['amount']*100);
			$totalamount = $get_player_details->balance;
			$payment_descriptor = $responseData['descriptor'];
			$charges = 0;
			
			update_payments_status($payment_id, $payment_details['player_id'], $status, $message, $transaction_id, $charges, $amount, $totalamount);
			if($responseData['transactionid'])
			{
				update_payment_foreignid($payment_id, $responseData['transactionid']);	
			}
			
			if($payment_descriptor)
			{
				update_payment_discriptor($payment_id, $payment_descriptor);
			}
		}
		
	}
}
?>
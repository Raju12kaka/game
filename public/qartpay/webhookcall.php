<?php

  include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
  include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';


  $json_response_Data = $_POST;
  
   /**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." qartpay webhook callback data :".json_encode($json_response_Data)."\n";
  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  /**  Log message end **/
  
  $paymentid = $json_response_Data['orderId'];
  $payment_details = getPaymentDetails($paymentid);
  $params = get_params($payment_details->payment_request_id);
  $providerDetails = getProviderDetails($payment_details->payment_provider_id);
  $statusInfo = $json_response_Data['responseCode']." || ".$json_response_Data['responseMessage'];
  $orderInfo = $json_response_Data['status'];
  
  	if($json_response_Data['responseCode'] == '000'){
  		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised";
		$_RESULT['errorcode'] 	= 0;
		$status 				= "SUCCESS";
		$charges				= 1;
	}else{
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "DECLINED";
		$charges				= 0;
	}
	
	$player = get_player_all_details($payment_details->player_id);
	$amount = $payment_details->player_currency_amount;
	$totalamount = $amount + $player->balance;
	if($payment_details->status != 'SUCCESS'){
		
		update_payments_status($json_response_Data['orderId'], $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		if(!empty($json_response_Data['txnId']))
			update_payment_foreignid($json_response_Data['orderId'], $json_response_Data['txnId']);
		
		 if($json_response_Data['responseCode'] == '000'){
		 
		    $_PARAMS['player_id'] = $player->mstrid;
			$_PARAMS['amount'] = $amount;
			update_player_balance($_PARAMS);	
		 }
		
		
	}
	
	
	return true;
	
?>
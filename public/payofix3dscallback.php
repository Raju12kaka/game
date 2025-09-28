<?php
//$getdata = file_get_contents('php://input');

if(isset($_GET))
{
	$getdata = $_GET;
}

if($getdata)
{
	//$responseData = json_decode($getdata, true);
	$responseData = $getdata;
	
	include_once dirname(dirname(__FILE__)).'/models/common.php';
	include_once dirname(dirname(__FILE__)).'/models/define.php';

	/**  Log message start **/
  	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  	array_pop($documentroot);
  	array_push($documentroot, 'logs');
  	$root_path = implode('/', $documentroot);

  	$logmessage = date('Y-m-d H:i:s')." PAYOFIX 3DSecure callback data :".json_encode($getdata)."\n";
  	file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  	/**  Log message end **/
  	
  	$tradeNo		= $responseData['transaction_id'];//return tradeNo
	$orderNo		= substr($responseData['order_number'], 3);//return orderno
	$orderAmount	= $responseData['amount'];//return orderAmount
	$orderAmount	= number_format($orderAmount, 2, '.', '');
	$orderCurrency	= $responseData['currency'];//return orderCurrency
	$orderStatus	= $responseData['result'];//return orderStatus
	$orderInfo		= $responseData['message'];//return orderInfo
	$riskInfo		= $responseData['channel']."||".$orderStatus;//return riskInfo
	$acquirerInfo	= $responseData['descriptor'];//return acquirerInfo
	
	$payment_request_details = get_params_from_id($orderNo);
	//$payment_details_id = get_payment_id($payment_request_details['request_id']);
	$payment_details_id = get_payment_id_by_providerid($payment_request_details['request_id'], $tradeNo);
	$payment_details 	= getPaymentDetails($payment_details_id);
	
	if($payment_details)
	{
		$provider_id_value 		= $payment_details->payment_provider_id;
		$provider_details_arr 	= getProviderDetails($provider_id_value);
		$merchant_id_value 		= $provider_details_arr->credential1;
		$sha56key_value 		= $provider_details_arr->credential2;
		$provider_name_val 		= $provider_details_arr->name;		
		
		$signature = $orderStatus.$responseData['order_number'].$orderCurrency.$orderAmount.$sha56key_value;
		$signature = hash('sha256', $signature);

		$_PARAMS 		= get_params($payment_details->payment_request_id);
	}
	else {
		$_POST['Succeed'] 	= 0;
		$_POST['Result'] 	= "Pyment id not matched";
	}
	
	if($orderStatus == 1){
		$get_player_details = get_player_all_details($payment_details->player_id);	
		
		if($responseData['signature'] == $signature)
		{
			$status 			= "OK";
			$_RESULT['status'] 	= 'Success';
			$_RESULT['html'] 	= "Authorised";
        	$_RESULT['errorcode']= 0;
			$acuityte_status_val= 1;
			
			$logmessage = date('Y-m-d H:i:s')." PAYOFIX 3DSecure order id :".$payment_details_id."Signature matched"."\n";
  			file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  			$amount 		= ($orderAmount*100);
			$totalamount 	= (($orderAmount*100)+$get_player_details->balance);
		}
		else
		{
			$status 			= "KO";
			$_RESULT['status'] 	= 'Declined';
			$_RESULT['html'] 	= "Signature not matching";
        	$_RESULT['errorcode']= 401;
        	$logmessage = date('Y-m-d H:i:s')." PAYOFIX 3DSecure order id :".$payment_details_id."Signature not matched".$signature."\n";
  			file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  			$amount 		= ($orderAmount*100);
			$totalamount 	= ($get_player_details->balance);
		}
		$charges 		= 1;
		/*$amount 		= ($orderAmount*100);
		$totalamount 	= ($orderAmount*100);*/
		
    }
    else
    {
    	$get_player_details = get_player_all_details($payment_details->player_id);
    	$status 				= "KO";
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= $riskInfo;
        $_RESULT['html'] 		= $orderInfo."||".$riskInfo;			
		$charges 				= 1;
        $amount 				= ($orderAmount*100);
        $totalamount 			= $get_player_details->balance;
    }
    
    if($payment_details->status != "OK")
	{
			
		$logmessage = date('Y-m-d H:i:s')." PAYOFIX 3DSecure order id :".$payment_details_id."CUREENT STATUS::".$payment_details->status."\n";
  		file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		update_payments_status($payment_details_id, $payment_details->player_id, $status, $_RESULT['html'], $payment_details->payment_foreign_id, $charges, $amount, $totalamount);
		if($status == "OK")
		{
			$player_amountupdate = array();
			$player_amountupdate['player_id'] = $payment_details->player_id;
			$player_amountupdate['amount'] = $amount;
			update_player_balance($player_amountupdate);	
			updateVipStatus($payment_details->player_id);
			checkplayerrisklevel($payment_details->player_id);
			
		
		}
		//Update payment descriptor
		if($acquirerInfo)
		{
			update_payment_discriptor($orderNo, $acquirerInfo);
		}
	}
}
?>
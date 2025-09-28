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
	
    if(!isset($responseData['order_id'])){
    $_LOCATION_URL  = WEBREDIRECTURL.'?s=cancelled';
	?>	
	 <script language="javascript">
	parent.parent.location = "<?php echo $_LOCATION_URL; ?>"; 
    </script>
	<?php }
  
	/**  Log message start **/
  	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  	array_pop($documentroot);
  	array_push($documentroot, 'logs');
  	$root_path = implode('/', $documentroot);
    
  	$logmessage = date('Y-m-d H:i:s')." OCTAPAY 3DSecure callback data :".json_encode($getdata)."\n";
  	file_put_contents($root_path.'/payments_octapay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  	/**  Log message end **/
  	
  	$tradeNo		= $responseData['order_id'];//return tradeNo
	$orderNo		= substr($responseData['customer_order_id'], 2);//return orderno
	$orderStatus	= $responseData['status'];//return orderStatus
	$orderInfo		= $responseData['message'];//return orderInfo
	$riskInfo		= $responseData['message']."||".$orderStatus;//return riskInfo
	$acquirerInfo	= $responseData['message'];//return acquirerInfo
    
	$payment_request_details = get_params_from_id($orderNo);
	$payment_details_id = get_payment_id_by_requestid($payment_request_details['request_id']);
	
	$payment_details 	= getPaymentDetails($payment_details_id);
	
	$orderAmount	= $payment_details->amount/100;//return orderAmount
	$orderAmount	= number_format($orderAmount, 2, '.', '');
	$orderCurrency	= $payment_details->currency_id;//return orderCurrency
	if($payment_details)
	{
		

		$_PARAMS 		= get_params($payment_details->payment_request_id);
	}
	else {
		
	}
	include_once('newloader.php');
	if($orderStatus == 'success'){
		$get_player_details = get_player_all_details($payment_details->player_id);	
		
		
			$status 			= "OK";
			$_RESULT['status'] 	= 'Success';
			$_RESULT['html'] 	= "Authorised";
        	$_RESULT['errorcode']= 0;
			$acuityte_status_val= 1;
			
			$logmessage = date('Y-m-d H:i:s')." OCTAPAY 3DSecure order id :".$payment_details_id."Signature matched"."\n";
  			file_put_contents($root_path.'/payments_octapay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  			$amount 		= $_PARAMS['player_currency_amount'];
			$totalamount 	= $_PARAMS['player_currency_amount']+$get_player_details->balance;
		    $charges 		= 1;
		
		
    }
    else
    {
    	$get_player_details = get_player_all_details($payment_details->player_id);
    	$status 				= "KO";
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= $riskInfo;
        $_RESULT['html'] 		= $orderInfo."||".$riskInfo;			
		$charges 				= 1;
        $amount 				= $_PARAMS['player_currency_amount'];
        $totalamount 			= $get_player_details->balance;
    }
    
    if($payment_details->status != "OK")
	{
			
		$logmessage = date('Y-m-d H:i:s')." OCTAPAY 3DSecure order id :".$payment_details_id."CUREENT STATUS::".$payment_details->status."\n";
  		file_put_contents($root_path.'/payments_octapay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		update_payments_status($payment_details_id, $payment_details->player_id, $status, $_RESULT['html'], $payment_details->payment_foreign_id, $charges, $amount, $totalamount,'Octapay3ds');
		update_payment_foreignid($payment_details_id, $tradeNo);
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
	
	if($_RESULT['status'] 	== 'Success'){
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$payment_details_id.'&amt='.$_PARAMS['player_currency_amount']/100;
		
	}else{
	    $_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$payment_details_id.'&s=declined';
	} ?>
	 <script language="javascript">
	parent.parent.location = "<?php echo $_LOCATION_URL; ?>"; 
    </script>
    <?php 
}
?>
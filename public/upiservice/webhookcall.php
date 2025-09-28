<?php

  /*include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
  include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';


  $response = $_POST;
  
  
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." hmtsquare webhook callback data :".json_encode($response)."\n";
  file_put_contents($root_path.'/payments_hmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  
 
  $paymentid = substr($response['orderId'],3);
  $payment_details = getPaymentDetails($paymentid);
  
  $logmessage = date('Y-m-d H:i:s')." hmtsquare webhook payment details :".json_encode($payment_details)."\n";
  file_put_contents($root_path.'/payments_hmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  
  
  $params = get_params($payment_details->payment_request_id);
  $providerDetails = getProviderDetails($payment_details->payment_provider_id);
  $statusInfo = $response['responseCode']." || ".$response['message'];
  $orderInfo = $response['status'];
  if($payment_details->status !='SUCCESS'){
  if($response['status'] == 'SUCCESS' || $response['responseCode'] == '000'){
  	
	
	  $mid = $providerdetails->credential1;
	  $password=$providerdetails->credential2;
	  
	  
	  // create a new cURL resource
		$headers = array(
				               "mid: ".$mid,
							   "password: ".$password,
							   "content-type: application/json",
							);
		$data =array();
		$data['order_token'] = $payment_details->order_token;					
		$postdata=json_encode($data);				
		
		$posturl = 'https://vpay.hmtsquare.com/statuscheck';
		
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST,true); // Transmit datas by using POST
	    curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$resp = curl_exec($ch);
		curl_close($ch);
		//echo "hello".$resp; echo "test";exit;
		curl_close($ch);
		$response1 = json_decode($resp,true);
		//print_r($response);exit;
		$logmessage = date('Y-m-d H:i:s')." hmtsquare webhook cron data response  :".json_encode($response1)."\n";
	    file_put_contents($root_path.'/payments_hmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	
		
		if($response1['status']=='SUCCESS'){
		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised || Type - ".$response1['paymentType'];
		$_RESULT['errorcode'] 	= 0;
		$status 				= "SUCCESS";
		$charges				= 1;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $response1['transactionId']);
		
		$_PARAMS['player_id'] = $player->mstrid;
		$_PARAMS['amount'] = $amount;
		update_player_balance($_PARAMS);
		//$_LOCATION_URL  = REDIRECTURL.'-success/'.$payment_id;
		}
		
		
		
		
		
	}elseif($response['status'] == 'PAYMENT_CREATED' || $response['status'] == 'ORDER_SESSION_CREATED' || $response['status'] == 'INITIATED'){
		
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "PENDING";
		$charges				= 0;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		//update_payment_foreignid($payment_details->id, $response['transactionId']);
		//$_LOCATION_URL  = REDIRECTURL.'-failed';
		
	}else{
		
		
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "DECLINED";
		$charges				= 0;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		//update_payment_foreignid($payment_details->id, $response['transactionId']);
		//$_LOCATION_URL  = REDIRECTURL.'-failed';
		
	}
	}
	
	return true;*/
	
?>
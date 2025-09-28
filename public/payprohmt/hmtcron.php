<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
  
  $data = file_get_contents('php://input');
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." hmtsquare cron details  :".$data."\n";
  file_put_contents($root_path.'/payments_newhmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  
  $res_data = json_decode($data,true);
  $pay_id=$res_data['payment_id'];
  $pay_breall = explode('-',$pay_id);
  $payment_id=$pay_breall[1];
  
  $payment_details = getPaymentDetails($payment_id);
  $params = get_params($payment_details->payment_request_id);
  $providerdetails = getProviderDetails($payment_details->payment_provider_id);

 if($payment_details->status!='SUCCESS'){
 
  
  $logmessage = date('Y-m-d H:i:s')." hmtsquare cron payment details  :".json_encode($payment_details)."\n";
  file_put_contents($root_path.'/payments_newhmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  
  if($payment_details->status!='SUCCESS'){
  
	
	
     $statusInfo = $res_data['message'];
     //$orderInfo = $response['status'];
     
	
	if($res_data['status'] == 1){
		
		
		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised";
		$_RESULT['errorcode'] 	= 0;
		$status 				= "SUCCESS";
		$charges				= 1;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $res_data['avptrans_num']);
		
		$_PARAMS['player_id'] = $player->mstrid;
		$_PARAMS['amount'] = $amount;
		update_player_balance($_PARAMS);
		
		
		
		
		
		
		
	}elseif($res_data['status'] == 0){
		
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "PENDING";
		$charges				= 0;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $res_data['avptrans_num']);
		
		
	}else{
		
		
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "DECLINED";
		$charges				= 0;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $res_data['avptrans_num']);
		
		
	}
	
	
	
	}else{
	
	}
	}
  
?>
 

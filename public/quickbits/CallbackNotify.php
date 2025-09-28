<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

  $data = file_get_contents('php://input');
  $json_response_Data = json_decode($data, true);
  /* construct message */
  
  /**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." quickbits callback data :".$_POST.$data."\n";
  file_put_contents($root_path.'/payments_quickbits.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  /**  Log message end **/
  
  $finalParamsArray = array();
  $finalParamsArray = $json_response_Data;

$log_filepath   =   $root_path.'/Reward_points.log'; 
$logmessage     =   date('Y-m-d H:i:s')." Quickbits JSON response Data :".  $data ."\n";
file_put_contents($log_filepath, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
        
  unset($finalParamsArray['checksum']);
	// $finalParamsArray = array(
		// 'status_code'       => $json_response_Data['status_code'],
		// 'status_msg'        => $json_response_Data['status_msg'],
		// 'trans_id'          => $json_response_Data['trans_id'],
		// 'pay_status'        => $json_response_Data['pay_status'],
		// 'order_status'      => $json_response_Data['order_status'],
		// 'request_reference' => $json_response_Data['request_reference'],
	// );
  $paymentid = explode('-',$json_response_Data['request_reference']);
  $json_response_Data['request_reference'] = $paymentid[1];
  $payment_details = getPaymentDetails($json_response_Data['request_reference']);
  $params = get_params($payment_details->payment_request_id);
  
  $providerDetails = getProviderDetails($payment_details->payment_provider_id);
  $final_request_data = implode("", $finalParamsArray);
  $secret_key           = $providerDetails->credential3;
  $calculated_check_sum = hash('sha256', $final_request_data . $secret_key);
  $statusInfo = $json_response_Data['status_code']." || ".$json_response_Data['status_msg'];
  $orderInfo = $json_response_Data['pay_status']." || ".$json_response_Data['order_status'];
  if($json_response_Data['status_code'] == 408){
	$statusInfo = $payment_details->description." || ".$statusInfo; 
  }
  if ($calculated_check_sum == $json_response_Data['checksum']){
  	if($json_response_Data['pay_status'] == 'completed'){
  		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised";
		$_RESULT['errorcode'] 	= 0;
		$status 				= "OK";
		$charges				= 1;
	}else if($json_response_Data['pay_status'] == 'pending'){
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "PENDING";
		$_RESULT['errorcode'] 	= 0;
		$charges				= 0;
	}else{
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "KO";
		$charges				= 0;
	}
  }else{
  	$_RESULT['status'] 		= 'Declined';
    $_RESULT['errorcode'] 	= 207;
    $_RESULT['html'] 		= 'Payment is not authorized due to invalid checksum.';
	$status 				= "KO";
	$charges				= 0;
  }
  	
	$player = get_player_all_details($payment_details->player_id);
	//$amount = $payment_details->amount;
	$amount = $payment_details->player_currency_amount;
	$totalamount = $amount + $player->balance;
	if($payment_details->status != 'OK'){
		update_payments_status($json_response_Data['request_reference'], $player->id, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		if(!empty($json_response_Data['trans_id']))
			update_payment_foreignid($json_response_Data['request_reference'], $json_response_Data['trans_id']);
		if($json_response_Data['pay_status'] == 'completed'){

			$logmessage = date('Y-m-d H:i:s')." Quickbits Payment Params :".json_encode($params)."\n";
			file_put_contents($root_path.'/Reward_points.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

			$logmessage = date('Y-m-d H:i:s')." Payment Details :".json_encode($payment_details)."\n";
			file_put_contents($root_path.'/Reward_points.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

			updateVipStatus($payment_details->player_id);
			$_PARAMS['player_id'] = $player->id;
			$_PARAMS['amount'] = $amount;
			update_player_balance($_PARAMS);
			checkplayerrisklevel($_PARAMS['player_id']);
//			update_player_viplevel($_PARAMS['player_id']);
			UpdatePlayerRewardpoints($player->id,$params,$payment_details->id);
                    
                $logmessage     =   date('Y-m-d H:i:s')." Quickbits Payment completed after Reward Points Update :\n";
                file_put_contents($root_path.'/Reward_points.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
				
				   /*alloting bonus to the player*/
					
					/*include_once dirname(dirname(dirname(__FILE__))).'/public/detectDevice.php';
                    $deviceType = Detect::isMobileDevice();
                    if($deviceType){
                    	$device_type='m';
                    }else{
                    	$device_type='d';
                    }
					 $headers = array(
					    "Content-type: application/json",
					    "Token: " . CASINO_ACCESS_TOKEN,
					    "Connection: close"
		             );
					$post_data = array(
							'site_id' => CASINO_SITE_ID,
							'token' => CASINO_ACCESS_TOKEN,
							'devicetype' =>  $device_type ,
							'username' => $player->username,
							'depositid'=> $json_response_Data['request_reference'],
							'amount' => $_PARAMS['amount']/100
							
							);
					$post_data = json_encode($post_data);	
					$apiurl = 'api/redeemdepositbonus';		
					$url = CASINO_SERVICES_URL.$apiurl;
		            $refererURL = PAYMENTURL;
		
					$curl = curl_init($url);
					curl_setopt ($curl, CURLOPT_HTTPHEADER,  $headers);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_REFERER, $refererURL);
					
					$json_response = curl_exec($curl); 
					$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					$error_msg = curl_error($curl); 
					curl_close($curl);
		            $response = json_decode($json_response, true);*/
					
			       /*end alloting bonus to the player*/
		}
	}
  
?>

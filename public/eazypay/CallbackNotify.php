<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

  $data = file_get_contents('php://input');
  
  
  /**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." eazypay callback data :".$data."\n";
  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  /**  Log message end **/
  
  
  $json_response_Data = $_POST;


  
  $paymentid = $json_response_Data['MerchantorderId'];
 
  $payment_details = getPaymentDetails($paymentid);
  $params = get_params($payment_details->payment_request_id);
  
  $providerDetails = getProviderDetails($payment_details->payment_provider_id);
  //$final_request_data = implode("", $finalParamsArray);
  //$json_response_Data['RESPONSE_CODE']=000;
  $statusInfo = $json_response_Data['status'];
  $orderInfo = $json_response_Data['status'];
  
  	if($json_response_Data['status'] == 'SUCCESS'){
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
	//$amount = $payment_details->amount;
	$amount = $payment_details->player_currency_amount;
	$totalamount = $amount + $player->balance;
	if($payment_details->status != 'SUCCESS'){
		update_payments_status($json_response_Data['MerchantorderId'], $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		if(!empty($json_response_Data['VendorOrderId']))
			update_payment_foreignid($json_response_Data['MerchantorderId'], $json_response_Data['VendorOrderId']);
		if($json_response_Data['status'] == 'SUCCESS'){

			$logmessage = date('Y-m-d H:i:s')." Quickbits Payment Params :".json_encode($params)."\n";
			file_put_contents($root_path.'/Reward_points.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

			$logmessage = date('Y-m-d H:i:s')." Payment Details :".json_encode($payment_details)."\n";
			file_put_contents($root_path.'/Reward_points.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

			updateVipStatus($payment_details->player_id);
			$_PARAMS['player_id'] = $player->mstrid;
			$_PARAMS['amount'] = $amount;
			update_player_balance($_PARAMS);
			//checkplayerrisklevel($_PARAMS['player_id']);
//			update_player_viplevel($_PARAMS['player_id']);
			//UpdatePlayerRewardpoints($player->id,$params,$payment_details->id);
                    
                $logmessage     =   date('Y-m-d H:i:s')." Quickbits Payment completed after Reward Points Update :\n";
                file_put_contents($root_path.'/Reward_points.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
				$_LOCATION_URL  = REDIRECTURL.'-success/'.$paymentid;
				
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
		}else{
			$_LOCATION_URL  = REDIRECTURL.'-failed';
		}
	}
  
?>
  <script language="javascript">
        window.parent.location = "<?php echo $_LOCATION_URL; ?>";
    </script>

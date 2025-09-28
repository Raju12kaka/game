<?php 
include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';


// Get the raw HTTP POST body (JSON object encoded as a string)
// Note: Substitute getBody() with a function call to retrieve the raw HTTP body.
// In "plain" PHP this can be done with file_get_contents('php://input')
$body = file_get_contents('php://input');

/*
 * Using the Coinify PHP SDK
 */
$logmessage = date('Y-m-d H:i:s')." Netcents Callback Response: ".json_encode($body)."\n";
file_put_contents(dirname(dirname(__DIR__)).'/logs/netcents.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

//Validate Callback
$result = validateCallback($body);
if ($result)
{	
	//Payment Details
	$orderId = $result->external_id;
	$payment_details = get_payment_details($orderId);
	$params = get_params($payment_details['payment_request_id']);
	$playerid = $payment_details['player_id'];
	$paymentStatus = $payment_details['status'];
	$transaction_status = $result->transaction_status;
	$trans_id = $result->payment_id;
        $log_filepath   =   dirname(dirname(__DIR__)).'/logs/Reward_points.log';
        
        $logmessage     =   date('Y-m-d H:i:s')." Netcents Call Bank Result :".json_encode($result)."\n";
        file_put_contents($log_filepath, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	
	//check due amount difference
	$totalamount = $result->amount_due * $result->exchange_rate;
	$receivedamount = $result->amount_received * $result->exchange_rate;
	$difference_amount = $totalamount - $receivedamount;
	
	if(in_array($transaction_status, array('paid', 'overpaid', 'completed')) || ($transaction_status == 'underpaid' && $difference_amount <= 3)){
  		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised";
		$_RESULT['errorcode'] 	= 0;
		$status 				= "OK";
		$charges				= 1;
	}else if($transaction_status == 'pending_confirmation'){
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$transaction_status.' - '.$json_response_Data['order_status'];
		$status 				= "PENDING";
		$_RESULT['errorcode'] 	= 0;
		$charges				= 0;
	}else{
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$transaction_status.' - '.$json_response_Data['order_status'];
		$status 				= "KO";
		$charges				= 0;
	}
	
	$player = get_player_all_details($playerid);
	//$amount = $payment_details['amount'];
	$amount = $payment_details['player_currency_amount'];
	$totalamount = $amount + $player->balance;
	
	if($paymentStatus != 'OK'){
		update_payments_status($orderId, $playerid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		if(!empty($trans_id))
			update_payment_foreignid($orderId, $trans_id);
		if(in_array($transaction_status, array('paid', 'overpaid', 'completed')) || ($transaction_status == 'underpaid' && $difference_amount <= 3)){
                
                $logmessage     =   date('Y-m-d H:i:s')." Netcents Payment Params :".json_encode($params)."\n";
                file_put_contents($log_filepath, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
                
                $logmessage     =   date('Y-m-d H:i:s')." Netcents Payment Details :".json_encode($payment_details)."\n";
                file_put_contents($log_filepath, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

                    updateVipStatus($playerid);
                    $_PARAMS['player_id'] = $player->id;
                    $_PARAMS['amount'] = $amount;
                    update_player_balance($_PARAMS);
					checkplayerrisklevel($_PARAMS['player_id']);
//                    update_player_viplevel($_PARAMS['player_id']);
                    UpdatePlayerRewardpoints($player->id,$params,$payment_details['id']);
					
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
							'depositid'=> $orderId,
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
                    
                $logmessage     =   date('Y-m-d H:i:s')." Netcents Payment completed after Reward Points :\n";
                file_put_contents($log_filepath, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		}
	}
	
}

function validateCallback($body)
{
	if(empty($body))
	  return false;
	
	//Call back data
	parse_str($body, $callback_data);
	$signature = $callback_data['signature'];
	$signing = $callback_data['signing'];
	$data = $callback_data['data'];
	
	//validate callback
	$signature_pair = $signature ? explode(',', $signature) : '';
	$timestamp_arr =  explode('=', $signature_pair[0]);
	$timestamp = $timestamp_arr[1]; 
	$signature_arr =  explode('=', $signature_pair[1]);
	$signature_callback = strtolower($signature_arr[1]); 
	$hashable_payload_string = $timestamp . '.' . $data;
	
	$signature_hash = strtolower(hash_hmac("sha256", $hashable_payload_string, $signing, false));
	
	if($signature_hash==$signature_callback)
	{
		$data = base64_decode($data);
  		$payload = json_decode($data);
		file_put_contents(dirname(dirname(__DIR__)).'/logs/bitcoinpro_payments.log', date('Y-m-d H:i:s').'Signature Success: '.$data.PHP_EOL , FILE_APPEND | LOCK_EX);
		return $payload;
	}
	else 
	{
		file_put_contents(dirname(dirname(__DIR__)).'/logs/bitcoinpro_payments.log', date('Y-m-d H:i:s').'Signature Invalid: '.$signature_hash.' | '.$signature_callback.PHP_EOL , FILE_APPEND | LOCK_EX);
		return false;	
	}
}

?>

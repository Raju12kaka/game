<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

  
 
 
 
  /**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
 
  
  $payments = get_hmt_payments();
  foreach($payments as $pay){
  if($pay['status']!='SUCCESS'){		
  $payment_id=$pay['id'];
  $payment_details = getPaymentDetails($payment_id);
  $params = get_params($payment_details->payment_request_id);
  $providerdetails = getProviderDetails($payment_details->payment_provider_id);	
  
   $logmessage = date('Y-m-d H:i:s')." hmtsquare cron data  :".json_encode($payment_details)."\n";
  file_put_contents($root_path.'/payments_hmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  
  
  
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
	$response = json_decode($resp,true);
	//print_r($response);exit;
	$logmessage = date('Y-m-d H:i:s')." hmtsquare cron data response  :".json_encode($response)."\n";
    file_put_contents($root_path.'/payments_hmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	
	
	
	$statusInfo = $response['responseCode']." || ".$response['message'];
	$orderInfo = $response['status'];
	
	if($response['status'] == 'SUCCESS' || $response['responseCode'] == '000'){
		
		
		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised || Type - ".$response['paymentType'];
		$_RESULT['errorcode'] 	= 0;
		$status 				= "SUCCESS";
		$charges				= 1;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $response['transactionId']);
		
		$_PARAMS['player_id'] = $player->mstrid;
		$_PARAMS['amount'] = $amount;
		update_player_balance($_PARAMS);
		//$_LOCATION_URL  = REDIRECTURL.'-success/'.$payment_id;
		
		
		/*postback aff*/
			if($player->affiliate_id==47){
				
				
				$reference_number = 'AKD'.'123'.$payment_details->id.'789'; 
				$randomNumber = time() . mt_rand(1000, 9999999); 
				$posturl = "https://ads.trafficjunky.net/tj_ads_pt?a=1000333251&member_id=1004142101&cb=".$randomNumber."&cti=".$reference_number."&ctv=".$amount."&ctd=paymentsuccess";
				$resp = file_get_contents($posturl);
				
				//update_payment_details($json_response_Data['ORDER_ID'],$reference_number,$posturl);
			}
			
			/*end postback aff*/


		$request_details = get_foreign_id($payment_details->payment_request_id);
		
		if($request_details->bonuscode!=""){
			$data1=array();
			$data1['player_id']=$player->mstrid;
			$data1['deposit_id']=$payment_details->id;
			$data1['bonus_code']=$request_details->bonuscode;
			
			$post_data = $data1;
			$apiurl = 'DepositsCntr/redeembonusfrompayments';		
			$url = CASINO_SERVICES_URL.$apiurl;
			
			$headersInfo = array(
				"content-type: application/json",
			);

			$curl = curl_init($url);

			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
			curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
			curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);//Transmit datas by using POST
			
			$json_rsponse = curl_exec($curl); 
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$error_msg = curl_error($curl); 
			curl_close($curl);
			$rsponse = json_decode($json_rsponse, true);	
		}
		
		
		// Code for Referral Amount of late payment players 

		$playerId = $player->mstrid;
		$referrer_id = $player->referrer_id;
		if($referrer_id!=""){

			$paymentCount = getpaymentsCount1($playerId);
			if($paymentCount->count == 1){
				$get_payment = getFirstpaymentsdata1($playerId);
				$first_amount = $get_payment->famount;
				
				if($first_amount >= 500){
					$data2['playerId'] =  $playerId;
					$data2['refered_player_id'] = $referrer_id;
					$post_data2 = $data2;
					
					$apiurl2 = 'UsersListCntr/addvirtualbonustoReferrerplayer_crondata';		
					$url2 = CASINO_SERVICES_URL.$apiurl2;
					
					$headersInfo = array(
						"content-type: application/json",
					);

					$curl2 = curl_init($url2);

					curl_setopt($curl2,CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($curl2,CURLOPT_HEADER, 0 ); // Colate HTTP header
					curl_setopt($curl2,CURLOPT_RETURNTRANSFER, true);// Show the output
					curl_setopt($curl2,CURLOPT_POST,true); // Transmit datas by using POST
					curl_setopt($curl2,CURLOPT_POSTFIELDS, $post_data2);//Transmit datas by using POST
					
					$json_rsponse2 = curl_exec($curl2); 
					$status2 = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
					$error_msg2 = curl_error($curl2); 
					curl_close($curl2);
					$rsponse2 = json_decode($json_rsponse2, true);
					// print_r($rsponse2); die;
				}
			}
		}
		
		// End referral Code
		
		
	}elseif($response['responseCode'] == '017' || $response['responseCode'] == '006' ||  $response['status'] == 'PAYMENT_CREATED' || $response['status'] == 'ORDER_SESSION_CREATED' || $response['status'] == 'INITIATED'){
		
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo." || ".$response['paymentType']; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "PENDING";
		$charges				= 0;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $response['transactionId']);
		//$_LOCATION_URL  = REDIRECTURL.'-failed';
		
	}else{
		
		
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo." || ".$response['paymentType']; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "DECLINED";
		$charges				= 0;
		
		$player = get_player_all_details($payment_details->player_id);
		$amount = $payment_details->player_currency_amount;
	    $totalamount = $amount + $player->balance;
		update_payments_status($payment_details->id, $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($payment_details->id, $response['transactionId']);
		//$_LOCATION_URL  = REDIRECTURL.'-failed';
		
	}
	}
	}	
  
?>
 

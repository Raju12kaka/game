<?php
	$cron_log_path = dirname(__DIR__).'/../logs/cron.log';
	$payment_log_path = dirname(__DIR__).'/../logs/payments_cardbit.log';

	$cron_logmessage  = "/****************".date('Y-m-d H:i:s')." Update Cardbit payments status to KO cron started*****************/";
	file_put_contents($cron_log_path, $cron_logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);	
	
	include_once dirname(__DIR__).'/../models/common.php';
	include_once dirname(__DIR__).'/../models/define.php';

	$date_24hrs = date('Y-m-d H:i:s',strtotime('-72 hours'));
	//$date_48hrs = date('Y-m-d H:i:s',strtotime('-48 hours'));
	
	//$date_48hrs = '2019-01-07 17:08:10';
	//$date_24hrs = '2019-01-07 17:08:11';
	
	$cardbit_pending_payments = get_payment_transactions_byprovider('171', $date_24hrs);
	
	//echo "<pre>";
	//echo "DATE::".$date_24hrs;
	//echo "TOTAL TRANSACTIONS::".count($cardbit_pending_payments);
	//print_r($cardbit_pending_payments);
	//exit();
	
	$cron_logmessage  = "/****************".date('Y-m-d H:i:s')." Total records ".count($cardbit_pending_payments)." *****************/";
	file_put_contents($cron_log_path, $cron_logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	
	
	if(!empty($cardbit_pending_payments))
	{
		foreach($cardbit_pending_payments as $cardbit_pending_payment)
		{
	  		if($cardbit_pending_payment['status'] == "INITIATED" || $cardbit_pending_payment['status'] == "PENDING")
	  		{
	  			$provider_details = getProviderDetails('171');
				$req['order_id'] = $cardbit_pending_payment['id'];
				$paymentData = getPaymentDetails($cardbit_pending_payment['id']);
				$req['ip'] = long2ip($paymentData->ip);
				
				//$url = "https://api.cardbit.io/api/v1/order_details";
				//$url = "https://api.cardbit.io/apiTest/v1/order_details";
				
				$shop_id            = trim($provider_details->credential1); 
			    $shop_key        	= trim($provider_details->credential2);
			    $url        	    = trim($provider_details->credential4); 
			    $nonce 				= time();
			    $signature           = hash_hmac('sha256', $nonce."|".$shop_id."|".$shop_key, $shop_key);
			    $request_header = array(
					'Accept: */*',
				    'C-Request-Nonce: '.$nonce,
				    'C-Request-Signature: '.$signature,
				    'C-Shop-Id: '.$shop_id,
				    'Content-Type: application/json'
				);
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
				curl_setopt($ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_HEADER, 1);
				
				$requestString = '';
				if (is_array($req))
					$requestString = json_encode($req);
					
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
				
				$response = curl_exec($ch);
				
				// GET RESPONSE HTTP CODE
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				
				// GET RESPONSE HEADER AND BODY
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$response = substr($response, $header_size);
				
				// CURL ERROR IF EXIST
				$error = curl_error($ch);
				$error_code = curl_errno($ch);
				curl_close($ch);
				
				$parsedResult = (array)json_decode($response, 1);
				
				$cron_logmessage  = "/****************".date('Y-m-d H:i:s')." Cardbit update to KO status payment Id ".$cardbit_pending_payment['id']." :: Amount:: ".$cardbit_pending_payment['amount']." *****************/";
				file_put_contents($cron_log_path, $cron_logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
				
				if(!empty($parsedResult))
				{
					if($parsedResult['error_code'] > 1)
					{	
						$status = "KO";
						$message = $parsedResult['error_message'];
						$error_code = $paymentData->error_code;
						$transactionId = $paymentData->id;
						$exttransid = '';
						
						//update_payments_status($transactionId, $paymentData->player_id, $status, $message, $exttransid);
					}
					else if($parsedResult['result'] == "success" && $parsedResult['error_code'] == 1)
					{
						$amount = convert_currency('EUR',$_PARAMS['player_currency_id'],$parsedResult['amount']);
						//$currency = $parsedResult['currency'];
						$currency = "USD";
						$transactionId = $paymentData->id;
						if($parsedResult['order_status'] == "paid")
						{
							$status = "OK";	
						}
						else {
							$status = "KO";
						}
						
						$error_code = $paymentData->error_code;
						$message = $parsedResult['error_message'];
						$exttransid = $parsedResult['order_id'];
					}
					else
					{
						$error_code = $paymentData->error_code;
						$message = $parsedResult['error_message'];
						$status = "KO";
					}
					
					update_payments_status($transactionId, $paymentData->player_id, $status, $message);
					if(!empty($exttransid))
					{
						update_payment_foreignid($transactionId, $exttransid);	
					}
				}
			}			  		
		}
	}
	
	$cron_logmessage  = "/****************".date('Y-m-d H:i:s')." Update Cardbit payments status to KO cron Ends*****************/";
	file_put_contents($cron_log_path, $cron_logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	
	exit();

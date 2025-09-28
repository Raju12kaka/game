<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

/*$url = 'https://adservices.akkhacasino.local/DepositsCntr/redeembonusfrompayments';
$data = array();
$data['player_id']=2017;
$data['deposit_id'] = 116;
$data['bonus_code'] = 'FREE100';

//$data = http_build_query($data);
$curl = curl_init($url);
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST

$resp= curl_exec($curl);
$err =  curl_error($curl);

curl_close ($curl);

echo $resp;
exit;*/


$provider_id=103;
$payment_details = get_payments();
$providerDetails = getProviderDetails($provider_id);
//print('<pre>');print_r($payment_details);exit;

 /**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);

foreach($payment_details as $pay){
	
	
	$posturl = "https://pg.hmtpayments.com/crm/services/paymentServices/getStatus?PAY_ID=".$providerDetails->credential1."&ORDER_ID=".$pay['id'];
	
	/**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmessage = date('Y-m-d H:i:s')." qartpay cron payment id  :".json_encode($pay)."\n";
  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  /**  Log message end **/
  
  
	// create a new cURL resource
	$headers = array(
		    "Content-type: application/json",
		    "Connection: close"
		);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $posturl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$resp = curl_exec($ch);
	//echo "hello".$resp; echo "test";exit;
	curl_close($ch);
	$response = json_decode($resp,true);
	//print_r($response);exit;
	$logmessage = date('Y-m-d H:i:s')." qartpay cron payment response  :".json_encode($response)."\n";
  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	
	 $statusInfo = $response['responseCode']." || ".$response['message'];
     $orderInfo = $response['status'];
	
	if($response['responseCode'] == '000'){
		
		
		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised";
		$_RESULT['errorcode'] 	= 0;
		$status 				= "SUCCESS";
		$charges				= 1;
		
		$player = get_player_all_details($pay['player_id']);
		$amount = $pay['player_currency_amount'];
	    $totalamount = $amount + $player->balance;
		update_payments_status($pay['id'], $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($pay['id'], $response['transactionId']);
		
		$_PARAMS['player_id'] = $player->mstrid;
		$_PARAMS['amount'] = $amount;
		update_player_balance($_PARAMS);
		
		
		/*Add Bonus to Player*/
		
		   /* $url = BONUS_REDEEM_URL;
			$params = get_params($pay['payment_request_id']);
			
			if($params['bonuscode']!=""){
			$data = array();
			$data['player_id']=$pay['player_id'];
			$data['deposit_id'] = $pay['id'];
			$data['bonus_code'] = $params['bonuscode'];
			
			
			
		    $curl = curl_init($url);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
            curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
            
            $resp= curl_exec($curl);
            curl_close ($curl);
			
			
			
			}else{
				
				$payments_count = get_success_count($pay['player_id']);
				if($payments_count->cnt==1){
					
					$data = array();
					$data['player_id']=$pay['player_id'];
					$data['deposit_id'] = $pay['id'];
					$data['bonus_code'] = 'BONUS200';
					
					$curl = curl_init($url);
		            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
		            curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
		            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
		            curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
		            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
		            
		            $resp= curl_exec($curl);
		            curl_close ($curl);
					
				}
				
			}*/
		
		
		
		
	}elseif($response['responseCode'] == '006'){
		
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "PENDING";
		$charges				= 0;
		
		$player = get_player_all_details($pay['player_id']);
		$amount = $pay['player_currency_amount'];
	    $totalamount = $amount + $player->balance;
		
		update_payments_status($pay['id'], $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($pay['id'], $response['transactionId']);
		
	}else{
		
		
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= $orderInfo ." || ". $statusInfo; //$json_response_Data['pay_status'].' - '.$json_response_Data['order_status'];
		$status 				= "DECLINED";
		$charges				= 0;
		
		$player = get_player_all_details($pay['player_id']);
		$amount = $pay['player_currency_amount'];
	    $totalamount = $amount + $player->balance;
		
		update_payments_status($pay['id'], $player->mstrid, $status, $_RESULT['html'], "", $charges, $amount, $totalamount);
		update_payment_foreignid($pay['id'], $response['transactionId']);
		
	}
	
	
}


?>
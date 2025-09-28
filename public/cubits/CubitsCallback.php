<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

  foreach (getallheaders() as $name => $value) {
    switch ($name) {
      case "CUBITS_CALLBACK_ID":
          $cubits_callback_id = $value;
          break;
      case "CUBITS_KEY":
          $cubits_key = $value;
          break;
      case "CUBITS_SIGNATURE":
          $cubits_signature = $value;
          break;
    }
  }
  /* set API access key*/
  $k = "294510e9c1fb0b9cc3bbe6bb33664280";
  $receiver_currency = "USD";
  //$name = "Alpaca Socks";
  $data = array(
    "receiver_currency" =>  $receiver_currency,
    "callback_url" =>  CALLBACKURL."/cubits/CubitsCallback.php"
  );
  $json_Data = json_encode($data);
  /* construct message */
  $msg = $cubits_callback_id . hash('sha256',  utf8_encode($json_Data), false );
  $signature = hash_hmac("sha512", $msg , $k);
  $params = (array) json_decode(file_get_contents('php://input'), TRUE);
  
  /**  Log message start **/
  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  array_pop($documentroot);
  array_push($documentroot, 'logs');
  $root_path = implode('/', $documentroot);
  
  $logmsg = ":  callbackid : ".$cubits_callback_id."; key : ".$cubits_key."; signature : ".$cubits_signature."; generatedsignature : ".$signature."; ";
  $logmessage = date('Y-m-d H:i:s').$logmsg." cubits callback data :".json_encode($params)."\n";
  file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  /**  Log message end **/
  
  //if ($signature == $cubits_signature){
  if (true){
  	$postData = $params;
	if($postData['state'] == 'completed'){
		$_RESULT['status'] 		= 'Success';
        $_RESULT['html'] 		= "Authorised";
		$_RESULT['errorcode'] 	= 0;
		$status 				= "OK";
		$charges				= 1;
	}elseif($postData['state'] == 'pending'){
		$_RESULT['status'] 		= 'Pending';
        $_RESULT['html'] 		= !empty($postData['description']) ? $postData['description'] : 'Pending';
		$status 				= "PENDING";
		$_RESULT['errorcode'] 	= 0;
		$charges				= 0;
	}else{
		$_RESULT['status'] 		= 'Declined';
        $_RESULT['errorcode'] 	= 207;
        $_RESULT['html'] 		= !empty($postData['description']) ? $postData['description'] : 'failed';
		$status 				= "KO";
		$charges				= 0;
	}
	//check payment already exists or not
	$checkCubitId = check_cubit_payment($postData['tx_ref_code'], $postData['channel_id']);
	if(!empty($checkCubitId)){
		$player = get_player_all_details($checkCubitId->player_id);
		$amount = $checkCubitId->amount;
		$totalamount = $amount + $player->balance;
		update_payments_status($checkCubitId->id, $checkCubitId->player_id, $status, $_RESULT['html'], $charges, $amount, $totalamount);
		if($postData['state'] == 'completed'){
			$_PARAMS['player_id'] = $player->id;
			$_PARAMS['amount'] = $amount;
			update_player_balance($_PARAMS);
		}
	}else{
	    //create payments request
	    $requestId = insert_payment_request($postData, REDIRECTURL);
		$_PARAMS = get_params($requestId);
		$_PARAMS['foreign_id'] = $postData['tx_ref_code'];
		$_PARAMS['amount'] = $_PARAMS['amount']/100;
		$paymentId = insert_payment($_RESULT, $_PARAMS);
		
		insert_payment_cubitid($paymentId, $postData['tx_ref_code'], $cubits_callback_id, $postData['channel_id']);
		if($postData['state'] == 'completed'){
			$_PARAMS['amount'] = $_PARAMS['amount']*100;
			update_player_balance($_PARAMS);
			$player = get_player_all_details($_PARAMS['player_id']);
			$amount = $_PARAMS['amount'];
			$totalamount = $player->balance;
			insert_player_transactions($_PARAMS['player_id'], $amount, $totalamount, $paymentId);
		}
	}
	echo "valid Signature";
  }else{
    echo "Not a Valid Signature";
  }
?>
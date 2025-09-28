<?php 
define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderCurrency'].$_POST['orderNumber'].PRIVATE_KEY);
		$getCurrencyAvailableProcessor = get_currency_available_processor_by_provider($_PARAMS['player_currency_id'], $_PARAMS['payment_provider_id']);
		if($getCurrencyAvailableProcessor->count > 0)
        {
           // $signInfo = hash('sha256', 'CETR'.($_PARAMS['player_currency_amount']/100).$_PARAMS['id'].PRIVATE_KEY);
            $params_currency = $_PARAMS['player_currency_id'];
            $currency_amount = ($_PARAMS['player_currency_amount']/100); 
        }
        else {
            //echo "ELSE CONDITION::";
            //$signInfo = hash('sha256', 'CETR'.($_PARAMS['player_currency_amount']/100).$_PARAMS['id'].PRIVATE_KEY);
            $params_currency = $_PARAMS['currency_id'];
            $currency_amount = $_PARAMS['amount']; 
        }
		if ($_PARAMS['player_currency_amount'] != ($_POST['orderAmount']*100) || $signature!=strtolower($_POST['signInfo'])){

			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			$_RESULT['status'] = 'Initiated';
      		$_RESULT['errorcode'] = 207;
      		$_RESULT['foreign_errorcode'] = 0;
      		$_RESULT['html']   = 'Pending';
			
			$bitcoinQRImage = 'img/loading_icon.jpg';
      		
      		//insert into payments table
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			$bitcoin_id = insert_bitcoin_status($_PARAMS['player_id'], $_PAYMENT_ID);
			//get authorization credentials from Provider
			$credentials = getProviderDetails($_PARAMS['payment_provider_id']);
			$playerDetails = get_player_all_details($_PARAMS['player_id']);

			$hostedId 		= $credentials->credential1;
			$apiKey			= $credentials->credential2;
			$apiSecretKey	= $credentials->credential3;
			$apiPostURL		= $credentials->credential4;
			$callbackURL	= CALLBACKURL.'/netcents/callbacknotify.php?orderId='.$_PAYMENT_ID;
			$webhookURL		= CALLBACKURL.'/netcents/callbackresponse.php?orderId='.$_PAYMENT_ID;
			$orderId 		= $_PAYMENT_ID;// "Order005";
			$payCurrency 	= 'USD'; // Customer pay amount calculation currency
			$payAmount 		= $_PARAMS['amount'];//0.00025; // Customer pay amount in calculation currency
			$receiveCurrency = 'USD'; // Merchant receive amount calculation currency
			$receiveAmount 	= null; // Merchant receive amount in calculation currency
			//set player info based on billing details
			$name	 = !empty($_POST['billingname']) ? $_POST['billingname'] : $playerDetails->realname;
			$lastname= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $playerDetails->reallastname;
			
			//Convert currency USD to EU
			$payAmountInEuro = convert_currency('USD', 'EUR', $payAmount);
			
			//set post parameters
			$postData = array(
				"external_id" =>  $orderId,
				"hosted_payment_id" => $hostedId,
				"amount" => number_format($payAmountInEuro, 2),
				"email" => trim($playerDetails->email),
				"first_name" => trim($name),
				"last_name" => trim($lastname),
				"callback_url" => $callbackURL,
				//"webhook_url" => $webhookURL,
				"confirmations_required" => 2,
				"crypto_currency_iso" => "BTC"
			);
			//print('<pre>');print_r($postData);exit;
			date_default_timezone_set('US/Eastern');
		    //echo date_default_timezone_get();
		    $currenttime = date('Y-m-d H:i:s');
			//echo $currenttime; exit;
			// echo "<pre>"; print_r($postData); exit;
			//===============================
			//echo "<br>".$apiPostURL."merchant/v2/widget_payments";
			$authHeader = base64_encode($apiKey.":".$apiSecretKey);
	        
	        /*
	        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
	                curl_setopt($curl,CURLOPT_HEADER, array("Authorization: Basic ".$authHeader,"Content-Type: application/json","Accept: application/json") ); // Colate HTTP header
	                curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
	                curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
	                curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($postData));*/
	        //Transmit datas by using POST
	        //curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
	        $curl = curl_init();
	        curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiPostURL."merchant/v2/widget_payments",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => http_build_query($postData),
			  CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Basic ".$authHeader,
			    "cache-control: no-cache",
			    "content-type: application/x-www-form-urlencoded"
			  ),
			));
	        
	        $responseData = curl_exec($curl);
	        curl_close ($curl);
			$session_start_time = time();
			
			$logmessage = date('Y-m-d H:i:s')." Netcents first response: ".$responseData."\n";
			file_put_contents(dirname(dirname(__DIR__)).'/logs/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			}
	
		} else { // INVALID PARAMETERS
			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 103;
			$_RESULT['html'] = 'Invalid parameters';
		}
	
} else { // NO REQUEST_ID
	$_RESULT['status'] = 'Error';
	$_RESULT['errorcode'] = 101;
	$_RESULT['html'] = 'No request id';
}

if($responseData)
{
	$responseData = json_decode($responseData);
	$token = $responseData->token;	
	header("Location: https://merchant.net-cents.com/widget/payment?data=$token");
}

?>

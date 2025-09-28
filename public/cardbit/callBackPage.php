<meta name="viewport" content="width=device-width, initial-scale=1.0">

<div style="text-align: center;background: #333;color: #fff;height: 100%;">
<br/>
	<p style="margin-top: 50px;"><img src="https://www.slots7casino.com/img/logo-shadow.png" alt="logo"></p>
	<p style="font-family: Arial;font-size:20px; font-weight:bold; padding-bottom:10px;padding-top:15px; ">Please Wait</p>
	
	<img src="../gateways/img/preloader.gif" alt=""  />
	<p style="font-family: Arial;">Please do not refresh or click the "Back" button on your browser</p>
	<p style="font-family: Arial;">You will be redirected to payment information page</p>
	<br>
	
</div>

<?php

include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';

//callback post response
$callbackdata = file_get_contents('php://input');
$callback_data = (array)json_decode($callbackdata, 1);
$callbacklogmessage = date('Y-m-d H:i:s')." cardbit callback response: ".$callbackdata."\n";
file_put_contents(dirname(dirname(__DIR__)).'/logs/payments.log', $callbacklogmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

$request_id = ($callback_data['order_id']) ? $callback_data['order_id'] : $_GET['id'];

$paymentData = getPaymentDetails($request_id);

if($paymentData->status == "INITIATED" || $paymentData->status == "PENDING")
{
	$provider_details = getProviderDetails($paymentData->payment_provider_id);
	$req['ip'] = $_SERVER['REMOTE_ADDR'];
	$req['order_id'] = $request_id;
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
	
	$logmessage = date('Y-m-d H:i:s')." cardbit response after payment: ".json_encode($parsedResult)."\n";
	file_put_contents(dirname(dirname(__DIR__)).'/logs/payments_cardbit.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);	
	
	if($parsedResult['error_code'] > 1)
	{
		$status = "KO";
		$message = $parsedResult['error_message'];
		$error_code = $paymentData->error_code;
		$transactionId = $paymentData->id;
		$exttransid = '';
		
		update_payments_status($transactionId, $paymentData->player_id, $status, $message, $exttransid);
		
		$get_redirect_url = get_returnurl_from_requestid($paymentData->payment_request_id);
		
		if($get_redirect_url['redirect_url'])
		{
?>
			<script language="javascript">
				window.location.replace("<?php echo $get_redirect_url['redirect_url']."i=".$paymentData->id.'&amt='.$_PARAMS['player_currency_amount']/100; ?>");
			</script>
<?php			
		}
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
		else if($parsedResult['order_status'] == "canceled"){
			$status = "KO";
		}
		else if($parsedResult['order_status'] == "expired")
		{
			$status = "KO";
		}
		else if($parsedResult['order_status'] == "pending")
		{
			$status = "PENDING";
		}
		else if($parsedResult['order_status'] == "confirming")
		{
			$status = "PENDING";
		}
		else if($parsedResult['order_status'] == "new")
		{
			$status = "INITIATED";
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
	if($paymentData->status != "OK"){
		update_payments_status($transactionId, $paymentData->player_id, $status, $message);
		if(!empty($exttransid))
		{
			update_payment_foreignid($transactionId, $exttransid);	
		}
	}
	
	$get_redirect_url = get_returnurl_from_requestid($paymentData->payment_request_id);
	if($get_redirect_url['redirect_url'])
	{
?>
		<script language="javascript">
			window.location.replace("<?php echo $get_redirect_url['redirect_url']."i=".$paymentData->id."&amt=".$paymentData->player_currency_amount/100; ?>");
		</script>
<?php			
	}
}
?>
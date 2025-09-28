<style>
  body>.container-fluid{
   	margin:3px !important;
   }
</style>
<?php 
set_time_limit(1000);
define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	//print_r($_PARAMS);exit;
	
	//$_PARAMS = get_params($_REQUEST_ID);
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	$signInfo = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderCurrency'].$_POST['orderNumber'].PRIVATE_KEY);
		
		if ($_PARAMS['amount'] != $_POST['orderAmount'] || $_PARAMS['currency_id'] != $_POST['orderCurrency'] || $signature!=strtolower($signInfo)){

			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			$_RESULT['status'] = 'Initiated';
      		$_RESULT['errorcode'] = 207;
      		$_RESULT['foreign_errorcode'] = 0;
      		$_RESULT['html']   = 'Pending';
			
			$palyer_details = get_player_all_details($_PARAMS['player_id']);
			//get authorization credentials from Provider
			$credentials = getProviderDetails($_PARAMS['payment_provider_id']);
			
			//set player info based on billing details
			$name	 = !empty($_POST['billingname']) ? $_POST['billingname'] : $palyer_details->realname;
			$lastname= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $palyer_details->reallastname;
			$address = !empty($_POST['billingaddress']) ? $_POST['billingaddress'] : $palyer_details->address;
			$city	 = !empty($_POST['billingcity']) ? $_POST['billingcity'] : $palyer_details->city;
			$zipcode = !empty($_POST['billingzip']) ? $_POST['billingzip'] : $palyer_details->zipcode;
			$state	 = !empty($_POST['billingstate']) ? $_POST['billingstate'] : $palyer_details->state;
			$country = !empty($_POST['billingcountry']) ? $_POST['billingcountry'] : $palyer_details->country;
			
			$_PARAMS['billingname']= trim($_POST['billingname']);
			$_PARAMS['billinglastname']= trim($_POST['billinglastname']);
			$_PARAMS['billingaddress']= trim($_POST['billingaddress']);
		    $_PARAMS['billingcity'] 	= trim($_POST['billingcity']);
		    $_PARAMS['billingcountry']= trim($_POST['billingcountry']);
		    $_PARAMS['billingstate'] 	= trim($_POST['billingstate']);
		    $_PARAMS['billingzip'] 	= trim($_POST['billingzip']);
			
			//insert into payments table
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			$getcountrycode = fetchCountryCode($palyer_details->country);
			$statename = get_states_name($palyer_details->state, $palyer_details->country);
			
			$phone = str_replace('+', '', $palyer_details->contact_phone);
			$phone = str_replace(' ', '', $phone);
			
			$shop_id            = trim($_POST['authorisationkey1']); //MerchantNo.
		    $shop_key        	= trim($_POST['authorisationkey2']); //GatewayNo.
		    $url          		= trim($_POST['authorisationkey3']); //SignKey
		    $nonce 				= time();
		    //$nonce              = str_replace('.', '', microtime(true));
		    
		    $signature           = hash_hmac('sha256', $nonce."|".$shop_id."|".$shop_key, $shop_key);
		    
			$converted_amount = convert_currency($_PARAMS['player_currency_id'],'EUR',$_PARAMS['amount']);
			
			$req['amount']      = number_format($converted_amount, 2);
			$req['currency'] 	= 'EUR';
			//$req['withdraw_address'] = '3HJnWBdVuiYANnQocfvvSJ8mVVyQziQpHF';
			$req['withdraw_address'] = '1BPo6xrE1BUE5esJ2CJMpfHhqGvoGW5Fhi';
			$req['order_id'] 	= $_PAYMENT_ID;
			$req['order_description'] = 'Slots7casino casino deposit';
			$req['ip'] 			= $_POST['ip'];
			$req['email'] 		= $palyer_details->email;
			$req['first_name']  = $name;
			$req['last_name']  = $lastname;
			$req['date_of_birth'] = $palyer_details->birthdate;
			$req['invoice_type'] = 3;
			$req['zip'] = $zipcode;
			$req['address'] = $address;
			$req['city'] = $city;
			$req['country'] = $country;
			//$req['return_url'] 	= $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID;
			$req['return_url'] 	= CALLBACKURL.'/cardbit/callBackPage.php?id='.$_PAYMENT_ID;
			
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
			
			/*echo "<pre>RESPONSE::";
			print_r($parsedResult);
			print_r($req);
			echo "REQUEST STRING::";
			print_r($requestString);
			echo "REQUEST HEADER::";
			print_r($request_header);
			echo "SHOP KEY::".$shop_key;
			echo "SHOP ID::".$shop_id;
			echo "NONCE::".$nonce;
			echo "SIGNATURE".$signature;
			exit();*/
			
			$logmessage = date('Y-m-d H:i:s')." cardbit response before payment: ".json_encode($parsedResult)."\n";
			file_put_contents(dirname(dirname(__DIR__)).'/logs/payments_cardbit.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
							
			 if (!empty($parsedResult) && $parsedResult['error_code'] == 1 && $parsedResult['result'] == "success") {
			 	//update_payments_status($_PAYMENT_ID, $palyer_details->id, "INITIATED", $parsedResult['payment_token']." || User redirected to payment page.");
			 	//$iframewidth = (is_wap($_PARAMS['web_id'])) ? '340px' : '600px';
			?>
			<!--<iframe src="<?php echo $parsedResult['payment_url']; ?>" width="<?php echo $iframewidth ?>" height="100%" style="border: none;"></iframe>-->
			<!--<script>
				parent.parent.parent.location = "<?php echo $parsedResult['payment_url']; ?>";
			</script>-->
			<?php
			
			    $status = "PENDING";
				$message = $parsedResult['error_message'];
				$error_code = $parsedResult['error_code'];
				$transactionId = $_PAYMENT_ID;
				if(!empty($parsedResult['order_id']))
				{
					$exttransid = $parsedResult['order_id'];	
				}
				
				$_RESULT['status'] = 'PENDING';
				$_RESULT['errorcode'] = $error_code;
				$_RESULT['html'] = $message;
			  

			 }
			 else { // INVALID PARAMETERS
			 	$status = "KO";
				$message = $parsedResult['error_message'];
				$error_code = $parsedResult['error_code'];
				$transactionId = $_PAYMENT_ID;
				if(!empty($parsedResult['order_id']))
				{
					$exttransid = $parsedResult['order_id'];	
				}
				
				$_RESULT['status'] = 'Error';
				$_RESULT['errorcode'] = $error_code;
				$_RESULT['html'] = $message;
				
			 }
				
				update_payments_status($transactionId, $palyer_details->id, $status, $message);
				if(!empty($exttransid))
				{
					update_payment_foreignid($transactionId, $exttransid);	
				}
				
				if ($_PAYMENT_ID){
					$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['amount'];
				} elseif($_CANCELLED) {
					$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=cancelled';
				} else {
					$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=unknown';
				}
				
				if (!$_PARAMS['redirect_url']){
					$_LOCATION_URL = HOST_HTTP_WEB;
				}
				
				/*switch($_RESULT['status']){
					case 'Success':	send_email_notification($_PAYMENT_ID, 'AUTHORISED_DEPOSIT', $_PARAMS); break;
					case 'Pending':	send_email_notification($_PAYMENT_ID, 'PENDING_DEPOSIT', $_PARAMS); break;
					case 'Error':	send_email_notification($_PAYMENT_ID, 'DECLINED_DEPOSIT', $_PARAMS); break;
				}*/
				
				?>
				
				<script language="javascript">
					parent.parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
				</script>
			<?php	
			
			 
			?>
			
			<?php
			
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
?>

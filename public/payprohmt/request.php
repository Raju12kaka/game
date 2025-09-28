<style>
#bitcoincss html, body{
    height: auto !important;
    min-height: 100%;
    overflow: hidden !important;;
}
iframe.bitcoin-iframe {
    min-height: 860px !important;
}
.bitcoin-iframe html, body {

 height: auto !important;
    min-height: 100%;
    overflow: hidden !important;;
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
	
	
	//$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if ($_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderNumber'].PRIVATE_KEY);
		$getCurrencyAvailableProcessor = get_currency_available_processor_by_provider($_PARAMS['player_currency_id'], $_PARAMS['payment_provider_id']);
        
        if($getCurrencyAvailableProcessor->count > 0)
        {
            $signInfo = hash('sha256', 'CETR'.($_PARAMS['player_currency_amount']/100).$_PARAMS['id'].PRIVATE_KEY);
            $params_currency = $_PARAMS['player_currency_id'];
            $currency_amount = ($_PARAMS['player_currency_amount']/100); 
        }
        else {
            //echo "ELSE CONDITION::";
            $signInfo = hash('sha256', 'CETR'.($_PARAMS['player_currency_amount']/100).$_PARAMS['id'].PRIVATE_KEY);
            $params_currency = $_PARAMS['currency_id'];
            $currency_amount = $_PARAMS['amount']; 
        }
		//if ($_PARAMS['amount'] != $_POST['orderAmount'] || $_PARAMS['currency_id'] != $_POST['orderCurrency'] || $signature!=strtolower($_POST['signInfo'])){
        if ($_PARAMS['player_currency_amount'] != ($_POST['orderAmount']*100) || $signature!=strtolower($signInfo)){	
			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			$_RESULT['status'] = 'Initiated';
      		$_RESULT['errorcode'] = 207;
      		$_RESULT['foreign_errorcode'] = 0;
      		$_RESULT['html']   = 'Pending';
			
			$playerDetails = get_player_all_details($_PARAMS['player_id']);
			
			//set player info based on billing details
			$name	 = !empty($_POST['billingname']) ? $_POST['billingname'] : $playerDetails->mstrname;
			$lastname= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $playerDetails->last_name;
			$address = !empty($_POST['billingaddress']) ? $_POST['billingaddress'] : $playerDetails->address;
			$city	 = !empty($_POST['billingcity']) ? $_POST['billingcity'] : $playerDetails->city;
			$zipcode = !empty($_POST['billingzip']) ? $_POST['billingzip'] : $playerDetails->zipcode;
			$phonenum = !empty($_POST['billingphone']) ? $_POST['billingphone'] : $playerDetails->mobileno;
			$state	 = !empty($_POST['billingstate']) ? $_POST['billingstate'] : $playerDetails->state;
			$country = !empty($_POST['billingcountry']) ? $_POST['billingcountry'] : $playerDetails->country_id;
			$email = !empty($_POST['billingemail']) ? $_POST['billingemail'] : $playerDetails->emailid;
			$dob=$playerDetails->birth_date;
			$contactno = str_replace('+', '', $playerDetails->mobileno);
			$contactno = str_replace(' ', '', $contactno);
			$contactno = str_replace('-', '', $contactno);
			$currency='356';
			
			
			
			  $mid            = trim($_POST['authorisationkey1']); //MerchantNo.
		      $password 	        = trim($_POST['authorisationkey2']); //terminalname.
		      $apikey		= trim($_POST['authorisationkey3']); 
			  $paymenturl		= trim($_POST['authorisationkey4']); 
		
		      $orderNo 			= trim($_PARAMS['id']);
		      $orderAmount      = trim($_PARAMS['amount']);
		      $cardNo           = trim($_POST['cardNumber']);
		      $cardExpireYear   = trim($_POST['cardExpireYear']);
		      $cardExpireMonth  = trim($_POST['cardExpireMonth']);
		      $cardSecurityCode = trim($_POST['cardSecurityCode']);
		      $paymethod 		= "Credit Card";
		      $ip               = long2ip($palyer_details->register_IP);
			
			$_PARAMS['billingname']= trim($_POST['billingname']);
			$_PARAMS['billinglastname']= trim($_POST['billinglastname']);
			$_PARAMS['billingaddress']= trim($_POST['billingaddress']);
		    $_PARAMS['billingcity'] 	= trim($_POST['billingcity']);
		    $_PARAMS['billingcountry']= trim($_POST['billingcountry']);
		    $_PARAMS['billingstate'] 	= trim($_POST['billingstate']);
		    $_PARAMS['billingzip'] 	= trim($_POST['billingzip']);
			$_PARAMS['billingphone'] 	= trim($_POST['billingphone']);
		    $_PARAMS['useragent'] 	= $_POST['origin'];
			if($_PARAMS['payment_method_id']==133){
			$_PARAMS['cardnumber'] 	= trim($_POST['cardNumber']);
  		    $_PARAMS['cvv'] 			= trim($_POST['cardSecurityCode']);
   	  	    $_PARAMS['expiryyear']  	= trim($_POST['cardExpireYear']); //set card expire year
		    $_PARAMS['expirymonth'] 	= trim($_POST['cardExpireMonth']); //set card expire month
		    $_PARAMS['cardname'] 		= trim($_POST['cardName']); //set name on card
			}
			
				  
			
			
			//insert into payments table
			$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
			$_PARAMS['player_currency_amount'] = $_PARAMS['player_currency_amount'] / 100;
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			
			
			$callback_url= CALLBACKURL.'/payprohmt/CallbackNotify.php';
			$return_url= CALLBACKURL.'/payprohmt/returnhmt.php';
			
			$upi = trim($_POST['upi']);
			
		  
			
			$arr = array(
             'merchant_id'            => $mid,            //MerchantNo.
             'merchant_auth_code'        => $password,        //GatewayNo.
             'gateway_code'      => $apikey,        //GatewayNo.
             //'paymethod'        => $paymethod,        
             'currency'        => 'INR',        
             'order_no'          => 'Akkha-'.$_PAYMENT_ID,          //OrderNo.
             'amount'      => $orderAmount,      //OrderAmount
             'first_name'        => $name,        //FirstName
             'last_name'         => $lastname,         //lastName
             /*'card_no'           => $cardNo,           //CardNo
             'card_expire_month'  => $cardExpireMonth,  //CardExpireMonth
             'card_expire_year'   => $cardExpireYear,   //CardExpireYear
             'card_security_code' => $cardSecurityCode, //CVV
             'card_type'      => $card_type, */     //Card Type
             'email'            => $email,            //EmailAddress
             'ip'               => $ip,               //ip
             'return_url'        => $return_url,        //real trading websites
             'phone'            => $contactno,            //Phone Number 
             'country'          => $country,          //Country
             'state'            => $state,            //State
             'city'             => $city,             //City
             'address'          => $address,          //Address
             'zip'              => $zipcode,              //Zip Code
             //'csid'              => "abc",              //
             //'issuing_bank'     => "bank",			 //
             //'user_agent'		=> $useragent,
             'dob'		=> $dob,
             'notifyURL'		=> $callback_url
            );

      
       $data =  http_build_query($arr);
			
			
	
	  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
	  array_pop($documentroot);
	  array_push($documentroot, 'logs');
	  $root_path = implode('/', $documentroot);
	  
	  $logmessage = date('Y-m-d H:i:s')." hmtsquare request data :".json_encode($data)."\n";
	  file_put_contents($root_path.'/payments_newhmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	  
	  
	  $url  = "https://services.payproexpress.com/apinterface"; 
       
            
	    //===============================
	    $curl = curl_init($url);
	    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
	    curl_setopt($curl,CURLOPT_RETURNTRANSFER, false);// Show the output
	    curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
	    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
	    curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
	    $jsonrs = curl_exec($curl);
	    curl_close ($curl); 
	
	
	    $result = json_decode($jsonrs,TRUE);
			 
		$logmessage = date('Y-m-d H:i:s')." hmtsquare response data :".$jsonrs."\n";
		file_put_contents($root_path.'/payments_newhmtsquare.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);		
		if($result['status']==0){
 		$order_token = $result['order_token'];
 		update_payment_foreignid($_PAYMENT_ID, $result['ap_transaction_number']);
		?>
			<script type="text/javascript">
			var script = document.createElement('script');
			var order_token = '<?php echo $order_token ?>';
			script.setAttribute('type', 'text/javascript');
			script.setAttribute('src', 'https://vpay.hmtsquare.com/js/pg_dr.js');
			script.setAttribute('id', 'mpscript');
			script.setAttribute('data-otk', order_token);
			document.head.appendChild(script);
			</script>
		<?php
		
 	}
			exit;
			
			
			
			
			
      		
      		
			$secretAuthKey = trim($_POST['authorisationkey3']);
			$receiver_currency = "USD";
			$session_start_time = time();
			
			# Request parameters in an array
			$requestParamArray = array(
			            'first_name' => trim($name),
			            'last_name' => trim($lastname),
			            'email' => trim($playerDetails->email),
			            'dob' => date('Y-m-d', strtotime(trim($playerDetails->birthdate))),
			            'phone_no' => $contactno,
			            'address' => trim($address),
			            'state_code' => trim($state),
			            'postal_code' => trim($zipcode),
			            'city' => trim($city),
			            'country_code' => trim($country),
			            'fiat_amount' => $currency_amount,
			            'fiat_currency' => trim($params_currency),
			            'crypto_currency' => "BCH",
			            'affiliate_referral_code' => $_POST['authorisationkey2'],
			            'callback_url' => CALLBACKURL."/quickbits/CallbackNotify.php",
			            'request_reference' => "s-".$_PAYMENT_ID,
			            'merchant_profile' => $merchantprofile,
			            'affiliate_redirect_url' => CALLBACKURL."/quickbits/success.php?id=".$_PAYMENT_ID,
			            'settlement_currency' => '',
			        );
			
			# Concat parameters in a string
			$request_data = implode("", $requestParamArray);
			
			# Concat secret key with $request_data and calculate the sha256 hash
			$checksum  = hash('sha256', $request_data . $secretAuthKey);
			
			# Add the calculated checksum to the bottom of the request array
			$requestParamArray['checksum'] = $checksum;
			
			# Send the CURL POST request at the API end point
			$url = trim($_POST['authorisationkey1']);
			//$url = 'https://test.quickbit.eu/direct_api/create_transaction';
			//print('<pre>');print_r($requestParamArray);exit;
			$qbch = curl_init($url);
			curl_setopt($qbch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($qbch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($qbch, CURLOPT_POST, 1);
			curl_setopt($qbch, CURLOPT_POSTFIELDS, $requestParamArray);
			
			# The response is in the variable $response
			$response = curl_exec($qbch);

			curl_close($qbch);
			$logmessage = date('Y-m-d H:i:s')." Quickbits Callback Response: ".$response."\n";
			file_put_contents(dirname(dirname(__DIR__)).'/logs/payments_quickbits.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			$firstRedirecURL = json_decode($response, true);
			$firstReqParamsArray = array(
									'status_code'       => $firstRedirecURL['status_code'],
								    'status_msg'        => $firstRedirecURL['status_msg'],
								    'redirect_url'      => $firstRedirecURL['redirect_url'],
								    "request_reference" => $firstRedirecURL['request_reference']
									);
			$first_request_data = implode("", $firstReqParamsArray);
			$calculated_check_sum  = hash('sha256', $first_request_data . $secretAuthKey);
			
			if ($calculated_check_sum == $firstRedirecURL['checksum']){
				if($firstRedirecURL['status_code'] == 201){
			
					if (!is_wap($_PARAMS['web_id'])){
						$reqwidth = "460px";
					}else{
						$reqwidth = "100%";
					}
					//echo '<iframe id="bitcoincss" src="'.$firstRedirecURL['redirect_url'].'" height="1250"  width="'.$reqwidth.'" style="display:block; border:none;"></iframe>';
			?>
					<script language="javascript">
						parent.parent.parent.location = "<?php echo $firstRedirecURL['redirect_url']; ?>";
					</script> 
			<?php
				}else{
					update_payments_status($_PAYMENT_ID, $_PARAMS['player_id'], 'KO', $firstRedirecURL['status_msg']);
			?>
			<div style="text-align: center;display: inline-block;width: 100%;">
			<img src="img/preloader.gif" /><br><br>
			<div style="background: #ffd2d2;padding: 10px;line-height: 1.3em;border-radius: 5px;">
				We are unable to process your payment request because of<br>
				<?php echo $firstRedirecURL['status_msg']; ?>
			</div>
			</div>
			<script type="text/javascript">
				var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&s=declined' ?>";
				setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 6000);
			</script>
			<?php
				}
			}else{
				update_payments_status($_PAYMENT_ID, $_PARAMS['player_id'], 'KO', 'Checksum validation failed');
		?>
			<div style="text-align: center;display: inline-block;width: 100%;">
			<img src="img/preloader.gif" /><br><br>
			<div style="background: #ffd2d2;padding: 10px;line-height: 1.3em;border-radius: 5px;">
				We are unable to process your payment request because of<br>
				Payment is not authorized.
			</div>
			</div>
			<script type="text/javascript">
				var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&s=declined' ?>";
				setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 6000);
			</script>
		<?php
			}
			$session_start_time = time();
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

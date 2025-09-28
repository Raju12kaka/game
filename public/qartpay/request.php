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
			
			$contactno = str_replace('+', '', $playerDetails->mobileno);
			$contactno = str_replace(' ', '', $contactno);
			$contactno = str_replace('-', '', $contactno);
			$currency='356';
			
			
			
			  $payid            = trim($_POST['authorisationkey1']); //MerchantNo.
		      $saltkey 	        = trim($_POST['authorisationkey2']); //terminalname.
		      $paymenturl		= trim($_POST['authorisationkey3']); 
		
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
			
			
			$callback_url= CALLBACKURL.'/qartpay/CallbackNotify.php';
			
			$upi = trim($_POST['upi']);
			
		
			
			/*Hash Generation*/
			$parameters = array();
			$parameters['AMOUNT']=$orderAmount;
			$parameters['CURRENCY_CODE']=$currency;
			$parameters['CUST_EMAIL']=strtoupper($email);
			$parameters['CUST_NAME']=$name;
			$parameters['CUST_PHONE']=$contactno;
			$parameters['ORDER_ID']=$_PAYMENT_ID;
			$parameters['PAY_ID']=$payid;
			$parameters['PRODUCT_DESC']="Product Purchase";
			$parameters['RETURN_URL']=$callback_url;
			$parameters['TXNTYPE']="SALE";
			/*$parameters['CUST_FIRST_NAME']=$name;
			$parameters['CUST_LAST_NAME']=$lastname;
			$parameters['CUST_STREET_ADDRESS1']=$address;
			$parameters['CUST_CITY']=$city;
			$parameters['CUST_SHIP_LAST_NAME']=$lastname;
			$parameters['CUST_SHIP_NAME']=$name;
			$parameters['CUST_SHIP_STREET_ADDRESS1']=$address;
			$parameters['CUST_SHIP_STREET_ADDRESS2']=$address;
			$parameters['CUST_SHIP_CITY']=$city;
			$parameters['CUST_SHIP_STATE']=$state;
			$parameters['CUST_SHIP_COUNTRY']='India';
			$parameters['CUST_SHIP_ZIP']=$zipcode;
			$parameters['CUST_SHIP_PHONE']=$contactno;*/
			
			if($_PARAMS['payment_method_id']==133){
			$parameters['CARD_NUMBER']=$cardNo;
			$parameters['CARD_EXP_DT']=$cardExpireMonth.$cardExpireYear;
			$parameters['CVV']=$cardSecurityCode;
			$parameters['MOP_TYPE']='VI';
			$parameters['PAYMENT_TYPE']='DC';
			//$parameters['MERCHANT_PAYMENT_TYPE']='CC';
			}else{
		    $parameters['MOP_TYPE']='UP';
			$parameters['PAYMENT_TYPE']='UP';
			//$parameters['MERCHANT_PAYMENT_TYPE']='UP';
			$parameters['UPI']=$upi;	
			}
			
			 ksort($parameters);
			
			//print_r($parameters);
			$hash_string="";
			$cnt_parameters = count($parameters);
			$kk=1;
			foreach ($parameters as $key => $value) {
				if($kk==$cnt_parameters){
				$hash_string.= $key.'='.$value;	
				}else{
				$hash_string.= $key.'='.$value.'~';
				}
				$kk++;
			}
			
			$hash_new_string = $hash_string.$saltkey;
			//echo $hash_new_string.'<br>'; 
			$hash= strtoupper(hash('sha256', $hash_new_string));
			//echo $hash;
			/*Hash Generation End*/
			/**  Log message start **/
			  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
			  array_pop($documentroot);
			  array_push($documentroot, 'logs');
			  $root_path = implode('/', $documentroot);
			  
			  $logmessage = date('Y-m-d H:i:s')." qartpay request data :".json_encode($parameters)."\n";
			  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			  /**  Log message end **/
			
			
			/*Form Generation*/
			if($_PARAMS['payment_method_id']==134){
			?>
			
			<form action="<?php echo $paymenturl; ?>" style="display:none;" target="_top" method="post" name="post_form" id="post_form">
			<input type="text" name="AMOUNT" value="<?php echo $orderAmount; ?>"/>
			<input type="text" name="CURRENCY_CODE" value="<?php echo $currency; ?>"/>
			<input type="text" name="CUST_EMAIL" value="<?php echo strtoupper($email); ?>"/>
			<input type="text" name="CUST_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_PHONE" value="<?php echo $contactno; ?>"/>
			<input type="text" name="MOP_TYPE" value="UP"/>
			<input type="text" name="ORDER_ID" value="<?php echo $_PAYMENT_ID; ?>"/>
			<input type="text" name="PAYMENT_TYPE" value="UP"/>
			<input type="text" name="PAY_ID" value="<?php echo $payid; ?>"/>
			<input type="text" name="PRODUCT_DESC" value="<?php echo "Product Purchase"; ?>"/>
			<input type="text" name="RETURN_URL" value="<?php echo $callback_url; ?>">
			<input type="text" name="TXNTYPE" value="SALE"/>
			<!--<input type="text" name="CARD_NUMBER" value="<?php echo $cardNo; ?>"/>
			<input type="text" name="CARD_EXP_DT" value="<?php echo $cardExpireMonth.$cardExpireYear; ?>"/>
			<input type="text" name="CVV" value="<?php echo $cardSecurityCode; ?>"/>-->
			<input type="text" name="UPI" value="<?php echo $upi; ?>"/>
			<input type="text" name="HASH" value="<?php echo $hash; ?>"/>
			<!--<input type="text" name="CUST_FIRST_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_SHIP_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS2" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_STATE" value="<?php echo $state; ?>"/>
			<input type="text" name="CUST_SHIP_COUNTRY" value="India"/>
			<input type="text" name="CUST_SHIP_ZIP" value="<?php echo $zipcode; ?>"/>
			<input type="text" name="CUST_SHIP_PHONE" value="<?php echo $contactno; ?>"/>
			<input type="text" name="MERCHANT_PAYMENT_TYPE" value="UP"/>-->
			
			<!--<button type="submit" value="Click to Pay" name="submit">SUBMIT</button>-->
			</form>
			<script language="javascript" src="js/jquery.min.js"></script>
			
			<script type="text/javascript">
            setTimeout(function(){ document.getElementById("post_form").submit(); }, 1000);
            </script>
			
			
			<?php 
			}
          
		    if($_PARAMS['payment_method_id']==133){
			?>
			
			<form action="<?php echo $paymenturl; ?>" style="display:none;" target="_top" method="post" name="post_form" id="post_form">
			<input type="text" name="AMOUNT" value="<?php echo $orderAmount; ?>"/>
			<input type="text" name="CURRENCY_CODE" value="<?php echo $currency; ?>"/>
			<input type="text" name="CUST_EMAIL" value="<?php echo strtoupper($email); ?>"/>
			<input type="text" name="CUST_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_PHONE" value="<?php echo $contactno; ?>"/>
			<input type="text" name="MOP_TYPE" value="VI"/>
			<input type="text" name="ORDER_ID" value="<?php echo $_PAYMENT_ID; ?>"/>
			<input type="text" name="PAYMENT_TYPE" value="DC"/>
			<input type="text" name="PAY_ID" value="<?php echo $payid; ?>"/>
			<input type="text" name="PRODUCT_DESC" value="<?php echo "Product Purchase"; ?>"/>
			<input type="text" name="RETURN_URL" value="<?php echo $callback_url; ?>">
			<input type="text" name="TXNTYPE" value="SALE"/>
			<input type="text" name="CARD_NUMBER" value="<?php echo $cardNo; ?>"/>
			<input type="text" name="CARD_EXP_DT" value="<?php echo $cardExpireMonth.$cardExpireYear; ?>"/>
			<input type="text" name="CVV" value="<?php echo $cardSecurityCode; ?>"/>
			<!--<input type="text" name="UPI" value="<?php echo $upi; ?>"/>-->
			<input type="text" name="HASH" value="<?php echo $hash; ?>"/>
			<!--<input type="text" name="CUST_FIRST_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_SHIP_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS2" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_STATE" value="<?php echo $state; ?>"/>
			<input type="text" name="CUST_SHIP_COUNTRY" value="India"/>
			<input type="text" name="CUST_SHIP_ZIP" value="<?php echo $zipcode; ?>"/>
			<input type="text" name="CUST_SHIP_PHONE" value="<?php echo $contactno; ?>"/>
			<input type="text" name="MERCHANT_PAYMENT_TYPE" value="CC"/>-->
			
			<!--<input type="submit" value="Click to Pay" name="submit"/>-->
			</form>
			<script language="javascript" src="js/jquery.min.js"></script>
			
			<script type="text/javascript">
            setTimeout(function(){ document.getElementById("post_form").submit(); }, 1000);
            </script>
			
			
			<?php 
			}
         
			/*ENd Form Generation*/
			
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

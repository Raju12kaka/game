<?php 
	
	define('PROVIDER_NAME', $_POST['providerName']);
	
	$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];
	
	$_PAYMENTID = isset($_POST['dummypaymentID']) ? $_POST['dummypaymentID'] : '';
	$_VIRTUALPT = isset($_POST['vtpaymentId']) ? $_POST['vtpaymentId'] : '';
	
	if ($_REQUEST_ID){
	
		include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
		include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
		include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
		
		$_PARAMS = array();
		$_PARAMS = get_params($_REQUEST_ID);
		
		// Get all player details
		$palyer_details 	= get_player_all_details($_PARAMS['player_id']);
		//get country three letter code
		$getcountrycode = fetchCountryCode($palyer_details->country);
		$encrypted_card_no 	= $_POST['cardNumber'];
		//$encrypted_card_no = encrypt_card($_POST['cardNumber'], 'encode');
		
		// Get all player details
		$methodData 		= getPaymenthodDetails($_PARAMS['payment_method_id']);
		
		$_PARAMS['amount'] 	= $_PARAMS['amount'] / 100;
		
		if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
			
			/** Setting default status **/
			$_ARR_RESP 				= array();
			$_ARR_RESP['Foreign'] 	= time('U');
			$_ARR_RESP['Error'] 	= 201;
			$_ARR_RESP['Status'] 	= 'Declined';
			
			$_RESULT['status'] 		= 'Initiated';
            $_RESULT['errorcode'] 	= 207;
            $_RESULT['foreign_errorcode'] = 0;
            $_RESULT['html']   		= 'Pending';

            			
			/* **********************************************************************
			Description:
			1)Please transafer interface parameters to radinatpay by using POST.
			2)Testing datas,Merchant ID, gateway URL. have been saved to payment providers table.Please write down your value in following variables accordingly.
			3)After payment, it will return JSON data
			**********************************************************************/
			$merName			= trim($_POST['authorisationkey1']); //MerchantName.
			$apiKey				= trim($_POST['authorisationkey2']); //apikey.
			$gateway_url		= trim($_POST['authorisationkey3']); //terminalname.
			
			$orderNo			= trim($_PARAMS['id']);
			$orderAmount		= trim($_PARAMS['amount']);
			$cardNo				= trim($_POST['cardNumber']);
			$cardExpireYear		= trim($_POST['cardExpireYear']);
			$cardExpireMonth	= trim($_POST['cardExpireMonth']);
			$cardSecurityCode	= trim($_POST['cardSecurityCode']);
			$ip					= long2ip($palyer_details->register_IP);
			
			/* Still need to get from DB */
			$firstName			= !empty($_POST['billingname']) ? $_POST['billingname'] : $palyer_details->realname;
			$lastName			= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $palyer_details->reallastname;
			$email				= $palyer_details->email; 
			$phone				= $palyer_details->contact_phone;
			$currency			= "USD";
			$card_type			= trim($_POST['authorisationkey4']);
			   
			$country			= !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
			$state				= !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
			$city				= trim($_POST['billingcity']);
			$address			= trim($_POST['billingaddress']);
			$zip				= !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
			$birthdate			= $palyer_details->birthdate;
			
			/****************************** 
			Submitting parameters by using curl and get returned JSON data
			****************************/
			
			$documentroot 	= explode('/', $_SERVER['DOCUMENT_ROOT']);
			array_pop($documentroot);
			array_push($documentroot, 'logs');
			$root_path 		= implode('/', $documentroot);
			
			//generating signature
			$orderamount = number_format($orderAmount, 2, '.', '');
			$signature = $merName."S7C".$orderNo.$currency.$orderamount.$firstName.$lastName.$cardNo.$cardExpireYear.$cardExpireMonth.$cardSecurityCode.$email.$apiKey;
			$signature = hash('sha256', $signature);
			
			$logmessage 	= date('Y-m-d H:i:s')." PAYOFIX Signature create :".$merName."S7C".$orderNo.$currency.$orderamount.$firstName.$lastName.$cardNo.$cardExpireYear.$cardExpireMonth.$cardSecurityCode.$email.$apiKey."\n";
			file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			
			$logmessage 	= date('Y-m-d H:i:s')." PAYOFIX Signature :".$signature."\n";
			file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			
			$requestArr = array(
				'firstname'			=> $firstName,				//Customer first name
				'lastname'			=> $lastName,				//Customer last name
				'email'				=> $email,					//EmailAddress
				'telephone'			=> substr($phone, -10),		//Phone Number
				'ip_addr'			=> $ip,						//Customer IPAddress
				'country'			=> $country,				//Country
				'state'				=> $state,					//State
				'city'				=> $city,					//City
				'address_1'			=> $address,				//Address
				'zip'				=> $zip,					//Zip Code
				'shipping_country'	=> $country,				//Country
				'shipping_state'	=> $state,					//State
				'shipping_city'		=> $city,					//City
				'shipping_address_1'=> $address,				//Address
				'shipping_zip'		=> $zip,					//Zip Code
				'order_number'		=> "S7C".$orderNo,			//OrderNo.
				'amount'			=> $orderamount,            //OrderAmount
				'currency'			=> $currency,				//Currency
				'card_number'		=> $cardNo,					//CardNo
				'card_code'			=> $cardSecurityCode,		//CVV
				'card_month'		=> $cardExpireMonth,		//CardExpireMonth
				'card_year'			=> $cardExpireYear,			//CardExpireYear
				'username'			=> $merName, 				//Customer Id
				'signature'			=> $signature,				//Customer product id
				'callback_url'      => CALLBACKURL.'/payofix3dscallback.php',
				'success_url'       => CALLBACKURL.'/payofix3dsnotify.php/?id='."S7C".$orderNo,
				'fail_url'          => CALLBACKURL.'/payofix3dsnotify.php/?id='."S7C".$orderNo
			);
			
			$curlRequestURL		= $gateway_url;
			$logmessage 	= date('Y-m-d H:i:s')." PAYOFIX cards request data :".http_build_query($requestArr)."\n";
			file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			
			//===============================//
			$curl = curl_init($curlRequestURL);
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
			curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
			curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($requestArr));//Transmit datas by using POST
			//curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
			$jsonresponse = curl_exec($curl);
			curl_close ($curl);
			
			//decode the encoded json response data
			$finalResult 	= json_decode($jsonresponse,TRUE);
			//echo "<pre>RESPONSE::"; print_r($finalResult); exit;
			/***
			 * saving response from processor in the logs
			 * start
			 ***/
			
			$logmessage 	= date('Y-m-d H:i:s')." PAYOFIX cards response data :".$jsonresponse."\n";
			file_put_contents($root_path.'/payments_payofix.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			/*** saving logs end ***/
			
			if(isset($finalResult['redirect']) && !empty($finalResult['redirect']))
			{
				//insert into payments table
	            $_PAYMENT_ID = false;
				
				//set card details to save
		        $_PARAMS['cardnumber']      = trim($_POST['cardNumber']);
		        $_PARAMS['cvv']             = trim($_POST['cardSecurityCode']);
		        //$_PARAMS['cardnumber'] 	    = $encrypted_card_no;
				//$_PARAMS['cvv'] 			= encrypt_card($_POST['cardSecurityCode'], 'encode');
		        $_PARAMS['expiryyear']      = trim($_POST['cardExpireYear']); //set card expire year
		        $_PARAMS['expirymonth']     = trim($_POST['cardExpireMonth']); //set card expire month
		        $_PARAMS['cardname']        = trim($_POST['cardName']); //set name on card
		        if(isset($_POST['ssnnumber'])){
				  	$_PARAMS['ssnnumber']		= trim($_POST['ssnnumber']); //ssn number
				  }
		
		        $_PARAMS['billingname']		= trim($_POST['billingname']);
				$_PARAMS['billinglastname']	= trim($_POST['billinglastname']);				  
				$_PARAMS['billingaddress']	= trim($_POST['billingaddress']);
				$_PARAMS['billingcity']		= trim($_POST['billingcity']);
				$_PARAMS['billingcountry']	= trim($_POST['billingcountry']);
				$_PARAMS['billingstate']	= trim($_POST['billingstate']);
				$_PARAMS['billingzip']		= trim($_POST['billingzip']);
		        $_PARAMS['useragent']       = $_POST['origin'];
				$_PARAMS['binrule_id']	= trim($_POST['binrule_id']);
				
				//inserting payment before going to process the transaction
				$_PAYMENT_ID 	= insert_payment($_RESULT, $_PARAMS);
				if($_PAYMENT_ID && isset($finalResult['transaction_id']))
				{
					update_payment_foreignid($_PAYMENT_ID, $finalResult['transaction_id']);
				}
?>
				<script language="javascript">parent.parent.parent.location = "<?php echo $finalResult['redirect']; ?>";</script>
<?php		
			exit();		
			}			
			
			$tradeNo		= $finalResult['transaction_id'];//return tradeNo
			$orderNo		= $orderNo;//return orderno
			$orderStatus	= $finalResult['success'];//return orderStatus
			$orderResult	= $finalResult['result'];//return orderStatus
			$orderInfo		= !empty($finalResult['message']) ? $finalResult['message'] : "";//return orderInfo
			$riskInfo		= $finalResult['message'];//return riskInfo
			$acquirerInfo	= $finalResult['descriptor'];//return acquirerInfo   
			        
			if (isset($finalResult)){
			
				$_ARR_RESP['Foreign']		= $tradeNo;   //return Id need to 
				
				$_PARAMS['foreign_id']		= null;
				$_PARAMS['foreign_id']		= $_ARR_RESP['Foreign'];
				set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				  
				//set card details and billing information to save  
				//$_PARAMS['cardnumber']		= trim($_POST['cardNumber']);
				$_PARAMS['cardnumber'] 	    = $encrypted_card_no;
				$_PARAMS['cvv']				= trim($_POST['cardSecurityCode']);
  		  		//$_PARAMS['cvv'] 			= encrypt_card($_POST['cardSecurityCode'], 'encode');
				$_PARAMS['expiryyear']		= trim($_POST['cardExpireYear']); //set card expire year
				$_PARAMS['expirymonth']		= trim($_POST['cardExpireMonth']); //set card expire month
				$_PARAMS['cardname']		= trim($_POST['cardName']); //set name on card
				if(isset($_POST['ssnnumber'])){
					$_PARAMS['ssnnumber']	= trim($_POST['ssnnumber']); //ssn number
				}
				
				$_PARAMS['billingname']		= trim($_POST['billingname']);
				$_PARAMS['billinglastname']	= trim($_POST['billinglastname']);				  
				$_PARAMS['billingaddress']	= trim($_POST['billingaddress']);
				$_PARAMS['billingcity']		= trim($_POST['billingcity']);
				$_PARAMS['billingcountry']	= trim($_POST['billingcountry']);
				$_PARAMS['billingstate']	= trim($_POST['billingstate']);
				$_PARAMS['billingzip']		= trim($_POST['billingzip']);
				$_PARAMS['billingphone'] 	= trim($_POST['billingphone']);
				$_PARAMS['useragent']		= $_POST['origin'];
				$_PARAMS['acquirer']		= $acquirerInfo;
				
				if($orderStatus == true && $orderResult == 1){
				
					$_ARR_RESP['Status'] = 'Approved';
					  
					if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
						if ($_ARR_RESP['Status'] == 'Approved'){
							$_RESULT['status']		= 'Success';
							$_RESULT['errorcode']	= 0;
							//$_RESULT['html']		= $orderInfo."||".$riskInfo;
							$_RESULT['html']		= $orderInfo;
						} else {
							$_RESULT['status']		= 'Error';
							$_RESULT['errorcode']	= 207;
							$_RESULT['foreign_errorcode']= $_ARR_RESP['Error'];
							//$_RESULT['html']		= $orderInfo."||".$riskInfo;
							$_RESULT['html']		= $orderInfo;
						}
					} else {
						$_RESULT['status']		= 'Error';
						$_RESULT['errorcode']	= 204;
						$_RESULT['html']		= 'Update error';
					}
				          
				}
				else if($orderStatus == true && $orderResult == 0){
					//$_RESULT['status']		= 'Pending';
					$_RESULT['errorcode']	= 207;
					$_RESULT['foreign_errorcode']= $_ARR_RESP['Error'];
					$_RESULT['html']		= $orderInfo."||".$riskInfo;		
				}
				else
				{
				  	
					//check if dummy available or not
					$card_details			= get_payment_card_details($encrypted_card_no);
					$card_number			= !empty($card_details->cardnumber) ? $card_details->cardnumber : '';
					$paymentMethod = ($_PARAMS['payment_method_id'] == 100) ? 106 : 107;
					$paymentProvider = ($paymentMethod == 106) ? 111 : 112;
					$dummymethods = implode(',', array(106,107));
					$checkPlayerHasDummy	= get_player_hasdummy($_PARAMS['player_id'], $dummymethods);
					//check if the player country is enable for dummy process
					//$checkCountryEnable = get_country_dummy_status($_PARAMS['country_id']);
					/*
					//if($methodData->is_dummy == 1 && ($card_number != $encrypted_card_no) && ($checkPlayerHasDummy->cnt == 0) && ($checkCountryEnable->is_enable == 1)){
					if($methodData->is_dummy == 1 && ($card_number != $encrypted_card_no) && ($checkPlayerHasDummy->cnt == 0)){	
						$checkProviderBasedFailDeposit = get_provider_based_fails($_PARAMS['player_id']);
						if($checkProviderBasedFailDeposit == 0 && $checkPlayerHasDummy->cnt == 0){
							include_once dirname(dirname(dirname(__FILE__))).'/models/dummymoney.php';
							$dummyamt = new AddDummyMoney();
							$response = $dummyamt::process_dummy_amount($_PARAMS, $paymentMethod, $paymentProvider, $tradeNo, $orderInfo);
							$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$response['paymentId'].'&amt='.$_PARAMS['amount'];
						
						?>
							<script language="javascript">parent.parent.location = "<?php echo $_LOCATION_URL; ?>";</script>						 
						<?php
							exit;
						}
					}
					*/	
					$_RESULT['status'] = 'Declined';
					$_RESULT['errorcode'] = 207;
					$_RESULT['html'] = $orderStatus."||".$orderInfo."||".$riskInfo;
				}
			}else{
				
			}
		
		}
		else
		{
			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 102;
			$_RESULT['html'] = 'Infromation Incorrect';
		}
		
	} 
	else 
	{ // NO REQUEST_ID
		$_RESULT['status'] = 'Error';
		$_RESULT['errorcode'] = 101;
		$_RESULT['html'] = 'No request id';
	}
	
	if(!empty($_PAYMENTID) && $_RESULT['status'] == 'Success'){
	
		$id = update_payments($_PAYMENTID, $_PARAMS, $tradeNo);
	}elseif(!empty($_PAYMENTID) && $_RESULT['status'] != 'Success'){
		insert_payment($_RESULT, $_PARAMS);
		$id = insert_vt_payments($_PAYMENTID, $_PARAMS, $_RESULT);
	}else{
		$_PAYMENT_ID = false;
		$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
	}
	
	if ($_PAYMENT_ID){
		//$_LOCATION_URL  = $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID;
		if($_RESULT['status'] == 'Success')
		{
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['player_currency_amount']/100;
		}
		else
		{
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&s=declined';
		}
	} elseif($_CANCELLED) {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=cancelled';
	} else {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=unknown';
	}
	
	if (!$_PARAMS['redirect_url']){
		$_LOCATION_URL = HOST_HTTP_WEB;
	}
	
	//loading message before completing payment
	include_once '../loader.php';
	
	if(!empty($_PAYMENTID)){
		if($_RESULT['status'] == 'Success'){
			echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
		}else{
			echo "<b color='green'>Transaction failed. Please try again.</b>";
		}
		?>
		<script language="javascript">
			parent.parent.location="<?php echo CRMURL; ?>Transacciones/Depositos/?msg=<?php echo $_RESULT['status']; ?>";
		</script>
	<?php 
	}else if($_VIRTUALPT == 1){
		if($_RESULT['status'] == 'Success'){
			echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
		}else{
			echo "<b color='green'>Transaction failed. Please try again.</b>";
		}
		?>
		<script language="javascript">
			parent.parent.location="<?php echo $_PARAMS['redirect_url'] ?>/?msg=<?php echo $_RESULT['status'].'&result='.$_PAYMENT_ID; ?>";
		</script>
	<?php 
	}else{ ?>
		<script language="javascript">
			parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
		</script>
<?php 
	} 
?>
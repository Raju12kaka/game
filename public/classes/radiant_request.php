<?php 
	
	define('PROVIDER_NAME', $_POST['providerName']);
	
	/***************** Set log root path ********************/
	$documentroot 	= explode('/', $_SERVER['DOCUMENT_ROOT']);
	array_pop($documentroot);
	array_push($documentroot, 'logs');
	$root_path 		= implode('/', $documentroot);
	/***************** Set log root path ********************/
	
	$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];
	
	$_PAYMENTID = isset($_POST['dummypaymentID']) ? $_POST['dummypaymentID'] : '';
	$_VIRTUALPT = isset($_POST['vtpaymentId']) ? $_POST['vtpaymentId'] : '';
	
	if ($_REQUEST_ID){
	
		include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
		include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
		include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
		//include_once dirname(dirname(dirname(__FILE__))).'/models/acuitytechprocess.php';
		
		$_PARAMS = array();
		$_PARAMS = get_params($_REQUEST_ID);
		
		// Get all player details
		$palyer_details 	= get_player_all_details($_PARAMS['player_id']);
		//get country three letter code
		$getcountrycode = fetchCountryCode($palyer_details->country);
		$encrypted_card_no 	= $_POST['cardNumber'];
		
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
			$merId				= trim($_POST['authorisationkey1']); //MerchantNo.
			$gateway_url		= trim($_POST['authorisationkey2']); //terminalname.
			
			$orderNo			= trim($_PARAMS['id']);
			$orderAmount		= trim($_PARAMS['amount']);
			$cardNo				= trim($_POST['cardNumber']);
			$cardExpireYear		= trim($_POST['cardExpireYear']);
			$cardExpireMonth	= trim($_POST['cardExpireMonth']);
			$cardSecurityCode	= trim($_POST['cardSecurityCode']);
			//$ip					= long2ip($palyer_details->register_IP);
			
			/*if($_VIRTUALPT)
			{
				$ip_arr         = get_player_lastlogin_ip($_PARAMS['player_id']);
				if($ip_arr[0]['ip'])
				{
					$ip         = long2ip($ip_arr[0]['ip']);	
				}
				else {
					$ip			= long2ip($palyer_details->register_IP);
				}
			}
			else {
				$ip				= trim($_SERVER['REMOTE_ADDR']);	
			}*/
			
			$ip               = long2ip($palyer_details->register_IP);
			
			/* Still need to get from DB */
			$firstName			= !empty($_POST['billingname']) ? $_POST['billingname'] : $palyer_details->realname;
			$lastName			= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $palyer_details->reallastname;
			$email				= $palyer_details->email; 
			$phone				= explode(' ', $palyer_details->contact_phone);
			$phone				= $phone[1];
			$currency			= "USD";
			$card_type			= trim($_POST['authorisationkey3']);
			   
			$country			= !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
			$state				= !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
			$city				= trim($_POST['billingcity']);
			$address			= trim($_POST['billingaddress']);
			$zip				= !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
			$birthdate			= $palyer_details->birthdate;
			
			/****************************** 
			Submitting parameters by using curl and get returned JSON data
			****************************/
			$requestArr = array(
				'tr_merchant_id'		=> $merId,					//Merchant id provided by processor
				'tr_firstname'			=> $firstName,				//Customer first name
				'tr_lastname'			=> $lastName,				//Customer last name
				'tr_email'				=> $email,					//EmailAddress
				'tr_phone'				=> substr($phone, -10),		//Phone Number
				'tr_ip'					=> $ip,						//Customer IPAddress
				'tr_address1'			=> $address,				//Address
				'tr_city'				=> $city,					//City
				'tr_state'				=> $state,					//State
				'tr_zip'				=> $zip,					//Zip Code
				'tr_country'			=> $getcountrycode->code3,	//Country
				'tr_card_no'			=> $cardNo,					//CardNo
				'tr_card_security_code'	=> $cardSecurityCode,		//CVV
				'tr_customer_id'		=> $palyer_details->id, 	//Customer Id
				'tr_product_id'			=> 'PROD',					//Customer product id
				'tr_card_expiry_month'	=> $cardExpireMonth,		//CardExpireMonth
				'tr_card_expiry_year'	=> $cardExpireYear,			//CardExpireYear
				'tr_issuing_bank'		=> 'BANK',					//Issuing Bank name
				'tr_merchant_ref_id'	=> "S7".$orderNo,			//OrderNo.
				'tr_payment_method'		=> $card_type,				//Card Type
				'tr_card_type'			=> $card_type,				//Card Type
				'tr_amount'				=> number_format($orderAmount, 2, '.', ''),//OrderAmount
				'tr_currency_code'		=> $currency,				//Currency
				'tr_remark'				=> 'slots7casino', 		//Remarks
				'tr_dob'				=> $birthdate,				//Dateofbirth
				'tr_return_url'			=> CALLBACKURL,				//Returnurl for response
			);
			
			$curlRequestURL		= $gateway_url;
			
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
			
			
			
			
			/*Submit from cron start*/
			file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." radiant response data: ".$json.PHP_EOL , FILE_APPEND | LOCK_EX);		
			if(isset($_POST['submitfrom']) && $_POST['submitfrom']=="cron"){
				
			 $result1['tradeNo']      =  $finalResult['transaction_id'];//return tradeNo
             $result1['orderNo']      =  !empty($finalResult['tr_merchant_ref_id']) ? substr($finalResult['tr_merchant_ref_id'], 2) : $orderNo;//return orderno
             $result1['orderAmount']  =  $orderAmount;//return orderAmount
             $result1['orderCurrency']=  $currency;//return orderCurrency
             if($finalResult['response'] == 'SUCCESSFUL'){
	         $result1['orderStatus'] = 1;	
	         }else{
	         $result1['orderStatus']  = $finalResult['response'];//return orderStatus
			 }
             
             $orderInfo      =   !empty($finalResult['message']['message']) ? $finalResult['message']['message'] : $finalResult['message']['orderInfo'];//return orderInfo
            
             $result1['orderInfo']    = $orderInfo;//return riskInfo
             file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." radiant response data for cron: ".json_encode($result1).PHP_EOL , FILE_APPEND | LOCK_EX);
			 echo  json_encode($result1);exit;
				
			}
			/*End Submit from cron*/
			
			/***
			 * saving response from processor in the logs
			 * start
			 ***/
			
			$logmessage 	= date('Y-m-d H:i:s')." Radiantpay response data :".$jsonresponse."\n";
			file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			/*** saving logs end ***/
			
			$tradeNo		= $finalResult['transaction_id'];//return tradeNo
			$orderNo		= !empty($finalResult['tr_merchant_ref_id']) ? substr($finalResult['tr_merchant_ref_id'], 2) : $orderNo;//return orderno
			$orderStatus	= $finalResult['response'];//return orderStatus
			$orderInfo		= !empty($finalResult['message']['message']) ? $finalResult['message']['message'] : $finalResult['message']['message'];//return orderInfo
			$riskInfo		= $finalResult['message']['message'];//return riskInfo
			$acquirerInfo	= $finalResult['message']['message'];//return acquirerInfo   
			        
			if (isset($finalResult)){
			
				$_ARR_RESP['Foreign']		= $tradeNo;   //return Id need to 
				
				$_PARAMS['foreign_id']		= null;
				$_PARAMS['foreign_id']		= $_ARR_RESP['Foreign'];
				set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				  
				//set card details and billing information to save  
				$_PARAMS['cardnumber']		= trim($_POST['cardNumber']);
				$_PARAMS['cvv']				= trim($_POST['cardSecurityCode']);
				$_PARAMS['expiryyear']		= trim($_POST['cardExpireYear']); //set card expire year
				$_PARAMS['expirymonth']		= trim($_POST['cardExpireMonth']); //set card expire month
				$_PARAMS['cardname']		= trim($_POST['cardName']); //set name on card
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
				$_PARAMS['billingphone'] 	= trim($_POST['billingphone']);
				$_PARAMS['useragent']		= $_POST['origin'];
				$_PARAMS['acquirer']		= $acquirerInfo;
				
				if($orderStatus == 'SUCCESSFUL' ){
				
					$_ARR_RESP['Status'] = 'Approved';
					  
					if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
						if ($_ARR_RESP['Status'] == 'Approved'){
							$_RESULT['status']		= 'Success';
							$_RESULT['errorcode']	= 0;
							$_RESULT['html']		= $orderInfo."||".$riskInfo;
						} else {
							$_RESULT['status']		= 'Error';
							$_RESULT['errorcode']	= 207;
							$_RESULT['foreign_errorcode']= $_ARR_RESP['Error'];
							$_RESULT['html']		= $orderInfo."||".$riskInfo;
						}
					} else {
						$_RESULT['status']		= 'Error';
						$_RESULT['errorcode']	= 204;
						$_RESULT['html']		= 'Update error';
					}
				          
				}
				else
				{
				  	  
					//check if dummy available or not
		       		$card_details = get_payment_card_details($encrypted_card_no);
					$card_number = !empty($card_details->cardnumber) ? $card_details->cardnumber : '';
					$paymentMethod = ($_PARAMS['payment_method_id'] == 100) ? 106 : 107;
					$paymentProvider = ($paymentMethod == 106) ? 111 : 112;
					$dummymethods = implode(',', array(106,107));
					$checkPlayerHasDummy = get_player_hasdummy($_PARAMS['player_id'], $dummymethods);
					
					/*if($methodData->is_dummy == 1 && ($card_number != $encrypted_card_no) && ($checkPlayerHasDummy->cnt == 0)){	
						$checkProviderBasedFailDeposit = get_provider_based_fails($_PARAMS['player_id']);
						if($checkProviderBasedFailDeposit == 0 && $checkPlayerHasDummy->cnt == 0){
							include_once dirname(dirname(dirname(__FILE__))).'/models/dummymoney.php';
							$dummyamt = new AddDummyMoney();
							$response = $dummyamt::process_dummy_amount($_PARAMS, $paymentMethod, $paymentProvider, $tradeNo, $orderInfo);
							
							
							
							$_LOCATION_URL  = $_PARAMS['redirect_url'].'?i='.$response['paymentId'];
						
						?>
							<script language="javascript">parent.parent.location = "<?php echo $_LOCATION_URL; ?>";</script>						 
						<?php
							exit;
						}
					} */
          		  $_RESULT['status'] = 'Declined';
		          $_RESULT['errorcode'] = 207;
		          $_RESULT['html'] = $riskInfo;
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
	/*
	echo "<pre>";
	print_r($_PARAMS);
	echo "</pre>";
	echo "<pre>";
	print_r($_RESULT);
	echo "</pre>";
	exit; */
	
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
		if($_RESULT['status'] != 'Success')
		{
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID."&s=declined";	
		}
		else {
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['amount'];
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
			parent.parent.location="<?php echo $_PARAMS['redirect_url'] ?>/?msg=<?php echo $_RESULT['status'].'&result='.$_PAYMENT_ID.'&redirect='.$_PARAMS['player_id']; ?>";
		</script>
	<?php 
	}else{ ?>
		<script language="javascript">
			parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
		</script>
<?php 
	} 
?>
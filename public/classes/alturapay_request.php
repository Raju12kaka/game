<?php 
	include_once '../loader.php';
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
		include_once dirname(dirname(dirname(__FILE__))).'/models/acuitytechprocess.php';
		
		$_PARAMS = array();
		$_PARAMS = get_params($_REQUEST_ID);
		
		// Get all player details
		$palyer_details 	= get_player_all_details($_PARAMS['player_id']);
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
			
			/* **********************************************************************
			Description:
			1)Please transafer interface parameters to lightpay by using POST.
			2)Testing datas,API Key, AuthURL, gateway URL. have been saved to payment providers table.Please write down your value in following variables accordingly.
			3)First we need to send authentication request to get transactionid from the processor
			4)Send the transaction id along with the creditcard info
			5)After payment, it will return JSON data
			**********************************************************************/
			$merchant_id		= trim($_POST['authorisationkey2']); //Merchant Id
			$secret				= trim($_POST['authorisationkey3']); //Secret
			$gateway_url		= trim($_POST['authorisationkey1']); //gateway url
			
			$orderNo			= trim($_PARAMS['id']);
			$orderAmount		= trim($_PARAMS['amount']*100);//Amount should be in cents as per documentation
			$cardNo				= trim($_POST['cardNumber']);
			$cardExpireYear		= trim($_POST['cardExpireYear']);
			$cardExpireMonth	= trim($_POST['cardExpireMonth']);
			$cardSecurityCode	= trim($_POST['cardSecurityCode']);
			//$ip					= long2ip($palyer_details->register_IP);
			if($_VIRTUALPT)
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
				$ip                 = trim($_SERVER['REMOTE_ADDR']);	
			}
			
			
			/******************************
			 * Sending the auth request to get unique transaction id
			 ****************************/
			$authURL = $gateway_url.'/sale.asp';
			$checksum = md5(trim($orderNo).trim($orderAmount).trim($secret));
			$post_data['merchantid']	= $merchant_id;
			$post_data['checksum']	= $checksum;
			$post_data['txid']	= $orderNo;
			$post_data['pmtype']= 'CC';
			$post_data['amount']= $orderAmount;
			$post_data['currencycode']	= $_POST['orderCurrency']?:'USD';
			$post_data['ip']	= $ip;
			$post_data['customer']	= $customer;
			$post_data['pmethod']	= $payment_method;
			$post_data['csid']	= $_POST['csid'];
			
			$post_data['fname']			= !empty($_POST['billingname']) ? $_POST['billingname'] : $palyer_details->realname;
			$post_data['lname']			= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $palyer_details->reallastname;
			$post_data['email']			= $palyer_details->email; 
			$post_data['phone1']			= $palyer_details->contact_phone; 
			$post_data['country']		= !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
			$state						= !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
			//$state_full_name_arr    	= get_states_name($state, $post_data['country']);
			$post_data['province'] 		= in_array($post_data['country'], array('US', 'CA')) ? $state : ''; 
			$post_data['city']			= trim($_POST['billingcity']);
			$post_data['address1']		= urldecode(trim($_POST['billingaddress']));
			$post_data['pcode']			= !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
			$post_data['dob']			= $palyer_details->birthdate;
			
			/*Credit card details*/
			$post_data['cardtype']			= trim($_POST['cardtype']);
			$post_data['cnumber']			= trim($_POST['cardNumber']);
			$post_data['exp_month']		= trim($_POST['cardExpireMonth']);
			$post_data['exp_year']			= trim($_POST['cardExpireYear']);
			$post_data['cvv']				= trim($_POST['cardSecurityCode']);
			
			$acurl = curl_init($authURL);		
			
			curl_setopt($acurl, CURLOPT_SSL_VERIFYPEER, 0);
									//curl_setopt($acurl, CURLOPT_HEADER, array('X-AUTH-TOKEN:DS8-X4234')); // Colate HTTP header
									curl_setopt($acurl, CURLOPT_HTTPHEADER, array("Authorization: MerchantId= ".$merchant_id.", Check-Sum: ".$checksum)); // Colate HTTP header
									curl_setopt($acurl, CURLOPT_HTTPHEADER, 0);
									curl_setopt($acurl, CURLOPT_RETURNTRANSFER, true);// Show the output
									curl_setopt($acurl, CURLOPT_POST, true); // Transmit datas by using POST
									curl_setopt($acurl, CURLOPT_POSTFIELDS, http_build_query($post_data));//Transmit datas by using POST
									$jsonauthresponse = curl_exec($acurl);
									if(curl_errno($acurl)){
										$err = curl_error($acurl);
										$logmessage 	= date('Y-m-d H:i:s')." Bluepay response data for AUTH error : ".$err."\n";
										file_put_contents($root_path.'/payments_bluepay.log', $jsonauthresponse.PHP_EOL , FILE_APPEND | LOCK_EX);
									}
									
			curl_close ($acurl);
			
			$transaction_resp_details = json_decode($jsonauthresponse);
			//echo "<pre>";var_dump($transaction_resp_details);var_dump($jsonauthresponse);exit;
			$logmessage 	= date('Y-m-d H:i:s')." Bluepay response data for AUTH :".$jsonresponse."\n";
			file_put_contents($root_path.'/payments_bluepay.log', $jsonauthresponse.PHP_EOL , FILE_APPEND | LOCK_EX);
			//Set status params
			$status = $transaction_resp_details->status;
			$status_decscription = $transaction_resp_details->status_desc;
			$order_number = $transaction_resp_details->txid;
			//echo "<pre>";print_r($transaction_resp_details);
			//echo 'status :'.$status.'<br> stat descr: '.$status_decscription.'<br>payment id: '.$order_number.'<br>';
			//Check transaction succeeded or faild
			if($status == 1 && $status_decscription == 'Approved' && $order_number)
			{			
				$tradeNo		 = $transaction_resp_details->itxid;//return payment gateway transaction id
				$orderNo		 = $order_number;						//return orderno
				$orderAmount	 = number_format($orderAmount/100, 2);		//return orderAmount
				$orderCurrency	 = $currency;					//return orderCurrency
				$orderStatus	 = $status;		//return orderStatus
				$orderStatusCode = '156';		//return orderStatus
				$orderInfo		 = $status_decscription;//return orderInfo
				$orderRealStatusCode = "156";
				$riskInfo		 = $transaction_resp_details->itxid."||".$status."||".$status_decscription;//return riskInfo
				$acquirerInfo	 = $transaction_resp_details->descriptor;	//return acquirerInfo
				
				$_ARR_RESP['Foreign']	= $tradeNo;   //return Id need to 
				
				$_PARAMS['foreign_id']	= null;
				$_PARAMS['foreign_id']	= $_ARR_RESP['Foreign'];
				set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				  
				//set card details and billing information to save  
				$_PARAMS['cardnumber']		= trim($_POST['cardNumber']);
				$_PARAMS['cvv']				= trim($_POST['cardSecurityCode']);
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
				
				$_RESULT['status'] 		= 'Success';
				$_RESULT['errorcode'] 	= 200;
				$_RESULT['html'] 		= 'Transaction Succussfully Completed';
			}
			else
			{
				$_RESULT['status'] 		= 'Error';
				$_RESULT['errorcode'] 	= 102;
				$_RESULT['html'] 		= $status.' || '.$status_decscription;
			}	
	} 
	else 
	{ // NO REQUEST_ID
		$_RESULT['status'] 		= 'Error';
		$_RESULT['errorcode'] 	= 101;
		$_RESULT['html'] 		= 'No request id';
	}
	

	
	
	/*
	echo "<pre>";
				print_r($_PARAMS);
				echo "</pre>";
				echo "<pre>";
				print_r($_RESULT);
				echo "</pre>";
				echo "Payment ID:".$_PAYMENTID;*/
	
			
	
 
	
	if(!empty($_PAYMENTID) && $_RESULT['status'] == "Success"){
	
		$id = update_payments($_PAYMENTID, $_PARAMS, $tradeNo);
		/*************** Update acuitytec deposit id ************/
		if($_PAYMENTID && !empty($_POST['acutytecid']))
		{
			update_acutytec_paymentid($_PAYMENTID,$_POST['acutytecid']);
			if(!empty($_POST['acutytecinternalid']))
			{
				$post_string =  Array(
					'merchant_id'	=> ACUITYTECHMID,
					'password'		=> ACUITYTECHPASSWORD,
					'trans_id'		=> $_POST['acutytecinternalid'],
					'status'		=> '1',
					'reason'		=> $_RESULT['html'],
					'processor'		=> $_POST['providerName']
				);
				$acuitytecprocess = new AcuitytechProcess();
				$acuitytecprocess->getAcuitytechUpdateData($post_string);
			}
		}
		/*************** Update acuitytec deposit id ************/
	}elseif(!empty($_PAYMENTID) && $_RESULT['status'] != "Success"){
		insert_payment($_RESULT, $_PARAMS);
		$id = insert_vt_payments($_PAYMENTID, $_PARAMS, $_RESULT);
		/*************** Update acuitytec deposit id ************/
		if($_PAYMENTID && !empty($_POST['acutytecid']))
		{
			update_acutytec_paymentid($_PAYMENTID,$_POST['acutytecid']);
			if(!empty($_POST['acutytecinternalid']))
			{
				$post_string =  Array(
					'merchant_id'	=> ACUITYTECHMID,
					'password'		=> ACUITYTECHPASSWORD,
					'trans_id'		=> $_POST['acutytecinternalid'],
					'status'		=> '6',
					'reason'		=> $_RESULT['html'],
					'processor'		=> $_POST['providerName']
				);
				$acuitytecprocess = new AcuitytechProcess();
				$acuitytecprocess->getAcuitytechUpdateData($post_string);
			}
		}
		/*************** Update acuitytec deposit id ************/
	}else{
		$_PAYMENT_ID = false;
		$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
		
		/*************** Update acuitytec deposit id ************/
		if($_PAYMENT_ID && !empty($_POST['acutytecid']))
		{
			update_acutytec_paymentid($_PAYMENT_ID,$_POST['acutytecid']);	
			if($_RESULT['status'] == 1){
				$acuitytech_status = 1;
			}else{
				$acuitytech_status = 6;
			}
			
			if(!empty($_POST['acutytecinternalid']))
			{
				$post_string =  Array(
					'merchant_id'	=> ACUITYTECHMID,
					'password'		=> ACUITYTECHPASSWORD,
					'trans_id'		=> $_POST['acutytecinternalid'],
					'status'		=> $acuitytech_status,
					'reason'		=> $_RESULT['html'],
					'processor'		=> $_POST['providerName']
				);
				$acuitytecprocess = new AcuitytechProcess();
				$acuitytecprocess->getAcuitytechUpdateData($post_string);
			}
		
		}
		/*************** Update acuitytec deposit id ************/
	}
	
	if ($_PAYMENT_ID){
		if($_RESULT['status'] != 1)
		{
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$_PAYMENT_ID."&st=declined";	
		}
		else {
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$_PAYMENT_ID.'&amt='.$_PARAMS['player_currency_amount']/100;
		}
	} elseif($_CANCELLED) {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'&s=cancelled';
	} else {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'&s=unknown';
	}
	
	if (!$_PARAMS['redirect_url']){
		$_LOCATION_URL = HOST_HTTP_WEB;
	}
	
	//loading message before completing payment
//echo "<pre>".$_PAYMENTID;print_r($_REQUEST);exit;	
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
}
?>
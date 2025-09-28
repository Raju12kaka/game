<?php 

define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

$_PAYMENTID = isset($_POST['dummypaymentID']) ? $_POST['dummypaymentID'] : '';
$_VIRTUALPT = isset($_POST['vtpaymentId']) ? $_POST['vtpaymentId'] : '';

$documentroot   = explode('/', $_SERVER['DOCUMENT_ROOT']);
array_pop($documentroot);
array_push($documentroot, 'logs');
$root_path              = implode('/', $documentroot);


if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	// Get all player details
	$palyer_details = get_player_all_details($_PARAMS['player_id']);
	$getcountrycode = fetchCountryCode($palyer_details->country);
	$encrypted_card_no = $_POST['cardNumber'];
	$methodData = getPaymenthodDetails($_PARAMS['payment_method_id']);
	//print('<pre>');print_r($_POST);exit;
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){


		$_ARR_RESP = array();
		$_ARR_RESP['Foreign'] = time('U');
		$_ARR_RESP['Error'] = 201;
		
		$_ARR_RESP['Status'] = 'Declined';
		
		/* **********************************************************************
		   Descriptionï¼š1ã€�Please transafer interface parameters to trust payment gateway by using POST.
		                2ã€�Testing datas,Signkey,MerchantNo.,GatewayNo. have been writen into other parameters in demo.Please write down your value in following variate accordingly.
		                3ã€�After payment, it will return XML
		  **********************************************************************/
		$merNo            = trim($_POST['authorisationkey1']); //MerchantNo.
		$gatewayNo        = trim($_POST['authorisationkey2']); //GatewayNo.
		$signkey          = trim($_POST['authorisationkey3']); //SignKey
		$orderNo          = trim($_PARAMS['id']);
		$orderCurrency    = trim($_PARAMS['currency_id']);
		$orderAmount      = trim($_PARAMS['amount']);
		$cardNo           = trim($_POST['cardNumber']);
		$cardExpireYear   = trim($_POST['cardExpireYear']);
		$cardExpireMonth  = trim($_POST['cardExpireMonth']);
		$cardSecurityCode = trim($_POST['cardSecurityCode']);
		$csid             = "abc";
		$ip               = trim($_POST['ip']);

		/* Still need to get from DB */
		$firstName        = $palyer_details->realname;
		$lastName         = $palyer_details->reallastname;
		$email            = $palyer_details->email;
		$issuingBank      = "bank"; 
		$phone            = $palyer_details->contact_phone;


		$signInfo         = hash("sha256" , $merNo . $gatewayNo . $orderNo . $orderCurrency . $orderAmount . $firstName . $lastName.$cardNo.$cardExpireYear.$cardExpireMonth.$cardSecurityCode.$email.$signkey );
		   
		$country          = !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
		$state            = !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : $palyer_details->state;
		$city             = !empty($_POST['billingcity']) ? trim($_POST['billingcity']) : $palyer_details->city;
		$address          = !empty($_POST['billingaddress']) ? trim($_POST['billingaddress']) : $palyer_details->address;
		$zip              = !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : $palyer_details->zipcode;
		//$zip              = "560046";
		$returnUrl        = "http://".$_SERVER['HTTP_HOST']; //real trading websites

		/****************************** 
		  Submitting parameters by using curl and get returned XML parameters
		  
		****************************/
		$arr = array(
		     'merNo'            => $merNo,            //MerchantNo.
		     'gatewayNo'        => $gatewayNo,        //GatewayNo.
		     'orderNo'          => $orderNo,          //OrderNo.
		     'orderCurrency'    => $orderCurrency,    //OrderCurrency
		     'orderAmount'      => $orderAmount,      //OrderAmount
		     'firstName'        => $firstName,        //FirstName
		     'lastName'         => $lastName,         //lastName
		     'cardNo'           => $cardNo,           //CardNo
		     'cardExpireMonth'  => $cardExpireMonth,  //CardExpireMonth
		     'cardExpireYear'   => $cardExpireYear,   //CardExpireYear
		     'cardSecurityCode' => $cardSecurityCode, //CVV
		     'issuingBank'      => $issuingBank,      //IssuingBank
		     'email'            => $email,            //EmailAddress
		     'ip'               => $ip,               //ip
		     'returnUrl'        => $returnUrl,          //real trading websites
		     'phone'            => $phone,            //Phone Number 
		     'country'          => $country,          //Country
		     'state'            => $state,            //State
		     'city'             => $city,             //City
		     'address'          => $address,          //Address
		     'zip'              => $zip,              //Zip Code
		     'signInfo'         => $signInfo ,         //SignInfo 
		     'csid'             => $csid
		);
		
		$data =  http_build_query($arr);

		
		$url = "https://shoppingingstore.com/TPInterface";
       
        // echo "<pre>"; print_r($arr); exit;
		//===============================
		$curl = curl_init($url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// Show the output
		curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
		curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
		curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
		$xmlrs = curl_exec($curl);
		curl_close ($curl); 
		 
		//===============================
		
		$xmlob = simplexml_load_string(trim($xmlrs));
		
		
		
		/*Submit from cron start*/
		file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." trustpay response data: ".$json.PHP_EOL , FILE_APPEND | LOCK_EX);		
		if(isset($_POST['submitfrom']) && $_POST['submitfrom']=="cron"){
			
		 $result1['tradeNo']      =  (string)$xmlob->tradeNo;//return tradeNo
         $result1['orderNo']      =  (string)$xmlob->orderNo;//return orderno
         $result1['orderAmount']  =  $_PARAMS['amount'];//return orderAmount
         $result1['orderCurrency']=  $_POST['orderCurrency'];//return orderCurrency
         $result1['orderStatus']  =  (string)$xmlob->orderStatus;//return orderStatus
         $result1['orderInfo']    =  (string)$xmlob->orderInfo;//return riskInfo
         file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." trustpay response data for cron: ".json_encode($result1).PHP_EOL , FILE_APPEND | LOCK_EX);
		 echo  json_encode($result1);exit;
			
		}
	    /*End Submit from cron*/
		
		$merNo        = (string)$xmlob->merNo; //return merNo    
		$gatewayNo    = (string)$xmlob->gatewayNo;//return gatewayNo
		$tradeNo      = (string)$xmlob->tradeNo;//return tradeNo
		$orderNo      = (string)$xmlob->orderNo;//return orderno
		$orderAmount  = (string)$xmlob->orderAmount;//return orderAmount
		$orderCurrency= (string)$xmlob->orderCurrency;//return orderCurrency
		$orderStatus  = (string)$xmlob->orderStatus;//return orderStatus
		$orderInfo    = (string)$xmlob->orderInfo;//return orderInfo
		$signInfo     = (string)$xmlob->signInfo;//return signInfo
		$riskInfo     = (string)$xmlob->riskInfo." - ".(string)$xmlob->responseCode;//return riskInfo
		
		//signInfocheck
		$signInfocheck=hash("sha256",$merNo.$gatewayNo.$tradeNo.$orderNo.$orderCurrency.$orderAmount.$orderStatus.$orderInfo.$signkey);


        if (strtolower($signInfo) == strtolower($signInfocheck)){
			//set card details to save  
			$_PARAMS['cardnumber'] 		= trim($_POST['cardNumber']);
			$_PARAMS['cvv'] 			= trim($_POST['cardSecurityCode']);
			$_PARAMS['expiryyear']  	= trim($_POST['cardExpireYear']); //set card expire year
			$_PARAMS['expirymonth'] 	= trim($_POST['cardExpireMonth']); //set card expire month
			$_PARAMS['cardname'] 		= trim($_POST['cardName']); //set name on card
			  
			$_PARAMS['billingaddress']	= trim($_POST['billingaddress']);
			$_PARAMS['billingcity'] 	= trim($_POST['billingcity']);
			$_PARAMS['billingcountry']	= trim($_POST['billingcountry']);
			$_PARAMS['billingstate'] 	= trim($_POST['billingstate']);
			$_PARAMS['billingzip'] 		= trim($_POST['billingzip']);
			$_PARAMS['useragent'] 		= $_POST['origin'];
			$_PARAMS['binrule_id']	= trim($_POST['binrule_id']);
				  
			if($orderStatus == "1"){
				$_ARR_RESP['Status'] = 'Approved';
				  			          
				$_PARAMS['foreign_id'] = null;
				if ($_ARR_RESP['Status'] == 'Approved'){
					$_PARAMS['foreign_id'] = $_ARR_RESP['Foreign'];
					set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				}
				  
				if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {	
					if ($_ARR_RESP['Status'] == 'Approved'){
						$_RESULT['status'] = 'Success';
						$_RESULT['errorcode'] = 0;
						$_RESULT['html']   = 'Authorised';
					} else {
						$_RESULT['status'] = 'Error';
						$_RESULT['errorcode'] = 207;
						$_RESULT['foreign_errorcode'] = $_ARR_RESP['Error'];
						$_RESULT['html']   = $riskInfo;
					}
				} else {
					$_RESULT['status'] = 'Error';
					$_RESULT['errorcode'] = 204;
					$_RESULT['html']   = 'Update error';
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
				/*
				if($methodData->is_dummy == 1 && ($card_number != $encrypted_card_no) && ($checkPlayerHasDummy->cnt == 0)){
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
	       		}
				 */
				$_RESULT['status'] = 'Declined';
				$_RESULT['errorcode'] = 207;
				$_RESULT['html'] = $riskInfo;
			}
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
{
	// NO REQUEST_ID
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
	
	$id = update_payments($_PAYMENTID, $_PARAMS);
}elseif(!empty($_PAYMENTID) && $_RESULT['status'] != 'Success'){
	insert_payment($_RESULT, $_PARAMS);
	$id = insert_vt_payments($_PAYMENTID, $_PARAMS, $_RESULT);
}else{
	$_PAYMENT_ID = false;
	$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
}

if ($_PAYMENT_ID){
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

switch($_RESULT['status']){
	case 'Success':	send_email_notification($_PAYMENT_ID, 'AUTHORISED_DEPOSIT', $_PARAMS); break;
	case 'Pending':	send_email_notification($_PAYMENT_ID, 'PENDING_DEPOSIT', $_PARAMS); break;
	case 'Error':	send_email_notification($_PAYMENT_ID, 'DECLINED_DEPOSIT', $_PARAMS); break;
}

if (SEND_EMAIL_TO_TECH){
	send_email(TECH_EMAIL, PROVIDER_NAME.': '.$_RESULT['status'].'('.$_RESULT['errorcode'].'): '.$_RESULT['html'], "PAYMENT_ID: ".$_PAYMENT_ID.", RESULT: ".var_export($_RESULT, true).", PARAMS: ".var_export($_PARAMS, true).", OUTPUT: ".var_export($_ARR_RESP, true));
}
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
<?php }else if($_VIRTUALPT == 1){
	if($_RESULT['status'] == 'Success'){
		echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
	}else{
		echo "<b color='green'>Transaction failed. Please try again.</b>";
	}
?>
	<script language="javascript">
		parent.parent.location="<?php echo $_PARAMS['redirect_url'] ?>/?msg=<?php echo $_RESULT['status'].'&result='.$_PAYMENT_ID; ?>";
	</script>
<?php }else{ ?>

<script language="javascript">
	parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
</script>
<?php } ?>
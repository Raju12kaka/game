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
	$palyer_details = get_player_all_details($_PARAMS['player_id']);
	
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


		$merNo              = trim($_POST['authorisationkey1']); //MerchantNo.
      $merchant_auth_code = trim($_POST['authorisationkey2']); //MerchantAuthcode.
      $gateway_code       = trim($_POST['authorisationkey3']); //GatewayId
      

      $orderNo          = trim($_PARAMS['id']);
      $orderAmount      = trim($_PARAMS['amount']);
      $cardNo           = trim($_POST['cardNumber']);
      $cardExpireYear   = trim($_POST['cardExpireYear']);
      $cardExpireMonth  = trim($_POST['cardExpireMonth']);
      $cardSecurityCode = trim($_POST['cardSecurityCode']);
      $paymethod        = "Credit Card";
      $ip 				= long2ip($palyer_details->register_IP);

      /* Still need to get from DB */
      $firstName        = $palyer_details->realname;
      $lastName         = $palyer_details->reallastname;
      $email            = $palyer_details->email;
      $issuingBank      = "bank"; 
      $phone            = $palyer_details->contact_phone;
      $currency         = "USD";
      $card_type        = trim($_POST['cardtype']);
      $country          = !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
      $state            = !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
      $city             = trim($_POST['billingcity']);
      $address          = trim($_POST['billingaddress']);
      $zip              = !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
	  $useagent			= trim($_POST['useragent']);
      
      $returnUrl        = "http://".$_SERVER['HTTP_HOST']."/error.php"; //real trading websites

     /****************************** 
       Submitting parameters by using curl and get returned XML parameters
      
     ****************************/
      $arr = array(
             'merchant_id'        => $merNo,            //MerchantNo.
             'merchant_auth_code' => $merchant_auth_code,        //GatewayNo.
             'gateway_code'       => $gateway_code,        //GatewayNo.
             'paymethod'          => $paymethod,        
         	 'currency'        	  => $currency,        
             'order_no'           => $orderNo,          //OrderNo.
             'amount'      		  => $orderAmount,      //OrderAmount
             'first_name'         => $firstName,        //FirstName
             'last_name'          => $lastName,         //lastName
             'card_no'            => $cardNo,           //CardNo
             'card_expire_month'  => $cardExpireMonth,  //CardExpireMonth
             'card_expire_year'   => $cardExpireYear,   //CardExpireYear
             'card_security_code' => $cardSecurityCode, //CVV
         	 'card_type'      	  => $card_type,      //Card Type
             'email'              => $email,            //EmailAddress
             'ip'                 => $ip,               //ip
             'return_url'         => $returnUrl,        //real trading websites
             'phone'              => $phone,            //Phone Number 
             'country'            => $country,          //Country
             'state'              => $state,            //State
             'city'               => $city,             //City
             'address'            => $address,          //Address
             'zip'                => $zip,              //Zip Code
             'csid'               => "abc",             //
             'issuing_bank'       => "bank",			//
             'userAgent'		  => $useagent
            );
	

      //echo "In avenue pay gateway request"; echo "<pre>"; print_r($arr); exit;
       $data =  http_build_query($arr);
       
	   $url  = "https://services.avenuepay.com/apinterface"; 
	           
	   //===============================
	   $curl = curl_init($url);
	   curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
	   curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
	   curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
	   curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
	   curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
	   curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
	   $jsonrs = curl_exec($curl);
	   curl_close ($curl); 
	
	   $result = json_decode($jsonrs,TRUE);
	    
       $tradeNo      = $result['ap_transaction_number'];//return tradeNo
       $orderNo      = $result['transaction_number'];//return orderno
       $orderAmount  = $result['amount'];//return orderAmount
       $orderCurrency= $result['currency'];//return orderCurrency
       $orderStatus  = $result['status'];//return orderStatus
       $orderInfo    = $result['message'];//return orderInfo
       $riskInfo     = $result['message'];//return riskInfo
       

       if (isset($result)){

          $_ARR_RESP['Foreign'] 	= $tradeNo;   //return Id need to 

          $_PARAMS['foreign_id'] 	= null;
          $_PARAMS['foreign_id'] 	= $_ARR_RESP['Foreign'];
          set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
        
          //set card details to save  
          $_PARAMS['cardnumber'] 	= $encrypted_card_no;
  		  $_PARAMS['cvv'] 			= encrypt_card($_POST['cardSecurityCode'], 'encode');
   	  	  $_PARAMS['expiryyear']  	= trim($_POST['cardExpireYear']); //set card expire year
		  $_PARAMS['expirymonth'] 	= trim($_POST['cardExpireMonth']); //set card expire month
		  $_PARAMS['cardname'] 		= trim($_POST['cardName']); //set name on card
		  
		  $_PARAMS['billingaddress']= trim($_POST['billingaddress']);
		  $_PARAMS['billingcity'] 	= trim($_POST['billingcity']);
		  $_PARAMS['billingcountry']= trim($_POST['billingcountry']);
		  $_PARAMS['billingstate'] 	= trim($_POST['billingstate']);
		  $_PARAMS['billingzip'] 	= trim($_POST['billingzip']);
		  $_PARAMS['useragent'] 	= $_POST['origin'];

          if($orderStatus == "1" ){
	          $_ARR_RESP['Status'] 	= 'Approved';
	          
	          if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
		          	if ($_ARR_RESP['Status'] == 'Approved'){
		          		$_RESULT['status'] 		= 'Success';
		          		$_RESULT['errorcode'] 	= 0;
		          		$_RESULT['html']   		= 'Authorised';
		          	} else {
		          		$_RESULT['status'] 				= 'Error';
		          		$_RESULT['errorcode'] 			= 207;
		          		$_RESULT['foreign_errorcode'] 	= $_ARR_RESP['Error'];
		          		$_RESULT['html']   				= $riskInfo;
		          	}
	          } else {
		          	$_RESULT['status'] 		= 'Error';
		          	$_RESULT['errorcode'] 	= 204;
		          	$_RESULT['html']   		= 'Update error';
	          }
		          
		   }
           else
           {	
      			$_RESULT['status'] 		= 'Declined';
		        $_RESULT['errorcode'] 	= 207;
		        $_RESULT['html'] 		= $riskInfo;
           }
	    } 

	}
  else
  {
  	  $_RESULT['status'] 	= 'Error';
	  $_RESULT['errorcode'] = 102;
	  $_RESULT['html']	 	= 'Infromation Incorrect';
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
	
	$id = update_payments($_PAYMENTID, $_PARAMS);
}elseif(!empty($_PAYMENTID) && $_RESULT['status'] != 'Success'){
	insert_payment($_RESULT, $_PARAMS);
	$id = insert_vt_payments($_PAYMENTID, $_PARAMS, $_RESULT);
}else{
	$_PAYMENT_ID = false;
	$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
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

switch($_RESULT['status']){
	case 'Success':	send_email_notification($_PAYMENT_ID, 'AUTHORISED_DEPOSIT', $_PARAMS); break;
	case 'Pending':	send_email_notification($_PAYMENT_ID, 'PENDING_DEPOSIT', $_PARAMS); break;
	case 'Error':	send_email_notification($_PAYMENT_ID, 'DECLINED_DEPOSIT', $_PARAMS); break;
}

if (SEND_EMAIL_TO_TECH){
	send_email(TECH_EMAIL, PROVIDER_NAME.': '.$_RESULT['status'].'('.$_RESULT['errorcode'].'): '.$_RESULT['html'], "PAYMENT_ID: ".$_PAYMENT_ID.", RESULT: ".var_export($_RESULT, true).", PARAMS: ".var_export($_PARAMS, true).", OUTPUT: ".var_export($_ARR_RESP, true));
}

if(!empty($_PAYMENTID)){
	if($_RESULT['status'] == 'Success'){
		echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
	}else{
		echo "<b color='green'>Transaction failed. Please try again.</b>";
	}
?>

	<script language="javascript">
		parent.parent.location="<?php echo CRMURL; ?>Queues/dummydeposits";
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

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
	//print('<pre>');print_r($_PARAMS);exit;
	// Get all player details
	$palyer_details = get_player_all_details($_PARAMS['player_id']);
	
	$encrypted_card_no = $_POST['cardNumber'];
	$methodData = getPaymenthodDetails($_PARAMS['payment_method_id']);


	$getcountrycode = fetchCountryCode($palyer_details->country);
	
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
      $sidno            	= trim($_POST['authorisationkey1']); //sidno.
      $rcode 	= trim($_POST['authorisationkey2']); //Rcode
     

      

      $orderNo = trim($_PARAMS['id']);

     //$orderCurrency = trim($_POST['orderCurrency']);
      $orderAmount      = trim($_PARAMS['amount']);
	  $cardName = trim($_POST['cardName']);
      $cardNo           = trim($_POST['cardNumber']);
      $cardExpireYear   = trim($_POST['cardExpireYear']);
	  $cardExpireYear   = substr( $cardExpireYear, -2);
      $cardExpireMonth  = trim($_POST['cardExpireMonth']);
      $cardSecurityCode = trim($_POST['cardSecurityCode']);
      $paymethod 		= "Credit Card";
      $ip               = long2ip($palyer_details->register_IP);

      /* Still need to get from DB */
      $firstName        = $palyer_details->realname;
      $lastName         = $palyer_details->reallastname;
      $email            = $palyer_details->email;
      $issuingBank      = "bank"; 
      $phone            = $palyer_details->contact_phone;
	  $birthdate		= $palyer_details->birthdate;
      $currency = "USD";
	  
      $card_type = trim($_POST['authorisationkey4']);
       
      $country          = !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
      $state            = !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
      $city             = trim($_POST['billingcity']);
      $address          = trim($_POST['billingaddress']);
      $zip              = !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
	  $useragent		= trim($_POST['useragent']);
	  $tim=time();

	  $signinfo =  md5($sid.$tim.$orderAmount.$currency.$rcode);

      //var_dump(class_exists("SOAPClient"));exit;

     /****************************** 
       Submitting parameters by using curl and get returned XML parameters
      
     ****************************/
     
     $client = new SoapClient("https://api.umbrellaegateway.com/Service.svc?singleWsdl");
	 
     $arr = array(
             'Sid'            => $sidno,            //sidno.
             'Rcode'        => $rcode,        //rcode.
             'TotalCartAmount'      => $orderAmount,     
             'FirstName'        => $firstName,        
             'LastName'        => $lastName,        
             'Email'          => $email,          
             'Phone'      => $phone,     
             'Mobile'        => $phone,        
             'Address'         => $address,         
             'SuburbCity'           => $city,           
             'State'  => $state,  
             'ZipCode'   => $zip,   
             'Country' => $country, 
             'ShipFirstName'      => $firstName,      
             'ShipLastName'            => $lastName,          
             'ShipAddress'               => $address,              
             'ShipSuburbCity'        => $city,      
             'ShipState'            => $state,            
             'ShipZipCode'          => $zip,        
             'ShipCountry'            => $country,           
             'ShipAddress'             => $address,            
             'DOB'              => $birthdate,             
             'UIP'         =>  $ip,
             'PayBy'          =>  $card_type,
             'CardName'         =>  $cardName,
             'CardNo'         =>  $cardNo,
             'CCV'        => $cardSecurityCode,      
             'ExpiryMonth'            => $cardExpireMonth,            
             'ExpiryYear'          => $cardExpireYear,        
             'CurrencyCode'            => $currency,           
             'ref1'             => '',            
             'ref2'              => '',             
             'ref3'         =>  '',
             'ref4'          =>  '',
             'timestamp'         => $tim,
             'hash'         =>  $signinfo,
            );
			
			
			
			
			
			/*$obj->Sid = $sidno;
			$obj->Rcode = $rcode;
			$obj->TotalCartAmount = $orderAmount;
			$obj->FirstName = $firstName;
			$obj->LastName = $lastName;
			$obj->Email = $email;
			$obj->Phone = $phone;
			$obj->Mobile = $phone;
			$obj->Address = $address;
			$obj->SuburbCity = $city;
			$obj->State = $state;
			$obj->ZipCode = $zip;
			$obj->Country = $country;
			$obj->ShipFirstName = $firstName;
			$obj->ShipLastName = $lastName;
			$obj->ShipAddress = $address;
			$obj->ShipSuburbCity = $city;
			$obj->ShipState = $state;
			$obj->ShipZipCode = $zip;
			$obj->ShipCountry = $country;
			$obj->ShipAddress = $address;
			$obj->DOB = $birthdate;
			$obj->UIP = $ip;
			$obj->PayBy = $card_type;
			$obj->CardName = $cardName;
			$obj->CardNo = $cardNo;
			$obj->CCV = $cardSecurityCode;
			$obj->ExpiryMonth = $cardExpireMonth;
			$obj->ExpiryYear = $cardExpireYear;
			$obj->CurrencyCode = $currency;
			$obj->ref1 = "";
			$obj->ref2 = "";
			$obj->ref3 = "";
			$obj->ref4 = "";
			$obj->timestamp = $tim;
			$obj->hash = $signinfo;*/
			
			
			
			
			$soapParameters=array(
			     'paymentDetails' => $arr
			);
			// print('<pre>');print_r($soapParameters); exit;
			
			
			$result=$client->__soapCall("AddPaymentDetails",array($soapParameters));


           
            
            
			//print('<pre>');print_r($response->AddPaymentDetailsResult);
			$documentroot   = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($documentroot);
            array_push($documentroot, 'logs');
            $root_path              = implode('/', $documentroot);
			$res = json_decode($result->AddPaymentDetailsResult->GatewayResponseText,TRUE);
			
			file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." umberellagateway request data: ".json_encode($soapParameters).PHP_EOL , FILE_APPEND | LOCK_EX);
			file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." umberellagateway response data: ".$json.PHP_EOL , FILE_APPEND | LOCK_EX);		
			if(isset($_POST['submitfrom']) && $_POST['submitfrom']=="cron"){
				
			 $result1['tradeNo']      = $result->AddPaymentDetailsResult->TransactionId;//return tradeNo
             $result1['orderNo']      = $orderNo;//return orderno
             $result1['orderAmount']  = ($result->AddPaymentDetailsResult->TotalCartAmount) ? $result->AddPaymentDetailsResult->TotalCartAmount : $orderAmount;//return orderAmount
             $result1['orderCurrency'] =$result->AddPaymentDetailsResult->CurrencyCode;//return orderCurrency
             $result1['orderStatus']  = $result->AddPaymentDetailsResult->Status;//return orderStatus
             $result1['orderInfo']     = $result->AddPaymentDetailsResult->Response;//return riskInfo
             file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." umberellagateway response data for cron: ".json_encode($result1).PHP_EOL , FILE_APPEND | LOCK_EX);
			 echo  json_encode($result1);exit;
				
			}
			
			
	
            
            $tradeNo      = $result->AddPaymentDetailsResult->TransactionId;//return tradeNo
            $orderNo      = $orderNo;//return orderno
            $orderAmount  = $result->AddPaymentDetailsResult->TotalCartAmount;//return orderAmount
            $orderCurrency= $result->AddPaymentDetailsResult->CurrencyCode;//return orderCurrency
            $orderStatus  = $result->AddPaymentDetailsResult->Status;//return orderStatus
            $orderInfo    = $result->AddPaymentDetailsResult->Response;//return orderInfo
            $riskInfo     = $result->AddPaymentDetailsResult->Response;//return riskInfo
           
            if (isset($result)){

                  $_ARR_RESP['Foreign'] = $tradeNo;   //return Id need to 

                  $_PARAMS['foreign_id'] = null;
                  $_PARAMS['foreign_id'] = $_ARR_RESP['Foreign'];
                  set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				  
				  //set card details to save  
		          $_PARAMS['cardnumber'] 	= trim($_POST['cardNumber']);
		  		  $_PARAMS['cvv'] 			= trim($_POST['cardSecurityCode']);
		   	  	  $_PARAMS['expiryyear']  	= trim($_POST['cardExpireYear']); //set card expire year
				  $_PARAMS['expirymonth'] 	= trim($_POST['cardExpireMonth']); //set card expire month
				  $_PARAMS['cardname'] 		= trim($_POST['cardName']); //set name on card
				  
				  $_PARAMS['billingaddress']= trim($_POST['billingaddress']);
				  $_PARAMS['billingcity'] 	= trim($_POST['billingcity']);
				  $_PARAMS['billingcountry']= trim($_POST['billingcountry']);
				  $_PARAMS['billingstate'] 	= trim($_POST['billingstate']);
				  $_PARAMS['billingzip'] 	= trim($_POST['billingzip']);
				  $_PARAMS['useragent'] 	= $_POST['origin'];
				  $_PARAMS['binrule_id']	= trim($_POST['binrule_id']);

              if($orderStatus == "OK" ){
             	
    		          $_ARR_RESP['Status'] = 'Approved';
			          
			          if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
			          	if ($_ARR_RESP['Status'] == 'Approved'){
			          		$_RESULT['status'] = 'Success';
			          		$_RESULT['errorcode'] = 0;
			          		$_RESULT['html']   = $orderStatus." || ".$orderInfo;
			          	} else {
			          		$_RESULT['status'] = 'Error';
			          		$_RESULT['errorcode'] = 207;
			          		$_RESULT['foreign_errorcode'] = $_ARR_RESP['Error'];
			          		$_RESULT['html']   = $orderStatus." || ".$orderInfo;
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
					//$checkplaybonus = check_plays_bonuses($_PARAMS['player_id']);
					//print_r($methodData);exit;
					/*
					if($methodData->is_dummy == 1 && ($card_number != $encrypted_card_no) && ($checkPlayerHasDummy->cnt == 0) && ($checkplaybonus == 0)){
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
		          $_RESULT['html'] = $orderStatus." || ".$orderInfo;
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
	if($_RESULT['status'] == 'Success')
		{
			//$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$_PAYMENT_ID.'&amt='.$_PARAMS['player_currency_amount']/100;	
		     $_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['amount'];

        }
		else {
			//$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$_PAYMENT_ID.'&s=declined';
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=declined';
		}
} elseif($_CANCELLED) {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=cancelled';
	//$_LOCATION_URL  = $_PARAMS['redirect_url'].'&s=cancelled';
} else {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=unknown';
	//$_LOCATION_URL  = $_PARAMS['redirect_url'].'&s=unknown';
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

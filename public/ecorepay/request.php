<?php 

define('PROVIDER_ID', 110);
define('PROVIDER_NAME', 'ecorepay');

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/private/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/private/'.PROVIDER_NAME.'/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/private/'.PROVIDER_NAME.'/utils.php';
	
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
		
		//echo "through visa payment gateway"; exit;
		
/* **********************************************************************
       Descriptionï¼š1ã€�Please transafer interface parameters to trust payment gateway by using POST.
                    2ã€�Testing datas,Signkey,MerchantNo.,GatewayNo. have been writen into other parameters in demo.Please write down your value in following variate accordingly.
                    3ã€�After payment, it will return XML
      **********************************************************************/


	    // $accountid = "30579389";
	    // $authcode = "eEcnoaGbiUXDSjyh";
	      
	    //Test credentials
	    $accountid = "65098754";
	    $authcode = "yLTuvmeYVuHVmbog";
	
	    $reference 		= trim($_PARAMS['id']);
	    $currency 		= trim($_PARAMS['currency_id']);
	    $amount 		= trim($_PARAMS['amount']);
	    $firstname 		= $palyer_details->realname;
	    $lastname 		= $palyer_details->reallastname;
	    $card_no 		= trim($_POST['cardNumber']);
	    $card_exp_year 	= trim($_POST['cardExpireYear']);
	    $card_exp_month = trim($_POST['cardExpireMonth']);
	    $card_cvv 		= trim($_POST['cardSecurityCode']);
	    $email          = $palyer_details->email;
	    $issuingBank    = "bank"; 
	    $phone          = $palyer_details->contact_phone;
	    $uip 			= long2ip($palyer_details->register_IP); 
	
	    $countrycode 	= $palyer_details->country;
	    //$state = $palyer_details->state;
	    $state 			= (in_array($countrycode, array('US','CA'))) ? $palyer_details->state : 'XX';
	    $city           =  $palyer_details->city;
	    $address 		= $palyer_details->address;
	    $postcode       = $palyer_details->zipcode;
	    
	    $brand 			= "VISA";
	    if ( $_PARAMS['payment_method_id'] == 101 )
			$brand 		= "MASTERCARD";
	
	     $action 		= "AuthorizeCapture";
		
		
	     $xmlquerybuild = '<?xml version="1.0" encoding="utf-8"?>
	                                <Request type="'.$action.'">  
	                                <AccountID>'.$accountid.'</AccountID>
	                                <AccountAuth>'.$authcode.'</AccountAuth> 
	                                <Transaction> 
	                                     <Reference>'.$reference.'</Reference>
	                                     <Amount>'.$amount.'</Amount> 
	                                     <Currency>'.$currency.'</Currency>
	                                     <Email>'.$email.'</Email>
	                                     <IPAddress>'.$uip.'</IPAddress>
	                                     <Phone>'.$phone.'</Phone>
	                                     <FirstName>'.$firstname.'</FirstName>
	                                     <LastName>'.$lastname.'</LastName>    
	                                     <Address>'.$address.'</Address> 
	                                     <City>'.$city.'</City>         
	                                     <State>'.$state.'</State> 
	                                     <PostCode>'.$postcode.'</PostCode>  
	                                     <Country>'.$countrycode.'</Country> 
	                                     <CardNumber>'.$card_no.'</CardNumber>
	                                     <CardExpMonth>'.$card_exp_month.'</CardExpMonth>
	                                     <CardExpYear>'.$card_exp_year.'</CardExpYear>  
	                                     <CardCVV>'.$card_cvv.'</CardCVV> 
	                                 </Transaction>  
	                               </Request>';
		                               
			$posturl = "https://gateway.ecorepay.cc";
		    // echo "<pre>"; print_r($xmlquerybuild); exit;
            //===============================
            $ch = curl_init (); //initiate the curl session
 
			$ch = curl_init();      
			curl_setopt($ch, CURLOPT_URL, $posturl);  
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));   
            curl_setopt($ch, CURLOPT_POST, 1);   
          	curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlquerybuild);      
          	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);     
          	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    
          	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);  

          	$result = curl_exec($ch); 
	                        
			$xml = json_decode(json_encode(simplexml_load_string($result)), 1);
	
			$status = $xml['ResponseCode'];
			$return_tnx_id  = $xml['TransactionID'];
			$description = $xml['Description'];
			
			
            if (isset($xml)){

		        $_ARR_RESP['Foreign'] = $return_tnx_id;
	
		        $_PARAMS['foreign_id'] = null;
	          	$_PARAMS['foreign_id'] = $_ARR_RESP['Foreign'];
		        set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				
				if($status == 100){
					$_ARR_RESP['Status'] = 'Approved';
					if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
						
						$_PARAMS['cardnumber'] = substr($_POST['cardNumber'], 0, CARD_FIRST_BLOCK).str_repeat('*', CARD_MIDDLE_BLOCK).substr($_POST['cardNumber'], -(CARD_LAST_BLOCK));
			          	$_PARAMS['cvv'] = $_POST['cardSecurityCode'];
						          		
						if ($_ARR_RESP['Status'] == 'Approved'){
							$_RESULT['status'] = 'Success';
							$_RESULT['errorcode'] = 0;
							$_RESULT['html']   = 'Authorised';
						} else {
							$_RESULT['status'] = 'Error';
							$_RESULT['errorcode'] = 207;
							$_RESULT['foreign_errorcode'] = $_ARR_RESP['Error'];
							$_RESULT['html']   = 'Declined';
						}
					} else {
						$_RESULT['status'] = 'Error';
						$_RESULT['errorcode'] = 204;
						$_RESULT['html']   = 'Update error';
					}
				      
				}
				else
				{	
					$_RESULT['status'] = 'Declined';
					$_RESULT['errorcode'] = $status;
					$_RESULT['html'] = $description;
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

$_PAYMENT_ID = false;
$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);

if ($_PAYMENT_ID){
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID;
} elseif($_CANCELLED) {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'?s=cancelled';
} else {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'?s=unknown';
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

?>

<script language="javascript">
	parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
</script>
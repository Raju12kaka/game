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


$username = "APIHC";
$password = "APIHCn1*";
$ProviderPIN = "DesipayTP";
$AccountID = trim($_POST['authorisationkey1']);
$AccountPassword = trim($_POST['authorisationkey2']);
$AccountKey =  trim($_POST['authorisationkey3']);



       
      $transactionnumber          = trim($_PARAMS['id']);
       
      $orderCurrency    = trim($_PARAMS['currency_id']);

      $amount      = trim($_PARAMS['amount']);

      $firstname        = $palyer_details->realname;

      $lastname         = $palyer_details->reallastname;

      $creditcardnum           = trim($_POST['cardNumber']);

      $cardexpyear   = trim($_POST['cardExpireYear']);

      $cardexpmonth  = trim($_POST['cardExpireMonth']);

      $CVV = trim($_POST['cardSecurityCode']);

      $email            = $palyer_details->email;

      $issuingBank      = "bank"; 

      $phone            = $palyer_details->contact_phone;

      $ipaddress               =  trim($_POST['ip']);

      //$signInfo         = hash("sha256" , $merNo . $gatewayNo . $orderNo . $orderCurrency . $orderAmount . $firstName .
      //$lastName.$cardNo.$cardExpireYear.$cardExpireMonth.$cardSecurityCode.$email.$signkey );
       
      $country          = !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;

      $province            = $palyer_details->state;


      $city             =  $palyer_details->city;

      $address          = $palyer_details->address;

      $postalcode       = $palyer_details->zipcode;

      $paypunto_status        = "http://".$_SERVER['HTTP_HOST']; //real trading websites

    
      $brand = trim($_POST['authorisationkey4']);

      $objMethod = "objWSCreditCardBE";
      $provider = "PayPunto";


								// Initialize the soap client
                                $soapActionXmlNS = "http://tempuri.org/Initiate_Deposit";
                                
                                // Build the SOAP XML request
                                
                                $soapXML = '<?xml version="1.0" encoding="utf-8"?>
                                <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                                <soap:Body>
                                <Initiate_Deposit xmlns="http://tempuri.org/">
                                <'.$objMethod.'>
                                <Username>' . $username . '</Username>
                                <Password>' . $password . '</Password>
                                <AccountID>'. $AccountID .'</AccountID>
                                <AccountPassword>'. $AccountPassword .'</AccountPassword>
                                <AccountKey>'. $AccountKey .'</AccountKey>
                                <ProviderPIN>'. $ProviderPIN .'</ProviderPIN>
                                
								<CustomerPIN>' . $accountid . '</CustomerPIN>
                               
								<TraceID>' . $transactionnumber . '</TraceID>
                                <FirstName>' . trim($firstname) . '</FirstName>
                                <LastName>' . trim($lastname) . '</LastName>
                                <Address>' . $address . '</Address>
                                <City>' . $city . '</City>
                                <StateCode>' . $province . '</StateCode>
                                <CountryCode>' . $country . '</CountryCode>
                                <PostalCode>' . $postalcode . '</PostalCode>
                                <Email>' . $email . '</Email>
                                <Phone>' . $phone . '</Phone>
                                <CreditCardHolder>'.strtolower(trim($firstname)).' '.strtolower(trim($lastname)).'</CreditCardHolder>
                                <CreditCardNumber>' . $creditcardnum . '</CreditCardNumber>
                                <CreditCardExpirationMonth>' . $cardexpmonth . '</CreditCardExpirationMonth>
                                <CreditCardExpirationYear>' . $cardexpyear . '</CreditCardExpirationYear>
                                <CreditCardType>'.$brand.'</CreditCardType>
                                <CreditCardCVV>' . $CVV . '</CreditCardCVV>
                                <Amount>' . $amount . '</Amount>
                                <CurrencyCode>USD</CurrencyCode>
                                <IPv4Address>'.$ipaddress.'</IPv4Address>
                                <StatusURL>'.$paypunto_status.'</StatusURL>
                                <Comments>Processed with ' .$provider . '</Comments>
                                </'.$objMethod.'>
                                </Initiate_Deposit>
                                </soap:Body>
                                </soap:Envelope>';
                                
							$host = "http://gateway.paypunto.com/Gateway/API/CreditCards/CreditCardAPI.asmx";
            
            //===============================
            $ch = curl_init (); //initiate the curl session
            curl_setopt ( $ch, CURLOPT_URL, $host ); //set to url to post to
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true ); // return data in a variable
            curl_setopt ( $ch, CURLOPT_HEADER, 0 );
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $soapXML ); // post the xml
 
                                $header = array ("Content-Type: text/xml; charset=utf-8" );
            $header [] = "Content-Length: ".strlen($soapXML);
            $header [] = 'SOAPAction: ' . $soapActionXmlNS;
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
            
            $result = curl_exec ( $ch );
                                
			$xml = json_decode(json_encode(simplexml_load_string(strtr($result, array(' xmlns:'=>' ')))), 1);


			$status = $xml['Body']['Initiate_DepositResponse']['Initiate_DepositResult']['Mnet']['Status'];
			$return_tnx_id  = $xml['Body']['Initiate_DepositResponse']['Initiate_DepositResult']['Mnet']['TransactionID'];

            if (!curl_errno($ch) ){

    		          $_ARR_RESP['Foreign'] = $return_tnx_id;

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
					  
              if($status == "Approved"){
             	
    		          $_ARR_RESP['Status'] = 'Approved';
			          
			          if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
			          	
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
			          $_RESULT['errorcode'] = 207;
			          $_RESULT['html'] = 'Declined';
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

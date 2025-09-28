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
	$encrypted_card_no = $_POST['cardNumber'];
	$methodData = getPaymenthodDetails($_PARAMS['payment_method_id']);
	
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
	      
	    //Test credentials
	    // $accountid = "65173355";
	    // $authcode = "YFMihITjPndWcsBM";
	    
	    //Visa Live credentials
	    $accountid 		= trim($_POST['authorisationkey1']);
	    $authcode 		= trim($_POST['authorisationkey2']);
	
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
	
	    $countrycode 	= !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
	    //$state = $palyer_details->state;
	    $statecode 		= !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
        $state 			= (in_array($countrycode, array('US','CA'))) ? $statecode : 'XX';
        $city         	=  trim($_POST['billingcity']);
        $address 		= trim($_POST['billingaddress']);
        $postcode     	= !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
    
        $brand 			= trim($_POST['authorisationkey4']);
	
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
				
				$_PARAMS['cardnumber'] 	= trim($_POST['cardNumber']);
	  		  	$_PARAMS['cvv'] 		= trim($_POST['cardSecurityCode']);
	       	    $_PARAMS['expiryyear']  = trim($_POST['cardExpireYear']); //set card expire year
    		    $_PARAMS['expirymonth'] = trim($_POST['cardExpireMonth']); //set card expire month
    		    $_PARAMS['cardname'] = trim($_POST['cardName']); //set name on card
    			  
			    $_PARAMS['billingaddress'] = trim($_POST['billingaddress']);
			    $_PARAMS['billingcity'] = trim($_POST['billingcity']);
				$_PARAMS['billingcountry']= trim($_POST['billingcountry']);
			    $_PARAMS['billingstate'] = trim($_POST['billingstate']);
			    $_PARAMS['billingzip'] = trim($_POST['billingzip']);
			    $_PARAMS['useragent'] = $_POST['origin'];
				
				if($status == 100){
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
					//check if dummy available or not
		       		$card_details = get_payment_card_details($encrypted_card_no);
					$card_number = !empty($card_details->cardnumber) ? $card_details->cardnumber : '';
					$paymentMethod = ($_PARAMS['payment_method_id'] == 100) ? 106 : 107;
					$paymentProvider = ($paymentMethod == 106) ? 111 : 112;
					$dummymethods = implode(',', array(106,107));
					$checkPlayerHasDummy = get_player_hasdummy($_PARAMS['player_id'], $dummymethods);
					
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

  // echo "<pre>";
  // print_r($_PARAMS);
  // echo "</pre>";
  // echo "<pre>";
  // print_r($_RESULT);
  // echo "</pre>";
  // exit; 

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

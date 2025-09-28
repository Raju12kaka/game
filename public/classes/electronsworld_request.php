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
		
/* **********************************************************************
       Descriptionï¼š1ã€�Please transafer interface parameters to trust payment gateway by using POST.
                    2ã€�Testing datas,Signkey,MerchantNo.,GatewayNo. have been writen into other parameters in demo.Please write down your value in following variate accordingly.
                    3ã€�After payment, it will return XML
 **********************************************************************/
      $MerchantID       = trim($_POST['authorisationkey1']); //key.
      $gatewayURL	 	= trim($_POST['authorisationkey2']); //url
      $card_type 		= trim($_POST['authorisationkey3']);//cardtype
      

      $orderNo 			= trim($_PARAMS['id']);
      $orderAmount      = trim($_PARAMS['amount']);
	  $nameoncard       = trim($_POST['cardName']);
      $cardNo           = trim($_POST['cardNumber']);
      $cardExpireYear   = trim($_POST['cardExpireYear']);
      $cardExpireMonth  = trim($_POST['cardExpireMonth']);
      $cardSecurityCode = trim($_POST['cardSecurityCode']);
      $paymethod 		= "Credit Card";
      $ip               = long2ip($palyer_details->register_IP);

      /* Still need to get from DB */
      $firstName        = trim($palyer_details->realname);
      $lastName         = trim($palyer_details->reallastname);
      $email            = trim($palyer_details->email);
      $issuingBank      = "bank"; 
      $phone            = explode(" ",$palyer_details->contact_phone);
	  $phone			= $phone[1];
	  $birthdate		= $palyer_details->birthdate;
      $currency 		= "USD";
	       
      //$country          = !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
	  $getcountrycode 	= fetchCountryCode($palyer_details->country);
	  $country 			= $getcountrycode->code3;
      $state            = !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
      $city             = trim($_POST['billingcity']);
      $address          = trim($_POST['billingaddress']);
      $zip              = !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
	  $useragent		= trim($_POST['useragent']);
	  $csid				= $_POST['direct_csid'];
	  
      $returnUrl        = "http://".$_SERVER['HTTP_HOST']."/error.php"; //real trading websites
      
      $signInfo         = hash("sha256" , $merNo . $gateway_code . 'S7'.$orderNo . $currency . $orderAmount . $firstName . $lastName.$cardNo.$cardExpireYear.$cardExpireMonth.$cardSecurityCode.$email.$api_sign_key );

     /****************************** 
       Submitting parameters by using curl and get returned XML parameters
      
     ****************************/
     
			
		
			
		$postDataArr = array();
		$postDataArr['tr_merchant_id'] = $MerchantID;
		$postDataArr['tr_firstname'] = $firstName;
		$postDataArr['tr_lastname'] =  $lastName;	
		$postDataArr['tr_email'] = $email;
		$postDataArr['tr_phone'] = substr($phone, -10);
		$postDataArr['tr_ip'] = $ip;
		$postDataArr['tr_address1'] = $address;
		$postDataArr['tr_city'] = $city;
		$postDataArr['tr_state'] = $state;
		$postDataArr['tr_zip'] = $zip;
		$postDataArr['tr_country'] = $country;
		$postDataArr['tr_card_no']= $cardNo;
		$postDataArr['tr_card_security_code']= $cardSecurityCode;
		$postDataArr['tr_customer_id'] = $palyer_details->id;
		$postDataArr['tr_product_id'] = "PROD";
		$postDataArr['tr_card_expiry_month']= $cardExpireMonth;
		$postDataArr['tr_card_expiry_year']= $cardExpireYear;
		$postDataArr['tr_issuing_bank'] = "BANK";
		$postDataArr['tr_merchant_ref_id'] = 'S7'.$orderNo;
		$postDataArr['tr_payment_method']= $card_type;
		$postDataArr['tr_card_type']= $card_type;
		$postDataArr['tr_amount'] = number_format($orderAmount, 2, '.', '');
		$postDataArr['tr_currency_code'] = $currency;
		$postDataArr['tr_remark'] = 'Slots7Casino';
		$postDataArr['tr_dob'] =$birthdate;
		$postDataArr['tr_return_url']= CALLBACKURL;
					
		// print('<pre>');print_r($postDataArr);
              
        $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
	    array_pop($documentroot);
	    array_push($documentroot, 'logs');
	    $root_path = implode('/', $documentroot);
	    file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')."ElectronsWorlds::REQUEST ::".json_encode($postDataArr).PHP_EOL , FILE_APPEND | LOCK_EX);
			
     
        //===============================
        $curl = curl_init($gatewayURL);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
		curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
		curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($postDataArr));//Transmit datas by using POST
		$response = curl_exec($curl);
		curl_close($curl);
        $result_response = json_decode($response,true);
		// print('<pre>');print_r($result_response);
		//return result array
		$result = array();

		
        
        $result['tradeNo']      = $result_response['transaction_id'];//return tradeNo
        $result['orderNo']      = $orderNo;//return orderno
        $result['orderAmount']  = $orderAmount;//return orderAmount
        $result['orderCurrency'] = $currency;//return orderCurrency
        $result['orderStatus']  = $result_response['response'];//return orderStatus
        $result['orderInfo']    = !empty($result_response['message'][0]) ? $result_response['message'][0] : $result_response['message']['p_pay_info'];//return orderInfo
        $result['response_code']= $result_response['message']['p_responseCode'];//return signInfo
        $result['riskInfo']     = $result_response['message']['p_riskInfo'];//return riskInfo

           
			
		file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')."Electronsworld::RESPONSE ::".json_encode($result).PHP_EOL , FILE_APPEND | LOCK_EX);
		/* Executes only when request from cron*/
		if(isset($_POST['submitfrom']) && $_POST['submitfrom']=="cron"){
			 $result1['tradeNo']      = $result_response['transaction_id'];//return tradeNo
             $result1['orderNo']      = $orderNo;//return orderno
             $result1['orderAmount']  =($result['orderAmount']) ? $result['orderAmount'] : $orderAmount;//return orderAmount
             $result1['orderCurrency'] =$result['orderCurrency'];//return orderCurrency
             if($result['orderStatus'] == 'SUCCESSFUL'){
             	$result1['orderStatus'] = 1;	
             }else{
             	$result1['orderStatus']  = $result['orderStatus'];//return orderStatus
			 }
             $result1['orderInfo']     = $result['orderInfo']."||".$result['riskInfo'];//return riskInfo
             file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." Electronsworld response data for cron: ".json_encode($result1).PHP_EOL , FILE_APPEND | LOCK_EX);
			 echo  json_encode($result1);exit;
			
		}
			/*End Executes only when request from cron*/
			
			
		            
            $tradeNo      = $result['tradeNo'];//return tradeNo
            $orderNo      = $result['orderNo'];//return orderno
            $orderAmount  = $result['orderAmount'];//return orderAmount
            $orderCurrency= $result['orderCurrency'];//return orderCurrency
            $orderStatus  = $result['orderStatus'];//return orderStatus
            $orderInfo    = $result['orderInfo'];//return orderInfo
            $riskInfo     = $orderInfo.'||'.$result['response_code'].'||'.$result['riskInfo'];//return riskInfo
            
			// $descriptor = $result['riskInfo'] ;
            if (isset($result)){

                  $_ARR_RESP['Foreign'] = $tradeNo;   //return Id need to 

                  $_PARAMS['foreign_id'] = null;
                  $_PARAMS['foreign_id'] = $_ARR_RESP['Foreign'];
                  set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
				  
				  //set card details to save  
		          $_PARAMS['cardnumber'] 	= $encrypted_card_no;
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

              if($orderStatus == "SUCCESSFUL" ){
             	
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
					}*/
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
	update_payment_discriptor($_PAYMENT_ID,$descriptor);
	
}


if ($_PAYMENT_ID){
		// $_LOCATION_URL  = $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID;
		if($_RESULT['status'] != 'Success')
		{
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID."&s=declined";	
		}
		else {
			$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['amount'];
		}
	} elseif($_CANCELLED) {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'?s=cancelled';
	} else {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'?s=unknown';
	}

if (!$_PARAMS['redirect_url']){
	$_LOCATION_URL = HOST_HTTP_WEB;
}

/*switch($_RESULT['status']){
	case 'Success':	send_email_notification($_PAYMENT_ID, 'AUTHORISED_DEPOSIT', $_PARAMS); break;
	case 'Pending':	send_email_notification($_PAYMENT_ID, 'PENDING_DEPOSIT', $_PARAMS); break;
	case 'Error':	send_email_notification($_PAYMENT_ID, 'DECLINED_DEPOSIT', $_PARAMS); break;
}*/

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
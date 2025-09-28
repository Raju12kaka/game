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
      $merNo            = trim($_POST['authorisationkey1']); //MerchantNo.
      $merchant_api_key = trim($_POST['authorisationkey2']); //terminalname.
      $siteId			= trim($_POST['authorisationkey3']); 

      $orderNo 			= trim($_PARAMS['id']);
      $orderAmount      = trim($_PARAMS['amount']);
      $cardNo           = trim($_POST['cardNumber']);
      $cardExpireYear   = substr(trim($_POST['cardExpireYear']), -2);
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
      $currency 		= "USD";
	
      $card_type 		= trim($_POST['authorisationkey4']);
       
      $country          = !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
      $state            = !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
      $city             = trim($_POST['billingcity']);
      $address          = trim($_POST['billingaddress']);
      $zip              = !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
	  $useragent		= trim($_POST['useragent']);
	  
      $returnUrl        = "http://".$_SERVER['HTTP_HOST']."/error.php"; //real trading websites

     /****************************** 
       Submitting parameters by using curl and get returned XML parameters
      
     ****************************/
      //encrypted the sensitive data
      	$decimalamount = number_format($orderAmount, 2, ".", ",");
	    $hash_info=hash("sha256",$merNo.$siteId.$orderNo.$decimalamount.$currency.$merchant_api_key);
	    
	    
	    
	    $curlPost="order_amount=".$decimalamount;
	    $curlPost.="&order_currency=".$currency;
	    $curlPost.="&mid=".$merNo;
	    $curlPost.="&site_id=".$siteId;
	    $curlPost.="&oid=".$orderNo;
	    $curlPost.="&hash_info=".$hash_info;
	    
	    $curlPost.="&card_no=".$cardNo; 
	    $curlPost.="&card_ex_year=".$cardExpireYear;
	    $curlPost.="&card_ex_month=".$cardExpireMonth;
	    $curlPost.="&card_cvv=".$cardSecurityCode;
	    
	    $curlPost.='&bill_firstname='.$firstName;
	    $curlPost.='&bill_lastname='.$lastName;
	    $curlPost.='&bill_street='.$address;
	    $curlPost.='&bill_city='.$city;
	    $curlPost.='&bill_state='.$state;
	    $curlPost.='&bill_country='.$country;
	    $curlPost.='&bill_zip='.$zip;
	    $curlPost.='&bill_phone='.$phone;
	    $curlPost.='&bill_email='.$email;
	    
	    $curlPost.='&syn_url='.PAYMENTURL.'ipasspayNotify.php';
	    $curlPost.='&asyn_url='.PAYMENTURL.'ipasspayNotify.php';
	    
	    $curlPost.='&source_ip='.$ip; 
	    $curlPost.='&source_url='.$_SERVER["HTTP_REFERER"]; 
	    $curlPost.='&gateway_version=1.0'; 
	    $curlPost.='&uuid='.create_guid(); 

        $url  = "https://www.ipasspay.biz/index.php/Gateway/securepay"; 
       	
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = curl_exec($ch);
		curl_close($ch);
		
		//process $response
		$jsonresult = json_decode($response,true);
		
            
        $tradeNo      = $jsonresult['data']['pid'];//return tradeNo
        $orderNo      = !empty($jsonresult['data']['oid']) ? $jsonresult['data']['oid'] : $orderNo;//return orderno
        $orderAmount  = $orderAmount;//return orderAmount
        $orderCurrency= $currency;//return orderCurrency
        $orderStatus  = ($jsonresult['status'] == 0) ? $jsonresult['status'] : $jsonresult['data']['order_status'];//return orderStatus
        $orderInfo    = $jsonresult['data']['info'].'||'.$jsonresult['data']['billing_desc'].'||'.$jsonresult['data'].'||'.$jsonresult['info'];//return orderInfo
            
        if (isset($jsonresult)){

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

	          if(in_array($orderStatus, array(1,2,3,6))){
	         			          
		          if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
					if ($orderStatus == 2){
						$_RESULT['status'] = 'Success';
						$_RESULT['errorcode'] = 0;
						$_RESULT['html']   = $orderInfo;
					} else if ($orderStatus == 1){
						$_RESULT['status'] = 'Pending';
						$_RESULT['errorcode'] = 0;
						$_RESULT['html']   = $riskInfo;
					} else if ($orderStatus == 6){
						$_RESULT['status'] = 'Declined';
						$_RESULT['errorcode'] = 0;
						$_RESULT['html']   = $riskInfo;
					} else if ($orderStatus == 3){
						$_RESULT['status'] = 'Authorized';
						$_RESULT['errorcode'] = 0;
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
		          $_RESULT['errorcode'] = 207;
		          $_RESULT['html'] = $orderInfo;
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
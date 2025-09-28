<?php 

define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

$_PAYMENTID = isset($_POST['dummypaymentID']) ? $_POST['dummypaymentID'] : '';
$_VIRTUALPT = isset($_POST['vtpaymentId']) ? $_POST['vtpaymentId'] : '';

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/patriot_class.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	// Get all player details
	$palyer_details = get_player_all_details($_PARAMS['player_id']);
	$getcountrycode = fetchCountryCode($palyer_details->country);
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){


		$_ARR_RESP = array();
		$_ARR_RESP['Foreign'] = time('U');
		$_ARR_RESP['Error'] = 201;
		
		$_ARR_RESP['Status'] = 'Declined';
		
/* **********************************************************************
       Description:Please transafer interface parameters to trust payment gateway by using POST.
                    2.Testing datas,Signkey,MerchantNo.,GatewayNo. have been writen into other parameters in demo.Please write down your value in following variate accordingly.
                    3.After payment, it will return JSON
 **********************************************************************/
      $key            	= trim($_POST['authorisationkey1']); //MerchantKey.
      $account_id 	= trim($_POST['authorisationkey2']); //Merchant account id.
      $secrete_key 	= trim($_POST['authorisationkey3']); //Merchant secrete key.
      $curl_url			= trim($_POST['authorisationkey4']); 

      $orderNo = trim($_PARAMS['id']);
      $orderAmount      = trim($_PARAMS['amount']);
      $cardNo           = trim($_POST['cardNumber']);
      $cardExpireYear   = trim($_POST['cardExpireYear']);
      $cardExpireMonth  = trim($_POST['cardExpireMonth']);
      $cardSecurityCode = trim($_POST['cardSecurityCode']);
      $paymethod 		= "Credit Card";
      $ip               = long2ip($palyer_details->register_IP);

      /* Still need to get from DB */
      $firstName        = $palyer_details->realname;
      $lastName         = $palyer_details->reallastname;
      $email            = $palyer_details->email;
      //$issuingBank      = "bank"; 
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
	  
      $returnUrl        = "http://".$_SERVER['HTTP_HOST']."/error.php"; //real trading websites
      $timestamp        = time();
	  $apikeys			= array("1-WC" => "858931", "9-IR" => "144661", "17-OZ" => "762006", "25-JC" => "856052", "33-CP" => "762836", "2-KL" => "288261", "10-YG" => "713682", "18-EO" => "570474", "26-XA" => "933709", "34-VJ" => "404549", "3-DE" => "556798", "11-AC" => '813200', "19-LX" => "249717", "27-HO" => "925360", "35-GT" => "493685", "4-ZE" => "719840", "12-WN" => "251595", "20-PV" => "495636", "28-BT" => "498807", "36-CQ" => "495212", "5-UY" => "464969", "13-EQ" => "411362", "21-IW" => "602604", "29-QE" => "407285", "37-BX" => "047335", "6-QW" => "638204", "14-SQ" => "575620", "22-DT" => "111181", "30-WU" => "244525", "38-ZZ" => "785949", "7-XJ" => "635896", "15-LL" => "531631", "23-JX" => "764784", "31-JC" => "105354", "39-MP" => "975400", "8-PV" => "264343", "16-XC" => "393945", "24-UQ" => "408175", "32-ZF" => "848703", "40-WL" => "760347", "41-DV" => "854474", "49-BN" => "867270", "57-PW" => "608506", "65-SE" => "250096", "73-VN" => "317422", "42-FU" => "070981", "50-AK" => "319475", "58-XD" => "678571", "66-DK" => "871583", "74-NT" => "088813", "43-BW" => "661464", "51-UN" => "136051", "59-FE" => "213685", "67-RZ" => "720272", "75-XM" => "280646", "44-VS" => "037286", "52-QT" => "919604", "60-BF" => "621133", "68-GX" => "843498", "76-DP" => "666991", "45-GY" => "774068", "53-SV" => "781314", "61-KA" => "145441", "69-YC" => "027210", "77-AH" => "651287", "46-TB" => "403440", "54-JQ" => "485967", "62-NR" => "891963", "70-EE" => "246810", "78-SN" => "552884", "47-FC" => "319349", "55-KX" => "441739", "63-LK" => "709403", "71-UO" => "262644", "79-OM" => "655582", "48-HB" => "283801", "56-FQ" => "504682", "64-FU" => "360597", "72-YC" => "929187", "80-PQ" => "826653", "81-VK" => "385571", "89-PA" => "112917", "97-SN" => "358079", "105-AM" => "452962", "113-JO" => "595452", "82-CI" => "468389", "90-GQ" => "752547", "98-UO" => "803546", "106-YJ" => "585970", "114-LF" => "411018", "83-AE" => "994710", "91-CM" => "171281", "99-LE" => "820975", "107-PB" => "110665", "115-NG" => "899202", "84-JR" => "745850", "92-MO" => "514560", "100-OK" => "437575", "108-YW" => "678337", "116-YE" => "267781", "85-LO" => "207775", "93-YP" => "674348", "101-HM" => "900158", "109-TH" => "841868", "117-OU" => "338980", "86-SS" => "822226", "94-YZ" => "360048", "102-MY" => "471367", "110-OV" => "256843", "118-QS" => "206686", "87-KS" => "093885", "95-RK" => "932257", "103-ZX" => "080391", "111-AD" => "749914", "119-IM" => "502156", "88-BL" => "631258", "96-WD" => "641073", "104-QV" => "044652", "112-XP" => "706654", "120-EA" => "338309");
      
      $send_creditcard_no = encrypt3DES($cardNo);
      $send_cvv = encrypt3DES($cardSecurityCode);
      $send_cardExpireYear = encrypt3DES($cardExpireYear);
      $send_cardExpireMonth = encrypt3DES($cardExpireMonth);

     /****************************** 
       Submitting parameters by using curl and get returned XML parameters
      
     ****************************/
      
            $arr = array(
				'account_id' => $account_id,
				'credit_card_number' => $send_creditcard_no,
				'cvv' => $send_cvv,
				'expiration_date_month' => $send_cardExpireMonth,
				'expiration_date_year' => $send_cardExpireYear,
				'name_on_card' => $firstName." ".$lastName,
				'deposit_category' => 1,
				'address' => $address,
				'zip' => $zip,
				'city' => $city,
				'state' => $state,
				'amount' => number_format($orderAmount, 2, ".", ","),
				'currency' => $currency,
				'key' => $key,
				'ts' => "$timestamp",
			);
			
            $strToSign = '';
            foreach ($arr as $k => $v) {
        		if ($v !== NULL) {
            		$strToSign .= "$k:$v:";
        		}
    		}
            
            $strToSign .= $secrete_key;
    		$arr['sign'] = md5($strToSign);

      
       		$data = json_encode($arr);
       		$url  = $curl_url; 
       
       		file_put_contents(dirname(dirname(__DIR__)) . '/logs/payments_patroit.log', " CURL URL:: ".$url." :: POST REQUEST FROM GATEWAY:: " . $data . PHP_EOL, FILE_APPEND | LOCK_EX);
            
            //===============================
            $curl = curl_init($url);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
            curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
            //curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
            $jsonrs = curl_exec($curl);
            curl_close ($curl); 
			
			file_put_contents(dirname(dirname(__DIR__)) . '/logs/payments_patroit.log', " RESPONSE FROM GATEWAY:: " . $jsonrs . PHP_EOL, FILE_APPEND | LOCK_EX);

            $result = json_decode($jsonrs,TRUE);
			$tradeNo      = '';//return tradeNo
            $orderNo      = $orderNo;//return orderno
            $orderAmount  = $orderAmount;//return orderAmount
            $orderCurrency= $currency;//return orderCurrency
            $orderStatus  = '';//return orderStatus
            $orderInfo    = $result['msg'];//return orderInfo
            $riskInfo     = "token : ".$result['token_number'];//return riskInfo
            $errorcode	  = '';
           // print('<pre>');print_r($result);exit;
			if($result['status'] == "success")
			{
				$url_unverified_finish = "https://api.paytriot.co.uk/api/merchant/v/1.0/function/finish_load_from_unverified_card/";
				$timestamp_finish = time();
				$arr_finish = array(
					'hash' => $result['hash'],
					'token_number' => $result['token_number'],
					'token_code' => $apikeys[$result['token_number']],
					'key' => $key,
					'ts' => "$timestamp_finish",
				);
				
	            $strToSign_finish = '';
	            foreach ($arr_finish as $k_finish => $v_finish) {
	        		if ($v_finish !== NULL) {
	            		$strToSign_finish .= "$k_finish:$v_finish:";
	        		}
	    		}
	            
	            $strToSign_finish .= $secrete_key;
	    		$arr_finish['sign'] = md5($strToSign_finish);
					      
	       		$data_finish = json_encode($arr_finish);
	       		$url_finish  = $url_unverified_finish; 
	       
	       		file_put_contents(dirname(dirname(__DIR__)) . '/logs/payments_patroit.log', " CURL URL:: ".$url_finish." :: POST REQUEST TO GATEWAY FOR UNVERIFIED FINISH:: " . $data_finish . PHP_EOL, FILE_APPEND | LOCK_EX);
	            
	            //===============================
	            $curl_finish = curl_init("https://api.paytriot.co.uk/api/merchant/v/1.0/function/finish_load_from_unverified_card");
	            curl_setopt($curl_finish, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl_finish, CURLOPT_SSL_VERIFYHOST, 0);
	            curl_setopt($curl_finish, CURLOPT_HEADER, 0 ); // Colate HTTP header
	            curl_setopt($curl_finish, CURLOPT_RETURNTRANSFER, true);// Show the output
	            curl_setopt($curl_finish, CURLOPT_POST,true); // Transmit datas by using POST
	            curl_setopt($curl_finish, CURLOPT_POSTFIELDS,$data_finish);//Transmit datas by using POST
	            // curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
	            $jsonrs_finish = curl_exec($curl_finish);
	            curl_close ($curl_finish);
	            
				file_put_contents(dirname(dirname(__DIR__)) . '/logs/payments_patroit.log', " RESPONSE FROM GATEWAY FINISH:: " . $jsonrs_finish . PHP_EOL, FILE_APPEND | LOCK_EX);
	
	            $result_finish = json_decode($jsonrs_finish,TRUE);
	            
				$tradeNo 		= $result_finish['transaction_id'];
				$orderStatus  	= $result_finish['status'];//return orderStatus
	            $orderInfo    	= $result_finish['msg'];//return orderInfo
	            $riskInfo     	= ($result_finish['description']) ? $result_finish['description']." : " : '';//return riskInfo
	            $errorcode	  	= ($result_finish['code']) ? $result_finish['code']." : " : '';//return error code
	            $sddata			= $result_finish['s3d'];
	            
				
				if($result_finish['s3d'] == 1){
					$s3dURL = "https://api.paytriot.co.uk/api/merchant/v/1.0/function/s3d_data_submit";
					$timestamp_s3d = time();
					$arr_s3d_submit = array(
						'hash' => $result['hash'],
						'md' => $result_finish['md'],
						'pa_res' => $result_finish['pa_req'],
						'key' => $key,
						'ts' => "$timestamp_s3d",
					);
					
		            $strToSign_ssubmit = '';
		            foreach ($arr_s3d_submit as $k_submit => $v_submit) {
		        		if ($v_submit !== NULL) {
		            		$strToSign_ssubmit .= "$k_submit:$v_submit:";
		        		}
		    		}
		            
		            $strToSign_ssubmit .= $secrete_key;
		    		$arr_s3d_submit['sign'] = md5($strToSign_ssubmit);
						      
		       		$data_submit = json_encode($arr_s3d_submit);
					
					file_put_contents(dirname(dirname(__DIR__)) . '/logs/payments_patroit.log', " CURL URL:: ".$s3dURL." :: s3d data submit :: " . $data_submit . PHP_EOL, FILE_APPEND | LOCK_EX);
	            
		            //===============================
		            $curl_submit = curl_init($s3dURL);
		            curl_setopt($curl_submit, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($curl_submit, CURLOPT_SSL_VERIFYHOST, 0);
		            curl_setopt($curl_submit, CURLOPT_HEADER, 0 ); // Colate HTTP header
		            curl_setopt($curl_submit, CURLOPT_RETURNTRANSFER, true);// Show the output
		            curl_setopt($curl_submit, CURLOPT_POST,true); // Transmit datas by using POST
		            curl_setopt($curl_submit, CURLOPT_POSTFIELDS,$data_submit);//Transmit datas by using POST
		            // curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
		            $jsonrs_submit = curl_exec($data_submit);
		            curl_close ($data_submit);
		            
					file_put_contents(dirname(dirname(__DIR__)) . '/logs/payments_patroit.log', " RESPONSE FROM GATEWAY S3D data submit:: " . $jsonrs_submit . PHP_EOL, FILE_APPEND | LOCK_EX);
		
		            $result_submit = json_decode($jsonrs_submit, TRUE);
					
					if($result_submit['status'] == 'success'){
						$_RESULT['status'] = 'Initiated';
			      		$_RESULT['errorcode'] = 207;
			      		$_RESULT['foreign_errorcode'] = 0;
			      		$_RESULT['html']   = ($result_submit['msg']) ? $result_submit['msg'] : 'Pending';
						$_REQUEST['payment_foreign_id'] = $result_submit['transaction_id'];
						
						//setting params for card details
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
				  
						//insert into payments table
			      		$_PAYMENT_ID = false;
						$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
						//submitting 3ds form to the bank url
					?>
					
					<script type="text/javascript">
				        window.onload = function(e){
				            setTimeout(function(){ document.getElementById("clubS3DdataSubmit").submit();  }, 3000);
				        }
				    </script>
				
				    <div>
				        <form id="clubS3DdataSubmit" action="<?php echo $result_finish["bank_link"]; ?>" method="post">
				            <input type="hidden" name="MD" value="<?php echo $result_finish["md"]; ?>" />
				            <input type="hidden" name="PaReq" value="<?php echo $result_finish["pa_req"]; ?>" />
				            <input type="hidden" name="TermUrl" value="<?php echo CALLBACKURL ?>/patroit_notify.php?reference_id=<?php echo $_PAYMENT_ID; ?>" />
				        </form>
				    </div>
					<?php
					exit;
					}
				}
			}		   
		               
            
            
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

              if($orderStatus == "success" && $sddata == 0){
             	
    		          $_ARR_RESP['Status'] = 'Approved';
			          
			          if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
			          	if ($_ARR_RESP['Status'] == 'Approved'){
			          		$_RESULT['status'] = 'Success';
			          		$_RESULT['errorcode'] = 0;
			          		$_RESULT['html']   = $errorcode.$orderInfo." || ".$riskInfo;
			          	} else {
			          		$_RESULT['status'] = 'Error';
			          		$_RESULT['errorcode'] = 207;
			          		$_RESULT['foreign_errorcode'] = $_ARR_RESP['Error'];
			          		$_RESULT['html']   = $errorcode.$orderInfo." || ".$riskInfo;
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
		          $_RESULT['html'] = $errorcode.$orderInfo." || ".$riskInfo;
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

if ($_PAYMENT_ID && $_RESULT['status'] == 'Success'){
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['amount'];
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
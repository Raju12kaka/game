<?php

define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

$_PAYMENTID = isset($_POST['dummypaymentID']) ? $_POST['dummypaymentID'] : '';
$_VIRTUALPT = isset($_POST['vtpaymentId']) ? $_POST['vtpaymentId'] : '';

if ($_REQUEST_ID){
    
    include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
    include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
    include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
    include_once dirname(dirname(dirname(__FILE__))).'/models/iCanPayModel.php';
    
    $_PARAMS = array();
    $_PARAMS = get_params($_REQUEST_ID);
    
    // Get all player details
    $palyer_details 	= get_player_all_details($_PARAMS['player_id']);
    $encrypted_card_no 	= $_POST['cardNumber'];
    
    // Get all player details
    $methodData 		= getPaymenthodDetails($_PARAMS['payment_method_id']);
    
    $_PARAMS['amount'] 	= $_PARAMS['amount'] / 100;
    
    if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
        $documentroot 	= explode('/', $_SERVER['DOCUMENT_ROOT']);
        array_pop($documentroot);
        array_push($documentroot, 'logs');
        $root_path 		= implode('/', $documentroot);
        
        /** Setting default status **/
        $_ARR_RESP 				= array();
        $_ARR_RESP['Foreign'] 	= time('U');
        $_ARR_RESP['Error'] 	= 201;
        $_ARR_RESP['Status'] 	= 'Declined';
        
        $_RESULT['status'] = 'Initiated';
        $_RESULT['errorcode'] = 207;
        $_RESULT['foreign_errorcode'] = 0;
        $_RESULT['html']   = 'Pending';
        
        //insert into payments table
        $_PAYMENT_ID = false;
        
        //set card details to save
        $_PARAMS['cardnumber']      = trim($_POST['cardNumber']);
        $_PARAMS['cvv']             = trim($_POST['cardSecurityCode']);
        $_PARAMS['expiryyear']      = trim($_POST['cardExpireYear']); //set card expire year
        $_PARAMS['expirymonth']     = trim($_POST['cardExpireMonth']); //set card expire month
        $_PARAMS['cardname']        = trim($_POST['cardName']); //set name on card
        if(isset($_POST['ssnnumber'])){
            $_PARAMS['ssnnumber']		= trim($_POST['ssnnumber']); //ssn number
        }
        
        $_PARAMS['billingname']		= trim($_POST['billingname']);
        $_PARAMS['billinglastname']	= trim($_POST['billinglastname']);
        $_PARAMS['billingaddress']	= trim($_POST['billingaddress']);
        $_PARAMS['billingcity']		= trim($_POST['billingcity']);
        $_PARAMS['billingcountry']	= trim($_POST['billingcountry']);
        $_PARAMS['billingstate']	= trim($_POST['billingstate']);
        $_PARAMS['billingzip']		= trim($_POST['billingzip']);
        $_PARAMS['billingphone'] 	= trim($_POST['billingphone']);
        $_PARAMS['useragent']       = $_POST['origin'];
        
        //inserting payment before going to process the transaction
        $_PAYMENT_ID 	= insert_payment($_RESULT, $_PARAMS);
        
        
        /* **********************************************************************
         Description:
         1)Please transafer interface parameters to citigateway by using POST.
         2)Testing datas,Merchant Name, Merchant Password, gateway URL. have been saved to payment providers table.Please write down your value in following variables accordingly.
         3)After payment, it will return JSON data
         **********************************************************************/
        $sec_key        	= trim($_POST['authorisationkey1']); //MerchantNo.
        $authenticate_id	= trim($_POST['authorisationkey2']); //terminalname.
        $authenticate_pwd	= trim($_POST['authorisationkey3']);
        
        $orderNo			= trim($_PAYMENT_ID);
        $orderAmount		= trim($_PARAMS['amount']);
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
        
        /* Still need to get from DB */
        $firstName			= !empty($_POST['billingname']) ? $_POST['billingname'] : $palyer_details->realname;
        $lastName			= !empty($_POST['billinglastname']) ? $_POST['billinglastname'] : $palyer_details->reallastname;
        $email				= $palyer_details->email;
        $phone				= $palyer_details->contact_phone;
        $currency			= "USD";
        $card_type			= trim($_POST['authorisationkey4']);
        
        $country			= !empty($_POST['billingcountry']) ? trim($_POST['billingcountry']) : $palyer_details->country;
        $state				= !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : "FL";
        $city				= trim($_POST['billingcity']);
        $address			= trim($_POST['billingaddress']);
        $zip				= !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : "12345";
        $birthdate			= $palyer_details->birthdate;
        //$transaction_hash   = $_POST['transaction_hash'];
        $transaction_hash   = "/CNXXsC/3+jp6iLkr1OjDYcvDZZ/zWjo/CVNLh9nxzBVR+MaIEmGUukgS4yd4ImyF/d628wIIIXVZ17kQRgXxg==";
        
        $country_code3 = fetchCountryCode($country);
        $country = $country_code3->code3;
        
        $params = array( 'authenticate_id'=>$authenticate_id, //'YOUR AUTHENTICATE ID'
            'authenticate_pw'=>$authenticate_pwd, //'YOUR AUTHENTICATE PW'
            'orderid'=>"S7".$orderNo,  //'YOUR ORDER ID'
            'transaction_type'=>'a',
            'amount'=>number_format($orderAmount,2,".",""),
            'currency'=>$currency,
            'ccn'=>$cardNo,
            'exp_month'=>$cardExpireMonth,
            'exp_year'=>substr($cardExpireYear, -2),
            'cvc_code'=>$cardSecurityCode,
            'firstname'=>$firstName,
            'lastname'=>$lastName,
            'email'=>$email,
            'street'=>$address,
            'city'=>$city,
            'zip'=>$zip,
            'state'=>$state,    
            'country'=>$country,
            'phone'=>str_replace('+', "", str_replace(' ', "", $phone)),
            'transaction_hash' => $transaction_hash,
            'dob'=>$birthdate,
            'success_url'=>urlencode(CALLBACKURL.'/icanpaynotifyreturn.php'), //Successurl
            'fail_url'=>urlencode(CALLBACKURL.'/icanpaynotifyreturn.php'), //Failurl
            'notify_url'=>urlencode(CALLBACKURL.'/icanpaynotify.php'), //Notifyurl
            'customerip'=>$ip
        );     //'SCRIPT GENERATED TRANSACTION HASH'
        
       
        /*$jsonRequestData 	= json_encode($requestArr);
         */
        
        // 			$pay = new iCanPay($sec_key, $params, 'API');
        // 			$jsonresponse = $pay->payment();
        
        $pay = new iCanPayModel();
        
        $params['card_info'] = $pay->getCardHash($params, $sec_key);
        
		$signature = "";
    	ksort($params);

    	foreach ($params as $key => $val) {
        	if ($key != "signature") {
            	$signature .= $val;
        	}
    	}
    	$signature = $signature . $sec_key;
    	//echo "SIGNATURE BEFORE ENCRYPT::".$signature;
    	$signature = strtolower(sha1($signature));
    	//echo "SIGNATURE AFTER ENCRYPT::".$signature;
    	
    	$params['signature'] = $signature;
		
        // $params['signature'] = $pay->get3DSSignature($params, $sec_key);
        // $params['transaction_hash'] = $_POST['transaction_hash'];
		// $params['success_url'] = urlencode(CALLBACKURL.'/icanpaynotifyreturn.php'); //Successurl
		// $params['fail_url'] = urlencode(CALLBACKURL.'/icanpaynotifyreturn.php'); //Failurl
		// $params['notify_url'] = urlencode(CALLBACKURL.'/icanpaynotify.php'); //Notifyurl
		
        //$params['transaction_hash'] = $_POST['transaction_hash'];
		//$params['customerip'] = $_SERVER['REMOTE_ADDR'];
        //echo "<pre>"; print_r($params); echo "<br>";
        $curlRequestURL		= 'https://pay.paymentechnologies.co.uk/authorize3dsv_payment';
		// $RequestData = "authenticate_id=".$params['authenticate_id'].
						// "&authenticate_pw=".$params['authenticate_pw'].
						// "&orderid=".$params['orderid'].
						// "&transaction_type=".$params['transaction_type'].
						// "&signature=".$params['signature'].
						// "&amount=".$params['amount'].
						// "&currency=".$params['currency'].
						// "&card_info=".$params['card_info'].
						// "&email=".$params['email'].
						// "&street=".$params['street'].
						// "&city=".$params['city'].
						// "&zip=".$params['zip'].
						// "&state=".$params['state'].
						// "&country=".$params['country'].
						// "&transaction_hash=".$params['transaction_hash'].
						// "&phone=".$params['phone'].
						// "&dob=".$params['dob'].
						// "&success_url=".$params['success_url'].
						// "&fail_url=".$params['fail_url'].
						// "&notify_url=".$params['notify_url'].
						// "&customerip=".$params['customerip'];
        $data_stream = http_build_query($params);
        //echo $RequestData;exit;
        $logmessage 	= date('Y-m-d H:i:s')." iCanPay request data :".$RequestData."\n";
        file_put_contents($root_path.'/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
        
        //===============================//
        // $curl = curl_init($curlRequestURL);
        // curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($curl,CURLOPT_HEADER, "Content-Type: application/x-www-form-urlencoded" ); // Colate HTTP header
        // curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
        // curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
        // curl_setopt($curl,CURLOPT_POSTFIELDS,$data_stream);//Transmit datas by using POST
        //curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
        $ch = curl_init();
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_stream);
    	curl_setopt($ch, CURLOPT_URL, $curlRequestURL);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $jsonresponse = curl_exec($ch);
        curl_close ($ch);
        //
        $finalResult = json_decode($jsonresponse, true);
        //echo "<pre>"; print_r($finalResult);
        /***
         * saving response from processor in the logs
         * start
         ***/
        
        $logmessage 	= date('Y-m-d H:i:s')." iCanPay redirection response data :".$jsonresponse."\n";
        file_put_contents($root_path.'/payments_icanpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		
		
		if($finalResult && $finalResult['redirect_url']){
			$orderNo		= substr($finalResult['orderid'], 2);
			update_payment_foreignid($orderNo, $finalResult['transactionid']);
		?>
		<script language="javascript">location.href = "<?php echo $finalResult['redirect_url']; ?>";</script>
		<?php
		exit;
		}
		
        /*** saving logs end ***/
        /* Executes only when request from cron*/
        if(isset($_POST['submitfrom']) && $_POST['submitfrom']=="cron"){
            $result1['tradeNo']      = $finalResult['transactionid'];//return tradeNo
            $result1['orderNo']      = substr($finalResult['orderid'], 2);//return orderno
            $result1['orderAmount']  =($finalResult['amount']) ? $finalResult['amount'] : $orderAmount;//return orderAmount
            $result1['orderCurrency'] =$finalResult['currency'];//return orderCurrency
            $result1['orderStatus']  = $finalResult['status'];//return orderStatus
            $result1['orderInfo']     = $finalResult['errormessage']."||".$finalResult['errorcode'];//return riskInfo
            file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." directpay response data for cron: ".json_encode($result1).PHP_EOL , FILE_APPEND | LOCK_EX);
            //echo  json_encode($result1);exit;
            
        }
        /*End Executes only when request from cron*/
        // 			echo $jsonresponse; exit;
        $tradeNo		= $finalResult['transactionid'];//return tradeNo
        $orderNo		= substr($finalResult['orderid'], 2);//return orderno
        $orderAmount	= $finalResult['amount'];//return orderAmount
        $orderCurrency	= $finalResult['currency'];//return orderCurrency
        $orderStatus	= $finalResult['status'];//return orderStatus
        $orderInfo		= $finalResult['errormessage'];//return orderInfo
        $riskInfo		= $finalResult['errorcode'];//return riskInfo
        $acquirerInfo	= $finalResult['descriptor'];//return acquirerInfo
        
        if (isset($finalResult)){
            
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
                $_PARAMS['ssnnumber']		= trim($_POST['ssnnumber']); //ssn number
            }
            
            $_PARAMS['billingname']		= trim($_POST['billingname']);
            $_PARAMS['billinglastname']	= trim($_POST['billinglastname']);
            $_PARAMS['billingaddress']	= trim($_POST['billingaddress']);
            $_PARAMS['billingcity']		= trim($_POST['billingcity']);
            $_PARAMS['billingcountry']	= trim($_POST['billingcountry']);
            $_PARAMS['billingstate']	= trim($_POST['billingstate']);
            $_PARAMS['billingzip']		= trim($_POST['billingzip']);
            $_PARAMS['useragent']		= $_POST['origin'];
            $_PARAMS['acquirer']		= $acquirerInfo;
            $paymentcharge				= 0;
            $amount						= 0;
            
            if($orderStatus == 1 ){
                
                $_ARR_RESP['Status'] = 'Approved';
                
                
                if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
                    if ($_ARR_RESP['Status'] == 'Approved'){
                        $_RESULT['status']		= 'Success';
                        $_RESULT['errorcode']	= 0;
                        $_RESULT['html']		= $orderInfo."||".$riskInfo;
                        $_RESULT['paystatus']	= 'OK';
                        $paymentcharge 			= 0;
                        $amount					= $_PARAMS['amount']*100;
                    } else {
                        $_RESULT['status']		= 'Error';
                        $_RESULT['errorcode']	= 207;
                        $_RESULT['foreign_errorcode']	= $_ARR_RESP['Error'];
                        $_RESULT['html']		= $orderInfo."||".$riskInfo;
                        $_RESULT['paystatus']	= 'KO';
                    }
                } else {
                    $_RESULT['status']		= 'Error';
                    $_RESULT['errorcode']	= 204;
                    $_RESULT['html']		= 'Update error';
                    $_RESULT['paystatus']	= 'KO';
                }
                
            }
            else
            {
                
                //check if dummy available or not
                $card_details			= get_payment_card_details($encrypted_card_no);
                $card_number			= !empty($card_details->cardnumber) ? $card_details->cardnumber : '';
                $paymentMethod			= ($_PARAMS['payment_method_id'] == 100) ? 106 : 107;
                $paymentProvider		= ($paymentMethod == 106) ? 111 : 112;
                $dummymethods			= implode(',', array(106,107));
                $checkPlayerHasDummy	= get_player_hasdummy($_PARAMS['player_id'], $dummymethods);
                //check if the player country is enable for dummy process
                /*
                 $checkCountryEnable = get_country_dummy_status($_PARAMS['country_id']);
                 
                 if($methodData->is_dummy == 1 && ($card_number != $encrypted_card_no) && ($checkPlayerHasDummy->cnt == 0) && ($checkCountryEnable->is_enable == 1)){
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
                $_RESULT['html'] = $orderStatus."||".$orderInfo."||".$riskInfo;
                $_RESULT['paystatus']	= 'KO';
            }
        }else{
            
        }
        
    }
    else
    {
        $_RESULT['status'] = 'Error';
        $_RESULT['errorcode'] = 102;
        $_RESULT['html'] = 'Infromation Incorrect';
        $_RESULT['paystatus']	= 'KO';
    }
    
}
else
{ // NO REQUEST_ID
    $_RESULT['status'] = 'Error';
    $_RESULT['errorcode'] = 101;
    $_RESULT['html'] = 'No request id';
    $_RESULT['paystatus']	= 'KO';
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
    
    $id = update_payments($_PAYMENTID, $_PARAMS, $tradeNo);
    
}elseif(!empty($_PAYMENTID) && $_RESULT['status'] != 'Success'){
    insert_payment($_RESULT, $_PARAMS);
    $id = insert_vt_payments($_PAYMENTID, $_PARAMS, $_RESULT);
    
}else{
    // $_PAYMENT_ID = false;
    // $_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
    $palyer_details 	= get_player_all_details($_PARAMS['player_id']);
    $totalamount		= $amount + $palyer_details->balance;
    update_payments_status($_PAYMENT_ID, $_PARAMS['player_id'], $_RESULT['paystatus'], $_RESULT['html'], 0, $paymentcharge, $amount, $totalamount, $_POST['providerName']);
    update_payment_foreignid($orderNo, $tradeNo);
    $_PARAMS['amount'] 	= $_PARAMS['amount'] * 100;
    //update_player_balance($_PARAMS);
    if($acquirerInfo)
        update_payment_discriptor($_PAYMENT_ID, $acquirerInfo);
        
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
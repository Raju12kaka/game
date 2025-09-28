<?php 

define('PROVIDER_NAME', '');
define('ENCRYPTION_KEY', 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282');

//$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];
$_REQUEST_ID = $_POST['transactionId'];

// $_PAYMENTID = isset($_POST['dummypaymentID']) ? $_POST['dummypaymentID'] : '';
// 
// $_VIRTUALPT = isset($_POST['vtpaymentId']) ? $_POST['vtpaymentId'] : '';

if ($_REQUEST_ID){

	include_once dirname(dirname(__FILE__)).'/models/common.php';
	include_once dirname(dirname(__FILE__)).'/models/define.php';
	include_once dirname(dirname(__FILE__)).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS['player_id'] = $_GET['playerid'];
	$prequestId = get_params_from_playerid($_PARAMS['player_id']);
	
	//save log
	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
	array_pop($documentroot);
	array_push($documentroot, 'logs');
	$root_path = implode('/', $documentroot);
	$logmessage = date('Y-m-d H:i:s')." ccpay3d transaction notify || playerid: ".$_GET['playerid']." || Mid : ".$_REQUEST_ID;
	file_put_contents($root_path.'/payments.log', date('Y-m-d H:i:s')." Payments redirect url: ".$result['redirectUrl'].PHP_EOL , FILE_APPEND | LOCK_EX);
	
	$arr = array('transaction_id' => $_REQUEST_ID);
	
	//==========================================================//
	$data =  http_build_query($arr);
   
	$url  = "https://services.avenuepay.com/requestStatus"; 
	           
	//===============================
	$ch = curl_init($url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch,CURLOPT_HEADER, 0 ); // Colate HTTP header
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);// Show the output
	curl_setopt($ch,CURLOPT_POST,true); // Transmit datas by using POST
	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
	//curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
	$jsonrs = curl_exec($ch);
	
	curl_close ($ch); 
	
	$response = json_decode($jsonrs,TRUE);
	//==========================================================//
	$result = $response['data'];
	if(empty($result)){
		$tradeNo      = $_REQUEST_ID;//return tradeNo
        $orderNo      = '';//return orderno
        $orderAmount  = ($prequestId->amount)/100;//return orderAmount
        $orderCurrency= $prequestId->currency_id;//return orderCurrency
        $orderStatus  = 0;//return orderStatus
        $orderInfo    = 'Declined';//return orderInfo
        $riskInfo     = 'Declined';//return riskInfo
	}
	if (!empty($result)){

		$_ARR_RESP 				= array();
		$_ARR_RESP['Foreign'] 	= time('U');
		$_ARR_RESP['Error'] 	= 201;
		
		$_ARR_RESP['Status'] = 'Declined';
	
	    
        $tradeNo      = $result['ap_transaction_id'];//return tradeNo
        $orderNo      = $result['transaction_id'];//return orderno
        $orderAmount  = $result['amount'];//return orderAmount
        $orderCurrency= $result['currency'];//return orderCurrency
        $orderStatus  = $result['status'];//return orderStatus
        $orderInfo    = $result['statusdescription'];//return orderInfo
        $riskInfo     = $result['statusdescription'];//return riskInfo
       

       if (isset($result)){

          $_ARR_RESP['Foreign'] 	= $tradeNo;   //return Id need to 

          $_PARAMS['id']			= $prequestId->id;
          $_PARAMS['foreign_id'] 	= null;
          $_PARAMS['foreign_id'] 	= $_ARR_RESP['Foreign'];
          set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
        
          //set card details to save  
          $_PARAMS['cardnumber'] 	= $result['card_no'];
  		  $_PARAMS['cvv'] 			= $result['cvv_no'];
   	  	  $_PARAMS['expiryyear']  	= trim($result['expiry_year']); //set card expire year
		  $_PARAMS['expirymonth'] 	= trim($result['expiry_month']); //set card expire month
		  $_PARAMS['cardname'] 		= trim($result['c_firstname'].' '.$result['c_lastname']); //set name on card
		  
		  $_PARAMS['amount']		= $orderAmount;
		  $_PARAMS['currency_id']	= $result['currency'];
		  $_PARAMS['ip']			= $prequestId->ip;
		  $_PARAMS['payment_method_id']= $_GET['m'];
		  $_PARAMS['payment_provider_id']= $_GET['p'];
		  $_PARAMS['country_id']	= $result['c_country'];
		  $_PARAMS['web_id']		= $_GET['w'];
		  $_PARAMS['request_id']	= $prequestId->request_id;
		  $_PARAMS['billingaddress']= trim($result['c_address']);
		  $_PARAMS['billingcity'] 	= trim($result['c_city']);
		  $_PARAMS['billingstate'] 	= trim($result['c_state']);
		  $_PARAMS['billingzip'] 	= trim($result['c_postal']);
		  $_PARAMS['player_currency_amount'] = $orderAmount*100;
		  $_PARAMS['player_currency_id'] = $result['currency'];
		  $_PARAMS['useragent'] 	= $_SERVER['HTTP_USER_AGENT'];
		  $_PARAMS['redirect_url']	= $prequestId->redirect_url;

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
	$_RESULT['status']		= 'Error';
	$_RESULT['errorcode'] 	= 101;
	$_RESULT['html'] 		= 'No request id';
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
	// case 'Success':	send_email_notification($_PAYMENT_ID, 'AUTHORISED_DEPOSIT', $_PARAMS); break;
	// case 'Pending':	send_email_notification($_PAYMENT_ID, 'PENDING_DEPOSIT', $_PARAMS); break;
	//case 'Error':	send_email_notification($_PAYMENT_ID, 'DECLINED_DEPOSIT', $_PARAMS); break;
}

if (SEND_EMAIL_TO_TECH){
	//send_email(TECH_EMAIL, PROVIDER_NAME.': '.$_RESULT['status'].'('.$_RESULT['errorcode'].'): '.$_RESULT['html'], "PAYMENT_ID: ".$_PAYMENT_ID.", RESULT: ".var_export($_RESULT, true).", PARAMS: ".var_export($_PARAMS, true).", OUTPUT: ".var_export($_ARR_RESP, true));
}
if(!empty($_PAYMENTID)){
	if($_RESULT['status'] == 'Success'){
		echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
	}else{
		echo "<b color='green'>Transaction failed. Please try again.</b>";
	}
?>

	<script language="javascript">
		parent.parent.location="<?php echo CRMURL; ?>Transacciones/dummydeposits";
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

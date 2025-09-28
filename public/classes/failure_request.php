<?php 

define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderCurrency'].$_POST['orderNumber'].PRIVATE_KEY);
		
		//if ($_PARAMS['amount'] != $_POST['orderAmount'] || $_PARAMS['currency_id'] != $_POST['orderCurrency'] || $signature!=strtolower($_POST['signInfo'])){
	    if ($_PARAMS['player_currency_amount'] != ($_POST['orderAmount']*100) || strtolower($signature)!=strtolower($_POST['signInfo'])){
			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			
			// DUMMY PROVIDER RESPONSE
			
			$_ARR_RESP = array();
			$_ARR_RESP['Foreign'] = time('U');
			$_ARR_RESP['Error'] = 201;
						
			$_ARR_RESP['Status'] = 'Declined';
			$_PARAMS['foreign_id'] = null;
			
			  //set card details to save  
	          $_PARAMS['cardnumber'] = trim($_POST['cardNumber']);
			  $_PARAMS['cvv'] = trim($_POST['cardSecurityCode']);
	      	  $_PARAMS['expiryyear']  = trim($_POST['cardExpireYear']); //set card expire year
			  $_PARAMS['expirymonth'] = trim($_POST['cardExpireMonth']); //set card expire month
			  $_PARAMS['cardname'] = trim($_POST['cardName']); //set name on card
            			  
			  $_PARAMS['billingaddress'] = trim($_POST['billingaddress']);
			  $_PARAMS['billingcity'] = trim($_POST['billingcity']);
			  $_PARAMS['billingcountry']= trim($_POST['billingcountry']);
			  $_PARAMS['billingstate'] = trim($_POST['billingstate']);
			  $_PARAMS['billingzip'] = trim($_POST['billingzip']);
			  $_PARAMS['billingemail'] = trim($_POST['billingemail']);
			  $_PARAMS['useragent'] = $_POST['origin'];
            			  
			if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
				$_RESULT['status'] = 'Error';
				$_RESULT['errorcode'] = !empty($_POST['errorcode']) ? $_POST['errorcode'] : 207;
				$_RESULT['foreign_errorcode'] = $_ARR_RESP['Error'];
				$_RESULT['html']   = !empty($_POST['custommsg']) ? $_POST['custommsg'] : 'Declined';
			} else {
				$_RESULT['status'] = 'Error';
				$_RESULT['errorcode'] = 204;
				$_RESULT['html']   = 'Update error';
			}

		}

	} else { // INVALID PARAMETERS
		$_RESULT['status'] = 'Error';
		$_RESULT['errorcode'] = 103;
		$_RESULT['html'] = 'Invalid parameters';
	}
	
} else { // NO REQUEST_ID
	$_RESULT['status'] = 'Error';
	$_RESULT['errorcode'] = 101;
	$_RESULT['html'] = 'No request id';
}
// echo "<pre>";
// print_r($_RESULT);
// exit;

//print('<pre>');print_r($_PARAMS);print_r($_RESULT);exit;
$_PAYMENT_ID = false;
$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);

if ($_PAYMENT_ID){
	if($_RESULT['errorcode']=='10407'){
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=10407';	
	}else{
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=declined';
	}
} elseif($_CANCELLED) {
	
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=declined';
} else {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'s=unknown';
}

if (!$_PARAMS['redirect_url']){
	$_LOCATION_URL = HOST_HTTP_WEB;
}

switch($_RESULT['status']){
	case 'Success':	send_email_notification($_PAYMENT_ID, 'AUTHORISED_DEPOSIT', $_PARAMS); break;
	case 'Pending':	send_email_notification($_PAYMENT_ID, 'PENDING_DEPOSIT', $_PARAMS); break;
	//case 'Error':	send_email_notification($_PAYMENT_ID, 'DECLINED_DEPOSIT', $_PARAMS); break;
}

?>

<script language="javascript">
	parent.parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
</script>
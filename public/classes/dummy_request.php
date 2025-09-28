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
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	$_PARAMS['player_currency_amount'] = $_PARAMS['player_currency_amount'] / 100;
		
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderCurrency'].$_POST['orderNumber'].PRIVATE_KEY);
		
		if ($_PARAMS['amount'] != $_POST['orderAmount'] || $_PARAMS['currency_id'] != $_POST['orderCurrency'] || $signature!=strtolower($_POST['signInfo'])){
        //if ($_PARAMS['player_currency_amount'] != ($_POST['orderAmount']*100) || strtolower($signature)!=strtolower($_POST['signInfo'])){
			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			
			// DUMMY PROVIDER RESPONSE
			//echo "In";
			$_ARR_RESP 				= array();
			$_ARR_RESP['Foreign'] 	= time('U');
			$_ARR_RESP['Error'] 	= 201;
			
			$_ARR_RESP['Status'] 	= 'Approved';
			//$_ARR_RESP['Status'] 	= 'Declined';
			$_PARAMS['foreign_id'] 	= null;
			if ($_ARR_RESP['Status'] == 'Approved'){
				$_PARAMS['foreign_id'] = $_ARR_RESP['Foreign'];
				set_foreign_id($_PARAMS['id'], $_PARAMS['foreign_id']);
			}
			
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
			  $_PARAMS['billingemail'] 	= trim($_POST['billingemail']);
			  $_PARAMS['useragent'] 	= $_POST['origin'];
			  $_PARAMS['binrule_id']	= trim($_POST['binrule_id']);
            			  
			if (set_request($_PARAMS['id']) && set_response($_PARAMS['id'])) {
				$_RESULT['status'] 		= 'Success';
				$_RESULT['errorcode'] 	= 0;
				$_RESULT['html']   		= 'Authorised';
			} else {
				$_RESULT['status'] 		= 'Error';
				$_RESULT['errorcode'] 	= 204;
				$_RESULT['html']   		= 'Update error';
			}
			// $_RESULT['status'] 		= 'Error';
			// $_RESULT['errorcode'] 	= 204;
			// $_RESULT['html']   		= 'Update error';

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

/*
 echo "<pre>";
 print_r($_PARAMS);
 echo "</pre>";
 echo "<pre>";
 print_r($_RESULT);
 echo "</pre>";
 // exit; 
*/

$_PAYMENT_ID = false;
$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
/*
echo "<pre>";
print_r($_PAYMENT_ID);
echo "<br>";
*/
if ($_PAYMENT_ID){
	//$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$_PAYMENT_ID.'&amt='.$_PARAMS['player_currency_amount']/100;;
	if($_RESULT['status'] == 'Success')
	{
		// $_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID.'&amt='.$_PARAMS['player_currency_amount']/100;
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'-success/'.$_PAYMENT_ID;
	}
	else
	{
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'-declined/'.$_PAYMENT_ID;
		// $_LOCATION_URL  = $_PARAMS['redirect_url'].'-failed';
	}
} elseif($_CANCELLED) {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'-failed';
} else {
	$_LOCATION_URL  = $_PARAMS['redirect_url'].'-failed';
}

/*
echo "<pre>";
print_r($_PARAMS['redirect_url']);
echo "<br>";
print_r($_LOCATION_URL);
// echo "<br>";
//rint_r($_VIRTUALPT);
die; 
*/
include_once '../loader.php';
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
?>

<?php if($_VIRTUALPT == 1){
    if($_RESULT['status'] == 'Success'){
        echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
    }else{
        echo "<b color='green'>Transaction failed. Please try again.</b>";
    }
    ?>
    <script language="javascript">
        parent.parent.location="<?php echo $_PARAMS['redirect_url'] ?>/&msg=<?php echo $_RESULT['status'].'&result='.$_PAYMENT_ID.'&amt='.$_PARAMS['player_currency_amount']/100; ?>";
    </script>
<?php } else{ ?>
    <script language="javascript">
        window.parent.location = "<?php echo $_LOCATION_URL; ?>";
    </script>
<?php } ?>
<?php 
set_time_limit(1000);
define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	//$_PARAMS = get_params($_REQUEST_ID);
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	$signInfo = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderCurrency'].$_POST['orderNumber'].PRIVATE_KEY);
		
		if ($_PARAMS['amount'] != $_POST['orderAmount'] || $_PARAMS['currency_id'] != $_POST['orderCurrency'] || $signature!=strtolower($signInfo)){

			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			$_RESULT['status'] = 'Initiated';
      		$_RESULT['errorcode'] = 207;
      		$_RESULT['foreign_errorcode'] = 0;
      		$_RESULT['html']   = 'Pending';
      		
      		//insert into payments table
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			
			//get authorization credentials from Provider
			$credentials = getProviderDetails($_PARAMS['payment_provider_id']);
			require_once('Api.php');
			
			$url = "https://sandboxapi.upaycard.com/api/merchant"; 
			$key = $credentials->credential1; 
			$secret = $credentials->credential2; 
			$encryption_key = $credentials->credential3; 
			$debug_mode = false; // in live should be false 
			
			$api = new Upaycard_Api($url, $key, $secret, $debug_mode,$encryption_key); 

			$receiver_account = $credentials->credential4; // your receiving_account number 
			$sender = $_POST['useraccountid']; // sender account id/username/email 1783901
			$amount = number_format((float)$_PARAMS['amount'], 2, '.', ''); 
			$currency = $_PARAMS['currency_id']; // currency code (USD or EUR) 
			$order_id = $_POST['orderNumber']; // order_id in your system 
			$description = "First order"; // transaction description //
			
			// 1 step initialize transfer and get token code 
			$data = $api->initializeTransfer($receiver_account, $sender, $amount, $currency, $order_id, $description,"1");
			
			if ($data["status"] == "success") {
				//Payment Confirmation Page
				include_once 'tokenform.php';
			?>
			
			<?php
			}else{
				$status = "KO";
				update_payments_status($_PAYMENT_ID, $_PARAMS['player_id'], $status, $data['code'].' || '.$data['description']);
			?>
			
			<div style="text-align: center"><img src="img/oQ0tF.gif" align="center" width="80px" height="80px" /></div>
			<script type="text/javascript">
				var LOCATIONURL  = "<?php echo $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID ?>";
				setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 2000);
			</script>
			<?php
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



?>

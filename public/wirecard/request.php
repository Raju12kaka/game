<style>
  body>.container-fluid{
   	margin:3px !important;
   }
   .errormsg{
   	color:red;
    line-height: 1.5em;
   }
</style>
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
			
			$palyer_details = get_player_all_details($_PARAMS['player_id']);
			//get authorization credentials from Provider
			$credentials = getProviderDetails($_PARAMS['payment_provider_id']);
			
			//insert into payments table
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			$getcountrycode = fetchCountryCode($palyer_details->country);
			$statename = get_states_name($palyer_details->state, $palyer_details->country);
			$phone = str_replace('+', '', $palyer_details->contact_phone);
			$phone = str_replace(' ', '', $phone);
      		
			$data = array(
				'pay_to_email' 		=> $credentials->credential1,
				'pay_from_email' 	=> $palyer_details->email,
				'merchant_id' 		=> $credentials->credential2,
				'transaction_id' 	=> $_PAYMENT_ID,
				'mb_amount' 		=>  number_format($_PARAMS['amount'], 2),
				'mb_currency' 		=> 'EUR',
				'amount' 			=> number_format($_PARAMS['amount'], 2),
				'currency' 			=> 'EUR',
				'language' 			=> 'EN',
				'prepare_only' 		=> 1,
				'firstname' 		=> $palyer_details->realname,
				'lastname' 			=> $palyer_details->reallastname,
				'date_of_birth' 	=> date('dmY', strtotime($palyer_details->birthdate)),
				'address' 			=> $palyer_details->address,
				'phone_number' 		=> $phone,
				'postal_code' 		=> $palyer_details->zipcode,
				'city' 				=> $palyer_details->city,
				'state' 			=> $statename[0]['state_name'],
				'country' 			=> $getcountrycode->code3,
				'status_url' 		=> PAYMENTURL.'skrill/callBackPage.php',
				'return_url' 		=> $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID,
				'return_url_target' => 1,
				'cancel_url' 		=> $_PARAMS['redirect_url'].'?s=cancelled',
				'cancel_url_target' => 1,
				//'ondemand_note' 		=> '1-Tap Payment',
				//'ondemand_status_url' => PAYMENTURL.'skrill/callBackPage.php'
			);
			
			$timestamp = date('YmdHms');
			$ipAddress = long2ip($palyer_details->register_IP);
			
			$hash_fields = $timestamp.$_PAYMENT_ID."61e8c484-dbb3-4b69-ad8f-706f13ca141b"."purchase".$_PARAMS['amount']."EUR"."127.0.0.1".PAYMENTURL."skrill/callBackPage.php"."efabf47b-e43b-4785-873f-1c5bc65b7cd2";
			$hashstring = hash('sha256', $hash_fields);
			?>
			<div id="loadWircardForm"></div>
			<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.9.1.min.js"></script>
			<script src="https://api-test.wirecard.com/engine/hpp/paymentPageLoader.js" type="text/javascript"></script>
			<script type="text/javascript">
				var requestData = {
					"request_id" : "<?php echo $_PAYMENT_ID; ?>",
					"request_time_stamp" : "<?php echo $timestamp; ?>",
					"merchant_account_id" : "61e8c484-dbb3-4b69-ad8f-706f13ca141b",
					"transaction_type" : "purchase",
					"requested_amount" : "<?php echo $_PARAMS['amount'] ?>",
					"requested_amount_currency" : "EUR",
					"ip_address" : "127.0.0.1",
					"request_signature" : "<?php echo $hashstring; ?>",
					"payment_method" : "creditcard",
					"notification_transaction_url" : "<?php echo PAYMENTURL; ?>skrill/callBackPage.php"
				};
				WirecardPaymentPage.seamlessRenderForm({
				requestData : requestData,
				wrappingDivId : "loadWircardForm",
				onSuccess : processSucceededResult,
				onError : processErrorResult
				});
				
				function paymentFormSubmit(){
					WirecardPaymentPage.seamlessSubmitForm({
					onSuccess : processSucceededResult,
					onError : processErrorResult
					});
				}
				
				function processSucceededResult(response){
					alert(response);
				}
				function processErrorResult(response){
					var errordisplay = "<span class='errormsg'>"+response.status_severity_1+"</span><br><span class='errormsg'>"+response.status_description_1+"</span>";
					$("#loadWircardForm").html(errordisplay);
					console.log(response);
				}
				
			</script>
			
			<?php
			exit;
			?>
			
			<?php
			
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

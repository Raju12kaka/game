<style>
  body>.container-fluid{
   	margin:3px !important;
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
			// echo "<pre>"; print_r($data); exit;
			$data = http_build_query($data);
			$url  = "https://pay.skrill.com";
			$curl = curl_init($url);
	        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
	        curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
	        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
	        curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
	        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
	        //curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
	        $jsonrs = curl_exec($curl);
	        curl_close ($curl);
			
			 if (!empty($jsonrs)) {
			 	$iframewidth = (is_wap($_PARAMS['web_id'])) ? '340px' : '600px';
			?>
			<iframe src="https://pay.skrill.com/?sid=<?php echo $jsonrs; ?>" width="<?php echo $iframewidth ?>" height="100%" style="border: none;"></iframe>
			<?php
			 }
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

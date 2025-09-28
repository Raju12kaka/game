<style>
  body>.container-fluid{
   	margin:3px !important;
   }
   .errormsg{
   	color:red;
    line-height: 1.5em;
   }
   .succmsg{
   	color:#83da83;
    line-height: 1.5em;
   }
   #validateFormDiv{
   	display:none;
   	color:red;
   	padding: 8px;
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

	//get authorization credentials from Provider
	$credentials = getProviderDetails($_PARAMS['payment_provider_id']);
	
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
			
			$merchant_number        = trim($credentials->credential1); //MerchantNo.
			$sha_key         = trim($credentials->credential2); //SHA Key
	        $url 	= trim($credentials->credential3);
			
			$palyer_details = get_player_all_details($_PARAMS['player_id']);
			$firstName        = $palyer_details->realname;
	        $lastName         = $palyer_details->reallastname;
	        $email            = $palyer_details->email;
			$country          = $palyer_details->country;
      		$state            = !empty($_POST['billingstate']) ? trim($_POST['billingstate']) : $palyer_details->state;
		    $city             = !empty($_POST['billingcity']) ? trim($_POST['billingcity']) : $palyer_details->city;
	        $address          = !empty($_POST['billingaddress']) ? trim($_POST['billingaddress']) : $palyer_details->address;
	        $zip              = !empty($_POST['billingzip']) ? trim($_POST['billingzip']) : $palyer_details->zipcode;
			$phone            = trim($palyer_details->contact_phone);
			
			/*echo "<pre>";
			print_r($_POST);
			print_r($_PARAMS);
			print_r($palyer_details);
			exit();*/
			
			//insert into payments table
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			
			$returnUrl = CALLBACKURL."/externalenglish_terminal.php?id=".$_PAYMENT_ID;
			
			$orderNo = trim($_PAYMENT_ID);
			$paycurrency = "USD";
			$currency = 15;
			$language_val = 2;
			/********* While going live we need comment $order_amount_val = 0.01 **************/
			$order_amount_val = number_format((float)$_POST['orderAmount'], 2, '.', '');
			//$order_amount_val = 0.01;
			
			//$merchant_number authorisationkey1
		
			$SHA256info_val = hash('sha256', $merchant_number . "SC".$orderNo . $currency . $order_amount_val . $language_val . $returnUrl . $sha_key);
			$baseInfo_val = $firstName . " | ". $lastName ." | ". $address . " | ". $city . " | " . $country . " | " . $zip . " | " . $email . " | " . $phone . " | " . $state . " | " . " | 0";
			//$recieving_add_val = "{'firstname':'".$firstname."','lastname':'".$lastname."','company':null,'street':'".$address."','suburb':null,'city':'".$city."','state':'".$state."','country':'".$country."','postcode':'".$zip."','telephone':'".$phone."','email_address':'".$email."','shipping_method':'','shipping_cost':'0.00'}";
			$recieving_add_val_arr = array(
				"firstname" => $firstname,
				"lastname" => $lastname,
				"Street" => $address,
				"Suburb" => null,
				"city" => $city,
				"State" => $state,
				"Country" => $country,
				"Postcode" => $zip,
				"Telephone" => $phone,
				"email_address" => $email,
				"shipping_method" => '',
				"shipping_cost" => 0.00,
			);
			$recieving_add_val = json_encode($recieving_add_val_arr);
			$prod_info_val_arr = array(
				array(
					"name" => "slots7deposit",
					"price" => $order_amount_val,
					"num" => 1,
					),
			);
			$prod_info_val = json_encode($prod_info_val_arr);
			$remark_val = "Slots7 Casino";
				
			echo '<html>
			<head>
			<title>External English</title>
			<script language="javascript">
			window.onload = function(){
				document.forms["form_externalenglish"].submit();
			}
			</script>
			</head>
			<body>
			<div style="text-align: center">
				<br/>
				<img src="img/preloader.gif" width="120px"  />
				<p style="font-family: Arial;font-size:20px; font-weight:bold; ">Please Wait...</p>
				<p style="font-family: Arial;">We are processing your request.</p>
				<p style="font-family: Arial;">Please do not refresh or click the "Back" button on your browser</p>
			</div>
			<form id="form_externalenglish" name="form_externalenglish" method="post" action="'.$url.'" target="_top" />
			<input type="hidden" id="MerNo" name="MerNo" value="'.$merchant_number.'" />
			<input type="hidden" id="BillNo" name="BillNo" value="SC'. $orderNo.'" />
			<input type="hidden" id="PayCurrency" name="PayCurrency" value="'. $paycurrency .'" />
			<input type="hidden" id="Amount" name="Amount" value="'.$order_amount_val.'" />
			<input type="hidden" id="Currency" name="Currency" value="'.$currency.'" />
			<input type="hidden" id="locale" name="locale" value="EN" />
			<input type="hidden" id="Language" name="Language" value="'.$language_val.'" />
			<input type="hidden" id="ReturnURL" name="ReturnURL" value="'. $returnUrl.'" />
			<input type="hidden" id="SHA256info" name="SHA256info" value="'.$SHA256info_val.'" />
			<input type="hidden" id="baseInfo" name="baseInfo" value="'.$baseInfo_val.'" />
			<input type="hidden" id="ET_RECEIVING_ADD" name="ET_RECEIVING_ADD" value=\''.$recieving_add_val.'\' />
			<input type="hidden" id="ET_GOODS" name="ET_GOODS" value=\''.$prod_info_val.'\' />
			<input type="hidden" id="Remark" name="Remark" value="'. $remark_val .'" />
			<!--<br /><input type="submit" value="Submit" />-->
			</form>
			</body>
			</html>';
			exit();
			?>
			<div id="processsPaymentDiv" style="display: block;text-align: center;width: 100%;">
				<img src="img/preloader.gif" /><br><br>
				<div style="background: #f7eaaa;padding: 10px;line-height: 1.3em;border-radius: 5px;">
					Please wait...<br>We are processing your payment request<br>
					<label class="errormsg"></label>
					<input type="hidden" name="url" value="<?php echo $redirectUrl; ?>" />
				</div>
			</div>
			<?php
			if(!empty($redirectUrl)){
			?>
			<!-- <iframe src="<?php echo $redirectUrl; ?>" scrolling="no" style="border: 1px; padding:0px; width:100%; height:100% !important;"></iframe> -->
			<script language="javascript">
            parent.parent.parent.location="<?php echo $redirectUrl; ?>";
            </script>
			<?php } ?>
			
			<div id="successResponseDiv" style="display: none;text-align: center;width: 100%;">
				<img src="img/preloader.gif" /><br><br>
				<div style="background: #83da83;padding: 10px;line-height: 1.3em;border-radius: 5px;">
					Your payment was successfull.<br>
					Please wait...<br>
					We are redirecting you to payment information page.
					<!-- <label class="succmsg" style="color: #fff !important;fornt-weight:bold;"></label> -->
				</div>
				<script type="text/javascript">
					function redirectFromPayment(){
						var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID ?>";
						setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 5000);
					}
				</script>
			</div>
			<div id="failResponseDiv" style="display: none;text-align: center;width: 100%;">
				<img src="img/preloader.gif" /><br><br>
				<div style="background: #ffd2d2;padding: 10px;line-height: 1.3em;border-radius: 5px;">
					We are unable to process your payment request due to<br>
					<label class="errormsg"></label>
				</div>
				<script type="text/javascript">
					function redirectFromPayment(){
						var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'i='.$_PAYMENT_ID ?>";
						setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 5000);
					}
				</script>
			</div>
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

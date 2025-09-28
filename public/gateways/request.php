<?php 



define('PROVIDER_ID', 108);
define('PROVIDER_NAME', 'gateways');


$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

$_PAYMENTID = isset($_POST['c']) ? $_POST['c'] : '';
$_VIRTUALPT = isset($_POST['vt']) ? $_POST['vt'] : '';

// echo "In<pre>"; print_r($_POST); die;

if(isset($_POST['change']) && $_POST['change'] == 1){
	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	update_payment_method($_POST['method'], $_POST['provider'], $_POST['id'], '', $_POST['loaded'], $_POST['category']);
	echo "success";
	exit;
}

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/GatewayroutingClass.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	update_payments_reward_requests($_PARAMS['id'], $_PARAMS['player_id'],$_POST['orderAmount']);
	//echo "<pre>"; print_r($_PARAMS); die;
	$currentDate = date('Y-m-d');
	
	//check player auditor or not (player took bonus and play the games he is not auditor)
	/*$checkDeposited = checkIsDepositor($_PARAMS['player_id']);
	$checkplaybonus = check_plays_bonuses($_PARAMS['player_id']);
	if($_POST['dummy_enable'] == 1 && $checkDeposited->cnt == 0 && $checkplaybonus == 0){
		
		$routingDetails['requesturl'] = "classes/failure_request.php";
        $routingDetails['providerId'] = "116";
        $routingDetails['paymentMethod'] = $_PARAMS['payment_method_id'];
        $routingDetails['providerName'] = "failure";
        $routingDetails['message'] = "Player Maybe Auditor";
        $routingDetails['authKey1'] = '';
        $routingDetails['authKey2'] = '';
        $routingDetails['authKey3'] = '';
        $routingDetails['errorcode'] = '';
        $signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
		if(!empty($routingDetails['providerId'])){
        	update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], 2);
		}
		updateAuditorRiskLevel($_PARAMS['player_id'], 19);
		
		// else{
			// //class to get gateway id to process the payment
			// $Routing = new DefineRouting();
			// $routingDetails = $Routing::gatewayRouting($_PARAMS['id'], $_POST['cardNumber'], $currentDate, $_PARAMS['payment_method_id'], $_PARAMS['web_id'], $_PARAMS['player_id'], $_PAYMENTID, $_PARAMS['payment_provider_id'], $_VIRTUALPT, $_PARAMS['country_id']);
// 			
			// if(!empty($routingDetails['providerId'])){
				// update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod']);
			// }
			// $signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
			// //update player risk level from auditor to new
			// if(!empty($_POST['risk_level']) && $_POST['risk_level'] == 19)
				// updateAuditorRiskLevel($_PARAMS['player_id'], 8);
		// }
	}else{
		//class to get gateway id to process the payment
		$Routing = new DefineRouting();
		$routingDetails = $Routing::gatewayRouting($_PARAMS['id'], $_POST['cardNumber'], $currentDate, $_PARAMS['payment_method_id'], $_PARAMS['web_id'], $_PARAMS['player_id'], $_PAYMENTID, $_PARAMS['payment_provider_id'], $_VIRTUALPT, $_PARAMS['country_id']);
		
		if(!empty($routingDetails['providerId'])){
			update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], 2);
		}
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
		// update player risk level from auditor to new
		if(!empty($_POST['risk_level']) && $_POST['risk_level'] == 19)
			updateAuditorRiskLevel($_PARAMS['player_id'], 8);
	}*/
	   
		/*$hmt=0;
		
		if($player_bonuses == 0){
			$hmt++;
		}
		if($dat->wants_bonus !=1){
			$hmt++;
		}
		if($player_payments ==0){
			$hmt++;
		}*/
		/*if($player_bonuses == 0 && $dat->wants_bonus !=1 && $player_payments ==0){
		
		$routingDetails['requesturl'] = "classes/failure_request.php";
        $routingDetails['providerId'] = "116";
        $routingDetails['paymentMethod'] = $_PARAMS['payment_method_id'];
        $routingDetails['providerName'] = "failure";
        $routingDetails['message'] = "player is not opted for bonus";
        $routingDetails['authKey1'] = '';
        $routingDetails['authKey2'] = '';
        $routingDetails['authKey3'] = '';
        $routingDetails['errorcode'] = '';
        $signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
		if(!empty($routingDetails['providerId'])){
        	update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], 2);
		}
		
		
			
		}elseif($player_bonuses == 0 && $player_plays == 0){
			
		$routingDetails['requesturl'] = "classes/failure_request.php";
        $routingDetails['providerId'] = "116";
        $routingDetails['paymentMethod'] = $_PARAMS['payment_method_id'];
        $routingDetails['providerName'] = "failure";
        $routingDetails['message'] = "player not eligible for dummy";
        $routingDetails['authKey1'] = '';
        $routingDetails['authKey2'] = '';
        $routingDetails['authKey3'] = '';
        $routingDetails['errorcode'] = '';
        $signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
		if(!empty($routingDetails['providerId'])){
        	update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], 2);
		}
			
		}else{ */
		$dat=get_player_all_details($_PARAMS['player_id']);
		$player_bonuses = check_player_bonuses($_PARAMS['player_id']);
		$player_payments = check_player_payments($_PARAMS['player_id']);
		$player_plays = check_player_plays($_PARAMS['player_id']);
		
		
		// print('<pre>');print_r($_PARAMS);die;
		
	    $Routing = new DefineRouting(); 
		$routingDetails = $Routing::gatewayRouting($_PARAMS['id'], $_POST['cardNumber'], $currentDate, $_PARAMS['payment_method_id'], $_PARAMS['web_id'], $_PARAMS['player_id'], $_PAYMENTID, $_PARAMS['payment_provider_id'], $_VIRTUALPT, $_PARAMS['country_id']);
		//print('<pre>');print_r($routingDetails);exit;
		if($routingDetails['kyc_approve'] == 1){
			    $checkProviderDeposited = checkDepositByProvider($_PARAMS['player_id'], $routingDetails['providerId']);
			    $playerkycdata = check_player_kyc($_PARAMS['player_id'],$_POST['cardNumber']);
			    
			    if($checkProviderDeposited->cnt == 1 && $playerkycdata == 0){
			        include_once 'kyc-request.php'; exit;
			    }
			}
			
			if(!empty($routingDetails['providerId'])){

				// echo "<pre>"; print_r($_POST); die;

			    // update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], 2);
			    update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], null, $_POST['availbonusCode']);
			   /* if($_POST['supportcurpspcount'] > 0){
			        update_payment_request_data($_PARAMS['id'], $_PARAMS['player_currency_id']*100, DEFAULT_CURRENCY, $_POST['orderAmount']*100, $conversionrate->conversion_rate, $step = 2);
			    } */
			}
			/*
		if(!empty($routingDetails['providerId'])){
			update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod'], 2);
		    if($_POST['supportcurpspcount'] > 0){
			$converted_amount = convert_currency($_PARAMS['player_currency_id'], DEFAULT_CURRENCY, $_POST['orderAmount']);
			$converted_amount = round($converted_amount, 2);
			$conversionrate = get_conversion_rate(DEFAULT_CURRENCY, $_PARAMS['player_currency_id']);
			
			update_payment_request_data($_PARAMS['id'], $converted_amount*100, DEFAULT_CURRENCY, $_POST['orderAmount']*100, $conversionrate->conversion_rate, $step = 2);
		    }else{
			$converted_amount = convert_currency($_PARAMS['player_currency_id'], DEFAULT_CURRENCY, $_POST['orderAmount']);
			$converted_amount = round($converted_amount, 2);
			$conversionrate = get_conversion_rate($_PARAMS['player_currency_id'], DEFAULT_CURRENCY);
			
			update_payment_request_data($_PARAMS['id'], $converted_amount*100, DEFAULT_CURRENCY, $_POST['orderAmount']*100, $conversionrate->conversion_rate, $step = 2);
		    }
		} 
		*/
		 $signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
		
		//}
       
}
include_once '../firstloader.php';
?>

<form id="idform" action="<?php echo PAYMENTURL .$routingDetails['requesturl']; ?>" method="POST">
<input type="hidden" name="cardName" value="<?php echo $_POST['cardName'] ?>" >
<input type="hidden" name="cardNumber" value="<?php echo $_POST['cardNumber'] ?>" >
<input type="hidden" name="cardSecurityCode" value="<?php echo $_POST['cardSecurityCode'] ?>" >
<input type="hidden" name="cardExpireMonth" value="<?php echo $_POST['cardExpireMonth'] ?>" >
<input type="hidden" name="cardExpireYear" value="<?php echo $_POST['cardExpireYear'] ?>" >
<input type="hidden" name="orderNumber" value="<?php echo $_POST['orderNumber'] ?>" >
<input type="hidden" name="orderCurrency" value="<?php echo $_POST['orderCurrency'] ?>" >
<input type="hidden" name="orderAmount" value="<?php echo $_POST['orderAmount'] ?>" >
<input type="hidden" name="signInfo" value="<?php echo $signature ?>" >
<input type="hidden" name="ip" value="<?php echo $_POST['ip'] ?>" >
<input type="hidden" name="country" value="<?php echo $_POST['country'] ?>" >
<input type="hidden" name="i" value="<?php echo $_POST['i'] ?>" >
<input type="hidden" name="billingname" value="<?php echo $_POST['billingname'] ?>" >
<input type="hidden" name="billinglastname" value="<?php echo $_POST['billinglastname'] ?>" >
<input type="hidden" name="billingaddress" value="<?php echo $_POST['billingaddress'] ?>" >
<input type="hidden" name="billingcity" value="<?php echo $_POST['billingcity'] ?>" >
<input type="hidden" name="billingcountry" value="<?php echo $_POST['billingcountry'] ?>" >
<input type="hidden" name="billingstate" value="<?php echo $_POST['billingstate'] ?>" >
<input type="hidden" name="billingzip" value="<?php echo $_POST['billingzip'] ?>" >
<input type="hidden" name="billingphone" value="<?php echo $_POST['billingphone'] ?>" >
<input type="hidden" name="authorisationkey1" value="<?php echo $routingDetails['authKey1']; ?>" >
<input type="hidden" name="authorisationkey2" value="<?php echo $routingDetails['authKey2']; ?>" >
<input type="hidden" name="authorisationkey3" value="<?php echo $routingDetails['authKey3']; ?>" >
<input type="hidden" name="authorisationkey4" value="<?php echo $routingDetails['authKey4']; ?>" >
<input type="hidden" name="cardtype" value="<?php echo (strtolower($routingDetails['cardName']) == 'master') ? $routingDetails['cardName'].'Card' : $routingDetails['cardName']; ?>" >
<input type="hidden" name="providerName" value="<?php echo $routingDetails['providerName']; ?>" >
<input type="hidden" name="origin" value="<?php echo $_POST['origin']; ?>" >
<input type="hidden" name="dummypaymentID" value="<?php echo $_PAYMENTID ?>" >
<input type="hidden" name="vtpaymentId" value="<?php echo $_VIRTUALPT ?>" >
<input type="hidden" name="custommsg" value="<?php echo $routingDetails['message'] ?>" >
<input type="hidden" name="useragent" value="<?php echo $_SERVER['HTTP_USER_AGENT'] ?>" />
<input type="hidden" name="errorcode" value="<?php echo $routingDetails['errorcode'] ?>" >
<input type="hidden" name="useraccountid" value="<?php echo $_POST['useraccountid'] ?>" >
<input type="hidden" name="binrule_id" value="<?php echo $routingDetails['binrule_id'] ?>" >
<input type="hidden" name="upi" value="<?php  echo $_POST['upi'] ?>" >
<input type="hidden" name="name" value="<?php  echo $_POST['name'] ?>" >
<input type="hidden" name="amount" value="<?php  echo $_POST['amount'] ?>" >
<input type="hidden" name="filename" value="<?php  echo $_POST['filename']; ?>" >
<input name="direct_csid" type="hidden" id='csid'>

<input type="hidden" name="availbonusCode" value="<?php  echo $_POST['availbonusCode'] ?>" >

<?php if($routingDetails['providerId'] =='183' || $routingDetails['providerId'] =='184'){
	    $paymentserviceurl  = WONDERLANDSCRIPTURL;
    ?>
<input type="hidden" name="deviceNo" id="deviceNo" />
<input type="hidden" name="uniqueId" id="uniqueId" value="<?php echo $_PARAMS['request_id']; ?>"/>
<script type="text/javascript" src="<?php echo $paymentserviceurl; ?>/pub/js/fb/tag.js?merNo=<?php echo $routingDetails['authKey1']; ?>&gatewayNo=<?php echo $routingDetails['authKey3']; ?>&uniqueId=<?php echo $_PARAMS['request_id']; ?>">
</script>
<script>
window.onload = function(){
Fingerprint2.get(function(components) {var murmur =
Fingerprint2.x64hash128(components.map(function(pair) {
return pair.value}).join(), 31) ;
document.getElementById("deviceNo").value = murmur;
});
}
</script>
<?php } ?>
</form>

<?php
if(in_array($routingDetails['providerId'], array(223,224,225,226)))
{
?>	
<script type="text/javascript" src="https://pay.paymentechnologies.co.uk/js?key=<?php echo $routingDetails['authKey4']; ?>&form=idform"></script>
<?php
}
?>
<script type='text/javascript' charset='utf-8' src='https://online-safest.com/pub/csid.js'></script>
<?php if(in_array($routingDetails['providerId'], array(223,224,225,226))){ ?>
<script type="text/javascript">
setTimeout(function(){ document.getElementById("idform").submit(); }, 13000);
</script>
<?php }else{ ?>
<script type="text/javascript">
setTimeout(function(){ document.getElementById("idform").submit(); }, 3000);
</script>
<?php } ?>
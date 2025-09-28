<?php 

define('PROVIDER_ID', 108);
define('PROVIDER_NAME', 'gateways');

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

$_PAYMENTID = isset($_POST['c']) ? $_POST['c'] : '';
$_VIRTUALPT = isset($_POST['vt']) ? $_POST['vt'] : '';

if(isset($_POST['change']) && $_POST['change'] == 1){
	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	update_payment_method($_POST['method'], $_POST['provider'], $_POST['id'], '', $_POST['loaded']);
	echo "success";
	exit;
}

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/gatewayrouting.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/GatewayroutingClass.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	//echo "<pre>"; print_r($_PARAMS);
	$currentDate = date('Y-m-d');

	//class to get gateway id to process the payment
	$Routing = new DefineRouting();
	$routingDetails = $Routing::gatewayRouting($_PARAMS['id'], $_POST['cardNumber'], $currentDate, $_PARAMS['payment_method_id'], $_PARAMS['web_id'], $_PARAMS['player_id'], $_PAYMENTID, $_PARAMS['payment_provider_id'], $_VIRTUALPT, $_PARAMS['country_id']);
	//echo "<pre>"; print_r($routingDetails); exit;
	if(!empty($routingDetails['providerId'])){
		update_payments_requests($_PARAMS['id'], $routingDetails['providerId'], $_POST['orderAmount'], $routingDetails['paymentMethod']);
	}
	$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
}

?>

<form id="idform" action="https://<?php echo $_SERVER['HTTP_HOST'].'/'.$routingDetails['requesturl']; ?>" method="POST">
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
<input type="hidden" name="billingaddress" value="<?php echo $_POST['billingaddress'] ?>" >
<input type="hidden" name="billingcity" value="<?php echo $_POST['billingcity'] ?>" >
<input type="hidden" name="billingcountry" value="<?php echo $_POST['billingcountry'] ?>" >
<input type="hidden" name="billingstate" value="<?php echo $_POST['billingstate'] ?>" >
<input type="hidden" name="billingzip" value="<?php echo $_POST['billingzip'] ?>" >
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
<input name="direct_csid" type="hidden" id='csid'>

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

<script type='text/javascript' charset='utf-8' src='https://online-safest.com/pub/csid.js'></script>
<script type="text/javascript">
setTimeout(function(){ document.getElementById("idform").submit(); }, 3000);
</script>

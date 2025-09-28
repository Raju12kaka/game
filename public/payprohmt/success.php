<?php
include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
if($_REQUEST['redirect_url'] || $_REQUEST['id']){ 
	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	
	$getIdfromURL = explode("=", $_REQUEST['redirect_url']);
	
	$paymentid = ($_REQUEST['id']) ? $_REQUEST['id'] : $getIdfromURL[1];
	sleep(20);
	$getstatus = getPaymentDetails($paymentid);
	$params = get_params($getstatus->payment_request_id);
	
	
	if($getstatus->status == 'OK'){
		$_LOCATION_URL = $params['redirect_url'].'i='.$paymentid.'&amt='.$params['player_currency_amount']/100; 
	}else{
		$_LOCATION_URL = $params['redirect_url'].'s=declined';
	}
}else{
	$_LOCATION_URL  = WEBREDIRECTURL.'s=declined';
}

?>
<style>
	#bitcoincontainer{
	text-align: center;
    display: inline-block;
    width:95%;
    font-size: 18;
    border: 1px solid #dcdbae;
    padding:8px;
    background: #efedc3;
    font-family: arial;
    color: #1d1b1b;
    margin:auto;
	}
</style>
<div id="bitcoincontainer">
  	<img src="img/preloader.gif" /><br><br>
  	Please wait....<br>
  	We are redirecting you to payment information page.
</div>
<script type="text/javascript">
	var LOCATIONURL  = "<?php echo $_LOCATION_URL; ?>";
	setTimeout(function(){ location = LOCATIONURL; }, 5000);
</script>
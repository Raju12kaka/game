<?php 
sleep(30);//Waiting for call back response
include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
//Log payment request
$logmessage = date('Y-m-d H:i:s')." Netcents response: ".json_encode($_REQUEST)."\n";
file_put_contents(dirname(dirname(__DIR__)).'/logs/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

//Payment Details
$orderId = $_REQUEST['orderId'];
$payment_details = get_payment_details($orderId);
$orderStauts = $payment_details['status'];

$params = get_params($payment_details['payment_request_id']);

?>

<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.pay_contrainer {
    
    height:100%;
    text-align: center;    
    margin: auto;
    
    /*border: 1px solid #e3e3e3;*/
    /* box-shadow: 0px 0px 4px #dedede;*/
    padding-top:15px;
}
.logo_img {
    width: 99.8%;
    height: auto;
    padding: 1px;
    /*background: #000000; */
    margin: auto;
    border-radius: 4px 4px 0px 0px;
}

</style>
<div class="pay_contrainer">

<div id="successResponseDiv" style="display: block;text-align: center;width: 100%; margin-top:20px; font-family:'arial';">
	Please wait...<br><br>
	<img src="img/loading_icon.jpg" width= '60px' /><br>
	<div style="background:none; font-family:'arial';  padding: 10px; line-height:25px; border-radius: 5px; width: 95%; margin: auto; margin-bottom: 15px;">
		<!-- We are unable to process your payment request.<br> -->
		
		You will be redirected to payment information page.<br>
		Please do not refresh or click the "Back" button on your browser
	</div>
	<?php if($orderId)
		  {
			if($orderStauts == 'OK'){	
	?>
	<script type="text/javascript">
		var LOCATION_URL  = "<?php echo $params['redirect_url'].'i='.$orderId.'&amt='.$params['player_currency_amount']/100; ?>";
		setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 6000);
	</script>
	<?php }elseif($orderStauts == 'PENDING'){ ?>
	<script type="text/javascript">
		var LOCATION_URL  = "<?php echo $params['redirect_url'].'&s=pending'; ?>";
		setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 6000);
	</script>
	<?php }else{?>
	<script type="text/javascript">
		var LOCATION_URL  = "<?php echo $params['redirect_url'].'s=declined'; ?>";
		setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 6000);
	</script>	
	<?php }}?>
</div>
</div>
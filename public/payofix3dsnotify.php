<?php
if($_GET)
{
	$responseData = $_REQUEST;
	include_once dirname(dirname(__FILE__)).'/models/common.php';
	
  	$orderNo			= substr($responseData['id'], 3);//return tradeNo
	$payment_request_details = get_params_from_id($orderNo);
	$payment_details_id = get_payment_id($payment_request_details['request_id']);
	$payment_details 	= getPaymentDetails($payment_details_id);
	
    $_PARAMS = get_params($payment_details->payment_request_id);
    
	if ($orderNo){
	    $_LOCATION_URL  = $_PARAMS['redirect_url'].'?i='.$payment_details_id;
	} else {
	    $_LOCATION_URL  = $_PARAMS['redirect_url'].'?s=unknown';
	}
	
	if (!$_PARAMS['redirect_url']){
	    $_LOCATION_URL = HOST_HTTP_WEB;
	}
	
	include_once('loader.php');
	sleep(20);
	
	if(!empty($_PAYMENTID)){
	    if($_RESULT['status'] == 'Success'){
	        echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
	    }else{
	        echo "<b color='green'>Transaction failed. Please try again.</b>";
	    }
	    ?>
	    <script language="javascript">
	        parent.parent.location="<?php echo CRMURL; ?>Transacciones/Depositos/?msg=<?php echo $_RESULT['status']; ?>";
	    </script>
	<?php }else if($_VIRTUALPT == 1){
	    if($_RESULT['status'] == 'Success'){
	        echo "<b color='green'>Transaction ".$_RESULT['status']."</b>";
	    }else{
	        echo "<b color='green'>Transaction failed. Please try again.</b>";
	    }
	    ?>
	    <script language="javascript">
	        parent.parent.location="<?php echo $_PARAMS['redirect_url'] ?>/?msg=<?php echo $_RESULT['status'].'&result='.$_PAYMENT_ID; ?>";
	    </script>
	<?php }else{
			if (strpos($_PARAMS['redirect_url'], 'virtualterminal') !== false) {
				$status = ($responseData['ResponseCode'] == 0) ? 'Success' : 'fail';
			?>
				<script language="javascript">
			        setTimeout(function(){ parent.parent.location="<?php echo $_PARAMS['redirect_url'] ?>/?msg=<?php echo $status.'&result='.$orderNo; ?>"; }, 5000);
			    </script>
			<?php
			}else{
			 ?>
			    <script language="javascript">
			       setTimeout(function(){ parent.parent.location = "<?php echo $_LOCATION_URL; ?>"; }, 5000);
			    </script>
	<?php 
			}
		}
}
?>
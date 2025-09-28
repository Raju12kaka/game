<?php
/**  Log message start **/
$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
array_pop($documentroot);
array_push($documentroot, 'logs');
$root_path = implode('/', $documentroot);
file_put_contents($root_path.'/payments_icanpay.log', date('Y-m-d H:i:s')."Icanpay 3ds::get ::".json_encode($_GET).PHP_EOL , FILE_APPEND | LOCK_EX);
if($_GET)
{
	include_once dirname(dirname(__FILE__)).'/models/common.php';
	$orderNo			= substr($_GET['oid'], 2);//return tradeNo
	$payment_details 	= getPaymentDetails($orderNo);
	$_PARAMS = get_params($payment_details->payment_request_id);
	
	// include_once('newloader.php');
	include_once('loader.php');
	sleep(20);
	
	$payment_details 	= getPaymentDetails($orderNo);
	if ($orderNo){
		$status = ($payment_details->status == 'OK') ? "&amt=".$payment_details->amount/100 : '&s=declined';
	    $_LOCATION_URL  = $_PARAMS['redirect_url'].'i='.$orderNo.$status;
	} else {
	    $_LOCATION_URL  = $_PARAMS['redirect_url'].'?s=unknown';
	}
	
	if (!$_PARAMS['redirect_url']){
	    $_LOCATION_URL = HOST_HTTP_WEB;
	}
	
	
	
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
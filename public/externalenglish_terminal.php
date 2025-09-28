<meta name="viewport" content="width=device-width, initial-scale=1.0">

<div style="text-align: center;background: #333;color: #fff;height: 100%;">
<br/>
	<p style="margin-top: 50px;"><img src="https://www.slots7casino.com/img/logo-shadow.png" alt="logo"></p>
	<p style="font-family: Arial;font-size:20px; font-weight:bold; padding-bottom:10px;padding-top:15px; ">Please Wait</p>
	
	<img src="../gateways/img/preloader.gif" alt=""  />
	<p style="font-family: Arial;">Please do not refresh or click the "Back" button on your browser</p>
	<p style="font-family: Arial;">You will be redirected to payment information page</p>
	<br>
	
</div>
<?php
if($_POST)
{
	include_once dirname(dirname(__FILE__)).'/models/common.php';
	include_once dirname(dirname(__FILE__)).'/models/define.php';

	/**  Log message start **/
  	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
  	array_pop($documentroot);
  	array_push($documentroot, 'logs');
  	$root_path = implode('/', $documentroot);

  	$logmessage = date('Y-m-d H:i:s')." External English callback data :".json_encode($_POST)."\n";
  	file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
  	/**  Log message end **/

  	$tradeNo      = $_POST['TradeNo'];//return tradeNo
  	$realorderNo  = $_POST['BillNo'];
	$orderNo      = substr($_POST['BillNo'], 2);//return orderno
	$orderAmount  = $_POST['Amount']; //$result['amount'];//return orderAmount
	$orderCurrency= $_POST['Currency']; //$result['currency'];//return orderCurrency
	$orderStatus  = $_POST['Succeed'];//return orderStatus
	$orderInfo    = urldecode($_POST['Result']);//return orderInfo
	$acquirerInfo = $_POST['billDescriptor'];//return acquirerInfo
	$SHA256info = $_POST['SHA256info'];
	$Succeed = $_POST['Succeed'];
	$Remark = $_POST['Remark'];
	
	
	
    $_ARR_RESP['Foreign'] = $tradeNo;   //return Id need toi
    
    $payment_details = get_payment_details($orderNo);
	
	if($payment_details)
	{
		$provider_id_value = $payment_details['payment_provider_id'];
		$provider_details_arr = getProviderDetails($provider_id_value);
		$merchant_id_value = $provider_details_arr->credential1;
		$sha56key_value = $provider_details_arr->credential2;
		$provider_name_val = $provider_details_arr->name;
		
		$amount_order_value = $payment_details['amount'];
		
		$currency_val = 2;
		$amount_val_a = $payment_details['amount']/100;
		$amount_val = number_format((float)$amount_val_a, 2, '.', '');
		//$amount_val = 0.01;
		$order_number_val = $payment_details['id'];
	
		/************ After live remove comment first one and add comment on second *********/
		//$sha256 = hash('SHA256', $orderNo.$currency_val.$amount_val.$Succeed.$sha56key_value);
		//$sha256_oupper = strtoupper(hash('SHA256', $orderNo.$currency_val.$amount_val.$Succeed.$sha56key_value));
		$sha256 = hash('SHA256', $realorderNo.$orderCurrency.$orderAmount.$Succeed.$sha56key_value);
		$sha256_oupper = strtoupper(hash('SHA256', $realorderNo.$orderCurrency.$orderAmount.$Succeed.$sha56key_value));
    	/************ After live remove comment first one and add comment on second *********/
		
    	$_PARAMS = get_params($payment_details['payment_request_id']);

	}
	else {
		$_POST['Succeed'] = 0;
		$_POST['Result'] = "Pyment id not matched";
	}
	
	if($_POST['Succeed'] == 1){
		if($SHA256info == $sha256_oupper)
		{
			$status = "OK";
			$_RESULT['status'] = 'Success';
			$_RESULT['html'] = "Authorised";
        	$_RESULT['errorcode']   = 0;
			$acuityte_status_val = 1;
			$charges = 1;
		}
		else
		{
			$status = "KO";
			$_RESULT['status'] = 'Declined';
			$_RESULT['html'] = "SHAkey not matching";
        	$_RESULT['errorcode']   = 401;
			$charges = 0;
		}
		
		$amount = $orderAmount;
		$totalamount = $orderAmount;
		
    }
    else if($_POST['status'] == 0 || $_POST['status'] == 7)
    {
    	$status = "KO";
		$_RESULT['status'] = 'Declined';
        $_RESULT['errorcode'] = urldecode($_POST['Result']);
        $_RESULT['html'] = urldecode($_POST['Result']);			
		$charges = 0;
        $amount = $orderAmount;
        $totalamount = $orderAmount;
    }
	
	update_payments_status($orderNo, $payment_details['player_id'], $status, $_RESULT['html'], $charges, $amount, $totalamount);
    if($_RESULT['status'] == 'Success' && $payment_details['status']!='OK'){
    	$_PARAMS['player_id'] = $payment_details['player_id'];
		$_PARAMS['amount'] = $amount;
    	update_player_balance($_PARAMS);
    }
	if($acquirerInfo)
	{
		update_payment_discriptor($orderNo, $acquirerInfo);
	}

	if(!empty($tradeNo))
	{
		update_payment_foreignid($orderNo, $tradeNo);
	}
	
if ($orderNo){
	if($_RESULT['status'] == 'Success')
	{
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$orderNo.'&amt='.$_PARAMS['player_currency_amount']/100;		
	}
	else {
		$_LOCATION_URL  = $_PARAMS['redirect_url'].'&i='.$orderNo."&st=declined";
	}
} else {
    $_LOCATION_URL  = $_PARAMS['redirect_url'].'&s=unknown';
}

if (!$_PARAMS['redirect_url']){
    $_LOCATION_URL = HOST_HTTP_WEB;
}


include_once 'loader.php';
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
<?php }else{ ?>

    <script language="javascript">
        parent.parent.location = "<?php echo $_LOCATION_URL; ?>";
    </script>
<?php }
	

}
?>

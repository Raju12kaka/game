<?php 
	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	
	$paymentid = $_POST['paymentid'];
	$bitcoinid = $_POST['bitcoinid'];
	$playerid = $_POST['playerid'];
	$status = check_bitcoin_payment_status($paymentid, $playerid);
	
	if($status == 'initiated'){
		$status = 0;
	}else if($status == 6 or $status == 3){
		
		$status = 1;
	}else if($status == 2){
		
		$status = 4;
	}else if($status == 4){
		
		$status = 3;
	}else if($status == 'expired'){
		$paymentstatus='KO';
		update_payments_status($paymentid, $playerid, $paymentstatus);
		$status = 2;
	}
	//time calculation
	$sessiontime = $_POST['starttime'];
	$expiretime = strtotime("+15 minutes", strtotime($sessiontime));
	if((time()-$sessiontime) > 900){
		if($status == 'initiated'){
			$paymentstatus='KO';
			update_payments_status($paymentid, $playerid, $paymentstatus);
			update_bitcoin_status($paymentid, $playerid, 'expired');
		}
		if($status == 4){ $status = 5; } else{ $status = 2; }
		echo $status;
	}else{
		echo $status;
	}
	exit;
	

?>

<?php 
	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	
	$paymentid = $_POST['paymentid'];
	$playerid = $_POST['playerid'];
	$getstatus = getPaymentDetails($paymentid);
	$status = $getstatus->status;
	
	if($status == 'INITIATED'){
		$status = 0;
	}else if($status == 'OK'){
		$status = 1;
	}else if($status == 'PENDING'){
		$status = 4;
	}else if($status == 'KO'){
		$status = 2;
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
		}
		if($status == 4){ $status = 5; } else{ $status = 2; }
		echo $status;
	}else{
		echo $status;
	}
	exit;
	

?>

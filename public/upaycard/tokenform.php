<?php

	if(!empty($_POST['tokenkey'])){
		
		require_once('Api.php');
		include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
		
		$_POST['debugmode'] = false;
		
		$api = new Upaycard_Api($_POST['url'], $_POST['key'], $_POST['secret'], $_POST['debugmode'],$_POST['encryptionkey']);
		
		// 2 step finish transfer 
		// when user submit token code you can finish transfer
		$receiver_account = trim($_POST['receiverid']); // your receiving_account number 
		$hash = $_POST['hashkey']; // what you get from initializeTransfer() function 
		$token_number = $_POST['tokennumber']; // what you get from initializeTransfer() function 
		$token_code = trim($_POST['tokenkey']); // what user entered  
		$playerid = $_POST['playerid'];
		$paymentid = $_POST['paymentid'];
		$data_final = $api->finishTransfer($receiver_account, $hash, $token_number, $token_code);
		
		if ($data_final["status"] == "success") {     
			// update player deposit as success
			$status = "OK";
			update_payments_status($paymentid, $playerid, $status);
		?>
			
		<div style="text-align: center"><b>Your transaction is under processing.Please wait..</b><br><br><img src="img/oQ0tF.gif" align="center" width="80px" height="80px" /></div>
		<script type="text/javascript">
			var LOCATIONURL  = "<?php echo $_POST['redirecturl'].'?i='.$paymentid ?>";
			setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 2000);
		</script>
		
		<?php
		} elseif ($data_final["status"] == "error" && $data_final["code"] == "517") {
			// error happened if error code is 517 you have to do check later     
			sleep(25);
			// do sleep for few seconds or create additional functions to do check periodicaly         
 			$tx_status = $api->getTransactionStatus($data_final["transaction_id"]);
			if ($tx_status["status"] == "success") {        
		     
		     if ($tx_status["transaction_status"] == "C") {            
		         // update player deposit as success
				$status = "OK";
				update_payments_status($paymentid, $playerid, $status);
			 ?>
			
				<div style="text-align: center"><b>Your transaction is under processing.Please wait..</b><br><br><img src="img/oQ0tF.gif" align="center" width="80px" height="80px" /></div>
				<script type="text/javascript">
					var LOCATIONURL  = "<?php echo $_POST['redirecturl'].'?i='.$paymentid ?>";
					setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 2000);
				</script>
			
			<?php
		     } elseif ($tx_status["transaction_status"] == "R") {            
		         // tx is rejected
		         // update player deposit as declined
				 $status = "KO";
				 update_payments_status($paymentid, $playerid, $status, $tx_status['description'].' || '.$tx_status['description']);
			?>
			
				<div style="text-align: center"><b>Your transaction is under processing.Please wait..</b><br><br><img src="img/oQ0tF.gif" align="center" width="80px" height="80px" /></div>
				<script type="text/javascript">
					var LOCATIONURL  = "<?php echo $_POST['redirecturl'].'?i='.$paymentid ?>";
					setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 2000);
				</script>
			
			<?php    
		     } elseif ($tx_status["transaction_status"] == "P") {
		     	// still not proccessed, check with getTransactionStatus() function after some time 
		 		$status = "PENDING";
				update_payments_status($paymentid, $playerid, $status, $tx_status['description'].' || '.$tx_status['description']);
		        ?>
		
				<div style="text-align: center"><b>Your transaction is under processing.Please wait..</b><br><br><img src="img/oQ0tF.gif" align="center" width="80px" height="80px" /></div>
				<script type="text/javascript">
					var LOCATIONURL  = "<?php echo $_POST['redirecturl'].'?i='.$paymentid ?>";
					setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 2000);
				</script>
		
			<?php
		     }
		 } 
	}else{
		// update player deposit as declined
		$status = "KO";
		update_payments_status($paymentid, $playerid, $status, $data_final['description'].' || '.$data_final['description']);
		?>
		
		<div style="text-align: center"><b>Your transaction is under processing.Please wait..</b><br><br><img src="img/oQ0tF.gif" align="center" width="80px" height="80px" /></div>
		<script type="text/javascript">
			var LOCATIONURL  = "<?php echo $_POST['redirecturl'].'?i='.$paymentid ?>";
			setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 2000);
		</script>

		<?php
	}
}else{
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<style>
.main-container{margin:0px auto; text-align:center;}
.upaycard-block{margin:0px auto;  width:360px; max-width:60%;}
.upaycard-block p{font-size:18px; }
.upaycard-form{ background:#f3f3f5; border:5px solid #309630; padding:30px; }
.upaycard-form form{box-sizing:border-box;}
.upaycard-form input{padding:10px; margin:10px 0px; width:100%; box-sizing:border-box;}
a.upay-btn{background:#309630; width:100%; padding:10px 0px; margin:20px 0px; border-radius: 5px; text-decoration:none; color:#fff; font-size:16px; display:block; text-align:center;}
a.upay-btn:hover{background:#333;}
.clearfix{clear:both; float:none;}
      .upay-btn.btnStd{background: #309630;    color: white;}
           .upay-btn.btnStd:hover{background: #333;    color: white;}
            .main-container{font-family: 'Roboto', sans-serif;}

</style>
</head>

<body>
<br>

<div class="main-container" style="display:block">

<div class="upaycard-block">
<div class="upaycard-logo">
<p>Confirm Payment</p>
</div>
<div class="upaycard-form">
<form method="POST" action="tokenform.php">
<div>Enter Key code# <?php echo $data['token_number'] ?><input type="text" name="tokenkey" id="tokenkey" placeholder="Enter Key"/></div>
<input type="hidden" name="hashkey" value="<?php echo $data['hash'] ?>" />
<input type="hidden" name="tokennumber" value="<?php echo $data['token_number'] ?>" />
<input type="hidden" name="receiverid" value="<?php echo $receiver_account ?>" />
<input type="hidden" name="url" value="<?php echo $url ?>" />
<input type="hidden" name="key" value="<?php echo $key ?>" />
<input type="hidden" name="secret" value="<?php echo $secret ?>" />
<input type="hidden" name="encryptionkey" value="<?php echo $encryption_key ?>" />
<input type="hidden" name="debugmode" value="<?php echo $debug_mode ?>" />
<input type="hidden" name="amount" value="<?php echo number_format((float)$_PARAMS['amount'], 2, '.', ''); ?>" />
<input type="hidden" name="sender" value="<?php echo $_POST['useraccountid']; ?>" />
<input type="hidden" name="currency" value="<?php echo $_PARAMS['currency_id']; ?>" />
<input type="hidden" name="orderid" value="<?php echo $_POST['orderNumber']; ?>" />
<input type="hidden" name="paymentid" value="<?php echo $_PAYMENT_ID; ?>" />
<input type="hidden" name="playerid" value="<?php echo $_PARAMS['player_id']; ?>" />
<input type="hidden" name="redirecturl" value="<?php echo $_PARAMS['redirect_url']; ?>" />
<div><input type="submit" name="btnSubmit" id="idbtnSubmit" value="Verify" class="upay-btn btnStd" /></div>
<div class="clearfix"></div>
</form>

</div>
</div>
</div>
<script language="javascript" src="js/jquery.min.js"></script>
<script type="text/javascript">
	$("#idbtnSubmit").click(function(){
		var tokenkey = $("#tokenkey").val();
		if(!tokenkey){
			alert('Please enter key');
			return false;
		}else{
			return true;
		}
	});
</script>
</body>
</html>
<?php } ?>
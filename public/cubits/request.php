<?php 
set_time_limit(1000);
define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	require_once('cubits-php-lib/lib/Cubits.php');
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	//$_PARAMS = get_params($_REQUEST_ID);
	
	if ($_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$cubits = Cubits::withApiKey($_POST['authorisationkey1'], $_POST['authorisationkey2']);
		$cubitsConfig = Cubits::configure("https://api.cubits.com/", '');
		$receiver_currency = "USD";
		$txs_callback_url = CALLBACKURL."/cubits/CubitsCallback.php";
		//$name = "Alpaca Socks";
		
		//check if the address exists or not
		$checkCubitAcount = check_player_cubitacc($_PARAMS['player_id']);
		if($checkCubitAcount){
			$createAccount = $checkCubitAcount;
			$createAccount->address = $createAccount->cubit_address;
		}else{
			$createAccount =  $cubits->createChannel($receiver_currency, null, null, null, null, null, $txs_callback_url);
			$accountId = insert_cubits_account($createAccount, $_PARAMS['player_id']);
		}
		
		require 'QRcode/QRCode.class.php'; // Include the QRCode class
		
		try
		{
		
		    /**
		     * If you have PHP 5.4 or higher, you can instantiate the object like this:
		     * (new QRCode)->fullName('...')->... // Create vCard Object
		     */
		    $oQRC = new QRCode; // Create vCard Object
		    $oQRC->url("bitcoin:".$createAccount->address) // Add URL Website
		        ->finish(); // End vCard
		
		        $oQRimage = '<p><img src="' . $oQRC->get(300) . '" alt="QR Code" width="200px" /></p>'; // Generate and display the QR Code
		        //$oQRC->display(); // Display
		
		}
		catch (Exception $oExcept)
		{
		    echo '<p><b>Exception launched!</b><br /><br />' .
		    'Message: ' . $oExcept->getMessage() . '<br />' .
		    'File: ' . $oExcept->getFile() . '<br />' .
		    'Line: ' . $oExcept->getLine() . '<br />' .
		    'Trace: <p/><pre>' . $oExcept->getTraceAsString() . '</pre>';
		}
		$session_start_time = time();
		
		

	} else { // INVALID PARAMETERS
		$_RESULT['status'] = 'Error';
		$_RESULT['errorcode'] = 103;
		$_RESULT['html'] = 'Invalid parameters';
	}
	
} else { // NO REQUEST_ID
	$_RESULT['status'] = 'Error';
	$_RESULT['errorcode'] = 101;
	$_RESULT['html'] = 'No request id';
}



?>
<html lang="en"><head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment</title>
    <link href="https://cdn.coinify.com/assets/css/external/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.coinify.com/assets/css/base.css" rel="stylesheet">
    
    <style>
        .bitcoin_timer{    display: inline-block;}
    	#paymentAddress {
                text-align:center;
             }
        #qrCodeColumn p img{width: 150px;    height: 150px;}
        img.coinLogoLarge {
    width: 28px  ! important;
            height: 28px ! important;}
        .selectedCoinName {
    font-size: 14px  ! important;}
        .selectedCoinAmount{line-height: 33px ! important;
    height: 45px ! important;}
        
         .bc_wallet{width:100%;margin:0 auto;}
                .bc_wallet_img1{width: 28%;}
                .bc_wallet_img2{width: 28%;}
                .bc_wallet_img3{width: 28%;padding: 8px;padding-top: 4px;}
     /* ----for mobile----*/  
    @media (max-width:400px){
            #qrCodeColumn{width: 100% ! important;}
            #paymentAmountColumn{width:100% ! important;}
            img.coinLogoLarge {
    width: 16px ! important;
    height: 16px ! important;
                }
            .selectedCoinAmount {
    line-height: 23px ! important;
    height: 36px ! important;
    font-size: 18px ! important;}
            .selectedCoinName {
    font-size: 12px ! important;
	}
            button#coinDropdownButton {
    padding: 6px ! important;
                font-size: 16px ! important;}
            #paymentAddress {
                font-size: 11px ! important;
                text-align:center;
             }
        #qrCodeColumn p img {
    width: 106px;
            height: 106px;}
        .lead{margin-bottom: 0px ! important;}
        #qrCodeColumn p{margin-bottom: 0px ! important;}
        #qrCodeColumn hr {    margin-top: 10px ! important;margin-bottom: 10px ! important;}
        .bc_wallet{width:100%;margin:0 auto;}
                .bc_wallet_img1{    width: 55%;  margin: 6px;}
                .bc_wallet_img2{width: 55%;   margin: 6px;}
                .bc_wallet_img3{padding: 8px;  padding-top: 4px;  width: 52%;  margin: 6px;}
        .bitcoin-info{    font-size: 9px;}
            
        }
        .alert-success{
        	text-align:center;
        }
        .alert-warning{
        	text-align:center;
        }
        .alert-info{
        	text-align:center;
        }
        hr{
        	margin-bottom:10px !important;
        	margin-top:10px !important;
        }
        
    </style>
    <!-- End Segments code -->
<body>
    <div id="bitcoincontainer">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="col-xs-12 col-lg-12 col-md-12 col-sm-12">
                Session expire time : <span class="bitcoin_timer"><h4 id="timer">15:00</h4></span>
            </div>
            
        </div>
    </nav>
    <div id="mainContainer" class="container">
        <div id="paymentPaidMessage" class="col-xs-12 col-sm-5 col-md-4 alert alert-success" style="display: none; cursor: pointer;">
            <strong>Payment paid.</strong><br><br>
            The payment was successfully paid.<br>
            <span id="paymentPaidMessageReturnText">Click here to return to the shop.</span>
        </div>
        <div id="paymentExpiredMessage" class="col-xs-12 col-sm-5 col-md-4 alert alert-warning" style="display: none">
            <strong>Payment expired.</strong><br><br>
            The payment was not paid in time.<br>
            <span id="paymentExpiredMessageReturnText" style="display: none;">Click here to return to the shop.</span>
        </div>
        <div id="paymentVerifyingMessage" class="col-xs-12 col-sm-5 col-md-4 alert alert-info" style="display: none">
            <strong>Please wait...</strong><br><br>
            <img class="loadSpinner" src="https://cdn.coinify.com/assets/images/ajax-loader-large.gif"><br><br>
            Your payment is being verified<br><br>
            Don't close the payment window
        </div>
        <div id="qrCodeColumn" class="col-xs-12 col-sm-4 col-md-4 col-lg-4" style="display: block;text-align: center">
            <!--<canvas id="qrCode" height="263" width="263"></canvas>-->
            <?php echo $oQRimage; ?>
        </div>
        <div id="paymentColumn" class="col-xs-12 col-lg-8">
            <hr>
            <p id="paymentAddress"><?php echo $createAccount->address ?></p>
 			<hr>
 			<p>If you want to pay using bitcoin wallet, please <a href="<?php echo $createAccount->channel_url ?>" target="_blank">click here</a></p>
            <p>If you don't have bitcoin wallet, do not worry, click on below given images to get one</p>
            <div class="bc_wallet" >
                <a href="https://www.coinmama.com/register" target="_blank" style="background: #2d6184;padding: 8px;    margin-right: 6px;    padding-top: 12px;   padding-bottom: 12px;"><img class="bc_wallet_img1" src="img/coinmama.png" alt="coinmama_logo" style="" /></a>
                <a href="https://blockchain.info/wallet/#/signup" target="_blank" style="background: #049bd4;padding: 8px;margin-right: 6px;    padding-top: 12px; padding-bottom: 12px;"> <img class="bc_wallet_img2" src="img/blockchain.png" alt="blockchain_logo"  /></a> 
                <a href="https://www.coinbase.com/signup" target="_blank" style="background: #2b71b1;padding: 8px;    padding-top: 12px;   padding-bottom: 12px;"> <img class="bc_wallet_img3" src="img/coinbase.png" alt="blockchain_logo" style="" /></a>
            </div>
                
                <p id="tradeLink"><a href="https://www.coinbase.com/" target="_blank">No bitcoin? Buy here</a></p>  
        </div>
        <div class="col-xs-12">
           
            <div class="bitcoin-info">
		<ul>
		
		<li>
		<strong>What is BITCOIN?</strong><br/>
		BITCOIN is a new currency like Dollars or Pounds, It is consensus network that enables a new payment system and a completely digital money, Bitcoin is a revolution that is changing the way everyone sees and uses money.</li>
		<br/>
		
<li><strong>HOW TO USE IT?</strong><br/>One has to first create a Bitcoin Wallet and purchase Bitcoin in order to add it to your BITCOIN wallet and then one can make payments through BITCOIN wallet using available BITCOINS.</li>
<br/>
<li><strong>WHY BITCOIN?</strong><br/>Bitcoin payments are easy to make with a wallet application and addresses. You can use a standard desktop or smartphone to transact with an individual, merchant and exchange.</li> 

		</ul>
		
		</div>
        </div>
    </div>
  </div>


<script>
function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    setInterval(function () {
        minutes = parseInt(timer / 60, 10)
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}

//window.onload = function () {
    var fiveMinutes = 60 * 15,
    display = document.querySelector('#timer');
    startTimer(fiveMinutes, display);
//};
</script>
<script type="text/javascript">
	setInterval(function()
	{
		$.ajax({
			type:'POST',
			url:"check_bitcoin_status.php/",
			data:"paymentid=<?php echo $_PAYMENT_ID.'&bitcoinid='.$bitcoin_id.'&playerid='.$_PARAMS['player_id'].'&starttime='.$session_start_time; ?>",
			success:function(response){
				
				if(response == 1){
					$("#bitcoincontainer").css('display', 'none');
					$("#paymentPaidMessagediv").removeAttr("style").show();
					var LOCATIONURL  = "<?php echo $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID ?>";
					setTimeout(function(){ parent.parent.parent.location = LOCATIONURL; }, 5000);
					
				}else if(response == 2){
					$("#bitcoincontainer").css('display', 'none');
					$("#paymentExpiredMessagediv").removeAttr("style").show();
					var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID ?>";
					setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 5000);
					
				}else if(response == 3){
					var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'?s=unknown' ?>";
					parent.parent.parent.location = LOCATION_URL;
				}
				if(response == 5){
					$("#bitcoincontainer").css('display', 'none');
					$("#paymentPendingMessagediv").removeAttr("style").show();
					var LOCATION_URL  = "<?php echo $_PARAMS['redirect_url'].'?i='.$_PAYMENT_ID ?>";
					setTimeout(function(){ parent.parent.parent.location = LOCATION_URL; }, 5000);
				}
			}
		});
		
	}, 15000);//time in milliseconds 
</script>
</body></html>
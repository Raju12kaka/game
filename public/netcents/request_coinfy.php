<?php 
set_time_limit(1000);
define('PROVIDER_NAME', $_POST['providerName']);

$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	require './CoinifyAPI.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		$signature = hash('sha256', 'CETR'.$_POST['orderAmount'].$_POST['orderCurrency'].$_POST['orderNumber'].PRIVATE_KEY);
		
		if ($_PARAMS['amount'] != $_POST['orderAmount'] || $_PARAMS['currency_id'] != $_POST['orderCurrency'] || $signature!=strtolower($_POST['signInfo'])){

			$_RESULT['status'] = 'Error';
			$_RESULT['errorcode'] = 205;
			$_RESULT['html'] = 'Type mismatch';
		
		} else {
			$_RESULT['status'] = 'Initiated';
      		$_RESULT['errorcode'] = 207;
      		$_RESULT['foreign_errorcode'] = 0;
      		$_RESULT['html']   = 'Pending';
      		
      		//insert into payments table
      		$_PAYMENT_ID = false;
			$_PAYMENT_ID = insert_payment($_RESULT, $_PARAMS);
			$bitcoin_id = insert_bitcoin_status($_PARAMS['player_id'], $_PAYMENT_ID);
			
			//Bitcoin iframe loads
			$api = new CoinifyAPI(trim($_POST['authorisationkey1']), trim($_POST['authorisationkey2']));

			$plugin_name = 'MyPlugin';
			$plugin_version = '1';
			$custom = array('playerid' => $_PARAMS['player_id'], 'orderid' => $_PAYMENT_ID);
			
			
			$response = $api->invoiceCreate($_PARAMS['amount'], "USD", $plugin_name, $plugin_version,"",$custom,"https://payments.royalcasinolounge.com/bitcoin/callback.php");
			
			require 'QRcode/QRCode.class.php'; // Include the QRCode class
			try
			{
			
			    /**
			     * If you have PHP 5.4 or higher, you can instantiate the object like this:
			     * (new QRCode)->fullName('...')->... // Create vCard Object
			     */
			    $oQRC = new QRCode; // Create vCard Object
			    $oQRC->url($response['data']['bitcoin']['payment_uri']) // Add URL Website
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
		     
			//echo '<iframe id="bitcoincss" src="'.$response['data']['payment_url'].'" height="750" width="500"></iframe>';
		}

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
    <link rel="icon" href="https://www.coinify.com/favicon.ico">
    <title>Coinify: Payment</title>
    <link href="https://cdn.coinify.com/assets/css/external/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.coinify.com/assets/css/base.css" rel="stylesheet">
   
   
     <style>
        .bitcoin_timer{    display: inline-block;}
    
        #qrCodeColumn p img{width: 150px;    height: 150px;}
        img.coinLogoLarge {
    width: 28px  ! important;
            height: 28px ! important;}
        .selectedCoinName {
    font-size: 14px  ! important;}
        .selectedCoinAmount{line-height: 33px ! important;
    height: 45px ! important;}
        
         .bc_wallet{width:100%;margin:0 auto;}
                .bc_wallet_img1{width: 100%;}
                .bc_wallet_img2{width: 100%;}
                .bc_wallet_img3{width: 100%;/* padding: 8px;padding-top: 4px; */}
       
         #paymentAmountColumn{width: 58% !important;    float: right;}
            .selectedCoinAmount{    float: left;    margin-top: 10px;}
            .bc_wallet a{float:left; width: 30%;}
         
     /* ----for mobile----*/  
    @media (max-width:300px){
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
                font-size: 11px ! important;}
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
         .content iframe{
	    height: 480px !important;
}
       
    </style>
    <!-- End Segments code -->
        <link href="https://cdn.coinify.com/assets/css/payment.css" rel="stylesheet">
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
        <div id="qrCodeColumn" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: block;">
            <!--<canvas id="qrCode" height="263" width="263"></canvas>-->
            <?php echo $oQRimage; ?>
        </div>
       <!-- <div id="merchantLogoColumn" class="col-xs-12 col-sm-6 col-sm-offset-1 col-lg-4 col-lg-offset-0 col-lg-push-4">
            <img id="merchantLogo" src="https://s3-eu-west-1.amazonaws.com/upload.coinify.com/merchant_logos/20973.png" onerror="this.style.display='none'" style="display: none;">
        </div>-->
        <div id="paymentAmountColumn" class="col-xs-8 col-sm-8 col-lg-8 col-md-8">
            <p class="lead text-muted"></p>

            <div id="coinAmountField" class="btn-group" style="">
                <button class="btn btn-default dropdown-toggle" type="button" id="coinDropdownButton" data-toggle="dropdown">
                    <img class="coinLogoLarge selectedCoinLogo" src="https://cdn.coinify.com/assets/images/coins/BTC.png">
                    <span class="selectedCoinName">BTC</span>
                    <span class="caret"></span>
                </button>
                <span class="selectedCoinAmount"><?php echo $response['data']['bitcoin']['amount'] ?></span>
                <ul class="dropdown-menu coinColumns" aria-labelledby="coinDropdown">
                                            <li>
                            <a href="#" data-coin="BTC">
                                <img src="https://cdn.coinify.com/assets/images/coins/BTC.png" class="coinLogoSmall coinLogoDropdown">
                                Bitcoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="LTC">
                                <img src="https://cdn.coinify.com/assets/images/coins/LTC.png" class="coinLogoSmall coinLogoDropdown">
                                Litecoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="ETH">
                                <img src="https://cdn.coinify.com/assets/images/coins/ETH.png" class="coinLogoSmall coinLogoDropdown">
                                Ether
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="START">
                                <img src="https://cdn.coinify.com/assets/images/coins/START.png" class="coinLogoSmall coinLogoDropdown">
                                Startcoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="USDT">
                                <img src="https://cdn.coinify.com/assets/images/coins/USDT.png" class="coinLogoSmall coinLogoDropdown">
                                TetherUSD
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="DOGE">
                                <img src="https://cdn.coinify.com/assets/images/coins/DOGE.png" class="coinLogoSmall coinLogoDropdown">
                                Dogecoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="RDD">
                                <img src="https://cdn.coinify.com/assets/images/coins/RDD.png" class="coinLogoSmall coinLogoDropdown">
                                Reddcoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="PPC">
                                <img src="https://cdn.coinify.com/assets/images/coins/PPC.png" class="coinLogoSmall coinLogoDropdown">
                                Peercoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="SJCX">
                                <img src="https://cdn.coinify.com/assets/images/coins/SJCX.png" class="coinLogoSmall coinLogoDropdown">
                                StorjX
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="NBT">
                                <img src="https://cdn.coinify.com/assets/images/coins/NBT.png" class="coinLogoSmall coinLogoDropdown">
                                Nubits
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="NVC">
                                <img src="https://cdn.coinify.com/assets/images/coins/NVC.png" class="coinLogoSmall coinLogoDropdown">
                                Novacoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="DGB">
                                <img src="https://cdn.coinify.com/assets/images/coins/DGB.png" class="coinLogoSmall coinLogoDropdown">
                                Digibyte
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="MSC">
                                <img src="https://cdn.coinify.com/assets/images/coins/MSC.png" class="coinLogoSmall coinLogoDropdown">
                                Mastercoin
                            </a>
                        </li>
                                            <li>
                            <a href="#" data-coin="XCP">
                                <img src="https://cdn.coinify.com/assets/images/coins/XCP.png" class="coinLogoSmall coinLogoDropdown">
                                Counterparty
                            </a>
                        </li>
                                    </ul>
            </div>
            <div id="coinAmountFieldPlain" style="display: none;">
                <img class="coinLogoLarge selectedCoinLogo" src="https://cdn.coinify.com/assets/images/coins/BTC.png" style="margin-top: -6px">
                <span class="selectedCoinName" style="margin-right: 4px">BTC</span>
                <span class="selectedCoinAmount"><?php echo $response['data']['bitcoin']['amount'] ?></span>
            </div>
        </div>
        <div id="paymentColumn" class="col-xs-12 col-lg-8">
            <hr>
            <p id="paymentAddress"><?php echo $response['data']['bitcoin']['address'] ?></p>
            <hr>
          <!--  <p id="paymentButton">
                <a class="btn btn-primary" href="https://blockchain.info/" target="_blank">
                    <span id="paymentButtonBTCIcon" class="glyphicon glyphicon-btc"></span>
                    <span id="paymentButtonText">Pay using Bitcoin client</span>
                </a>
            </p>-->

           <p>If you don't have bitcoin wallet, do not worry, click on below given images to get one</p><br>
           
            <div class="bc_wallet" >
                <a href="https://www.coinmama.com/register" target="_blank" style="background: #2d6184;padding: 8px;    margin-right: 6px;    padding-top: 12px;   padding-bottom: 12px;"><img class="bc_wallet_img1" src="img/coinmama.png" alt="coinmama_logo" style="" /></a>
                <a href="https://blockchain.info/wallet/#/signup" target="_blank" style="background: #049bd4;padding: 8px;margin-right: 6px;    padding-top: 12px; padding-bottom: 12px;"> <img class="bc_wallet_img2" src="img/blockchain.png" alt="blockchain_logo"  /></a> 
                <a href="https://www.coinbase.com/signup" target="_blank" style="background: #2b71b1;padding: 8px;    padding-top: 12px;   padding-bottom: 12px;"> <img class="bc_wallet_img3" src="img/coinbase.png" alt="blockchain_logo" style="" /></a>
            </div>
            
           
            <!--<p id="tradeLink"><a href="https://www.coinbase.com/" target="_blank">No bitcoin? Buy here</a></p>-->
        </div>
           <div class="col-xs-12" style="margin-top:8px; display:none;">
           
            <div class="bitcoin-info">
		<ul style="font-size: 13px !important;    padding-left: 8px;">
		
		<li>
		<strong>WHAT IS BITCOIN?</strong><br/>
		BITCOIN is a new currency like Dollars or Pounds, It is consensus network that enables a new payment system and a completely digital money, Bitcoin is a revolution that is changing the way everyone sees and uses money.</li>
		<br/>
		
<li><strong>HOW TO USE IT?</strong><br/>One has to first create a Bitcoin Wallet and purchase Bitcoin in order to add it to your BITCOIN wallet and then one can make payments through BITCOIN wallet using available BITCOINS.</li>
<br/>
<li><strong>WHY BITCOIN?</strong><br/>Bitcoin payments are easy to make with a wallet application and addresses. You can use a standard desktop or smartphone to transact with an individual, merchant and exchange.</li> 

		</ul>
		
		</div>
        </div>
        <!--<div class="col-xs-12 text-center">
            <hr style="margin-bottom: 10px">
        </div>-->
    </div>
  </div>
		<div id="paymentPaidMessagediv" class="col-xs-12 col-sm-5 col-md-4 alert alert-success" style="display: none; cursor: pointer;">
            <strong>Payment paid.</strong><br><br>
            The payment was successfully paid.<br>
            <img class="loadSpinner" src="https://cdn.coinify.com/assets/images/ajax-loader-large.gif"><br><br>
            <span id="paymentPaidMessageReturnText">You will automatically redirect to deposit details page.</span>
        </div>
        <div id="paymentExpiredMessagediv" class="col-xs-12 col-sm-5 col-md-4 alert alert-warning" style="display: none">
            <strong>Payment expired.</strong><br><br>
            The payment was not paid in time.<br>
            <img class="loadSpinner" src="https://cdn.coinify.com/assets/images/ajax-loader-large.gif"><br><br>
            <span id="paymentExpiredMessageReturnText">You will automatically redirect to web page.</span>
        </div>
        <div id="paymentPendingMessagediv" class="col-xs-12 col-sm-5 col-md-4 alert alert-info" style="display: none">
            <strong>Your Payment is in Pending state.</strong><br><br>
            Payment status will be updated once we got confirmation from bitcoin wallet processor.<br>
            <img class="loadSpinner" src="https://cdn.coinify.com/assets/images/ajax-loader-large.gif"><br><br>
            <span id="paymentExpiredMessageReturnText">You will automatically redirect to web page.</span>
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

<script src="https://cdn.coinify.com/external/javascript/jquery-2.1.1.min.js"></script>
<script src="https://cdn.coinify.com/assets/js/external/bootstrap.min.js"></script>
    
	

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

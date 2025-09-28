<?php 
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
?>

<html lang="en"><head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="https://www.coinify.com/favicon.ico">
    <title>Coinify: Payment</title>
    <link href="https://cdn.coinify.com/assets/css/external/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.coinify.com/assets/css/base.css" rel="stylesheet">
    <!-- Begin Segments code -->
    <script type="text/javascript" async="" src="/_Incapsula_Resource?SWJIYLWA=2977d8d74f63d7f8fedbea018b7a1d05&amp;ns=1"></script><script type="text/javascript" async="" src="https://cdn.segment.com/analytics.js/v1/J4obpuU4XpUEANJaM1n0UX8aGwIz1luJ/analytics.min.js"></script><script type="text/javascript">
        !function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&amp;&amp;console.error&amp;&amp;console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t&lt;analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t){var e=document.createElement("script");e.type="text/javascript";e.async=!0;e.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(e,n)};analytics.SNIPPET_VERSION="3.1.0";
            analytics.load("J4obpuU4XpUEANJaM1n0UX8aGwIz1luJ");
            analytics.page()
        }}();
    </script>
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
                font-size: 11px ! important;}
        #qrCodeColumn p img {
    width: 106px;
            height: 106px;}
        .lead{margin-bottom: 0px ! important;}
        #qrCodeColumn p{margin-bottom: 0px ! important;}
        #qrCodeColumn hr {    margin-top: 10px ! important;margin-bottom: 10px ! important;}
            
        }
    </style>
    <!-- End Segments code -->
        <link href="https://cdn.coinify.com/assets/css/payment.css" rel="stylesheet">
<script async="" src="https://wss.coinify.com/sub/1525392?callback=jQuery21106388410367622749_1481175751297&amp;_=1481175751298"></script></head>
<body>
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
                <span class="selectedCoinAmount">0.00066552</span>
            </div>
        </div>
        <div id="paymentColumn" class="col-xs-12 col-lg-8">
            <hr>
            <p id="paymentAddress"><?php echo $response['data']['bitcoin']['address'] ?></p>

            <p id="paymentButton">
                <a class="btn btn-primary" href="bitcoin:12SKSRKnhSkWAHzNn8oy8EefG2yAEVND3r?amount=0.00066552&amp;r=https%3A%2F%2Fwww.coinify.com%2Fpr%2Fr3f%2FgQyg">
                    <span id="paymentButtonBTCIcon" class="glyphicon glyphicon-btc"></span>
                    <span id="paymentButtonText">Pay using Bitcoin client</span>
                </a>
            </p>

            <p id="tradeLink"><a href="https://www.coinify.com/trade" target="_blank">No bitcoin? Buy here</a></p>
        </div>
        <div class="col-xs-12 text-center">
            <hr style="margin-bottom: 10px">
        </div>
    </div>
    <div class="modal fade" id="altcoinDialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">
                    <img id="altcoinDialogCoinLogo" class="coinLogoSmall" style="margin-right: 8px">
                    Pay with <span class="altcoinDialogCoinName"></span>
                </h4>
            </div>
            <div class="modal-body">
                <div id="altcoinDialogBody">
                    <p>
                        You have selected to pay with <strong><span class="altcoinDialogCoinName"></span></strong>.<br>
                        Please provide a <span class="altcoinDialogCoinName"></span> address that we can return your
                        deposit to in case of any errors:
                    </p>

                    <div class="input-group" style="width: 100%; margin-bottom: 10px;">
                        <input id="altcoinDialogReturnAddressInput" class="form-control" placeholder="Return address" type="text">
                        <span class="input-group-btn">
                            <button id="returnAddressScannerButton" class="btn btn-default" title="Scan a QR code with your return address"><span class="glyphicon glyphicon-qrcode"></span></button>
                        </span>
                    </div>
                    <p>
                        Price estimate: <strong><span id="altcoinDialogCoinAmount"></span></strong><br>
                        <span class="text-muted">We will give you an exact amount once you have provided a return address.</span>
                    </p>

                    <div id="altcoinDelayDisclaimer" class="alert alert-info">
                        <strong>Note:</strong>
                        Paying with altcoins takes a little longer than paying with bitcoins.<br>
                        Expect a delay of 30-90 seconds before we register your payment.
                    </div>
                </div>
                <div id="altcoinDialogLoader">
                    <img class="loadSpinner" src="https://cdn.coinify.com/assets/images/ajax-loader-large.gif" style="margin-right: 8px">
                    <em><span id="altcoinDialogLoaderText"></span></em>
                </div>
                <div id="altcoinErrorAlert" class="alert alert-danger"></div>
            </div>
            <div class="modal-footer">
                <a href="#" data-dismiss="modal" style="margin-right: 8px">Cancel</a>
                <button id="altcoinDialogPayButton" type="button" class="btn btn-primary">
                    Pay with <span class="altcoinDialogCoinName"></span>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="coinAddressScannerDialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">Please scan your address</h4>
            </div>
            <div class="modal-body">
                <div id="scannerView"></div>
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

<script src="https://cdn.coinify.com/external/javascript/jquery-2.1.1.min.js"></script>
<script src="https://cdn.coinify.com/assets/js/external/bootstrap.min.js"></script>
    <script src="https://cdn.coinify.com/assets/js/external/jsqrcode-combined.min.js"></script>
    <script src="https://cdn.coinify.com/assets/js/external/html5-qrcode.min.js"></script>
    <script src="https://cdn.coinify.com/assets/js/external/jquery.qrcode-0.12.0.min.js"></script>
    <script src="https://cdn.coinify.com/assets/js/payment.js"></script>
    <script type="text/javascript">
        &lt;!--
        btc_payment_address = "12SKSRKnhSkWAHzNn8oy8EefG2yAEVND3r";
        btc_amount = 0.00066552;
        btc_amount_paid = 0;
        btc_amount_due = 0.00066552;

        payment_id = 1525392;

                    payment_address = btc_payment_address;
            payment_amount_due = btc_amount_due;
            payment_uri_scheme = 'bitcoin';
            payment_coin = 'BTC';
        
        payment_state = "waiting";
        payment_url = "https:\/\/www.coinify.com\/payment\/r3f\/gQyg";
        payment_return_url = "http:\/\/gateway.cetroconcasino.com\/success.php";
        payment_cancel_url = null;

        seconds_left = 898;

        enable_altcoin_payments = true;
        altcoin_data = {"coins":{"BTC":{"name":"Bitcoin","symbol":"BTC","logo_file":"BTC.png","uri_scheme":"bitcoin"},"LTC":{"name":"Litecoin","symbol":"LTC","logo_file":"LTC.png","uri_scheme":"litecoin"},"ETH":{"name":"Ether","symbol":"ETH","logo_file":"ETH.png","uri_scheme":"ether"},"START":{"name":"Startcoin","symbol":"START","logo_file":"START.png","uri_scheme":"startcoin"},"USDT":{"name":"TetherUSD","symbol":"USDT","logo_file":"USDT.png","uri_scheme":"tetherusd"},"DOGE":{"name":"Dogecoin","symbol":"DOGE","logo_file":"DOGE.png","uri_scheme":"dogecoin"},"RDD":{"name":"Reddcoin","symbol":"RDD","logo_file":"RDD.png","uri_scheme":"reddcoin"},"PPC":{"name":"Peercoin","symbol":"PPC","logo_file":"PPC.png","uri_scheme":"peercoin"},"SJCX":{"name":"StorjX","symbol":"SJCX","logo_file":"SJCX.png","uri_scheme":"storjx"},"NBT":{"name":"Nubits","symbol":"NBT","logo_file":"NBT.png","uri_scheme":"nubits"},"NVC":{"name":"Novacoin","symbol":"NVC","logo_file":"NVC.png","uri_scheme":"novacoin"},"DGB":{"name":"Digibyte","symbol":"DGB","logo_file":"DGB.png","uri_scheme":"digibyte"},"MSC":{"name":"Mastercoin","symbol":"MSC","logo_file":"MSC.png","uri_scheme":"mastercoin"},"XCP":{"name":"Counterparty","symbol":"XCP","logo_file":"XCP.png","uri_scheme":"counterparty"}},"logo_url_prefix":"https:\/\/cdn.coinify.com\/assets\/images\/coins\/"};

        is_iframe = false;
        use_payment_request = true;
                payment_request_url = "https:\/\/www.coinify.com\/pr\/r3f\/gQyg";
        
        var websocketBaseHttpUrl = "https://wss.coinify.com/sub/";
        var websocketBaseWsUrl = "wss://wss.coinify.com/ws/";

        /**
         * Function to run when the page is first loaded
         */
        $(function () {
            initializePayment();
        });
        --&gt;
    </script>
	
<script type="text/javascript">
//&lt;![CDATA[
(function() {
var _analytics_scr = document.createElement('script');
_analytics_scr.type = 'text/javascript'; _analytics_scr.async = true; _analytics_scr.src = '/_Incapsula_Resource?SWJIYLWA=2977d8d74f63d7f8fedbea018b7a1d05&amp;ns=1';
var _analytics_elem = document.getElementsByTagName('script')[0]; _analytics_elem.parentNode.insertBefore(_analytics_scr, _analytics_elem);
})();
// ]]&gt;
</script>
</body></html>
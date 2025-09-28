<?php
define('PROVIDER_NAME', 'gateways');
//$_REQUEST_ID = isset($_POST['i']) ? $_POST['i'] : $_GET['i'];
include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
//get states list when the user changes country
if(isset($_POST['ajax_states']) && $_POST['ajax_states'] == 1){
	//get states list
	$statesList = get_states_code($_POST['countryId']);
	$list = '<option value = "">Select State</option>';
	foreach($statesList as $states){
		$list .= '<option value = "'.$states["state_code"].'">'.$states["state_name"].'</option>';
	}
	echo $list;
}
if(isset($_POST['ajax_conversion']) && $_POST['ajax_conversion'] == 1){
	//get states list
	$fromcurrency = $_POST['from'];
	$tocurrency = $_POST['to'];
	$amount = $_POST['amount'];
	$converted_amount = convert_currency($fromcurrency, $tocurrency, $amount);
	$converted_amount = round($converted_amount, 2);
	echo $converted_amount;
}
if ($_REQUEST_ID){
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/langs.php';
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	//get countries list
	$countries = get_countries_list($_PARAMS['web_id']);
    $playerDetails = get_player_all_details($_PARAMS['player_id']);
    $lastAmount=get_player_last_success_amount($_PARAMS['player_id']);
	//get min and max deposit limits
	//$depositlimits = get_deposit_limits($_PARAMS['payment_method_id']);
    $depositlimits = get_deposit_limits_byrisklevels($_PARAMS['payment_method_id'],$playerDetails->players_classes_id);
    $methodData = getPaymenthodDetails($_PARAMS['payment_method_id']);
	$card_number = '';
    $cvv_number = '';
    $expiry_year = '';
    $cexpiry_month = '';
	$nameOnCard = '';
    if(isset($_GET['c'])){
		$paymentid = $_GET['c'];
    	$card_info = get_carddetails($paymentid);
		$nameOnCard = get_player_name($_PARAMS['player_id']);
		$card_number = !empty($card_info->cardnumber) ? $card_info->cardnumber : '';
		$cvv_number = !empty($card_info->cvv_no) ? $card_info->cvv_no : '';
		$expiry_year = !empty($card_info->card_expiry_year) ? $card_info->card_expiry_year : '';
		$cexpiry_month = !empty($card_info->card_expiry_month) ? $card_info->card_expiry_month : '';
	}
	if(isset($_GET['vt'])){
		$nameOnCard = get_player_name($_PARAMS['player_id']);
	}
	/*** Multi currency code ***/
	if($_PARAMS['player_currency_id']){
	   $currencysymbol = get_currency_symbol($_PARAMS['player_currency_id']);
	   //echo "<pre>"; print_r($currencysymbol);
	}
	$getCurrencyAvailableProcessor = get_currency_available_processor($_PARAMS['player_currency_id'], $_PARAMS['payment_method_id']);
	// print_r($getCurrencyAvailableProcessor);
	/*if($getCurrencyAvailableProcessor->count > 0){
		$converted_amount = convert_currency($_PARAMS['player_currency_id'], DEFAULT_CURRENCY, $_PARAMS['amount']/100);
		$converted_amount = round($converted_amount, 2);
		$conversionrate = get_conversion_rate(DEFAULT_CURRENCY, $_PARAMS['player_currency_id']);
		$currencysymbol = get_currency_symbol($_PARAMS['player_currency_id']);
		// echo "<pre>"; print_r($currencysymbol);
		// echo "symbol || ".mb_convert_encoding($currencysymbol['sym'], 'UTF-8', 'UTF-8')." || ".utf8_decode2($currencysymbol['html_symbol']);
		// echo $_PARAMS['id']." || ".($converted_amount*100)." || ".DEFAULT_CURRENCY." || ".$_PARAMS['amount']." || ".$conversionrate->conversion_rate; exit;
		update_payment_request_data($_PARAMS['id'], $_PARAMS['amount'], DEFAULT_CURRENCY, $_PARAMS['amount'], 1, $step = 1);	
	}else{
	    $converted_amount = convert_currency($_PARAMS['player_currency_id'], DEFAULT_CURRENCY, $_PARAMS['amount']/100);
		$converted_amount = round($converted_amount, 2);
		$conversionrate = get_conversion_rate($_PARAMS['player_currency_id'], DEFAULT_CURRENCY);
		$currencysymbol = get_currency_symbol($_PARAMS['player_currency_id']);
		update_payment_request_data($_PARAMS['id'], $_PARAMS['amount'], DEFAULT_CURRENCY, $_PARAMS['amount'], 1, $step = 1);
	} */
	/*** Multi currency code ***/
?>
<!-- <link type="text/css" href="css/styles.css" rel="stylesheet"/> -->
<link type="text/css" href="css/messi.css" rel="stylesheet"/>
<!-- <script language="javascript" src="js/jquery.min.js"></script> --> 
<script language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script language="javascript" src="js/jquery.creditCardValidator.js"></script>
<script language="javascript" src="js/messi.min.js"></script>
<script language="javascript">
	function showerroralert(text){
		new Messi(text, {
			title: '<?php echo $_FORM_TEXTS['error'];?>',
			titleClass: 'anim error-ani',
			modal: true,
			width: '<?php if (isset($_PARAMS['web_id']) &&  (is_wap($_PARAMS['web_id']))) echo '150px'; else echo '350px' ?>'
		});
	}
	function toggleLangSelector(){
		var langSelector = document.getElementById('langSelector');
		if (langSelector.style.display == 'none'){
			langSelector.style.display='';
		} else {
			langSelector.style.display='none';
		}
	}
	function toggleTermsWindow(){
		var windowVisib = document.getElementById('tblTermsConditions').style.display;
		if (windowVisib != 'none' && windowVisib != '') document.getElementById('tblTermsConditions').style.display = 'none';
		else  {
			closeWindows();
			document.getElementById('tblTermsConditions').style.display = 'inline';
		}
	}
	function toggleAboutUsWindow(){
		var windowVisib = document.getElementById('tblAboutUs').style.display;
		if (windowVisib != 'none' && windowVisib != '') document.getElementById('tblAboutUs').style.display = 'none';
		else {
			closeWindows();
			document.getElementById('tblAboutUs').style.display = 'inline';
		}
	}        
	function toggleSecurityWindow(){
		var windowVisib = document.getElementById('tblTrustwaveInfo').style.display;
		
		if (windowVisib != 'none' && windowVisib != '') document.getElementById('tblTrustwaveInfo').style.display = 'none';
		else {
			closeWindows();
			document.getElementById('tblTrustwaveInfo').style.display = 'inline';
		}
	}
	function toggleCvvWindow() {
		var windowVisib = document.getElementById('tblCvvInfo').style.display;

		if (windowVisib != 'none' && windowVisib != '') document.getElementById('tblCvvInfo').style.display = 'none';
		else {
			closeWindows();
			document.getElementById('tblCvvInfo').style.display = 'inline';
		}
	}            
	function filterCardNumber(textbox) {
		var ValidChars = "0123456789";
		var Char;

		for (i = 0; i < textbox.value.length; i++) { 
			Char = textbox.value.charAt(i); 
			if (ValidChars.indexOf(Char) == -1) {
				textbox.value = textbox.value.replace(Char,'');
			}
		}
	}    
	function filterNumber(textbox) {
		var ValidChars = "0123456789";
		var Char;

		for (i = 0; i < textbox.value.length; i++) {
			Char = textbox.value.charAt(i);
			if (ValidChars.indexOf(Char) == -1) {
				textbox.value = textbox.value.replace(Char, '');
			}
		}
	}       
	/*
	function verifyCardNumber(cardnumber){ 
		//Validate Credit/Debit Card
		var credit_card_number = cardnumber;
		if(credit_card_number)
		{
			$('#idcardnumber').validateCreditCard(function(result) {      
				if(!result.valid)
				{
					showerrorborders('idcardnumber');
					//showerroralert('Enter valid card number');
					document.getElementById('idbtnSubmit').disabled=true;
					document.getElementById('idbtnCancel').disabled=true;
				}	
				else
				{
					document.getElementById('idbtnSubmit').disabled=false;
					document.getElementById('idbtnCancel').disabled=false;
				}
			});
		}
	}
	*/
	function closeWindows() {
		//document.getElementById('tblTermsConditions').style.display = 'none';
		//document.getElementById('tblTrustwaveInfo').style.display = 'none';
		//document.getElementById('tblAboutUs').style.display = 'none';
	}	

	// Code for Add Cash functiolnality

	
	function quickeamountfun(val, playerId){
		$("#termsdata").empty();
		$("#pay_loader").show();
		$("#idorderAmount").append('');
		$("#idorderAmount").val(val);

		$.ajax({
			type: 'post',
			url: '../availablebonuslist.php',
			data: {selected_amount :val, playerId:playerId},
			success:function(result){
				if(result){
					$('#availbonusCode').html(result);
					$("#available_coupons").show();
					$("#pay_loader").hide();
				}else{
					$('#availbonusCode').html(result);
					$("#available_coupons").hide();
					$("#pay_loader").hide();
				}
			}
		});
	}

	// code for Entering Amount
	function enteredAmountValue(playerId){
		$("#termsdata").empty();
		var eAmount = $('#idorderAmount').val();
		$("#pay_loader").show();

		$.ajax({
			type: 'post',
			url: '../availablebonuslist.php',
			data: {selected_amount :eAmount, playerId:playerId},
			success:function(result){
				// console.log("Enter Amount Call :: "+JSON.stringify(result));
				if(result){
					$('#availbonusCode').html(result);
					$("#available_coupons").show();
					$("#pay_loader").hide();
				}else{
					$('#availbonusCode').html(result);
					$("#available_coupons").hide();
					$("#pay_loader").hide();
				}
			}
		});
	}
	
	// End code

	// Get Terms by bonus List
	function gettermsbybonus(bonusCode, playerId){
		$("#termsdata").empty();
		$.ajax({
			type: 'post',
			url: '../gettermsdata.php',
			data: {bonusCode :bonusCode, playerId:playerId},
			success:function(result){
				// console.log("resp :: "+result);
				if(result){
					$('#termsdata').append(result);
				}else{
					$('#termsdata').append('');
				}
			}
		});
	}

	function shownormalborders(id){
		document.getElementById(id).style.borderColor = "#C0C0C0";
	}                
	function showerrorborders(id){
		document.getElementById(id).style.borderColor = "#E75400";
	}
	/*** Multi currency code ***/
	//convert amount to default currency and player cirrency
	function convertAmount(fromcurrency, tocurrency, amount){
		$.ajax({
			type:'post',
			url:'form.php',
			data:'amount='+amount+'&from='+fromcurrency+'&to='+tocurrency+'&ajax_conversion=1',
			success:function(result){
				$("#idconvertedOrderAmount").val(result);
			}
		});
	} 
	/*** Multi currency code ***/                
	function checkform(f){

		window.top.postMessage(950 + '-' + 'iframe2',"*");
		document.getElementById('idbtnSubmit').disabled=true;
		document.getElementById('idbtnCancel').disabled=true;
		<?php /*if($_PARAMS['payment_method_id'] == 100){ ?>
			var re = new RegExp("^4");
		<?php }else{ ?>
			var re = new RegExp("^5");
		<?php } */?>
		var allowcard = '';
		<?php if($_PARAMS['payment_method_id'] == 100){ ?>
			var re = new RegExp("^4");
			allowcard = '<?php echo $_TEST_CARDS_ARR[100]; ?>';
		<?php }else if($_PARAMS['payment_method_id'] == 101){ ?>
			var re = new RegExp("^5");
			allowcard = '<?php echo $_TEST_CARDS_ARR[101]; ?>';
		<?php }else if($_PARAMS['payment_method_id'] == 119){ ?>
			var re = new RegExp("^6");
		<?php }else if($_PARAMS['payment_method_id'] == 117){ ?>
			var re = new RegExp("^35");
		<?php }else if($_PARAMS['payment_method_id'] == 120){ ?>
			var re = new RegExp("^37");
		<?php }else{ ?>
			var re = new RegExp("^3");
		<?php } ?>
		var checkname = /^[A-Za-z ]+$/;
		var checkalphanum = /^[a-z0-9 ]+$/i;
		var appenv = '<?php echo APPENV; ?>';
		var minamount = '<?php echo $depositlimits->minimum_deposit_amount/100; ?>';
		var maxamount = '<?php echo $depositlimits->maximum_deposit_amount/100; ?>';

		if (!f.cardName.value || !f.cardNumber.value || f.cardNumber.value.length<13 || !f.cardSecurityCode.value || f.cardSecurityCode.value.length<3 || !f.cardExpireMonth.value || !f.cardExpireYear.value || (f.cardNumber.value.match(re) == null) || (f.cardName.value.match(checkname) == null) || !f.orderAmount.value || f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value)) || !f.billingaddress.value || !f.billingcity.value || !f.billingzip.value || !f.billingstate.value || !f.billingcountry.value){

				if (!f.cardName.value){
					showerrorborders('idcardname');
				}
				if(f.cardName.value && f.cardName.value.length<3){
					showerrorborders('idcardname');
					showerroralert('Please enter proper Name');
				}
				if (f.cardName.value && f.cardName.value.match(checkname) == null){
					showerrorborders('idcardname');
					showerroralert('Please enter valid Name');
				}
				if (!f.cardNumber.value || f.cardNumber.value.length<13){
					showerrorborders('idcardnumber');
				}
				if (f.cardNumber.value && f.cardNumber.value.match(re) == null){
					showerrorborders('idcardnumber');
					showerroralert('Please enter valid card number');
				}
				
				if (!f.cardSecurityCode.value || f.cardSecurityCode.value.length<3){
					showerrorborders('idcvv');
				}
				if (!f.cardExpireMonth.value){
					showerrorborders('idmonth');
				}
				if (!f.cardExpireYear.value){
					showerrorborders('idyear');
				}
				if (!f.orderAmount.value){
					showerrorborders('idorderAmount');
				}
				if (f.orderAmount.value && (f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount))){
					showerrorborders('idorderAmount');
					showerroralert('Deposit amount should be min '+minamount+' and max '+maxamount);
				}
				if(f.orderAmount.value && isNaN(f.orderAmount.value)){
					showerrorborders('idorderAmount');
					showerroralert('Please enter valid amount');
				}
				if (!f.billingaddress.value){
					showerrorborders('idbillingaddress');
					$("#flip").click();
				}
				if (!f.billingcity.value){
					showerrorborders('idbillingcity');
					$("#flip").click();
				}
				if(f.billingcity.value && f.billingcity.value.match(checkalphanum) == null){
					showerrorborders('idbillingcity');
					showerroralert('Please enter valid city name');
					$("#flip").click();
				}
				if (!f.billingcountry.value){
					showerrorborders('idbillingcountry');
					$("#flip").click();
				}
				if (!f.billingstate.value){
					showerrorborders('idbillingstate');
					$("#flip").click();
				}
				if (!f.billingzip.value){
					showerrorborders('idbillingzip');
					$("#flip").click();
				}
				if (f.idbillingzip.value && (f.idbillingzip.value.length < 4 || f.idbillingzip.value.length > 7)){
					showerrorborders('idbillingzip');
					showerroralert('Please enter valid zipcode');
					$("#flip").click();
				}
					
				document.getElementById('idbtnSubmit').disabled=false;
				document.getElementById('idbtnCancel').disabled=false;
				
				if (!f.cardNumber.value || !f.cardSecurityCode.value || !f.cardExpireMonth.value || !f.cardExpireYear.value){
					showerroralert('<?php echo $_FORM_TEXTS['alert_error1'];?>');
				} else if(f.cardNumber.value.length<13 || f.cardSecurityCode.value.length<3){
					showerroralert('<?php echo $_FORM_TEXTS['alert_error2'];?>');
				}
				return false;
		}

		// if(f.availbonusCode.value){
		// 	console.log("If Case");
		// }else{
		// 	console.log("Else Case");
		// }
		// return false;

		document.getElementById('idbtnSubmit').disabled=true;
		document.getElementById('idbtnCancel').disabled=true;
		return true;
	}
</script>
<style>
	.formTable td{padding-right: 10px !important;}
	#panel {display: none;}
	.formTable .address a{color: red; font-size: 11px; text-decoration: none;}
	a#updateaddress, a#cancel{color: red; font-size: 11px; text-decoration: none;}
	.rowColoured{white-space: normal;}
	@-moz-document url-prefix() {
		.rowColoured{white-space: normal;}
	}
	.mainDiv iframe {height: 760px !important;}
</style>
<?php if (is_wap($_PARAMS['web_id'])){ ?>
	<style>
		.formTable td{padding-right:0px !important; }
		/* #idbillingzip, #idbillingcity{width:98px !important;} */
		.indent2{padding-left:0px !important; }
	</style>
<?php } ?>
<script>
	setInterval(function() {
		//window.top.postMessage(document.body.scrollHeight + '-' + 'iframe1', "*");
	}, 500);
</script>
<script> 
	$(document).ready(function(){    
	    $("#flip").click(function(){
	    	//setInterval(function() {
	    	    window.top.postMessage(1320 + '-' + 'iframe1', "*");
	    	//}, 300);
	        $("#panel").slideDown("slow");
	        
	        //$("#idIframe", window.parent.document).height('1200px');
	        
	        if($(this).text() == 'Change')
	        {
	            $(this).text(' ');
	            $("#idbillingdetails").show();
	        }
	        else
	        {
	        	   $("#idbillingdetails").show();
	            $(this).text('Change');
	        }
	    });
	    $("#cancel").click(function(){
	    	window.top.postMessage(950 + '-' + 'iframe2',"*");
	    	var optionctryValue  = $("#plbillcount").text();
	        getstatesList(optionctryValue);
	        $("#panel").slideUp("slow");
	        $("#flip").text('Change');
	        $("#idbillingaddress").val($("#plbilladd").text());
	        $("#idbillingcity").val($("#plbillcit").text());
	        $("#idbillingzip").val($("#plbillzip").text());
	        $("#idbillingcountry").val(optionctryValue).find("option[value=" + optionctryValue +"]").attr('selected', true);
	        var optionValue  = $("#plbillsta").text();
	        setTimeout(function(){
				$("#idbillingstate").val(optionValue).find("option[value=" + optionValue +"]").attr('selected', true);
			}, 1000);
	       
			//$('.qbdiframe', window.parent.document).height('650px');
	    });
	    $("#updateaddress").click(function(){
	    	window.top.postMessage(950 + '-' + 'iframe2',"*");
	    	var add = $("#idbillingaddress").val();
	    	var cit = $("#idbillingcity").val();
	    	var ctry = $("#idbillingcountry").val();
	    	var sta = $("#idbillingstate").val();
	    	var zip = $("#idbillingzip").val();
	    	$("#plbilladd").text(add);
	    	$("#plbillcit").text(cit);
	    	$("#plbillcount").text(ctry);
	    	$("#plbillsta").text(sta);
	    	$("#plbillzip").text(zip);
	    	$("#idbillingdetails").show();
	    	$("#panel").hide();
	    	$("#flip").text('Change');
	    	
			//$('.qbdiframe', window.parent.document).height('650px');
	    });
	});	  
	function getstatesList(countryId){
	  	$.ajax({
	  		type:'post',
	  		url:'form.php',
	  		data:'countryId='+countryId+'&ajax_states=1',
	  		success:function(result){
	  			$("#idbillingstate").html('');
	  			$("#idbillingstate").html(result);
	  		}
	  	});
	}                                            
</script>	
<?php 
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		/*   METHOD START   */
		
		$action = VALIDATOR;
		$playerDetails = get_player_all_details($_PARAMS['player_id']);
		if(isset($_GET['bill']) && !empty($_GET['bill'])){
			$billdetails = explode('*', $_GET['bill']);
			$lastbillingdetails->billing_address = $billdetails[5];
			$lastbillingdetails->billing_city = $billdetails[8];
			$lastbillingdetails->billing_country = $billdetails[6];
			$lastbillingdetails->billing_state = $billdetails[7];
			$lastbillingdetails->billing_zipcode = $billdetails[9];
		}else{
			$lastbillingdetails = get_last_billing_details($_PARAMS['player_id']);
		}
		//get states list
		$countryCode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country;
		$states = get_states_code($countryCode);
		?>
<style>
.bimg img{    float: right;    width: 65%;}
.title{width: 50%;float: left;}
.plogo{  width: 50%;  float: right;}
.title1{    display: table;}
#idcardname,#idcardnumber{}
#idcvv{float: left;    display: block;    margin-right:2%;}
.cvvlbl{float: left; display: block;margin-right:5%;}
SELECT{padding: 10px;padding-bottom: 14px;}
INPUT{font-size: 14px;}
#idbillingdetails {font-size: 14px;margin: 6px 0;}
#flip{margin: 6px 0;font-size: 14px;color: #403c3c;}
#idbtnCancel{width:90px;background:#c0c0c0;padding:8px;border:none;color:white;font-size:16px;border-radius:4px;-webkit-appearance:none;}
#idbtnCancel:hover{background:#a7a7a7;}
#idbtnSubmit{width:90px;background:#61d666;padding:8px;border:none;color:white;font-size:16px;border-radius:4px;-webkit-appearance:none;}
#idbtnSubmit:hover{background:#53a00b;}
 
    
     @media only screen 
  and (min-device-width: 120px) 
  and (max-device-width: 480px)
      and (-webkit-min-device-pixel-ratio: 2) {
         
            #idyear{width: 63px;}
            #idmonth{width:70px;margin-left: 0px;}  
           #idcvv { /* margin-right:10%;margin-left: 6px;    margin-top: 6px;*/     margin-right: 3%;}
            .cvvlbl{margin-right:7%;}
          .explbl{    margin-right:25%;}
          
          
    }
    
       
   .main-container
           {
    margin: auto;
    background: #ffffff;
    border: 3px solid #a09f9f;
    padding: 30px;
    border-radius: 3px;
    width:80%;
    margin-top:25px;
    }
           
    }
  
  
  
  

</style>


<?php

	function getAvailableBonusList($player_id){

		// $url = 'https://adservices.akkhacasino.local/BonusExngCntr/getavailableBonusList';
		$url = AVAILABLE_BONUS_REDEEM_URL;

		$data = array();

		$data['player_id'] = $player_id;
		
		//$data = http_build_query($data);
		$curl = curl_init($url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
		curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
		curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST

		$resp= curl_exec($curl);
		$err =  curl_error($curl);

		curl_close ($curl);

		return $response = json_decode($resp,true);

	}

	$resBonusdetails = getAvailableBonusList($_PARAMS['player_id']);
	// echo "test<pre>"; print_r($resBonusdetails); die;

	if($resBonusdetails['error']==0){
		if($resBonusdetails['bonusesCount'] >0){
			$AvailbonusDetails = $resBonusdetails['bonusSetDetails'];

			foreach($AvailbonusDetails as $key => $vl){

				// if($vl['minimum_deposit_amount'] && ($_PARAMS['amount'] >= $vl['minimum_deposit_amount'])){
					if($vl['minimum_deposit_amount'] && (($depositlimits->minimum_deposit_amount/100) >= $vl['minimum_deposit_amount'])){
					$listofavail[$key]['bonusid'] = $vl['bonuses_id'];
					$listofavail[$key]['name'] = $vl['name'];
					$listofavail[$key]['min_amount'] = $vl['minimum_deposit_amount'];
					$listofavail[$key]['coupon_code'] = $vl['coupon_code'];
				}
			}
			if(empty($listofavail)){ ?>
                <style type="text/css">#available_coupons{
                    display:none;
                    }</style>
            <?php }else{ ?>
                <style type="text/css">#available_coupons{
                    display:block;
                    }</style>
          <?php  }
		}
	}
	

?>
		<div class="content" style="">
			
			<form id="idform" action='<?php echo $action; ?>' method='POST' onSubmit="return checkform(this);">

				<table width="100%" cellspacing="1" cellpadding="0" class="formTable">

                    <tr>
                        <td>
                        <tr class="rowColoured">
                            <b>
                            <div class="indent2"><?php echo $_FORM_TEXTS['cardname']; ?>&nbsp;<span id="asterisc">*</span>
                            </div>
                            </b>
                        </tr>
                        <tr>
							
						<input name="cardName" id="idcardname" type="text" value="<?php echo $nameOnCard; ?>" class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width:100%;" />
						<div></div>
						
                        </tr>
                    </td>
                    </tr>
                                    
                    <tr>
                        <td>
                        <tr class="rowColoured">
                            <b>
                            <div class="indent2"><?php echo $_FORM_TEXTS['cardnumber']; ?> &nbsp;<span id="asterisc">*</span>
                            </div>
                            </b>
                        </tr>
                        <tr>
							
						<input name="cardNumber" id="idcardnumber" type="text" value="<?php echo $card_number; ?>" maxlength="16" class="required" autocomplete="off" onkeyup="filterCardNumber(this)" onkeydown="filterCardNumber(this)" onkeypress="filterCardNumber(this)" onclick="shownormalborders(this.id);" style="width:100%;" />
						<div></div>
						
                        </tr>
                        </td>
                    </tr>
                     
                    
 <tr>
                <td>
                    
                            
                            <tr class="rowColoured">
								
								<div class="form_main_div">
                                 <!--CVV LABEL-->
								
                                <div class="indent2 cvv_div">
                                <div class="visa_cvv">CVV &nbsp;<span id="asterisc">*</span></div>
                                
                                <div class="visa_input">
                                 <!--CVV TEXT FIELD-->
                                <input name="cardSecurityCode" id="idcvv" value="<?php echo $cvv_number; ?>" maxlength="4"  class="required" autocomplete="off" onkeyup="filterNumber(this)" onkeydown="filterNumber(this)" onkeypress="filterNumber(this)" onclick="shownormalborders(this.id);" type="<?php echo !empty($cvv_number) ? 'password' : 'text' ?>">
                                 <!--CVV TEXT FIELD END-->
                                
                                 </div>                            
                                
                                </div>
                                
	                              <!--CVV LABEL END-->                                     
	                              <!--EXPIRY DATE-->
                                <div class="indent2 expiry_div">
                                 <div class="visa_expiry"><?php echo $_FORM_TEXTS['expirydate']; ?> &nbsp;<span id="asterisc">*</span></div>
                                 
                                 <div class="visa_select">
                                 
                                  <!--EXPIRY DATE TEXT FIELD-->
                                
                                <select name="cardExpireMonth" id="idmonth" class="required" style="" onclick="shownormalborders(this.id);">
							<option value=""><?php echo $_FORM_TEXTS['month']; ?></option>
							<option value="01" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "01") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_jan']; ?></option>
							<option value="02" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "02") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_feb']; ?></option>
							<option value="03" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "03") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_mar']; ?></option>
							<option value="04" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "04") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_apr']; ?></option>
							<option value="05" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "05") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_may']; ?></option>
							<option value="06" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "06") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_jun']; ?></option>
							<option value="07" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "07") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_jul']; ?></option>
							<option value="08" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "08") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_aug']; ?></option>
							<option value="09" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "09") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_sep']; ?></option>
							<option value="10" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "10") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_oct']; ?></option>
							<option value="11" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "11") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_nov']; ?></option>
							<option value="12" <?php echo (isset($cexpiry_month) && !empty($cexpiry_month) && $cexpiry_month == "12") ? "selected = 'selected'" : '' ?>><?php echo $_FORM_TEXTS['month_dev']; ?></option>
						</select>
                        
						<select name="cardExpireYear" id="idyear" class="required" style="" onclick="shownormalborders(this.id);">
							<option value=""><?php echo $_FORM_TEXTS['year']; ?></option>
                            
                            <?php
                            $year = date('Y');
                            for ($i=$year; $i<=$year+50; $i++){
                            ?>
							<option value="<?php echo $i;?>" <?php echo (isset($expiry_year) && $expiry_year == $i) ? "selected = 'selected'" : '' ?>><?php echo $i;?></option>
                            <?php
                            }
                            ?>
						</select>
                                 <!--EXPIRY DATE TEXT FIELD END-->
                                 </div>
                                   
                                </div>
                                
                                     <!--EXPIRY DATE END-->
                                
                                     <!--Amount-->
                                <div class="indent2 amount_div">
									<div class="visa_amount">Amount &nbsp;<span id="asterisc">*</span></div>
									<div class="visa_amount_textbox">
									 <?php
									 
                                if(isset($lastAmount->amount)){
                                    $get_last_deposit_amount= $lastAmount->amount/100;
                                }else{
                                    $get_last_deposit_amount=0;
                                }
                                $min_amount=$depositlimits->minimum_deposit_amount/100;
                                $max_amount=$depositlimits->maximum_deposit_amount/100;

                                if($get_last_deposit_amount <= $min_amount){
                                    $amount = $min_amount;
                                }else if($get_last_deposit_amount >= $max_amount){
                                    $amount = $max_amount;
                                }else{
                                    $amount = $get_last_deposit_amount;
                                }

                                ?>
                                
                                <!--Amount -->
                                <span style="" class="dollor_symbol"><?php echo $currencysymbol['html_symbol']; ?></span>
                                <input name="orderAmount" id="idorderAmount" value="<?php echo $_PARAMS['amount']; ?>" maxlength="6"  class="required" autocomplete="off" onkeyup="enteredAmountValue(<?php echo $_PARAMS['player_id']; ?>)" onclick="shownormalborders(this.id);" style="width: 70px;" type="text">
								<input type="hidden" name="supportcurpspcount" id="supportcurpspcount" value="<?php echo $getCurrencyAvailableProcessor->count; ?>" />
								<?php //if(DEFAULT_CURRENCY != $_PARAMS['player_currency_id']){ ?>
								<?php //if($getCurrencyAvailableProcessor->count == 0){ ?>
								<!--span style="color: #000!important; font-size:13px;" class="dollor_symbol"><?php //echo $currencysymbol['html_symbol']; ?></span-->
								<!--input name="convertedOrderAmount" id="idconvertedOrderAmount" value="<?php // echo $converted_amount; ?>" maxlength="6" readonly style="width: 70px; cursor: default; background: #e8e8e8;" type="text" -->
								<?php //} ?>
							
                                <!--Amount end-->
									</div>
									
					       		</div>
                                     <!--Amount end-->
								
					</div>
								
								
								
				<div class="payments-div">
					<div>
						<lable> Quick Select Options </label>
						<ul>
							<li id="quick_2k" name="quick_2k" onclick="quickeamountfun('2000', <?php echo $_PARAMS['player_id']; ?>);">2000</li>
							<li id="quick_1k" name="quick_1k" onclick="quickeamountfun('1000', <?php echo $_PARAMS['player_id']; ?>);">1000</li>
							<li id="quick_5h" name="quick_5h" onclick="quickeamountfun('500', <?php echo $_PARAMS['player_id']; ?>);">500</li>
							<li id="quick_2h" name="quick_2h" onclick="quickeamountfun('200', <?php echo $_PARAMS['player_id']; ?>);">200</li>
						</ul>
					</div>
				</div>

				<!-- Select Dropdown option -->
				
				<div id="pay_loader" style="text-align: center; display:none;">
					
					<div class="loader-icon">
						<div class="spinner-border" role="loading">
							<span class="sr-only"></span>
						</div>
					</div>
				</div>

				<div id="available_coupons"> 
					<label for="availbonusCode">Available Coupon Codes :</label>
					<select name="availbonusCode" id="availbonusCode" onchange="gettermsbybonus(this.value, <?php echo $_PARAMS['player_id']; ?>);">
						<?php 
							if(!empty($listofavail)){
								echo '<option value=0> Select Available Option </option>';
								foreach( $listofavail as $val )
								{
									$bonusid = $val['bonusid'];
									$ccode = $val['coupon_code'];
									$redamount = $val['min_amount'];

									echo '<option value='.$ccode.'>'.$ccode.'</option>';
								}
							}else{
								// echo '<option value=0> Select Available Option </option>';
								echo '';
							} 
						?>
					</select>
				</div>

				<div id="availbale_terms">
					<span id="termsdata"> </span>
				</div>
								
                            </tr>
                           
                </td>
				
				</tr>
				<tr>
	
					<span class="rowColoured" style="padding-top: 8px;display: block;">*Deposit amount should be minimum <?php echo $currencysymbol['html_symbol']; ?><?php echo $depositlimits->minimum_deposit_amount/100; ?> and maximum <?php echo $currencysymbol['html_symbol']; ?><?php echo $depositlimits->maximum_deposit_amount/100; ?></span>
                    </tr>
                    
                    <tr>
               
                <div style="float:none; clear: both;height: 1px;"></div>
							<span class="rowColoured" style="padding-top:2px;display: block;    white-space: nowrap;"> *Multiple transactions allowed</span><br/>
                    </tr>
                    
                    				
					<tr valign="top" class="address">
                        <td>
                        <tr class="rowColoured" ><div  class="indent2 "><strong>Billing Address</strong></div></tr>
                        <tr style="font-size:12px; white-space:normal !important;">
                            <div id="idbillingdetails">
                                <span id="plbilladd"><?php echo !empty($lastbillingdetails->billing_address) ? $lastbillingdetails->billing_address : $playerDetails->address; ?></span>,
                                 <span id="plbillcit"><?php echo !empty($lastbillingdetails->billing_city) ? $lastbillingdetails->billing_city : $playerDetails->city; ?></span>,
                                 <span id="plbillcount"><?php echo !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country; ?></span>,
                                  <span id="plbillsta"><?php echo !empty($lastbillingdetails->billing_state) ? $lastbillingdetails->billing_state : $playerDetails->state; ?></span>, 
                                  <span id="plbillzip"><?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?></span><br>
                               </div>
                            <a href="#" id="flip" >Change</a> 
                           <tr>
                           <td colspan="2">
							   
                            <div id="panel">
								
							<div class="add_main_div">
							<div class="add_1">
							<br>
										<div class=""><b>Address &nbsp;<span id="asterisc">*</span></b></div>
										<input name="billingaddress" id="idbillingaddress" value="<?php echo !empty($lastbillingdetails->billing_address) ? $lastbillingdetails->billing_address : $playerDetails->address; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" type="text">
								
							</div>
								
							<div class="add_1">
								<div class=""><b>City &nbsp;<span id="asterisc">*</span></b></div>
										<input name="billingcity" id="idbillingcity" value="<?php echo !empty($lastbillingdetails->billing_city) ? $lastbillingdetails->billing_city : $playerDetails->city; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" type="text">
							</div>
								
							<div class="add_1">
								<div class=""><b>Country &nbsp;<span id="asterisc">*</span></b></div>
										<?php $countrycode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country; ?>
										<select name="billingcountry" id="idbillingcountry" style="height:38px;" onchange="getstatesList(this.value)">
											<?php foreach($countries as $country): ?>
												<option value="<?php echo $country['country_id'] ?>" <?php echo ($country['country_id'] == $countrycode) ? "selected='selected'" : '' ?>><?php echo $country['name'] ?></option>
											<?php endforeach; ?>
										</select>
								</div>
								
								<div class="add_1">
										<div class=""><b>State &nbsp;<span id="asterisc">*</span></b></div>
										<?php $statecode = !empty($lastbillingdetails->billing_state) ? $lastbillingdetails->billing_state : $playerDetails->state; ?>
										<select name="billingstate" id="idbillingstate" style="height:38px;">
											<?php foreach($states as $state): ?>
												<option value="<?php echo $state['state_code'] ?>" <?php echo ($state['state_code'] == $statecode) ? "selected='selected'" : '' ?>><?php echo $state['state_name'] ?></option>
											<?php endforeach; ?>
										</select>
								</div>
								
								<div class="add_1">
									
									<div class=""><b>Zipcode &nbsp;<span id="asterisc">*</span></b></div>
										<input name="billingzip" id="idbillingzip" value="<?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?>" maxlength="7" class="required" autocomplete="off" onkeyup="filterCardNumber(this)" onkeydown="filterCardNumber(this)" onkeypress="filterCardNumber(this)" onclick="shownormalborders(this.id);" type="text">
								</div>
								
								<div class="add_1 update_cancel">
								<a href="#" id="updateaddress" >Update</a> | <a href="#" id="cancel" >Cancel</a>
								</div>
								
								
								
							</div>	
								
								
                        
								
								
								
                            
                            </div>
                            </td>
                            </tr>
						</tr>
                    </td>
					</tr>
									
                    <tr><td height="10px;"></td></tr>
                    <tr>
						<td align="left" class="rowColoured">
							<?php

							?>
							<input type="button" name="btnBack" id="idbtnBack" value="<?php echo $_FORM_TEXTS['back']; ?>" class="btnStd" style="width:90px; display:none;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnCancel').disabled=true; document.getElementById('idbtnCancel').disabled=true; location.href='<?php echo PAYMENTURL; ?>/gateways/?i=<?php echo $_REQUEST_ID; ?>';" />
							<?php
			

							$signature = hash('sha256', 'CETR'.$_PARAMS['amount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
							
							$ip = $_SERVER['REMOTE_ADDR'];
								
							$arr_post = array(
									"orderNumber"	=> $_PARAMS['id'],
									"orderCurrency"	=> $_PARAMS['currency_id'],
									//"orderAmount"	=> $_PARAMS['amount'],
									"signInfo"		=> $signature,
									"ip"			=> $ip,
									"country"		=> $_PARAMS['country_id'],
									"risk_level"	=> $playerDetails->players_classes_id,
									"dummy_enable"	=> $methodData->is_dummy
							);
							
							?>
							</td>
				</tr>
				<tr>
					<td>
						<?php 
						foreach($arr_post as $key=>$row) {
							?>
							<input type='hidden' name='<?php echo $key; ?>' value='<?php echo $row; ?>' />
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
                    <td align="left" class="rowColoured" colspan="2">
                    	<input type="hidden" name="i" value="<?php echo $_REQUEST_ID; ?>">
                    	<?php if(isset($_GET['c'])){ ?>
                    		<input type="hidden" name="c" value="<?php echo $_GET['c']; ?>">
                    	<?php } ?>
                    	<?php if(isset($_GET['vt'])){ ?>
                    		<input type="hidden" name="vt" value="<?php echo $_GET['vt']; ?>">
                    	<?php } ?>
						<input type="hidden" name="origin" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>">
                        <?php if(isset($_GET['c'])){ ?>
                        	<input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" style="width:45%;"  ui-sref="dashboard.RegPaymentMethod" />
                        <?php }else{ ?>
                        <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" style="width:30%;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.location.href='http://betampmnew.local:4200/profile/deposit-failed';" />
                        <?php } ?>
                        <input type="submit" name="btnSubmit" id="idbtnSubmit" value="<?php echo $_FORM_TEXTS['proceed']; ?>" class="btnStd" style="width:30%;" />
                    </td>
                </tr>
									
				<tr>
                	<td height="5px" colspan="2"></td>
                </tr>
            </table> 

		</form>
 <!-- <?php if($_PARAMS['payment_method_id'] == 101){ ?>
<div class="mastercard_freechip">
<?php if($_PARAMS['player_currency_id'] == 'USD'){ ?>
<img src="images/master.jpg">
<?php }elseif($_PARAMS['player_currency_id']=='AUD'){ ?>
<img src="images/master_au.jpg">
<?php }else{ ?>
<img src="images/master_eu.jpg">
<?php } ?>
</div>
<?php } ?> -->
		
		<?php
		
	/*   METHOD END    */
	
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
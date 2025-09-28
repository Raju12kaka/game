<?php

define('PROVIDER_NAME', 'gateways');

$_REQUEST_ID = isset($_POST['i']) ? $_POST['i'] : $_GET['i'];
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
	?>
		
		<link type="text/css" href="css/styles.css" rel="stylesheet"/>
        <link type="text/css" href="css/messi.css" rel="stylesheet"/>
	    <script language="javascript" src="js/jquery.min.js"></script>
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script language="javascript" src="js/messi.min.js"></script>
		<script language="javascript" src="js/jquery.creditCardValidator.js"></script>
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

            function closeWindows() {
                //document.getElementById('tblTermsConditions').style.display = 'none';
                //document.getElementById('tblTrustwaveInfo').style.display = 'none';
                //document.getElementById('tblAboutUs').style.display = 'none';
            }

            function shownormalborders(id){
                document.getElementById(id).style.borderColor = "#C0C0C0";
            }
                
            function showerrorborders(id){
                document.getElementById(id).style.borderColor = "#E75400";
            }
                
            function checkform(f){
            	<?php /*if($_PARAMS['payment_method_id'] == 100){ ?>
            		var re = new RegExp("^4");
            	<?php }else{ ?>
            		var re = new RegExp("^5");
            	<?php } */?>
            	<?php if($_PARAMS['payment_method_id'] == 100){ ?>
            		var re = new RegExp("^4");
            	<?php }else if($_PARAMS['payment_method_id'] == 101){ ?>
            		var re = new RegExp("^5");
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
                            showerroralert('Deposit amount should be min '+minamount+'$ and max '+maxamount+'$');
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

                document.getElementById('idbtnSubmit').disabled=true;
                document.getElementById('idbtnCancel').disabled=true;
                
                return true;
            }
            
            //Validate Credit/Debit Card
            $('#idcardnumber').on('change', function() {
            	console.log('hiii');
            	var credit_card_number = $('#idcardnumber').val();
            	if(credit_card_number)
		        {
			        $('#idcardnumber').validateCreditCard(function(result) {         
			            if(!result.valid)
			            {
			            	$('.log').html('Please enter valid credit card number');
			            	$('.log').show();
			            }	
			            else
			            {
			            	$('.log').hide();
			            }
			        });
			    }
		    });
            
            </script>
   <style>
   .formTable td{white-space: nowrap;
    padding-right: 10px !important;}
	#panel {
	   
	    display: none;
	}
	    
    .formTable .address a{color: red; font-size: 11px; text-decoration: none;}
    a#updateaddress, a#cancel{color: red; font-size: 11px; text-decoration: none;}
	    .rowColoured{white-space: normal;}
	     @-moz-document url-prefix() {
	     .rowColoured{white-space: normal;}
	     }
	    <style>
.mainDiv iframe {
            height: 760px !important;
         } 
</style>
	</style>
	<?php
	if (is_wap($_PARAMS['web_id'])){
	?>
	<style>
	.formTable td{padding-right:0px !important; }
	
	/* #idbillingzip, #idbillingcity{width:98px !important;} */
	.indent2{padding-left:0px !important; }
	</style>
	<?php
	}
	?>
	<script> 
	$(document).ready(function(){
	    $("#flip").click(function(){
	        $("#panel").slideDown("slow");
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
	    });
	    $("#updateaddress").click(function(){
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
    .indent2{padding-left: 0px;margin: 6px; margin-left: 0px; font-size: 12px;}
   #idform { 
 	width: 100%;
    margin: auto;
    background: #ffffff;
    border: 3px solid #a09f9f;
    padding:20px;
    border-radius: 3px;
    box-sizing: border-box;
    }
    #idcardname,#idcardnumber{}
   #idcvv {    float: left;    display: block;    margin-right:2%; border-radius:3px;}
    .cvvlbl{float: left; display: block;margin-right:9%;}
    .explbl{float: left; display: block;margin-right:21%;}
    SELECT{padding: 10px;    padding-bottom: 14px;   }
    INPUT{font-size: 14px;}
    .dollor_symbol{    padding:10px; font-size:18px; position: relative; padding-right:2px; vertical-align: middle;}
    #idorderAmount{position: absolute;  border-radius: 3px;}
   #idbillingdetails {    font-size: 14px;    margin: 6px;}
    #flip{
    margin: 6px;
    font-size: 14px;
    color: #403c3c;
    }
    #idbtnCancel{    width: 90px;    background: #cccccc;    padding: 8px;border: none;color: white;    font-size: 16px;    border-radius: 4px; -webkit-appearance:none;}
   #idbtnSubmit {    width: 90px;    background: #d83e0f;    padding: 8px;border: none;color: white;    font-size: 16px;    border-radius: 4px; -webkit-appearance:none;}
    #idyear{
    width: 69px;
    height: 39px;
    margin-left: 8px;
    }
    #idmonth{   
    width: 66px;
    margin-left: -3px;
    height: 39px;}
    
     @media only screen 
  and (min-device-width: 120px) 
  and (max-device-width: 480px)
      and (-webkit-min-device-pixel-ratio: 2) {
         
            #idyear{width: 69px;}
            #idmonth{width:88px;margin-left: 0px;}  
           #idcvv { /* margin-right:10%;margin-left: 6px;    margin-top: 6px;*/     margin-right: 3%;}
            .cvvlbl{margin-right:7%;}
          .explbl{    margin-right: 27%;}
    }
    
        @media only screen 
    and (min-device-width:300px) 
    and (max-device-width:700px) 
          
           
           {
    
     #idform { 
       width:100%;
    margin: auto;
    padding:12px;
    
    
    }
    
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
							
						<input name="cardNumber" id="idcardnumber" type="text" value="<?php echo $card_number; ?>" maxlength="19" class="required" autocomplete="off" onkeyup="filterCardNumber(this)" onkeydown="filterCardNumber(this)" onkeypress="filterCardNumber(this)" onclick="shownormalborders(this.id);" style="width:100%;" />
						<div></div>
						<span class="rowColoured log" style="display: none;"></span>
                        </tr>
                        </td>
                    </tr>
                     
                    
 <tr>
                <td>
                    
                            
                            <tr class="rowColoured">
                                 <!--CVV LABEL-->
                                <div class="indent2 cvvlbl" style="    "><b>CVV &nbsp;<span id="asterisc">*</span></b>
                                </div>
                                 <b>
                                      <!--CVV LABEL END-->
                                     
                                      <!--EXPIRY DATE-->
                                <div class="indent2 explbl"><?php echo $_FORM_TEXTS['expirydate']; ?> &nbsp;<span id="asterisc">*</span>
                                </div>
                                </b>
                                     <!--EXPIRY DATE END-->
                                
                                     <!--Amount-->
                                <div class="indent2"><b>Amount &nbsp;<span id="asterisc">*</span></b>
					        </div>
                                     <!--Amount end-->
                            </tr>
                            <tr>   
                                <!--CVV TEXT FIELD-->
                                <input name="cardSecurityCode" id="idcvv" value="<?php echo $cvv_number; ?>" maxlength="4"  class="required" autocomplete="off" onkeyup="filterNumber(this)" onkeydown="filterNumber(this)" onkeypress="filterNumber(this)" onclick="shownormalborders(this.id);" style="width: 50px;" type="<?php echo !empty($cvv_number) ? 'password' : 'text' ?>">
                                 <!--CVV TEXT FIELD END-->
                                
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
                                <span style="" class="dollor_symbol">$</span><input name="orderAmount" id="idorderAmount" value="<?php echo $_PARAMS['amount']; ?>" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width: 50px;" type="text">
							
                                <!--Amount end-->
                                
                            </tr>
                    
                            
                     
                </td>
				
				</tr>
				<tr>
                <br>
                <div style="float: none;    clear: both;    height: 1px;"></div>
							<span class="rowColoured" style="padding-top: 8px;display: block;    white-space: nowrap;">*Deposit amount should be minimum $<?php echo $depositlimits->minimum_deposit_amount/100 ?> and <br/>maximum $<?php echo $depositlimits->maximum_deposit_amount/100; ?></span>
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
                           <tr  >
                           <td colspan="2">
                            <div id="panel" class="indent2">
                            <table>
                                
                                 <tr>
									<td class="rowColoured">
									<br>
										<div class=""><b>Address &nbsp;<span id="asterisc">*</span></b></div>
										<input name="billingaddress" id="idbillingaddress" value="<?php echo !empty($lastbillingdetails->billing_address) ? $lastbillingdetails->billing_address : $playerDetails->address; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width: 140px;" type="text">
									</td>
									<td class="rowColoured">
									<br>
										<div class=""><b>City &nbsp;<span id="asterisc">*</span></b></div>
										<input name="billingcity" id="idbillingcity" value="<?php echo !empty($lastbillingdetails->billing_city) ? $lastbillingdetails->billing_city : $playerDetails->city; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width: 140px;" type="text">
									</td>
								</tr>
								<tr>
									<td class="rowColoured">
									<br>
										<div class=""><b>Country &nbsp;<span id="asterisc">*</span></b></div>
										<?php $countrycode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country; ?>
										<select name="billingcountry" id="idbillingcountry" style="width: 140px; height:38px;" onchange="getstatesList(this.value)">
											<?php foreach($countries as $country): ?>
												<option value="<?php echo $country['country_id'] ?>" <?php echo ($country['country_id'] == $countrycode) ? "selected='selected'" : '' ?>><?php echo $country['name'] ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td class="rowColoured">
									<br>
										<div class=""><b>State &nbsp;<span id="asterisc">*</span></b></div>
										<?php $statecode = !empty($lastbillingdetails->billing_state) ? $lastbillingdetails->billing_state : $playerDetails->state; ?>
										<select name="billingstate" id="idbillingstate" style="width: 140px; height:38px;">
											<?php foreach($states as $state): ?>
												<option value="<?php echo $state['state_code'] ?>" <?php echo ($state['state_code'] == $statecode) ? "selected='selected'" : '' ?>><?php echo $state['state_name'] ?></option>
											<?php endforeach; ?>
										</select>
									</td>
                
									
                    			</tr>
    				<tr>
    				<td class="rowColoured">
    				<br>
										<div class=""><b>Zipcode &nbsp;<span id="asterisc">*</span></b></div>
										<input name="billingzip" id="idbillingzip" value="<?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?>" maxlength="7" class="required" autocomplete="off" onkeyup="filterCardNumber(this)" onkeydown="filterCardNumber(this)" onkeypress="filterCardNumber(this)" onclick="shownormalborders(this.id);" style="width: 140px;" type="text">
                    				</td>
                    				<td>
                    				</td>
    				</tr>
                                <tr>
                                	<td height="20px" colspan="2"><a href="#" id="updateaddress" >Update</a> | <a href="#" id="cancel" >Cancel</a></td>
                                </tr>
                                
                                </table>
                            
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
							<input type="button" name="btnBack" id="idbtnBack" value="<?php echo $_FORM_TEXTS['back']; ?>" class="btnStd" style="width:90px; display:none;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnCancel').disabled=true; document.getElementById('idbtnCancel').disabled=true; location.href='index.php?i=<?php echo $_REQUEST_ID; ?>';" />
							<?php
			

							$signature = hash('sha256', 'CETR'.$_PARAMS['amount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
							
							$ip = $_SERVER['REMOTE_ADDR'];
								
							$arr_post = array(
									"orderNumber"	=> $_PARAMS['id'],
									"orderCurrency"	=> $_PARAMS['currency_id'],
									//"orderAmount"	=> $_PARAMS['amount'],
									"signInfo"		=> $signature,
									"ip"			=> $ip,
									"country"		=> $_PARAMS['country_id']
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
                        	<input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" style="width:45%;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.location.href='<?php echo CRMURL; ?>/Queues/dummydeposits?s=cancelled';" />
                        <?php }else{ ?>
                        <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" style="width:45%;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.location.href='<?php echo $_PARAMS['redirect_url']; ?>?s=cancelled';" />
                        <?php } ?>
                        <input type="submit" name="btnSubmit" id="idbtnSubmit" value="<?php echo $_FORM_TEXTS['proceed']; ?>" class="btnStd" style="width:45%;" />
                    </td>
                </tr>
									
				<tr>
                	<td height="5px" colspan="2"></td>
                </tr>
            </table> 

		</form>
		
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
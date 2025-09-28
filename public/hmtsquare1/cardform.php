<?php 
set_time_limit(1000);
define('PROVIDER_NAME', 'qartpay');

//$_REQUEST_ID = $_POST['i'] ? $_POST['i'] : $_GET['i'];

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
	
	$providerdetails = getProviderDetails($_PARAMS['payment_provider_id']);
 
    $playerDetails = get_player_all_details($_PARAMS['player_id']);

	//get min and max deposit limits
	//$depositlimits = get_deposit_limits($_PARAMS['payment_method_id']);
    $depositlimits = get_deposit_limits_byrisklevels($_PARAMS['payment_method_id'],$playerDetails->players_classes_id);
    $getCurrencyAvailableProcessor = get_currency_available_processor_by_provider($_PARAMS['player_currency_id'], $_PARAMS['payment_provider_id']);
	
	if($_PARAMS['bonuscode']!=''){
		$bonus_details = get_bonus_details($_PARAMS['bonuscode']);
		if($bonus_details->bonuses_redemption_types_id == 1 || $bonus_details->bonuses_redemption_types_id == 2){
		$depositlimits->minimum_deposit_amount = $bonus_details->minimum_deposit_amount*100;
		}
	}
	
    ?>
	<link type="text/css" href="../qartpay/css/styles.css" rel="stylesheet"/>
        <link type="text/css" href="../qartpay/css/messi.css" rel="stylesheet"/>
        <style>
        	.messi{
        		top: 15px !important;
        	}
        </style>
	    <script language="javascript" src="../qartpay/js/jquery.min.js"></script>
        <script language="javascript" src="../qartpay/js/messi.min.js"></script>
		
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
            
            function filterZipCode(zipcode) {
            	var ValidChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ";
                var Char;
                                
                for (i = 0; i < zipcode.value.length; i++) {
                    Char = zipcode.value.charAt(i);
                    if (ValidChars.indexOf(Char) == -1) {
                        zipcode.value = zipcode.value.replace(Char,'');
                    }
                }
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
            	var minamount = '<?php echo $depositlimits->minimum_deposit_amount/100; ?>';
            	var maxamount = '<?php echo $depositlimits->maximum_deposit_amount/100; ?>';
            	var checkalphanum = /^[a-z0-9 ]+$/i;
            	var checkname = /^[A-Za-z ]+$/;
            	var checkphnum = /^[0-9+ ]+$/i;
            	<?php 
            	if($getCurrencyAvailableProcessor->count > 0)
            	{
            	?>
            	if (!f.cardName.value || !f.cardNumber.value || f.cardNumber.value.length<13 || !f.cardSecurityCode.value || f.cardSecurityCode.value.length<3 || !f.cardExpireMonth.value || !f.cardExpireYear.value || (f.cardName.value.match(checkname) == null) || !f.orderAmount.value || f.orderAmount.value < parseInt(minamount)  || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value)) ){
            	<?php
				}
				else {
				?>	
				if (!f.cardName.value || !f.cardNumber.value || f.cardNumber.value.length<13 || !f.cardSecurityCode.value || f.cardSecurityCode.value.length<3 || !f.cardExpireMonth.value || !f.cardExpireYear.value || (f.cardName.value.match(checkname) == null) || !f.convertedOrderAmount.value || f.convertedOrderAmount.value < parseInt(minamount)  || f.convertedOrderAmount.value > parseInt(maxamount) || (isNaN(f.convertedOrderAmount.value)) ){
				<?php		
				}
            	?>	
            	
            		
	               	if (!f.orderAmount.value){
	                    showerrorborders('idorderAmount');
	                }
	                /*if (f.orderAmount.value && (f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount))){
	                    showerrorborders('idorderAmount');
	                    showerroralert('Deposit amount should be min '+minamount+'$ and max '+maxamount+'$');
	                }*/
	                 if($('#idconvertedOrderAmount').length){
                        if (!f.convertedOrderAmount.value || f.convertedOrderAmount.value < parseInt(minamount)  || f.convertedOrderAmount.value > parseInt(maxamount) || (isNaN(f.convertedOrderAmount.value))){
                            if (!f.convertedOrderAmount.value){
                                showerrorborders('idconvertedOrderAmount');
                            }
                            if (f.convertedOrderAmount.value && (f.convertedOrderAmount.value < parseInt(minamount) || f.convertedOrderAmount.value > parseInt(maxamount))){
                                showerrorborders('idconvertedOrderAmount');
                               // showerroralert('Deposit amount should be min '+minamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?> and max '+maxamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?>');
                            }
                            if(f.convertedOrderAmount.value && isNaN(f.convertedOrderAmount.value)){
                                showerrorborders('idconvertedOrderAmount');
                                //showerroralert('Please enter valid amount');
                            }
                            return false;
                        }   
                    }
                    else
                    {
                        if (!f.orderAmount.value || f.orderAmount.value < parseInt(minamount)  || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value))){
                            if (!f.orderAmount.value){
                                showerrorborders('idorderAmount');
                            }
                            if (f.orderAmount.value && (f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount))){
                                showerrorborders('idorderAmount');
                                //showerroralert('Deposit amount should be min '+minamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?> and max '+maxamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?>');
                                //showerroralert('Deposit amount should be min '+minamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol["html_symbol"] : '$' ?> and max '+maxamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol["html_symbol"] : '$' ?>');
                                
                               
                                // Code commented
                                // showerroralert('Deposit amount should be min '+minamount+'<?php // echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?>');
                                // End code commented
                            }
                            if(f.orderAmount.value && isNaN(f.orderAmount.value)){
                                showerrorborders('idorderAmount');
                                //showerroralert('Please enter valid amount');
                            }
                            return false;
                        }
                    }
	               /* if(f.orderAmount.value && isNaN(f.orderAmount.value)){
	                	showerrorborders('idorderAmount');
	                	showerroralert('Please enter valid amount');
                	}*/
                	    if (!f.cardName.value){
                            showerrorborders('idcardname');
                        }
                        if(f.cardName.value && f.cardName.value.length<3){
                        	showerrorborders('idcardname');
                        	//showerroralert('Please enter proper Name');
                        }
                        if (f.cardName.value && f.cardName.value.match(checkname) == null){
                        	showerrorborders('idcardname');
                        	//showerroralert('Please enter valid Name');
                        }
                        if (!f.cardNumber.value || f.cardNumber.value.length<13){
                            showerrorborders('idcardnumber');
                        }
                        if (f.cardNumber.value && f.cardNumber.value.match(re) == null){
                        	showerrorborders('idcardnumber');
                        	//showerroralert('Please enter valid card number');
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
                	/*if (!f.billingname.value){
                        showerrorborders('idbillingname');
                        $("#flip").click();
                    }
                    if (f.billingname.value.match(checkname) == null){
                        showerrorborders('idbillingname');
                        $("#flip").click();
                    }
                    if (f.billingname.value.length < 2){
                        showerrorborders('idbillingname');
                        $("#flip").click();
                    }
                    if (f.billingname.value.length > 32){
                        showerrorborders('idbillingname');
                        $("#flip").click();
                    }
                    if (!f.billinglastname.value){
                        showerrorborders('idbillinglastname');
                        $("#flip").click();
                    }
                    if (f.billinglastname.value.match(checkname) == null){
                        showerrorborders('idbillinglastname');
                        $("#flip").click();
                    }
                    if (f.billinglastname.value.length < 2){
                        showerrorborders('idbillinglastname');
                        $("#flip").click();
                    }
                    if (f.billinglastname.value.length > 32){
                        showerrorborders('idbillinglastname');
                        $("#flip").click();
                    }
                	if (!f.billingaddress.value){
                        showerrorborders('idbillingaddress');
                        $("#flip").click();
                    }
                    if (f.billingaddress.value.length < 5){
                        showerrorborders('idbillingaddress');
                        $("#flip").click();
                    }
                    if (f.billingaddress.value.length > 128){
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
                    if (f.billingcity.value.length < 2){
                        showerrorborders('idbillingcity');
                        $("#flip").click();
                    }
                    if (!f.billingcountry.value){
                        showerrorborders('idbillingcountry');
                        $("#flip").click();
                    }
                    if (f.billingcountry.value.length < 2){
                        showerrorborders('idbillingcountry');
                        $("#flip").click();
                    }
                    if (!f.billingstate.value){
                        showerrorborders('idbillingstate');
                        $("#flip").click();
                    }
                    if (f.billingstate.value.length < 2){
                        showerrorborders('idbillingstate');
                        $("#flip").click();
                    }
                    if (!f.billingzip.value){
                        showerrorborders('idbillingzip');
                        $("#flip").click();
                    }
                    if (f.idbillingzip.value && (f.idbillingzip.value.length < 4 || f.idbillingzip.value.length > 10)){
                        showerrorborders('idbillingzip');
                        $("#flip").click();
                    }
                	if (!f.billingphone.value){
                        showerrorborders('idbillingphone');
                        $("#flip").click();
                    }
                    if (f.billingphone.value && f.billingphone.value.match(checkphnum) == null){
                        showerrorborders('idbillingphone');
                        $("#flip").click();
                    }
                    if (f.billingphone.value.length < 7){
                        showerrorborders('idbillingphone');
                        $("#flip").click();
                    }
                    if (f.billingphone.value.length > 18){
                        showerrorborders('idbillingphone');
                        $("#flip").click();
                    }*/
                	return false;
                }	

                document.getElementById('idbtnSubmit').disabled=true;
                document.getElementById('idbtnCancel').disabled=true;
                
                return true;
            }
            
            
            </script>
            
            <!-----  Update or cancel billing details  ---->
            
           <script> 
			$(document).ready(function(){
			    $("#flip").click(function(){
			   // $('.qbdiframe', window.parent.document).height('1110px');
			        
	    	    window.top.postMessage(1460 + '-' + 'iframe1', "*");
	    	
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
			    	window.top.postMessage(950 + '-' + 'iframe2', "*");
			    	var optionctryValue  = $("#plbillcount").text();
			        getstatesList(optionctryValue);
			        $("#panel").slideUp("slow");
			        $("#flip").text('Change');
			        $("#idbillingname").val($("#plbillname").text());
			        $("#idbillinglastname").val($("#plbilllastname").text());
			        $("#idbillingaddress").val($("#plbilladd").text());
			        $("#idbillingcity").val($("#plbillcit").text());
			        $("#idbillingzip").val($("#plbillzip").text());
			        $("#idbillingphone").val($("#plbillphone").text());
			        $("#idbillingcountry").val(optionctryValue).find("option[value=" + optionctryValue +"]").attr('selected', true);
			        var optionValue  = $("#plbillsta").text();
			        setTimeout(function(){
						$("#idbillingstate").val(optionValue).find("option[value=" + optionValue +"]").attr('selected', true);
					}, 1000);
					
					
	    	    	
	    	
					//$('.qbdiframe', window.parent.document).height('650px');
			    });
			    $("#updateaddress").click(function(){
			    	window.top.postMessage(950 + '-' + 'iframe2', "*");
			    	var name = $("#idbillingname").val();
			    	var lastname = $("#idbillinglastname").val();
			    	var add = $("#idbillingaddress").val();
			    	var cit = $("#idbillingcity").val();
			    	var ctry = $("#idbillingcountry").val();
			    	var sta = $("#idbillingstate").val();
			    	var zip = $("#idbillingzip").val();
			    	var bphone = $("#idbillingphone").val();
			    	$("#plbillname").text(name);
			    	$("#plbilllastname").text(lastname);
			    	$("#plbilladd").text(add);
			    	$("#plbillcit").text(cit);
			    	$("#plbillcount").text(ctry);
			    	$("#plbillsta").text(sta);
			    	$("#plbillzip").text(zip);
			    	$("#plbillphone").text(bphone);
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
			<!-----  Update or cancel billing details  ---->
<?php
    /*** Multi currency code ***/
    
    // echo "<pre>"; echo $_PARAMS['player_currency_id']." || ".$_PARAMS['payment_provider_id']."<br>"; print_r($getCurrencyAvailableProcessor); exit;
    if($getCurrencyAvailableProcessor->count > 0){
        $converted_amount = convert_currency($_PARAMS['player_currency_id'], DEFAULT_CURRENCY, $_PARAMS['amount']/100);
        $converted_amount = round($converted_amount, 2);
        $conversionrate = get_conversion_rate(DEFAULT_CURRENCY, $_PARAMS['player_currency_id']);
        $currencysymbol = get_currency_symbol($_PARAMS['player_currency_id']);
            
    }else{
        $converted_amount = convert_currency($_PARAMS['player_currency_id'], DEFAULT_CURRENCY, $_PARAMS['amount']/100);
        $converted_amount = round($converted_amount, 2);
        $conversionrate = get_conversion_rate($_PARAMS['player_currency_id'], DEFAULT_CURRENCY);
        $currencysymbol = get_currency_symbol($_PARAMS['player_currency_id']);
    }
    
    /*** Multi currency code ***/
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	$action = VALIDATOR;
	if ($_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		//getting last billing details
		$lastbillingdetails = get_last_billing_details($_PARAMS['player_id']);
		//get countries list
		$countries = get_countries_list($_PARAMS['web_id']);
		//getting states code
		$countryCode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country;
		$states = get_states_code($countryCode);
?>
<style>

#idorderAmount{
 	width: 132px;
    font-size: 14px;
    padding-left: 6px;
    height: 41px;
    display: inline-block;
}
INPUT{
	vertical-align: top !important;
}

     .qb_direct_imp {
width: 97%;height:auto;border:1px solid #ff2600; font-size:13px; line-height:20px;padding: 5px;border-radius: 3px;
}
.imp_direct {
width: 14%;
    display: inline-block;
    vertical-align: top;
    color: red;
    font-weight: bold;
}
.imp_text {
    display: inline-block;
    width: 82%;
}
@media only screen 
  and (min-width:300px) 
  and (max-width:560px)
   {
   .imp_direct {
    display: block;
}
   }

#idbillingname,  #idbillinglastname, #idbillingaddress, #idbillingcity, #idbillingzip { padding:8px; } 
   #idbillingcountry, #idbillingstate { padding:8px; height:34px; }


#panel {
    display: none;
}
#idbillingdetails {

       padding-left: 0px;
    font-size: 13px;
    padding-top: 4px;
    display: inline-block;
    
    }
    
    #idbillingdetails {
     display: inline-block;
     }
    

.formTable .address a{

    color: red;
    font-size: 11px;
    text-decoration: none;
    padding: 1px 0px 0px 4px;
    position: relative;
    top: 4px;


}

 a#flip {
    color: #ccc;
    padding-left:0px;
    padding-top: 5px;
    font-size: 13px;
    text-decoration:underline;
    display:block;
}

.billing_add {
font-size: 12px;
    width: 125px;
    display: inline-block;
    
    }
.billing_name {
    padding-top: 1px;
    top: -7px;
    position: relative;
    }


a#updateaddress, a#cancel{color: red; font-size: 11px; text-decoration: none;}
	@media only screen 
	  and (min-width:200px) 
	  and (max-width:560px)
	  {
  
  #idorderAmount{
	width:132px !important;
	padding:12.3px;
	font-size: 14px;
	margin-bottom:12px;
	}
	    
	a#flip {
   color: red;
    padding-left: 4px;
    position: relative;
    top: 4px;
    text-decoration: none;
	}
	
	#idbillingdetails {
	    display: block;
	    padding-left: 0px;
	}
	.billing_name {
	    padding-top:1px;
	    top: 1px;
	    position: relative;
	}

  
  }
</style>
		<div class="content" style="">
            <div class="text_quickbits">
            <p style="color:#fff;font-weight: bold;font-size:20px;margin-bottom:30px;"><span id="asterisc">*</span> Deposit with your Debit Card</p>
            <!-- <ol>
                <li>Enter your deposit amount</li>
		        <li>Provide your <b>Debit Card</b> details for the purchase </li>
			    <li> Wait for purchase to complete. Don't hit the back button</li>
			 </ol> -->
			 <?php if($_PARAMS['bonuscode']){ ?>
                <h2 style="background:#3d3c78;color:#fff;padding:10px;">Applied Bonus Code is : <?php echo $_PARAMS['bonuscode']; ?></h2>
			 <?php } ?>
			
			
            
           
            </div>
		
			<form id="idform" action='<?php echo $action; ?>' method='POST' onSubmit="return checkform(this);">
				<table width="100%" cellspacing="1" cellpadding="0" class="formTable">

                    <!-- <tr>
	                    <td style="font-size: 14px; color: #ccc;">
	                    Click on proceed button to make deposit
	                    </td>
                    </tr> -->
                    <div>
                      <tr>
                        <td>
                        <tr class="rowColoured">
                            <div class="form_main_div">
                                <div class="col-two">
                                    <div class="indent2"><?php echo $_FORM_TEXTS['cardname']; ?>&nbsp;<span id="asterisc">*</span></div>
                                    <input name="cardName" id="idcardname" type="text" value="<?php echo $nameOnCard; ?>" class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width:100%;" />
                                </div>
                                <div class="col-two">
                                    <div class="indent2"><?php echo $_FORM_TEXTS['cardnumber']; ?> &nbsp;<span id="asterisc">*</span></div>
                                    <input name="cardNumber" id="idcardnumber" type="text" value="<?php echo $card_number; ?>" maxlength="16" class="required" autocomplete="off" onkeyup="filterCardNumber(this)" onkeydown="filterCardNumber(this)" onkeypress="filterCardNumber(this)" onclick="shownormalborders(this.id);" style="width:100%;" />
                                </div>
                                <div style="clear:both;"></div>
                            </div>
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
                                <input name="orderAmount" id="idorderAmount" value="<?php echo $amount; ?>" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width:122px;" type="text">
								<input type="hidden" name="supportcurpspcount" id="supportcurpspcount" value="<?php echo $getCurrencyAvailableProcessor->count; ?>" />

                                <!-- Start Code -->
                                <?php if($getCurrencyAvailableProcessor->count == 0){ ?>
							    <span style="font-size: 18px;margin-right: 0;display: inline-block;padding: 1px 10px;background: #ccc;border-radius: 5px 0px 0px 5px;vertical-align: top;" class="dollor_symbol"><?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$"; ?></span>
							    <input name="convertedOrderAmount" id="idconvertedOrderAmount" style="width: 110px;padding: 11px 11px 12px;font-size: 14px;margin-left: -3px;" value="<?php echo $converted_amount; ?>" maxlength="6" readonly type="text">
							    <?php } ?>
						    	<br>
                                <!-- End Code -->

								<?php //if(DEFAULT_CURRENCY != $_PARAMS['player_currency_id']){ ?>
								<?php //if($getCurrencyAvailableProcessor->count == 0){ ?>
								<!--span style="color: #000!important; font-size:13px;" class="dollor_symbol"><?php //echo $currencysymbol['html_symbol']; ?></span-->
								<!--input name="convertedOrderAmount" id="idconvertedOrderAmount" value="<?php // echo $converted_amount; ?>" maxlength="6" readonly style="width: 70px; cursor: default; background: #e8e8e8;" type="text" -->
								<?php //} ?>
							
                                <!--Amount end-->
									</div>
									
					       		</div>
                                     <!--Amount end-->
                                     <span style="font-size:13px;display:block;line-height:20px;">*Deposit amount should be minimum <?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : '$'; ?><?php echo $depositlimits->minimum_deposit_amount/100 ?> and maximum <?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : '$'; ?><?php echo $depositlimits->maximum_deposit_amount/100; ?>.</span>
						    	<!-- <span class="note_text" style="font-size:13px;display:block;line-height:20px;margin-top:5px;"><b>Note:</b> Please make sure that you unblock or disable the pop ups in your browser to make sure that your attempt is successful. Also, ensure that you allow the notifications for this site.</span> -->
					</div>
								
								
								
								
                            </tr>
                           
                    
                            
                     
                </td>
				
				</tr>
					</div>
									
					<!--<tr valign="top" class="address">
						<td class="rowColoured" >
						<div  class="indent2 "><strong style="font-size:12px;" class="billing_add">Card holder Name : </strong>
						<div id="idbillingdetails">
								<span id="plbillname"><?php echo !empty($lastbillingdetails->billing_first_name) ? $lastbillingdetails->billing_first_name : $playerDetails->mstrname; ?></span>,
                        		<span id="plbilllastname"><?php echo !empty($lastbillingdetails->billing_last_name) ? $lastbillingdetails->billing_last_name : $playerDetails->last_name; ?></span>
							</div>
						</div>
							
						</td>
						</tr>
						<tr>
                        <td class="rowColoured" >
                        <div  class="indent2 billing_name"><strong style="font-size:12px;" class="billing_add"> Billing Address : </strong>
                        
                        <div id="idbillingdetails">
                        
                        <span id="plbilladd"><?php echo !empty($lastbillingdetails->billing_address) ? $lastbillingdetails->billing_address : $playerDetails->address; ?></span>,
                         <span id="plbillcit"><?php echo !empty($lastbillingdetails->billing_city) ? $lastbillingdetails->billing_city : $playerDetails->city; ?></span>,
                         <span id="plbillcount"><?php echo !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country; ?></span>,
                          <span id="plbillsta"><?php echo !empty($lastbillingdetails->billing_state) ? $lastbillingdetails->billing_state : $playerDetails->state; ?></span>,
                          <span id="plbillzip"><?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?></span>,
                          <span id="plbillphone"><?php echo !empty($lastbillingdetails->billing_phone) ? $lastbillingdetails->billing_phone : $playerDetails->mobileno; ?></span><br>
                           </div>
                           </div>
                            <a href="#" id="flip" >Change</a> 
                        </td>
                        <td style="font-size:12px; white-space:normal !important;">
                        
                           <tr>
                           <td colspan="2">
							   
							   
                            <div id="panel" class="indent2">
								
							<div class="add_main_div">
								
							<div class="add_1">
									<div class=""><b><span id="asterisc">*</span>&nbsp;Card holder first name</b></div>
							<input name="billingname" id="idbillingname" value="<?php echo !empty($lastbillingdetails->billing_first_name) ? $lastbillingdetails->billing_first_name : $playerDetails->mstrname; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" type="text">
							</div>
								
							<div class="add_1">
								<div class=""><b><span id="asterisc">*</span>&nbsp;Card holder last name</b></div>
							<input name="billinglastname" id="idbillinglastname" value="<?php echo !empty($lastbillingdetails->billing_last_name) ? $lastbillingdetails->billing_last_name : $playerDetails->last_name; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  type="text">
								
								</div>	
								
								
								<div class="add_1">
										<div class=""><b><span id="asterisc">*</span>&nbsp;Address</b></div>
							<input name="billingaddress" id="idbillingaddress" value="<?php echo !empty($lastbillingdetails->billing_address) ? $lastbillingdetails->billing_address : $playerDetails->address; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  type="text">
								</div>
								
								<div class="add_1">
										<div class=""><b><span id="asterisc">*</span>&nbsp;City</b></div>
							<input name="billingcity" id="idbillingcity" value="<?php echo !empty($lastbillingdetails->billing_city) ? $lastbillingdetails->billing_city : $playerDetails->city; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  type="text">
								</div>
								
							<div class="add_1">
									<div class=""><b><span id="asterisc">*</span>&nbsp;Country </b></div>
						<?php $countrycode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country; ?>
						<select name="billingcountry" id="idbillingcountry" onchange="getstatesList(this.value)">
							<?php foreach($countries as $country): ?>
								<option value="<?php echo $country['country_id'] ?>" <?php echo ($country['country_id'] == $countrycode) ? "selected='selected'" : '' ?>><?php echo $country['name'] ?></option>
							<?php endforeach; ?>
						</select>
								</div>
								
							<div class="add_1">
								<div class=""><b><span id="asterisc">*</span>&nbsp;State</b></div>
							<?php $statecode = !empty($lastbillingdetails->billing_state) ? $lastbillingdetails->billing_state : $playerDetails->state; ?>
							<select name="billingstate" id="idbillingstate" >
								<?php foreach($states as $state): ?>
									<option value="<?php echo $state['state_code'] ?>" <?php echo ($state['state_code'] == $statecode) ? "selected='selected'" : '' ?>><?php echo $state['state_name'] ?></option>
								<?php endforeach; ?>
							</select>
							</div>	
								
							<div class="add_1">
								<div class=""><b><span id="asterisc">*</span>&nbsp;Zipcode</b></div>
							<input name="billingzip" id="idbillingzip" value="<?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?>" maxlength="12" class="required" autocomplete="off" onkeyup="filterZipCode(this)" onkeydown="filterZipCode(this)" onkeypress="filterZipCode(this)" onclick="shownormalborders(this.id);" type="text">
							</div>
							<div class="add_1">
								<div class=""><b><span id="asterisc">*</span>&nbsp;Phone</b></div>
							<input name="billingphone" id="idbillingphone" value="<?php echo !empty($lastbillingdetails->billing_phone) ? $lastbillingdetails->billing_phone : $playerDetails->mobileno; ?>" maxlength="12" class="required" autocomplete="off" onclick="shownormalborders(this.id);" type="text">
							</div>
								
							<div class="add_1 update_cancel">	
								<a href="#" id="updateaddress" >Update</a> | <a href="#" id="cancel" >Cancel</a>
							</div>	
								
								
							</div>
							
                            </div>
                            </td>
                            </tr>
						</td>
					</tr>-->
							
					<tr>
                    	<td height="10px" colspan="2"></td>
                    </tr>
                            
                            <tr>
					<td>
					<!-- <div class="qb_direct_imp">
						<span class="imp_direct">IMPORTANT:</span> <span class="imp_text">First QBD depositors may be required to enter the last 4 digits of their Social Security Number to ensure their account security</span>
					</div> -->
					</td>
					</tr>
                                    
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

                            <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.parent.location.href='<?php echo REDIRECTURL; ?>-failed';" />
                            <input type="submit" name="btnSubmit" id="idbtnSubmit" value="<?php echo $_FORM_TEXTS['proceed']; ?>" class="btnStd" />
                        </td>
                    </tr>
									
					<tr>
                    	<td height="20px" colspan="2"></td>
                    </tr>
                </table> 

		</form>
	</div>
<?php		
		
		
		
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
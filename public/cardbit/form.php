<?php

define('PROVIDER_NAME', 'cardbit');

$_REQUEST_ID = isset($_POST['i']) ? $_POST['i'] : $_GET['i'];



include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
if(isset($_POST['ajax_conversion']) && $_POST['ajax_conversion'] == 1){
	//get states list
	$fromcurrency = $_POST['from'];
	$tocurrency = $_POST['to'];
	$amount = $_POST['amount'];
	
	$converted_amount = convert_currency($fromcurrency, $tocurrency, $amount);
	$converted_amount = round($converted_amount, 2);
	echo $converted_amount;
}

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

	include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/langs.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	//get min and max deposit limits
	$depositlimits = get_deposit_limits($_PARAMS['payment_method_id']);
	
	
	

	
	?>
		
		<link type="text/css" href="css/styles.css" rel="stylesheet"/>
        <link type="text/css" href="css/messi.css" rel="stylesheet"/>
	    <script language="javascript" src="js/jquery.min.js"></script>
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
                
            // function checkform(f){
            //	var minamount = '<?php //echo $depositlimits->minimum_deposit_amount/100; ?>';
            //	var maxamount = '<?php //echo $depositlimits->maximum_deposit_amount/100; ?>';
            	
            //	if (!f.orderAmount.value || f.orderAmount.value < parseInt(minamount)  || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value)) || !f.useraccountid.value){
            		
	        //       	if (!f.useraccountid.value){
	        //            showerrorborders('useraccountid');
	        //            showerroralert('Please enter your account id');
	        //        }
	        //       	if (!f.orderAmount.value){
	        //            showerrorborders('idorderAmount');
	        //        }
	        //        if (f.orderAmount.value && (f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount))){
	        //            showerrorborders('idorderAmount');
	        //            showerroralert('Deposit amount should be min '+minamount+'$ and max '+maxamount+'$');
	        //        }
	        //        if(f.orderAmount.value && isNaN(f.orderAmount.value)){
	        //        	showerrorborders('idorderAmount');
	        //        	showerroralert('Please enter valid amount');
            //    	}
            //    	return false;
            //    }	

            //    document.getElementById('idbtnSubmit').disabled=true;
            //    document.getElementById('idbtnCancel').disabled=true;
                
            //    return true;
            //}
            
            
           
           function checkform(f){
            	var minamount = '<?php echo $depositlimits->minimum_deposit_amount/100; ?>';
            	var maxamount = '<?php echo $depositlimits->maximum_deposit_amount/100; ?>';
            	var checkalphanum = /^[a-z0-9 ]+$/i;
            	var checkname = /^[A-Za-z ]+$/;
            	
				if (!f.orderAmount.value || f.orderAmount.value < parseInt(minamount)  || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value)) || !f.billingname.value || (f.billingname.value.match(checkname) == null) || (f.billingname.value.length < 2) || !f.billinglastname.value || (f.billinglastname.value.match(checkname) == null) || (f.billinglastname.value.length < 2) || !f.billingaddress.value || (f.billingaddress.value.length < 5) || !f.billingcity.value || !f.billingzip.value || !f.billingstate.value || !f.billingcountry.value){
	               	if (!f.orderAmount.value){
	                    showerrorborders('idorderAmount');
	                     //showerrorborders('idorderAmount1');
	                }
	                if (f.orderAmount.value && (f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount))){
	                    showerrorborders('idorderAmount');
	                     //showerrorborders('idorderAmount1');
	                    //showerroralert('Deposit amount should be min '+minamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "€" ?> and max '+maxamount+'<?php //echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "€" ?>');
	                    showerroralert('Deposit amount should be min '+minamount+'<?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?>');
	                }
	                if(f.orderAmount.value && isNaN(f.orderAmount.value)){
	                	showerrorborders('idorderAmount');
	                	 //showerrorborders('idorderAmount1');
	                	showerroralert('Please enter valid amount');
                	}
                	if (!f.billingname.value){
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
                	if (!f.billingaddress.value){
                        showerrorborders('idbillingaddress');
                        $("#flip").click();
                    }
                    if (f.billingaddress.value.length < 5){
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
                    if (f.idbillingzip.value && (f.idbillingzip.value.length < 4 || f.idbillingzip.value.length > 10)){
                        showerrorborders('idbillingzip');
                        showerroralert('Please enter valid zipcode');
                        $("#flip").click();
                    }
                	return false;
                }
				

                document.getElementById('idbtnSubmit').disabled=true;
                document.getElementById('idbtnCancel').disabled=true;
                
                return true;
            }
            
            function currencyconvert(){
	
            	var from = '<?php echo $_PARAMS['player_currency_id'] ?>';
            	var to = "EUR";
            	var amount =  $("#idorderAmount").val();
            	//alert(amount);
            	$.ajax({
			  		type:'post',
			  		url:'form.php',
			  		data:'amount='+amount+'&from='+from+'&to='+to+'&ajax_conversion=1',
			  		success:function(result){
			  			$("#idorderAmount1").val(result);
			  		}
			  	});
            	
}
            
            
            
            
            </script>
            <!-----  Update or cancel billing details  ---->
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
			        $("#idbillingname").val($("#plbillname").text());
			        $("#idbillinglastname").val($("#plbilllastname").text());
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
			    	var name = $("#idbillingname").val();
			    	var lastname = $("#idbillinglastname").val();
			    	var add = $("#idbillingaddress").val();
			    	var cit = $("#idbillingcity").val();
			    	var ctry = $("#idbillingcountry").val();
			    	var sta = $("#idbillingstate").val();
			    	var zip = $("#idbillingzip").val();
			    	$("#plbillname").text(name);
			    	$("#plbilllastname").text(lastname);
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
			<!-----  Update or cancel billing details  ---->
       <style>
           .main-container {	    font-family: 'Roboto', sans-serif;}
		.main-container{margin:0px auto; text-align:center;}
		.upaycard-block{margin:0px auto;  width:360px; max-width:78%;}
		.upaycard-block p{font-size:18px; }
		.upaycard-form{ background:#f3f3f5; border:2px solid #d83e0f; padding:30px; }
		.upaycard-form form{box-sizing:border-box;}
		.upaycard-form input{padding:10px; margin:10px 0px; width:100%; box-sizing:border-box;}
		.upay-btn{background:#d83e0f; width:100%; padding:10px 0px; margin:20px 0px; border-radius: 5px; text-decoration:none; color:#fff; font-size:16px; display:block; text-align:center;}
		.upay-btn:hover{background:#333;}
		.clearfix{clear:both; float:none;}
           #idbtnSubmit{width:30%; float: left;}
           #idbtnCancel{
    width:30%;
    float: left;
    margin-left: 5px;
    }
           #spaniput{
           padding: 0px;
    position: absolute;
    margin-left: 7px;
    margin-top:-9px;
           }
           #spaniput input {
               padding:12px 10px;
    border-radius: 2px;
           }
            .cardbit_container {           
               margin: auto;
    background: #ffffff;
    /*border: 3px solid #a09f9f;*/
    padding: 30px;
    border-radius: 3px;
    width: 80%;
    margin-top: 25px;
    }
    
   
          
          .clearfix {
          clear:both;
          display:block;
          } 
           
           
            @media only screen and (max-width: 480px) and (min-width:300px)
                 {
           
                     .upaycard-block{    max-width: 82%;}
                     
                      .cardbit_container {   
                      width:79%;
                      margin:auto;
                      margin-top:20px;
                      }                     
                     
                 }
         
		 .acc_h{    margin-bottom: 0px;    font-size: 13px !important;    text-align: left;}
           #useraccountid{margin-top: 1px;margin-bottom: 4px}
           #idorderAmount{margin-top:0px;margin-bottom: 4px}
           #idbtnCancel{    background: #cccccc;}
           
           
           body>.container-fluid{
           	margin:3px !important;
           }
           
   #panel {
    display: none;
}
#idbillingdetails {

       padding-left: 11px;
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
    padding-left: 130px;
    padding-top: 5px;
    font-size: 13px;
    text-decoration: none;
}

.billing_add {
font-size: 12px;
    width: 115px;
    display: inline-block;
    
    }
.billing_name {
    padding-top: 1px;
    top: -7px;
    position: relative;
    }


a#updateaddress, a#cancel{color: red; font-size: 13px; text-decoration: none;}
@media only screen 
  and (min-width:300px) 
  and (max-width:560px)
  {
  
  #idorderAmount{
width:150px !important;
padding:12.3px;
font-size: 14px;
}
   
   #idbillingname,  #idbillinglastname, #idbillingaddress, #idbillingcity, #idbillingzip { padding:8px; } 
   #idbillingcountry, #idbillingstate { padding:8px; height:34px; }
   
   .indent2 { padding-left:0; }
   
    
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
    padding-top: 1px;
    top: 1px;
    position: relative;
}

  
  }   
  
  .labe-new {font-size:13px;padding:2px 0;}     
		</style>
		
		
	<?php 
	
	
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	//print_r($_PARAMS);exit;
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		/*   METHOD START   */
		
		$action = VALIDATOR;
		
		//getting last billing details
		$lastbillingdetails = get_last_billing_details($_PARAMS['player_id']);
		//get countries list
		$countries = get_countries_list($_PARAMS['web_id']);
		//getting states code
		$countryCode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country;
		$states = get_states_code($countryCode);
		
		  $from = $_PARAMS['player_currency_id'];
		  $to= 'EUR';
		  $amount=$_PARAMS['amount'];
		  
		  $currency_in_euros = convert_currency($from,$to,$amount);	
		  
		?>
		
		
		
		<div class="main-container cardbit_container" style="padding:30px;">
			<form id="idform" action='<?php echo $action; ?>' method='POST' onSubmit="return checkform(this);">
			<p style="text-align:left; font-size:15px;">Click on Pay button to make deposit through Cbit</p>
				<div> 
					
	<p class="acc_h"><label style="width:70px;display: inline-block;">Amount <span style="color:red;">*</span> </label>  
	<span id="spaniput">
    <span style="font-size: 18px;display: inline-block;padding: 11px 8px 10px;background: #ccc;border-radius: 5px 0px 0px 5px;vertical-align: bottom;top: -4px;position: relative;left:4px;" class="indent2">$</span>
    <input name="orderAmount" id="idorderAmount" value="<?php echo $_PARAMS['amount']; ?>" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" onkeyup="currencyconvert()" type="text">
    </span>
    </p>
    
    
    <br><br>
    <p class="acc_h"><label style="width:70px;display: inline-block;"> Amount in<span style="color:red;">*</span> </label>
    <span id="spaniput"> 
    <span style="font-size: 18px;display: inline-block;padding:10px 8px 9px;background: #ccc;border-radius: 5px 0px 0px 5px;vertical-align: bottom;top:0px;position: relative;left: 4px;" class="indent2">€</span>
    <input name="orderAmount1" id="idorderAmount1" value="<?php echo $currency_in_euros; ?>" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  type="text" readonly="readonly"> 
    </span>
    </p>                                     
					<br><br> <span style="font-size:10px; font-weight:bold; text-align:left; display:block;">*Deposit amount should be minimum $<?php echo $depositlimits->minimum_deposit_amount/100 ?> and maximum $<?php echo $depositlimits->maximum_deposit_amount/100; ?></span>
					<span style="font-size:10px; font-weight:bold; text-align:left; display:block;">*Multiple transactions allowed</span>
				</div>
				<table width="100%" cellspacing="1" cellpadding="0" class="formTable">
				<tr valign="top" class="address">
					<br>
					
						<td class="rowColoured" >
						<div  class="indent2 "><strong style="font-size:12px;" class="billing_add">Card holder Name : </strong>
						<div id="idbillingdetails">
								<span id="plbillname"><?php echo !empty($lastbillingdetails->billing_first_name) ? $lastbillingdetails->billing_first_name : $playerDetails->realname; ?></span>,
                        		<span id="plbilllastname"><?php echo !empty($lastbillingdetails->billing_last_name) ? $lastbillingdetails->billing_last_name : $playerDetails->reallastname; ?></span>
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
                          <span id="plbillzip"><?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?></span><br>
                           </div>
                           </div>
                            <a href="#" id="flip" >Change</a> 
                        </td>
                        <td style="font-size:12px; white-space:normal !important;">
                        
                           <tr>
                           <td colspan="2">
                            <div id="panel" class="indent2" style="padding-left:0;padding-top:15px;">
                            <table>
                     <tr>
                     	<td class="rowColoured">
							<div class="labe-new">Card holder first name <span id="asterisc">*</span></div>
							<input name="billingname" id="idbillingname" value="<?php echo !empty($lastbillingdetails->billing_first_name) ? $lastbillingdetails->billing_first_name : $playerDetails->realname; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width:120px;" type="text">
						</td>
						<td class="rowColoured">
							<div class="labe-new">Card holder last name <span id="asterisc">*</span></div>
							<input name="billinglastname" id="idbillinglastname" value="<?php echo !empty($lastbillingdetails->billing_last_name) ? $lastbillingdetails->billing_last_name : $playerDetails->reallastname; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width:120px;" type="text">
						</td>
					</tr>       
                     <tr>
						<td class="rowColoured">
							<div class="labe-new">Address <span id="asterisc">*</span></div>
							<input name="billingaddress" id="idbillingaddress" value="<?php echo !empty($lastbillingdetails->billing_address) ? $lastbillingdetails->billing_address : $playerDetails->address; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width:120px;" type="text">
						</td>
						<td class="rowColoured">
							<div class="labe-new">City <span id="asterisc">*</span></div>
							<input name="billingcity" id="idbillingcity" value="<?php echo !empty($lastbillingdetails->billing_city) ? $lastbillingdetails->billing_city : $playerDetails->city; ?>"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width: 120px;" type="text">
						</td>
					</tr>
					<tr>
					<td class="rowColoured">
						<div class="labe-new">Country &nbsp;<span id="asterisc">*</span></div>
						<?php $countrycode = !empty($lastbillingdetails->billing_country) ? $lastbillingdetails->billing_country : $playerDetails->country; ?>
						<select name="billingcountry" id="idbillingcountry" style="width: 120px;" onchange="getstatesList(this.value)">
							<?php foreach($countries as $country): ?>
								<option value="<?php echo $country['country_id'] ?>" <?php echo ($country['country_id'] == $countrycode) ? "selected='selected'" : '' ?>><?php echo $country['name'] ?></option>
							<?php endforeach; ?>
						</select>
					</td>
						
						<td class="rowColoured">
							<div class="labe-new">State <span id="asterisc">*</span></div>
							<?php $statecode = !empty($lastbillingdetails->billing_state) ? $lastbillingdetails->billing_state : $playerDetails->state; ?>
							<select name="billingstate" id="idbillingstate" style="width: 120px;">
								<?php foreach($states as $state): ?>
									<option value="<?php echo $state['state_code'] ?>" <?php echo ($state['state_code'] == $statecode) ? "selected='selected'" : '' ?>><?php echo $state['state_name'] ?></option>
								<?php endforeach; ?>
							</select>
						</td>
    
						</tr>
						<tr>
							<td class="rowColoured">
							<div class="labe-new">Zipcode&nbsp;<span id="asterisc">*</span></div>
							<input name="billingzip" id="idbillingzip" value="<?php echo !empty($lastbillingdetails->billing_zipcode) ? $lastbillingdetails->billing_zipcode : $playerDetails->zipcode; ?>" maxlength="12" class="required" autocomplete="off" onkeyup="filterZipCode(this)" onkeydown="filterZipCode(this)" onkeypress="filterZipCode(this)" onclick="shownormalborders(this.id);" style="width: <?php echo (is_wap($_PARAMS['web_id'])) ? '70px' : '120px' ?>;" type="text">
        			</td>
						</tr>
    				
                    <tr>
                    	<td height="20px" colspan="2"><a href="#" id="updateaddress" >Update</a> | <a href="#" id="cancel" >Cancel</a></td>
                    </tr>
                                
                                </table>
				</div>
				<input type="hidden" name="i" value="<?php echo $_REQUEST_ID; ?>">
				<input type="submit" name="btnSubmit" id="idbtnSubmit" value="<?php echo $_FORM_TEXTS['pay']; ?>" class="upay-btn btnStd" style="" />
	            <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="upay-btn btnStd" style="" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.parent.location.href='<?php echo $_PARAMS['redirect_url']; ?>?s=cancelled';" />
	            <input type="button" name="btnBack" id="idbtnBack" value="<?php echo $_FORM_TEXTS['back']; ?>" class="btnStd" style="width:90px; display:none;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnCancel').disabled=true; document.getElementById('idbtnCancel').disabled=true; location.href='index.php?i=<?php echo $_REQUEST_ID; ?>';" />
				<?php
				$signature = hash('sha256', 'CETR'.$_PARAMS['amount'].$_PARAMS['currency_id'].$_PARAMS['id'].PRIVATE_KEY);
				
				$ip = $_SERVER['REMOTE_ADDR'];
					
				$arr_post = array(
						"orderNumber"	=> $_PARAMS['id'],
						"orderCurrency"	=> $_PARAMS['currency_id'],
						// "orderAmount"	=> $_PARAMS['amount'],
						"signInfo"		=> $signature,
						"ip"			=> $ip,
						"country"		=> $_PARAMS['country_id']
				);
				
				?>
				<?php 
					foreach($arr_post as $key=>$row) {
						?>
						<input type='hidden' name='<?php echo $key; ?>' value='<?php echo $row; ?>' />
						<?php
					}
				?>
			</form>
			<div class="clearfix"></div>
		<!--</div>-->
		
		
		
	
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

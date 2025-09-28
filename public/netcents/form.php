<?php

define('PROVIDER_NAME', 'bitcoin');

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
	

if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
	include_once dirname(dirname(dirname(__FILE__))).'/models/langs.php';
	
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	//get min and max deposit limits
	$depositlimits = get_deposit_limits($_PARAMS['payment_method_id']);
	$getCurrencyAvailableProcessor = get_currency_available_processor_by_provider($_PARAMS['player_currency_id'], $_PARAMS['payment_provider_id']);
	?>
		
		<link type="text/css" href="../netcents/css/styles.css" rel="stylesheet"/>
        <link type="text/css" href="../netcents/css/messi.css" rel="stylesheet"/>
	    <script language="javascript" src="../netcents/js/jquery.min.js"></script>
        <script language="javascript" src="../netcents/js/messi.min.js"></script>
		
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
            	window.top.postMessage(920 + '-' + 'iframe2',"*");
            	var minamount = '<?php echo $depositlimits->minimum_deposit_amount/100; ?>';
            	var maxamount = '<?php echo $depositlimits->maximum_deposit_amount/100; ?>';
            	
            	
            	<?php 
            	if($getCurrencyAvailableProcessor->count > 0)
            	{
            	?>
            	if (!f.orderAmount.value || f.orderAmount.value < parseInt(minamount)  || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value))){
            	<?php
				}
				else {
				?>	
				if (!f.convertedOrderAmount.value || f.convertedOrderAmount.value < parseInt(minamount)  || f.convertedOrderAmount.value > parseInt(maxamount) || (isNaN(f.convertedOrderAmount.value))){
				<?php		
				}
            	?>		
	               	if (!f.orderAmount.value){
	                    showerrorborders('idorderAmount');
	                }
	               /* if (f.orderAmount.value && (f.orderAmount.value < parseInt(minamount) || f.orderAmount.value > parseInt(maxamount))){
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
                                showerroralert('Deposit amount should be min '+minamount+'<?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?> and max '+maxamount+'<?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?>');
                            }
                            if(f.convertedOrderAmount.value && isNaN(f.convertedOrderAmount.value)){
                                showerrorborders('idconvertedOrderAmount');
                                showerroralert('Please enter valid amount');
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
                                showerroralert('Deposit amount should be min '+minamount+'<?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$" ?>');
                            }
                            if(f.orderAmount.value && isNaN(f.orderAmount.value)){
                                showerrorborders('idorderAmount');
                                showerroralert('Please enter valid amount');
                            }
                            return false;
                        }
                    }
	               /* if(f.orderAmount.value && isNaN(f.orderAmount.value)){
	                	showerrorborders('idorderAmount');
	                	showerroralert('Please enter valid amount');
                	}*/
                	return false;
                }	

                document.getElementById('idbtnSubmit').disabled=true;
                document.getElementById('idbtnCancel').disabled=true;
                
                return true;
            }
            
            
            </script>
		 <style>
            
            .amount{font-size: 13px;
    vertical-align: top;
    display: inline-block;
    padding: 16px 0px;}
					 @media only screen 
                            and (min-device-width: 375px) 
                            and (max-device-width: 667px) 
                            and (-webkit-min-device-pixel-ratio: 2) { 
                            #idorderAmount{width: 145px ! important;border-radius:0;}
                            
                            .amount{    padding: 16px 0px;}
                            .great-news{display: none;}
                            
                            }
					@media only screen 
  and (min-device-width: 320px) 
  and (max-device-width: 480px)
  and (-webkit-min-device-pixel-ratio: 2) {
  
  							#idorderAmount{width: 150px;}
                            
                            .amount{    padding: 16px 0px;}
                            .great-news{display: none;}
                           
  
  }
                
                @-moz-document url-prefix() {
			 
			 @media only screen 
                            and (min-device-width: 375px) 
                            and (max-device-width: 667px) 
                            and (-webkit-min-device-pixel-ratio: 2) { 
                            #idorderAmount{width: 150px;}
                            
                            .amount{    padding: 16px 0px;}
                            .great-news{display: none;}
                           
			 
			 #idorderAmount{width: 104px !important;}
			 
			 
                            }
                            
                            
                            
                        
                            
             @media only screen 
  and (min-device-width: 320px) 
  and (max-device-width: 480px)
  and (-webkit-min-device-pixel-ratio: 2) {
  
  							#idorderAmount{width: 129px;}
                            
                            .amount{    padding: 16px 0px;}
                            .great-news{display: none;}
                           
			 
			 #idorderAmount{width: 104px !important;}
			 
			
  
  }       
                    

			 

              #idbtnSubmit{    
                    width: 108px;
                    background-color: #d83e0f;
                    height: 32px;
                    font-size: 16px;
                    font-weight: normal;
                    color: white;
                    background-image: none;border: none;
                  border-radius: 4px;
             }
             
                #idbtnCancel{
                    width: 108px;
                    background-color:#cccccc;
                    height: 32px;
                    font-size: 16px;
                    font-weight: normal;
                    color: white;
                    background-image: none;
                    margin-left: 5px;
                    border: none;
                    border-radius: 4px;
             }
		.indent2 * {
    float: left;
    margin: 0px 0px 10px;
}


</style>
		
		
	<?php 
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
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		/*   METHOD START   */
		
		$action = VALIDATOR;
		
		?>
<style>
.bimg img{    float: right;    width: 65%;}
    .title{width: 50%;float: left;}
    .plogo{  width: 50%;  float: right;}
   
    .title1{    display: table;    width: 100%;}
</style>
		<div class="content" style="">
		
			<!--<div class="title1">
                <div class="title">
				<br>
            	Bitcoin Wallet</br>
                <br><br>
            </div>
             <div class="plogo" >
            
                <div class="bimg">
											<?php 
											
											$percentage_width = '';
											if (is_wap($_PARAMS['web_id'])){
												$percentage_width  = 'width="50%"';
											} else {
												$percentage_width  = 'width="30%"';
											}
											
											
											
											
											$provider_url = 'form.php?';
											switch($_PARAMS['payment_method_id']){
												case 105:
													$provider_url = '../bitcoin/index.php?';
													$provider_name = '/bitcoin/';
													break;
											}
											
											?>
											
											
											<img src="img/<?php echo $_PARAMS['image'] ?>" <?php echo $percentage_width; ?> <?php echo $ppercentage_height; ?>>
										</div>                      
            
            </div>
            
            
            </div>-->
            <?php $url_process_direct = explode("deposit",$_PARAMS['redirect_url']); ?>
		
			<form id="idform" action='<?php echo $action; ?>' method='POST' onSubmit="return checkform(this);">
				<table width="100%" cellspacing="1" cellpadding="0" class="formTable">

									<tr>
									<td>
									<p>To deposit with Bitcoin:
									</p>
									</td>									
									</tr>
									
									<tr>
									<td class="bitcoin_deposit">
									<ul>
									<li>1. Enter a deposit amount in the box below</li>
									<li>2. Copy the BTC address provided on the next screen</li>
									<li>3. Send the deposit amount from your BTC wallet to the address that you copied</li>	
									<li>4. Your account will be updated in 5-20 minutes</li>
									<li>5. To Know the Deposit process <a href="<?php echo $url_process_direct[0]."deposit-process/"; ?>" target="_blank"> Click Here </a></li>
									</ul>
									</td>									
									</tr>

                                    <!--<tr>
                                    <td>
                                    Click on proceed button to make deposit through bitcoin wallet
                                    </td>
                                    </tr>-->

									 
                                    <tr>
                                    	<td height="20px" colspan="2"></td>
                                    </tr>
                                    
                                    
                                    <div>
                                       <tr>
										<td class="rowColoured">
									    	<div class="indent2"><b class="amount" style="width:80px;"><span id="asterisc">*</span>&nbsp;Amount :</b> 
									    	<span style="font-size: 18px;display: inline-block;padding:10px 10.5px;margin-right:0;background: #ccc;border-radius: 5px 0px 0px 5px;vertical-align: top;" class="indent2"><?php echo $currencysymbol['html_symbol']; ?></span>
									    	<input name="orderAmount" id="idorderAmount" value="<?php echo $_PARAMS['amount']; ?>" onkeyup="currencyconvert();convertAmount('<?php echo $_PARAMS['player_currency_id'] ?>', '<?php echo DEFAULT_CURRENCY ?>', this.value);" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width: 110px;padding: 12px;font-size: 14px;margin-left: -3px;margin-top:0px;" type="text">
									    	<input type="hidden" name="supportcurpspcount" id="supportcurpspcount" value="<?php echo $getCurrencyAvailableProcessor->count; ?>" />
                                            </div>
                                            
                                             <?php if($getCurrencyAvailableProcessor->count == 0){ ?>
                                             		<div class="indent2">
											    <span style="font-size: 18px;display: inline-block;padding:1px 10.5px 2px;margin-right: 0;background: #ccc;border-radius: 5px 0px 0px 5px;vertical-align: top;width: 18px;text-align: center;" class="dollor_symbol"><?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : "$"; ?></span>
											    <input name="convertedOrderAmount" id="idconvertedOrderAmount" style="width: 110px;padding: 12px;font-size: 14px;margin-left: -3px;" value="<?php echo $converted_amount; ?>" maxlength="6" readonly type="text">
											      </div>
											    <?php } ?>
											  
									    	
									    	
									    	<?php if($_PARAMS['player_currency_id']!='EUR'){ ?>
									    		<div style="clear:both"></div>
									    	<div class="indent2"><b class="amount empty-div" style="width:80px;"><span id="asterisc">&nbsp;</span>&nbsp;</b> 
									    	<span style="font-size: 18px;display: inline-block;padding:10px 10.5px 11.2px;margin-right:0;background: #ccc;border-radius: 5px 0px 0px 5px;vertical-align: top;" class="indent2">&euro;</span>
									    	<input name="orderAmountInEuro" id="idorderAmountInEuro" value="<?php if($_PARAMS['amount']) echo number_format(convert_currency($_PARAMS['player_currency_id'], 'EUR',  $_PARAMS['amount']), 2); ?>" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" style="width: 130px;padding: 12px;font-size: 14px;margin-left: -3px;border-radius: 0;margin-top:0" type="text" disabled readonly>
									    	<br>
									    	<?php  } ?>
									    	<span class="note_text" style="font-size:13px;display:block;line-height:20px;">*Deposit amount should be minimum <?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : '$'; ?><?php echo $depositlimits->minimum_deposit_amount/100 ?> and maximum <?php echo ($getCurrencyAvailableProcessor->count > 0) ? $currencysymbol['html_symbol'] : '$'; ?><?php echo $depositlimits->maximum_deposit_amount/100; ?>
									    	
									    	<br>
									    	
									    	*Multiple transactions allowed
									    	</span>
									    	<span class="note_text" style="font-size:13px;display:block;line-height:20px;margin-top:5px;"><b>Note:</b> Please make sure that you unblock or disable the pop ups in your browser to make sure that your attempt is successful. Also, ensure that you allow the notifications for this site.</span>
								        <script>
								        	function currencyconvert(){	
								            	var from = '<?php echo $_PARAMS['player_currency_id']; ?>';
								            	var to = "EUR";
								            	var amount =  $("#idorderAmount").val();
								            	//alert(amount);
								            	$.ajax({
											  		type:'post',
											  		url:'../netcents/form.php',
											  		data:'amount='+amount+'&from='+from+'&to='+to+'&ajax_conversion=1',
											  		success:function(result){
											  			console.log(result);
											  			$("#idorderAmountInEuro").val(result);
											  		}
											  	});
								            	
								             }
								        </script>
								        
								      
								        
								        </div>
								        <div></div>
								    </td>
									<td>                                     
										
									</td>
									</tr>
									
									
									
									</div>
									
									
                                    
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

                                            <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" style="width:90px;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.parent.location.href='<?php echo $_PARAMS['redirect_url']; ?>&s=cancelled';" />
                                            <input type="submit" name="btnSubmit" id="idbtnSubmit" value="<?php echo $_FORM_TEXTS['proceed']; ?>" class="btnStd" style="width:90px;" />
                                        </td>
                                    </tr>
									
									<tr>
                                    	<td height="20px" colspan="2"></td>
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
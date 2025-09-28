<?php

define('PROVIDER_NAME', 'ecorepay');

$_REQUEST_ID = isset($_POST['i']) ? $_POST['i'] : $_GET['i'];


if ($_REQUEST_ID){

	include_once dirname(dirname(dirname(__FILE__))).'/private/common.php';
	include_once dirname(dirname(dirname(__FILE__))).'/private/'.PROVIDER_NAME.'/define.php';
	include_once dirname(dirname(dirname(__FILE__))).'/private/'.PROVIDER_NAME.'/utils.php';
	include_once dirname(dirname(dirname(__FILE__))).'/private/'.PROVIDER_NAME.'/langs.php';
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);

	
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
                if (!f.cardNumber.value || f.cardNumber.value.length<16 || !f.cardSecurityCode.value || f.cardSecurityCode.value.length<3 || !f.cardExpireMonth.value || !f.cardExpireYear.value){

                        if (!f.cardNumber.value || f.cardNumber.value.length<16){
                            showerrorborders('idcardnumber');
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

                        document.getElementById('idbtnSubmit').disabled=false;
                        document.getElementById('idbtnCancel').disabled=false;
                        
                        if (!f.cardNumber.value || !f.cardSecurityCode.value || !f.cardExpireMonth.value || !f.cardExpireYear.value){
                            showerroralert('<?php echo $_FORM_TEXTS['alert_error1'];?>');
                        } else if(f.cardNumber.value.length<16 || f.cardSecurityCode.value.length<3){
                            showerroralert('<?php echo $_FORM_TEXTS['alert_error2'];?>');
                        }
                        return false;
                } 	        	

                document.getElementById('idbtnSubmit').disabled=true;
                document.getElementById('idbtnCancel').disabled=true;
                
                return true;
            }
            
            
            </script>
		
		
	<?php 
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	


	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		/*   METHOD START   */
		
		$action = VALIDATOR;

		
		?>
		<div class="content" style="">
		
			<div class="title1">
				<br>
            	<?php echo $_FORM_TEXTS['title2']; ?></br>
                <span id="asterisc" class="rowColoured">* <?php echo $_FORM_TEXTS['required']; ?></span>
                <br><br>
            </div>
		
			<form id="idform" action='<?php echo $action; ?>' method='POST' onSubmit="return checkform(this);">
				<table width="100%" cellspacing="1" cellpadding="0" class="formTable">

                                    <tr>
                                        <td class="rowColoured">
                                            <b>
                                            <div class="indent2"><span id="asterisc">*</span>&nbsp;<?php echo $_FORM_TEXTS['cardnumber']; ?>
                                            </div>
                                            </b>
                                        </td>
                                        <td>
											
										<input name="cardNumber" id="idcardnumber" type="text" maxlength="19" class="required" autocomplete="off" onkeyup="filterCardNumber(this)" onkeydown="filterCardNumber(this)" onkeypress="filterCardNumber(this)" onclick="shownormalborders(this.id);" style="width:185px;" />
										<div></div>
										
                                        </td>
                                    </tr>
                                    
                                    <tr>
										<td class="rowColoured">
									    	<div class="indent2"><b><span id="asterisc">*</span>&nbsp;CVV</b>
								        </div>
								    </td>
									<td>                                     
										<input name="cardSecurityCode" id="idcvv" maxlength="4"  class="required" autocomplete="off" onkeyup="filterNumber(this)" onkeydown="filterNumber(this)" onkeypress="filterNumber(this)" onclick="shownormalborders(this.id);" style="width: 50px;" type="text">
									</td>
									</tr>
                                    
                                    
                                    <tr>
                                        <td class="rowColoured">
                                            <b>
                                            <div class="indent2"><span id="asterisc">*</span>&nbsp;<?php echo $_FORM_TEXTS['expirydate']; ?>
                                            </div>
                                            </b>
                                        </td>
                                        <td>
                                        
										<select name="cardExpireMonth" id="idmonth" class="required" style="width:110px;margin-left: 0px;" onclick="shownormalborders(this.id);">
									<option value=""><?php echo $_FORM_TEXTS['month']; ?></option>
									<option value="01"><?php echo $_FORM_TEXTS['month_jan']; ?></option>
									<option value="02"><?php echo $_FORM_TEXTS['month_feb']; ?></option>
									<option value="03"><?php echo $_FORM_TEXTS['month_mar']; ?></option>
									<option value="04"><?php echo $_FORM_TEXTS['month_apr']; ?></option>
									<option value="05"><?php echo $_FORM_TEXTS['month_may']; ?></option>
									<option value="06"><?php echo $_FORM_TEXTS['month_jun']; ?></option>
									<option value="07"><?php echo $_FORM_TEXTS['month_jul']; ?></option>
									<option value="08"><?php echo $_FORM_TEXTS['month_aug']; ?></option>
									<option value="09"><?php echo $_FORM_TEXTS['month_sep']; ?></option>
									<option value="10"><?php echo $_FORM_TEXTS['month_oct']; ?></option>
									<option value="11"><?php echo $_FORM_TEXTS['month_nov']; ?></option>
									<option value="12"><?php echo $_FORM_TEXTS['month_dev']; ?></option>
										</select>
                                        
										<select name="cardExpireYear" id="idyear" class="required" style="width:73px;" onclick="shownormalborders(this.id);">
							<option value=""><?php echo $_FORM_TEXTS['year']; ?></option>
                                            
                                            <?php
                                            $year = date('Y');
                                            for ($i=$year; $i<=$year+50; $i++){
                                            ?>
							<option value="<?php echo $i;?>"><?php echo $i;?></option>
                                            <?php
                                            }
                                            ?>
										</select>
                                        
                                        </td>
                                    </tr>

									 
                                    <tr>
                                    	<td height="20px" colspan="2"></td>
                                    </tr>
                                    
                                    
                                    <div>
                                        <tr>
											<td class="rowColouredBox" colspan="2">
                                                &nbsp;<i><?php echo $_FORM_TEXTS['totalamount']; ?>&nbsp;<b><?php echo $_PARAMS['currency_id']; ?>&nbsp;<?php echo number_format(round($_PARAMS['amount'],2),2); ?></b></i>
                                            </td>
										</tr>
									</div>
									
									<tr>
                                    	<td height="20px" colspan="2"></td>
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
									"orderAmount"	=> $_PARAMS['amount'],
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
                                        <td align="right" class="rowColoured" colspan="2">
                                        	<input type="hidden" name="i" value="<?php echo $_REQUEST_ID; ?>">
											
                                            <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="btnStd" style="width:90px;" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.location.href='<?php echo $_PARAMS['redirect_url']; ?>?s=cancelled';" />
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

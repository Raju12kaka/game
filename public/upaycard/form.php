<?php

define('PROVIDER_NAME', 'upaycard');

$_REQUEST_ID = isset($_POST['i']) ? $_POST['i'] : $_GET['i'];

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
                
            function checkform(f){
            	var minamount = '<?php echo $depositlimits->minimum_deposit_amount/100; ?>';
            	var maxamount = '<?php echo $depositlimits->maximum_deposit_amount/100; ?>';
            	
            	if (!f.orderAmount.value || f.orderAmount.value < parseInt(minamount)  || f.orderAmount.value > parseInt(maxamount) || (isNaN(f.orderAmount.value)) || !f.useraccountid.value){
            		
	               	if (!f.useraccountid.value){
	                    showerrorborders('useraccountid');
	                    showerroralert('Please enter your account id');
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
                	return false;
                }	

                document.getElementById('idbtnSubmit').disabled=true;
                document.getElementById('idbtnCancel').disabled=true;
                
                return true;
            }
            
            
            </script>
       <style>
           .main-container {	    font-family: 'Roboto', sans-serif;}
		.main-container{margin:0px auto; text-align:center;}
		.upaycard-block{margin:0px auto;  width:360px; max-width:78%;}
		.upaycard-block p{font-size:18px; }
		.upaycard-form{
		
		   margin: auto;
    background: #ffffff;
    border: 3px solid #a09f9f;
    padding: 30px;
    border-radius: 3px;
    width:85%;
		 
		 
		 }
		.upaycard-form form{box-sizing:border-box;}
		.upaycard-form input{padding:10px; margin:10px 0px; width:100%; box-sizing:border-box;}
		.upay-btn{background:#d83e0f; width:100%; padding:10px 0px; margin:20px 0px; border-radius: 5px; text-decoration:none; color:#fff; font-size:16px; display:block; text-align:center;}
		.upay-btn:hover{background:#333;}
		.clearfix{clear:both; float:none;}
           #idbtnSubmit{width: 45%;    float: left;}
           #idbtnCancel{width: 45%;    float: right;}
            @media only screen and (max-device-width: 480px) and (min-device-width:100px)
                 {
           
                     .upaycard-block{    max-width: 82%;}
                 }
         
		 .acc_h{    margin-bottom: 0px;    font-size: 13px !important;    text-align: left;}
           #useraccountid{margin-top: 1px;margin-bottom: 4px}
           #idorderAmount{margin-top: 1px;margin-bottom: 4px}
           #idbtnCancel{    background: #cccccc;}
		</style>
		
		
	<?php 
	
	$_PARAMS = array();
	$_PARAMS = get_params($_REQUEST_ID);
	
	$_PARAMS['amount'] = $_PARAMS['amount'] / 100;
	
	if (VALIDATOR && $_PARAMS['amount'] && $_PARAMS['currency_id'] && $_PARAMS['payment_method_id'] && $_PARAMS['country_id'] && $_PARAMS['language']){
		
		/*   METHOD START   */
		
		$action = VALIDATOR;
		
		?>
		
		
		
		<div class="main-container">
			<div class="upaycard-block">
				<div class="upaycard-logo">
					<!-- <img src="img/<?php echo $_PARAMS['image'] ?>" <?php echo $percentage_width; ?> <?php echo $ppercentage_height; ?>>-->
				<p></p>	
				</div>
                <br/>
				<div class="upaycard-form">
                    <p style="    font-size: 17px;">Transaction Information</p>
					<form id="idform" action='<?php echo $action; ?>' method='POST' onSubmit="return checkform(this);">
						<div> <p class="acc_h">Account Id <span style="color:red;">*</span></p><br><input type="text" name="useraccountid" id="useraccountid" placeholder="Upaycard account Id"/></div>
						<div> <p class="acc_h">Amount(In €) <span style="color:red;">*</span></p><br> <input name="orderAmount" id="idorderAmount" value="<?php echo $_PARAMS['amount']; ?>" maxlength="6"  class="required" autocomplete="off" onclick="shownormalborders(this.id);" type="text"></div>
						<div style="font-size:13px;">Deposit amount should be minimum €<?php echo $depositlimits->minimum_deposit_amount/100; ?> and maximum €<?php echo $depositlimits->maximum_deposit_amount/100; ?></div>
						<div>
						<input type="hidden" name="i" value="<?php echo $_REQUEST_ID; ?>">
	                    <input type="submit" name="btnSubmit" id="idbtnSubmit" value="<?php echo $_FORM_TEXTS['pay']; ?>" class="upay-btn btnStd" style="" />
	                    <input type="button" name="btnCancel" id="idbtnCancel" value="<?php echo $_FORM_TEXTS['cancel']; ?>" class="upay-btn btnStd" style="" onclick="javascript: this.disabled=true; document.getElementById('idbtnSubmit').disabled=true; document.getElementById('idbtnBack').disabled=true; parent.parent.parent.location.href='<?php echo $_PARAMS['redirect_url']; ?>?s=cancelled';" />
						<?php?>
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
						<?php 
							foreach($arr_post as $key=>$row) {
								?>
								<input type='hidden' name='<?php echo $key; ?>' value='<?php echo $row; ?>' />
								<?php
							}
						?>
				</div>
				<div class="clearfix"></div>
			</form>
		</div>
	</div>
</div>
		
		
		
		
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
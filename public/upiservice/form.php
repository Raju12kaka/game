<?php 
set_time_limit(1000);
define('PROVIDER_NAME', 'hmtsquare');
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
	<link type="text/css" href="../hmtsquare/css/styles.css" rel="stylesheet"/>
    <style>
        .messi{top: 15px !important;}
    </style>
    <script language="javascript" src="../hmtsquare/js/jquery.min.js"></script>
    <script language="javascript" src="../hmtsquare/js/messi.min.js"></script>
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
            // console.log("Ok");
            $("#termsdata").empty();
            var eAmount = $('#idorderAmount').val();

            // if(eAmount<= 100){
            //     $("#available_coupons").addClass('required');
            // }else{
            //     $("#available_coupons").removeClass('required');
            // }

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
            
          
            if (!f.amount.value){
                showerrorborders('amount');
                
            }
            
            if (!f.upi.value){
                showerrorborders('upi');
                 
            }
            
            if (!f.name.value){
                showerrorborders('name');
                 
            }
            
             if (!f.file.value){
                showerrorborders('file');
                 
            }
            
            if(!f.amount.value || !f.upi.value || !f.name.value || !f.file.value){
            	return false;
            }

            document.getElementById('idbtnSubmit').disabled=true;
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
                if($(this).text() == 'Change') {
                    $(this).text(' ');
                    $("#idbillingdetails").show();
                } else {
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
    #idorderAmount{width: 132px;font-size: 14px;padding-left: 6px;height: 41px;display: inline-block;}
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
	width:87% !important;
	padding:12.3px;
	font-size: 14px;
	margin-bottom:12px;
    margin-right:0 !important;
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

    // print_r($_PARAMS['amount']); die;
    // echo "test<pre>"; print_r($resBonusdetails); die;

    // 
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
            <div class="text_quickbits">
            <!-- <p style="color:#fff;font-weight: bold;font-size:20px;margin-bottom:30px; "><span id="asterisc">*</span> Deposit with Multiple Payment Options</p> -->
            <p class="mb-text-center" style="color:#fff;font-weight: bold;font-size:18px;margin-bottom:20px; line-height: 25px;"><span id="asterisc">*</span> Deposit by Scanning the below QR / <a style="color: #ff992e; text-decoration: none;" download="scanqr_new.jpg" href="../upiservice/img/scanqr_new.jpg"> Download Code</a></p>
        
            <div class="flx_box">
         <div class="qr_scan">
         
            <!-- <p><a download="scanqr_new.jpg" href="../upiservice/img/scanqr_new.jpg" class="qrbtn"> Download QR Code</a></p> -->
            <a  download="scanqr_new.jpg" href="../upiservice/img/scanqr_new.jpg">
                <img src="../upiservice/img/scanqr_new.jpg">
                </a>
    
       
            
            
            <!-- <ol>
            <li>Enter your UPI ID & Deposit Amount.</li>
		  <li>Open your UPI App & Click on Pay Button</li>
			 <li> Wait for purchase to complete. Don't hit the back button.</li>
			
            </ol>  -->
            <?php if($_PARAMS['bonuscode']){ ?>
			  <h2 style="background:#3d3c78;color:#fff;padding:10px;">Applied Bonus Code is : <?php echo $_PARAMS['bonuscode']; ?></h2>
			 <?php } ?>
           
        </div>
		
            <div class="pay_right_form">
           <p>Once you make a payment to the QR Code, please enter requred details below, upload the screenshot of your success payment and confirm deposit:</p>
				<table width="100%" cellspacing="1" cellpadding="0" class="formTable">

                    <!-- <tr>
	                    <td style="font-size: 14px; color: #ccc;">
	                    Click on proceed button to make deposit
	                    </td>
                    </tr> -->
                  
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
                        <tr>
							<td class="rowColoured">
						    	<div class="indent2 dff">
						    		
						    		 <form id="idform" name="idform" action="<?php echo $action; ?>" method="POST"  onSubmit="return checkform(this);" enctype="multipart/form-data">
                                    
                                    <div class="form_main_div">
                                        <!--<div class="col">
                                        <div class="indent2" style="display:block;margin-bottom: 7%;padding-left: 0;"><b class="amount">Your Name<span id="asterisc">*</span></b></div>
                                            <input name="name" id="name" value="" maxlength="40"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  style="width:100% !important;padding: 13px;font-size: 14px;margin-left:-4px;margin-right:10px;" type="text">
                                        </div>-->
                                        <div class="col">
                                        <div class="indent2" style="display:block;margin-bottom: 7%;padding-left: 0;"><b class="amount">UPI ID <span id="asterisc">*</span></b></div>
                                            <input name="upi" id="upi" value="" maxlength="40"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  style="width:100% !important;padding: 13px;font-size: 14px;margin-left:-4px;margin-right:10px;" type="text">
                                        </div>
                                        <div class="col">
                                        <div class="indent2" style="display:block;margin-bottom: 7%;padding-left: 0;"><b class="amount">Deposit Amount<span id="asterisc">*</span></b></div>
                                            <input name="amount" id="amount" value="" maxlength="40"  class="required" autocomplete="off" onclick="shownormalborders(this.id);"  style="width:100% !important;padding: 13px;font-size: 14px;margin-left:-4px;margin-right:10px;" type="text">
                                        </div>
                                        <div class="col">
                                        <div class="indent2" style="display:block;margin-bottom: 7%;padding-left: 0;"><b class="amount">Upload Screenshot<span id="asterisc">*</span></b></div>
                                            <input  id="file"   class="required"   style="width:100% !important;padding: 13px;font-size: 14px;margin-left:-4px;margin-right:10px;" type="file">
                                        </div>
                                        
                                        <div class="indent2" style="display:block;margin-bottom: 7%;padding-left: 0;"></div>
                                            <input name="filename" id="filename" value=""  class="required"   style="width:100% !important;padding: 13px;font-size: 14px;margin-left:-4px;margin-right:10px;" type="hidden">
                                        </div>
                                        
                                       
                                        <div style="clear:both;"></div>
                                    </div>
                                    
                    <?php 
						foreach($arr_post as $key=>$row) {
							?>
							<input type='hidden' name='<?php echo $key; ?>' value='<?php echo $row; ?>' />
							<?php
						}
						?>
						<input type="hidden" name="i" value="<?php echo $_REQUEST_ID; ?>">
                        <input type="submit" name="btnSubmit" id="idbtnSubmit" value="Submit" class="btnStd" />
                                
					        
					        </div>
					      </form>  

                       </td>
							
				</tr>
				
									
			
						
					
					
						
					<tr>
                    	<td height="20px" colspan="2"></td>
                    </tr>
                </table> 

		
            </div>

            </div>
         </div>
	</div>
	 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
	$(document).ready(function(){
		
	
		$("#file").change(function(){
			if(this.files[0].size > 1000000) {
            alert("Please upload file less than 1MB. Thanks!!");
            $(this).val('');
            return false;
             }
			 var fd = new FormData();
             var files = $('#file')[0].files[0];
             
             fd.append('image', files);
             
		      $.ajax({
		        url: "/upiservice/upload.php",
		        type: "POST",
		        data: fd,
		        processData: false,
		        contentType: false,
		        success: function(data){
		            $("#filename").val(data);
		        }
		      });
		     
		});
	});	
	</script>
	
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
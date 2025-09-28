<?php 
            include_once dirname(dirname(dirname(__FILE__))).'/models/common.php';
			include_once dirname(dirname(dirname(__FILE__))).'/models/define.php';
			include_once dirname(dirname(dirname(__FILE__))).'/models/utils.php';
            $callback_url= CALLBACKURL.'/qartpay/CallbackNotify.php';
			$payid = '2109161220181324';
			$saltkey= '232f10b412ab4eed';
			$paymenturl='https://pg.hmtpayments.com/crm/jsp/merchantpay';
			$cardNo='4143670069042117';
			$cardExpireMonth='04';
			$cardExpireYear='2023';
			$cardSecurityCode='120';
			$upi='sunilkumarkolisetty@okicici';
            $_PARAMS['payment_method_id'] = 134;
            /*Hash Generation*/
			$parameters = array();
			$parameters['AMOUNT']=100;
			$parameters['CURRENCY_CODE']=356;
			$parameters['CUST_EMAIL']=strtoupper('sunilk@mailinator.com');
			$parameters['CUST_NAME']='sunil';
			$parameters['CUST_PHONE']=9298208258;
			$parameters['ORDER_ID']='19';
			$parameters['PAY_ID']=$payid;
			$parameters['PRODUCT_DESC']="Product Purchase";
			$parameters['RETURN_URL']=$callback_url;
			$parameters['TXNTYPE']="SALE";
			/*$parameters['CUST_FIRST_NAME']=$name;
			$parameters['CUST_LAST_NAME']=$lastname;
			$parameters['CUST_STREET_ADDRESS1']=$address;
			$parameters['CUST_CITY']=$city;
			$parameters['CUST_SHIP_LAST_NAME']=$lastname;
			$parameters['CUST_SHIP_NAME']=$name;
			$parameters['CUST_SHIP_STREET_ADDRESS1']=$address;
			$parameters['CUST_SHIP_STREET_ADDRESS2']=$address;
			$parameters['CUST_SHIP_CITY']=$city;
			$parameters['CUST_SHIP_STATE']=$state;
			$parameters['CUST_SHIP_COUNTRY']='India';
			$parameters['CUST_SHIP_ZIP']=$zipcode;
			$parameters['CUST_SHIP_PHONE']=$contactno;*/
			
			if($_PARAMS['payment_method_id']==133){
			$parameters['CARD_NUMBER']=$cardNo;
			$parameters['CARD_EXP_DT']=$cardExpireMonth.$cardExpireYear;
			$parameters['CVV']=$cardSecurityCode;
			$parameters['MOP_TYPE']='VI';
			$parameters['PAYMENT_TYPE']='DC';
			//$parameters['MERCHANT_PAYMENT_TYPE']='CC';
			}else{
		    $parameters['MOP_TYPE']='UP';
			$parameters['PAYMENT_TYPE']='UP';
			//$parameters['MERCHANT_PAYMENT_TYPE']='UP';
			$parameters['UPI']=$upi;	
			}
			
			 ksort($parameters);
			
			//print('<pre>');print_r($parameters);
			$hash_string="";
			$cnt_parameters = count($parameters);
			$kk=1;
			foreach ($parameters as $key => $value) {
				if($kk==$cnt_parameters){
				$hash_string.= $key.'='.$value;	
				}else{
				$hash_string.= $key.'='.$value.'~';
				}
				$kk++;
			}
			
			$hash_new_string = $hash_string.$saltkey;
			//echo $hash_new_string.'<br>'; exit;
			$hash= strtoupper(hash('sha256', $hash_new_string));
			$parameters['HASH']=$hash;
			/*Form Generation*/
			
			/**  Log message start **/
			  $documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
			  array_pop($documentroot);
			  array_push($documentroot, 'logs');
			  $root_path = implode('/', $documentroot);
			  
			  $logmessage = date('Y-m-d H:i:s')." qartpay request data :".json_encode($parameters)."\n";
			  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			  
			  $logmessage = date('Y-m-d H:i:s')." qartpay string :".$hash_new_string."\n";
			  file_put_contents($root_path.'/payments_qartpay.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			  /**  Log message end **/
			if($_PARAMS['payment_method_id']==134){
			?>
			
			<form action="<?php echo $paymenturl; ?>" style="display:none;" target="_top" method="post" name="post_form" id="post_form">
			<input type="text" name="AMOUNT" value="<?php echo $parameters['AMOUNT']; ?>"/>
			<input type="text" name="CURRENCY_CODE" value="<?php echo $parameters['CURRENCY_CODE']; ?>"/>
			<input type="text" name="CUST_EMAIL" value="<?php echo strtoupper($parameters['CUST_EMAIL']); ?>"/>
			<input type="text" name="CUST_NAME" value="<?php echo $parameters['CUST_NAME']; ?>"/>
			<input type="text" name="CUST_PHONE" value="<?php echo $parameters['CUST_PHONE']; ?>"/>
			<input type="text" name="MOP_TYPE" value="UP"/>
			<input type="text" name="ORDER_ID" value="<?php echo $parameters['ORDER_ID']; ?>"/>
			<input type="text" name="PAYMENT_TYPE" value="UP"/>
			<input type="text" name="PAY_ID" value="<?php echo $parameters['PAY_ID']; ?>"/>
			<input type="text" name="PRODUCT_DESC" value="<?php echo "Product Purchase"; ?>"/>
			<input type="text" name="RETURN_URL" value="<?php echo $callback_url; ?>">
			<input type="text" name="TXNTYPE" value="SALE"/>
			<!--<input type="text" name="CARD_NUMBER" value="<?php echo $cardNo; ?>"/>
			<input type="text" name="CARD_EXP_DT" value="<?php echo $cardExpireMonth.$cardExpireYear; ?>"/>
			<input type="text" name="CVV" value="<?php echo $cardSecurityCode; ?>"/>-->
			<input type="text" name="UPI" value="<?php echo $upi; ?>"/>
			<input type="text" name="HASH" value="<?php echo $hash; ?>"/>
			<!--<input type="text" name="CUST_FIRST_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_SHIP_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS2" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_STATE" value="<?php echo $state; ?>"/>
			<input type="text" name="CUST_SHIP_COUNTRY" value="India"/>
			<input type="text" name="CUST_SHIP_ZIP" value="<?php echo $zipcode; ?>"/>
			<input type="text" name="CUST_SHIP_PHONE" value="<?php echo $contactno; ?>"/>
			<input type="text" name="MERCHANT_PAYMENT_TYPE" value="UP"/>-->
			
			<!--<button type="submit" value="Click to Pay" name="submit">SUBMIT</button>-->
			</form>
			<script language="javascript" src="js/jquery.min.js"></script>
			
			<script type="text/javascript">
            setTimeout(function(){ document.getElementById("post_form").submit(); }, 1000);
            </script>
			
			
			<?php 
			}
          
		    if($_PARAMS['payment_method_id']==133){
			?>
			
			<form action="<?php echo $paymenturl; ?>" style="display:none;" target="_top" method="post" name="post_form" id="post_form">
			<input type="text" name="AMOUNT" value="<?php echo $parameters['AMOUNT']; ?>"/>
			<input type="text" name="CURRENCY_CODE" value="<?php echo $parameters['CURRENCY_CODE']; ?>"/>
			<input type="text" name="CUST_EMAIL" value="<?php echo strtoupper($parameters['CUST_EMAIL']); ?>"/>
			<input type="text" name="CUST_NAME" value="<?php echo $parameters['CUST_NAME']; ?>"/>
			<input type="text" name="CUST_PHONE" value="<?php echo $parameters['CUST_PHONE']; ?>"/>
			<input type="text" name="MOP_TYPE" value="VI"/>
			<input type="text" name="ORDER_ID" value="<?php echo $parameters['ORDER_ID']; ?>"/>
			<input type="text" name="PAYMENT_TYPE" value="DC"/>
			<input type="text" name="PAY_ID" value="<?php echo $parameters['PAY_ID']; ?>"/>
			<input type="text" name="PRODUCT_DESC" value="<?php echo "Product Purchase"; ?>"/>
			<input type="text" name="RETURN_URL" value="<?php echo $callback_url; ?>">
			<input type="text" name="TXNTYPE" value="SALE"/>
			<input type="text" name="CARD_NUMBER" value="<?php echo $cardNo; ?>"/>
			<input type="text" name="CARD_EXP_DT" value="<?php echo $cardExpireMonth.$cardExpireYear; ?>"/>
			<input type="text" name="CVV" value="<?php echo $cardSecurityCode; ?>"/>
			<!--<input type="text" name="UPI" value="<?php echo $upi; ?>"/>-->
			<input type="text" name="HASH" value="<?php echo $hash; ?>"/>
			<!--<input type="text" name="CUST_FIRST_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_LAST_NAME" value="<?php echo $lastname; ?>"/>
			<input type="text" name="CUST_SHIP_NAME" value="<?php echo $name; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS1" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_STREET_ADDRESS2" value="<?php echo $address; ?>"/>
			<input type="text" name="CUST_SHIP_CITY" value="<?php echo $city; ?>"/>
			<input type="text" name="CUST_SHIP_STATE" value="<?php echo $state; ?>"/>
			<input type="text" name="CUST_SHIP_COUNTRY" value="India"/>
			<input type="text" name="CUST_SHIP_ZIP" value="<?php echo $zipcode; ?>"/>
			<input type="text" name="CUST_SHIP_PHONE" value="<?php echo $contactno; ?>"/>
			<input type="text" name="MERCHANT_PAYMENT_TYPE" value="CC"/>-->
			
			<!--<input type="submit" value="Click to Pay" name="submit"/>-->
			</form>
			<script language="javascript" src="js/jquery.min.js"></script>
			
			<script type="text/javascript">
            setTimeout(function(){ document.getElementById("post_form").submit(); }, 1000);
            </script>
			
			
			<?php 
			}
         
		


?>
<?php

error_reporting(E_ALL & ~E_NOTICE);
/** Database configuration **/
include_once dirname(dirname(__FILE__)).'/models/common.php';
include_once dirname(dirname(__FILE__)).'/models/define.php';

/**
 * Class for processing dummy transactions to different processors
 * This file will run for every two hours by using cron setup
 * Dummy transaction with in twenty four hours will be processed, others will be eliminated.
 */
 
class DummyProcess{
	
	var $currentTime;
	var $startTime;
	var $root_path;
	
	function __construct(){
		$this->currentTime 	= date('Y-m-d H:i:s');
		$this->startTime	= date("Y-m-d H:i:s", strtotime('-24 hours', time()));
		$this->root_path = dirname(__DIR__).'/logs/cron.log';
	}
	
	function connection_DB(){
		try {
			$db = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB, MYSQL_USER, MYSQL_PASS);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $db;
		} catch(PDOException $ex) {
			return false;
		}
		
		return false;
	}
	
	function processDummyToPaymentgateway(){
		$dbConn = $this->connection_DB();
		if(!$dbConn){
			return false;
		}
		
		$SQL = "select * from payments where payment_method_id IN (106, 107) and status = 'OK' and date >= '".$this->startTime."' and date <= '".$this->currentTime."'";
		$PRE = $dbConn->prepare($SQL);
		$PRE->execute();
		$RES = $PRE->fetchAll();
		
		//get pre approved processors class id
		$PSQL = "select id from players_classes where name = 'Pre approved processor'";
		$PESQL = $dbConn->prepare($PSQL);
		$PESQL->execute();
		$PRES = $PESQL->fetchObject();
		
		//Get processors list to process pre approved deposits to payment gateway
		$PRO = "select d.method_id,p.* from define_processors_routing d left join routing_processors r on (r.routing_id = d.id) left join payments_providers p ON (p.id = r.processor_id) where d.player_level = ".$PRES->id;
		$PRSQL = $dbConn->prepare($PRO);
		$PRSQL->execute();
		$PROS = $PRSQL->fetchAll();
	
		$p = 0; $v = 0;
		foreach($PROS as $provider){
			if($provider['method_id'] == 100){
				$providers[100][$p] = $provider;
				$p++;
			}else{
				$providers[101][$v] = $provider;
				$v++;
			}	
		}
		
		//insert log
		$logmessage  = "/****************".date('Y-m-d H:i:s')." Process Dummy cron started*****************/";
		file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		if($RES){
			foreach ($RES as $deposits) {
				$checkPlayAndBonus = $this->checkHasPlaysAndBonusExchanged($deposits['player_id']);
				$checkProcessedOrNot = $this->checkHasProcessed($deposits['id'], $deposits['player_id']);
				$payment_method = ($deposits['payment_method_id'] == 106) ? 100 : 101;
				
				$finalProviders[$payment_method] = $providers[$payment_method];
				if(!empty($checkProcessedOrNot->providers)){
					$finalProviders = $this->eliminateProcessors($checkProcessedOrNot->providers, $providers[$payment_method]);
				}
				
				
				if($checkPlayAndBonus == 1 && (!empty($finalProviders))){
					$c = 0;
					foreach ($finalProviders[$payment_method] as $processor) {
						
						if($c == 1){
							break;
						}
						
						$sendToGateway = $this->sendPaymentToGateway($processor, $deposits);
						if($sendToGateway == 1){
							break;
						}
						$c++;
					}	
				}
			}
		}else{
			$logmessage  = "No pre approved deposits found";
			file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		}
		//insert log
		$logmessage  = "/****************Process Dummy cron end*****************/";
		file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		
	}
	
	function checkHasPlaysAndBonusExchanged($playerId){
		$dbConn = $this->connection_DB();
		if(!$dbConn){
			return false;
		}
		
		$SQL  = "SELECT count(*) as cnt FROM transactions WHERE player_id = ?";
        try{
	        $res = $dbConn->prepare($SQL);
	        $res->execute(array($playerId));
	        $plays = $res->fetchObject();
		}catch(PDOException $ex) {
	        return false;
	    }
		
		$BSQL  = "SELECT count(*) as cnt FROM players_bonuses WHERE player_id = ? AND bonus_status_id = 3";
        try{
	        $res = $dbConn->prepare($BSQL);
	        $res->execute(array($playerId));
	        $bonus = $res->fetchObject();
		}catch(PDOException $ex) {
	        return false;
	    }
        
        if(($plays->cnt > 1) || ($bonus->cnt > 0)){
        	return 1;
        }else{
        	return 0;
        }
	}
	
	function checkHasProcessed($paymentId, $playerId){
		$dbConn = $this->connection_DB();
		if(!$dbConn){
			return false;
		}
		
		$SQL = "select GROUP_CONCAT(provider_id) providers from pre_deposits_process where payment_id = ? and player_id = ?";
		try{
			$res = $dbConn->prepare($SQL);
	        $res->execute(array($paymentId, $playerId));
	        return $res->fetchObject();
		}catch(PDOException $ex) {
	        return false;
	    }
	}
	
	function eliminateProcessors($processed, $providers){
		$providerToProcess = array();
		$processedProviders = explode(',', $processed);
		$p = 0;
		foreach ($providers as $provider) {
			if(!in_array($provider['id'], $processedProviders)){
				$providerToProcess[$provider['method_id']][$p] = $provider;
				$p++;
			}
		}
		return $providerToProcess;
	}
	
	function sendPaymentToGateway($providers, $depositDetails){
		
		
		
		$_PARAMS = array();
		$_PARAMS = get_params($depositDetails['payment_request_id']);
		
		// Get all player details
		$palyer_details = get_player_all_details($depositDetails['player_id']);
		
		$getcountrycode = fetchCountryCode($palyer_details->country);
		$card_info = get_carddetails($depositDetails['id']);
		
		$nameoncard = get_player_name($depositDetails['player_id']);
		$card_number = !empty($card_info->cardnumber) ? $card_info->cardnumber : '';
		$cvv_number = !empty($card_info->cvv_no) ? $card_info->cvv_no : '';
		
		$expiry_year = !empty($card_info->card_expiry_year) ? $card_info->card_expiry_year : '';
		$cexpiry_month = !empty($card_info->card_expiry_month) ? $card_info->card_expiry_month : '';
		$lastbillingdetails = get_last_billing_details($depositDetails['player_id']);
		$phone				= explode(' ', $palyer_details->contact_phone);
		$phone				= $phone[1];
		
		//All Form values
		
		$gateway_url = "classes/".$providers['gateway_class']."_request.php";
		$cardname = $nameoncard;
		$cardnumber = $card_number;
		$cardsecuritycode = $cvv_number;
		$cardexpiryyear = $expiry_year;
		$cardexpirymonth = $cexpiry_month;
		$ordernumber = $_PARAMS['id'];
		$ordercurrency = $depositDetails['currency_id'];
		$orderamount = $depositDetails['amount']/100;
		$signature = hash('sha256', 'CETR'.$orderamount.$ordercurrency.$_PARAMS['id'].PRIVATE_KEY);
		$ip=long2ip($palyer_details->register_IP);
		$country=$getcountrycode->code3;
		$i=$_PARAMS['request_id'];
		$billingname =$palyer_details->realname;
		$billinglastname =$palyer_details->reallastname;
		$billingaddress=$palyer_details->address;
		$billingcity=$palyer_details->city;
		$billingcountry=$palyer_details->country;
		$billingstate=$palyer_details->state;
		$billingzip=$palyer_details->zipcode;
		$auth1=$providers['credential1'];
		$auth2=$providers['credential2'];
		$auth3=$providers['credential3'];
		$auth4=$providers['credential4'];
		$cardtype=$providers['credential4'];
		$providername=$providers['name'];
		$origin=$_SERVER['HTTP_USER_AGENT'];
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		$posturl= PAYMENTURL.$gateway_url;
		
		$deposit_id=$depositDetails['id'];
		$paymentmethodid=$providers['method_id'];
		$paymentproviderid=$providers['id'];

	        $form=array();
			$form['cardName']=$cardname;
			$form['cardNumber']=$cardnumber;
			$form['cardSecurityCode']=$cardsecuritycode;
			$form['cardExpireMonth']=$cardexpirymonth;
			$form['cardExpireYear']=$cardexpiryyear;
			$form['orderNumber']=$ordernumber;
			$form['orderCurrency']=$ordercurrency;
			$form['orderAmount']=$orderamount;
			$form['signInfo']=$signature;
			$form['ip']=$ip;
			$form['country']=$country;
			$form['i']=$i;
			$form['billingname']=$billingname;
			$form['billinglastname']=$billinglastname;
			$form['billingaddress']=$billingaddress;
			$form['billingcity']=$billingcity;
			$form['billingcountry']=$billingcountry;
			$form['billingstate']=$billingstate;
			$form['billingzip']=$billingzip;
			$form['authorisationkey1']=$auth1;
			$form['authorisationkey2']=$auth2;
			$form['authorisationkey3']=$auth3;
			$form['authorisationkey4']=$auth4;
			$form['cardtype']=$cardtype;
			$form['providerName']=$providername;
			$form['origin']=$origin;
			$form['useragent']=$useragent;
			$form['paymentid']=$deposit_id;
			$form['paymentmethodid']=$paymentmethodid;
			$form['paymentproviderid']=$paymentproviderid;
			$form['direct_csid']='abc';
			$form['submitfrom']='cron';
			$postcsidurl = PAYMENTURL.'csidgenerate.php';
			
		    if($providers['id'] =='183' || $providers['id'] =='184'){
		    	$generatedata = array('providerId' => $providers['id'], 'merno' => $auth1, "gateway" => $auth3, "uniqueid" => $i);
		     	// $paymentserviceurl  = WONDERLANDSCRIPTURL;
			 	$form['uniqueId']=$i;
			 	// $form['deviceNo'] = '<script type="text/javascript" src="'.$paymentserviceurl.'/pub/js/fb/tag.js?merNo='.$auth1.'&gatewayNo='.$auth3.'&uniqueId='.$i.'"></script><script>window.onload = function(){Fingerprint2.get(function(components) {var murmur = Fingerprint2.x64hash128(components.map(function(pair) {return pair.value}).join(), 31) ;document.write(murmur);});}</script>';
		    }
			
			/*if($providers['id'] =='134' || $providers['id'] =='133'){
				$generatedata = array('providerId' => $providers['id']);
			}*/
             
			 
			 // if($providers['id'] =='134'){
			 	// session_start();
				// echo $_SESSION['directcsid'];
			 // // echo  '<input name="direct_csid" type="hidden" id="csid">';	
			 // // echo   "<script type='text/javascript' charset='utf-8' src='https://online-safest.com/pub/csid.js'></script>";
		     // // $form['direct_csid'] ='<script>var vals = document.getElementById("csid");document.write(vals.value);</script>';
			 // // $form['direct_csid'] = strip_tags($form['direct_csid']);
			 // }
			 
			 
// 			 echo file_get_contents($postcsidurl); exit(' Exit at '. __LINE__. ' in page : '. __FILE__);
			 $curl = curl_init();
			 curl_setopt($curl,CURLOPT_URL,$postcsidurl);
			 curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
			 curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
			 curl_setopt($curl,CURLOPT_RETURNTRANSFER, false);// Show the output
			 curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
			 curl_setopt($curl,CURLOPT_POSTFIELDS,$generatedata);//Transmit datas by using POST
			 $res = curl_exec($curl);
			// if(in_array($providers['id'], array(134,133,184,183))){
			if(in_array($providers['id'], array(184,183))){
			 	$fh = fopen('csid_value.txt','r');
				while ($line = fgets($fh)) {
				  // <... Do your work with the line ...>
				  if($providers['id'] =='134' || $providers['id'] =='133'){
				  	//$form['direct_csid'] = $line;
				  }elseif($providers['id'] =='183' || $providers['id'] =='184'){
				  	$form['deviceNo'] = $line;
				  }
				}
				fclose($fh);
			}
			/*if($providers['id'] =='134' || $providers['id'] =='133'){
			$form['direct_csid'] = 'abc';
			}*/
			 
			
			file_put_contents($this->root_path, "post url : ".$posturl.PHP_EOL , FILE_APPEND | LOCK_EX);
			file_put_contents($this->root_path, "curl post data : ".json_encode($form).PHP_EOL , FILE_APPEND | LOCK_EX);
			
             $curl = curl_init();
			 curl_setopt($curl,CURLOPT_URL,$posturl);
			 curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
			 curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
			 curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
			 curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
			 curl_setopt($curl,CURLOPT_POSTFIELDS,$form);//Transmit datas by using POST
			 $res = curl_exec($curl);
			 
			 //insert log
			$logmessage  = "/**************************************************************************/"."\n";
			$logmessage .= date('Y-m-d H:i:s')." Dummy deposit process for Payment Id ".$depositDetails['id']." for player: ".$depositDetails['player_id']." with processor: ".$providers['id']."\n";
			$logmessage .= " Response ".$res."\n";
			$logmessage .= "/**************************************************************************/"."\n";
			file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			 
			
			 
			 $result1 = json_decode($res,TRUE);
			
			 $result = array();
      		 $result['ap_transaction_number']      = $result1['tradeNo'];//return tradeNo
             $result['orderNo']      = $result1['orderNo'];//return orderno
             $result['amount']  	= ($result1['orderAmount']) ? $result1['orderAmount'] : $orderamount;//return orderAmount
             $result['orderCurrency'] =$result1['orderCurrency'];//return orderCurrency
             $result['status']  = $result1['orderStatus'];//return orderStatus
             $result['message']     = $result1['orderInfo'];//return riskInfo
			 
			 $orderStatus = $result['status'];
			 
			 $logmessage  = "/**************************************************************************/"."\n";
			$logmessage .= date('Y-m-d H:i:s')." Dummy deposit process for Payment Id ".$depositDetails['id']." for player: ".$depositDetails['player_id']." with processor: ".$providers['id']."\n";
			$logmessage .= " Response ".json_encode($result)."\n";
			$logmessage .= "/**************************************************************************/"."\n";
			file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
			 
			
		     $this->insertProcessedProcessors($providers, $depositDetails, $result);
			 if($orderStatus == 1 ){
				$this->updatePayments($providers['method_id'], $providers['id'], $depositDetails['id'],$result['ap_transaction_number']);
				$this->updatePlayersVIPStatus($_PARAMS['player_id']);
				return 1;
			 }
			
			 return 0;
			 
         	
	}

	function insertProcessedProcessors($providerDetails, $payments, $result){
		$dbConn = $this->connection_DB();
		if(!$dbConn){
			return false;
		}
		$logmessage  = "/**************************************************************************/"."\n";
			$logmessage .= date('Y-m-d H:i:s')." Dummy deposit process for Payment Id ".$payments['id']." for player: ".$depositDetails['player_id']." with processor: ".$providers['id']."\n";
			$logmessage .= " Response ".json_encode($result)."\n";
			$logmessage .= "/**************************************************************************/"."\n";
			file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		$result['status'] = ($result['status'] == 1) ? "OK" : "KO";
		
		$SQL  = "insert into pre_deposits_process (player_id, payment_id, method_id, provider_id, ap_transaction_id, amount, status, description, created) values(?, ?, ?, ?, ?, ? ,? , ?, NOW())";
		try {
			$res = $dbConn->prepare($SQL);
			$res->execute(array($payments['player_id'], $payments['id'], $providerDetails['method_id'], $providerDetails['id'], $result['ap_transaction_number'], $result['amount']*100, $result['status'], $result['message']));
		}catch(PDOException $ex) {
	        return false;
	    }
	}

	function updatePayments($methodId, $providerId, $paymentId, $transaction_id){
		$dbConn = $this->connection_DB();
		if(!$dbConn){
			return false;
		}
		
		$SQL  = "UPDATE payments set payment_method_id = ? ,payment_provider_id = ? ,payment_foreign_id = ? WHERE id = ? ";
		try{
			$res = $dbConn->prepare($SQL);
			$res->execute(array($methodId, $providerId, $transaction_id, $paymentId));
		}catch(PDOException $ex) {
	        return false;
	    }
	}
	
	function updatePlayersVIPStatus($playerId){
		$dbConn = $this->connection_DB();
		if(!$dbConn){
			return false;
		}
		
		$SQL  = "UPDATE players set players_vip_classes_id = 1 WHERE id = ? ";
		try{
			$res = $dbConn->prepare($SQL);
			$res->execute(array($playerId));
		}catch(PDOException $ex) {
	        return false;
	    }
	}
}

$process = new DummyProcess();
$process->processDummyToPaymentgateway();
?>

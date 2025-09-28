<?php   

/** Database configuration **/
include_once ('dbInitialise.php'); 

/**
***
*** This class defines the routing for transactions
*** First checks for payment type and based on that it defines the routing
***
**/

class DefineRouting{
	
	public function connect_db(){
		try {
			$db = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB, MYSQL_USER, MYSQL_PASS);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $db;
		} catch(PDOException $ex) {
			send_email(TECH_EMAIL, 'connect_db', "catch: ".var_export($ex->getMessage(),true));
			return false;
		}
		
		return false;
	}
	
	public static function gatewayRouting($orderNo, $cardno, $date, $paymentmethodId, $webId, $playerId, $paymentId = null, $providerId = null, $virtualPayment = null, $countryId){
		$types = get_payment_types();
		$paymentType = self::current_paymenttype($paymentmethodId, $webId);
		
		$methodData = self::getPaymenthodDetails($paymentmethodId);
		if(in_array($paymentmethodId, array(112, 116, 121, 133, 134, 135, 142, 143,144,145))) { $paymentType->name = 'Wallets'; }		
		//return routing based on payment types
		switch($paymentType->name){
			case 'Credit / Debit Cards':
				$getProvider = self::checkProvider_conditions($orderNo, $playerId, $cardno, $paymentmethodId, $paymentId, $providerId, $virtualPayment, $countryId, $webId);
				return $getProvider;
				break;
			case 'Wallets':
				$methodDetails = self::getPaymenthodDetails($paymentmethodId);
				$providerDetails = self::getPaymentProviderDetails($providerId);
				$url = strtolower($providerDetails->name)."/request.php";
				$processorDetails = array('providerId' => $providerDetails->id, 'paymentMethod' => $methodDetails->id, 'providerName' => $providerDetails->name, 'cardName' => '', 'authKey1' => $providerDetails->credential1, 'authKey2' => $providerDetails->credential2, 'authKey3' => $providerDetails->credential3, 'authKey4' => $providerDetails->credential4, 'requesturl' => $url);
				return $processorDetails;
				break;
			case 'Bank Transfer':
				$methodDetails = self::getPaymenthodDetails($paymentmethodId);
				$providerDetails = self::getPaymentProviderDetails($providerId);
				$url = strtolower($providerDetails->name)."/request.php";
				$processorDetails = array('providerId' => $providerDetails->id, 'paymentMethod' => $methodDetails->id, 'providerName' => $providerDetails->name, 'cardName' => '', 'authKey1' => $providerDetails->credential1, 'authKey2' => $providerDetails->credential2, 'authKey3' => $providerDetails->credential3, 'requesturl' => $url);
				return $processorDetails;
				break;
			case 'Electronic Payment':
				
				break;
		}
	}
	
	//get payment type for current payment method
	public function current_paymenttype($methodId, $webId){
		$db = connect_db();
	    if (!$db || !$methodId || !$webId){
	            return false;
	    }
		
		$SQL  = "SELECT c.payment_type, p.name from config_frontend c ";
		$SQL .= "LEFT JOIN payments_types p ON (p.id = c.payment_type) ";
	    $SQL .= "WHERE c.payment_method_id = ? AND c.web_id = ?";
		
	    try {
	            $res = $db->prepare($SQL);
	            $res->execute(array($methodId, $webId));
				$result = $res->fetchObject();
	            return $result;
	    } catch(PDOException $ex) {
	            return false;
	    }
	}
	
	//Get list of payment types
	public function get_payment_types(){
		$db = connect_db();
	    if (!$db){
	        return false;
	    }
		
		$SQL  = "SELECT * from payments_types ";
	    $SQL .= "WHERE is_active = 1 ";
		
	    try {
	        $res = $db->prepare($SQL);
	        $res->execute();
	        return $res->fetchAll();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	

	//get provider details
	public function getProviderDetails($binid, $position, $type, $excludeProviders){
		$db = connect_db();
	    if (!$db || !$binid){
	            return false;
	    }
		 
		$SQL = "SELECT d.provider_id, p.* from distribute_processors d LEFT JOIN payments_providers p ON (p.id = d.provider_id) where d.binrule_id = ? AND d.position = ? AND d.type = ?";
		if(!empty($excludeProviders)){
			$SQL .= " and d.provider_id NOT IN (".implode(',', $excludeProviders).")";
		}

		try{
			$res = $db->prepare($SQL);
			$res->execute(array($binid, $position, $type));
			return $res->fetchAll();
		} catch(PDOException $ex){
			return false;
		}
	}

	

	//get processors list which are in distribution type

	public function getDistributionProcessors($binruleId, $type, $excludeProviders){
		$db = connect_db();
	    if (!$db || !$binruleId){
	            return false;
	    }
		
		$SQL = "SELECT GROUP_CONCAT(provider_id ORDER BY position asc) as processors from distribute_processors where binrule_id = ? and type = ?";
		if(!empty($excludeProviders)){
			$SQL .= " and provider_id NOT IN (".implode(',', $excludeProviders).")";
		}
		
		try{
			$res = $db->prepare($SQL);
			$res->execute(array($binruleId, $type));
			return $res->fetchAll();
		} catch(PDOException $ex){
			return false;
		}
	}
	
	
	//get payment method details
	public function getPaymenthodDetails($methodId){
		$db = connect_db();
	    if (!$db || !$methodId){
            return false;
	    }
		
		$SQL  = "SELECT * FROM payments_methods ";
	    $SQL .= "WHERE id = ? ";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($methodId));
			return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//get payment method details
	public function getPaymentProviderDetails($providerId){
		$db = connect_db();
	    if (!$db || !$providerId){
            return false;
	    }
		
		$SQL  = "SELECT * FROM payments_providers ";
	    $SQL .= "WHERE id = ? ";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($providerId));
			return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//count of transaction for current date
	public function hasone_transactions($cardNo, $type){
		$db = connect_db();
	    if (!$db || !$cardNo){
            return false;
	    }
	      
		$SQL  = "SELECT count(*) as cnt ";
	    $SQL .= "FROM payments_card pc ";
	    $SQL .= "INNER JOIN payments p ON (p.id = pc.payment_id) ";
	    $SQL .= "WHERE pc.cardnumber = ENCODE(".$cardNo.", 'xxfgtmnjidppbmyews@00910426#@$*') AND p.payment_method_id = ?";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($type));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//count of success transactions
	public function hasone_success_transactions($cardNo, $type){
		$db = connect_db();
	    if (!$db || !$cardNo){
            return false;
	    }
	      
		$SQL  = "SELECT count(*) as cnt ";
	    $SQL .= "FROM payments_card pc ";
	    $SQL .= "INNER JOIN payments p ON (p.id = pc.payment_id) ";
	    $SQL .= "WHERE pc.cardnumber = ENCODE(".$cardNo.", 'xxfgtmnjidppbmyews@00910426#@$*') AND p.payment_method_id = ? AND p.status = 'OK'";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($type));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	//count of success transaction based on player and cardno
	public function success_transactions($playerid, $cardNo, $method){
		$db = connect_db();
	    if (!$db || !$cardNo){
            return false;
	    }
	        
		$SQL  = "SELECT count(*) as cnt ";
	    $SQL .= "FROM payments_card pc ";
	    $SQL .= "INNER JOIN payments p ON (p.id = pc.payment_id) ";
	    $SQL .= "WHERE pc.cardnumber = ENCODE(".$cardNo.", 'xxfgtmnjidppbmyews@00910426#@$*') AND p.payment_method_id = ? AND p.player_id = ?";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($method, $playerid));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}

	
	//List of active gateways
	public function getActivegateways($method, $country, $routingProviders = null, $excludeProviders = null){
		
		$db = connect_db();
	    if (!$db || !$method || !$country){
            return false;
	    }
	    
		$RSQL = "";
		$ESQL = "";
		
		$SQL  = "SELECT p.* ";
	    $SQL .= "FROM payments_providers p ";
		$SQL .= "INNER JOIN payments_providers_countries pc ";
		$SQL .= "ON (pc.payment_provider_id = p.id) ";
	    $SQL .= "WHERE pc.country_id = ? ";
	    if(!empty($routingProviders)){
	    	$RSQL .= "AND p.id IN (".$routingProviders.") ";
	    }
	    if(!empty($excludeProviders)){
	    	$providers = implode(',', $excludeProviders);
	    	$ESQL .= "AND p.id NOT IN (".$providers.") ";
	    }
	    $SQL .= $RSQL.$ESQL."ORDER BY FIELD(p.id, ".$routingProviders.")";
		 // echo $SQL; die;
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($country));
            return $res->fetchAll();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	//override routing providers 
	public function checkRoutingOverride($method){
		$db = connect_db();
	    if (!$db || !$method){
	            return false;
	    }
	    
		$SQL  = "SELECT * ";
	    $SQL .= "FROM deposits_routing d ";
	    $SQL .= "WHERE d.landing_pmethod_id = ? AND d.is_override = 1";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($method));
            return $res->fetchAll();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//get processor details from routing configuration
	public function getProcessorDetails($depositcnt, $methodid){
		$db = connect_db();
	    if (!$db || !$depositcnt || !$methodid){
            return false;
	    }
	    
		$SQL  = "SELECT d.*,p.* ";
	    $SQL .= "FROM deposits_routing d INNER JOIN payments_providers p ON (p.id = d.landing_provider_id) ";
	    $SQL .= "WHERE d.landing_pmethod_id = ? AND d.deposit_count = ? AND d.status = 1";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($methodid,$depositcnt));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//get processor details from routing configuration
	public function getProcessorDetailsByProvider($providerId){
		$db = connect_db();
	    if (!$db || !$providerId){
            return false;
	    }
		
		$SQL  = "SELECT p.* ";
	    $SQL .= "FROM payments_providers p ";
	    $SQL .= "WHERE p.id = ?";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($providerId));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	//get processor details from routing configuration
	public function getProviderDetailsByProvider($providerId){
		$db = connect_db();
	    if (!$db || !$providerId){
            return false;
	    }
		
		$SQL  = "SELECT p.* ";
	    $SQL .= "FROM payments_providers p ";
	    $SQL .= "WHERE p.id = ?";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($providerId));
            $result = $res->fetchAll();
			return $result[0];
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//get dummy processor details from routing configuration
	public function getDummyProcessorDetails($methodId){
		$db = connect_db();
	    if (!$db || !$methodId){
            return false;
	    }
	    
		$SQL  = "SELECT pm.id as methodid,pm.name,pp.* ";
	    $SQL .= "FROM payments_methods pm INNER JOIN payments_providers_methods ppm ON (ppm.payment_method_id = ".$methodId.") INNER JOIN payments_providers pp ON (pp.id = ppm.payment_provider_id) ";
	    $SQL .= "WHERE pm.id = ?";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($methodId));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	//get dummy payment for player
	public function get_dummy_payment($playerId){ 
		$db = connect_db();
        if (!$db || !$playerId){
            return false;
        }
        
        $SQL  = "select count(*) as cnt from ";
        $SQL .= "((SELECT p.* FROM casino_cetrocon.payments p where p.player_id= ".$playerId." and p.status='SUCCESS') ";
		$SQL .=	" UNION (select * from casino_cetrocon.payments pm where pm.player_id= ".$playerId." and pm.payment_method_id IN (100,101))) as cnt";

        try {
            $res = $db->prepare($SQL);
            $res->execute();
            return $res->fetchObject();
        } catch(PDOException $ex) {
            return false;
        }
	}
	
	//Check card number exists or not
	public function get_payment_card_details($card_no){
        $db = connect_db();
        if (!$db || !$card_no){
            return false;
        }
		
        $SQL  = "SELECT DECODE(cardnumber, 'xxfgtmnjidppbmyews@00910426#@$*') as cardnumber ";
        $SQL .= "FROM payments_card ";
        $SQL .= "WHERE cardnumber = ENCODE(".$card_no.", 'xxfgtmnjidppbmyews@00910426#@$*') ";
        $SQL .= "LIMIT 1";

        try {
            $res = $db->prepare($SQL);
            $res->execute();
            return $res->fetchObject();
        } catch(PDOException $ex) {
            return false;
        }
	}
	
	public function get_has_real_transactions($playerId, $countstype = null, $perdaycount = null){
		$db = connect_db();
        if (!$db || !$playerId){
            return false;
        }

        $SQL  = "SELECT count(*) as cnt ";
        $SQL .= "FROM payments ";
        $SQL .= "WHERE player_id = ? ";
        $SQL .= "AND ((payment_method_id NOT IN (?, ?, ?)";
		if(!empty($countstype) && $countstype == 'success'){
			$SQL .= " AND status = 'OK')) ";
		}else if(!empty($countstype) && $countstype == 'all'){
			$SQL .= ")) ";
		}
		else if(empty($countstype)){
			$SQL .= " AND status = 'OK')) ";
		}
		if($perdaycount == 1){
			$todaydate = date('Y-m-d');
			$SQL .= "AND date(updated) = '".$todaydate."' ";
		}
        
        try {
            $res = $db->prepare($SQL);
            $res->execute(array($playerId, 104, 106, 107));
			// print_r($res->fetchObject());die;
            return $res->fetchObject();
        } catch(PDOException $ex) {
            return false;
        }
	}
	
	
	public function get_has_played_realgames($playerId){
		$db = connect_db();
	    if (!$db || !$playerId){
            return false;
	    }
	
	    $SQL  = "SELECT count(*) as cnt ";
	    $SQL .= "FROM transactions ";
	    $SQL .= "WHERE player_id = ?";
	
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($playerId));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	public function check_has_bonus_exchanged($playerId){
		$db = connect_db();
	    if (!$db || !$playerId){
            return false;
	    }
	
	    $SQL  = "SELECT count(*) as cnt ";
	    $SQL .= "FROM players_bonuses ";
	    $SQL .= "WHERE player_id = ?";
	    $SQL .= " AND bonus_status_id = 3";
	
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($playerId));
            return $res->fetchObject();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	public function checkPaymentToOverride($playerId, $creditcard, $method, $country, $excludeproviders = null, $providers)
	{
		$db = connect_db();
	    if (!$db || !$playerId || !$creditcard || !$method || !$country){
            return false;
	    }
		
		$SQL  = "SELECT p.*,pc.cardnumber ";
	    $SQL .= "FROM payments p ";
		$SQL .= "INNER JOIN payments_card pc ON (pc.payment_id = p.id) ";
		$SQL .= "INNER JOIN payments_providers_countries ppc ON (ppc.payment_provider_id = p.payment_provider_id) ";
	    $SQL .= "WHERE pc.cardnumber = ? ";
	    $SQL .= "AND p.status = 'OK' AND p.payment_method_id = ? AND ppc.country_id = ? AND p.payment_provider_id IN (".$providers.") ";
		if(!empty($excludeproviders)){
			$providers = implode(',', $excludeproviders);
			$SQL .= "AND p.payment_provider_id NOT IN (".$providers.") ";
		}
		$SQL .= "GROUP BY p.payment_provider_id ";
		$SQL .= "ORDER BY p.id desc";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($creditcard, $method, $country));
            return $res->fetchAll();
	    } catch(PDOException $ex) {
            return false;
	    }
	}

	function get_player_all_details($player_id){
        $db = connect_db();
        if (!$db || !$player_id){
            return false;
        }
        
        $SQL  = "SELECT * ";
        $SQL .= "FROM players ";
        $SQL .= "WHERE id = ? ";
        $SQL .= "LIMIT 1";

        try {
            $res = $db->prepare($SQL);
            $res->execute(array($player_id));
            return $res->fetchObject();
        } catch(PDOException $ex) {
            return false;
        }
	}

	//get continent name
	public function getContinentName($country){
		$db = connect_db();
	    if (!$db || !$country){
            return false;
	    }
	    
	    $SQL = "SELECT continent from countries where iso = ?";
	    try{
	    	$res = $db->prepare($SQL);
	    	$res->execute(array($country));
	    	return $res->fetchObject();
	    } catch(PDOException $ex) {
	    	return false;
	    }
	}
	
	//check kyc received for card number
	public function check_kyc_received($playerid, $cardno){
		$db = connect_db();
	    if (!$db || !$playerid || !$cardno){
            return false;
	    }
	    
	    $SQL = "SELECT kyc_received from player_kyc where player_id = ? AND card_no = ENCODE(".$cardno.", 'xxfgtmnjidppbmyews@00910426#@$*')";
	    try{
	    	$res = $db->prepare($SQL);
	    	$res->execute(array($playerid));
	    	return $res->fetchObject();
	    } catch(PDOException $ex) {
	    	return false;
	    }
	}


    //check card blocked or not
    public function is_cardblocked($playerid, $cardno){
        $db = connect_db();
        if (!$db || !$playerid || !$cardno){
            return false;
        }

        $SQL = "SELECT is_blocked from player_kyc where card_no = ENCODE(".$cardno.", 'xxfgtmnjidppbmyews@00910426#@$*') and is_blocked = 1 limit 1";
        try{
            $res = $db->prepare($SQL);
            $res->execute();
            $res=$res->fetchObject();
            if(!$res){
				/*
                $sql_1="select * from card_details where card_number = ENCODE(".$cardno.", 'xxfgtmnjidppbmyews@00910426#@$*')  limit 1";
                try{
                    $result = $db->prepare($sql_1);
                    $result->execute();
                    $resultval=$result->fetchObject();
                    if($resultval){
                        $resultval->is_blocked=1;
                        return $resultval;
                    } else {
                        return false;
                    }

                }catch(PDOException $ex) {
                    return false;
                } */
				return "Inside data";
            }else{
                return $res;
            }

        } catch(PDOException $ex) {
            return false;
        }
    }

	
	//get available processors from defined routing
	public function getAvilableProcessors($methodId, $continent, $country, $playerClass){
		$db = connect_db();
	    if (!$db || !$methodId){
	            return false;
	    }
	    
	    $SQL  = "SELECT d.id as binid,d.priority,d.player_per_day,d.player_per_week,d.player_per_month,d.card_per_day,d.card_per_week,d.card_per_month,GROUP_CONCAT(r.processor_id order by r.id asc) as processors,d.routing_success_rate,d.routing_last_success_mid, dr.* from define_processors_routing d ";
	    $SQL .= "INNER JOIN routing_processors r ON (r.routing_id = d.id) ";
		$SQL .= "INNER JOIN distribution_rules dr ON (dr.binrule_id = d.id) ";
		$SQL .= "WHERE d.method_id = ? AND (d.continent = ? OR d.continent = 'all') ";
		$SQL .= "AND (d.country = ? OR d.country = 'all') ";
		$SQL .= "AND (d.player_level = ? OR d.player_level = 'all') ";
		$SQL .= "AND d.status = 1 ";
		$SQL .= "GROUP BY r.routing_id,dr.id ";
		$SQL .= "ORDER BY d.priority asc";
		// echo $SQL; die;
		try{
			$res = $db->prepare($SQL);
			$res->execute(array($methodId, $continent, $country, $playerClass));
			return $res->fetchAll();
		} catch(PDOException $ex){
			return false;
		}
	} 

	//get payment method details
	public function getPaymentProviderLimits($providerId){
		$db = connect_db();
	    if (!$db || !$providerId){
            return false;
	    }
		$providers = implode(',', $providerId);
		$SQL  = "SELECT email_limit_hour, email_limit_day, email_limit_week, email_limit_month, email_limit_always, card_limit_hour, card_limit_day, card_limit_week, card_limit_month, card_limit_always FROM payments_providers ";
	    $SQL .= "WHERE id IN (".$providerId.") order by FIELD(id, ".$providerId.")";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute();
			return $res->fetchAll();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	//get processor limits based on bin rules
	public function getBinWiseProcessorLimits($binRuleId){
		$db = connect_db();
	    if (!$db || !$binRuleId){
            return false;
	    }
		$SQL  = "SELECT d.p_player_per_day,d.p_player_per_week,d.p_player_per_month,d.p_card_per_day,d.p_card_per_week,d.p_card_per_month FROM define_processors_routing d ";
	    $SQL .= "WHERE d.id = ?";
		
	    try {
            $res = $db->prepare($SQL);
            $res->execute(array($binRuleId));
			return $res->fetchAll();
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	/***
	 * 
	 * params @$depostcounts, @$limts
	 * This function compares the deposits counts with providers limits and return exceeded provider ids
	 * 
	 */
	public function compare_arrays($deposits, $limits){
		$excludeProvider = array();
		foreach ($deposits as $key => $value) {
			foreach ($value as $key1 => $val) {
				if($val >= $limits[$key][$key1] && $limits[$key][$key1] != -1){
					$excludeProvider[] = $key1;
				}
			}
		}
		return $excludeProvider;
	}
	
	/***
	 * 
	 * params @$playerId, @$providers, @$creditcardno
	 * This function gets the different counts and checks for provider limits to return excluded providers list
	 * 
	 */
	public function getPlayerTransactioncount($playerId, $providerIds, $creditcard, $webid, $binId){
		$db = connect_db();
	    if (!$db || !$playerId || !$providerIds){
	            return false;
	    }
	    
	    $providerLimits = self::getPaymentProviderLimits($providerIds);	
		$binruleProcessorLimits = self::getBinWiseProcessorLimits($binId);	
		$currentdate = date('Y-m-d');
		$providers = array_unique(explode(',', $providerIds));
		
		//Deposit counts by player id
		//$SQL = "SELECT COUNT(IF(date(updated) = '".$currentdate."', 1, null)) daycnt, COUNT(IF(date(updated) >= '".$this_week_sd."' and date(updated) <= '".$this_week_ed."', 1, null)) weekcnt, COUNT(IF(date(updated) >= '".$month_sd."' and date(updated) <= '".$month_ed."', 1, null)) monthcnt, count(id) as count from payments where payment_provider_id IN (".$providers.") and player_id = ".$playerId." and status = 'OK' group by payment_provider_id order by FIELD(payment_provider_id, ".$providers.")";
		$SQL = "SELECT sum(hour) hour,sum(day) day,sum(week) week,sum(month) month,sum(always) always FROM counts_per_player WHERE provider_id IN (".$providerIds.") and player_id = ".$playerId." group by provider_id order by FIELD(provider_id, ".$providerIds.")";
		try {
            $res = $db->prepare($SQL);
            $res->execute();
            $emailwise = $res->fetchAll();
	    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            //return false;
	    }
		
		//Deposit counts by credit card number
		//$CARD = "SELECT COUNT(IF(date(p.updated) = '".$currentdate."', 1, null)) daycnt, COUNT(IF(date(p.updated) >= '".$this_week_sd."' and date(p.updated) <= '".$this_week_ed."', 1, null)) weekcnt, COUNT(IF(date(p.updated) >= '".$month_sd."' and date(p.updated) <= '".$month_ed."', 1, null)) monthcnt, count(p.id) as count from payments p INNER JOIN payments_card pc ON (pc.payment_id = p.id) where pc.cardnumber ='".$creditcard."' AND p.payment_provider_id IN (".$providers.") and p.status = 'OK' group by p.payment_provider_id order by FIELD(p.payment_provider_id, ".$providers.")";
		$CARD = "SELECT sum(hour) hour,sum(day) day,sum(week) week,sum(month) month,sum(always) always FROM counts_per_card WHERE provider_id IN (".$providerIds.") and card_no = ENCODE(".$creditcard.", 'xxfgtmnjidppbmyews@00910426#@$*') group by provider_id order by FIELD(provider_id, ".$providerIds.")";
		
		try {
            $res = $db->prepare($CARD);
            $res->execute();
            $cardwise = $res->fetchAll();
	    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
	    }
		//assign email wise counts and limits to array
		$p = 0;
		if($emailwise){
			foreach ($providers as $value) {
				$countsarr['hour'][$value] = !empty($emailwise[$p]['hour']) ? $emailwise[$p]['hour'] : 0;
				$countsarr['day'][$value] = !empty($emailwise[$p]['day']) ? $emailwise[$p]['day'] : 0;
				$countsarr['week'][$value] = !empty($emailwise[$p]['week']) ? $emailwise[$p]['week'] : 0;
				$countsarr['month'][$value] = !empty($emailwise[$p]['month']) ? $emailwise[$p]['month'] : 0;
				$countsarr['always'][$value] = !empty($emailwise[$p]['always']) ? $emailwise[$p]['always'] : 0;
				$p++;
			}
		}
		$l = 0;
		foreach ($providers as $value) {
			$emaillimitsarr['hour'][$value] = $providerLimits[$l]['email_limit_hour'];
			$emaillimitsarr['day'][$value] = ($binruleProcessorLimits[0]['p_player_per_day'] > 0) ? $binruleProcessorLimits[0]['p_player_per_day'] : $providerLimits[$l]['email_limit_day'];
			$emaillimitsarr['week'][$value] = ($binruleProcessorLimits[0]['p_player_per_week'] > 0) ? $binruleProcessorLimits[0]['p_player_per_week'] : $providerLimits[$l]['email_limit_week'];
			$emaillimitsarr['month'][$value] = ($binruleProcessorLimits[0]['p_player_per_month'] > 0) ? $binruleProcessorLimits[0]['p_player_per_month'] : $providerLimits[$l]['email_limit_month'];
			$emaillimitsarr['always'][$value] = $providerLimits[$l]['email_limit_always'];
			$l++;
		}
		//assign card wise counts and limits to array
		$c = 0;
		if($cardwise){
			foreach ($providers as $value) {
				$cardcountsarr['hour'][$value] = !empty($cardwise[$c]['hour']) ? $cardwise[$c]['hour'] : 0;
				$cardcountsarr['day'][$value] = !empty($cardwise[$c]['day']) ? $cardwise[$c]['day'] : 0;
				$cardcountsarr['week'][$value] = !empty($cardwise[$c]['week']) ? $cardwise[$c]['week'] : 0;
				$cardcountsarr['month'][$value] = !empty($cardwise[$c]['month']) ? $cardwise[$c]['month'] : 0;
				$cardcountsarr['always'][$value] = !empty($cardwise[$c]['always']) ? $emailwise[$c]['always'] : 0;
				$c++;
			}
		}
		$i = 0;
		foreach ($providers as $value) {
			$cardlimitsarr['hour'][$value] = $providerLimits[$i]['card_limit_hour'];
			$cardlimitsarr['day'][$value] = ($binruleProcessorLimits[0]['p_card_per_day'] > 0) ? $binruleProcessorLimits[0]['p_card_per_day'] : $providerLimits[$i]['card_limit_day'];
			$cardlimitsarr['week'][$value] = ($binruleProcessorLimits[0]['p_card_per_week'] > 0) ? $binruleProcessorLimits[0]['p_card_per_week'] : $providerLimits[$i]['card_limit_week'];
			$cardlimitsarr['month'][$value] = ($binruleProcessorLimits[0]['p_card_per_month'] > 0) ? $binruleProcessorLimits[0]['p_card_per_month'] : $providerLimits[$i]['card_limit_month'];
			$cardlimitsarr['always'][$value] = $providerLimits[$i]['card_limit_always'];
			$i++;
		}
		
		//compare and merge two arrays and get unique values into new array
	    $emailexcludeProviders = self::compare_arrays($countsarr, $emaillimitsarr);
		$cardexcludeProviders = self::compare_arrays($cardcountsarr, $cardlimitsarr);
		$excludeProviders = array_unique(array_merge($emailexcludeProviders, $cardexcludeProviders));
		
		//return final provider id's
		return $excludeProviders;
	}

	
	public function getPlayerandCardCounts($playerId, $cardNo){
		$db = connect_db();
	    if (!$db || !$playerId){
            return false;
	    }
		//Deposit counts by player id
		$SQL = "SELECT sum(day) day,sum(week) week,sum(month) month FROM counts_per_player WHERE player_id = ".$playerId." group by player_id";
		try {
            $res = $db->prepare($SQL);
            $res->execute();
            $playerCounts = $res->fetchAll();
	    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
	    }
		
		//Deposit counts by credit card number
		$CARD = "SELECT sum(day) day,sum(week) week,sum(month) month FROM counts_per_card WHERE card_no = '".$cardNo."' group by card_no";
		try {
            $res = $db->prepare($CARD);
            $res->execute();
            $cardCounts = $res->fetchAll();
	    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
	    }
		$playercardcounts = array($playerCounts, $cardCounts);
		return $playercardcounts;
	}

  

	
	/***
	 * 
	 * params @$orederno, @$playerid, @$creditcardno, @$paymentmethodid, @$paymentid, @$vtpayment, @$countryid
	 * This function returns the provider name
	 * main function for checking providers conditions and dummy deposits
	 * and also handling payments requests from crm dummy deposits process and virtual terminal payments
	 * 
	 */
	public function checkProvider_conditions($orderno, $playerId, $creditCardno, $methodId, $paymentId = null, $providersId, $virtualPayment = null, $countryid, $webid){
		$methodData = self::getPaymenthodDetails($methodId);
		$check_dummy_money = self::get_dummy_payment($playerId);
		// echo "IIIISSSSSSSSS"; die;
		//Handling virtual terminal payments and dummy deposits processing
		if(!empty($paymentId) || !empty($virtualPayment)){
			
            //Check card blocked or not
            $checkiscardblocked = self::is_cardblocked($playerId, $creditCardno);
			// print_r($checkiscardblocked); die;
            if($checkiscardblocked->is_blocked == 1 )
            {
				echo "111111"; die;
                $url = "classes/failure_request.php";
                $processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "Card Blocked", 'errorcode' => '', 'binrule_id' => '');
                return $processorDetails;
            }

			$paymentMethodData = self::getPaymenthodDetails($methodId);
			$paymentProviderData = self::getProcessorDetailsByProvider($providersId);
			$url = "classes/".strtolower($paymentProviderData->gateway_class)."_request.php";
			$processorDetails = array('providerId' => $paymentProviderData->landing_provider_id, 'paymentMethod' => $paymentMethodData->id, 'providerName' => $paymentProviderData->gateway_class, 'cardName' => $paymentMethodData->name, 'authKey1' => $paymentProviderData->credential1, 'authKey2' => $paymentProviderData->credential2, 'authKey3' => $paymentProviderData->credential3, 'authKey4' => $paymentProviderData->credential4, 'requesturl' => $url, 'binrule_id' => '', 'kyc_approve' => $paymentProviderData->is_kyc_approval);
			return $processorDetails;
		}
		
		$card_details = self::get_payment_card_details($creditCardno);
		$card_number = !empty($card_details->cardnumber) ? $card_details->cardnumber : '';
		//Check if player has real transactions
		$real_transactions = self::get_has_real_transactions($playerId);
		//Check if player has real transactions based on card
		$real_card_transactions = self::hasone_success_transactions($creditCardno, $methodId);
		//Check if player has played games
		$has_played = self::get_has_played_realgames($playerId);
		//Check if player has bonus exchanged
		$has_bonus_exchanged = self::check_has_bonus_exchanged($playerId);
		//get continent name from country
		$continent = self::getContinentName($countryid);
		
		//get all player details
		$details = self::get_player_all_details($playerId);
		
		
		//print_r($has_played);print_r($has_bonus_exchanged);exit;
		
		
		
		//check if dummy available or not
		if($methodData->is_dummy == 1 && ($card_number != $creditCardno) && ($check_dummy_money->cnt <= 0)){
			
			/*for making directly to dummy payment for first time*/
	        
			if($methodData->direct_dummy == 1){
			$allow_dummy=1;	
			if($methodData->check_play_bonus == 1 ){
				
				if($has_played->cnt > 1 && $has_bonus_exchanged->cnt > 0){
					$allow_dummy=0;
				}
			}
 			if($allow_dummy == 1){
		    $paymentMethodData = self::getPaymenthodDetails($methodId);
			$providersId = ($methodId == 100) ? 111 : 112;
			$method_id =  ($methodId == 100) ? 106 : 107;
			$paymentProviderData = self::getProcessorDetailsByProvider($providersId);
			
			
			$url = "classes/".strtolower($paymentProviderData->gateway_class)."_request.php"; 
			$processorDetails = array('providerId' => $paymentProviderData->id, 'paymentMethod' => $method_id, 'providerName' => $paymentProviderData->gateway_class, 'cardName' => $paymentMethodData->name, 'authKey1' => $paymentProviderData->credential1, 'authKey2' => $paymentProviderData->credential2, 'authKey3' => $paymentProviderData->credential3, 'authKey4' => $paymentProviderData->credential4, 'requesturl' => $url);
			return $processorDetails;
			}
			}
			/*End for making directly to dummy payment for first time*/
		
			//Check card blocked or not
	 		$checkiscardblocked = self::is_cardblocked($playerId, $creditCardno);		
			if( $checkiscardblocked->is_blocked == 1 )
			{
				$url = "classes/failure_request.php";
				$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "Card Blocked", 'errorcode' => '', 'binrule_id' => '');
				return $processorDetails;
			}
			
			/**
			 * Code modified for adding dummy money, if player first transaction fails start 
			**/
			// echo "111111"; die;
			$processorsList = self::getAvilableProcessors($methodId, $continent->continent, $countryid, $details->players_classes_id);
			
			//echo "<pre>"; print_r($processorsList[0]); die;
			if($processorsList){
				// echo "111111"; die;
				
				$processorsForRouting = explode(',', $processorsList[0]['processors']);
				if($processorsList[0]['is_default'] == 1 || ($processorsList[0]['is_distribution'] == 0 && $processorsList[0]['is_routing'] == 0)){  //it follows default routing
					$providersarray = $processorsList[0]['processors'];
					//get list of active gateways by excluding providers from limits filter
					$active_gateways = self::getActivegateways($methodId, $countryid, $providersarray, $providersExclude);
					
					if($active_gateways){					
						$url = "classes/".strtolower($active_gateways[0]['gateway_class'])."_request.php";
						//return details need to send payment to gateway with credentials
						$processorDetails = array('providerId' => $active_gateways[0]['id'], 'paymentMethod' => $methodData->id, 'providerName' => $active_gateways[0]['name'], 'cardName' => $methodData->name, 'authKey1' => $active_gateways[0]['credential1'], 'authKey2' => $active_gateways[0]['credential2'], 'authKey3' => $active_gateways[0]['credential3'], 'authKey4' => $active_gateways[0]['credential4'], 'requesturl' => $url, 'message' => '', 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $active_gateways[0]['is_kyc_approval']);
					}else{
						//fails the transaction if player doesn't meet the conditions
						$url = "classes/failure_request.php";
						$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '', 'binrule_id' => '');
					}
					return $processorDetails;
				}elseif($processorsList[0]['is_distribution'] == 1){  //it follows distribution routing
					$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 1, $providersExclude);
					$providersarray = $distributedProcessors[0]['processors'];
					
					//get list of active gateways by excluding providers from limits filter
					$active_gateways = self::getProviderDetails($processorsList[0]['binid'], 1, 1, $providersExclude);
					
					if($active_gateways){
						$url = "classes/".strtolower($active_gateways[0]['gateway_class'])."_request.php";
						//return details need to send payment to gateway with credentials
						$processorDetails = array('providerId' => $active_gateways[0]['id'], 'paymentMethod' => $methodData->id, 'providerName' => $active_gateways[0]['name'], 'cardName' => $methodData->name, 'authKey1' => $active_gateways[0]['credential1'], 'authKey2' => $active_gateways[0]['credential2'], 'authKey3' => $active_gateways[0]['credential3'], 'authKey4' => $active_gateways[0]['credential4'], 'requesturl' => $url, 'message' => '', 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $active_gateways[0]['is_kyc_approval']);
					}else{
						//fails the transaction if player doesn't meet the conditions
						$url = "classes/failure_request.php";
						$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '');
					}
					return $processorDetails;
				}else if($processorsList[0]['is_routing'] == 1){  //it follows routing based on all counts or success counts
					
					if($processorsList[0]['counts_type'] == 1){
						$disttype = 2;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 2, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						//get player all transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'all', $processorsList[0]['is_day_count']);
					}elseif($processorsList[0]['counts_type'] == 2){
						$disttype = 3;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 3, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						//get player all success transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'success', $processorsList[0]['is_day_count']);
					}elseif($processorsList[0]['counts_type'] == 3){
						$disttype = 4;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 4, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						//get player all success transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'success', $processorsList[0]['is_day_count']);
					}
					
					
					//deposit count
					$providerslist = explode(',', $providersarray);
					
					//get list of active gateways by excluding providers from limits filter
					$active_gateways = self::getProviderDetails($processorsList[0]['binid'], 1, $disttype, $providersExclude);
					
					$active_gateways = !empty($active_gateways) ? $active_gateways[0] : $distributedProcessors[0];
					if(empty($active_gateways['name'])){
						$active_gateways = self::getProviderDetailsByProvider($active_gateways[0]);
					}
					if($active_gateways){
						$url = "classes/".strtolower($active_gateways['gateway_class'])."_request.php";
						//return details need to send payment to gateway with credentials
						$processorDetails = array('providerId' => $active_gateways['id'], 'paymentMethod' => $methodData->id, 'providerName' => $active_gateways['name'], 'cardName' => $methodData->name, 'authKey1' => $active_gateways['credential1'], 'authKey2' => $active_gateways['credential2'], 'authKey3' => $active_gateways['credential3'], 'authKey4' => $active_gateways['credential4'], 'requesturl' => $url, 'message' => '', 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $active_gateways['is_kyc_approval']);
					}else{
						//fails the transaction if player doesn't meet the conditions
						$url = "classes/failure_request.php";
						$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '');
					}
					
					return $processorDetails;
				}
			}else{
				
				//fails the transaction if player doesn't meet the conditions
				$url = "classes/failure_request.php";
				$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No processors for this continent or country or player class", 'errorcode' => '');
				return $processorDetails;
			}
			/**
			 * Code modified for adding dummy money, if player first transaction fails end 
			**/
		}else if($real_transactions->cnt > 0 || $methodData->is_dummy == 0 || ($has_played->cnt > 1 || $has_bonus_exchanged->cnt > 0)){
			// echo "33333";
			//get transactions count for current date
			$currentDate = date('Y-m-d');
			$transactioncount = self::hasone_transactions($creditCardno, $methodId);
			
			//Check card blocked or not
	 		$checkiscardblocked = self::is_cardblocked($playerId, $creditCardno);		
			if( $checkiscardblocked->is_blocked == 1 )
			{
				
				$url = "classes/failure_request.php";
				$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "Card Blocked", 'errorcode' => '');
				return $processorDetails;
			}
			
			//get defined routing based on continent and country and player levels
			$processorsList = self::getAvilableProcessors($methodId, $continent->continent, $countryid, $details->players_classes_id);
			// echo "<pre>"; print_r($processorsList); die;
			if($processorsList){
				$processorsForRouting = explode(',', $processorsList[0]['processors']);
				
			}else{
				// echo "12";
				//fails the transaction if player doesn't meet the conditions
				$url = "classes/failure_request.php";
				$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No processors for this continent or country or player class", 'errorcode' => '');
				
				return $processorDetails;
			}

			if($methodData->is_kyc_check == 1){
				
				//check if the player submitted KYC documents or not
				$successtransactioncount = self::success_transactions($playerId, $creditCardno, $methodId);
				$checkKycReceived = self::check_kyc_received($playerId, $creditCardno);
				if(($successtransactioncount->cnt >= $methodData->allowed_transactions) && ($checkKycReceived->kyc_received == 0)){
					
					//fails the transaction if player reaches allowed transactions count
					$url = "classes/failure_request.php";
					$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'authKey4' => '', 'requesturl' => $url, 'message' => "Player didn't send the KYC documents", 'errorcode' => '');
					return $processorDetails;
				}
			}
			
			//Check player & card limits based on bin rules
			if($processorsList){
				
				$playerrulelimits = array('0' => $processorsList[0]['player_per_day'], '1' => $processorsList[0]['player_per_week'], '2' => $processorsList[0]['player_per_month']);
				$cardrulelimits = array('0' => $processorsList[0]['card_per_day'], '1' => $processorsList[0]['card_per_week'], '2' => $processorsList[0]['card_per_month']);
				$playercounts = self::getPlayerandCardCounts($playerId, $creditCardno);
				$limits_exceeded = 0;
				for($i=0;$i<count($playercounts[0][0]);$i++){
					if($playerrulelimits[$i] > 0 && $playercounts[0][0][$i] >= $playerrulelimits[$i]){
						$limits_exceeded = 1;
					}
				}
				for($i=0;$i<count($playercounts[1][0]);$i++){
					if($cardrulelimits[$i] > 0 && $playercounts[1][0][$i] >= $cardrulelimits[$i]){
						$limits_exceeded = 1;
					}
				}
				if($limits_exceeded == 1){
					
					//fails the transaction if player reaches bin rule allowed transactions count
					$url = "classes/failure_request.php";
					$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'authKey4' => '', 'requesturl' => $url, 'message' => "Limits got exceeded", 'errorcode' => 10407);
					return $processorDetails;
				}
				
			}
			
			$providersExclude = array();
			//check limits per email id and credit card
			$providersExclude = self::getPlayerTransactioncount($playerId, $processorsList[0]['processors'], $creditCardno, $webid, $processorsList[0]['binid']);
			
			//last success processor
			if($processorsList[0]['routing_last_success_mid'] == 1){
				
				$checkLastSuccessDeposit = self::checkLastSuccessDeposit($playerId);
				if($checkLastSuccessDeposit && $checkLastSuccessDeposit->is_active == 1){
					$get_routing = self::getProcessorDetailsByProvider($checkLastSuccessDeposit->payment_provider_id);
					$url = "classes/".strtolower($get_routing->gateway_class)."_request.php";
					$processorDetails = array('providerId' => $get_routing->id, 'paymentMethod' => $checkLastSuccessDeposit->payment_method_id, 'providerName' => $get_routing->name, 'cardName' => $methodData->name, 'authKey1' => $get_routing->credential1, 'authKey2' => $get_routing->credential2, 'authKey3' => $get_routing->credential3, 'authKey4' => $get_routing->credential4, 'requesturl' => $url, 'binrule_id' => $processorsList[0]['binid']);
					return $processorDetails;
				}
			}
			
			if($processorsList[0]['is_default'] == 1 || ($processorsList[0]['is_distribution'] == 0 && $processorsList[0]['is_routing'] == 0)){  //it follows default routing
				
				$providersarray = $processorsList[0]['processors'];
				// print_r($providersarray); die;

				// $transactionscount->cnt = $transactioncount->cnt;
				$transactionscountcnt = 0;
				// echo "<pre>"; print_r($transactionscount->cnt); die;
				//get list of active gateways by excluding providers from limits filter
				
				$active_gateways = self::getActivegateways($methodId, $countryid, $providersarray, $providersExclude);
				// echo "22222"; die;
				// print_r($active_gateways); die;
				if(count($active_gateways) > 0){
				// $depositcnt = $transactionscount->cnt % count($active_gateways);	
				$depositcnt = $transactionscountcnt % count($active_gateways);
				}else{
				$depositcnt =0;	
				} 
				//deposit count
				
				
				
				if($active_gateways){
					//Check override routing is enabled
					if($active_gateways[$depositcnt]['is_override'] == 1){
						$checkPayment = self::checkPaymentToOverride($playerId, $creditCardno, $methodId, $countryid, $providersExclude, $providersarray);
						
						if($checkPayment){
							foreach ($checkPayment as $cpayment) {
								$get_routing = self::getProcessorDetailsByProvider($cpayment['payment_provider_id']);
								$url = "classes/".strtolower($get_routing->gateway_class)."_request.php";
								$processorDetails = array('providerId' => $get_routing->id, 'paymentMethod' => $methodData->id, 'providerName' => $get_routing->name, 'cardName' => $methodData->name, 'authKey1' => $get_routing->credential1, 'authKey2' => $get_routing->credential2, 'authKey3' => $get_routing->credential3, 'authKey4' => $get_routing->credential4, 'requesturl' => $url, 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $get_routing->is_kyc_approval);
								return $processorDetails;
							}
						}
					}
					
					if($processorsList[0]['routing_success_rate'] == 1){
						
					}
					
					$url = "classes/".strtolower($active_gateways[$depositcnt]['gateway_class'])."_request.php";
					//return details need to send payment to gateway with credentials
					$processorDetails = array('providerId' => $active_gateways[$depositcnt]['id'], 'paymentMethod' => $methodData->id, 'providerName' => $active_gateways[$depositcnt]['name'], 'cardName' => $methodData->name, 'authKey1' => $active_gateways[$depositcnt]['credential1'], 'authKey2' => $active_gateways[$depositcnt]['credential2'], 'authKey3' => $active_gateways[$depositcnt]['credential3'], 'authKey4' => $active_gateways[$depositcnt]['credential4'], 'requesturl' => $url, 'message' => '', 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $active_gateways[$depositcnt]['is_kyc_approval']);
				}else{
					
					//fails the transaction if player doesn't meet the conditions
					$url = "classes/failure_request.php";
					$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '', 'binrule_id' => $processorsList[0]['binid']);
				}
				return $processorDetails;
			}else{
				
				if($real_card_transactions->cnt <= 0 && $processorsList[0]['is_distribution'] == 1){  //it follows distribution routing
					$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 1, $providersExclude);
					$providersarray = $distributedProcessors[0]['processors'];
					$transactionscount->cnt = $transactioncount->cnt;
					
					//deposit count
					$depositcnt = ($transactionscount->cnt % count(explode(',', $providersarray))) + 1;
					
					//get list of active gateways by excluding providers from limits filter
					$active_gateways = self::getProviderDetails($processorsList[0]['binid'], $depositcnt, 1, $providersExclude);
					
					if($active_gateways){
						//Check override routing is enabled
						if($active_gateways[0]['is_override'] == 1){
							$checkPayment = self::checkPaymentToOverride($playerId, $creditCardno, $methodId, $countryid, $providersExclude, $providersarray);
							if($checkPayment){
								foreach ($checkPayment as $cpayment) {
									$get_routing = self::getProcessorDetailsByProvider($cpayment['payment_provider_id']);
									$url = "classes/".strtolower($get_routing->gateway_class)."_request.php";
									$processorDetails = array('providerId' => $get_routing->id, 'paymentMethod' => $methodData->id, 'providerName' => $get_routing->name, 'cardName' => $methodData->name, 'authKey1' => $get_routing->credential1, 'authKey2' => $get_routing->credential2, 'authKey3' => $get_routing->credential3, 'authKey4' => $get_routing->credential4, 'requesturl' => $url, 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $get_routing->is_kyc_approval);
									return $processorDetails;
								}
							}
						}
						
						$url = "classes/".strtolower($active_gateways[0]['gateway_class'])."_request.php";
						//return details need to send payment to gateway with credentials
						$processorDetails = array('providerId' => $active_gateways[0]['id'], 'paymentMethod' => $methodData->id, 'providerName' => $active_gateways[0]['name'], 'cardName' => $methodData->name, 'authKey1' => $active_gateways[0]['credential1'], 'authKey2' => $active_gateways[0]['credential2'], 'authKey3' => $active_gateways[0]['credential3'], 'authKey4' => $active_gateways[0]['credential4'], 'requesturl' => $url, 'message' => '', 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $active_gateways[0]['is_kyc_approval']);
					}else{
						//fails the transaction if player doesn't meet the conditions
						$url = "classes/failure_request.php";
						$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '', 'binrule_id' => $processorsList[0]['binid']);
					}
					return $processorDetails;
				}else if($processorsList[0]['is_routing'] == 1){  //it follows routing based on all counts or success counts
					if($processorsList[0]['counts_type'] == 3 && $real_card_transactions->cnt <= 0){
						//fails the transaction if player doesn't meet the conditions
						$url = "classes/failure_request.php";
						$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "Player doen't have atleast one success transaction", 'errorcode' => '', 'binrule_id' => $processorsList[0]['binid']);
						return $processorDetails;
					}
					//check players is one month old depositor
					$checkPlayer = self::checkPlayerOneMonthOld($playerId);
					
					if($processorsList[0]['counts_type'] == 4 && $checkPlayer->noofdays > 31){
						$disttype = 4;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 4, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						
						//get player all transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'all', $processorsList[0]['is_day_count']);
					}else{
						$processorsList[0]['counts_type'] = 1;
					}
					if($processorsList[0]['counts_type'] == 1){
						
						$disttype = 2;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 2, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						
						//get player all transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'all', $processorsList[0]['is_day_count']);
					}elseif($processorsList[0]['counts_type'] == 2){
						$disttype = 3;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 3, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						//get player all success transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'success', $processorsList[0]['is_day_count']);
					}elseif($processorsList[0]['counts_type'] == 3){
						$disttype = 4;
						$distributedProcessors = self::getDistributionProcessors($processorsList[0]['binid'], 4, $providersExclude);
						$providersarray = $distributedProcessors[0]['processors'];
						//get player all success transactions count
						$transactionscount = self::get_has_real_transactions($playerId, 'success', $processorsList[0]['is_day_count']);
					}
					
					//deposit count
					$providerslist = explode(',', $providersarray);
					$depositcnt = ($transactionscount->cnt % count($providerslist)) + 1;
					
					//get list of active gateways by excluding providers from limits filter
					$active_gateways = self::getProviderDetails($processorsList[0]['binid'], $depositcnt, $disttype, $providersExclude);
					
					$active_gateways = !empty($active_gateways) ? $active_gateways[0] : $distributedProcessors[$depositcnt-1];
					if(empty($active_gateways['name'])){
						$active_gateways = self::getProviderDetailsByProvider($active_gateways[0]);
					}
					if($active_gateways){
						//Check override routing is enabled
						if($active_gateways['is_override'] == 1){
							$checkPayment = self::checkPaymentToOverride($playerId, $creditCardno, $methodId, $countryid, $providersExclude, $providersarray);
							if($checkPayment){
								foreach ($checkPayment as $cpayment) {
									$get_routing = self::getProcessorDetailsByProvider($cpayment['payment_provider_id']);
									$url = "classes/".strtolower($get_routing->gateway_class)."_request.php";
									$processorDetails = array('providerId' => $get_routing->id, 'paymentMethod' => $methodData->id, 'providerName' => $get_routing->name, 'cardName' => $methodData->name, 'authKey1' => $get_routing->credential1, 'authKey2' => $get_routing->credential2, 'authKey3' => $get_routing->credential3, 'authKey4' => $get_routing->credential4, 'requesturl' => $url, 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $get_routing->is_kyc_approval);
									return $processorDetails;
								}
							}
						}
						
						$url = "classes/".strtolower($active_gateways['gateway_class'])."_request.php";
						//return details need to send payment to gateway with credentials
						$processorDetails = array('providerId' => $active_gateways['id'], 'paymentMethod' => $methodData->id, 'providerName' => $active_gateways['name'], 'cardName' => $methodData->name, 'authKey1' => $active_gateways['credential1'], 'authKey2' => $active_gateways['credential2'], 'authKey3' => $active_gateways['credential3'], 'authKey4' => $active_gateways['credential4'], 'requesturl' => $url, 'message' => '', 'binrule_id' => $processorsList[0]['binid'], 'kyc_approve' => $active_gateways['is_kyc_approval']);
					}else{
						//fails the transaction if player doesn't meet the conditions
						$url = "classes/failure_request.php";
						$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '', 'binrule_id' => $processorsList[0]['binid']);
					}
					
					return $processorDetails;
				}
				else{
					//fails the transaction if player doesn't meet the conditions
					$url = "classes/failure_request.php";
					$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => "No active gateways OR Player deposits limits exceeded", 'errorcode' => '', 'binrule_id' => $processorsList[0]['binid']);
					return $processorDetails;
				}
			}
		}else{
			//fails the transaction if player doesn't meet the conditions
			$url = "classes/failure_request.php";
			$processorDetails = array('providerId' => '116', 'paymentMethod' => $methodId, 'providerName' => 'failure', 'cardName' => $methodData->name, 'authKey1' => '', 'authKey2' => '', 'authKey3' => '', 'requesturl' => $url, 'message' => 'Player may be auditor', 'errorcode' => '');
			return $processorDetails;
		}
	}
	
	
	
	public function checkLastSuccessDeposit($playerId){
		$db = connect_db();
	    if (!$db || !$playerId){
            return false;
	    }
		//Deposit counts by player id
		$SQL = "select p.payment_method_id,p.payment_provider_id,pp.is_active from payments p inner join payments_providers pp on (pp.id = p.payment_provider_id and pp.gateway_class != '') where p.status = 'OK' and p.payment_provider_id not in (100,111,112,104) and p.player_id = ? order by p.id desc limit 1";
		try {
            $res = $db->prepare($SQL);
            $res->execute(array($playerId));
            $result = $res->fetchObject();
            return $result;
	    } catch(PDOException $ex) {
            return false;
	    }	
	}
	
	
	public function checkPlayerOneMonthOld($playerId){
		$db = connect_db();
	    if (!$db || !$playerId){
            return false;
	    }
		//Deposit counts by player id
		$SQL = "select p.player_id,min(date) firstdate, datediff(NOW(), min(date)) noofdays from casino_cetrocon.payments p left join casino_cetrocon.players_adjustments pa on (pa.player_id = p.player_id and type not in (1,2)) where p.player_id = ? and p.status = 'OK' and p.payment_provider_id not in (100,104,111,112) group by p.player_id";
		try {
            $res = $db->prepare($SQL);
            $res->execute(array($playerId));
            $result = $res->fetchObject();
            return $result;
	    } catch(PDOException $ex) {
            return false;
	    }
	}
	
	
	//Encrypt card number
	public function encrypt_card($stringval, $type){
		$key = "#&$sdfdfs789fs7d";
		if($type == 'encode'){
			$encoded_decoded_string = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $stringval, MCRYPT_MODE_CBC, md5(md5($key))));
		}else if($type == 'decode'){
			$encoded_decoded_string = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($stringval), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		}
		return $encoded_decoded_string;
	}
	
}
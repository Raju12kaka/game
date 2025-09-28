<?php 

include_once 'dbInitialise.php';

function connect_db(){
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

function get_params($request_id){
	$db = connect_db();
	if (!$db || !$request_id){
		return false;
	}
		
	if (!preg_match("/^([0-9]|[a-f]){32}\-([0-9]|[a-f]){40}/i", $request_id)) {
		return false;
	}
		
	$SQL  = "SELECT p.*,pm.image ";
	$SQL .= "FROM betting.payments_requests p INNER JOIN betting.payments_methods pm ON(pm.id=p.payment_method_id) ";
	$SQL .= "WHERE p.request_id = ? ";
	$SQL .= "LIMIT 1";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id));
		return $res->fetch();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_params', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}	

}

function get_params_from_id($id){

	$db = connect_db();
	if (!$db || !$id){
		return false;
	}
		
	$SQL  = "SELECT * ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($id));
		return $res->fetch();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_params_from_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}	

}


function get_params_from_playerid($id){

	$db = connect_db();
	if (!$db || !$id){
		return false;
	}
		
	$SQL  = "SELECT * ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE player_id = ? ";
	$SQL .= "ORDER BY id desc LIMIT 1";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($id));
		return $res->fetchObject();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_params_from_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}	

}

function get_params_from_foreign_id($foreign_id){

	$db = connect_db();
	if (!$db || !$foreign_id){
		return false;
	}

	$SQL  = "SELECT * ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE foreign_id = ? ";
	$SQL .= "LIMIT 1";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($foreign_id));
		return $res->fetch();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_params_from_foreign_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function generate_payment_request_id(){
	
	return md5('SDWQ' . uniqid(true) . '1tf2') . '-' . sha1('TXZQ' . uniqid(true) . 'piq2');
}

function avilable_methods_continent($country){
	$db = connect_db();
    if (!$db || !$country){
        return false;
    }
    
    $SQL = "SELECT continent from countries where iso = ?";
    try{
    	$res = $db->prepare($SQL);
    	$res->execute(array($country));
    	$continent = $res->fetchObject();
    } catch(PDOException $ex) {
    	return false;
    }
    
    $CSQL = "SELECT continent, GROUP_CONCAT(m.method_ids ORDER BY m.order_no asc) as methods from continent_frontend_config c INNER JOIN continent_methods m ON (m.config_id = c.id) where (c.continent = ? OR c.continent = 'all') and c.is_active = 1 GROUP BY c.continent";
    try{
    	$res = $db->prepare($CSQL);
    	$res->execute(array($continent->continent));
		$result = $res->fetchAll();
		foreach($result as $rs){
			if($rs['continent'] == $continent->continent){
				$providers = $rs['methods'];
			}
		}
		if(!empty($providers)){
			return $providers;
		}else{
			return $result[0]['methods'];
		}
    } catch(PDOException $ex) {
    	return false;
    }
}

function avilable_methods_continent_new($country,$playerlevel,$webid){
	
	$db = connect_db();
    if (!$db || !$country){
        return false;
    }

    $SQL = "SELECT continent from countries where iso = ?";
    try{
        $res = $db->prepare($SQL);
        $res->execute(array($country));
        $continent = $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }

    $con = ($webid==11)?' and is_mobile=1 ':' and is_mobile=0 ';

    $CSQL=" Select a.priority_order,a.continent,a.country_id,a.defaultmethod,GROUP_CONCAT(DISTINCT(b.method_ids) order by b.order_no) as methods
from continent_frontend_config a left join continent_methods b on (a.id=b.config_id) 
INNER JOIN payments_methods_countries pc ON (pc.payment_method_id = b.method_ids AND pc.deposit_enabled = 1 AND pc.country_id = ?)  
where (a.continent=? or a.continent='all') and (a.country_id=? or a.country_id='all') and (a.player_risk_level=? or a.player_risk_level='0' ) and a.is_active=1 
 $con group by b.config_id, a.id order by a.priority_order limit 1 ";

    $result=array();
    try{
        $res = $db->prepare($CSQL);
        $res->execute(array($country, $continent->continent,$country,$playerlevel));
		
        $result = $res->fetchAll();

        if($webid==11){ // If Dont have any mobile bin rules then execute web rules.
            if(!$result){
                $CSQL=" Select a.priority_order,a.continent,a.country_id,a.defaultmethod,GROUP_CONCAT(DISTINCT(b.method_ids) order by b.order_no) as methods
	from continent_frontend_config a left join continent_methods b on (a.id=b.config_id) 
	INNER JOIN payments_methods_countries pc ON (pc.payment_method_id = b.method_ids AND pc.deposit_enabled = 1 AND pc.country_id = ?)  
	where (a.continent=? or a.continent='all') and (a.country_id=? or a.country_id='all') and (a.player_risk_level=? or a.player_risk_level='0' ) and a.is_active=1 
	  group by b.config_id, a.id order by a.priority_order limit 1 ";
                try {
                    $res = $db->prepare($CSQL);
                    $res->execute(array($country, $continent->continent, $country, $playerlevel));
                    $result = $res->fetchAll();
                }catch(PDOException $ex) {
                    return false;
                }
            }
        }



        return $result[0]['methods'].'###'.$result[0]['defaultmethod'].'###'.$result[0]['priority_order'];

    } catch(PDOException $ex) {
        return false;
    }
}




function get_deault_payment_method($webid, $country, $methods = null){
	$db = connect_db();
    if (!$db || !$webid || !$country){
            return false;
    }
	
	$SQL  = "SELECT f.* from config_frontend f ";
	$SQL .= "INNER JOIN payments_methods p ON (p.id=f.payment_method_id) ";
	$SQL .= "INNER JOIN payments_methods_countries pc ON (pc.payment_method_id = p.id) ";
    $SQL .= "WHERE f.web_id = ? AND f.is_active = 1 AND f.is_default = 1 ";
    if(!empty($methods)){
    	$SQL .= "AND f.payment_method_id IN (".$methods.")";
    }
    $SQL .= "AND pc.country_id = ? AND pc.deposit_enabled = 1";
	
    try {
            $res = $db->prepare($SQL);
            $res->execute(array($webid, $country));
            return $res->fetchObject();
    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}

function get_payment_types(){
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
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }	
}

function get_active_methods($webid, $country, $methodids = null){
	$db = connect_db();
    if (!$db || !$webid || !$country){
            return false;
    }
	
	$SQL  = "SELECT f.*,p.name, ppm.payment_provider_id from config_frontend f INNER JOIN payments_methods p ";
    $SQL .= "ON (p.id=f.payment_method_id) ";
	$SQL .= "INNER JOIN payments_methods_countries pc ";
	$SQL .= "ON (pc.payment_method_id = p.id) ";
	$SQL .= "LEFT JOIN payments_providers_methods ppm ON (ppm.payment_method_id = p.id AND p.payment_type IN (2,3,4)) ";
    $SQL .= "WHERE f.web_id = ? AND f.is_active = 1 AND pc.country_id = ? AND pc.deposit_enabled = 1";
	if(!empty($methodids)){
		$SQL .= " AND f.payment_method_id IN (".$methodids.")";
	}
	$SQL .= " ORDER BY FIELD(f.payment_method_id, ".$methodids.")";
	
    try {
            $res = $db->prepare($SQL);
            $res->execute(array($webid, $country));
            return $res->fetchAll();
    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}

function get_states_code($countrycode){
	$db = connect_db();
	if (!$db || !$countrycode){
		return false;
	}

	$SQL  = "SELECT * ";
	$SQL .= "FROM states ";
	$SQL .= "WHERE country_code = ? AND locale='en'";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($countrycode));
		
		return $res->fetchAll();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_name', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_countries_list($webid){
	$db = connect_db();
	if (!$db || !$webid){
		return false;
	}

	$SQL  = "SELECT w.country_id,c.name ";
	$SQL .= "FROM webs_countries w ";
	$SQL .= " LEFT JOIN countries c ON (c.iso = w.country_id) ";
	$SQL .= "WHERE w.web_id = ".$webid." AND w.is_active = 1 order by c.name";

	try {
		$res = $db->prepare($SQL);
		$res->execute();
		
		return $res->fetchAll();
	} catch(PDOException $ex) {
		return false;
	}
}

function get_deposit_limits($paymethodid){
	$db = connect_db();
    if (!$db || !$paymethodid){
            return false;
    }

    $SQL  = "SELECT minimum_deposit_amount, maximum_deposit_amount ";
    $SQL .= "FROM payments_methods ";
    $SQL .= "WHERE id = ?";

    try {
            $res = $db->prepare($SQL);
            $res->execute(array($paymethodid));
            return $res->fetchObject();
    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}

function get_deposit_limits_byrisklevels($paymethodid,$risklevelid){
    $db = connect_db();
    if (!$db || !$paymethodid){
        return false;
    }

    $SQL  = "SELECT minimum_deposit_amount, maximum_deposit_amount ";
    $SQL .= "FROM payments_risklevels ";
    $SQL .= "WHERE payment_method_id = ?";
    $SQL .= " and  risklevel_id = ?";
    $SQL .= " and  status = 'A' order by id desc limit 1 ";

    try { 
        $res = $db->prepare($SQL);
        $res->execute(array($paymethodid,$risklevelid));
        $result=$res->fetchObject();
        //echo " <h1>Result </h1> "; print_r(($result));
        if(($result)=='') {

            $SQL = "SELECT minimum_deposit_amount, maximum_deposit_amount ";
            $SQL .= "FROM payments_methods ";
            $SQL .= "WHERE id = ?";

            try {
                $res = $db->prepare($SQL);
                $res->execute(array($paymethodid));
                return $res->fetchObject();
            } catch (PDOException $ex) {
                send_email(TECH_EMAIL, 'get_player_all_details', "catch: " . var_export($ex->getMessage(), true));
                return false;
            }
        } else{
            return $result;
        }

    } catch(PDOException $ex) {
        send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
        return false;
    }
 }

function get_player_last_success_amount($palyerid){

    $db = connect_db();
    if (!$db || !$palyerid){
        return false;
    }

    $SQL = "SELECT amount ";
    $SQL .= "FROM payments ";
    $SQL .= "WHERE player_id = ? and status='SUCCESS' AND payment_provider_id NOT IN (100,104,111,112) order by id desc limit 1 ";
    //echo $palyerid;
    try {
        $res = $db->prepare($SQL);
        $res->execute(array($palyerid));
        return $res->fetchObject();
    } catch (PDOException $ex) {
        send_email(TECH_EMAIL, 'get_player_all_details', "catch: " . var_export($ex->getMessage(), true));
        return false;
    }

}

function insert_parent_transaction($paymentId, $res){
	$db = connect_db();
    if (!$db || !$paymentId){
        return false;
    }
	
	$SQL  = "insert into pre_deposits_process (player_id, payment_id, method_id, provider_id, ap_transaction_id, amount, status, description, created) values(".$res['player_id'].",". $paymentId.",".$res['method'].",".$res['provider'].",'".$res['transactionid']."',".($res['amount']*100).",'".$res['status']."','".$res['description']."', NOW())";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute();
	} catch(PDOException $ex) {
		echo $ex->getMessage();
        return false;
    }
}

function get_carddetails($payment_id){
        $db = connect_db();
        if (!$db || !$payment_id){
                return false;
        }


        $SQL  = "SELECT DECODE(cardnumber, 'xxfgtmnjidppbmyews@00910426#@$*') as cardnumber,DECODE(cvv_no, 'xxfgtmnjidppbmyews@00910426#@$*') as cvv_no,card_expiry_year,card_expiry_month,payment_id,name_on_card ";
        $SQL .= "FROM payments_card ";
        $SQL .= "WHERE payment_id = ? ";
        $SQL .= "LIMIT 1";

        try {
                $res = $db->prepare($SQL);
                $res->execute(array($payment_id));
                return $res->fetchObject();
        } catch(PDOException $ex) {
                send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
                return false;
        }
}

function get_billing_details($payment_id){
        $db = connect_db();
        if (!$db || !$payment_id){
                return false;
        }


        $SQL  = "SELECT * ";
        $SQL .= "FROM payments_billing_details ";
        $SQL .= "WHERE payment_id = ? ";
        $SQL .= "LIMIT 1";

        try {
                $res = $db->prepare($SQL);
                $res->execute(array($payment_id));
                return $res->fetchObject();
        } catch(PDOException $ex) {
                send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
                return false;
        }
}

function get_last_billing_details($playerId){
	$db = connect_db();
    if (!$db || !$playerId){
            return false;
    }


    $SQL  = "SELECT * ";
    $SQL .= "FROM payments_billing_details ";
    $SQL .= "WHERE player_id = ? ";
    $SQL .= " ORDER BY id desc ";
    $SQL .= "LIMIT 1";

    try {
            $res = $db->prepare($SQL);
            $res->execute(array($playerId));
            return $res->fetchObject();
    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}

function set_foreign_id($id, $foreign_id){

	$db = connect_db();
	if (!$db || !$id || !$foreign_id){
		return false;
	}

	$SQL  = "UPDATE payments_requests ";
	$SQL .= "SET foreign_id = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($foreign_id, $id));
		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_foreign_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_foreign_id($request_id){

	$db = connect_db();
	if (!$db || !$request_id){
		return false;
	}

	$SQL  = "SELECT foreign_id ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE request_id = ? ";
	$SQL .= "LIMIT 1";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id));
		return $res->fetchObject()->foreign_id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_foreign_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_payment_id($request_id){
	
	$db = connect_db();
	if (!$db || !$request_id){
		return false;
	}
	
	$SQL  = "SELECT id ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE payment_request_id = ? ";
	$SQL .= "LIMIT 1";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id));
		return $res->fetchObject()->id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_payment_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_extra_information_table($method_id){

	$db = connect_db();
	if (!$db || !$method_id){
		return false;
	}

	$SQL  = "SELECT extra_information_table ";
	$SQL .= "FROM payments_methods ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($method_id));
		return $res->fetchObject()->extra_information_table;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_extra_information_table', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_all_details($player_id){



        $db = connect_db();
        if (!$db || !$player_id){
                return false;
        }


        $SQL  = "SELECT * ";
        $SQL .= "FROM createmaster ";
        $SQL .= "WHERE mstrid = ? ";
        $SQL .= "LIMIT 1";

        try {
                $res = $db->prepare($SQL);
                $res->execute(array($player_id));
                return $res->fetchObject();
        } catch(PDOException $ex) {
                send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
                return false;
        }
}

function get_current_paymenttype($method){
	$db = connect_db();
	if (!$db || !$method){
		return false;
	}
	
	$SQL  = "SELECT payment_type ";
    $SQL .= "FROM payments_methods ";
    $SQL .= "WHERE id = ? ";
    $SQL .= "LIMIT 1";

    try {
            $res = $db->prepare($SQL);
            $res->execute(array($method));
            return $res->fetchObject();
    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}


function hasonesuccess_transaction($playerId, $methodId){
	$db = connect_db();
	if (!$db || !$playerId){
		return false;
	}

	$SQL  = "SELECT count(id) as depositcount ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE player_id = ? AND payment_method_id = ? AND payment_provider_id NOT IN (100,104,109) AND status = 'OK'";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($playerId, $methodId));
		return $res->fetchObject();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_name', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}


function getProviderDetails($providerId){
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
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}


//update payments requests table
function update_payments_request($requestid, $providerid){
	$db = connect_db();
	if (!$db || !$requestid){
		return false;
	}
		
	$SQL  = "UPDATE payments_requests set ";
	$SQL .= "payment_provider_id = ?";
	$SQL .= " WHERE id = ? ";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($providerid, $requestid));
		
		if ($res->fetchObject()->status == 'OK'){
			return false;
		} else {
			return true;
		}

	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'is_cancelled', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}


function get_player_name($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT realname ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		
		$name = $res->fetchObject()->realname;
		$name = utf8_encode($name);
		
		return $name;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_name', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_name($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET realname = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_name', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_surname($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT reallastname ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		
		$name = $res->fetchObject()->reallastname;
		$name = utf8_encode($name);
		
		return $name;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_surname', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_surname($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET reallastname = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_surname', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_country($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT country ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->country;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_country', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_country($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET country = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_country', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_email($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT email ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->email;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_email', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_email($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET email = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_email', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_phone($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT contact_phone ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->contact_phone;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_phone', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_phone($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET contact_phone = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_phone', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_address($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT address ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->address;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_address', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_address($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET address = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_address', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_city($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT city ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->city;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_city', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_city($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET city = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_city', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_zipcode($player_id, $data){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT zipcode ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->zipcode;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_zipcode', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_zipcode($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET zipcode = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_zipcode', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_player_birthdate($player_id){

	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT birthdate ";
	$SQL .= "FROM players ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject()->birthdate;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_birthdate', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_player_birthdate($player_id, $data){

	$db = connect_db();
	if (!$db || !$data){
		return false;
	}

	$SQL  = "UPDATE players ";
	$SQL .= "SET birthdate = ? ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($data, $player_id));

		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_birthdate', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_request($id){

	$db = connect_db();
	if (!$db || !$id){
		return false;
	}

	$SQL  = "UPDATE payments_requests ";
	$SQL .= "SET request_finished_at = NOW(), step = 2 ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "AND step = 1 ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($id));
		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_request', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function set_response($id){

	$db = connect_db();
	if (!$db || !$id){
		return false;
	}

	$SQL  = "UPDATE payments_requests ";
	$SQL .= "SET response_finished_at = NOW(), step = 3 ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "AND step = 2 ";
	$SQL .= "LIMIT 1";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($id));
		return true;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_response', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	return false;
}

function insert_payment($result, $params){
    
    $db = connect_db();
    if (!$db || !$result || !$params){
        return false;
    }
    
    $params['status'] = 'KO';
    if ($result['status'] == 'Success'){
        $params['status'] = 'SUCCESS';
    } elseif ($result['status'] == 'Pending'){
        $params['status'] = 'PENDING';
    } elseif ($result['status'] == 'Initiated'){
        $params['status'] = 'INITIATED';
    }
    
	
    if (!isset($params['foreign_id']) || !$params['foreign_id']){
        $params['foreign_id'] = 0;
    }
    
    if (!isset($result['foreign_errorcode']) || !$result['foreign_errorcode']){
        $result['foreign_errorcode'] = 0;
    }
    
    if ($params['binrule_id']==""){
        
        $params['binrule_id'] = NULL;
    }
    
    // $params['amount'] = $params['amount']*100;
	$params['amount'] = $params['amount'];
    
    if (!$params['player_currency_amount']){
        $params['player_currency_amount'] = $params['amount'];
    }
	$params['player_currency_amount'] = $params['player_currency_amount'];
    
    if (!$params['player_currency_id']){
        $params['player_currency_id'] = $params['currency_id'];
    }
   
    if ($params['amount'] && $params['status'] && $params['currency_id'] && $params['ip'] && $params['player_id'] && $params['payment_method_id'] && $params['payment_provider_id'] && $params['request_id'] && $params['web_id'] && $params['country_id']){
        
        $SQL  = "INSERT INTO payments(amount, description, date, updated, status, currency_id, errorcode, foreign_errorcode, ip, player_id, payment_method_id, payment_provider_id, payment_request_id, web_id, country_id, payment_foreign_id, player_currency_amount, player_currency_id, user_agent, binrule_id) ";
        $SQL .= "VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
        // return $SQL; die;
        try {
            $res = $db->prepare($SQL);
			
            $res->execute(array($params['amount'], $result['html'], $params['status'], $params['currency_id'], $result['errorcode'], $result['foreign_errorcode'], $params['ip'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['request_id'], $params['web_id'], $params['country_id'], $params['foreign_id'], $params['player_currency_amount'], $params['player_currency_id'], $params['useragent'], $params['binrule_id']));
        }catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'insert_payment', "catch 1: ".var_export($ex->getMessage(),true));
            return false;
        }
        
        $SQL  = "SELECT id ";
        $SQL .= "FROM payments ";
        $SQL .= "WHERE player_id = ? ";
        $SQL .= "AND payment_method_id = ? ";
        $SQL .= "AND payment_provider_id = ? ";
        $SQL .= "AND payment_request_id = ? ";
        $SQL .= "AND web_id = ? ";
        $SQL .= "AND ip = ? ";
        $SQL .= "AND status = ? ";
        $SQL .= "AND amount = ? ";
        $SQL .= "AND currency_id = ? ";
        $SQL .= "AND country_id = ? ";
        $SQL .= "AND payment_foreign_id = ? ";
        $SQL .= "AND errorcode = ? ";
        $SQL .= "AND foreign_errorcode = ? ";
        $SQL .= "ORDER BY id DESC ";
        $SQL .= "LIMIT 1";
        
        try {
            $res = $db->prepare($SQL);
            $res->execute(array($params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['request_id'], $params['web_id'], $params['ip'], $params['status'], $params['amount'], $params['currency_id'], $params['country_id'], $params['foreign_id'], $result['errorcode'], $result['foreign_errorcode']));
            $PAYMENT_ID = $res->fetchObject()->id;
        } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'insert_payment', "catch 2: ".var_export($ex->getMessage(),true));
            return false;
        }
        // return $PAYMENT_ID;

        insert_billing_details($PAYMENT_ID, $params['player_id'],'','', $params['billingaddress'], $params['billingcity'], $params['billingstate'], $params['billingzip'], $params['billingcountry'],'','');
        
        switch(get_extra_information_table($params['payment_method_id'])){
            
            case 'payments_card':
                
                if ($params['cardnumber']){
                    
                    $SQL  = "INSERT INTO payments_card(cardnumber, cvv_no, card_expiry_year, card_expiry_month, payment_id, name_on_card) ";
                    $SQL .= "VALUES (ENCODE(".$params['cardnumber'].", 'xxfgtmnjidppbmyews@00910426#@$*'), ENCODE(".$params['cvv'].", 'xxfgtmnjidppbmyews@00910426#@$*'), ?, ?, ?, ?) ";
                    
                    try {
                        $res = $db->prepare($SQL);
                        $res->execute(array($params['expiryyear'], $params['expirymonth'], $PAYMENT_ID, $params['cardname']));
                    } catch(PDOException $ex) {
                        send_email(TECH_EMAIL, 'insert_payment', "catch 3: ".var_export($ex->getMessage(),true));
                        return false;
                    }
                    
                    if (isset($params['token_scss']) && $params['token_scss'] && $params['status'] == 'OK'){
                        $SQL  = "SELECT COUNT(*) AS total ";
                        $SQL .= "FROM payments_card_players ";
                        $SQL .= "WHERE cardnumber = ? ";
                        $SQL .= "AND player_id = ? ";
                        $SQL .= "AND payment_method_id = ? ";
                        $SQL .= "AND payment_provider_id = ? ";
                        
                        try {
                            $res = $db->prepare($SQL);
                            $res->execute(array($params['cardnumber'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id']));
                            $exists = $res->fetchObject()->total;
                        } catch(PDOException $ex) {
                            send_email(TECH_EMAIL, 'insert_payment', "catch 4: ".var_export($ex->getMessage(),true));
                            return false;
                        }
                        
                        if ($exists){
                            $SQL  = "UPDATE payments_card_players ";
                            $SQL .= "SET token_scss = ?, payment_id = ?, updated = NOW(), status = 1 ";
                            $SQL .= "WHERE cardnumber = ? ";
                            $SQL .= "AND player_id = ? ";
                            $SQL .= "AND payment_method_id = ? ";
                            $SQL .= "AND payment_provider_id = ? ";
                            
                            try {
                                $res = $db->prepare($SQL);
                                $res->execute(array($params['token_scss'], $PAYMENT_ID, $params['cardnumber'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id']));
                            } catch(PDOException $ex) {
                                send_email(TECH_EMAIL, 'insert_payment', "catch 5: ".var_export($ex->getMessage(),true));
                                return false;
                            }
                        } else {
                            
                            $SQL  = "INSERT INTO payments_card_players(player_id, payment_method_id, payment_provider_id, cardnumber, cvv, token_scss, payment_id, date, updated, status) ";
                            $SQL .= "VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1) ";
                            
                            try {
                                $res = $db->prepare($SQL);
                                $res->execute(array($params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['cardnumber'], $params['cvv'], $params['token_scss'], $PAYMENT_ID));
                            } catch(PDOException $ex) {
                                send_email(TECH_EMAIL, 'insert_payment', "catch 6: ".var_export($ex->getMessage(),true));
                                return false;
                            }
                        }
                    }
                } else {
                	return $PAYMENT_ID;
                    return false;
                }
                break;
                
            case 'payments_voucher':
                if ($params['changevouchenumber']){
                    $SQL  = "INSERT INTO payments_voucher(vouchernumber, changevouchenumber, changevouchercurrency, changevoucheramount, changevoucherexpirydate, payment_id) ";
                    $SQL .= "VALUES (?, ?, ?, ?, ?, ?) ";
                    
                    try {
                        $res = $db->prepare($SQL);
                        $res->execute(array($params['cardnumber'], $params['changevouchenumber'], $params['changevouchercurrency'], $params['changevoucheramount'], $params['changevoucherexpirydate'], $PAYMENT_ID));
                    } catch(PDOException $ex) {
                        send_email(TECH_EMAIL, 'insert_payment', "catch 4: ".var_export($ex->getMessage(),true));
                        return false;
                    }
                } else {
                    $SQL  = "INSERT INTO payments_voucher(vouchernumber, payment_id) ";
                    $SQL .= "VALUES (?, ?) ";
                    
                    try {
                        $res = $db->prepare($SQL);
                        $res->execute(array($params['cardnumber'], $PAYMENT_ID));
                    } catch(PDOException $ex) {
                        send_email(TECH_EMAIL, 'insert_payment', "catch 5: ".var_export($ex->getMessage(),true));
                        return false;
                    }
                }
                break;
                
            case 'payments_multi_card':
                if ($params['cardnumbers']){
                    
                    $SQL  = "INSERT INTO payments_multi_card(cardnumber, partial_amount, payment_id) ";
                    $SQL .= "VALUES (?, ?, ?) ";
                    
                    try {
                        foreach($params['cardnumbers'] as $key=>$row){
                            $res = $db->prepare($SQL);
                            $res->execute(array($key, $row, $PAYMENT_ID));
                        }
                    } catch(PDOException $ex) {
                        send_email(TECH_EMAIL, 'insert_payment', "catch 6: ".var_export($ex->getMessage(),true));
                        return false;
                    }
                } else {
                    return false;
                }
                break;
                
            case 'payments_accounts':
                if ($params['consumer_account'] && $params['business_account']){
                    $SQL  = "INSERT INTO payments_accounts(consumer_account, business_account, payment_id) ";
                    $SQL .= "VALUES (?, ?, ?) ";
                    
                    try {
                        $res = $db->prepare($SQL);
                        $res->execute(array($params['consumer_account'], $params['business_account'], $PAYMENT_ID));
                    } catch(PDOException $ex) {
                        send_email(TECH_EMAIL, 'insert_payment', "catch 7: ".var_export($ex->getMessage(),true));
                        return false;
                    }
                } else {
                    send_email(TECH_EMAIL, 'insert_payment', "missing payment account params");
                    return false;
                }
                break;
                
        }
        
        if ($params['status']=='SUCCESS'){
            $SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at, charged_at) ";
            $SQL .= "VALUES (?, 1, NOW(), NOW()) ";
            
            try {
                $res = $db->prepare($SQL);
                $res->execute(array($PAYMENT_ID));
            } catch(PDOException $ex) {
                send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
                return false;
            }
            
            if(!empty($params['cardnumber'])){
                //check player has card no in kyc table
                $CHECK  = "SELECT id from player_kyc ";
                $CHECK .= "WHERE player_id = ? AND card_no = ENCODE(".$params['cardnumber'].", 'xxfgtmnjidppbmyews@00910426#@$*')";
                try {
                    $res = $db->prepare($CHECK);
                    $res->execute(array($params['player_id']));
                    $result = $res->fetchObject();
                } catch(PDOException $ex) {
                    return false;
                }
                if(!$result){
                    //insert into players kyc table
                    $SQL  = "INSERT INTO player_kyc(player_id, card_no, created) ";
                    $SQL .= "VALUES (?, ENCODE(".$params['cardnumber'].", 'xxfgtmnjidppbmyews@00910426#@$*'), NOW())";
                    try {
                        $res = $db->prepare($SQL);
                        $res->execute(array($params['player_id']));
                    } catch(PDOException $ex) {
                        return false;
                    }
                }
            }
            
            //Update transaction counts
            update_transaction_count($params['player_id'], $params['payment_provider_id'], $params['web_id'], $params['cardnumber']);
            /////// update player balance with deposit amount - written by Yuvaram on 27-02-2019 /////////
            if ($PAYMENT_ID){
                $SQL  = "UPDATE createmaster ";
                $SQL .= "SET balance = balance + ?  ";
                $SQL .= "WHERE mstrid = ? ";
                
                try {
                    $res1 = $db->prepare($SQL);
                    $res1->execute(array($params['amount'], $params['player_id']));
                } catch(PDOException $ex) {
                    return false;
                }
                //////// insert into player_transaction table /////////
                $currentPlayerSQL = "SELECT balance FROM createmaster WHERE mstrid = ? ";
                $currPlayerRes = $db->prepare($currentPlayerSQL);
                $currPlayerRes->execute(array($params['player_id']));
                $currentPlayerBalance = $currPlayerRes->fetchObject()->balance;
                
                $SQL  = "INSERT INTO player_transactions(player_id, transaction_name, transaction_date, transaction_type, transaction_id, amount, total_amount, notes, site_id, web_id, currency, game_status, created, updated) ";
                $SQL .= "VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                try {
                    $res2 = $db->prepare($SQL);
                    $res2->execute(array($params['player_id'], "Deposit", "Deposit", $PAYMENT_ID, $params['amount'], $currentPlayerBalance,  "PayPunto", $params['site_id'], $params['web_id'], $params['player_currency_id'], '2'));
                } catch(PDOException $ex) {
                    return false;
                }
            }
            ///////////////ends here////////////////////
        }
        
        /* Calling Status updation function */
        updateVipStatus($params['player_id']);
        
        
        return $PAYMENT_ID;
    }
    send_email(TECH_EMAIL, 'insert_payment', "missing params");
    return false;
}

/*
function insert_payment($result, $params){
		
	$db = connect_db();
	if (!$db || !$result || !$params){
		return false;
	}
		
	$params['status'] = 'KO';
	if ($result['status'] == 'Success'){
		$params['status'] = 'OK';
		$params['reward_points'] = $params['reward_points'];
	} elseif ($result['status'] == 'Pending'){
		$params['status'] = 'PENDING';
		$params['reward_points'] = '0';
	} elseif ($result['status'] == 'Initiated'){
		$params['status'] = 'INITIATED';
		$params['reward_points'] = '0';
 	}
	
	if (!isset($params['foreign_id']) || !$params['foreign_id']){
		$params['foreign_id'] = 0;
	}
	
	if (!isset($result['foreign_errorcode']) || !$result['foreign_errorcode']){
		$result['foreign_errorcode'] = 0;
	}
	
	if ($params['binrule_id']==""){
		
		$params['binrule_id'] = NULL;
	}
	
	$params['amount'] = $params['amount']*100;
	
	if (!$params['player_currency_amount']){
		$params['player_currency_amount'] = $params['amount'];
	}
	
	if (!$params['player_currency_id']){
		$params['player_currency_id'] = $params['currency_id'];
	}
		
	if ($params['amount'] && $params['status'] && $params['currency_id'] && $params['ip'] && $params['player_id'] && $params['payment_method_id'] && $params['payment_provider_id'] && $params['request_id'] && $params['web_id'] && $params['country_id']){
		
		$SQL  = "INSERT INTO payments(amount, description, date, updated, status, currency_id, errorcode, foreign_errorcode, ip, player_id, payment_method_id, payment_provider_id, payment_request_id, web_id, country_id, payment_foreign_id, player_currency_amount, player_currency_id, user_agent, binrule_id,reward_points) ";
		$SQL .= "VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";		
		
		try {
			$res = $db->prepare($SQL);
			$res->execute(array($params['amount'], $result['html'], $params['status'], $params['currency_id'], $result['errorcode'], $result['foreign_errorcode'], $params['ip'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['request_id'], $params['web_id'], $params['country_id'], $params['foreign_id'], $params['player_currency_amount'], $params['player_currency_id'], $params['useragent'], $params['binrule_id'],$params['reward_points']));
		} catch(PDOException $ex) {
		    send_email(TECH_EMAIL, 'insert_payment', "catch 1: ".var_export($ex->getMessage(),true));
			return false;
		}
								
		$SQL  = "SELECT id ";
		$SQL .= "FROM payments ";
		$SQL .= "WHERE player_id = ? ";
		$SQL .= "AND payment_method_id = ? ";
		$SQL .= "AND payment_provider_id = ? ";
		$SQL .= "AND payment_request_id = ? ";
		$SQL .= "AND web_id = ? ";
		$SQL .= "AND ip = ? ";
		$SQL .= "AND status = ? ";
		$SQL .= "AND amount = ? ";
		$SQL .= "AND currency_id = ? ";
		$SQL .= "AND country_id = ? ";
		$SQL .= "AND payment_foreign_id = ? ";
		$SQL .= "AND errorcode = ? ";
		$SQL .= "AND foreign_errorcode = ? ";
		$SQL .= "ORDER BY id DESC ";
		$SQL .= "LIMIT 1";
				
		try {
			$res = $db->prepare($SQL);
			$res->execute(array($params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['request_id'], $params['web_id'], $params['ip'], $params['status'], $params['amount'], $params['currency_id'], $params['country_id'], $params['foreign_id'], $result['errorcode'], $result['foreign_errorcode']));
			$PAYMENT_ID = $res->fetchObject()->id;
		} catch(PDOException $ex) {
		    send_email(TECH_EMAIL, 'insert_payment', "catch 2: ".var_export($ex->getMessage(),true));
			return false;
		}
		
		//insert_billing_details($PAYMENT_ID, $params['player_id'], $params['billingaddress'], $params['billingcity'], $params['billingstate'], $params['billingzip'], $params['billingcountry']);
		insert_billing_details($PAYMENT_ID, $params['player_id'], $params['billingname'], $params['billinglastname'], $params['billingaddress'], $params['billingcity'], $params['billingstate'], $params['billingzip'],$params['billingcountry'], $params['ssnnumber'], $params['billingphone']);
			
		switch(get_extra_information_table($params['payment_method_id'])){
			
			case 'payments_card':

				if ($params['cardnumber']){

					$SQL  = "INSERT INTO payments_card(cardnumber, cvv_no, card_expiry_year, card_expiry_month, payment_id, name_on_card) ";
					$SQL .= "VALUES (ENCODE(".$params['cardnumber'].", 'xxfgtmnjidppbmyews@00910426#@$*'), ENCODE(".$params['cvv'].", 'xxfgtmnjidppbmyews@00910426#@$*'), ?, ?, ?, ?) ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($params['expiryyear'], $params['expirymonth'], $PAYMENT_ID, $params['cardname']));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'insert_payment', "catch 3: ".var_export($ex->getMessage(),true));
						return false;
					}
					
					if (isset($params['token_scss']) && $params['token_scss'] && $params['status'] == 'OK'){
						$SQL  = "SELECT COUNT(*) AS total ";
						$SQL .= "FROM payments_card_players ";
						$SQL .= "WHERE cardnumber = ? ";
						$SQL .= "AND player_id = ? ";
						$SQL .= "AND payment_method_id = ? ";
						$SQL .= "AND payment_provider_id = ? ";
						
						try {
							$res = $db->prepare($SQL);
							$res->execute(array($params['cardnumber'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id']));
							$exists = $res->fetchObject()->total;
						} catch(PDOException $ex) {
							send_email(TECH_EMAIL, 'insert_payment', "catch 4: ".var_export($ex->getMessage(),true));
							return false;
						}
						
						if ($exists){
							$SQL  = "UPDATE payments_card_players ";
							$SQL .= "SET token_scss = ?, payment_id = ?, updated = NOW(), status = 1 ";
							$SQL .= "WHERE cardnumber = ? ";
							$SQL .= "AND player_id = ? ";
							$SQL .= "AND payment_method_id = ? ";
							$SQL .= "AND payment_provider_id = ? ";
							
							try {
								$res = $db->prepare($SQL);
								$res->execute(array($params['token_scss'], $PAYMENT_ID, $params['cardnumber'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id']));
							} catch(PDOException $ex) {
								send_email(TECH_EMAIL, 'insert_payment', "catch 5: ".var_export($ex->getMessage(),true));
								return false;
							}
						} else {
							
							$SQL  = "INSERT INTO payments_card_players(player_id, payment_method_id, payment_provider_id, cardnumber, cvv, token_scss, payment_id, date, updated, status) ";
							$SQL .= "VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1) ";
							
							try {
								$res = $db->prepare($SQL);
								$res->execute(array($params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['cardnumber'], $params['cvv'], $params['token_scss'], $PAYMENT_ID));
							} catch(PDOException $ex) {
								send_email(TECH_EMAIL, 'insert_payment', "catch 6: ".var_export($ex->getMessage(),true));
								return false;
							}
						}
					}
				} else {
					return false;
				}
			break;
		
			case 'payments_voucher':
				if ($params['changevouchenumber']){
					$SQL  = "INSERT INTO payments_voucher(vouchernumber, changevouchenumber, changevouchercurrency, changevoucheramount, changevoucherexpirydate, payment_id) ";
					$SQL .= "VALUES (?, ?, ?, ?, ?, ?) ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($params['cardnumber'], $params['changevouchenumber'], $params['changevouchercurrency'], $params['changevoucheramount'], $params['changevoucherexpirydate'], $PAYMENT_ID));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'insert_payment', "catch 4: ".var_export($ex->getMessage(),true));
						return false;
					}
				} else {
					$SQL  = "INSERT INTO payments_voucher(vouchernumber, payment_id) ";
					$SQL .= "VALUES (?, ?) ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($params['cardnumber'], $PAYMENT_ID));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'insert_payment', "catch 5: ".var_export($ex->getMessage(),true));
						return false;
					}
				}
			break;
		
			case 'payments_multi_card':
				if ($params['cardnumbers']){
						
					$SQL  = "INSERT INTO payments_multi_card(cardnumber, partial_amount, payment_id) ";
					$SQL .= "VALUES (?, ?, ?) ";
					
					try {
						foreach($params['cardnumbers'] as $key=>$row){
							$res = $db->prepare($SQL);
							$res->execute(array($key, $row, $PAYMENT_ID));
						}
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'insert_payment', "catch 6: ".var_export($ex->getMessage(),true));
						return false;
					}
				} else {
					return false;
				}
			break;
			
			case 'payments_accounts':
				if ($params['consumer_account'] && $params['business_account']){
					$SQL  = "INSERT INTO payments_accounts(consumer_account, business_account, payment_id) ";
					$SQL .= "VALUES (?, ?, ?) ";
						
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($params['consumer_account'], $params['business_account'], $PAYMENT_ID));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'insert_payment', "catch 7: ".var_export($ex->getMessage(),true));
						return false;
					}
				} else {
					send_email(TECH_EMAIL, 'insert_payment', "missing payment account params");
					return false;
				}
			break;
			
		}

        if(!empty($params['cardnumber'])){
				//check player has card no in kyc table
				$CHECK  = "SELECT id from player_kyc ";
				$CHECK .= "WHERE player_id = ? AND card_no = ENCODE(".$params['cardnumber'].", 'xxfgtmnjidppbmyews@00910426#@$*')";
				try {
					$res = $db->prepare($CHECK);
					$res->execute(array($params['player_id']));
					$result = $res->fetchObject();
				} catch(PDOException $ex) {
					return false;
				}
				if(!$result){
					//insert into players kyc table
					$SQL  = "INSERT INTO player_kyc(player_id, card_no, created) ";
					$SQL .= "VALUES (?, ENCODE(".$params['cardnumber'].", 'xxfgtmnjidppbmyews@00910426#@$*'), NOW())";
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($params['player_id']));
					} catch(PDOException $ex) {
						return false;
					}
				}
			}
			
		if ($params['status']=='OK'){
			$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at, charged_at) ";
			$SQL .= "VALUES (?, 1, NOW(), NOW()) ";

			try {
				$res = $db->prepare($SQL);
				$res->execute(array($PAYMENT_ID));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
				return false;
			}

			
			
			$pay_method_name = get_current_paymentmethodname($params['payment_method_id']);
			$payment_method_name = $pay_method_name->name; 
			
			//Update transaction counts
			update_transaction_count($params['player_id'], $params['payment_provider_id'], $params['web_id'], $params['cardnumber']);
			/////// update player balance with deposit amount - written by Yuvaram on 27-02-2019 /////////
			if ($PAYMENT_ID){
				$SQL  = "UPDATE players ";
				//$SQL .= "SET balance = balance + ?  ";
				$SQL .= "SET balance = balance + ? , reward_points = reward_points + ?  ";
				if(in_array($params['payment_provider_id'], array(111,112))){
					$SQL .= ", casino_bonus_balance = casino_bonus_balance + ".$params['player_currency_amount']." ";
				}
				$SQL .= "WHERE id = ? ";
				
				try {
					$res1 = $db->prepare($SQL);
					//$res1->execute(array($params['player_currency_amount'], $params['player_id'])); 
					$res1->execute(array($params['player_currency_amount'],$params['reward_points'],$params['player_id']));
				} catch(PDOException $ex) {
					return false;
				}
				//	Insert into Reward Points table
				$currentPlayerSQL = "SELECT reward_points,balance FROM players WHERE id = ? ";
				$currPlayerRes = $db->prepare($currentPlayerSQL);
				$currPlayerRes->execute(array($params['player_id']));
				$current_reward_points 	=	$currPlayerRes->fetchObject()->reward_points;
				$data 					=	array();
				$data['deposit_amount']	=	$params['player_currency_amount'];
				$data['deposit_status']	=	"Successfull Deposit";

					
				$SQL  = "INSERT INTO reward_points_history(player_id, reward_points, txn_type,
					 total_reward_points, created_on,details,payment_id) ";
				$SQL .= "VALUES (?, ?, ?, ?, NOW(),?,?)";
				try {
					$res2 = $db->prepare($SQL);
					$res2->execute(array($params['player_id'], $params['reward_points'],
						"credit", $current_reward_points,json_encode($data),$PAYMENT_ID));
				} catch(PDOException $ex) {
						return false;
				}
				
				
				//////// insert into player_transaction table /////////
				$currentPlayerSQL = "SELECT balance FROM players WHERE id = ? ";
				$currPlayerRes = $db->prepare($currentPlayerSQL);
				$currPlayerRes->execute(array($params['player_id']));
				$currentPlayerBalance = $currPlayerRes->fetchObject()->balance;
					
				$SQL  = "INSERT INTO player_transactions(player_id, transaction_name, transaction_date, transaction_type, transaction_id, amount, total_amount, notes, site_id, web_id, currency, game_status, created, updated) ";
				$SQL .= "VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
				try {
					$res2 = $db->prepare($SQL);
					$res2->execute(array($params['player_id'], "Deposit", "Deposit", $PAYMENT_ID, $params['player_currency_amount'], $currentPlayerBalance,  $payment_method_name, $params['site_id'], $params['web_id'], $params['player_currency_id'], '2'));
				} catch(PDOException $ex) {
						return false;
				}
				
				
				if($params['payment_method_id']=='100'){
					
				$paymentslist = "SELECT count(*) as cnt FROM payments WHERE status = ? and payment_method_id = ? and payment_provider_id = ? and player_id= ?";
				$paymentslistres = $db->prepare($paymentslist);
				$paymentslistres->execute(array('OK',$params['payment_method_id'],'190',$params['player_id']));
				$cnt_visa_payments = $paymentslistres->fetchObject()->cnt;
				
					if($cnt_visa_payments >=2){
						$updateriskquery="update players set players_classes_id= ? where id= ?";
						$riskquery = $db->prepare($updateriskquery);
					    $riskquery->execute(array('21',$params['player_id']));
					}
				} 		
			}
			///////////////ends here////////////////////
		}

        //  Calling Status updation function 
        updateVipStatus($params['player_id']);
		
		$playerclass = "SELECT players_classes_id FROM players WHERE id = ? ";
		$playerclassres = $db->prepare($playerclass);
		$playerclassres->execute(array($params['player_id']));
		$playerclassid = $playerclassres->fetchObject();
		if($playerclassid->players_classes_id == 8 && $params['status']=='OK'){
		updatePlayersRiskLevel($params['player_id']);
		}
		
		return $PAYMENT_ID;
	}
	send_email(TECH_EMAIL, 'insert_payment', "missing params");
	return false;
} */

/* Update Player vip status function
  @player_id  -- input paramer
*/
function updateVipStatus($payer_id){
    $db = connect_db();
    if (!$db || !$payer_id){
        return false;
    }
    /*
    Below script has been commented as we are updating the Player's VIP Class based on Reward Points

    $exists = 0;
    $SQL1 = " SELECT COUNT(*) AS total ";
    $SQL1 .= " FROM payments ";
    $SQL1 .= " WHERE player_id = ? ";
    $SQL1 .= " AND status = 'OK' ";
    //echo $SQL1;
    try {
        $res = $db->prepare($SQL1);
        $res->execute(array($payer_id));
        $exists = $res->fetchObject()->total;
    } catch (PDOException $ex) {
        send_email(TECH_EMAIL, 'getting_payments_count', "catch 1: " . var_export($ex->getMessage(), true));
        return false;
    }
    if ($exists == 1) {
        $SQL2 = " UPDATE players SET players_vip_classes_id = 1 WHERE id = ? ";
        $res = $db->prepare($SQL2);
        $res->execute(array($payer_id));
    }
    if ($exists == 2) {
        $SQL2 = " UPDATE players SET players_vip_classes_id = 6 WHERE id = ? ";
        $res = $db->prepare($SQL2);
        $res->execute(array($payer_id));
    }
    //echo " <br/>  Before Result - ";print_r($res);
    */
    
    $players_vip_classes_id	 =	"";
    $SQL1					 =	" SELECT reward_points,players_vip_classes_id,currency ";
    $SQL1 					.=	" FROM players ";
    $SQL1 					.=	" WHERE id = ? ";

    try
    {
        $res    =   $db->prepare($SQL1);
        $res->execute(array($payer_id));
        $result =   $res->fetch(PDO::FETCH_ASSOC);
        
        $RewardPoints		 =	$result['reward_points'];           #$res->fetchObject()->reward_points;
        $currency		 =	$result['currency'];                #$res->fetchObject()->currency;
    $players_vip_classes_id	 =	$result['players_vip_classes_id'];  #$res->fetchObject()->players_vip_classes_id;
        #echo '<pre>';   print_r($result);var_dump($currency);exit(' Exit at '. __LINE__. ' in page : '. __FILE__);
    }
    catch (PDOException $ex)
    {
        send_email(TECH_EMAIL, 'getting_payments_count', "catch 1: " . var_export($ex->getMessage(), true));
        return false;
    }
    $SQL2					 =	" SELECT vip_class_id ";
    $SQL2 					.=	" FROM players_vip_classes_limits	";
    $SQL2 					.=	" WHERE lower_limit <=	?	";
    $SQL2 					.=	" and upper_limit >=	?	";
    $SQL2 					.=	" and status =	1	";
    $SQL2 					.=	" and currency_id =	?	";

    $res					 =	$db->prepare($SQL2);
    $res->execute(array($RewardPoints,$RewardPoints,$currency));
    $vip_class_id                               =	$res->fetchObject()->vip_class_id;
    if(!$vip_class_id)
    {
        $vip_class_id                           =	1;
    }


    $SQL3					 =	" UPDATE players SET players_vip_classes_id = ? WHERE id = ? ";
    $res 					 =	$db->prepare($SQL3);
    $res->execute(array($vip_class_id,$payer_id));
    return $res;
}

/* Status Updation End here.*/

function insert_bitcoin_status($player_id, $payment_id){
	$db = connect_db();
	if (!$db || !$player_id || !$payment_id){
		return false;
	}
	
	$status = "initiated";
	$SQL  = "INSERT INTO bitcoin_status(payment_id, player_id, status, created) ";
	$SQL .= "VALUES (?, ?, ?, NOW())";		
	
	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
	array_pop($documentroot);
	array_push($documentroot, 'logs');
	$root_path = implode('/', $documentroot);
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($payment_id, $player_id, $status));
		
		$logmessage = "Payment through bitcoin initiated for player: ".$params['player_id']." with payment id: ".$payment_id."\n";
		file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	} catch(PDOException $ex) {
		$logmessage = "Error while insert into bitcoin status : ".$ex->getMessage()."\n";
		file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		return false;
	}
}

function check_bitcoin_payment_status($paymentid, $playerid){
	$db = connect_db();
	if (!$db || !$paymentid || !$playerid){
		return false;
	}

	$SQL  = "SELECT * FROM bitcoin_status";
	$SQL .= " WHERE payment_id = ? AND player_id = ?";
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($paymentid, $playerid));
		
		return $res->fetchObject()->status;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_birthdate', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function insert_billing_details($paymentid, $playerid, $firstname, $lastname, $address, $city, $state, $zipcode,$country, $ssnnumber, $phoneno){
	$db = connect_db();
	if (!$db || !$paymentid){
		return false;
	}
	
	// $SQL = "INSERT INTO payments_billing_details (payment_id, player_id, billing_address, billing_city, billing_state, billing_zipcode, billing_country)";
	// $SQL .= " VALUES(?, ?, ?, ?, ?, ?, ?)";
	$SQL = "INSERT INTO payments_billing_details (payment_id, player_id, billing_first_name, billing_last_name, billing_address, billing_city, billing_state, billing_zipcode, billing_country, billing_phone, ssn_number)";
	$SQL .= " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	
	try {
		$res = $db->prepare($SQL);
		//$res->execute(array($paymentid, $playerid, $address, $city, $state, $zipcode, $country));
		$res->execute(array($paymentid, $playerid, $firstname, $lastname, $address, $city, $state, $zipcode,$country, $phoneno, $ssnnumber));
		
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'insert_notify_data', "catch 3: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function update_payments_status($paymentid, $playerid, $paymentstatus, $message = null, $extId = null, $paymentcharge = null, $amount = null, $totalamount = null, $providername = null){
	$db = connect_db();
	if (!$db || !$paymentid || !$playerid){
		return false;
	}
	
	$status = 'DECLINED';
	$description = !empty($message) ? $message : 'Declined';
	if ($paymentstatus=='SUCCESS'){
			$status = 'SUCCESS';
			$description = $message;
	} elseif ($paymentstatus=='PENDING'){
			$description = !empty($message) ? $message : 'Pending';
			$status = 'PENDING';
	}elseif ($paymentstatus=='Authorized'){
			$description = !empty($message) ? $message : 'Authorized';
			$status = 'AUTHORIZED';
	}elseif ($paymentstatus=='INITIATED'){
		$description = !empty($message) ? $message : 'INITIATED';
		$status = 'INITIATED';
	}
	
	/*Icubes affiliates postback response*/
		$SQLL  = "SELECT * ";
        $SQLL .= "FROM createmaster ";
        $SQLL .= "WHERE mstrid = ? ";
        $SQLL .= "LIMIT 1";

        try {
                $res = $db->prepare($SQLL);
                $res->execute(array($playerid));
                $playerdetails =  $res->fetchObject();
        } catch(PDOException $ex) {
                send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
                return false;
        }
		$reference_number = 'AKD'.'123'.$paymentid.'789';
		$postback_response =NULL;
		if ($status=='SUCCESS' && $playerdetails->affiliate_id == 21){
			//$posturl = "https://tracking.icubeswire.co/aff_a?offer_id=2458&goal_id=3574&transaction_id=".$reference_number;
			//$postback_response = file_get_contents($posturl);
		}
		
		
    /*End Icubes affiliates postback response*/
	
	$SQL  = "UPDATE payments SET description = ?, updated = NOW(), status = ?, payment_foreign_id = ?, reference_number = ?, response_postback = ? ";
	$SQL .= "WHERE id = ? AND player_id = ?";
	
	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
	array_pop($documentroot);
	array_push($documentroot, 'logs');
	$root_path = implode('/', $documentroot);
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($description, $status, $extId, $reference_number, $postback_response, $paymentid, $playerid));
		$logmessage = date('Y-m-d H:i:s')." Payment status updated as :".$status." for player: ".$playerid." with payment id: ".$paymentid."\n";
		file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	} catch(PDOException $ex) {
		$logmessage = date('Y-m-d H:i:s')." Error while updating Payment status as :".$status." for payment ID:".$paymentid." Error message: ".$ex->getMessage()."\n";
		file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	if ($status=='SUCCESS'){
		$charge = (!empty($paymentcharge) && $paymentcharge == 1) ? 1 : 0;
		
		$CSQL = "SELECT * FROM payments_charges where payment_id = ?";
		try {
			$res = $db->prepare($CSQL);
			$res->execute(array($paymentid));
			$pres = $res->fetchObject();
		} catch(PDOException $ex) {
			return false;
		}
		
		
		
		
		if($pres){
			$USQL = "UPDATE payments_charges SET status = ? where payment_id = ?";
			try {
				$res = $db->prepare($USQL);
				$res->execute(array($charge, $paymentid));
			} catch(PDOException $ex) {
				return false;
			}
		}else{
			if(!empty($paymentcharge) && $paymentcharge == 1){
				$providername = !empty($providername) ? $providername : 'Qart Pay';
				$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at, charged_at) ";
				$SQL .= "VALUES (?, ?, NOW(), NOW()) ";
			}else{
				$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at) ";
				$SQL .= "VALUES (?, ?, NOW()) ";
			}
			
			try {
				$res = $db->prepare($SQL);
				$res->execute(array($paymentid, $charge));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
				return false;
			}
		}
		
		if(!empty($paymentcharge) && $paymentcharge == 1){
			$TSQL  = "INSERT INTO player_transactions (player_id, transaction_name, transaction_date, transaction_type, amount, total_amount, notes) ";
			$TSQL .= "VALUES (?, 'Deposit', NOW(), 'Credit', ?, ?, ?)";
			
			try {
				$res = $db->prepare($TSQL);
				$res->execute(array($playerid, $amount, $totalamount, $providername));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
				return false;
			}
		}
	}
	
}


function update_payments_new($paymentid, $post){
	$db = connect_db();
	if (!$db || !$paymentid){
		return false;
	}
	
	
	
	
	
	$SQL  = "UPDATE payments SET payment_image = ?, payment_person_name = ?, payment_upi = ?";
	$SQL .= "WHERE id = ? ";
	
	$documentroot = explode('/', $_SERVER['DOCUMENT_ROOT']);
	array_pop($documentroot);
	array_push($documentroot, 'logs');
	$root_path = implode('/', $documentroot);
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($post['filename'], $post['name'],$post['upi'],$paymentid));
		$logmessage = date('Y-m-d H:i:s')." Payment status updated as :".$status." for player: ".$playerid." with payment id: ".$paymentid."\n";
		file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		return true;
	} catch(PDOException $ex) {
		$logmessage = date('Y-m-d H:i:s')." Error while updating Payment status as :".$status." for payment ID:".$paymentid." Error message: ".$ex->getMessage()."\n";
		file_put_contents($root_path.'/payments.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	
	
}

//update payments requests table
function update_payments_requests($requestid, $providerid, $amount, $paymentMethod, $step = null, $availablebonus=null){
	$db = connect_db();
	if (!$db || !$requestid){
		return false;
	}
	
	$SQL  = "UPDATE payments_requests set ";
	$SQL .= "payment_method_id = ?, payment_provider_id = ?, amount = ?, player_currency_amount = ?";
	if($step){
		$SQL .= ", step = ".$step;
	}

	if(!empty($availablebonus)){
		$SQL .= ", bonuscode = '".$availablebonus."'";
	}

	$SQL .= " WHERE id = ? ";

	// echo $SQL; die;
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($paymentMethod, $providerid, $amount*100, $amount*100, $requestid));
		
		if ($res->fetchObject()->status == 'OK'){
			return false;
		} else {
			return true;
		}

	} catch(PDOException $ex) {
		//send_email(TECH_EMAIL, 'is_cancelled', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

//update Reward Points in payments requests table
function update_payments_reward_requests($requestid, $player_id, $amount){
    
	$db = connect_db();
	if (!$db || !$requestid){
		return false;
	}
		
	$SQL  = "select players_vip_classes_id,b.* from players a ";
	$SQL .= " left join reward_points b on a.players_vip_classes_id = b.vip_class_id";
	$SQL .= " where a.id=? and a.currency=b.currency_id and b.status=1; ";

        try {
            
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
                
                $comp_point_ratio   =   $res->fetchObject()->comp_point_ratio;
                $reward_points      =   ($amount)*($comp_point_ratio/100);
		
                $SQL  = "UPDATE payments_requests set ";
                $SQL .= "reward_points = ? ";
                $SQL .= " WHERE id = ? ";

                try {
                        $res = $db->prepare($SQL);
                        $res->execute(array($reward_points, $requestid));
                        
                        if ($res->fetchObject()->status == 'OK'){
                                return false;
                        } else {
                                return true;
                        }

                } catch(PDOException $ex) {
                        //send_email(TECH_EMAIL, 'is_cancelled', "catch: ".var_export($ex->getMessage(),true));
                        return false;
                }

	} catch(PDOException $ex) {
		//send_email(TECH_EMAIL, 'is_cancelled', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function update_payment_method($methodid, $providerid, $id, $firsttime=null, $loaded=0, $category = 0){
	$db = connect_db();
	if (!$db || !$methodid || !$id){
		return false;
	}
	$SQL  = "UPDATE payments_requests SET payment_method_id = ?, payment_provider_id = ?, has_loaded = ?, is_dummy = ? ";
	if($firsttime == 1){
		$SQL .= ", is_first_time = 1 ";
	}
	$SQL .= "WHERE id = ?";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($methodid, $providerid, $loaded, $category, $id));
		return true;
	} catch(PDOException $ex) {
		//echo "<pre>";
		//print_r($ex);
		//exit();
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function update_bitcoin_status($paymentid, $playerid, $status){
	$db = connect_db();
	if (!$db || !$paymentid || !$playerid){
		return false;
	}
	
	$SQL  = "UPDATE bitcoin_status SET status = ?, modified = NOW() ";
	$SQL .= "WHERE payment_id = ? AND player_id = ?";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($status, $paymentid, $playerid));
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
}

//update payments table id if the transaction from dummy visa chargeback from crm
function update_payments($paymentid, $params){
	$db = connect_db();
	if (!$db || !$paymentid){
		return false;
	}
	
	if($params['payment_provider_id'] == 107){
		$providerid = 108;
	}
	if($params['payment_method_id'] == 105){
		$paymentmethodid = 100;
	}
	
	$SQL  = "UPDATE payments set ";
	$SQL .= "payment_method_id = ?";
	$SQL .= " ,payment_provider_id = ?";
	$SQL .= " WHERE payment_request_id = ? ";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($params['payment_method_id'], $params['payment_provider_id'], $params['request_id']));
		
		if ($res->fetchObject()->status == 'OK'){
			return false;
		} else {
			return true;
		}

	} catch(PDOException $ex) {
		//send_email(TECH_EMAIL, 'is_cancelled', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

//Insert into vt attempts when transaction fails for dummy visa charge back from crm
function insert_vt_payments($paymentid, $params, $result){
	$db = connect_db();
	if (!$db || !$paymentid){
		return false;
	}
	
	
	$SQL  = "INSERT INTO vt_payment_attempts(player_id, payment_id, status, description, created) ";
	$SQL .= "VALUES (?, ?, ?, ?, NOW())";		
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($params['player_id'], $paymentid, $result['status'], $result['html']));
	} catch(PDOException $ex) {
	    //send_email(TECH_EMAIL, 'insert_payment', "catch 1: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_payment_charges($paymentid){
	$db = connect_db();
	if (!$db || !$paymentid){
		return false;
	}
	
	$SQL  = "SELECT count(*) as cnt FROM payments_charges ";
	$SQL .= "WHERE payment_id = ?";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($paymentid));
		return $res->fetchObject()->cnt;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
}

//function for update success transaction counts
function update_transaction_count($playerId, $providerId, $webId, $cardNumber){
	$db = connect_db();
	if (!$db){
		return false;
	}
	
	//check and insert/update transaction counts player based
	$SQL  = "SELECT * FROM counts_per_player ";
	$SQL .= "WHERE player_id = ? AND provider_id = ? AND web_id = ?";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($playerId, $providerId, $webId));
		$result = $res->fetchObject();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	if($result){
		$USQL  = "UPDATE counts_per_player SET ";
		$USQL .= "hour = ?, day = ?, week = ?, month = ?, always = ? ";
		$USQL .= "WHERE player_id = ? AND provider_id = ? AND web_id = ?";
		
		try {
			$res = $db->prepare($USQL);
			$res->execute(array($result->hour+1, $result->day+1, $result->week+1, $result->month+1, $result->always+1, $playerId, $providerId, $webId));
		} catch(PDOException $ex) {
			send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
			return false;
		}
	}else{
		$ISQL  = "INSERT INTO counts_per_player (player_id, provider_id, web_id, hour, day, week, month, always) ";
		$ISQL .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		
		try {
			$res = $db->prepare($ISQL);
			$res->execute(array($playerId, $providerId, $webId, 1, 1, 1, 1, 1));
		} catch(PDOException $ex) {
			send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
			return false;
		}
	}
	
	//check and insert/update transaction counts credit card based
	$SQL  = "SELECT * FROM counts_per_card ";
	$SQL .= "WHERE card_no = ENCODE(".$cardNumber.", 'xxfgtmnjidppbmyews@00910426#@$*') AND provider_id = ? AND web_id = ?";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($providerId, $webId));
		$result = $res->fetchObject();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	if($result){
		$USQL  = "UPDATE counts_per_card SET ";
		$USQL .= "hour = ?, day = ?, week = ?, month = ?, always = ? ";
		$USQL .= "WHERE card_no = ENCODE(".$cardNumber.", 'xxfgtmnjidppbmyews@00910426#@$*') AND provider_id = ? AND web_id = ?";
		
		try {
			$res = $db->prepare($USQL);
			$res->execute(array($result->hour+1, $result->day+1, $result->week+1, $result->month+1, $result->always+1, $providerId, $webId));
			return true;
		} catch(PDOException $ex) {
			send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
			return false;
		}
	}else{
		$ISQL  = "INSERT INTO counts_per_card (card_no, provider_id, web_id, hour, day, week, month, always) ";
		$ISQL .= "VALUES (ENCODE(".$cardNumber.", 'xxfgtmnjidppbmyews@00910426#@$*'), ?, ?, ?, ?, ?, ?, ?)";
		
		try {
			$res = $db->prepare($ISQL);
			$res->execute(array($providerId, $webId, 1, 1, 1, 1, 1));
			return true;
		} catch(PDOException $ex) {
			send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
			return false;
		}
	}
}

function update_payments_state($payment_id, $status, $errorcode, $foreign_errorcode, $transaction_id){
		
		$db = connect_db();
		if (!$db || !$payment_id || !$status){
			return false;
		}
		
		if (!$errorcode){
			$errorcode = 0;
		}
		
		if (!$foreign_errorcode){
			$foreign_errorcode = 0;
		}
		
		if (!$transaction_id){
			$transaction_id = 0;
		}
		
		$description = 'Declined';
		if ($status=='OK'){
				$description = 'Authorised';
		} elseif ($status=='PENDING'){
				$description = 'Pending';
		}
		
		$SQL  = "SELECT COUNT(*) AS total ";
		$SQL .= "FROM payments ";
		$SQL .= "WHERE id = ? ";
		$SQL .= "AND status = 'PENDING' ";
		
		try {
			$res = $db->prepare($SQL);
			$res->execute(array($payment_id));
			$exists = $res->fetchObject()->total;
		} catch(PDOException $ex) {
		    send_email(TECH_EMAIL, 'update_payments_state', "catch 1: ".var_export($ex->getMessage(),true));
			return false;
		}
			
		if ($exists==1){
				
				if ($transaction_id){
					
					$SQL  = "UPDATE payments ";
					$SQL .= "SET status = ?, description = ?, errorcode = ?, foreign_errorcode = ?, payment_foreign_id = ?, updated = NOW() ";
					$SQL .= "WHERE id = ? ";
					$SQL .= "AND status = 'PENDING' ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($status, $description, $errorcode, $foreign_errorcode, $transaction_id, $payment_id));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'update_payments_state', "catch 2: ".var_export($ex->getMessage(),true));
						return false;
					}
					
				} else {
					
					$SQL  = "UPDATE payments ";
					$SQL .= "SET status = ?, description = ?, errorcode = ?, foreign_errorcode = ?, updated = NOW() ";
					$SQL .= "WHERE id = ? ";
					$SQL .= "AND status = 'PENDING' ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($status, $description, $errorcode, $foreign_errorcode, $payment_id));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'update_payments_state', "catch 2: ".var_export($ex->getMessage(),true));
						return false;
					}
				}
				
				
				
				if ($status=='OK'){
						
					$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at) ";
					$SQL .= "VALUES (?, 0, NOW()) ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($payment_id));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'update_payments_state', "catch 3: ".var_export($ex->getMessage(),true));
						return false;
					}
				}
				return true;
		}
		
		return false;
}

function update_payments_state_for_pending_and_expired($payment_id, $status, $errorcode, $foreign_errorcode, $transaction_id){

	$db = connect_db();
	if (!$db || !$payment_id || !$status){
		return false;
	}

	if (!$errorcode){
		$errorcode = 0;
	}

	if (!$foreign_errorcode){
		$foreign_errorcode = 0;
	}

	if (!$transaction_id){
		$transaction_id = 0;
	}

	$description = 'Declined';
	if ($status=='OK'){
		$description = 'Authorised';
	} elseif ($status=='PENDING'){
		$description = 'Pending';
	}

	$SQL  = "SELECT COUNT(*) AS total ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "AND (status = 'PENDING' OR (status = 'KO' AND errorcode = 207 AND foreign_errorcode = 101)) ";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($payment_id));
		$exists = $res->fetchObject()->total;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'update_payments_state', "catch 1: ".var_export($ex->getMessage(),true));
		return false;
	}

	if ($exists==1){

		if ($transaction_id){

			$SQL  = "UPDATE payments ";
			$SQL .= "SET status = ?, description = ?, errorcode = ?, foreign_errorcode = ?, payment_foreign_id = ?, updated = NOW() ";
			$SQL .= "WHERE id = ? ";
			$SQL .= "AND (status = 'PENDING' OR (status = 'KO' AND errorcode = 207 AND foreign_errorcode = 101)) ";

			try {
				$res = $db->prepare($SQL);
				$res->execute(array($status, $description, $errorcode, $foreign_errorcode, $transaction_id, $payment_id));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'update_payments_state', "catch 2: ".var_export($ex->getMessage(),true));
				return false;
			}

		} else {

			$SQL  = "UPDATE payments ";
			$SQL .= "SET status = ?, description = ?, errorcode = ?, foreign_errorcode = ?, updated = NOW() ";
			$SQL .= "WHERE id = ? ";
			$SQL .= "AND (status = 'PENDING' OR (status = 'KO' AND errorcode = 207 AND foreign_errorcode = 101)) ";

			try {
				$res = $db->prepare($SQL);
				$res->execute(array($status, $description, $errorcode, $foreign_errorcode, $payment_id));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'update_payments_state', "catch 2: ".var_export($ex->getMessage(),true));
				return false;
			}
		}



		if ($status=='OK'){

			$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at) ";
			$SQL .= "VALUES (?, 0, NOW()) ";

			try {
				$res = $db->prepare($SQL);
				$res->execute(array($payment_id));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'update_payments_state', "catch 3: ".var_export($ex->getMessage(),true));
				return false;
			}
		}
		return true;
	}

	return false;
}

function insert_notify_data($params, $data, $payment_id) {
	
		$db = connect_db();
		if (!$db || !$params || !$payment_id){
				return false;
		}

		switch($params['payment_method_id']){
			case 110:
			case 121:
				
				$SQL  = "SELECT COUNT(*) AS total ";
				$SQL .= "FROM payments_email ";
				$SQL .= "WHERE payment_id = ? ";
				
				try {
					$res = $db->prepare($SQL);
					$res->execute(array($payment_id));
					$exists = $res->fetchObject()->total;
				} catch(PDOException $ex) {
					send_email(TECH_EMAIL, 'insert_notify_data', "catch 1: ".var_export($ex->getMessage(),true));
					return false;
				}
				
				if ($exists){
					return false;
				}

				if ($data['pay_from_email'] && $data['pay_to_email']){
					
					
					if ($data['rec_payment_id'] && strtolower($data['rec_payment_type']) == 'on-demand'){ // INICIO 1-TAP
					
						$SQL  = "SELECT COUNT(*) AS total ";
						$SQL .= "FROM payments_email_1tap ";
						$SQL .= "WHERE rec_payment_id = ? ";
						$SQL .= "AND player_id = ? ";
						
						try {
							$res = $db->prepare($SQL);
							$res->execute(array($data['rec_payment_id'], $params['player_id']));
							$exists = $res->fetchObject()->total;
						} catch(PDOException $ex) {
							send_email(TECH_EMAIL, 'insert_notify_data', "catch 2: ".var_export($ex->getMessage(),true));
						}
						
						if ($exists){
							
							$SQL  = "UPDATE payments_email_1tap ";
							$SQL .= "SET payment_id = ?, updated = NOW(), status = 1 ";
							$SQL .= "WHERE rec_payment_id = ? ";
							$SQL .= "AND player_id = ? ";
							
							try {
								$res = $db->prepare($SQL);
								$res->execute(array($payment_id, $data['rec_payment_id'], $params['player_id']));
							} catch(PDOException $ex) {
								send_email(TECH_EMAIL, 'insert_notify_data', "catch 3: ".var_export($ex->getMessage(),true));
							}
							
						} else {
							
							$SQL  = "INSERT INTO payments_email_1tap(consumer_email, player_id, business_email, payment_id, rec_payment_id, date, updated, status) ";
							$SQL .= "VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 1) ";
							
							try {
								$res = $db->prepare($SQL);
								$res->execute(array($data['pay_from_email'], $params['player_id'], $data['pay_to_email'], $payment_id, $data['rec_payment_id']));
							} catch(PDOException $ex) {
								send_email(TECH_EMAIL, 'insert_notify_data', "catch 4: ".var_export($ex->getMessage(),true));
							}
						}
					} // FIN 1-TAP
					
					
					$SQL  = "INSERT INTO payments_email(consumer_email, business_email, payment_id) ";
					$SQL .= "VALUES (?, ?, ?) ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($data['pay_from_email'], $data['pay_to_email'], $payment_id));
					} catch(PDOException $ex) {
						send_email(TECH_EMAIL, 'insert_notify_data', "catch 3: ".var_export($ex->getMessage(),true));
						return false;
					}
					return true;
					
				} else {
					return false;
				}
			break;
		}
		
		return true;
}

function get_payment_date($payment_id){

	$db = connect_db();
	if (!$db || !$payment_id){
		return false;
	}

	$SQL  = "SELECT date ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($payment_id));
		
		return $res->fetchObject()->date;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_payment_date', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_payment_amount($payment_id){

	$db = connect_db();
	if (!$db || !$payment_id){
		return false;
	}

	$SQL  = "SELECT amount ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($payment_id));
		
		return $res->fetchObject()->amount;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_payment_amount', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function send_email($to, $subject, $message){
	
		if (SEND_EMAIL_TO_TECH) {
		
			$headers = "From: xxx <xxx@xxx.xxx>\r\n";
			
			if (!$to || !$subject || !$message){
					return false;
			}
	
			if (mail($to, $subject, $message, $headers)){
					return true;
			} else {
					return false;
			}
		}
}

function get_email_notification($notification_type, $language){

		$db = connect_db();
		if (!$db || !$notification_type || !$language){			
				return false;
		}
				
		$SQL  = "SELECT subject, message ";
		$SQL .= "FROM email_notifications ";
		$SQL .= "WHERE type = ? ";
		$SQL .= "AND language = ? ";
		$SQL .= "LIMIT 1";
	
		try {
				$res = $db->prepare($SQL);
				$res->execute(array($notification_type, $language));
				$data = $res->fetchObject();
				return array('subject'=>$data->subject, 'message'=>$data->message);
		
		} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'get_email_notification', "catch: ".var_export($ex->getMessage(),true));
				return false;
		}
}

function send_email_notification($payment_id, $notification_type, $params){

		if (!$payment_id || !$notification_type || !$params){
				return false;
		}

		$to = get_player_email($params['player_id']);
		$name = get_player_name($params['player_id']);

		$language = strtoupper($params['language']);
		if (!$language){
			$language = 'EN';
		}
		
		$notification_data = get_email_notification($notification_type, $language);
		if (!$notification_data){
				return false;
		}
				
		$amount = get_payment_amount($payment_id)/100;
		$currency = $params['currency_id'];
		switch($currency){
				case 'EUR': $amount .= ' �'; break;
				case 'GBP': $amount = '� '.$amount; break;
				case 'USD': $amount = '$ '.$amount; break;
				default: $amount .= ' '.$currency;
		}
		
		$subject = $notification_data['subject'];
		$message = $notification_data['message'];
		
		$message = str_replace('{{NAME}}', $name, $message);
		$message = str_replace('{{AMOUNT}}', $amount, $message);
		$message = str_replace('{{TRANSACTIONID}}', $payment_id, $message);
		
		if (!$to || !$subject || !$message){
				return false;
		}
		
		if (send_email($to, $subject, $message)){
				return true;
		} else {
				send_email(TECH_EMAIL, 'send_email_notification', "!send_email");
				return false;
		}
}

function convert_currency($from, $to, $amount){
	
	$db = connect_db();
	if (!$db || !$from || !$to || !$amount){
		return false;
	}
	
	$SQL  = "SELECT currencies_convert_amount(?,?,?) AS conversion_rate";
	
	try {
		
		$res = $db->prepare($SQL);
		$res->execute(array($from, $to, $amount));
		return $res->fetchObject()->conversion_rate;
		
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'convert_currency', var_export($ex->getMessage(),true));
		return false;
	}
}

function generate_payments_requests($id, $amount){
	
	$db = connect_db();
	if (!$db || !$id){
		return false;
	}
	
	$data = get_params_from_id($id);
	

	$amount_conv = convert_currency($data['currency_id'], $data['player_currency_id'], $amount);
	$amount_conv = round($amount_conv);
	
	$request_id = generate_payment_request_id();
	
	if (!$request_id || !$amount_conv){
		return false;
	}
	
	$SQL  = "INSERT INTO payments_requests(request_id, amount, currency_id, player_currency_amount, player_currency_id, payment_method_id, country_id, player_id, language, ip, foreign_id, redirect_url, inserted_at, request_finished_at, response_finished_at, step, web_id, payment_provider_id) ";
	$SQL .= "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
    
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id, $amount, $data['currency_id'], $amount_conv, $data['player_currency_id'],
							$data['payment_method_id'], $data['country_id'], $data['player_id'], $data['language'], $data['ip'],
							null, $data['redirect_url'], $data['inserted_at'], $data['request_finished_at'],
							$data['response_finished_at'], $data['step'], $data['web_id'], $data['payment_provider_id']));
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments_requests', "catch 1: ".var_export($ex->getMessage(),true));
		return false;
	}

	$SQL  = "SELECT id ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE request_id = ? ";
	$SQL .= "LIMIT 1 ";
	
	$new_id = false;
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id));
		$new_id = $res->fetchObject()->id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments_requests', "catch 2: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	return $new_id;
	
}

function generate_payments($old_id, $new_id, $amount){
	
	$db = connect_db();
	if (!$db || !$old_id || !$new_id || !$amount){
		return false;
	}
		
	$SQL  = "SELECT request_id ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1 ";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($old_id));
		$old_request_id = $res->fetchObject()->request_id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments', "catch 1: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	$SQL  = "SELECT request_id ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1 ";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($new_id));
		$new_request_id = $res->fetchObject()->request_id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments', "catch 2: ".var_export($ex->getMessage(),true));
		return false;
	}
		
	$SQL  = "SELECT * ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE payment_request_id = ? ";
	$SQL .= "LIMIT 1 ";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($old_request_id));
		$data = $res->fetchObject();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments', "catch 3: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	$amount_conv = convert_currency($data->currency_id, $data->player_currency_id, $amount);
	$amount_conv = round($amount_conv);
	

	$SQL  = "INSERT INTO payments(amount, description, date, updated, status, errorcode,	foreign_errorcode, ip, currency_id, player_id, payment_method_id, payment_provider_id, payment_request_id, web_id, country_id, payment_foreign_id, player_currency_amount, player_currency_id)";
	$SQL .= "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($amount, $data->description, $data->date, $data->updated, $data->status, $data->errorcode,
							$data->foreign_errorcode, $data->ip, $data->currency_id, $data->player_id, $data->payment_method_id,
							$data->payment_provider_id, $new_request_id, $data->web_id, $data->country_id, null,
							$amount_conv, $data->player_currency_id));
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments', "catch 4: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	$SQL  = "SELECT id ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE payment_request_id = ? ";
	$SQL .= "LIMIT 1 ";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($new_request_id));
		$payment_id = $res->fetchObject()->id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'generate_payments', "catch 4: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	return $payment_id;
	
}

function is_wap($web_id){

	$db = connect_db();
	if (!$db || !$web_id){
		return false;
	}

	$SQL  = "SELECT is_mobile ";
	$SQL .= "FROM webs ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($web_id));
		
		return $res->fetchObject()->is_mobile;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'is_wap', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function is_cancelled($request_id){

	$db = connect_db();
	if (!$db || !$request_id){
		return false;
	}

	$SQL  = "SELECT status ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE payment_request_id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id));
		
		if ($res->fetchObject()->status == 'OK'){
			return false;
		} else {
			return true;
		}

	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'is_cancelled', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function check_is_allowed_to_pay($payment_method_id, $player_id, $payment_country_iso, $amount) {
	
	if (!$payment_method_id || !$player_id || !$payment_country_iso || !$amount) {
		return false;
	}
	
	$arr_post = array(
			"payment_method_id"	=> $payment_method_id,
			"player_id" => $player_id,
			"payment_country_iso" => $payment_country_iso,
			"amount" => amount
	);
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_PORT, 8181);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_post));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		
	$resp = curl_exec($ch);
	
	if (!curl_errno($ch) && $resp) {
		$json_result = json_decode($resp, true);
		if (!$json_result['error'] && !$json_result['data']['error'] && $json_result['data']['allowed']) {
			return true;
		}
	}
	
	return false;
}

//This function to update counts to zero based on parameter like hour, day, week, month, always
function update_player_transaction_counts($QUERY, $interval, $type){
	$db = connect_db();
	if (!$db || !$QUERY){
		return false;
	}
	
	try {
		$res = $db->prepare($QUERY);
		$res->execute();
		$logmessage  = "//---- ".date('Y-m-d H:i:s')." Cron job initiated to update ".$interval." counts based on ".$type." ----//"."\n";
		$logmessage .= date('Y-m-d H:i:s')." Cron job run successfull for query: ".$QUERY."\n";
		$logmessage .= "//---- ".date('Y-m-d H:i:s')." Cron job execution finished -----//"."\n";
		file_put_contents(dirname(__DIR__).'/logs/cron.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
	} catch(PDOException $ex) {
		$logmessage = date('Y-m-d H:i:s')." Cron job failed to update for query: ".$QUERY." due to ".var_export($ex->getMessage(),true)."\n";
		file_put_contents(dirname(__DIR__).'/logs/cron.log', $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);
		return false;
	}
}

function get_payment_details($payment_id){

	$db = connect_db();
	if (!$db || !$payment_id){
		return false;
	}

	$SQL  = "SELECT * ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE id = ? ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($payment_id));
		return $res->fetch();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_payment_date', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function check_skrill_payment_status($paymentid, $playerid){
	$db = connect_db();
	if (!$db || !$paymentid || !$playerid){
		return false;
	}

	$SQL  = "SELECT * FROM payments";
	$SQL .= " WHERE id = ? AND player_id = ?";
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($paymentid, $playerid));
		
		return $res->fetchObject()->status;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_birthdate', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function fetchCountryCode($code){
	$db = connect_db();
    if (!$db || !$code){
        return false;
    }
    
    $SQL = "SELECT code3 from countries where iso = ?";
    try{
    	$res = $db->prepare($SQL);
    	$res->execute(array($code));
    	return $res->fetchObject();
    } catch(PDOException $ex) {
    	return false;
    }
}

function get_states_name($statecode, $countrycode){
	$db = connect_db();
	if (!$db || !$statecode){
		return false;
	}

	$SQL  = "SELECT * ";
	$SQL .= "FROM states ";
	$SQL .= "WHERE country_code = ? AND state_code = ?";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($countrycode, $statecode));
		
		return $res->fetchAll();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'set_player_name', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

//Insert cubits account data
function insert_cubits_account($account, $playerId){
	$db = connect_db();
	if (!$db || !$playerId){
		return false;
	}
	
	$SQL  = "INSERT INTO players_cubits_account(player_id, cubit_id, cubit_address, currency, name, description, reference, channel_url, callback_url, success_url, created_at, txs_callback_url) ";
	$SQL .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";		
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($playerId, $account->id, $account->address, $account->receiver_currency, $account->name, $account->description, $account->reference, $account->channel_url, $account->callback_url, $account->success_url, $account->txs_callback_url));
	} catch(PDOException $ex) {
		return false;
	}
}

function check_player_cubitacc($playerId){
	$db = connect_db();
	if (!$db || !$playerId){
		return false;
	}
	
	$SQL  = "SELECT * FROM players_cubits_account ";
	$SQL .= "WHERE player_id = ? ";
	$SQL .= "ORDER BY id DESC LIMIT 1";		
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($playerId));
		return $res->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
}


//create payment request
function insert_payment_request($data, $redirectUrl){
	$db = connect_db();
	if (!$db){
		return false;
	}
	$signature = md5('SDWQ' . uniqid(true) . '1tf2') . '-' . sha1('TXZQ' . uniqid(true) . 'piq2');
	
	//get player details
	$GET  = "SELECT p.* from players_cubits_account c LEFT JOIN players p ON (p.id=c.player_id) where c.cubit_id = '".$data['channel_id']."'";
	try{
		$result = $db->prepare($GET);
		$result->execute();
		$playerdetails = $result->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
	
	//get paymentmethod and payment provider details
	$GETMETHOD = "SELECT m.id as method, p.id FROM payments_methods m LEFT JOIN payments_providers_methods pm ON (pm.payment_method_id = m.id) LEFT JOIN payments_providers p ON (p.id=pm.payment_provider_id) where m.id = 109";
	try{
		$presult = $db->prepare($GETMETHOD);
		$presult->execute();
		$mdetails = $presult->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
	
	$SQL  = "INSERT INTO payments_requests (request_id, amount, currency_id, player_currency_amount, player_currency_id, payment_method_id, country_id, player_id, language, ip, redirect_url, inserted_at, request_finished_at, response_finished_at, step, web_id, payment_provider_id) ";
	$SQL .= "VALUES(?, ?, ?, ?, ?, ?, ?, ?, 'en', ?, ?, NOW(), NOW(), NOW(), 3, ?, ?)";
	
	try{
		$res = $db->prepare($SQL);
		$res->execute(array($signature, $data['receiver']['amount']*100, $data['receiver']['currency'], $data['receiver']['amount']*100, $data['receiver']['currency'], $mdetails->method, $playerdetails->country, $playerdetails->id, $playerdetails->register_IP, $redirectUrl, $playerdetails->web_id, $mdetails->id));
	}catch(PDOException $ex) {
		return false;
	}
	
	return $signature;
}


function insert_payment_cubitid($paymentId, $cubitId, $callbackid, $channelid){
	$db = connect_db();
	if (!$db){
		return false;
	}
	
	$CSQL  = "INSERT INTO payments_cubit_id (payment_id, cubit_id, channel_id, created) ";
	$CSQL .= "VALUES (?, ?, ?, NOW())";
	
	try {
		$res = $db->prepare($CSQL);
		$res->execute(array($paymentId, $cubitId, $channelid));
	} catch(PDOException $ex) {
		return false;
	}
}


function check_cubit_payment($cubitId, $channelid){
	$db = connect_db();
	if (!$db || !$cubitId){
		return false;
	}
	
	$CSQL  = "SELECT p.* FROM payments_cubit_id c ";
	$CSQL .= "LEFT JOIN payments p ON (p.id=c.payment_id) ";
	$CSQL .= "WHERE c.cubit_id = '".$cubitId."' AND c.channel_id = '".$channelid."'";
	
	try {
		$res = $db->prepare($CSQL);
		$res->execute();
		return $res->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
}

function insert_player_transactions($playerid, $amount, $totalamount, $payment_id){
	$db = connect_db();
	if (!$db){
		return false;
	}
	
	$TSQL  = "INSERT INTO player_transactions (player_id, transaction_name, transaction_date, transaction_type, amount, total_amount, notes) ";
	$TSQL .= "VALUES (?, 'Deposit', NOW(), 'Credit', ?, ?, 'Cubits bitcoin')";
	
	try {
		$res = $db->prepare($TSQL);
		$res->execute(array($playerid, $amount, $totalamount));
	} catch(PDOException $ex) {
		return false;
	}
	
	$CSQL  = "UPDATE payments_charges set status = 1, charged_at = NOW() ";
	$CSQL .= "WHERE payment_id = ?";
	
	try {
		$res = $db->prepare($CSQL);
		$res->execute(array($payment_id));
	} catch(PDOException $ex) {
		return false;
	}
}

function update_player_balance($params){
	$db = connect_db();
	if (!$db || !$params){
		return false;
	}
	
	$SQL  = "SELECT balance ";
    $SQL .= "FROM createmaster ";
    $SQL .= "WHERE mstrid = ? ";
    $SQL .= "LIMIT 1";
    $res = $db->prepare($SQL);
    $res->execute(array($params['player_id']));
    $result = $res->fetchObject();
	$totalbalance = $result->balance+$params['amount'];
	
	$SQL  = "UPDATE createmaster set balance = ? ";
	$SQL .= "where mstrid = ?";		
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($totalbalance, $params['player_id']));
	} catch(PDOException $ex) {
		return false;
	}
	
}

function getPlayerVIPLevels($riskId){
	$db = connect_db();
    if (!$db || !$riskId){
        return false;
    }
	
	$SQL  = "SELECT * FROM players_classes ";
    $SQL .= "WHERE id = ? ";
	
    try {
        $res = $db->prepare($SQL);
        $res->execute(array($riskId));
		return $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }
}

//To update Reward Points to Player in Wallet Deposits
function UpdatePlayerRewardpoints($playerId,$params,$PAYMENT_ID)
{
	$db = connect_db();
	if (!$db){
		return false;
	}
	$SQL  = "UPDATE players ";
	$SQL .= "SET reward_points = reward_points + ?  ";
	$SQL .= "WHERE id = ? ";

	try {
		$array =	array($params['reward_points'],$playerId);
		$res1 = $db->prepare($SQL);
		$res1->execute($array);


//        $logmessage = date('Y-m-d H:i:s')." Player Reward Points Update Query :".($SQL)."\n";
//        $logmessage = date('Y-m-d H:i:s')." parameters for Player Reward Points Update Query :".($array)."\n";
//        file_put_contents($log_file, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

	} catch(PDOException $ex) {
		return false;
	}
	//	Insert into Reward Points table
	$currentPlayerSQL = "SELECT reward_points,balance FROM players WHERE id = ? ";
	$currPlayerRes = $db->prepare($currentPlayerSQL);
	$currPlayerRes->execute(array($playerId));
	$current_reward_points 	=	$currPlayerRes->fetchObject()->reward_points;
	$data                       =	array();
	$data['deposit_amount']	=	$params['player_currency_amount'];
	$data['deposit_status']	=	"Successfull Deposit";


	$SQL  = "INSERT INTO reward_points_history(player_id, reward_points, txn_type,
             total_reward_points, created_on,details,payment_id) ";
	$SQL .= "VALUES (?, ?, ?, ?, NOW(),?,?)";
	try {
		$res2 = $db->prepare($SQL);
		$res2->execute(array($params['player_id'], $params['reward_points'],
				"credit", $current_reward_points,json_encode($data),$PAYMENT_ID));
	} catch(PDOException $ex) {
		return false;
	}
}

function getPaymentDetails($paymentId){
	$db = connect_db();
    if (!$db || !$paymentId){
        return false;
    }
	
	$SQL  = "SELECT * FROM payments ";
    $SQL .= "WHERE id = ? ";
	
    try {
        $res = $db->prepare($SQL);
        $res->execute(array($paymentId));
		return $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }
}

function update_payment_foreignid($paymentId, $transId){
	$db = connect_db();
	if (!$db || !$paymentId){
		return false;
	}
	
	$SQL  = "UPDATE payments ";
    $SQL .= "SET payment_foreign_id = ? ";
    $SQL .= "WHERE id = ? ";
	
	try {
		$res = $db->prepare($SQL);
    	$res->execute(array($transId, $paymentId));
	} catch(PDOException $ex) {
		return false;
	}
}


function update_payment_token($paymentId, $transId){
	$db = connect_db();
	if (!$db || !$paymentId){
		return false;
	}
	
	$SQL  = "UPDATE payments ";
    $SQL .= "SET order_token = ? ";
    $SQL .= "WHERE id = ? ";
	
	try {
		$res = $db->prepare($SQL);
    	$res->execute(array($transId, $paymentId));
	} catch(PDOException $ex) {
		return false;
	}
}

function update_payment_details($paymentId, $ref,$posturl){
	$db = connect_db();
	if (!$db || !$paymentId){
		return false;
	}
	
	$SQL  = "UPDATE payments ";
    $SQL .= "SET reference_number = ? , response_postback = ? ";
    $SQL .= "WHERE id = ? ";
	
	try {
		$res = $db->prepare($SQL);
    	$res->execute(array($ref, $posturl , $paymentId));
	} catch(PDOException $ex) {
		return false;
	}
}

//Generating guid for ipasspay payment gateway
function create_guid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45); // "-"
    $uuid = substr($charid, 0, 8) . $hyphen
    . substr($charid, 8, 4) . $hyphen
    . substr($charid, 12, 4) . $hyphen
    . substr($charid, 16, 4) . $hyphen
    . substr($charid, 20, 12);
	
    return $uuid;
}

function get_player_transactions($playerId){
		$db = connect_db();
        if (!$db || !$playerId){
                return false;
        }


        $SQL  = "SELECT count(*) as cnt ";
        $SQL .= "FROM payments ";
        $SQL .= "WHERE player_id = ? ";
        $SQL .= "AND payment_provider_id NOT IN (?, ?, ?) AND status = 'OK' ";

        try {
                $res = $db->prepare($SQL);
                $res->execute(array($playerId, 104, 111, 112));
                return $res->fetchObject();
        } catch(PDOException $ex) {
                send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
                return false;
        }
}

//get provider based fails counts
function get_provider_based_fails($playerId){
	$db = connect_db();
    if (!$db || !$playerId){
        return false;
    }
	// $SQL  = "SELECT count(*) as cnt from payments ";
    // $SQL .= "WHERE player_id = ?";
    $SQL  = "select count(*) as cnt from ";
    $SQL .= "((SELECT p.* FROM betting.payments p where p.player_id= ".$playerId." and p.status='SUCCESS') ";
	$SQL .=	" UNION (select * from betting.payments pm where pm.player_id= ".$playerId." and pm.payment_method_id IN (100,101))) as cnt";
	
    try {
        $res = $db->prepare($SQL);
        $res->execute();
		$result = $res->fetchObject();
		return $result->cnt;
    } catch(PDOException $ex) {
        return false;
    }
}

function get_player_hasdummy($playerId, $methodId){
	$db = connect_db();
    if (!$db || !$playerId){
        return false;
    }
	$SQL  = "SELECT count(*) as cnt from payments ";
    $SQL .= "WHERE player_id = ? and payment_method_id IN (?) ";
	
    try {
        $res = $db->prepare($SQL);
        $res->execute(array($playerId, $methodId));
		return $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }
}

//get payment method details
function getPaymenthodDetails($methodId){
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

//Check card number exists or not
function get_payment_card_details($card_no){
    $db = connect_db();
    if (!$db || !$card_no){
        return false;
    }
	
    $SQL  = "SELECT * ";
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
/********************* Get payment id by provider id *********************/
function get_payment_id_by_providerid($request_id, $providerpaymentid){
	
	$db = connect_db();
	if (!$db || !$request_id || !$providerpaymentid){
		return false;
	}
	
	$SQL  = "SELECT id ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE payment_request_id = ? AND payment_foreign_id = ?";
	$SQL .= "LIMIT 1";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id, $providerpaymentid));
		return $res->fetchObject()->id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_payment_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}

/********************* Get payment id by provider id *********************/
function get_payment_id_by_requestid($request_id){
	
	$db = connect_db();
	if (!$db || !$request_id){
		return false;
	}
	
	$SQL  = "SELECT id ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE payment_request_id = ? ";
	$SQL .= "LIMIT 1";
		
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($request_id));
		return $res->fetchObject()->id;
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_payment_id', "catch: ".var_export($ex->getMessage(),true));
		return false;
	}
}
/********************* Get payment id by provider id *********************/
function update_payment_discriptor($paymentId, $payment_descriptor){
        $db = connect_db();
        if (!$db || !$paymentId){
                return false;
        }

        $SQL  = "UPDATE payments ";
    $SQL .= "SET payment_descriptor = ? ";
    $SQL .= "WHERE id = ? ";

        try {
                $res = $db->prepare($SQL);
        $res->execute(array($payment_descriptor, $paymentId));
        } catch(PDOException $ex) {
                return false;
        }
}



function updatePlayersRiskLevel($playerId){
        $db = connect_db();
        if (!$db || !$playerId){
                return false;
        }

        $SQL  = "UPDATE players ";
    $SQL .= "SET players_classes_id = 9 ";
    $SQL .= "WHERE id = ? ";

        try {
                $res = $db->prepare($SQL);
                $res->execute(array($playerId));
        } catch(PDOException $ex) {
                return false;
        }
}

function get_current_paymentmethodname($method){
	$db = connect_db();
	if (!$db || !$method){
		return false;
	}
	
	$SQL  = "SELECT name ";
    $SQL .= "FROM payments_methods ";
    $SQL .= "WHERE id = ? ";
    $SQL .= "LIMIT 1";

    try {
            $res = $db->prepare($SQL);
            $res->execute(array($method));
            return $res->fetchObject();
    } catch(PDOException $ex) {
            send_email(TECH_EMAIL, 'get_player_all_details', "catch: ".var_export($ex->getMessage(),true));
            return false;
    }
}

function check_plays_bonuses($playerId){
	
		$dbConn = connect_db();
        if (!$dbConn || !$playerId){
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
		
		$BSQL  = "SELECT count(*) as cnt FROM players_bonuses WHERE player_id = ? and bonus_status_id = 3";
        try{
	        $res = $dbConn->prepare($BSQL);
	        $res->execute(array($playerId));
	        $bonus = $res->fetchObject();
		}catch(PDOException $ex) {
	        return false;
	    }
       
        if(($plays->cnt > 0) && ($bonus->cnt > 0)){
        	
		 return 1;
        	
        }else{
        	return 0;
        }
}

function check_player_plays($playerId){
	
		$dbConn = connect_db();
        if (!$dbConn || !$playerId){
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
		
		
       
        if($plays->cnt > 0) {
        	
		 return 1;
        	
        }else{
        	return 0;
        }
}			

function check_player_bonuses($playerId){
	$dbConn = connect_db();
        if (!$dbConn || !$playerId){
                return false;
        }
		$BSQL  = "SELECT count(*) as cnt FROM players_bonuses WHERE player_id = ? ";
        try{
	        $res = $dbConn->prepare($BSQL);
	        $res->execute(array($playerId));
	        $bonus = $res->fetchObject();
		}catch(PDOException $ex) {
	        return false;
	    }
		 if(($bonus->cnt > 0)){
        	
		 return 1;
        	
        }else{
        	return 0;
        }
	
}

function updateAuditorRiskLevel($playerId, $risklevel){
	$db = connect_db();
    if (!$db || !$playerId || !$risklevel){
            return false;
    }

    $SQL  = "UPDATE players ";
    $SQL .= "SET players_classes_id = ? ";
    $SQL .= "WHERE id = ? ";

    try {
            $res = $db->prepare($SQL);
            $res->execute(array($risklevel, $playerId));
    } catch(PDOException $ex) {
            return false;
    }
}

function checkIsDepositor($playerId){
	
	$dbConn = connect_db();
    if (!$dbConn || !$playerId){
            return false;
    }
	
	$SQL  = "SELECT count(*) as cnt FROM payments WHERE player_id = ? and status = 'OK'";

    try{
        $res = $dbConn->prepare($SQL);
        $res->execute(array($playerId));
        return $res->fetchObject();
	}catch(PDOException $ex) {
        return false;
    }
       
        	
}

function checkplayerrisklevel($player_id){
	
	$db = connect_db();
    if (!$db || !$player_id){
            return false;
    }
	
	$playerclass = "SELECT players_classes_id FROM players WHERE id = ? ";
	$playerclassres = $db->prepare($playerclass);
	$playerclassres->execute(array($player_id));
	$playerclassid = $playerclassres->fetchObject();
	if($playerclassid->players_classes_id == 8 ){
	updatePlayersRiskLevel($player_id);
	}
			
}

function check_player_payments($player_id){
	
	$db = connect_db();
    if (!$db || !$player_id){
            return false;
    }
	
	$playerdep = "SELECT count(*) as cnt  FROM payments WHERE player_id = ? and status='SUCCESS' and payment_method_id NOT IN(106,107)";
	$playerdepinfo= $db->prepare($playerdep);
	$playerdepinfo->execute(array($player_id));
	$playerdeposits = $playerdepinfo->fetchObject();
	
	if($playerdeposits->cnt >=1){
		$count=1;
	}else{
		$count=0;
	}
	return $count;
			
}

/*** Multi currency code ****/
function get_currency_symbol($currencyid){
    $db = connect_db();
    if (!$db || !$currencyid){
        return false;
    }
	
    $SQL  = "SELECT *, CONCAT('(', RTRIM(LTRIM(symbol)) ,')') as sym ";
    $SQL .= "FROM currencies ";
    $SQL .= "WHERE iso = ?";
    // echo $currencyid;
    try {
        $res = $db->prepare($SQL);
		$res->execute(array($currencyid));
		return $res->fetch();
    } catch(PDOException $ex) {
        return false;
    }
}

function update_payment_request_data($id, $actualAmount, $currency, $amount, $conversionrate, $step){
	$db = connect_db();
    if (!$db || !$id || !$currency || !$amount){
        return false;
    }
	
    $SQL  = "UPDATE payments_requests ";
    $SQL .= "SET amount = ?, currency_id = ?, player_currency_amount = ?, client_currency_amount = ?, default_currency = ?, conversion_rate = ?, step = ?";
    $SQL .= "WHERE id = ? ";

    try {
        $res = $db->prepare($SQL);
		$res->execute(array($actualAmount, $currency, $amount, $amount, $currency, $conversionrate, $step, $id));
    } catch(PDOException $ex) {
        return false;
    }
}

function get_conversion_rate($defaultcurrencyId, $playercurrencyId){
	$db = connect_db();
    if (!$db || !$defaultcurrencyId || !$playercurrencyId){
        return false;
    }
    $SQL  = "SELECT * ";
    $SQL .= "FROM currencies_equivalences_history ";
    $SQL .= "WHERE currency_id_from = ? AND currency_id_to = ? AND date(created) = CURDATE() ";
	$SQL .= "ORDER BY id DESC ";
    $SQL .= "LIMIT 1";
    try {
        $res = $db->prepare($SQL);
		$res->execute(array($defaultcurrencyId, $playercurrencyId));
		return $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }
}

function get_currency_available_processor($currencyId, $method_id){
	$db = connect_db();
    if (!$db || !$currencyId){
        return false;
    }
	
    $SQL  = "SELECT COUNT(*) count ";
    $SQL .= "FROM payments_providers_currencies pc ";
	$SQL .= "LEFT JOIN payments_providers pp ON (pp.id = pc.payment_provider_id) ";
	$SQL .= "INNER JOIN payments_providers_methods pm ON (pm.payment_provider_id = pc.payment_provider_id) ";
    $SQL .= "WHERE pc.currency_id = ? AND pc.is_deleted = 0 AND pp.is_active = 1 AND pm.payment_method_id = ?";
    
    try {
        $res = $db->prepare($SQL);
		$res->execute(array($currencyId, $method_id));
		return $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }
}

function get_currency_available_processor_by_provider($currencyId, $providerId = null){
	$db = connect_db();
    if (!$db || !$currencyId){
        return false;
    }
	
    $SQL  = "SELECT COUNT(*) count ";
    $SQL .= "FROM payments_providers_currencies pc ";
	$SQL .= "LEFT JOIN payments_providers pp ON (pp.id = pc.payment_provider_id) ";
	$SQL .= "INNER JOIN payments_providers_methods pm ON (pm.payment_provider_id = pc.payment_provider_id) ";
    $SQL .= "WHERE pc.currency_id = ? AND pc.is_deleted = 0 AND pp.is_active = 1";
	if(!is_null($providerId)){
		$SQL .= " AND pp.id = ".$providerId;
	}
	
    try {
        $res = $db->prepare($SQL);
		$res->execute(array($currencyId));
		return $res->fetchObject();
    } catch(PDOException $ex) {
        return false;
    }
}
/*** Multi currency code ****/

function checkDepositByProvider($playerId, $providerId){
	
	$dbConn = connect_db();
    if (!$dbConn || !$playerId || !$providerId){
            return false;
    }
	
	$SQL  = "SELECT count(*) as cnt FROM payments WHERE player_id = ? and payment_provider_id = ? and status = 'OK'";
	
    try{
        $res = $dbConn->prepare($SQL);
        $res->execute(array($playerId, $providerId));
        return $res->fetchObject();
	}catch(PDOException $ex) {
        return false;
    }
       
        	
}

function check_player_kyc($playerId, $cardnumber){
	
	
	$dbConn = connect_db();
    if (!$dbConn || !$playerId ){
            return false;
    }
    
	$CHECK  = "SELECT * from player_kyc ";
	$CHECK .= "WHERE player_id = ? AND card_no = ENCODE(".$cardnumber.", 'xxfgtmnjidppbmyews@00910426#@$*') AND kyc_received=1 and is_blocked=0";
	
	try {
		$res = $dbConn->prepare($CHECK);
		$res->execute(array($playerId));
		$result = $res->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
	
	if($result->kyc_received==1){
		return 1;
	}else{
		return 0;
	}
	
} 


function get_payments(){
	$db = connect_db();
    if (!$db){
        return false;
    }
	$curtime = time() - 600;
	$cur_day = date('d');
	
	$start_time = time()-($cur_day * 24 *3600);
	//$start_date = date("Y-m-d H:i:s",$start_time);
	$start_date = date("Y-m-d H:i:s",$start_time);
	
	$end_date = date("Y-m-d H:i:s",$curtime);
	
    $SQL  = "SELECT * ";
    $SQL .= "FROM payments ";
    $SQL .= "WHERE (status = ? OR status = ?) AND payment_method_id IN (134,133)  AND date>='$start_date' and date<='$end_date'";
	$SQL .= " ORDER BY id DESC ";
    
    try {
        $res = $db->prepare($SQL);
		$res->execute(array('INITIATED','PENDING'));
		return $res->fetchAll();
    } catch(PDOException $ex) {
        return false;
    }
}


function get_success_count($playerId){
	
	$dbConn = connect_db();
    if (!$dbConn || !$playerId ){
            return false;
    }
	
	$SQL  = "SELECT count(*) as cnt FROM payments WHERE player_id = ? and status = 'SUCCESS'";
	
    try{
        $res = $dbConn->prepare($SQL);
        $res->execute(array($playerId));
        return $res->fetchObject();
	}catch(PDOException $ex) {
        return false;
    }
       
        	
}

function get_bonus_details($bonuscode){
	
	$dbConn = connect_db();
    if (!$dbConn || !$bonuscode ){
            return false;
    }
	
	$SQL  = "SELECT * FROM bonuses WHERE coupon_code = ? ";
	
    try{
        $res = $dbConn->prepare($SQL);
        $res->execute(array($bonuscode));
        return $res->fetchObject();
	}catch(PDOException $ex) {
        return false;
    }
       
        	
}

function get_hmt_payments(){
	$db = connect_db();
    if (!$db){
        return false;
    }
	$curtime = time() - 600;
	$cur_day = date('d');
	
	$start_time = time()-(2 * 24 *3600);
	//$start_date = date("Y-m-d H:i:s",$start_time);
	$start_date = date("Y-m-d H:i:s",$start_time);
	
	$end_date = date("Y-m-d H:i:s",$curtime);
	
    $SQL  = "SELECT * ";
    $SQL .= "FROM payments ";
    $SQL .= "WHERE (status = ? OR status = ?) AND payment_method_id IN (142)  AND date>='$start_date' and date<='$end_date'";
	$SQL .= " ORDER BY id DESC ";
    
    try {
        $res = $db->prepare($SQL);
		$res->execute(array('INITIATED','PENDING'));
		return $res->fetchAll();
    } catch(PDOException $ex) {
        return false;
    }
}

function get_hmt_payments1(){
	$db = connect_db();
    if (!$db){
        return false;
    }
	$curtime = time() - 600;
	$cur_day = date('d');
	
	$start_time = time()-(2 * 24 *3600);
	//$start_date = date("Y-m-d H:i:s",$start_time);
	$start_date = date("Y-m-d H:i:s",$start_time);
	
	$end_date = date("Y-m-d H:i:s",$curtime);
	
    $SQL  = "SELECT * ";
    $SQL .= "FROM payments ";
    $SQL .= "WHERE (status = ? OR status = ?) AND payment_method_id IN (143)  AND date>='$start_date' and date<='$end_date'";
	$SQL .= " ORDER BY id DESC ";
    
    try {
        $res = $db->prepare($SQL);
		$res->execute(array('INITIATED','PENDING'));
		return $res->fetchAll();
    } catch(PDOException $ex) {
        return false;
    }
}
function getpaymentsCount1($player_id){
	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT count(*) as count ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE player_id = ? and status = 'SUCCESS' ";
	// $SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
}

function getFirstpaymentsdata1($player_id){
	$db = connect_db();
	if (!$db || !$player_id){
		return false;
	}

	$SQL  = "SELECT id, amount as famount ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE player_id = ? and status = 'SUCCESS' ";
	$SQL .= "ORDER BY id ASC ";
	$SQL .= "LIMIT 1";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($player_id));
		return $res->fetchObject();
	} catch(PDOException $ex) {
		return false;
	}
}

?>

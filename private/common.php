<?php 

require_once 'log4php/Logger.php';

$env = getenv('APP_ENV');
$env = getenv('APP_ENV');
if ($env == 'development'){
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_DB', 'casino_cetrocon');
	define('MYSQL_USER', 'cetrocon_admin');
	define('MYSQL_PASS', 'Dice@1235');
	define('HOST_HTTP', 'https://payments.royalcasino.local');
	define('HOST_HTTP_WEB', 'https://www.royalcasino.local');
	define('HOST_HTTP_NOTIFY', 'https://payments.royalcasino.local');
	define('STAGING',	true);
} elseif ($env == 'preproduction') {
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_DB', 'casino_cetrocon');
	define('MYSQL_USER', 'cetrocon_admin');
	define('MYSQL_PASS', 'lDpxRgwD');
	define('HOST_HTTP', 'https://payments.cetroconcasino.com');
	define('HOST_HTTP_WEB', 'https://www.cetroconcasino.com');
	define('HOST_HTTP_NOTIFY', 'https://payments.cetroconcasino.com');
	define('STAGING',	false);
} else {
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_DB', 'casino_cetrocon');
	define('MYSQL_USER', 'cetrocon_admin');
	define('MYSQL_PASS', 'fpV7u4js');
	define('HOST_HTTP', 'https://payments.royalcasinolounge.com');
	define('HOST_HTTP_WEB', 'https://www.royalcasinolounge.com');
	define('HOST_HTTP_NOTIFY', 'https://payments.royalcasinolounge.com');
	define('STAGING',	false);
}

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
		
	$SQL  = "SELECT * ";
	$SQL .= "FROM payments_requests ";
	$SQL .= "WHERE request_id = ? ";
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
        $SQL .= "FROM players ";
        $SQL .= "WHERE id = ? ";
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


function hasonesuccess_transaction($playerId){
	$db = connect_db();
	if (!$db || !$playerId){
		return false;
	}

	$SQL  = "SELECT count(id) as depositcount ";
	$SQL .= "FROM payments ";
	$SQL .= "WHERE player_id = ? AND payment_method_id = 101 AND payment_provider_id NOT IN (100,104) AND status = 'OK'";

	try {
		$res = $db->prepare($SQL);
		$res->execute(array($playerId));
		return $res->fetchObject();
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_player_name', "catch: ".var_export($ex->getMessage(),true));
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
		$params['status'] = 'OK';
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
	
	$params['amount'] = $params['amount']*100;
	
	if (!$params['player_currency_amount']){
		$params['player_currency_amount'] = $params['amount'];
	}
	
	if (!$params['player_currency_id']){
		$params['player_currency_id'] = $params['currency_id'];
	}
		
	if ($params['amount'] && $params['status'] && $params['currency_id'] && $params['ip'] && $params['player_id'] && $params['payment_method_id'] && $params['payment_provider_id'] && $params['request_id'] && $params['web_id'] && $params['country_id']){
		
		$SQL  = "INSERT INTO payments(amount, description, date, updated, status, currency_id, errorcode, foreign_errorcode, ip, player_id, payment_method_id, payment_provider_id, payment_request_id, web_id, country_id, payment_foreign_id, player_currency_amount, player_currency_id) ";
		$SQL .= "VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";		
		
		try {
			$res = $db->prepare($SQL);
			$res->execute(array($params['amount'], $result['html'], $params['status'], $params['currency_id'], $result['errorcode'], $result['foreign_errorcode'], $params['ip'], $params['player_id'], $params['payment_method_id'], $params['payment_provider_id'], $params['request_id'], $params['web_id'], $params['country_id'], $params['foreign_id'], $params['player_currency_amount'], $params['player_currency_id']));
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
				
		switch(get_extra_information_table($params['payment_method_id'])){
			
			case 'payments_card':

				if ($params['cardnumber']){

					$SQL  = "INSERT INTO payments_card(cardnumber, payment_id) ";
					$SQL .= "VALUES (?, ?) ";
					
					try {
						$res = $db->prepare($SQL);
						$res->execute(array($params['cardnumber'], $PAYMENT_ID));
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
			
		if ($params['status']=='OK'){
			$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at) ";
			$SQL .= "VALUES (?, 0, NOW()) ";
			
			try {
				$res = $db->prepare($SQL);
				$res->execute(array($PAYMENT_ID));
			} catch(PDOException $ex) {
				send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
				return false;
			}
		}
		
		return $PAYMENT_ID;
	}
	send_email(TECH_EMAIL, 'insert_payment', "missing params");
	return false;
}

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


function update_payments_status($paymentid, $playerid, $paymentstatus){
	$db = connect_db();
	if (!$db || !$paymentid || !$playerid){
		return false;
	}
	
	$status = 'KO';
	$description = 'Declined';
	if ($paymentstatus=='OK'){
			$status = 'OK';
			$description = 'Authorised';
	} elseif ($paymentstatus=='PENDING'){
			$description = 'Pending';
			$status = 'PENDING';
	}
	
	$SQL  = "UPDATE payments SET description = ?, updated = NOW(), status = ? ";
	$SQL .= "WHERE id = ? AND player_id = ?";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($description, $status, $paymentid, $playerid));
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
		return false;
	}
	
	if ($status=='OK'){
		$SQL  = "INSERT INTO payments_charges(payment_id, status, inserted_at) ";
		$SQL .= "VALUES (?, 0, NOW()) ";
		
		try {
			$res = $db->prepare($SQL);
			$res->execute(array($paymentid));
		} catch(PDOException $ex) {
			send_email(TECH_EMAIL, 'insert_payment', "catch 8: ".var_export($ex->getMessage(),true));
			return false;
		}
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

?>

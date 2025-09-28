<?php

function get_countryid_from_iso($country_iso){
	
	$db = connect_db();
	
	if (!$db || !$country_iso){
		return false;
	}
	$country_iso = strtoupper(trim($country_iso));
	
	
	$SQL  = "SELECT iso_num ";
	$SQL .= "FROM countries ";
	$SQL .= "WHERE iso = ? ";
	$SQL .= "LIMIT 1 ";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($country_iso));
		$data = $res->fetchObject()->iso_num;
		
		return $data;
		
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_countryid_from_iso', "catch 1: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_currencyid_from_iso($currency_iso){
	
	$db = connect_db();
	
	if (!$db || !$currency_iso){
		return false;
	}
	$currency_iso = strtoupper(trim($currency_iso));
	
	$SQL  = "SELECT iso_num ";
	$SQL .= "FROM currencies ";
	$SQL .= "WHERE iso = ? ";
	$SQL .= "LIMIT 1 ";
	
	try {
		$res = $db->prepare($SQL);
		$res->execute(array($currency_iso));
		$data = $res->fetchObject()->iso_num;
		
		return $data;
		
	} catch(PDOException $ex) {
		send_email(TECH_EMAIL, 'get_currencyid_from_iso', "catch 1: ".var_export($ex->getMessage(),true));
		return false;
	}
}

function get_errorcode($error) {
	if (!$error) {
		return 0;
	}
	
	list($errorcode,) = explode(':', $error);
	$errorcode = preg_replace("/[a-zA-Z]/", "", $errorcode);
	
	if (!is_numeric(intval($error))) {
		send_email(TECH_EMAIL, 'get_errorcode', var_export($error, true).' '.var_export($errorcode, true));
		return 999;
	}
	
	return $errorcode;
}

?>
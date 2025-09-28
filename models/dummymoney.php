<?php

include_once 'common.php';
class AddDummyMoney{
	
	public function process_dummy_amount($params, $method, $provider, $tradeno, $orderinfo){
		// DUMMY PROVIDER RESPONSE
			
		$_ARR_RESP 				= array();
		$_ARR_RESP['Foreign'] 	= time('U');
		$_ARR_RESP['Error'] 	= 201;
		
		$parentProcessarr = array('player_id' => $params['player_id'], 'method' => $params['payment_method_id'], 'provider' => $params['payment_provider_id'], 'transactionid' => $tradeno, 'amount' => $params['amount'], 'status' => 'KO', 'description' => $orderinfo);
		
		$_ARR_RESP['Status'] 	= 'Approved';
		$params['foreign_id'] 	= null;
		if ($_ARR_RESP['Status'] == 'Approved'){
			$params['foreign_id'] = $_ARR_RESP['Foreign'];
			set_foreign_id($params['id'], $params['foreign_id']);
		}
		
		set_request($params['id']);
		set_response($params['id']);
		
		$_RESULT['status'] 		= 'Success';
		$_RESULT['errorcode'] 	= 0;
		$_RESULT['html']   		= 'Authorised';
		
		update_payments_request($params['id'], $provider, $params['amount'], $method);
		$params['payment_method_id'] = $method;
		$params['payment_provider_id'] = $provider;
		$_PAYMENT_ID = false;
		$_PAYMENT_ID = insert_payment($_RESULT, $params);
		insert_parent_transaction($_PAYMENT_ID, $parentProcessarr);
		$finalResult = array('paymentId' => $_PAYMENT_ID, 'status' => $_RESULT['status']);
		return $finalResult;
	}
	
}
?>